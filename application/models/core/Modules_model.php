<?php

/**
 * OpenBuilder Client Core model
 *
 */
class Modules_model extends CI_Model
{
    var $temp_folder;

    public function __construct()
    {
        parent::__construct();


        //$this->load->model('utils', 'utils_base');

        // Check because Module manager can work without modules_manager_settings! (like when che project is new)
        $check_settings = $this->crmentity->entityExists('modules_manager_settings');

        if ($check_settings) {
            $this->settings = $this->apilib->searchFirst('modules_manager_settings');
            $this->_license_token = $this->settings['modules_manager_settings_license_token'];
        } else {
            $this->settings = "";
            $this->_license_token = null;
        }
        
        $this->temp_folder = FCPATH . 'uploads/tmp/';
        
        $this->_project_id = (defined('ADMIN_PROJECT') && !empty(ADMIN_PROJECT)) ? ADMIN_PROJECT : 0;
        
        $this->load->model('entities');
    }

    public function installModule($identifier, $update_repository_url = null)
    {
        $return = $this->updateModule($identifier, $update_repository_url, false);
        if ($return) {
            $module = $this->getModuleRepositoryData($identifier, $update_repository_url, $this->_project_id, $this->_license_token);
            $this->db->insert(
                'modules',
                array(
                    'modules_name' => $module['modules_repository_name'],
                    'modules_version' => $module['modules_repository_version'],
                    'modules_identifier' => $module['modules_repository_identifier'],
                    'modules_version_code' => $module['modules_repository_version_code'],
                    'modules_description' => $module['modules_repository_description'],
                    'modules_notification_message' => $module['modules_repository_notification_message'],
                    'modules_created_by_user' => $module['modules_repository_created_by_user'],
                    'modules_thumbnail' => $module['modules_repository_thumbnail'],
                    'modules_version_date' => $module['modules_repository_last_update'],
                    'modules_core' => $module['modules_repository_core'],
                    'modules_auto_update' => DB_BOOL_TRUE,
                )
            );
        }
        return $return;
    }


    /**
     * From 2.3.9 Core method to update client
     * @param string $module_identifier Identifier of the module to be updated
     * 
     * @throws Exception
     * @return bool|string
     */
    public function updateModule($identifier, $update_repository_url = null, $is_update = true)
    {
        if ($update_repository_url === null) {
            $update_repository_url = defined('OPENBUILDER_ADMIN_BASEURL') ? OPENBUILDER_ADMIN_BASEURL : null;
        }
        if (!$update_repository_url) {
            my_log('error', 'No module repository url defined', 'update');
            return false;
        }
        $module = $this->getModuleRepositoryData($identifier, $update_repository_url, $this->_project_id, $this->_license_token);
        //debug($module, true);
        // Check Min. client version
        if (!empty($module['modules_repository_min_client_version']) && version_compare($module['modules_repository_min_client_version'], VERSION, '>')) {
            echo_log('error', "{$module['modules_repository_name']} requires at least '{$module['modules_repository_min_client_version']}' version of client (current is '" . VERSION . "')");
            return false;

        }

        $get_module_info_url = $update_repository_url . '/public/client/download_module/' . $identifier;

        // Scarica il contenuto JSON dall'URL specificato
        $zip_content = file_get_contents($get_module_info_url);
        $zip = new ZipArchive;

        createFolderRecursive($this->temp_folder);
        $unzip_destination_folder = "{$this->temp_folder}/$identifier/";

        //Devo crearmi un file temporaneo perchè la libreria zip archive non ha un metodo openFromString
        $tmp_zip_file = "{$this->temp_folder}{$identifier}.zip";
        file_put_contents($tmp_zip_file, $zip_content);
        if ($zip->open($tmp_zip_file) === TRUE) {

            $zip->extractTo($unzip_destination_folder);
            $zip->close();

            unlink($tmp_zip_file);

            if (file_exists("{$unzip_destination_folder}/json/data.json")) {

                $content = file_get_contents("{$unzip_destination_folder}/json/data.json");

                //TODO: Check if module depends on other modules

                //Process json informations
                $this->json_process($content, $identifier, $is_update);

                //Copy module files on modules folder
                $this->copy_files($identifier);

                //Run migrations
                $old_module = $this->db->where('modules_identifier', $module['modules_repository_identifier'])->get('modules')->row_array();
                if ($old_module) {
                    $old_version = $old_module['modules_version'];
                } else {
                    $old_version = '0';
                }



                $this->run_migrations($identifier, $old_version, $module['modules_repository_version']);

                //Update database module version
                $this->db->where('modules_identifier', $identifier)->update('modules', [
                    'modules_name' => $module['modules_repository_name'],
                    'modules_version' => $module['modules_repository_version'],
                    'modules_description' => $module['modules_repository_description'],
                    'modules_notification_message' => $module['modules_repository_notification_message'],
                    'modules_created_by_user' => $module['modules_repository_created_by_user'],
                    'modules_thumbnail' => $module['modules_repository_thumbnail'],
                    'modules_version_code' => $module['modules_repository_version_code'],
                    'modules_version_date' => $module['modules_repository_last_update'],
                    'modules_core' => $module['modules_repository_core'],
                ]);


                //Rimuovo eventuali moduli duplicati
                while ($this->db->where('modules_identifier', $identifier)->count_all_results('modules') > 1) {
                    $duplicate = $this->db->where('modules_identifier', $identifier)->get('modules')->row_array();
                    $this->db->where('modules_id', $duplicate['modules_id'])->delete('modules');
                    $this->mycache->clearCache();
                }

                //Check if every is ok (checksum, module version code, ecc...)
                echo_log('debug', 'TODO: final checks...');
                deleteDirRecursive($unzip_destination_folder);

                $this->mycache->clearCache();
                return $module;
            } else {

                deleteDirRecursive($unzip_destination_folder);
                echo_log('error', "Missing data.json in module $identifier");

                return false;
            }

        } else {

            echo_log('error', "Unable to open zip file of module '{$module['modules_repository_name']}'!");
            return false;
        }



    }
    public function getModuleRepositoryData($module_identifier, $update_repository_url = null, $project_id = 0, $token = null)
    {
        if ($update_repository_url === null) {
            $update_repository_url = defined('OPENBUILDER_ADMIN_BASEURL') ? OPENBUILDER_ADMIN_BASEURL : null;
        }
        if (!$update_repository_url) {
            my_log('error', 'No module repository url defined', 'update');
            return false;
        }

        if (!$project_id || $project_id == 0) {
            $project_id = $this->_project_id;
        }

        if (!$token) {
            $token = $this->_license_token;
        }

        // Check module version
        $module = $this->db->get_where('modules', ['modules_identifier' => $module_identifier])->row_array();

        if (!empty($module)) {
            $module_installed_version = $module['modules_version'];
        } else {
            $module_installed_version = "";
        }

        //Fare curl ad admin o openbuilder?
        $get_module_info_url = $update_repository_url . '/public/client/get_module_info/' . $module_identifier . '/' . $project_id . '/' . $token . '/' . $module_installed_version;

        //debug($get_module_info_url);
        // Scarica il contenuto JSON dall'URL specificato
        $json = file_get_contents($get_module_info_url);

        // Decodifica il contenuto JSON in un oggetto PHP
        $data = json_decode($json, true);

        // Verifica se la decodifica è avvenuta correttamente
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        } else {
            // Se la decodifica non è avvenuta correttamente, mostra un errore
            return false;
        }

    }
    public function run_migrations($identifier, $old_version, $new_version)
    {
        $migration_dir = APPPATH . "modules/$identifier/migrations";
        if (file_exists($migration_dir)) {
            $files = scandir($migration_dir);

            foreach ($files as $file) {
                if ($file == 'update_php_code.php') {
                    // Check if exist an update_db file to execute update queries
                    include "$migration_dir/$file";

                    // Sort array from oldest version to newest
                    uksort($updates, 'my_version_compare');

                    foreach ($updates as $key => $value) {

                        $version_compare_old = version_compare($key, $old_version, '>');


                        //if ($version_compare_old || ($key == 0 && $old_version == 0)) { //1 se old è < di key
                        if ($version_compare_old || ($old_version == 0 || $key === '*')) { //Rimosso key == 0 perchè altrimenti esegue infinite volte l'update 0 (che di solito va fatto solo all'install)
                            foreach ($value as $key_type => $code) {
                                if ($key_type == 'eval') {
                                    eval($code);
                                } elseif ($key_type == 'include') {
                                    if (is_array($code)) {
                                        foreach ($code as $file_to_include) {
                                            $file_migration = "$migration_dir/$file_to_include";
                                            if (file_exists($file_migration)) {


                                                //debug("Eseguo migration $file_migration",true);

                                                include $file_migration;
                                            } else {
                                                echo_log('error', "Migration file {$file_migration} missing!");
                                            }
                                        }
                                    } else {
                                        $file_migration = APPPATH . 'migrations/' . $code;

                                        if (file_exists($file_migration)) {
                                            include $file_migration;
                                        } else {
                                            echo_log('error', "Migration file {$file_migration} missing!");
                                        }
                                    }
                                }
                            }
                        } else { //0 se uguale, -1 se old > key
                            //Vuol dire che gli ho già eseguiti, quindi skippo
                            //echo("$key version already run.");
                            continue;
                        }
                    }
                }
            }
            $this->mycache->clearCache();
            return true;
        } else {
            $this->mycache->clearCache();
            return true;
        }
    }
    private function copy_files($identifier)
    {
        $source_path = "{$this->temp_folder}/$identifier/";
        recurse_copy($source_path, APPPATH . "modules/{$identifier}/");
    }
    private function json_process($content, $module, $is_update)
    {
        $json = json_decode($content, true);

        //debug($json['entities'], true);

        $uninstall = false;
        $conditions = [];
        try {
            //Mi tengo in una variabile l'elenco dei nomi delle entità di questo modulo (mi serve dopo)

            $identifier = ($json['module_identifier']) ?: false;

            if (!$identifier) {
                echo_log('error', "Missing module identifier");
                throw new Exception("Modules identifier missing!");

            }

            $this->mycache->clearCache();

            $all_fields_map = [];

            //Creo tutte le entità
            $entities_id_map = [];
            my_log('debug', "Module install: start entities creation", 'update');




            if (empty($json['entities'])) {
                $json['entities'] = [];
            }
            $total = count($json['entities']);
            $c = 0;
            foreach ($json['entities'] as $old_entity_id => $entity) {
                $c++;
                progress($c, $total, 'entities');

                $entity_action_fields = json_decode($entity['entity_action_fields'], true);

                my_log('debug', "Module install: creating '{$entity['entity_name']}' (type: '{$entity['entity_type']}')", 'update');
                //debug($entity,true);

                if ($entity['entity_type'] != ENTITY_TYPE_RELATION) {
                    $data = [
                        'entity_name' => $entity['entity_name'],
                        'entity_type' => $entity['entity_type'],
                        'entity_searchable' => $entity['entity_searchable'],
                        'entity_action_fields' => $entity['entity_action_fields'],
                        'entity_visible' => $entity['entity_visible'],
                        'entity_module' => $entity['entity_module'],
                        'entity_preview_base' => $entity['entity_preview_base'],
                    ];
                    $entity_exists = $this->entities->entity_exists($entity['entity_name']);
                    if (!$entity_exists) {
                        try {
                            $new_entity_id = $this->entities->new_entity($data, false); //Il false evita la creazione di grid e form default (li inserisco dopo in base al json)
                        } catch (RuntimeException $e) {
                            my_log('error', "Entity '{$entity['entity_name']}' creation failed! (ex: {$e->getMessage()})", 'update');
                            continue;
                        }

                        my_log('debug', "Module install: '{$entity['entity_name']}' created", 'update');
                    } else {
                        my_log('debug', "Module install: entity '{$entity['entity_name']}' already present", 'update');

                        $this->db->where('entity_id', $entity_exists['entity_id'])->update('entity', $data);

                        $new_entity_id = $entity_exists['entity_id'];
                    }

                    //Per ogni entità creata mi ricreo l'array di mappatura vecchio_id=>nuovo_id, mi servirà dopo...
                    $entities_id_map[$old_entity_id] = $new_entity_id;
                }

                //                unset($entity_action_fields['create_time']);
                //                unset($entity_action_fields['update_time']);
            }
            my_log('debug', "Module install: end entities creation", 'update');

            //Adesso posso creare le relazioni...
            my_log('debug', "Module install: start relations creation", 'update');
            $total = count($json['entities']);
            $c = 0;
            foreach ($json['entities'] as $old_entity_id => $entity) {
                $c++;
                progress($c, $total, 'relations');
                $this->db->cache_delete_all();
                $this->db->data_cache = [];
                if ($entity['entity_type'] == ENTITY_TYPE_RELATION && !empty($entity['relation'])) {
                    //debug($entity);
                    $relation = $entity['relation'];
                    $rel_name = $relation['relations_name'];
                    $table1 = $relation['relations_table_1'];
                    $table2 = $relation['relations_table_2'];
                    $rel_type = $relation['relations_type'];
                    unset($relation['relations_id']);

                    if ($this->db->where('relations_name', $rel_name)->get('relations')->num_rows() == 0) {
                        $this->entities->relation($rel_name, $table1, $table2, $rel_type);
                    } elseif (!$this->entities->entity_exists($entity['entity_name'])) { //20230407 - MP - E' capitato che fosse presente il record in relations ma la tabella non esistesse... oin questo caso forzo una nuova creazione.
                        $this->db->where('relations_name', $rel_name)->delete('relations');
                        $this->entities->relation($rel_name, $table1, $table2, $rel_type);
                    }

                    $entity_exists = $this->entities->entity_exists($entity['entity_name']);
                    $new_entity_id = $entity_exists['entity_id'];

                    //Per ogni entità creata mi ricreo l'array di mappatura vecchio_id=>nuovo_id, mi servirà dopo...
                    $entities_id_map[$old_entity_id] = $new_entity_id;
                }
                //
            }
            my_log('debug', "Module install: end relation creation", 'update');

            //Creo tutti i campi (non dovrebbero esserci problemi col field ref in quanto le entità son già tutte create
            $fields_id_map = [];

            // Check if ref is a login entity
            $login_entity = $this->entities->get_login_entity();
            $total = 0;
            foreach ($json['entities'] as $old_entity_id => $entity) {
                $total += count($entity['fields']);
            }
            $c = 0;

            foreach ($json['entities'] as $old_entity_id => $entity) {
                $this->db->cache_delete_all();
                $this->db->data_cache = [];
                //                if ($entity['entity_name'] == 'settings') {
                //                    continue;
                //                }

                $return = [];
                //$entity['fields'] = $this->entities->changeMySqlTypes($entity['fields']);

                if (empty($entities_id_map[$old_entity_id])) {
                    continue;
                }

                my_log('debug', "Module install: start fields creation for entity '{$entity['entity_name']}'", 'update');
                foreach ($entity['fields'] as $field) {


                    $c++;
                    progress($c, $total, 'fields');
                    $this->db->cache_delete_all();
                    $this->db->data_cache = [];
                    $data = [
                        'entity_id' => $entities_id_map[$old_entity_id],
                        'fields' => [],
                    ];

                    //Verifico se il field esiste già o no.
                    $current_field = $this->entities->get_fields($data['entity_id'], [$field['fields_name']]);
                    $field['fields_name'] = str_ireplace($entity['entity_name'] . '_', '', $field['fields_name']);
                    //Se il campo è id (nelle support table il campo è presente anche nella tabella fields, cosa che non accade per le entity standard), lo ignoro perchè l'ho già creato creando l'entità...
                    if ($field['fields_name'] == 'id' && $entity['entity_type'] != ENTITY_TYPE_SUPPORT_TABLE) {
                        continue;
                    }

                    if (!empty($field['fields_ref']) && $field['fields_ref'] == '{login_entity}') {
                        $field['fields_ref'] = $login_entity['entity_name'];
                    }

                    //Verifico se il campo fields_ref non contenga per caso un entità non presente nel modulo stesso. In tal caso svuoto.
                    if (!empty($field['fields_ref']) && !$this->entities->entity_exists($field['fields_ref'])) {
                        $field['fields_ref'] = null;
                    }

                    unset($field['fields_id']);

                    if (!$current_field) {
                        //debug($field,true);
                        $data['fields'][] = $field;
                        $return = array_merge($return, $this->entities->addFields($data, true));
                        //debug($return);
                    } else {
                        //Se esiste già, mi preparo per fare eventuale update...
                        //debug($current_field,true);
                        $single_field_update_data = [
                            'entity_id' => $entities_id_map[$old_entity_id],
                            'fields' => [$field],
                            'fields_id' => $current_field[0]['fields_id'],
                        ];

                        $return = array_merge($return, $this->entities->addFields($single_field_update_data, true));
                        //debug($return);
                    }
                }
                my_log('debug', "Module install: end fields creation", 'update');

                foreach ($return as $field_name => $new_field_id) {
                    foreach ($entity['fields'] as $field) {
                        if ($field['fields_name'] == $field_name) {
                            $fields_id_map[$field['fields_id']] = $new_field_id;
                            break;
                        }
                    }
                }
                $fv_count = count($entity['fields_draw']);
                $fvc = 0;
                //Una volta che ho i fields creati, creo le fields_validations....
                foreach ($entity['fields_validation'] as $fv) {
                    $fvc++;
                    progress($fvc, $fv_count, 'fields validation');
                    unset($fv['fields_draw_id']);
                    unset($fv['fields_validation_id']);
                    $fv['fields_validation_fields_id'] = $fields_id_map[$fv['fields_validation_fields_id']];
                    $validation_exists = $this->db->get_where('fields_validation', [
                        'fields_validation_fields_id' => $fv['fields_validation_fields_id'],
                        'fields_validation_type' => $fv['fields_validation_type'],
                    ]);

                    if (!$validation_exists->num_rows() >= 1) {
                        //debug($fv);
                        $this->db->insert('fields_validation', $fv);
                    } else {
                        $this->db->where('fields_validation_id', $validation_exists->row()->fields_validation_id)->update('fields_validation', $fv);
                    }
                }

                //Una volta che ho i fields creati, creo le fields_draws....
                $fd_count = count($entity['fields_draw']);
                $fdc = 0;
                foreach ($entity['fields_draw'] as $fd) {
                    $fdc++;
                    progress($fdc, $fd_count, 'fields draw');
                    unset($fd['fields_draw_id']);
                    $fd['fields_draw_fields_id'] = $fields_id_map[$fd['fields_draw_fields_id']];
                    if (empty($fd['fields_draw_fields_id'])) {
                        //debug($fd,true);
                    }
                    $draw_exists = $this->db->get_where('fields_draw', [
                        'fields_draw_fields_id' => $fd['fields_draw_fields_id'],
                    ]);

                    if (!$draw_exists->num_rows() >= 1) {

                        $this->db->insert('fields_draw', $fd);
                    } else {
                        $this->db->where('fields_draw_id', $draw_exists->row()->fields_draw_id)->update('fields_draw', $fd);
                    }
                }
            }

            //Dopo aver creato tutte le entità, posso ricrearmi la mappatura corretta dei fields:
            foreach ($this->db->get('fields')->result_array() as $field) {
                $all_fields_map[$field['fields_name']] = $field['fields_id'];
            }

            //Creo i layouts
            $layouts_id_map = [
                -1 => -1,
                //Fake per non avere un array vuoto (dopo mi serve l'implode in una query)
            ];
            my_log('debug', "Module install: start layouts creation", 'update');
            $total = count($json['layouts']);
            $c = 0;
            foreach ($json['layouts'] as $layout) {

                

                $c++;
                progress($c, $total, 'layouts delete');
                $this->db->cache_delete_all();
                $this->db->data_cache = [];
                if (!$layout || strtolower($layout['layouts_title']) == 'settings') {
                    continue;
                }

                //Rimuovo prima l'eventuale layout già presente (20190207 - MP - Modifica per cancellare solo quelli che non hanno la module key. In questo modo riesco a non creare ogni volta nuovi layouts, ma reciclare quelli esistenti)
                $this->db->query(
                    "DELETE FROM layouts WHERE layouts_module = '$identifier' AND (layouts_module_key IS NULL OR layouts_module_key = '')"
                );
            }
            $c = 0;
            foreach ($json['layouts'] as $key => $layout) {
                $c++;
                progress($c, $total, 'layouts');
                if ($layout == null) {
                    // debug($json['layouts']);
                    // debug($key, true);
                    continue;
                }
                my_log('debug', "Module install: create layout '{$layout['layouts_title']}'", 'update');
                $this->db->cache_delete_all();
                $this->db->data_cache = [];
                $old_layout_id = $layout['layouts_id'];
                if (strtolower($layout['layouts_title']) == 'settings') { //I settings sono un layout/entità speciali... meglio non toccare
                    $layouts_id_map[$old_layout_id] = $this->db->query("SELECT layouts_id FROM layouts WHERE layouts_title LIKE 'settings'")->row()->layouts_id;
                    continue;
                }

                //debug($layout);
                if (!empty($layout['layouts_entity_id'])) {
                    if (!array_key_exists($layout['layouts_entity_id'], $entities_id_map)) {
                        my_log('debug', "Entity '{$layout['layouts_entity_id']}' missing for layout '{$layout['layouts_title']}'. Fix and re-build again.", 'update');
                        $layout['layouts_entity_id'] = null;
                    } else {
                        $layout['layouts_entity_id'] = $entities_id_map[$layout['layouts_entity_id']];
                    }
                }
                //Se il layout non esiste
                //Rimosso filtro modulo: un layout potrebbe già esistere anche se di un altro modulo
                //$layout_exists = $this->db->query("SELECT * FROM layouts WHERE layouts_module = '$identifier' AND layouts_module_key = '{$layout['layouts_module_key']}'");

                $layout_exists = $this->db->query("SELECT * FROM layouts WHERE layouts_module_key = '{$layout['layouts_module_key']}'");

                if ($layout['layouts_module_key'] == 'modulo-hr-layout-532') {
                    //debug($layout,true);
                }

                if (!empty($layout['conditions'])) {
                    //debug($layout, true);
                    $conditions = array_merge($conditions, [$layout['conditions']]);

                }
                unset($layout['conditions']);



                if ($layout_exists->num_rows() == 0) {
                    unset($layout['layouts_id']);
                    $new_layout_id = $this->entities->new_layout($layout);
                    $layouts_id_map[$old_layout_id] = $new_layout_id;
                } else {
                    $existing_layout = $layout_exists->row_array();
                    $this->entities->edit_layout($layout, $existing_layout['layouts_id']);
                    $layouts_id_map[$old_layout_id] = $existing_layout['layouts_id'];
                }

                if ($layout['layouts_title'] == 'Customer Sublayout - Projects') {
                    // debug($layout);
                    // debug($layout_exists->row_array());
                    // debug($layouts_id_map, true);
                }
            }

            my_log('debug', "Module install: end layouts creation", 'update');

            my_log('debug', "Module install: start forms creation", 'update');
            //Creo i forms
            $forms_id_map = [];

            //Le grid hanno anche la module_key.... forse avrebbe senso metterla anche nei form?
            $this->db->query(

                "DELETE FROM forms
                 WHERE
                    forms_entity_id IN (
                        SELECT entity_id FROM entity WHERE (entity_module = '$identifier' OR entity_module LIKE '%,$identifier,%')
                    ) AND forms_id NOT IN (
                        SELECT
                            locked_elements_ref_id
                        FROM
                            locked_elements
                        WHERE
                            locked_elements_type = 'form'
                    ) AND (
                        forms_module_key IS NULL OR forms_module_key = ''
                    )
                    "
            );
            //die($this->db->last_query());

            //            foreach ($json['forms'] as $form) {
            //                //debug("COMMENTATO CODICE CHE TOGLIE I FORMS... CI SARANNO FORM DOPPI O TRIPLI SICURO");
            //                $this->db->query(
            //                    "DELETE FROM forms WHERE forms_entity_id IN (SELECT entity_id FROM entity WHERE entity_module = '$identifier')"
            //                );
            //            }
            $total = count($json['forms']);
            $c = 0;
            foreach ($json['forms'] as $form) {
                $c++;
                progress($c, $total, 'forms');
                //Rimuovo prima l'eventuale form già presente
                $old_form_id = $form['forms_id'];
                unset($form['forms_id']);
                unset($form['fields']);
                $form['forms_entity_id'] = $entities_id_map[$form['forms_entity_id']];
                //die('TODO: NON CANCELLARE MA VERIFICARE SE IL FORM ESISTE GIA\' COME LE GRID!');
                $form_exists = $this->db->query("SELECT * FROM forms WHERE forms_module_key = '{$form['forms_module_key']}'");
                if ($form_exists->num_rows() == 0) {
                    $this->db->insert('forms', $form);
                    $new_form_id = $this->db->insert_id();
                } else {
                    $new_form_id = $form_exists->row()->forms_id;
                    $this->db->where('forms_id', $new_form_id)->update('forms', $form);
                }

                $forms_id_map[$old_form_id] = $new_form_id;

                my_log('debug', "Module install: form {$form['forms_name']} created", 'update');
            }
            $total = 0;
            foreach ($json['forms'] as $form) {
                $total += count($form['fields']);
            }
            $c = 0;
            //I fields dei form li inserisco dopo aver inserito tutti i form (per non rischiare di avere sub_form_id come chiavi esterne di form non ancora creati...
            foreach ($json['forms'] as $form) {
                foreach ($form['fields'] as $field) {
                    if (!empty($field['conditions'])) {
                        $conditions = array_merge($conditions, [$field['conditions']]);
                    }

                    unset($field['conditions']);

                    if ($field['forms_fields_subform_id']) {
                        if (!array_key_exists($field['forms_fields_subform_id'], $forms_id_map)) {
                            //debug($lb);
//debug("ANOMALIA... Form '{$field['forms_fields_subform_id']}' non trovato!", true);
                            $field['forms_fields_subform_id'] = null;

                        } else {
                            $field['forms_fields_subform_id'] = $forms_id_map[$field['forms_fields_subform_id']];
                        }
                    }

                    $c++;
                    progress($c, $total, 'form fields');
                    // replace login entity session placeholder
                    if (strpos($field['forms_fields_default_value'], '{login_entity}') !== false) {
                        $field['forms_fields_default_value'] = str_replace('{login_entity}', $login_entity['entity_name'], $field['forms_fields_default_value']);
                    }

                    if (empty($fields_id_map[$field['forms_fields_fields_id']])) {
                        continue;
                        debug($form);
                        debug($field,true);
                    }

                    $field['forms_fields_fields_id'] = $fields_id_map[$field['forms_fields_fields_id']];
                    $field['forms_fields_forms_id'] = $forms_id_map[$form['forms_id']];


                    $duplicate_forms_fields = $this->db->where(
                        "
                            forms_fields_forms_id = '{$field['forms_fields_forms_id']}'
                            AND
                            forms_fields_fields_id = '{$field['forms_fields_fields_id']}'
                        ",
                        null,
                        false
                    )->join('fields', 'fields_id = forms_fields_fields_id', 'LEFT')->delete('forms_fields');




                    $this->db->insert('forms_fields', $field);
                    //my_log('debug', "Module install: form {$form['forms_name']} - field {$field['forms_fields_fields_id']} created", 'update');
                }
            }

            my_log('debug', "Module install: end forms creation", 'update');

            my_log('debug', "Module install: Start menu creation", 'update');
            //Creo i menu
            $menus_id_map = [];

            $total = count($json['menu']);
            $c = 0;
            // 20230215 - MP - Non rimuovo i menu, ma faccio dopo il controllo if exists
            // foreach ($json['menu'] as $menu) {
            //     $c++;
            //     progress($c, $total, 'menu delete');
            //     if (!$menu['menu_parent']) { //I parent non li cancello, altrimenti perdo anche tutti i figli già dentro...
            //         //debug("COMMENTATO CODICE CHE TOGLIE I MENU... CI SARANNO MENU DOPPI O TRIPLI SICURO");
            //         $this->db->query(
            //             "DELETE FROM menu WHERE menu_module = '{$identifier}'"
            //         );
            //     }
            // }
            $c = 0;
            foreach ($json['menu'] as $menu) {
                $c++;
                progress($c, $total, 'menu creation (step 1)');

                if ($menu['menu_parent']) { //Skippo quelli che hanno parent, li riprocesso dopo
                    continue;
                }
                $old_menu_id = $menu['menu_id'];
                unset($menu['menu_id']);
                if ($menu['menu_layout'] && $menu['menu_layout'] != -2) {
                    if (!array_key_exists($menu['menu_layout'], $layouts_id_map)) {
                        my_log('error', "Layout '{$menu['menu_layout']}' missing for menu '{$menu['menu_label']}'. Fix and re-build again.", 'update');
                    } else {
                        $menu['menu_layout'] = $layouts_id_map[$menu['menu_layout']];
                    }
                }
                if ($menu['menu_form']) {
                    if (!array_key_exists($menu['menu_form'], $forms_id_map)) {
                        my_log('error', "Form '{$menu['menu_form']}' missing for menu '{$menu['menu_label']}'. Fix and re-build again.", 'update');
                    } else {
                        $menu['menu_form'] = $forms_id_map[$menu['menu_form']];
                    }
                }
                //Fix per link custom...
                if (!empty($menu['menu_link'])) {
                    foreach ($layouts_id_map as $lay_id => $new_lay_id) {
                        $menu['menu_link'] = str_replace("layout/$lay_id", "layout/$new_lay_id", $menu['menu_link']);
                    }
                    foreach ($forms_id_map as $f_id => $new_f_id) {
                        $menu['menu_link'] = str_replace("form/$f_id", "form/$new_f_id", $menu['menu_link']);
                    }
                }
                $conditions = array_merge($conditions, [$menu['conditions']]);


                unset($menu['conditions']);

                //Vedo se esiste già un menu parent con questa label. In caso riutilizzo questo senza crearne uno nuovo...
                //$check_menu_exists = $this->db->query("SELECT * FROM menu WHERE menu_label = '{$menu['menu_label']}' AND menu_module = '$identifier'");
                $check_menu_exists = $this->db->query("SELECT * FROM menu WHERE menu_module_key = '{$menu['menu_module_key']}'");



                if ($check_menu_exists->num_rows() > 0) {
                    $menu_esistente = $check_menu_exists->row_array();

                    if ($is_update && ($menu['menu_position'] == 'sidebar')) {
                        //Se sto aggiornando, il menu esiste già e il menu è posizionato in sidebar, unsetto position e parent in modo che se lo sposto per un cliente non venga più sovrascritta la posizione
                        unset($menu['menu_parent']);
                        unset($menu['menu_position']);
                    }

                    $this->db->where('menu_id', $menu_esistente['menu_id'])->update('menu', $menu);
                    $menus_id_map[$old_menu_id] = $menu_esistente['menu_id'];
                } else {
                    $this->db->insert('menu', $menu);
                    $menuid = $this->db->insert_id();
                    $menus_id_map[$old_menu_id] = $menuid;
                }
            }
            //        debug($json['menu']);
            //        debug($menus_id_map);
            $c = 0;
            //Creo i menu
            foreach ($json['menu'] as $menu) {


                $c++;
                progress($c, $total, 'menu creation (step 2)');
                if (!$menu['menu_parent']) { //Skippo quelli che non hanno parent
                    continue;
                }
                unset($menu['menu_id']);

                //Fix per link custom...
                if (!empty($menu['menu_link'])) {
                    foreach ($layouts_id_map as $lay_id => $new_lay_id) {
                        $menu['menu_link'] = str_replace("layout/$lay_id", "layout/$new_lay_id", $menu['menu_link']);
                    }
                    foreach ($forms_id_map as $f_id => $new_f_id) {
                        $menu['menu_link'] = str_replace("form/$f_id", "layout/$new_f_id", $menu['menu_link']);
                    }
                }
                if ($menu['menu_layout'] && $menu['menu_layout'] != -2) {
                    if (!array_key_exists($menu['menu_layout'], $layouts_id_map)) {
                        my_log('debug', "Layout '{$menu['menu_layout']}' missing for menu '{$menu['menu_label']}'. Fix and re-build again.", 'update');
                        debug($menu, true);
                    }
                    $menu['menu_layout'] = $layouts_id_map[$menu['menu_layout']];
                }
                $menu['menu_parent'] = $menus_id_map[$menu['menu_parent']] ?? null;

                $conditions = array_merge($conditions, [$menu['conditions']]);
                unset($menu['conditions']);



                $check_menu_exists = $this->db->query("SELECT * FROM menu WHERE menu_module_key = '{$menu['menu_module_key']}'");

                if ($check_menu_exists->num_rows() > 0) {

                    if ($is_update && ($menu['menu_position'] == 'sidebar')) {
                        //Se sto aggiornando, il menu esiste già e il menu è posizionato in sidebar, unsetto position e parent in modo che se lo sposto per un cliente non venga più sovrascritta la posizione
                        unset($menu['menu_parent']);
                        unset($menu['menu_position']);
                        unset($menu['menu_order']);

                    }
                    $menu_esistente = $check_menu_exists->row_array();


                    $this->db->where('menu_id', $menu_esistente['menu_id'])->update('menu', $menu);
                    $menus_id_map[$old_menu_id] = $menu_esistente['menu_id'];
                } else {
                    //TODO: check menu already exists (see code before..)
                    //debug($menu,true);



                    $this->db->insert('menu', $menu);
                    $menuid = $this->db->insert_id();
                }

            }
            my_log('debug', "Module install: End menu creation", 'update');

            my_log('debug', "Module install: start grids creation", 'update');
            //Creo Le grid
            $grids_id_map = [];

            $this->db->query(
                "DELETE FROM grids
                 WHERE
                    grids_entity_id IN (
                        SELECT entity_id FROM entity WHERE (entity_module = '$identifier' OR entity_module LIKE '%,$identifier,%')
                    ) AND grids_id NOT IN (
                        SELECT
                            locked_elements_ref_id
                        FROM
                            locked_elements
                        WHERE
                            locked_elements_type = 'grid'
                    ) AND (
                        grids_module_key IS NULL OR grids_module_key = ''


                    )"
            );

            $total = count($json['grids']);

            for ($i = 0; $i < 2; $i++) { //Lo faccio due volte in quanto solo al secondo passaggio avrò tutte le mappature e potrò quindi rimappare anche le sub_grids_id...
                $c = 0;
                foreach ($json['grids'] as $grid) {
                    $c++;
                    progress($c, $total, "grids (step " . ($i + 1) . ")");
                    my_log('debug', "Module install: create grid {$grid['grids_name']}", 'update');

                    $orig_grid = $grid;
                    //Clean joined fields from grid
                    foreach ($grid as $column_name => $val) {
                        if (strpos($column_name, 'grids_') !== 0) {
                            unset($grid[$column_name]);
                        }
                    }

                    $old_grid_id = $grid['grids_id'];
                    unset($grid['grids_id']);
                    if (!array_key_exists($grid['grids_entity_id'], $entities_id_map)) {

                        //If entity is not in module and it's a system entity (or event entity exists), proceed with remapping entity_id
                        $entity_exists = $this->entities->entity_exists($orig_grid['entity_name']);
                        if ($entity_exists) {
                            $entities_id_map[$grid['grids_entity_id']] = $entity_exists['entity_id'];
                            $grid['grids_entity_id'] = $entities_id_map[$grid['grids_entity_id']];
                        } else {
                            //Se non esiste, probabilmente si tratta di una grid che punta a un entità di un altro modulo. Non cambio l'id (darebbe errore visto che non può essere null) e spero che poi venga installato l'altro modulo
                            //20230710 - Cambiato logica... skippo proprio questa grid!
                            continue;
                        }


                    } else {
                        $grid['grids_entity_id'] = $entities_id_map[$grid['grids_entity_id']];
                    }

                    unset($grid['fields']);
                    unset($grid['actions']);

                    if (!empty($grid['grids_inline_form'])) {
                        if (!array_key_exists($grid['grids_inline_form'], $forms_id_map)) {
                            my_log('error', "Form '{$grid['grids_inline_form']}' missing for grid '{$grid['grids_name']}'. Fix and re-build again.", 'update');
                        } else {
                            $grid['grids_inline_form'] = $forms_id_map[$grid['grids_inline_form']];
                        }
                    }

                    if (!empty($grid['grids_bulk_edit_form'])) {
                        if (!array_key_exists($grid['grids_bulk_edit_form'], $forms_id_map)) {
                            my_log('error', "Form '{$grid['grids_bulk_edit_form']}' missing for grid '{$grid['grids_name']}'. Fix and re-build again.", 'update');
                        } else {
                            $grid['grids_bulk_edit_form'] = $forms_id_map[$grid['grids_bulk_edit_form']];
                        }
                    }

                    if (empty($grid['grids_bulk_mode'])) {
                        $grid['grids_bulk_mode'] = DB_BOOL_FALSE;
                    }

                    if (empty($grid['grids_exportable'])) {
                        $grid['grids_exportable'] = DB_BOOL_FALSE;
                    }

                    // replace login entity session placeholder
                    if (strpos($grid['grids_where'], '{login_entity}') !== false) {
                        $grid['grids_where'] = str_replace('{login_entity}', $login_entity['entity_name'], $grid['grids_where']);
                    }

                    //Verifico che non esista già la grid
                    $grid_exists = $this->db->query("SELECT * FROM grids WHERE grids_module_key = '{$grid['grids_module_key']}'");
                    if ($grid_exists->num_rows() == 0) {
                        $this->db->insert('grids', $grid);
                        $new_grid_id = $this->db->insert_id();
                        $grids_id_map[$old_grid_id] = $new_grid_id;
                    } else {
                        $existing_grid = $grid_exists->row_array();
                        $new_grid_id = $existing_grid['grids_id'];
                        $check_lock = $this->db->query("SELECT
                            locked_elements_ref_id
                        FROM
                            locked_elements
                        WHERE
                            locked_elements_type = 'grid' AND locked_elements_ref_id = '$new_grid_id'");
                        if ($check_lock->num_rows() == 0) {
                            if (!empty($grid['grids_sub_grid_id']) && $i == 1) { //Solo al secondo passaggio sono sicuro di aver mappato tutte le grid e quindi posso sovrascrivere la sub_grid eventuale...
                                $grid['grids_sub_grid_id'] = $grids_id_map[$grid['grids_sub_grid_id']];
                            }


                            $this->db->where('grids_id', $existing_grid['grids_id'])->update('grids', $grid);

                            //Rimuovo però fields e actions, se la grid non è lockata altrimenti duplica tutto dopo...
                            if ($this->db->query("SELECT * FROM locked_elements WHERE locked_elements_type = 'grid' AND locked_elements_ref_id = '$new_grid_id'")->num_rows() == 0) {
                                $this->db
                                    ->where('grids_fields_grids_id', $new_grid_id)
                                    ->where('grids_fields_module', $identifier)
                                    ->delete('grids_fields');

                                $this->db->where('grids_actions_grids_id', $new_grid_id)->delete('grids_actions');

                            }
                        }

                        $grids_id_map[$old_grid_id] = $new_grid_id;

                    }
                }
            }

            $c = 0;
            $total = 0;
            foreach ($json['grids'] as $grid) {
                $total += count($grid['fields']);
            }
            $grids_fields_id_map = [];
            //I fields dei form li inserisco dopo aver inserito tutti i form (per non rischiare di avere sub_form_id come chiavi esterne di form non ancora creati...
            foreach ($json['grids'] as $grid) {
                $old_grid_id = $grid['grids_id'];
                $grid_id = $grids_id_map[$old_grid_id];

                if (!$grid_id) {
                    continue;
                }

                //debug($grid,true);
                if ($this->db->query("SELECT * FROM locked_elements WHERE locked_elements_type = 'grid' AND locked_elements_ref_id = '$grid_id'")->num_rows() == 0) {

                    foreach ($grid['fields'] as $field) {
                        $c++;
                        progress($c, $total, 'grids fields');
                        if ($field['grids_fields_replace_type'] == 'field') {
                            if (!array_key_exists($field['grids_fields_fields_id'], $fields_id_map)) {

                                //Verifico se è un modulo con dependency, in tal caso potrei avere comunque quel field_name (non id) già presente
                                if (array_key_exists($field['fields_name'], $all_fields_map)) {
                                    $field['grids_fields_fields_id'] = $all_fields_map[$field['fields_name']];
                                } else {
                                    my_log('debug', "Field '{$field['fields_name']}' missing for entity '{$field['fields_ref']}'. Fix and re-build again.", 'update');
                                    continue;
                                    //debug($field, true);
                                }
                            } else {
                                $field['grids_fields_fields_id'] = $fields_id_map[$field['grids_fields_fields_id']];
                            }
                        } else {
                            unset($field['grids_fields_fields_id']);
                        }

                        $field['grids_fields_grids_id'] = $grid_id;

                        //Se all'interno della stessa grid esiste un campo con la stessa label o field_id, rimuovo quello (refuso da vecchi moduli privi di identifier)
                        if ($field['grids_fields_column_name'] == 'Last name') {
                            //debug($field);
                        }

                        $duplicate_grids_fields = $this->db->where(
                            "
                                grids_fields_grids_id = '{$field['grids_fields_grids_id']}'
                                AND (
                                    (grids_fields_replace_type = 'field' AND fields_name = '{$field['fields_name']}')
                                    OR
                                    (grids_fields_replace_type <> 'field' AND grids_fields_column_name = '{$field['grids_fields_column_name']}')
                                )
                            ",
                            null,
                            false
                        )->join('fields', 'fields_id = grids_fields_fields_id', 'LEFT')->get('grids_fields')->row_array();
                        if (!empty($duplicate_grids_fields)) {
                            $this->db->where('grids_fields_id', $duplicate_grids_fields['grids_fields_id'])->delete('grids_fields');

                            if ($field['grids_fields_column_name'] == 'Last name') {
                                //debug($this->db->last_query(), true);
                            }
                        } else {
                            if ($field['grids_fields_column_name'] == 'Last name') {
                                //debug($this->db->last_query(), true);
                            }
                        }
                        $old_grids_fields_id = $field['grids_fields_id'];


                        $conditions = array_merge($conditions, [$field['conditions']]);
                        unset($field['grids_fields_id']);

                        foreach ($field as $column_name => $val) {
                            if (strpos($column_name, 'grids_fields') !== 0) {
                                unset($field[$column_name]);
                            }
                        }



                        $this->db->insert('grids_fields', $field);
                        $grids_fields_id_map[$old_grids_fields_id] = $this->db->insert_id();
                    }
                }
            }
            //Inserisco le actions
            //debug($json['grids'],true);
            $c = 0;
            $total = 0;
            foreach ($json['grids'] as $grid) {
                $total += count($grid['actions']);
            }
            $grids_actions_map = [];
            foreach ($json['grids'] as $grid) {
                $old_grid_id = $grid['grids_id'];
                $grid_id = $grids_id_map[$old_grid_id];
                //debug($grid,true);
                if ($this->db->query("SELECT * FROM locked_elements WHERE locked_elements_type = 'grid' AND locked_elements_ref_id = '$grid_id'")->num_rows() == 0) {

                    foreach ($grid['actions'] as $action) {
                        $c++;
                        progress($c, $total, 'grids actions');
                        $action['grids_actions_grids_id'] = $grid_id;
                        $old_grids_actions_id = $action['grids_actions_id'];
                        unset($action['grids_actions_id']);

                        //Rimappo i layout legati alle actions...
                        if (!empty($action['grids_actions_layout'])) {
                            if (!array_key_exists($action['grids_actions_layout'], $layouts_id_map)) {
                                // debug($grid);
                                // debug($action, true);
                                my_log('debug', 'TODO: fare in modo che in fase di export module, si porti dietro anche i layouts legati alle grid actions così da avere qui le mappature corrette', 'update');
                            }
                            $action['grids_actions_layout'] = $layouts_id_map[$action['grids_actions_layout']];
                        }
                        //Rimappo i form legati alle actions...
                        if (!empty($action['grids_actions_form'])) {
                            $action['grids_actions_form'] = $forms_id_map[$action['grids_actions_form']] ?? null;
                        }
                        $conditions = array_merge($conditions, [$action['conditions']]);
                        unset($action['conditions']);

                        $this->db->insert('grids_actions', $action);

                        $grids_actions_map[$old_grids_actions_id] = $this->db->insert_id();
                    }
                }
            }

            my_log('debug', "Module install: end grids creation", 'update');

            //Inserisco i grafici
            $this->db->query(
                "DELETE FROM charts
                 WHERE
                    charts_module = '$identifier'
                    AND charts_id NOT IN (
                        SELECT
                            locked_elements_ref_id
                        FROM
                            locked_elements
                        WHERE
                            locked_elements_type = 'chart'
                    )
                    AND (
                        charts_module_key IS NULL OR charts_module_key = ''
                    )"
            );
            $charts_id_map = [];

            $c = 0;
            $total = count($json['charts']);
            $total += count($grid['actions']);

            foreach ($json['charts'] as $chart) {
                $c++;
                progress($c, $total, 'charts');
                //Rimuovo prima l'eventuale form già presente
                $old_chart_id = $chart['charts_id'];
                unset($chart['charts_id']);

                $chart_exists = $this->db->query("SELECT * FROM charts WHERE charts_module_key = '{$chart['charts_module_key']}'");
                if ($chart_exists->num_rows() == 0) {
                    $this->db->insert('charts', $chart);
                    $new_chart_id = $this->db->insert_id();
                } else {
                    $new_chart_id = $chart_exists->row()->charts_id;
                    $this->db->where('charts_id', $new_chart_id)->update('charts', $chart);
                }
                $charts_id_map[$old_chart_id] = $new_chart_id;
            }

            //debug($charts_id_map,true);

            $this->db->query(
                "DELETE FROM charts_elements
                 WHERE
                    charts_elements_charts_id IN (SELECT charts_id FROM charts WHERE charts_module = '$identifier')"
            );

            $c = 0;
            $total = count($json['charts_elements']);

            foreach ($json['charts_elements'] as $chart_element) {
                $c++;
                progress($c, $total, 'charts elements');
                $chart_element['charts_elements_charts_id'] = $charts_id_map[$chart_element['charts_elements_charts_id']];
                $chart_element['charts_elements_entity_id'] = $entities_id_map[$chart_element['charts_elements_entity_id']];
                $chart_element['charts_elements_fields_id'] = $fields_id_map[$chart_element['charts_elements_fields_id']];
                unset($chart_element['charts_elements_id']);
                $this->db->insert('charts_elements', $chart_element);
            }

            //Maps insert
            $this->db->query(
                "DELETE FROM maps
                        WHERE
                            maps_module = '$identifier'
                            AND maps_id NOT IN (
                                SELECT
                                    locked_elements_ref_id
                                FROM
                                    locked_elements
                                WHERE
                                    locked_elements_type = 'map'
                            )
                            AND (
                                maps_module_key IS NULL OR maps_module_key = ''
                            )"
            );
            $c = 0;
            $total = count($json['maps']);
            $maps_id_map = [];
            foreach ($json['maps'] as $map) {
                $c++;
                progress($c, $total, 'maps');
                //Rimuovo prima l'eventuale form già presente
                $old_map_id = $map['maps_id'];
                unset($map['maps_id']);
                $map['maps_entity_id'] = $entities_id_map[$map['maps_entity_id']];

                $map_exists = $this->db->query("SELECT * FROM maps WHERE maps_module_key = '{$map['maps_module_key']}'");
                if ($map_exists->num_rows() == 0) {
                    $this->db->insert('maps', $map);
                    $new_map_id = $this->db->insert_id();
                } else {
                    $new_map_id = $map_exists->row()->maps_id;
                    $this->db->where('maps_id', $new_map_id)->update('maps', $map);
                }
                $maps_id_map[$old_map_id] = $new_map_id;
            }

            $this->db->query(
                "DELETE FROM maps_fields
                        WHERE
                        maps_fields_maps_id IN (SELECT maps_id FROM maps WHERE maps_module = '$identifier')"
            );
            $c = 0;
            $total = count($json['maps_fields']);
            foreach ($json['maps_fields'] as $map_field) {
                $c++;
                progress($c, $total, 'maps fields');
                $map_field['maps_fields_maps_id'] = $maps_id_map[$map_field['maps_fields_maps_id']];

                $map_field['maps_fields_fields_id'] = $fields_id_map[$map_field['maps_fields_fields_id']];
                unset($map_field['maps_fields_id']);
                $this->db->insert('maps_fields', $map_field);
            }

            $c = 0;
            $total = count($json['calendars']);

            //Inserisco i calendar
            foreach ($json['calendars'] as $calendar) {
                $c++;
                progress($c, $total, 'calendars');
                //debug($calendar,true);
                $old_calendar_id = $calendar['calendars_id'];
                unset($calendar['calendars_id']);

                if ($calendar['calendars_name'] == 'Calendario prenotazione automezzo') {
                    //debug($calendar);
                    //debug($entities_id_map);
                }

                $calendar['calendars_entity_id'] = $entities_id_map[$calendar['calendars_entity_id']];
                //TODO: se punta a users e users giustamente non è del modulo, è un problema... basarsi sul entity_name
                $calendar['calendars_filter_entity_id'] = (array_key_exists($calendar['calendars_filter_entity_id'], $entities_id_map) ? $entities_id_map[$calendar['calendars_filter_entity_id']] : $calendar['calendars_filter_entity_id']);

                if ($calendar['calendars_name'] == 'Calendario prenotazione automezzo') {
                    //debug($calendar,true);
                }


                unset($calendar['fields']);

                if (!empty($calendar['calendars_form_edit'])) {
                    if (!array_key_exists($calendar['calendars_form_edit'], $forms_id_map)) {
                        my_log('debug', "Form '{$calendar['calendars_form_edit']}' missing for calendar '{$calendar['calendars_name']}'. Fix and re-build again.", 'update');
                    } else {
                        $calendar['calendars_form_edit'] = $forms_id_map[$calendar['calendars_form_edit']];
                    }
                }
                if (!empty($calendar['calendars_form_create'])) {
                    if (!array_key_exists($calendar['calendars_form_create'], $forms_id_map)) {
                        my_log('debug', "Form '{$calendar['calendars_form_create']}' missing for calendar '{$calendar['calendars_name']}'. Fix and re-build again.", 'update');
                    } else {
                        $calendar['calendars_form_create'] = $forms_id_map[$calendar['calendars_form_create']];
                    }
                }

                if (empty($calendar['calendars_allow_create'])) {
                    $calendar['calendars_allow_create'] = DB_BOOL_FALSE;
                }

                if (empty($calendar['calendars_allow_edit'])) {
                    $calendar['calendars_allow_edit'] = DB_BOOL_FALSE;
                }

                //Verifico che non esista già la calendar
                $calendar_exists = $this->db->query("SELECT * FROM calendars WHERE calendars_module_key = '{$calendar['calendars_module_key']}'");
                if ($calendar_exists->num_rows() == 0) {
                    $this->db->insert('calendars', $calendar);
                    $new_calendar_id = $this->db->insert_id();
                    $calendars_id_map[$old_calendar_id] = $new_calendar_id;
                } else {
                    $existing_calendar = $calendar_exists->row_array();

                    $this->db->where('calendars_id', $existing_calendar['calendars_id'])->update('calendars', $calendar);
                    $new_calendar_id = $existing_calendar['calendars_id'];
                    //Rimuovo però fields e actions, altrimenti duplica tutto dopo...
                    $this->db->where('calendars_fields_calendars_id', $new_calendar_id)->delete('calendars_fields');

                    $calendars_id_map[$old_calendar_id] = $new_calendar_id;
                }
            }
            $c = 0;
            $total = 0;
            foreach ($json['calendars'] as $calendar) {
                $total += count($calendar['fields']);
            }
            //I fields dei calendar li inserisco dopo aver inserito tutti i calendar
            foreach ($json['calendars'] as $calendar) {
                foreach ($calendar['fields'] as $field) {
                    $c++;
                    progress($c, $total, 'calendars fields');
                    if (!array_key_exists($field['calendars_fields_fields_id'], $fields_id_map)) {
                        //debug($field);
                    }
                    $field['calendars_fields_fields_id'] = $fields_id_map[$field['calendars_fields_fields_id']];

                    unset($field['calendars_fields_id']);

                    if (array_key_exists('calendars_filter_session_key', $field)) {
                        unset($field['calendars_filter_session_key']);
                    }

                    $field['calendars_fields_calendars_id'] = $calendars_id_map[$field['calendars_fields_calendars_id']];
                    $this->db->insert('calendars_fields', $field);
                }
            }

            my_log('debug', "Module install: start layouts boxes creation", 'update');
            //Inserisco i layoutbox
            $layout_box_map = [];
            
            $this->db->query(
                "DELETE FROM layouts_boxes
                WHERE
                    layouts_boxes_layout IN (" . implode(',', array_filter($layouts_id_map)) . ")
                    AND layouts_boxes_layout IN (SELECT layouts_id FROM layouts WHERE layouts_module = '{$identifier}')
                    AND layouts_boxes_id NOT IN (
                        SELECT
                            locked_elements_ref_id
                        FROM
                            locked_elements
                        WHERE
                            locked_elements_type = 'layout_box'
                    )"
            );
            $c = 0;
            $total = count($json['layouts_boxes']);
            foreach ($json['layouts_boxes'] as $lb) {

                $c++;
                progress($c, $total, 'layouts boxes (step 1)');

                $old_layout_box_id = $lb['layouts_boxes_id'];
                unset($lb['layouts_boxes_id']);

                if (!array_key_exists($lb['layouts_boxes_layout'], $layouts_id_map)) {
                    my_log('debug', "Layout '{$lb['layouts_boxes_layout']}' not found", 'update');

                    continue;
                }

                $lb['layouts_boxes_layout'] = $layouts_id_map[$lb['layouts_boxes_layout']];

                switch ($lb['layouts_boxes_content_type']) {
                    case 'form':
                        if (!array_key_exists($lb['layouts_boxes_content_ref'], $forms_id_map)) {
                            //debug($lb);
                            debug("ANOMALIA... Form non trovato!", true);
                            continue 2;
                        }
                        $lb['layouts_boxes_content_ref'] = $forms_id_map[$lb['layouts_boxes_content_ref']];
                        break;
                    case 'grid':
                        if (!array_key_exists($lb['layouts_boxes_content_ref'], $grids_id_map)) {
                            debug($lb);
                            debug($grids_id_map);
                            my_log('error', "ANOMALIA... Grid nel layout '{$lb['layouts_boxes_title']}' non trovata!", 'update');
                            continue 2;
                        }

                        $lb['layouts_boxes_content_ref'] = $grids_id_map[$lb['layouts_boxes_content_ref']];
                        break;
                    /* case 'menu_group':
                    if (!array_key_exists($lb['layouts_boxes_content_ref'], $menus_id_map)) {
                    continue;
                    }
                    $lb['layouts_boxes_content_ref'] = $menus_id_map[$lb['layouts_boxes_content_ref']];
                    break; */
                    case 'tabs':
                        //                        $new_lays = [];
                        //                        foreach (explode(',', $lb['layouts_boxes_content_ref']) as $old_layout_id) {
                        //                            $new_lays[] = $layouts_id_map[$old_layout_id];
                        //                        }
                        //                        $lb['layouts_boxes_content_ref'] = implode(',', $new_lays);
                        //Li processo dopo in quanto devo avere prima i layoutbox per poterli rimappare nelle tab
                        continue 2;
                        break;
                    case 'menu_group':
                        //debug("Layout box di tipo menu_group non gestito, ma dovrebbe funzionare correttamente lo stesso...");
                        //continue 2;
                        break;
                    case 'chart':
                        if (!array_key_exists($lb['layouts_boxes_content_ref'], $charts_id_map)) {
                            //                            debug($lb);
                            //                            debug($charts_id_map);
                            //                            debug("ANOMALIA... Grafico non trovato!", true);
                            continue 2;
                        }

                        $lb['layouts_boxes_content_ref'] = $charts_id_map[$lb['layouts_boxes_content_ref']];

                        //debug($lb,true);

                        break;
                    case 'calendar':
                        if (!array_key_exists($lb['layouts_boxes_content_ref'], $calendars_id_map)) {
                            debug($lb);
                            debug($calendars_id_map);
                            debug("ANOMALIA... Grid non trovata!", true);
                            continue 2;
                        }

                        $lb['layouts_boxes_content_ref'] = $calendars_id_map[$lb['layouts_boxes_content_ref']];
                        break;
                    case 'layout':
                        if (!array_key_exists($lb['layouts_boxes_content_ref'], $layouts_id_map)) {
                            my_log('error', "Module install: Layout '{$lb['layouts_boxes_content_ref']}' not found.", 'update');
                            debug('Questo layout ci deve essere!', true);
                            continue 2;
                        }
                        $lb['layouts_boxes_content_ref'] = $layouts_id_map[$lb['layouts_boxes_content_ref']];
                        break;
                    case 'map':
                        if (!array_key_exists($lb['layouts_boxes_content_ref'], $maps_id_map)) {
                            debug($lb);
                            debug($maps_id_map);
                            debug("ANOMALIA... Mappa non trovata!", true);
                            continue 2;
                        }

                        $lb['layouts_boxes_content_ref'] = $maps_id_map[$lb['layouts_boxes_content_ref']];
                        break;
                    case '':
                        //                        debug($lb);
                        //                        debug("Layout box senza tipo... normale?");
                        //continue 2;
                        break;

                    default:
                        //                        debug($lb);
                        //                        debug("Layout box di tipo {$lb['layouts_boxes_content_type']} non gestito!",true);
                        //continue;
                        break;
                }

                //                if ($lb['layouts_boxes_content_ref'] == '195') {
                //                    debug($lb);
                //                    debug($grids_id_map);
                //                }
                //                echo 'Creo lb:';
                if (!$lb['layouts_boxes_dragable']) {
                    $lb['layouts_boxes_dragable'] = DB_BOOL_FALSE;
                }

                $conditions = array_merge($conditions, [$lb['conditions']]);
                unset($lb['conditions']);

                $this->db->insert('layouts_boxes', $lb);
                $new_lb_id = $this->db->insert_id();
                $layout_box_map[$old_layout_box_id] = $new_lb_id;
            }
            $c = 0;
            $total = count($json['layouts_boxes']);
            foreach ($json['layouts_boxes'] as $lb) {
                $c++;
                progress($c, $total, 'layouts boxes (step 2)');

                $old_layout_box_id = $lb['layouts_boxes_id'];
                unset($lb['layouts_boxes_id']);

                if (!array_key_exists($lb['layouts_boxes_layout'], $layouts_id_map)) {
                    debug("ANOMALIA... Layout non trovato!", true);
                    continue;
                }

                $lb['layouts_boxes_layout'] = $layouts_id_map[$lb['layouts_boxes_layout']];

                switch ($lb['layouts_boxes_content_type']) {

                    case 'tabs':
                        $new_lays = [];
                        foreach (explode(',', $lb['layouts_boxes_content_ref']) as $_old_layout_box_id) {
                            if (!empty($layout_box_map[$_old_layout_box_id])) {
                                $new_lays[] = $layout_box_map[$_old_layout_box_id];
                            }

                        }

                        //debug($new_lays);

                        $lb['layouts_boxes_content_ref'] = implode(',', $new_lays);

                        break;
                    default:
                        //debug("Layout box di tipo {$lb['layouts_boxes_content_type']} non gestito!");
                        continue 2;
                        break;
                }
                //                echo 'PASSAGGIO 2 - Creo lb:';
                //                debug($lb);
                if (!$lb['layouts_boxes_dragable']) {
                    $lb['layouts_boxes_dragable'] = DB_BOOL_FALSE;
                }

                $conditions = array_merge($conditions, [$lb['conditions']]);
                unset($lb['conditions']);

                $this->db->insert('layouts_boxes', $lb);
                $new_lb_id = $this->db->insert_id();
                $layout_box_map[$old_layout_box_id] = $new_lb_id;
            }
            my_log('debug', "Module install: end layouts boxes creation", 'update');

            my_log('debug', "Module install: start events creation", 'update');
            $this->db->where('fi_events_module', $identifier)->delete('fi_events');
            $this->db->where('post_process_module', $identifier)->delete('post_process');
            $this->db->where('hooks_module', $identifier)->delete('hooks');
            $this->db->where('crons_module', $identifier)->delete('crons');
            //debug($json, true);
            if (!empty($json['fi_events'])) {
                $this->load->model('fi_events');
                $c = 0;
                $total = count($json['fi_events']);
                foreach ($json['fi_events'] as $event) {
                    $c++;
                    progress($c, $total, 'events');
                    $data_post = json_decode($event['fi_events_json_data'], true);
                    switch ($event['fi_events_type']) {

                        case 'database':
                            $data_post['pp']['post_process_id'] = null;
                            $data_post['pp']['post_process_entity_id'] = $entities_id_map[$data_post['pp']['post_process_entity_id']];

                            //Sovrascrivo il pp when (perde il pre- o post- a volte, mentre l'informazione ce l'ho ancora nel fi_events_when)
                            $data_post['pp']['post_process_when'] = $event['fi_events_when'];
                            $data_post['_when'] = '';
                            break;
                        case 'hook':
                            $data_post['hook']['hooks_id'] = null;

                            switch ($data_post['hook']['hooks_type']) {
                                case 'layout':
                                    if ($data_post['hook']['hooks_ref']) {
                                        $data_post['hook']['hooks_ref'] = $layouts_id_map[$data_post['hook']['hooks_ref']];
                                    } else {
                                        $data_post['hook']['hooks_ref'] = '';
                                    }
                                    break;
                                case 'grid':
                                    $data_post['hook']['hooks_ref'] = $grids_id_map[$data_post['hook']['hooks_ref']];
                                    break;
                                case 'form':
                                    if (!empty($data_post['hook']['hooks_ref'])) {
                                        $data_post['hook']['hooks_ref'] = $forms_id_map[$data_post['hook']['hooks_ref']];
                                    } else {
                                        //debug($data_post, true);
                                        $data_post['hook']['hooks_ref'] = '';
                                    }

                                    break;

                                case 'template_hook':
                                    //E' un hook di tipo template_hook... lascio andare avanti, viene gestito tutto in automatico dal model fi_events
                                    break;
                                default:
                                    debug($event);
                                    debug($data_post, true);
                                    break;
                            }

                            break;
                        case 'cron':
                            $data_post['cron']['crons_id'] = null;

                            break;
                        default:
                            debug($event);
                            debug($data_post, true);

                            break;
                    }

                    $this->fi_events->new_event($data_post);
                }
            } else { //Per retrocompatibilità (vecchi modulo, mantengo la logica pp,hooks,crons...)
                $c = 0;
                $total = count($json['post_processes']);
                //INserisco i postprocess
                $this->db->where('post_process_module', $identifier)->delete('post_process');
                foreach ($json['post_processes'] as $pp) {
                    $c++;
                    progress($c, $total, 'post process');
                    unset($pp['post_process_id']);
                    $pp['post_process_entity_id'] = $entities_id_map[$pp['post_process_entity_id']];

                    $this->db->insert('post_process', $pp);
                }
                $c = 0;
                $total = count($json['hooks']);
                //Inserisco gli hooks
                $this->db->where('hooks_module', $identifier)->delete('hooks');
                foreach ($json['hooks'] as $h) {
                    $c++;
                    progress($c, $total, 'hooks');
                    unset($h['hooks_id']);
                    switch ($h['hooks_type']) {
                        case 'post-form':
                        case 'pre-form':
                            $h['hooks_ref'] = $forms_id_map[$h['hooks_ref']];
                            break;
                        case 'post-layout':
                        case 'pre-layout':
                            if ($h['hooks_ref']) {
                                $h['hooks_ref'] = $layouts_id_map[$h['hooks_ref']];
                            }
                            break;
                        case 'pre-grid':
                        case 'post-grid':
                            if ($h['hooks_ref']) {
                                $h['hooks_ref'] = $grids_id_map[$h['hooks_ref']];
                            }
                            break;
                        default:
                            die("Tipo di hook '{$h['hooks_type']}' non gestito!");
                            break;
                    }

                    $this->db->insert('hooks', $h);
                    $c++;
                    progress($c, $total);
                }
            }

            my_log('debug', "Module install: end events creation", 'update');

            my_log('debug', "Module install: start emails creation", 'update');

            $this->db->where('emails_module', $identifier)->delete('emails');
            if (!empty($json['emails'])) {
                $c = 0;
                $total = count($json['emails']);
                //Verifico se è già presente quella mail
                foreach ($json['emails'] as $email) {
                    $c++;
                    progress($c, $total, 'emails');
                    unset($email['emails_id']);
                    $this->db->insert('emails', $email);
                }
            }
            my_log('debug', "Module install: end emails creation", 'update');

            //Importo i raw_data

            if ($this->db->dbdriver != 'postgre') {
                $this->db->query("SET FOREIGN_KEY_CHECKS=0;");
            }

            my_log('debug', "Module install: start creating conditions", 'update');
            $conditions = array_filter($conditions, function ($condition) {
                //debug($condition,true);
                return $condition != [];
            });
            $c = 0;
            $total = count($conditions);
            foreach ($conditions as $condition) {
                $c++;
                progress($c, $total, 'Conditions');
                if (!empty($condition['conditions_module_key'])) {
                    $condition_exists = $this->db->where('conditions_module_key', $condition['conditions_module_key'])->get('_conditions')->row_array();
                } else {
                    $condition_exists = [];
                }
                //debug($condition);
                unset($condition['conditions_id']);

                //Remap id based on conditions_what
                switch ($condition['conditions_what']) {
                    case 'layouts_boxes':
                        $condition['conditions_ref'] = $layout_box_map[$condition['conditions_ref']];
                        break;
                    case 'grids_actions':
                        $condition['conditions_ref'] = $grids_actions_map[$condition['conditions_ref']];
                        break;
                    case 'layouts':
                        $condition['conditions_ref'] = $layouts_id_map[$condition['conditions_ref']];
                        break;
                    case 'menu':
                        $condition['conditions_ref'] = $menus_id_map[$condition['conditions_ref']];
                        break;
                    case 'grids_fields':
                        $condition['conditions_ref'] = $grids_fields_id_map[$condition['conditions_ref']];
                        break;
                    default:
                        debug($condition);
                        debug("Condition '{$condition['conditions_what']}' not recognized");
                        my_log('error', "Condition '{$condition['conditions_what']}' not recognized", 'update');
                        break;
                }

                if ($condition_exists) {
                    $this->db->where('conditions_id', $condition_exists['conditions_id'])->update('_conditions', $condition);
                } else {
                    $this->db->insert('_conditions', $condition);
                }

            }

            my_log('debug', "Module install: start raw data insert", 'update');
            //debug($json['entities'],true);
            $c = $cu = 0;
            $total = $totalu = 0;
            foreach ($json['entities'] as $entity) {
                $total += count($entity['raw_data_install']);
                $totalu += count($entity['raw_data_update']);
            }
            $this->mycache->clearCache();
            foreach ($json['entities'] as $entity) {
                if (!$this->crmentity->entityExists($entity['entity_name'])) {
                    continue;
                }
                //Inserisco i raw_data_install
                if (array_key_exists('raw_data_install', $entity)) {
                    if ($entity['raw_data_install']) {
                        my_log('debug', "Module install: raw data insert - {$entity['entity_name']}", 'update');
                    }
                    //20230210 - MP - Modificata la logica: inserisco solo se la tabella è vuota perchè se non è vuota vuol dire che è già stato inserito l'install o che il cliente si è inserito i suoi valori custom
                    $count_table_records = $this->db->count_all($entity['entity_name']);
                    if ($count_table_records == 0) {
                        foreach ($entity['raw_data_install'] as $row) {
                            $c++;
                            progress($c, $total, 'raw data install');
                            //debug($row);
                            //Verifico se il record esiste già basandomi sulla pk
                            if ($this->db->where($entity['entity_name'] . '_id', $row[$entity['entity_name'] . '_id'])->get($entity['entity_name'])->num_rows() == 0) {

                                $this->db->insert($entity['entity_name'], $row);
                            }

                        }
                    }

                }
                //Aggiorno i raw_data_update
                if (array_key_exists('raw_data_update', $entity)) {
                    if ($entity['raw_data_update']) {
                        my_log('debug', "Module install: raw data update - {$entity['entity_name']}", 'update');
                    }
                    foreach ($entity['raw_data_update'] as $row) {
                        $cu++;
                        progress($cu, $totalu, 'raw data install');
                        //debug($row);
                        //Verifico se il record esiste già basandomi sulla pk
                        if ($this->db->where($entity['entity_name'] . '_id', $row[$entity['entity_name'] . '_id'])->get($entity['entity_name'])->num_rows() == 0) {
                            $this->db->insert($entity['entity_name'], $row);
                        } else {
                            $id = $row[$entity['entity_name'] . '_id'];
                            unset($row[$entity['entity_name'] . '_id']);
                            $this->db->where($entity['entity_name'] . '_id', $id)->update($entity['entity_name'], $row);
                        }
                    }
                }
            }
            my_log('debug', "Module install: end raw data insert", 'update');

            //A questo punto rimappare eventuali hook custom (grid, layout ecc...)

            if ($this->db->dbdriver != 'postgre') {
                $this->db->query("SET FOREIGN_KEY_CHECKS=1;");
            }
            unset($json['entities']);
            unset($json['layouts']);
            unset($json['forms']);
            unset($json['menu']);
            unset($json['grids']);
            unset($json['layouts_boxes']);

            return true;

        } catch (Exception $e) {
            my_log('error', 'Install module error: ' . $e->getMessage(), 'update');
            die($e->getMessage());
            //$this->db->trans_rollback();
        }
    }

}