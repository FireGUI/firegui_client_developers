<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Db_ajax extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Ottieni il nome del metodo corrente
        $currentMethod = $this->router->fetch_method();
        $guest = $this->auth->guest();
        // Verifica se il metodo corrente è 'save_form'
        if ($currentMethod == 'save_form' && $guest) {
            // Qui dovresti implementare la logica per controllare se il form è pubblico o meno
            // Ad esempio, ottenere l'ID del form dai segmenti dell'URL e poi interrogare il database
            $formId = $this->uri->segment(3); // Assumendo che l'ID del form sia il terzo segmento dell'URL
            //debug($formId,true);
            $this->db->where('forms_id', $formId);
            $form = $this->db->get('forms')->row_array();

            if ($form && $form['forms_public'] == DB_BOOL_FALSE) {
                // Il form non è pubblico, quindi verifica l'autenticazione
                $this->checkAuth();
            }
            // Altrimenti, se il form è pubblico, non fare nulla (non richiedere l'autenticazione)
        } elseif ($currentMethod == 'multi_upload_async' && $guest) {
            $field_id = $this->uri->segment(3); 
            //$field = $this->datab->get_field($field_id);
            $form = $this->db->query("SELECT * FROM forms WHERE forms_public = '1' AND forms_id IN (SELECT forms_fields_forms_id FROM forms_fields WHERE forms_fields_fields_id = '$field_id')")->row_array();
            if (!$form) {
                // Il form non è pubblico, quindi verifica l'autenticazione
                $this->checkAuth();
            }
            
        } elseif ($guest) {
            // Per tutti gli altri metodi, verifica sempre l'autenticazione
            $this->checkAuth();
        }
        $this->apilib->setProcessingMode(Apilib::MODE_CRM_FORM);
    }

    private function checkAuth()
    {
        
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        
    }

    public function save_form($form_id = null, $edit = false, $value_id = null)
    {

        // ==========================
        // Data required
        // ==========================
        if (!$form_id) {
            show_error('Usage: save_form/{formId}/{editMode? true:false}/{valueId?}');
        }

        /**
         * 2021-12-09 - Michael E., Matteo P.
         * Queste 3 righe di codice son state fatte perchè per qualche motivo, sul crm di efficient driving, form di modifica trainer (layout 8, form 9) c'è questo parametro '0' sulla global $_POST.
         */
        if (isset($_POST[0])) {
            unset($_POST[0]);
        }

        if (!is_numeric($form_id)) {
            $this->db->where('forms_identifier', $form_id);
        } else {
            $this->db->where('forms_id', $form_id);
        }

        // ==========================
        // Load form related infos
        // ==========================
        $form = $this->db->join('entity', 'forms_entity_id=entity_id', 'left')->get('forms')->row_array();

        if (!$form)
            show_error(t('Form not found!'));

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
            $txt = t('Insufficient permissions to write');
            echo ($this->input->is_ajax_request() ? json_encode(array('status' => 0, 'txt' => $txt)) : $txt);
            die();
        }

        // ==========================
        // Obtain parameters
        // ==========================
        $dati = $this->input->post() ?: [];

        $dati = $this->security->xss_clean($dati, $form['fields']);

        $isOneRecord = $form['forms_one_record'] == DB_BOOL_TRUE;
        $edit = (bool) $edit;

        if ($edit && empty($value_id)) {
            $value_id = $this->input->post('value_id');
        }

        // Normalizza il post in modo che contenga tutti i field del form. Metti
        // null se il campo non è stato passato (oppure un array vuoto se il
        // il campo è una multiselect
        foreach ($form['fields'] as $field) {
            if (!array_key_exists($field['fields_name'], $dati)) {
                $type = $field['forms_fields_override_type'] ?: $field['fields_draw_html_type'];
                $dati[$field['fields_name']] = ($type === 'multiselect') ? [] : null;
            }
        }

        // ==========================
        // Data insert
        // ==========================
        $this->db->trans_start();
        $entity = $form['entity_name'];
        $entityIdField = $entity . '_id';

        try {
            if ($isOneRecord) {
                $old_record = $this->apilib->searchFirst($entity);
                if ($old_record) {
                    $savedId = $old_record[$entityIdField];

                    $this->apilib->edit($entity, $savedId, $dati);
                } else {
                    $savedId = $this->apilib->create($entity, $dati, false);
                }
            } else {
                if ($edit) {

                    // In questo caso devo controllare se ci sono i dati perché
                    // potrebbe essere che abbia aggiornato solamente una relazione
                    // e quindi non devo fare un update sull'entità puntata dal
                    // form. In ogni caso mi assicuro che $saved contenga il record
                    // in questione

                    if (!is_array($value_id)) {
                        $savedId = $this->apilib->edit($entity, $value_id, $dati, false);
                    } else {
                        //Nel dubbio rimuovo i fields non checcati nel form bulk (comunque non dovrebbe passarli il browser, ma non si sa mai cosa fa Internet explorer...)
                        foreach ($dati as $key => $val) {
                            if (!in_array($key, (array) $this->input->post('edit_fields'))) {
                                unset($dati[$key]);
                            }
                        }
                        foreach ($value_id as $val) {
                            $savedId = $this->apilib->edit($entity, $val, $dati, false);
                        }
                    }
                } else {
                    
                    $savedId = $this->apilib->create($entity, $dati, false);
                }
            }
            if (empty($savedId)) {
                throw new Exception('Non è stato possibile salvare i dati');
            }

            $saved = $this->apilib->getById($entity, $savedId);
        } catch (Exception $ex) {
            die(json_encode(['status' => 0, 'txt' => $ex->getMessage()]));
        }

        // ==========================
        // Finalization
        // ==========================
        $status = is_numeric($form['forms_success_status']) ? $form['forms_success_status'] : 7;
        $message = (empty($form['forms_success_message']) ? ($edit ? t('Changes saved successfully') : t('Successfully saved')) : $form['forms_success_message']);

        if ($edit && isset($form['forms_success_status_edit'])) {
            // In edit ho delle condizioni ancora diverse
            $status = is_numeric($form['forms_success_status_edit']) ? $form['forms_success_status_edit'] : $status;
            $message = empty($form['forms_success_message_edit']) ? $message : $form['forms_success_message_edit'];
        }

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
            if (in_array($status, [0, 1, 2, 3, 4, 5])) {
                echo json_encode(
                    array(
                        'status' => $status,
                        'txt' => str_replace($replaceFrom, $replaceTo, $message),
                        'data' => $saved,
                        'cache_tags' => $this->mycache->buildTagsFromEntity($entity),
                    ),
                    JSON_INVALID_UTF8_SUBSTITUTE,

                );
            } elseif (in_array($status, [6, 7])) {
                $return_data = array(
                    'status' => $status,
                    'txt' => str_replace($replaceFrom, $replaceTo, $message),
                    'close_modals' => 1,
                    'related_entity' => $entity,
                    'data' => $saved,
                );

                if ($status == 7) {
                    $return_data['reset_form'] = DB_BOOL_TRUE;
                    $return_data['refresh_grids'] = DB_BOOL_TRUE;
                    $return_data['cache_tags'] = $this->mycache->buildTagsFromEntity($entity);
                }

                echo json_encode($return_data, JSON_INVALID_UTF8_SUBSTITUTE);
            } else {
                echo json_encode(array('status' => 2, 'txt' => str_replace($replaceFrom, $replaceTo, $message), 'data' => $saved), JSON_INVALID_UTF8_SUBSTITUTE);
            }

            //
        } else {
            echo json_encode(array('status' => 0, 'txt' => ($edit ? 'Si è verificato un errore imprevisto durante la modifica dei dati' : 'Si è verificato un errore imprevisto durante il salvataggio dei dati')));
        }
    }

    public function new_chat_message($gridId = null)
    {
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

        $newChatMessage = $this->apilib->create($grid['grids']['entity_name'], $data);

        $output = array();
        foreach ($grid['replaces'] as $replace => $gridField) {
            if (isset($newChatMessage[$gridField['fields_name']])) {
                $output[$replace] = $newChatMessage[$gridField['fields_name']];
            }
        }

        $output['id'] = $newChatMessage[$grid['grids']['entity_name'] . '_id'];

        if (!empty($output['thumbnail'])) {
            $output['thumbnail'] = base_url_uploads("uploads/{$output['thumbnail']}");
        }

        if (!empty($output['date'])) {
            $output['date'] = date('d/m/Y H:i', strtotime($output['date']));
        }

        echo json_encode($output);
    }

    public function save_session_filter($form_id = null)
    {
        // Recupero il form
        // Ignoro il get_form del datab: a me serve solo la 'filter session key'
        // quindi faccio una query secca tramite il getById di apilib
        $form_id or die(json_alert('Form id required'));

        $this->mycache->clearCache('full_page');

        $form = $this->apilib->getById('forms', $form_id);

        $entity = $this->crmentity->getEntityFullData($form['forms_entity_id']);

        $_visible_fields = $entity['visible_fields'];
        $visible_fields = [];
        foreach ($_visible_fields as $field) {
            $visible_fields[$field['fields_id']] = $field;
        }

        $form or die(json_alert("Form `{$form_id}` not exists"));

        $filterSessionKey = $form['forms_filter_session_key'];

        // Processo le condizioni da salvare in input
        $conditions = [];
        $where_data = $this->session->userdata(SESS_WHERE_DATA);
        // Se clicchiamo il pulsante pulisci filtri, allora ignoro l'input
        if (!$this->input->post('clear-filters')) {

            // Se invece ho fatto un submit normale, valuto le condizioni valide
            // da tenere in sessione
            foreach ($this->input->post('conditions') as $conditional) {
                if (!array_key_exists($conditional['field_id'], $visible_fields)) {
                    //TODO Wrong! Field id can be in another left joined table, so get the field information direct from the field_id to check his type...
                    //throw new Exception("Missing field '{$conditional['field_id']}' in entity '{$entity['entity']['entity_name']}'.");
                } elseif (array_key_exists('value', $conditional) && $conditional['value'] !== '') { //Se ho passato un valore devo verificarne la consistenza
                    //Check field consistency

                    $error = false;
                    $error_text = '';
                    switch ($visible_fields[$conditional['field_id']]['fields_type']) {
                        case 'INT':
                        case 'TIMESTAMP WITHOUT TIME ZONE':
                            break;
                        default:
                            break;
                    }
                    if ($error && !empty($error_text)) {
                        throw new Exception($error_text);
                    }
                }

                if (
                    !empty($conditional['operator']) &&
                    !empty($conditional['field_id']) &&
                    (array_key_exists('value', $conditional))
                ) {
                    if (!array_key_exists('value', $conditional)) {
                        $conditional['value'] = '';
                    }
                    $conditions[$conditional['field_id']] = $conditional;
                } else {
                    //unset($where_data[$filterSessionKey][$conditional['field_id']]);
                }
            }
        }

        //debug($conditions, true);

        // Aggiorno i dati da sessione mettendo alla chiave corretta le
        // condizioni processate. Nel caso in cui siano vuote, queste verranno
        // rimosse con un array_filter

        $where_data[$filterSessionKey] = $conditions;

        $this->session->set_userdata(SESS_WHERE_DATA, array_filter($where_data));

        //Invalidate cache
        //$this->mycache->clearCache();

        //debug($this->session->userdata(SESS_WHERE_DATA));

        // Mando output
        //        json_refresh();
        $status = is_numeric($form['forms_success_status']) ? $form['forms_success_status'] : 7;
        $message = empty($form['forms_success_message']) ? t('Successfully saved') : $form['forms_success_message'];

        //debug($entity, true);
        $entity_name = $entity['entity']['entity_name'];
        if (in_array($status, [0, 1, 2, 3, 4, 5])) {
            echo json_encode(
                array(
                    'status' => $status,
                    'txt' => $message,
                )
            );
        } elseif (in_array($status, [6, 7])) {
            if ($this->input->post('clear-filters')) {
                $reset_form = true;
            } else {
                $reset_form = false;
            }
            echo json_encode(array('status' => $status, 'txt' => $message, 'close_modals' => false, 'refresh_grids' => 1, 'related_entity' => $entity_name, 'reset_form' => $reset_form));
        }
    }

    public function save_grid_data($form_id = null)
    {
        if (!$form_id) {
            die('Form id required');
        }

        $grid_id = $this->input->post('grid_id');
        if (!$grid_id) {
            die('Grid id required');
        }

        $conditions = array_filter($this->input->post('conditions'), function ($condition) {
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

    public function update_calendar_event($calendar_id = null)
    {
        if (!$calendar_id) {
            die();
        }

        $data = $this->input->post();
        if (!$data) {
            die();
        }

        $calendar = $this->datab->get_calendar($calendar_id);

        $entity_name = $calendar['calendars']['entity_name'];
        $id_column = $entity_name . '_id';

        try {
            $result = $this->apilib->edit($entity_name, $data[$id_column], $data);
            echo json_encode(array('status' => 1, 'txt' => t('salvato')));
        } catch (Exception $e) {
            echo json_encode(array('status' => 0, 'txt' => $e->getMessage()));
        }
    }

    public function old_save_permissions()
    {
        $this->db->trans_start();
        $user = $this->input->post('permissions_user_id');
        $is_admin = $this->input->post('permissions_admin');
        $groupName = $this->input->post('permissions_group');

        if ($is_admin != DB_BOOL_TRUE) {
            $is_admin = DB_BOOL_FALSE;
        }

        if ($is_admin == DB_BOOL_FALSE && is_numeric($user) && $user > 0) {
            // Controlla se ci sono altri amministratori,
            // altrimenti non posso settare l'ultimo utente come admin
            $login_table = LOGIN_ENTITY;
            $valid_administrators = $this->db->where("permissions_user_id IN (SELECT {$login_table}_id FROM {$login_table})")
                ->get_where('permissions', array('permissions_user_id <>' => $user, 'permissions_admin' => DB_BOOL_TRUE))->num_rows();

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
                        'permissions_entities_value' => $permission_value,
                    )
                    );
                }

                foreach ($modules as $mod_name => $permission_value) {
                    $this->db->insert('permissions_modules', array(
                        'permissions_modules_permissions_id' => $permissionId,
                        'permissions_modules_module_name' => $mod_name,
                        'permissions_modules_value' => $permission_value,
                    )
                    );
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
    public function save_permissions()
    {
        $user = $this->input->post('permissions_user_id');
        $isAdmin = ($this->input->post('permissions_admin') === DB_BOOL_TRUE);
        $groupName = $this->input->post('permissions_group');
        $permEntities = $this->input->post('entities') ? (array) $this->input->post('entities') : [];
        $permModules = $this->input->post('modules') ? (array) $this->input->post('modules') : [];

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

    public function save_views_permissions()
    {
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
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            $this->db->where(LOGIN_ACTIVE_FIELD, DB_BOOL_TRUE);
        }
        $users = $this->db->get(LOGIN_ENTITY)->result_array();

        $batchData = [];
        foreach ($layouts as $layout) {
            $layoutID = $layout['layouts_id'];
            foreach ($users as $user) {
                $userID = $user[LOGIN_ENTITY . '_id'];

                if (empty($viewsAccess[$layoutID]) or !in_array($userID, $viewsAccess[$layoutID])) {
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

    public function delete_permission_group()
    {
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
    public function datatable_inline_edit($entity_name = null, $id = null)
    {
        try {
            $fields = $this->db->join('entity', 'entity_id=fields_entity_id', 'left')

                ->get_where('fields', ['entity_name' => $entity_name])
                ->result_array();
            $data = $this->input->post();
            $data = $this->security->xss_clean($data, $fields);
            if ($id) {
                $this->apilib->edit($entity_name, $id, $data);
            } else {
                $this->apilib->create($entity_name, $data);
            }
        } catch (Exception $ex) {
            set_status_header(400); // Bad-Request se fallisce
            die(json_encode($ex->getMessage()));
        }
    }

    /**
     * Inserisci un nuovo record nell'entità
     * Il record viene inserito anche vuoto
     */
    public function datatable_inline_insert($entity_name = null)
    {
        try {
            $fields = $this->db->join('entity', 'entity_id=fields_entity_id', 'left')

                ->get_where('fields', ['entity_name' => $entity_name])
                ->result_array();
            $data = $this->input->post();
            $data = $this->security->xss_clean($data, $fields);
            $id = $this->apilib->create($entity_name, $data, false);
        } catch (Exception $ex) {
            set_status_header(400); // Bad-Request se fallisce
            die(json_encode($ex->getMessage()));
        }

        echo json_encode($id);
    }

    public function generic_delete($entity_name = null, $id = null)
    {

        /**
         * Controlla permessi di scrittura per utente loggato
         */
        $entity = $this->datab->get_entity_by_name($entity_name);
        $can_write = $this->datab->can_write_entity($entity['entity_id']);
        if (!$can_write) {
            //$txt = 'Non hai i permessi per eliminare il record';
            $txt = t('Insufficient permissions to delete');
            die($this->input->is_ajax_request() ? json_encode(['status' => 3, 'txt' => $txt]) : $txt);
        }

        try {
            if (empty($id) && is_array($this->input->post('ids'))) {
                foreach ($this->input->post('ids') as $id) {
                    $this->apilib->delete($entity_name, $id);
                }
            } else {
                $this->apilib->delete($entity_name, $id);
            }
        } catch (Exception $ex) {
            die($this->input->is_ajax_request() ? json_encode(['status' => 3, 'txt' => $ex->getMessage()]) : $ex->getMessage());
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 7]);
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
    public function update($entity, $id)
    {
        if (!$this->datab->can_write_entity($entity)) {
            if ($this->input->is_ajax_request()) {
                die(json_encode(array('status' => 5, 'txt' => t('Insufficient permissions to update'))));
            } else {
                $location = filter_input(INPUT_SERVER, 'HTTP_REFERER') ?: base_url();
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
            $location = filter_input(INPUT_SERVER, 'HTTP_REFERER') ?: base_url();
            redirect($location);
        }
    }

    /**
     * Switch di un field booleano
     * l'utente deve avere i permessi di scrittura per l'entità
     */
    public function switch_bool($field_identifier, $id, $use_apilib = DB_BOOL_TRUE)
    {
        $this->db->where('fields_type', DB_BOOL_IDENTIFIER);
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
                $new_value = (empty($record[$column]) || $record[$column] === DB_BOOL_FALSE ? DB_BOOL_TRUE : DB_BOOL_FALSE);
                
                if ($use_apilib == DB_BOOL_TRUE) {
                    $this->apilib->edit($table, $id, [$column => $new_value]);
                } else {
                    $this->db->where($table . '_id', $id)->update($table, [$column => $new_value]);
                    $this->mycache->clearCache();
                }
            }
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode(array('status' => 5, 'txt' => false)); //Refresh dei vari box
        } else {
            if (!empty($_SERVER['HTTP_REFERER'])) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(base_url());
            }
        }
    }

    public function change_value($entity_name = null, $id = null, $field_name = null, $new_value = null, $use_apilib = DB_BOOL_TRUE)
    {
        try {
            if ($use_apilib == DB_BOOL_TRUE) {
                $this->apilib->edit($entity_name, $id, [$field_name => $new_value]);
            } else {
                $this->db->where($entity_name . '_id', $id)->update($entity_name, [$field_name => $new_value]);
                $this->mycache->clearCache();
            }
            
            if ($this->input->is_ajax_request()) {
                die(json_encode(['status' => 2, 'txt' => null]));
            } else {
                redirect(base_url());
            }
        } catch (Exception $ex) {
            if ($this->input->is_ajax_request()) {
                die(json_encode(['status' => 3, 'txt' => $ex->getMessage()]));
            } else {
                die($ex->getMessage());
            }
        }
    }


    public function ck_uploader()
    {
        $url = './uploads/' . time() . "_" . strtolower(str_replace(' ', '_', $_FILES['upload']['name']));
        //extensive suitability check before doing anything with the file...
        if (($_FILES['upload'] == "none") or (empty($_FILES['upload']['name']))) {
            $message = "No file uploaded.";
        } elseif ($_FILES['upload']["size"] == 0) {
            $message = "The file is of zero length.";
        } elseif (($_FILES['upload']["type"] != "image/pjpeg") and ($_FILES['upload']["type"] != "image/jpeg") and ($_FILES['upload']["type"] != "image/png")) {
            $message = "The image must be in either JPG or PNG format. Please upload a JPG or PNG instead.";
        } elseif (!is_uploaded_file($_FILES['upload']["tmp_name"])) {
            $message = "You may be attempting to hack our server. We're on to you; expect a knock on the door sometime soon.";
        } else {
            $message = "";
            $move = @move_uploaded_file($_FILES['upload']['tmp_name'], $url);
            if (!$move) {
                $message = "Error moving uploaded file. Check the script is granted Read/Write/Modify permissions.";
            }
            $url = str_replace("./", base_url_admin(), $url);
        }

        $funcNum = $_GET['CKEditorFuncNum'];
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
    }

    public function multi_upload_async($field_id)
    {
        $field = $this->datab->get_field($field_id);

        $old_file_data = $_FILES['file'];
        unset($_FILES['file']);
        $_FILES[$field['fields_name']] = $old_file_data;

        usleep(100);

        //A questo punto, devo valutare se il field ha una tabella collegata ed è di tipo INT (forse questo non serve o se invece è di tipo JSON e allora chissene, va bene così (tanto mi salvo il json con tutte le info sul file

        if ($field['fields_ref']) { //Se ha una relazione collegata...
            $relations = $this->db->where('relations_name', $field['fields_ref'])->get('relations');

            if ($relations->num_rows() == 0) { //Allora il campo non punta a una relazione ma a una tabella diretta
                //Cerco allora la tabella

                $file_table = $field['fields_ref'];
                $entity_data = $this->datab->get_entity_by_name($file_table);

                //Cerco il campo file e lo uso per inserire
                $field_insert = false;
                foreach ($entity_data['fields'] as $_field) {
                    if (
                        in_array(
                            $_field['fields_draw_html_type'],
                            ['upload_image', 'upload', 'single_upload']
                        )
                    ) {
                        $field_insert = $_field;
                    }
                }
                if (!$field_insert) {
                    //debug($file_table);
                    echo json_encode(['status' => 0, 'txt' => "Entity '$file_table' don't have any field of type upload_image or upload)!"]);
                    exit;
                }
                //debug($_FILES, true);
                $_FILES[$field_insert['fields_name']] = $_FILES[$field['fields_name']];
                unset($_FILES[$field['fields_name']]);

                $data = $this->apilib->create($field['fields_ref'], [], true);
                //debug($data);
                echo json_encode(['status' => 1, 'file' => $data[$field['fields_ref'] . '_id']]);
            } else {
                $relation = $relations->row();
                //Verifico che effettivamente il campo sia di una tabella presente nella relazione
                if (!in_array($field['entity_name'], [$relation->relations_table_1, $relation->relations_table_2])) {
                    echo json_encode(['status' => 0, 'txt' => "Entity '{$field['entity_name']}' is not one of the tables of the relation '{$field['fields_ref']}' ('{$relation->relations_table_1}' or '{$relation->relations_table_2}')!"]);
                    exit;
                }

                //Se invece è presente, procedo a inserire il file nella seconda tabella
                $file_table = ($field['entity_name'] == $relation->relations_table_1) ? $relation->relations_table_2 : $relation->relations_table_1;
                $entity_data = $this->datab->get_entity_by_name($file_table);

                //Cerco il campo file e lo uso per inserire
                $field_insert = false;

                foreach ($entity_data['fields'] as $_field) {
                    if (in_array($_field['fields_draw_html_type'], ['upload_image', 'upload', 'single_upload'])) {
                        $field_insert = $_field;
                    }
                }

                if (!$field_insert) {
                    echo json_encode(['status' => 0, 'txt' => "Entity '$file_table' don't have any field of type upload_image or upload or single_upload)!"]);
                    exit;
                }
                //Change key of files to be inserted in the other entity
                unset($_FILES[$field['fields_name']]);
                $_FILES[$field_insert['fields_name']] = $old_file_data;
                
                $id = $this->apilib->create($file_table, [], false);
                echo json_encode(['status' => 1, 'file' => $id]);
            }
        } else {
            $ext = pathinfo($_FILES[$field['fields_name']]['name'], PATHINFO_EXTENSION);
            $filename = md5(time() . $_FILES[$field['fields_name']]['name']) . '.' . $ext;
            $uploadDepthLevel = defined('UPLOAD_DEPTH_LEVEL') ? (int) UPLOAD_DEPTH_LEVEL : 0;

            if ($uploadDepthLevel > 0) {
                // Voglio comporre il nome locale in modo che se il nome del file fosse
                // foofoo.jpg la cartella finale sarà: ./uploads/f/o/o/foofoo.jpg
                $localFolder = '';
                for ($i = 0; $i < $uploadDepthLevel; $i++) {
                    // Assumo che le lettere siano tutte alfanumeriche,
                    // alla fine le immagini sono tutte delle hash md5
                    $localFolder .= strtolower(isset($filename[$i]) ? $filename[$i] . DIRECTORY_SEPARATOR : '');
                }

                if (!is_dir(FCPATH . 'uploads/' . $localFolder)) {
                    mkdir(FCPATH . 'uploads/' . $localFolder, DIR_WRITE_MODE, true);
                }
            }

            if (file_exists(FCPATH . 'uploads/' . $localFolder . $filename)) {
                echo json_encode(['status' => 0, 'txt' => "File '{$filename}' already exists!"]);
                exit;
            }

            $this->load->library('upload', array(
                'upload_path' => FCPATH . 'uploads/' . $localFolder,
                'allowed_types' => '*',
                'max_size' => defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10000,
                'encrypt_name' => false,
                'file_name' => $filename,
            )
            );

            $uploaded = $this->upload->do_upload($field['fields_name']);
            if (!$uploaded) {
                debug($this->upload->display_errors());
                die();
            }

            $up_data = $this->upload->data();
            $up_data['original_filename'] = $_FILES[$field['fields_name']]['name'];
            $up_data['path_local'] = $localFolder . $filename;

            echo json_encode(['status' => 1, 'file' => $up_data]);
        }
    }

    //This function is used to remove file from a generic multiupload field. It detects the field type and if it's a JSON field or an INT (pointing to a relation)
    public function removeFileFromMultiUpload($fieldname, $edit_id, $file_id)
    {
        //Get field
        $field = $this->datab->get_field($fieldname);
        if (empty($field['fields_ref'])) { //It's a JSON/LONGTEXT field type
            $entity = $this->datab->get_entity($field['fields_entity_id']);
            $record = $this->apilib->view($entity['entity_name'], $edit_id);
            $json_data = $record[$fieldname];
            $files = json_decode($json_data);
            $new_files = [];

            foreach ($files as $key => $file) {
                $file = (array) $file;
                if ($key != $file_id) {
                    $new_files[] = $file;
                } else {
                    //debug($file, true);
                    @unlink($file['full_path']);
                }
            }
            $json_new = json_encode($new_files);
            $this->apilib->edit($entity['entity_name'], $edit_id, [$fieldname => $json_new]);
        } else { //It's a field pointing to a relation/table
            $relation_name = $field['fields_ref'];
            $field_name = $fieldname;

            $relation = $this->crmentity->getRelationByName($relation_name);

            $attachments_table = $relation['relations_table_2'];
            $field_support_id = $relation['relations_field_2'];

            $record = $this->apilib->searchFirst($relation_name, [$field_support_id => $file_id]);
            //debug($record, true);
            $key = array_keys($record)[0];
            $this->db->where($key, $record[$key])->delete($relation_name);

            $this->apilib->delete($attachments_table, $file_id);
        }
    }

    public function removeFileFromRelation($field_support_id, $field_id, $file_id)
    {
        $field = $this->datab->get_field($field_id);
        $relation_name = $field['fields_ref'];

        $field_name = $field['fields_name'];

        $relation = $this->crmentity->getRelationByName($relation_name);

        $entity_name = substr($field_support_id, 0, -3);
        $record = $this->apilib->searchFirst($relation_name, [$field_support_id => $file_id]);
        $key = array_keys($record)[0];
        $this->db->where($key, $record[$key])->delete($relation_name);

        $this->apilib->delete($entity_name, $file_id);
    }

    public function removeFileFromJson($field_id, $record_id, $key)
    {
        $field = $this->datab->get_field($field_id);
        $field_name = $field['fields_name'];
        $entity_name = $field['entity_name'];

        //Prendo il vecchio json
        $record = $this->apilib->view($entity_name, $record_id);
        $json_decoded = json_decode($record[$field_name]);
        unset($json_decoded[$key]);
        $newrecord = [];
        $newrecord[$field_name] = json_encode($json_decoded);

        $this->apilib->edit($entity_name, $record_id, $newrecord);
    }

    public function changeLanguage()
    {
        $langKey = $this->input->get_post('language');
        if ($this->datab->changeLanguage($langKey)) {
            echo json_encode(['success' => true, 'lang' => $this->datab->getLanguage()]);
        } else {
            echo json_encode(['success' => false, 'lang' => null]);
        }
    }
}
