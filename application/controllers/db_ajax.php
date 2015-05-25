<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Db_ajax extends CI_Controller {

    var $template = array();

    function __construct() {
        parent :: __construct();
    }

    public function index() {
        exit();
    }

    public function save_form($form_id, $edit = false, $value_id = null) {
        if (!$form_id) {
            redirect();
        }

        // Cast di edit a booleano
        $edit = (bool) $edit;



        /*
         * Carica form, entità e relativi campi da db
         */
        $this->db->trans_start();
        $form = $this->db->join('entity', 'forms_entity_id=entity_id', 'left')->get_where('forms', array('forms_id' => $form_id))->row_array();
        $form['fields'] = $this->db
                ->join('fields', 'forms_fields_fields_id=fields_id', 'left')
                ->join('fields_draw', 'fields_draw_fields_id=fields_id', 'left')
                ->where('forms_fields_forms_id', $form_id)->get('forms_fields')
                ->result_array();

        foreach ($form['fields'] as $k => $field) {
            $form['fields'][$k]['validations'] = $this->db->get_where('fields_validation', array('fields_validation_fields_id' => $field['fields_id']))->result_array();
        }

        /**
         * Controlla permessi di scrittura per utente loggato
         */
        $can_write = $this->datab->can_write_entity($form['entity_id']);
        if (!$can_write) {
            $txt = 'Insufficient permissions to write';
            echo ($this->input->is_ajax_request() ? json_encode(array('status' => 0, 'txt' => $txt)) : $txt);
            die();
        }


        /**
         * Upload di eventuali file
         * lo metto qui per prevenire eventuali errori in cui il file è richiesto
         * ed è messo in upload... quindi upload prima di validation
         */
        $files_fields = array_keys($_FILES);
        if (!empty($files_fields)) {
            $this->load->library('upload', array(
                'upload_path' => './uploads/',
                'allowed_types' => '*', //non possiamo fare assunzioni sulla natura del file
                'max_size' => 50000,
                'encrypt_name' => TRUE,
            ));

            foreach ($files_fields as $field_name) {
                $file_array = $_FILES[$field_name];
                if (empty($file_array) || !$file_array['name']) {
                    continue;
                }
                if (!$this->upload->do_upload($field_name)) {
                    /* Errore upload */
                    die(json_encode(array('status' => 0, 'txt' => $this->upload->display_errors())));
                } else {
                    /* Upload ok */
                    $up_data = $this->upload->data();
                    $_POST[$field_name] = $up_data['file_name'];
                }
            }
        }



        /**
         * Validazione
         */
        $rules = array();
        $rules_date_before = array();
        foreach ($form['fields'] as $field) {
            $rule = array();
            if ($field['fields_required'] === 't' && !$field['fields_default'] && ($field['fields_draw_html_type'] !== 'input_password' || !$edit)) {
                // Una password è required sse sto creando un nuovo record
                // e inoltre non è necessario controllare il required se ho un default value
                $rule[] = 'required';
            }

            foreach ($field['validations'] as $validation) {
                switch ($validation['fields_validation_type']) {
                    case 'valid_email':         case 'valid_emails':
                    case 'integer':             case 'numeric':
                    case 'is_natural':          case 'is_natural_no_zero':
                    case 'alpha':               case 'alpha_numeric':
                    case 'alpha_dash':
                        //Le validazioni che non richiedono parametri particolari
                        $rule[] = $validation['fields_validation_type'];
                        break;

                    case 'decimal':         case 'is_unique':
                    case 'min_length':      case 'max_length':
                    case 'exact_length':    case 'greater_than':
                    case 'less_than':
                        //Le validazioni che hanno parametri semplici
                        if ($validation['fields_validation_type'] === 'is_unique' && $edit) {
                            $num = $this->db->where($field['fields_name'], $this->input->post($field['fields_name']))
                                    ->where("{$form['entity_name']}_id <>", $value_id)
                                    ->count_all_results($form['entity_name']);

                            if ($num > 0) {
                                echo json_encode(array('status' => isset($form['forms_response_error']) ? $form['forms_response_error'] : 0, 'txt' => "Il campo {$field['fields_draw_label']} deve essere univoco"));
                                die();
                            }
                        } else {
                            $rule[] = "{$validation['fields_validation_type']}[{$validation['fields_validation_extra']}]";
                        }
                        break;

                    case 'date_before':
                        $rules_date_before[] = array('before' => $field['fields_name'], 'after' => $validation['fields_validation_extra'], 'message' => ($validation['fields_validation_message'] ? $validation['fields_validation_message'] : NULL));
                        break;

                    case 'date_after':
                        $rules_date_before[] = array('before' => $validation['fields_validation_extra'], 'after' => $field['fields_name'], 'message' => ($validation['fields_validation_message'] ? $validation['fields_validation_message'] : NULL));
                        break;
                }
            }

            if (!empty($rule)) {
                $rules[] = array('field' => $field['fields_name'], 'label' => $field['fields_draw_label'], 'rules' => implode('|', $rule));
            }
        }


        /**
         * Eseguo il process di pre-validation
         */
        $_predata = $_POST;
        try {
            $processed_predata = $this->datab->run_post_process($form['entity_id'], $edit ? 'pre-validation-update' : 'pre-validation-insert', array('post' => $_predata, 'value_id' => $value_id));
        } catch (Exception $ex) {
            die(json_encode(array('status' => isset($form['forms_response_error']) ? $form['forms_response_error'] : 0, 'txt' => $ex->getMessage())));
        }

        if (isset($processed_predata['post'])) {
            // Metto i dati processati nel post
            $_POST = $processed_predata['post'];
        }



        if (!empty($rules)) {
            $this->load->library('form_validation');
            $this->form_validation->set_rules($rules);
            if (!$this->form_validation->run()) {
                echo json_encode(array('status' => isset($form['forms_response_error']) ? $form['forms_response_error'] : 0, 'txt' => validation_errors()));
                die();
            }
        }

        $dati = $_POST;


        /**
         * Elabora i dati prima del salvataggio in base a fields_draw_html_type e tipo
         * es:
         *      password => md5($data['password'])
         */
        $post_process_relations = array();
        foreach ($form['fields'] as $field) {

            $sql_type = strtoupper($field['fields_type']);
            $html_type = $field['fields_draw_html_type'];

            // Se non esiste il campo allora vuol dire che non era un required,
            // ma comunque stava nel form. Vuol dire che deve essere inserito vuoto
            if (!array_key_exists($field['fields_name'], $dati)) {
                $dati[$field['fields_name']] = NULL;
            }

            // Colgo l'occasione per vedere se ci sono field che si riferiscono
            // a relazioni - questo è un passaggio che devo fare ora perché i
            // vari controlli sui campi fallirebbero dato che ho un array.
            // Prima la condizione richiedeva che ci fosse un REF e che fosse
            // SETTATO il campo dell'eventuale relazione
            // ----
            // Ma questo era scorretto perché se io mi sto ciclando tutti i
            // campi del form e mi trovo una relazione, allora mi aspetto anche
            // che questo form contenga un qualche tipo di multiselect e che se
            // questo campo non viene effettivamente passato, allora vuol dire
            // che devo eliminare tutte le eventuali relazioni (ciò vale per la
            // modifica)
            if ($field['fields_ref']) {
                /**
                 * in realtà il field ref dovrebbe puntare alla tabella pivot non alla tabella con cui è relazionata
                 * ad esempio ho aziende <-> tags
                 * il field ref di aziende non dovrebbe puntare a tags, ma ad aziende_tags (il nome della relazione).
                 * ---
                 * Per mantenere la retrocompatibilità vengono cercate entrambe le varianti
                 */
                $dataToInsert = isset($dati[$field['fields_name']]) ? $dati[$field['fields_name']] : array();
                if (is_array($dataToInsert)) {
                    $relations = $this->db->where_in('relations_name', array($form['entity_name'] . '_' . $field['fields_ref'], $field['fields_ref']))->get('relations');
                    if ($relations->num_rows() > 0) {
                        $relation = $relations->row();
                        $post_process_relations[] = array(
                            'entity' => $relation->relations_name,
                            'relations_field_1' => $relation->relations_field_1,
                            'relations_field_2' => $relation->relations_field_2,
                            'value' => $dataToInsert
                        );
                        unset($dati[$field['fields_name']]);
                        continue;
                    } elseif ($dataToInsert && in_array($sql_type, array('VARCHAR', 'TEXT'))) {
                        $dati[$field['fields_name']] = implode(',', $dataToInsert);
                    }
                }
            }


            switch ($html_type) {
                case 'date':
                    if (empty($dati[$field['fields_name']])) {
                        $dati[$field['fields_name']] = null;
                    } else {
                        // Il campo non era vuoto, quindi valido la stringa e se
                        // è vuota allora vuol dire che il formato era sbagliato
                        $dati[$field['fields_name']] = date_toDbFormat($dati[$field['fields_name']]);
                        if (!$dati[$field['fields_name']]) {
                            echo json_encode(array('status' => 0, 'txt' => $field['fields_draw_label'] . ' non è una data valida'));
                            die();
                        }
                    }
                    break;
                case 'date_time':
                    if (empty($dati[$field['fields_name']])) {
                        $dati[$field['fields_name']] = null;
                    } else {
                        // Vale la stessa nota fatta per il [date]
                        $dati[$field['fields_name']] = dateTime_toDbFormat($dati[$field['fields_name']]);
                        if (!$dati[$field['fields_name']]) {
                            echo json_encode(array('status' => 0, 'txt' => $field['fields_draw_label'] . ' non è una data valida'));
                            die();
                        }
                    }
                    break;
                case 'wysiwyg':
                    if (isset($dati[$field['fields_name']])) {
                        $dati[$field['fields_name']] = str_replace(base_url_template(), '{base_url}', $dati[$field['fields_name']]);
                    }
                    break;
                case 'input_password':
                    if (empty($dati[$field['fields_name']])) {
                        unset($dati[$field['fields_name']]);
                    } else {
                        $dati[$field['fields_name']] = md5($dati[$field['fields_name']]);
                    }
                    break;
                case 'map':
                    
                    $fieldData = $dati[$field['fields_name']];
                    $exp = array();
                    
                    if (is_array($fieldData)) {
                        if (isset($fieldData['geo'])) {
                            $dati[$field['fields_name']] = $fieldData['geo'];
                            break;
                        } elseif (isset($fieldData['lat']) && isset($fieldData['lng'])) {
                            $exp = array($fieldData['lat'], $fieldData['lng']);
                        } elseif (count($fieldData) > 1) {
                            $exp = array_values($fieldData);
                        } else {
                            unset($dati[$field['fields_name']]);
                            break;
                        }
                    } else {
                        $exp1 = (strpos($fieldData, ';') != false)? explode(';', $fieldData): array();
                        $exp2 = (strpos($fieldData, ',') != false)? explode(',', $fieldData): array();
                        
                        if (count($exp1) === 2) {
                            $exp = $exp1;
                        } elseif (count($exp2) === 2) {
                            $exp = $exp2;
                        } else {
                            unset($dati[$field['fields_name']]);
                            break;
                        }
                    }
                    
                    $dati[$field['fields_name']] = empty($exp)? null: $this->db->query("SELECT ST_GeographyFromText('POINT({$exp[1]} {$exp[0]})') AS geography")->row()->geography;
                    break;

                case 'date_range':
                    if ($sql_type === 'DATERANGE') {
                        $dati[$field['fields_name']] = '[' . implode(',', array_map(function($date) {
                                            return date_toDbFormat($date);
                                        }, explode(' - ', $dati[$field['fields_name']]))) . ']';
                    }
                    break;
            }


            switch ($sql_type) {
                case 'INT':
                    if ($dati[$field['fields_name']] && !is_numeric($dati[$field['fields_name']])) {
                        // FIX: non voglio fare il sanitize dell'input se è già una stringa numerica
                        $dati[$field['fields_name']] = filter_var($dati[$field['fields_name']], FILTER_SANITIZE_NUMBER_INT);
                    }

                    // Se non è stato inserito niente allora cancello il campo 
                    // sperando che sia stato inserito un valore di default...
                    if ($dati[$field['fields_name']] === ''/* && ($field['fields_required'] === 'f' OR $field['fields_default']) */) {
                        unset($dati[$field['fields_name']]);
                    }
                    break;

                case 'FLOAT':
                    $dati[$field['fields_name']] = (float) filter_var(str_replace(',', '.', $dati[$field['fields_name']]), FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    break;

                case 'BOOL':
                    if (isset($dati[$field['fields_name']])) {
                        if (!in_array($dati[$field['fields_name']], array('t', 'f'))) {
                            $dati[$field['fields_name']] = ($dati[$field['fields_name']] ? 't' : 'f');
                        }
                    } else {
                        $dati[$field['fields_name']] = 'f';
                    }
                    break;

                case 'TIMESTAMP WITHOUT TIME ZONE':
                    if (isset($dati[$field['fields_name']]) && !$dati[$field['fields_name']]) {
                        unset($dati[$field['fields_name']]);
                    }
                    break;

                case 'DATERANGE':
                    if (!isValidDateRange($dati[$field['fields_name']])) {
                        echo json_encode(array('status' => 0, 'txt' => $field['fields_draw_label'] . ' non è un date-range nel formato corretto'));
                        die();
                    }
                    break;
            }


            // Se alla fine di tutto sto processo il dato è ancora null E ha un
            // valore di default settato allora, ahimé, tocca unsettare il
            // valore null in quanto prenderò tal valore di default...
            if (array_key_exists($field['fields_name'], $dati) && is_null($dati[$field['fields_name']]) && trim($field['fields_default'])) {
                unset($dati[$field['fields_name']]);
            }
        }


        // Controllo delle regole custom di validazione
        foreach ($rules_date_before as $rule) {
            $before = $rule['before'];
            $after = $rule['after'];
            $message = $rule['message'];

            if (isset($dati[$before]) && isset($dati[$after]) && strtotime($dati[$after]) <= strtotime($dati[$before])) {
                if (!$message) {
                    $message = "La data di inizio dev'essere antecedente alla data di fine";
                }

                echo json_encode(array('status' => isset($form['forms_response_error']) ? $form['forms_response_error'] : 0, 'txt' => $message));
                die();
            }
        }


        /**
         * Eseguo il process di pre-action
         */
        try {
            $processed_data = $this->datab->run_post_process($form['entity_id'], $edit ? 'pre-update' : 'pre-insert', array('post' => $dati, 'value_id' => $value_id));
        } catch (Exception $ex) {
            die(json_encode(array('status' => isset($form['forms_response_error']) ? $form['forms_response_error'] : 0, 'txt' => $ex->getMessage())));
        }

        if (isset($processed_data['post'])) {
            // Metto i dati processati nell'array da inserire su db
            $dati = $processed_data['post'];
        }


        // Inserisco i dati
        $old_data = array();
        if ($form['forms_one_record'] == 'f') {
            if ($edit == TRUE) {

                // Preserve old data
                $old_data = $this->db->get_where($form['entity_name'], array($form['entity_name'] . "_id" => $value_id))->row_array();

                // Insert new data
                $insert_id = $value_id;
                if (!empty($dati)) {
                    $x = $this->db->update($form['entity_name'], $dati, array($form['entity_name'] . "_id" => $value_id));
                } else {
                    $x = TRUE;
                }
            } else {
                $this->db->prepare_data($dati);
                $x = $this->db->insert($form['entity_name'], $dati);
                $insert_id = $this->db->insert_id();
            }
        } else {
            if ($edit == TRUE) {
                $old_data = $this->db->get($form['entity_name'])->row_array();
            }
            $this->db->truncate($form['entity_name']);
            $x = $this->db->insert($form['entity_name'], $dati);
            $insert_id = $this->db->insert_id();
        }

        if (!$x) {
            // Se non è andata a buon fine faccio rollback ora senza proseguire
            $this->db->trans_rollback();
            echo json_encode(array('status' => 0, 'txt' => ($edit ? 'Si è verificato un errore imprevisto durante la modifica dei dati' : 'Si è verificato un errore imprevisto durante il salvataggio dei dati')));
            return;
        }

        // Salvo i dati avendo l'id
        if (count($post_process_relations) > 0) {
            foreach ($post_process_relations as $relation) {
                /* ==========================================================
                 * Prima di inserire i dati nella relazione faccio un delete 
                 * dei record con relations_field_1 uguale al mio insert id
                 * che corrispondono ai valori vecchi.
                 * --
                 * Nel caso di una modifica si eliminano valori vecchi
                 * nel caso di un inserimento non si elimina niente.
                 * Corretto?
                 * ========================================================= */
                $this->db->delete($relation['entity'], array($relation['relations_field_1'] => $insert_id));
                if (is_array($relation['value'])) {
                    foreach ($relation['value'] as $value) {
                        $data_rel = array(
                            $relation['relations_field_1'] => $insert_id,
                            $relation['relations_field_2'] => $value,
                        );
                        $this->db->insert($relation['entity'], $data_rel);
                    }
                }
            }
        }
        
        // Operazione eseguita con successo ==> esegui i post process
        
        $new_data = $this->db->get_where($form['entity_name'], array($form['entity_name'] . "_id" => $insert_id))->row_array();
        
        try {
            call_user_func_array(array($this->datab, 'run_post_process'), array(
                $form['entity_id'],
                $edit? 'update': 'insert',
                $edit? array('new' => $new_data, 'old' => $old_data, 'diff' => array_diff_assoc($new_data, $old_data), 'value_id' => $insert_id): $new_data
            ));
        } catch (Exception $ex) {
            $this->db->trans_rollback();
            die(json_encode(array('status' => isset($form['forms_response_error']) ? $form['forms_response_error'] : 0, 'txt' => $ex->getMessage())));
        }



        $status = (isset($form['forms_success_status']) && is_numeric($form['forms_success_status'])) ? $form['forms_success_status'] : 5;
        $message = (empty($form['forms_success_message']) ? ($edit ? 'Modifiche salvate correttamente' : 'Salvataggio effettuato con successo') : $form['forms_success_message']);

        if ($status == 1 && filter_var($message, FILTER_VALIDATE_URL) === false) {
            $message = base_url($message);
        }

        // Replace variabili dentro al message
        $replaceFrom = array_map(function($key) {
            return '{' . $key . '}';
        }, array_keys($dati));
        $replaceFrom[] = '{value_id}';

        $replaceTo = array_values($dati);
        $replaceTo[] = $insert_id;
        $committed = $this->db->trans_complete();

        if ($committed) {
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
            $output['date'] = dateFormat($output['date']);
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

            $where_data[$form['forms']['forms_filter_session_key']] = $conditions;
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

    public function save_permissions() {

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

    public function save_views_permissions() {

        $viewsAccess = $this->input->post('view');
        
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
            $txt = 'Insufficient permissions to write';
            echo ($this->input->is_ajax_request() ? json_encode(array('status' => 0, 'txt' => $txt)) : $txt);
            die();
        }

        $this->datab->run_post_process($entity['entity_id'], 'pre-delete', array('id' => $id));
        $this->db->where($entity_name . '_id', $id)->delete($entity_name);
        $this->datab->run_post_process($entity['entity_id'], 'delete', array('id' => $id));

        if ($this->input->is_ajax_request()) {
            echo json_encode(array('status' => 2));     //Refresh se richiesta fatta da ajax
        } else {
            /*
             * Torno alla home se la richiesta è stata fatta normalmente
             * non so da quale url viene fatta la richiesta
             * in ogni caso essendo il controller db_ajax non dovrebbe mai essere
             * fatta una richiesta non ajax...
             */
            redirect(base_url());
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
                $this->db->update($table, array($column => $new_value), array($table . '_id' => $id));
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
        $url = './uploads/' . time() . "_" . $_FILES['upload']['name'];
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

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */