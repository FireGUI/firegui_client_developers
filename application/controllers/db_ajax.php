<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Db_ajax extends MY_Controller {

    function __construct() {
        parent :: __construct();
        
        // Qualunque chiamata alle apilib da qua dentro è considerata una
        // chiamata in modalità CRM_FORM
        $this->apilib->setProcessingMode(Apilib::MODE_CRM_FORM);
    }
    
    public function save_form($form_id = null, $edit = false, $value_id = null) {
        
        // ==========================
        // Data required
        // ==========================
        if (!$form_id) {
            show_error('Usage: save_form/{formId}/{editMode? true:false}/{valueId?}');
        }

        // ==========================
        // Load form related infos
        // ==========================
        $form = $this->db->join('entity', 'forms_entity_id=entity_id', 'left')->get_where('forms', array('forms_id' => $form_id))->row_array();
        $form['fields'] = $this->db->join('fields', 'forms_fields_fields_id=fields_id', 'left')
                ->join('fields_draw', 'fields_draw_fields_id=fields_id', 'left')
                ->get_where('forms_fields', ['forms_fields_forms_id' => $form_id])
                ->result_array();

        // ==========================
        // Check permissions on form
        // entity
        // ==========================
        $can_write = $this->datab->can_write_entity($form['entity_id']);
        if (!$can_write) {
            $txt = 'Insufficient permissions to write';
            echo ($this->input->is_ajax_request() ? json_encode(array('status' => 0, 'txt' => $txt)) : $txt);
            die();
        }

        // ==========================
        // Obtain parameters
        // ==========================
        $dati = $this->input->post() ? : [];
        $isOneRecord = $form['forms_one_record'] == 't';
        $edit = (bool) $edit;

        // Normalizza il post in modo che contenga tutti i field del form. Metti
        // null se il campo non è stato passato (oppure un array vuoto se il 
        // il campo è una multiselect
        foreach ($form['fields'] as $field) {
            if (!array_key_exists($field['fields_name'], $dati)) {
                $type = $field['forms_fields_override_type'] ? : $field['fields_draw_html_type'];
                $dati[$field['fields_name']] = ($type==='multiselect') ? []: null;
            }
        }
        
        // Vedo se ci sono field che si riferiscono a relazioni
        // ====
        // questo è un passaggio che devo fare ora perché i
        // vari controlli sui campi fallirebbero dato che ho un array.
        // Prima la condizione richiedeva che ci fosse un REF e che fosse
        // SETTATO il campo dell'eventuale relazione
        // ====
        // Ma questo era scorretto perché se io mi sto ciclando tutti i
        // campi del form e mi trovo una relazione, allora mi aspetto anche
        // che questo form contenga un qualche tipo di multiselect e che se
        // questo campo non viene effettivamente passato, allora vuol dire
        // che devo eliminare tutte le eventuali relazioni (ciò vale per la
        // modifica)
        /*$post_process_relations = [];
        foreach ($form['fields'] as $field) {
            
            if (!$field['fields_ref']) {
                continue;
            }
            
            // In realtà il field ref dovrebbe puntare alla tabella pivot non
            // alla tabella con cui è relazionata ad esempio ho aziende <-> tags
            // il field ref di aziende non dovrebbe puntare a tags, ma ad
            // aziende_tags (il nome della relazione).
            // ====
            // Per mantenere la retrocompatibilità vengono cercate entrambe le varianti
            $dataToInsert = isset($dati[$field['fields_name']]) ? $dati[$field['fields_name']] : [];
            if (!is_array($dataToInsert)) {
                continue;
            }
            
            $relations = $this->db->where_in('relations_name', array($form['entity_name'] . '_' . $field['fields_ref'], $field['fields_ref']))->get('relations');
            if ($relations->num_rows() > 0) {
                $relation = $relations->row();
                
                // Se in un form, metto un campo relazione di un'altra entità,
                // allora probabilmente voglio andare ad inserirlo con un PP
                // Quindi ignoro il campo, assumendo che verrà gestito
                // manualmente
                if (!in_array($form['entity_name'], [$relation->relations_table_1, $relation->relations_table_2])) {
                    continue;
                }
                
                $post_process_relations[] = array(
                    'entity' => $relation->relations_name,
                    'relations_field_1' => $relation->relations_field_1,
                    'relations_field_2' => $relation->relations_field_2,
                    'value' => $dataToInsert
                );
                unset($dati[$field['fields_name']]);
            } elseif ($dataToInsert && in_array(strtoupper($field['fields_type']), ['VARCHAR', 'TEXT'])) {
                $dati[$field['fields_name']] = implode(',', $dataToInsert);
            }
        }*/
        
        // ==========================
        // Data insert
        // ==========================
        $this->db->trans_start();
        $entity = $form['entity_name'];
        $entityIdField = $entity . '_id';
        
        try {
            
            if ($edit) {
                // In questo caso devo controllare se ci sono i dati perché
                // potrebbe essere che abbia aggiornato solamente una relazione
                // e quindi non devo fare un update sull'entità puntata dal
                // form. In ogni caso mi assicuro che $saved contenga il record
                // in questione
//                $saved = $dati?
//                        $this->apilib->edit($entity, $value_id, $dati):
//                        $this->apilib->view($entity, $value_id);
                $savedId = $this->apilib->edit($entity, $value_id, $dati, false);
            } else {
                $savedId = $this->apilib->create($entity, $dati, false);
            }
            
            if (empty($savedId)) {
                throw new Exception('Non è stato possibile salvare i dati');
            }
            
            $saved = $this->apilib->getById($entity, $savedId);
                    
        } catch (Exception $ex) {
            die(json_encode(['status' => 0, 'txt' => $ex->getMessage()]));
        }
        
        if ($isOneRecord) {
            // Se è One Record cancello tutti gli eventuali dati associati
            $this->db->where($entityIdField.' <>', $savedId)->delete($entity);
        }

        
//        if (count($post_process_relations) > 0) {
//            foreach ($post_process_relations as $relation) {
//                /* ==========================================================
//                 * Prima di inserire i dati nella relazione faccio un delete 
//                 * dei record con relations_field_1 uguale al mio insert id
//                 * che corrispondono ai valori vecchi.
//                 * --
//                 * Nel caso di una modifica si eliminano valori vecchi
//                 * nel caso di un inserimento non si elimina niente.
//                 * Corretto?
//                 * ========================================================= */
//                $this->db->delete($relation['entity'], [$relation['relations_field_1'] => $savedId]);
//                if (is_array($relation['value']) && $relation['value']) {
//                    
//                    // Se $relation['value'] è vuoto allora anche
//                    // $relationFullData sarà vuoto
//                    $relationFullData = array_map(function($value) use ($relation, $savedId) {
//                        return [
//                            $relation['relations_field_1'] => $savedId,
//                            $relation['relations_field_2'] => $value
//                        ];
//                    }, $relation['value']);
//                                        
//                    $this->db->insert_batch($relation['entity'], $relationFullData);
//                }
//            }
//        }

        
        // ==========================
        // Finalization
        // ==========================
        $status = is_numeric($form['forms_success_status']) ? $form['forms_success_status'] : 5;
        $message = (empty($form['forms_success_message']) ? ($edit ? 'Modifiche salvate correttamente' : 'Salvataggio effettuato con successo') : $form['forms_success_message']);

        if ($status == 1 && filter_var($message, FILTER_VALIDATE_URL) === false) {
            $message = base_url($message);
        }
        
        if ($this->db->trans_complete()) {
            
            $replaceFrom = ['{value_id}'];
            $replaceTo = [$savedId];
            foreach ($saved as $k => $v) {
                if (!is_array($v)) {
                    $replaceFrom[] = '{' . $k . '}';
                    $replaceTo[] = $v;
                }
            }
            
            echo json_encode(array('status' => $status, 'txt' => str_replace($replaceFrom, $replaceTo, $message), 'close_modals' => 1));
        } else {
            echo json_encode(array('status' => 0, 'txt' => ($edit ? 'Si è verificato un errore imprevisto durante la modifica dei dati' : 'Si è verificato un errore imprevisto durante il salvataggio dei dati')));
        }
    }
    
    public function new_chat_message($gridId = null) {

        $grid = $this->datab->get_grid($gridId);
        if (empty($grid['grids'])) {
            return;
        }

        $data = array();
        foreach ($grid['replaces'] as $replace => $gridField) {
            if (($val = $this->input->post($replace))) {
                $data[$gridField['fields_name']] = $val;
            }
        }

        if (empty($data)) {
            // No data to insert
            return;
        }

        $this->load->library('apilib');
        $newChatMessage = $this->apilib->create($grid['grids']['entity_name'], $data);

        $output = array();
        foreach ($grid['replaces'] as $replace => $gridField) {
            if (isset($newChatMessage[$gridField['fields_name']])) {
                $output[$replace] = $newChatMessage[$gridField['fields_name']];
            }
        }

        if (!empty($output['thumbnail'])) {
            $output['thumbnail'] = base_url("uploads/{$output['thumbnail']}");
        }

        if (!empty($output['date'])) {
            $output['date'] = date('d/m/Y H:i', strtotime($output['date']));
        }

        echo json_encode($output);
    }

    public function save_session_filter($form_id = NULL) {

        if (!$form_id) {
            die('Form id required');
        }

        $form = $this->datab->get_form($form_id);

        if (!$form['forms']['forms_filter_session_key']) {
            debug("Attenzione, filter session key non impostato. Contattare l'assistenza.");
        }

        //debug($form['forms_filter_session_key']);

        $conditions = array_filter($this->input->post('conditions'), function($condition) {
            return !empty($condition['operator']) && !empty($condition['field_id']) && !empty($condition['value']);
        });

        $where_data = $this->session->userdata(SESS_WHERE_DATA);

        if (empty($conditions)) {

            unset($where_data[$form['forms']['forms_filter_session_key']]);
        } else {
            $where_data[$form['forms']['forms_filter_session_key']] = array_combine(array_key_map($conditions, 'field_id'), $conditions);
        }
        
        // Metto in sessione il tutto
        $this->session->set_userdata(SESS_WHERE_DATA, $where_data);

        echo json_encode(array('status' => 2));
    }

    public function save_grid_data($form_id = NULL) {

        if (!$form_id) {
            die('Form id required');
        }

        $grid_id = $this->input->post('grid_id');
        if (!$grid_id) {
            die('Grid id required');
        }

        $conditions = array_filter($this->input->post('conditions'), function($condition) {
            return !empty($condition['operator']) && !empty($condition['field_id']) && !empty($condition['value']);
        });
        $grids_data = $this->session->userdata(SESS_GRIDS_DATA);

        if (empty($conditions)) {
            unset($grids_data[$grid_id]);
        } else {
            $grids_data[$grid_id] = $conditions;
        }

        $this->session->set_userdata(SESS_GRIDS_DATA, $grids_data);
        echo json_encode(array('status' => 2));
    }

    public function update_calendar_event($calendar_id = NULL) {

        if (!$calendar_id) {
            die();
        }

        $data = $this->input->post();
        if (!$data) {
            die();
        }

        $calendar = $this->datab->get_calendar($calendar_id);

        $id_column = $calendar['calendars']['entity_name'] . '_id';
        $result = $this->db->where($id_column, $data[$id_column])
                ->update($calendar['calendars']['entity_name'], $data);

        if ($result) {
            echo json_encode(array('status' => 1, 'txt' => t('salvato')));
        } else {
            echo json_encode(array('status' => 0, 'txt' => t('errore')));
        }
    }
    
    public function old_save_permissions() {

        $this->db->trans_start();
        $user = $this->input->post('permissions_user_id');
        $is_admin = $this->input->post('permissions_admin');
        $groupName = $this->input->post('permissions_group');

        if ($is_admin != 't') {
            $is_admin = 'f';
        }

        if ($is_admin == 'f' && is_numeric($user) && $user > 0) {
            // Controlla se ci sono altri amministratori,
            // altrimenti non posso settare l'ultimo utente come admin
            $login_table = LOGIN_ENTITY;
            $valid_administrators = $this->db->where("permissions_user_id IN (SELECT {$login_table}_id FROM {$login_table})")
                            ->get_where('permissions', array('permissions_user_id <>' => $user, 'permissions_admin' => 't'))->num_rows();

            if ($valid_administrators < 1) {
                echo json_encode(array('status' => 3, 'txt' => 'Non puoi rimuovere i permessi di amministratore a questo utente'));
                return;
            }
        } elseif (is_numeric($groupName)) {
            echo json_encode(array('status' => 3, 'txt' => 'Il nome di un gruppo non può essere un numero'));
            return;
        }


        $data['permissions_admin'] = $is_admin;
        $getPermissionsFromGroup = false;


        if (!is_numeric($user)) {

            // Stiamo modificando un gruppo utenti
            if (!$groupName) {
                $groupName = $user;
            }

            $data['permissions_group'] = $groupName;

            // Prendo l'eventuale vecchio permesso di questo gruppo ed elimino
            // le vecchie impostazioni. Faccio l'update anche su tutti gli
            // utenti appartenenti a questo gruppo - N.B. $user conterrebbe il
            // vecchio nome utente (nel caso in cui questo sia stato rinominato)
            $oldGroupName = $user;
            $this->db->update('permissions', $data, array('permissions_group' => $oldGroupName));

            // Prendo tutti gli utenti con questi permessi
            $permissions = $this->db->get_where('permissions', array('permissions_group' => $groupName));
            $permissionIds = array_key_map($permissions->result_array(), 'permissions_id');
        } elseif ($user < 0) {

            // Stiamo inserendo un nuovo gruppo
            $data['permissions_user_id'] = null;
            $data['permissions_group'] = $groupName;

            $this->db->insert('permissions', $data);
            $permissionIds = array($this->db->insert_id());
        } else {

            if ($groupName) {
                $groupArray = $this->db->get_where('permissions', array('permissions_group' => $groupName))->row_array();
                if ($groupArray) {
                    $data['permissions_group'] = $groupArray['permissions_group'];
                    $data['permissions_admin'] = $groupArray['permissions_admin'];
                } else {
                    // Il gruppo non esiste più... mah...
                    $groupName = '';
                    $data['permissions_group'] = null;
                }
            } else {

                // Stiamo modificando i permessi di un utente, senza modifica
                // del gruppo, quindi devo toglierlo perché questo utente ha
                // dei permessi custom
                $data['permissions_group'] = null;
            }

            $data['permissions_user_id'] = $user;

            $oldPermissions = $this->db->get_where('permissions', array('permissions_user_id' => $user));
            if ($oldPermissions->num_rows() > 0) {
                $permissionId = $oldPermissions->row()->permissions_id;
                $this->db->update('permissions', $data, array('permissions_id' => $permissionId));
            } else {
                $getPermissionsFromGroup = (bool) $data['permissions_group'];
                $this->db->insert('permissions', $data);
                $permissionId = $this->db->insert_id();
            }

            $permissionIds = array($permissionId);
        }

        // Elimino eventuali vecchie informazioni residue
        $this->db->where_in('permissions_entities_permissions_id', $permissionIds)->delete('permissions_entities');
        $this->db->where_in('permissions_modules_permissions_id', $permissionIds)->delete('permissions_modules');


        if ($getPermissionsFromGroup) {

            // In questo caso sto assegnando un utente ad un gruppo quindi i
            // permessi che prendo sono quelli del gruppo e non quelli passati
            // in post
            $this->db->query("
                    INSERT INTO permissions_entities (permissions_entities_permissions_id, permissions_entities_entity_id, permissions_entities_value)
                    SELECT permissions_entities_permissions_id, permissions_entities_entity_id, permissions_entities_value
                    FROM permissions_entities
                    WHERE permissions_entities_permissions_id IN (SELECT permissions_id FROM permissions WHERE permissions_user_id IS NULL AND permissions_group = '{$groupName}')
                ");

            $this->db->query("
                    INSERT INTO permissions_modules (permissions_modules_permissions_id, permissions_modules_module_name, permissions_modules_value)
                    SELECT permissions_modules_permissions_id, permissions_modules_module_name, permissions_modules_value
                    FROM permissions_modules
                    WHERE permissions_modules_permissions_id IN (SELECT permissions_id FROM permissions WHERE permissions_user_id IS NULL AND permissions_group = '{$groupName}')
                ");
        } else {

            // In questo caso non assegno un utente da post, quindi mi tocca
            // prendere i dati passati dal browser
            $entities = (array) $this->input->post('entities');
            $modules = (array) $this->input->post('modules');

            // Ciclo tutti i permessi ad entità e moduli passati e 
            foreach ($permissionIds as $permissionId) {
                foreach ($entities as $entity_id => $permission_value) {
                    $this->db->insert('permissions_entities', array(
                        'permissions_entities_permissions_id' => $permissionId,
                        'permissions_entities_entity_id' => $entity_id,
                        'permissions_entities_value' => $permission_value
                    ));
                }

                foreach ($modules as $mod_name => $permission_value) {
                    $this->db->insert('permissions_modules', array(
                        'permissions_modules_permissions_id' => $permissionId,
                        'permissions_modules_module_name' => $mod_name,
                        'permissions_modules_value' => $permission_value
                    ));
                }
            }
        }




        // Gestione limiti completamente indipendente dai gruppi/permessi
        // (al momento)
        // Rimuovi limiti precedenti
        if (is_numeric($user) && $user > 0) {
            $limits = (array) $this->input->post('limits');
            $this->db->delete('limits', array('limits_user_id' => $user));

            foreach ($limits as $limit) {
                // Check array
                if (empty($limit['limits_fields_id']) || empty($limit['limits_operator']) || empty($limit['limits_value'])) {
                    continue;
                }

                // Check for this limit in the db
                $oldLimits = $this->db->get_where('limits', array('limits_user_id' => $user, 'limits_fields_id' => $limit['limits_fields_id']));
                if ($oldLimits->num_rows() > 0) {
                    // If found we want to edit it's value and operator
                    $limit_id = $oldLimits->row()->limits_id;
                    $this->db->update('limits', $limit, array('limits_id' => $limit_id));
                } else {
                    // Else we want to create a new limit for the user
                    $limit['limits_user_id'] = $user;
                    $this->db->insert('limits', $limit);
                }
            }
        }
        $this->db->trans_complete();

        echo json_encode(array('status' => 5, 'txt' => 'Permessi salvati correttamente'));
    }
    
    /**
     * Salva i permessi
     * accetta da post:
     * - Crea gruppo:
     *      permissions_user_id = -1
     *      permissions_group   = 'nome gruppo'
     *      
     * - Salva gruppo:
     *      permissions_user_id = 'nome gruppo'
     *      permissions_group   = null
     *      
     * - Salva utente con gruppo
     *      permissions_user_id = id_utente
     *      permissions_group   = 'nome gruppo'
     * 
     * - Salva utente no gruppo
     *      permissions_user_id = id_utente
     *      permissions_group   = null
     */
    public function save_permissions() {
        
        $user = $this->input->post('permissions_user_id');
        $isAdmin = ($this->input->post('permissions_admin')==='t');
        $groupName = $this->input->post('permissions_group');
        $permEntities = $this->input->post('entities')? (array)$this->input->post('entities'): [];
        $permModules  = $this->input->post('modules')? (array)$this->input->post('modules'): [];
        
        $this->db->trans_start();
        try {
            if (!is_numeric($user)) {
                
                // Salvataggio di un gruppo già creato (occhio che $user = nome gruppo in sto caso)
                $this->datab->setPermissions($user, $isAdmin, $permEntities, $permModules);
                foreach ($this->datab->getUserGroups() as $userId => $groupName) {
                    if ($groupName === $user) {
                        $this->datab->addUserGroup($userId, $groupName);
                    }
                }
                if (($rename = trim($this->input->post('permissions_group_rename')))) {
                    $this->db->update('permissions', ['permissions_group' => $rename], ['permissions_group' => $user]);
                }
                echo json_encode(['status' => 5, 'txt' => 'Gruppo salvato correttamente']);
                
            } elseif ($user < 0) {
                
                // Creazione nuovo gruppo
                $this->datab->setPermissions($groupName, $isAdmin, $permEntities, $permModules);
                echo json_encode(['status' => 5, 'txt' => 'Gruppo creato correttamente']);
                
            } elseif ($groupName) {
                
                // Assegno utente a gruppo
                $this->datab->addUserGroup($user, $groupName);
                echo json_encode(['status' => 5, 'txt' => 'Permessi utente salvati']);
                
            } else {
                
                // Salvo permessi utente senza gruppo
                $this->datab->setPermissions($user, $isAdmin, $permEntities, $permModules);
                echo json_encode(['status' => 5, 'txt' => 'Permessi utente salvati']);
                
            }
            
        } catch (Exception $ex) {
            die(json_encode(['status' => 3, 'txt' => $ex->getMessage()]));
        }


        // Gestione limiti completamente indipendente dai gruppi/permessi
        // (al momento)
        // Rimuovi limiti precedenti
        if (is_numeric($user) && $user > 0) {
            $limits = (array) $this->input->post('limits');
            $this->db->delete('limits', array('limits_user_id' => $user));

            foreach ($limits as $limit) {
                // Check array
                if (empty($limit['limits_fields_id']) || empty($limit['limits_operator']) || empty($limit['limits_value'])) {
                    continue;
                }

                // Check for this limit in the db
                $oldLimits = $this->db->get_where('limits', array('limits_user_id' => $user, 'limits_fields_id' => $limit['limits_fields_id']));
                if ($oldLimits->num_rows() > 0) {
                    // If found we want to edit it's value and operator
                    $limit_id = $oldLimits->row()->limits_id;
                    $this->db->update('limits', $limit, array('limits_id' => $limit_id));
                } else {
                    // Otherwise we want to create a new limit for the user
                    $limit['limits_user_id'] = $user;
                    $this->db->insert('limits', $limit);
                }
            }
        }


        $this->db->trans_complete();
        
        
    }  
    
    

    public function save_views_permissions() {
        
        $inputPostView = $this->input->post('view');
        
        /*
         * Ottieni per ogni gruppo la lista di utenti corrispondente, quindi
         * ad es:
         *      group1 => 1,2,3,4,5
         *      group2 => 10,12,15,20
         *      ...
         */
        $userGroupsStatus = $this->datab->getUserGroups();
        $groupUsers = [];
        foreach ($userGroupsStatus as $user => $group) {
            if ($group) {
                $groupUsers[$group][] = $user;
            }
        }
        
        /*
         * In input ho un array dove nelle chiavi ho l'id layout e ogni elemento
         * è un array contenente diversi valori:
         *  - se il valore è numerico, allora significa che quell'utente può
         *    accedere a quel layout
         *  - se il valore è una stringa non numerica, allora lo interpreto come
         *    nome gruppo e quindi tutti gli utenti appartenenti a quel gruppo
         *    potranno accedere al layout
         */
        $viewsAccessWithGroups = $inputPostView;
        $viewsAccess = [];
        foreach ($viewsAccessWithGroups as $layout => $users) {
            $viewsAccess[$layout] = [];
            foreach ($users as $user) {
                if (is_numeric($user)) {
                    $viewsAccess[$layout][] = $user;
                } elseif (isset($groupUsers[$user]) && is_array($groupUsers[$user])) {
                    $viewsAccess[$layout] = array_merge($viewsAccess[$layout], $groupUsers[$user]);
                }
            }
        }
        
        $layouts = $this->db->get('layouts')->result_array();
        $users = $this->db->get(LOGIN_ENTITY)->result_array();
        
        $batchData = [];
        foreach ($layouts as $layout) {
            $layoutID = $layout['layouts_id'];
            foreach ($users as $user) {
                $userID = $user[LOGIN_ENTITY . '_id'];

                if (empty($viewsAccess[$layoutID]) OR !in_array($userID, $viewsAccess[$layoutID])) {
                    $batchData[] = ['unallowed_layouts_layout' => $layoutID, 'unallowed_layouts_user' => $userID];
                }
            }
        }
        
        
        $this->db->trans_start();
        $this->db->query('DELETE FROM unallowed_layouts');
        if ($batchData) {
            $this->db->insert_batch('unallowed_layouts', $batchData);
        }
        $this->db->trans_complete();
        echo json_encode(array('status' => 5, 'txt' => 'Impostazioni visibilità layout impostate'));
    }
    
    public function delete_permission_group() {
        $groupName = $this->input->post('group');
        if (is_numeric($groupName)) {
            return;
        }
        
        $groupPermission = $this->datab->getPermission($groupName);
        
        // Rimuovi record da tabella permessi
        $this->datab->removePermissionById($groupPermission['permissions_id']);
    }
    

    /**
     * Metodo per edit delle datatable inline
     * Potrebbe funzionare anche per altre entità,
     * ma è designato per support table
     */
    public function generic_edit($entity_name = NULL, $id = NULL) {
        $data = $this->input->post();
        $this->db->update($entity_name, $data, array($entity_name . '_id' => $id));
    }

    /**
     * Inserisci un nuovo record nell'entità
     * Il record viene inserito anche vuoto
     */
    public function generic_insert($entity_name = NULL) {
        $data = $this->input->post();
        if ($data) {
            $this->db->insert($entity_name, $data);
        }

        echo $this->db->insert_id();
    }

    public function generic_delete($entity_name = NULL, $id = NULL) {

        /**
         * Controlla permessi di scrittura per utente loggato
         */
        $entity = $this->datab->get_entity_by_name($entity_name);
        $can_write = $this->datab->can_write_entity($entity['entity_id']);
        if (!$can_write) {
            $txt = 'Non hai i permessi per eliminare il record';
            die($this->input->is_ajax_request() ? json_encode(['status' => 3, 'txt' => $txt]) : $txt);
        }
        
        try {
            $this->apilib->delete($entity_name, $id);
        } catch (Exception $ex) {
            die($this->input->is_ajax_request() ? json_encode(['status' => 3, 'txt' => $ex->getMessage()]) : $ex->getMessage());
        }
        
        
        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 2]);
        } else {
            redirect(filter_input(INPUT_SERVER, 'HTTP_REFERER'));
        }
    }
    
    /**
     * Perform an update using $_GET data
     * 
     * @param string $entity
     * @param int $id
     */
    public function update($entity, $id) {
        
        if (!$this->datab->can_write_entity($entity)) {
            if ($this->input->is_ajax_request()) {
                die(json_encode(array('status' => 5, 'txt' => 'Non hai i permessi per eseguire questa azione')));
            } else {
                $location = filter_input(INPUT_SERVER, 'HTTP_REFERER')?:base_url();
                redirect($location);
            }
        }
        
        $data = $this->input->get();
        if ($data && is_array($data)) {
            $this->apilib->edit($entity, $id, $data);
        }
        
        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 2]);
        } else {
            $location = filter_input(INPUT_SERVER, 'HTTP_REFERER')?:base_url();
            redirect($location);
        }
    }
    

    /**
     * Switch di un field booleano
     * l'utente deve avere i permessi di scrittura per l'entità
     */
    public function switch_bool($field_identifier, $id) {

        $this->db->where('fields_type', 'BOOL');
        if (is_numeric($field_identifier)) {
            $field = $this->db->get_where('fields', array('fields_id' => $field_identifier))->row_array();
        } elseif (is_string($field_identifier)) {
            $field = $this->db->get_where('fields', array('fields_name' => $field_identifier))->row_array();
        } else {
            $field = array();
        }


        if (!empty($field) && $this->datab->can_write_entity($field['fields_entity_id'])) {
            $entity = $this->datab->get_entity($field['fields_entity_id']);
            $table = $entity['entity_name'];
            $column = $field['fields_name'];

            // Get current value
            $record = $this->db->get_where($table, array($table . '_id' => $id))->row_array();
            if ($record) {
                $new_value = (empty($record[$column]) || $record[$column] === 'f' ? 't' : 'f');
                $this->apilib->edit($table, $id, [$column => $new_value]);
                //$this->db->update($table, array($column => $new_value), array($table . '_id' => $id));
            }
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode(array('status' => 2));     //Refresh se richiesta fatta da ajax
        } else {
            redirect(base_url());
        }
    }

    public function notify_read($notificationId = null) {
        if ($notificationId && is_numeric($notificationId)) {
            $this->datab->readNotification($notificationId);
        } else {
            $this->datab->readAllNotifications();
        }
    }

    public function ck_uploader() {
        $url = './uploads/' . time() . "_" . strtolower(str_replace(' ', '_', $_FILES['upload']['name']));
        //extensive suitability check before doing anything with the file...
        if (($_FILES['upload'] == "none") OR ( empty($_FILES['upload']['name']))) {
            $message = "No file uploaded.";
        } else if ($_FILES['upload']["size"] == 0) {
            $message = "The file is of zero length.";
        } else if (($_FILES['upload']["type"] != "image/pjpeg") AND ( $_FILES['upload']["type"] != "image/jpeg") AND ( $_FILES['upload']["type"] != "image/png")) {
            $message = "The image must be in either JPG or PNG format. Please upload a JPG or PNG instead.";
        } else if (!is_uploaded_file($_FILES['upload']["tmp_name"])) {
            $message = "You may be attempting to hack our server. We're on to you; expect a knock on the door sometime soon.";
        } else {
            $message = "";
            $move = @ move_uploaded_file($_FILES['upload']['tmp_name'], $url);
            if (!$move) {
                $message = "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions.";
            }
            $url = str_replace("./", base_url_template(), $url);
        }

        $funcNum = $_GET['CKEditorFuncNum'];
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
    }
    
    
    public function changeLanguage() {
        $langKey = $this->input->get_post('language');
        if ($this->datab->changeLanguage($langKey)) {
            echo json_encode(['success' => true, 'lang' => $this->datab->getLanguage()]);
        } else {
            echo json_encode(['success' => false, 'lang' => null]);
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */