<?php

class Db_ajax extends MY_Controller {

    public function __construct() {
        parent::__construct();
        if ($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Module not installed');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Access forbidden');
        }
    }

    public function import_1() {
        $data = $this->input->post();

        if (empty($data) || !$data['entity_id']) {
            die();
        }
        
        if (!is_dir('./uploads/import/')) {
            mkdir('./uploads/import/', 0777, true);
        }
        
        $config['upload_path'] = './uploads/import/';
        $config['allowed_types'] = '*';
        $config['max_size'] = '5000';
        $this->load->library('upload');
        
        $this->upload->initialize($config);
        
        
        if (!$this->upload->do_upload('csv_file')) {
            echo json_encode(array('status'=>0, 'txt'=>$this->upload->display_errors()));
            die();
        }
        $data['csv_file'] = $this->upload->data();
        
        $this->session->set_userdata(SESS_IMPORT_DATA, $data);
        
        echo json_encode(array('status'=>1, 'txt'=>base_url('importer/import_map')));
    }

    
    
    public function import_2($test=0) {
        $data = $this->input->post();
        $import_data = $this->session->userdata(SESS_IMPORT_DATA);
        
        if (empty($data) || empty($import_data)) {
            echo json_encode(array('status'=>0, 'txt'=>"No session or file!"));
            die();
        }
        
        //Read csv first line
        if (($handle = fopen($import_data['csv_file']['full_path'], "r")) !== FALSE) {
            
            /*
             * Info preliminari
             */
            $csv_fields = array_filter($data['csv_fields']);
            $count = 0;
            
            $this->db->trans_start();
            
            $entity = $this->datab->get_entity($import_data['entity_id']);
            
            if ($import_data['action_on_data_present'] == 1) {//Metodo di importazione: DELETE INSERT
                //die('test');
                $this->db->empty_table($entity['entity_name']);
            }
            
            
            //Estraggo tutte le info sui campi (mi tornano utili dopo)
            $field_data_map = array();
            foreach ($csv_fields as $k => $field) {
                foreach ($entity['fields'] as $_field) {
                    if ($_field['fields_name'] == $field) {
                        $field_data_map[$field] = $_field;
                        continue;
                    }
                }
            }

            
            $head = fgetcsv($handle, 0, "{$import_data['field_separator']}");
            $body = array();
            $riga_id = 1;
            
            $errors = $warnings = array();
            while (($row = fgetcsv($handle, 0, "{$import_data['field_separator']}")) !== FALSE) {
                
                //
                // Remap campi riga su campi entità e metto in array insert
                //
                $insert = array();
                foreach ($csv_fields as $k => $field) {
                    if (array_key_exists('ref_fields', $data) && array_key_exists($k, $data['ref_fields'])) {
                        //TODO: esiste un field_ref impostato (prendo l'id dall'altra entità)
                        if ($data['ref_fields'][$k]) {
                            //Cerco il record con quella chiave
                            if ($row[$k]) {
                                $ref_record = $this->db->get_where($field_data_map[$field]['fields_ref'], array($data['ref_fields'][$k] => $row[$k]));
                                //debug($ref_record,true);
                                if ($ref_record->num_rows() >= 1 && $row[$k]) { //Fix
                                    //Giusto per avvisare l'utente, segnalo come warning il fatto che ho trovato più corrispondenze
                                    if ($ref_record->num_rows() > 1) {
                                        $warn = "{$ref_record->num_rows()} records found with {$data['ref_fields'][$k]}='{$row[$k]}', first one used.";
                                        $warnings[$warn] = $warn;
                                    }

                                    $insert[$field] = $ref_record->row_array()[$field_data_map[$field]['fields_ref'].'_id'];
                                } else {
                                    //Se il campo può essere null e non ho trovato corrispondenza, lo setto a null
                                    if ($field_data_map[$field]['fields_required'] != 't') {
                                        $insert[$field] = null;
                                        $warn = "I cannot find record in {$field_data_map[$field]['fields_ref']} with {$data['ref_fields'][$k]}='{$row[$k]}'.";
                                        $warnings[$warn] = $warn;
                                    } else { //Altrimenti errore
                                        $err = "I cannot find record in {$field_data_map[$field]['fields_ref']} with {$data['ref_fields'][$k]}='{$row[$k]}'.";
                                        $errors[$err] = $err;
                                    }
                                }  
                            } else {
                                //Se il campo può essere null e non ho trovato corrispondenza, lo setto a null
                                if ($field_data_map[$field]['fields_required'] != 't') {
                                    $insert[$field] = null;
                                    $warn = "I cannot find record in {$field_data_map[$field]['fields_ref']} with {$data['ref_fields'][$k]}='{$row[$k]}'.";
                                    $warnings[$warn] = $warn;
                                } else { //Altrimenti errore
                                    $err = "I cannot find record in {$field_data_map[$field]['fields_ref']} with {$data['ref_fields'][$k]}='{$row[$k]}'.";
                                    $errors[$err] = $err;
                                }
                            }
                            
                                                    
                            continue;
                        } else {
                            //Se è stato lasciato vuoto va bene andare avanti e prendere l'id
                        }
                    }
                    
                    switch ($field_data_map[$field]['fields_type']) {
                        case 'FLOAT':
                            $insert[$field] = str_replace(',', '.', $row[$k]);
                            break;
                        case 'INT':
                            $insert[$field] = (int)($row[$k]);
                            break;
                        case 'TIMESTAMP WITHOUT TIME ZONE':
                            if ($row[$k]) {
                                $time = strtotime($row[$k]);
                                $insert[$field] = date('Y-m-d h:m:s', $time);
                            } else {
                                $insert[$field] = null;
                            }
                            break;
                        case 'BOOL':
                            if (is_numeric($row[$k])) {
                                $insert[$field] = ($row[$k] != 0)?'t':'f';
                            } else {
                                $insert[$field] = ($row[$k])?'t':'f';
                            }
                            break;
                        default:
                            $insert[$field] = $row[$k];
                            break;
                    }
                    //Fix: se è multilingua mi aspetto un json_encode nella colonna del csv
                    if ($field_data_map[$field]['fields_multilingual'] == 't') {
                        $value = @json_decode($insert[$field], true);
                        if (is_array($value)) {
                            $insert[$field] = $value;
                        } else {
                            //Lascio com'era e importo nella lingua di default
                        }
                        
                    }
                    
                    //Se il campo può essere null e non ho trovato corrispondenza, lo setto a null
                    if ($field_data_map[$field]['fields_required'] == 't' && $insert[$field] == '') {
                        if ($field_data_map[$field]['fields_default']) {
                            unset($insert[$field]);
                        } else {
                            $warn = "I cannot insert row with $field empty (row id: $riga_id)";
                            $warnings[$warn] = $warn;
                            $insert = array();
                            continue 2;
                        }
                    }
                }

                //
                //Se ho qualcosa da inserire e non ho riscontrato errori
                //
                if(!empty($insert) && empty($errors)) {
                    if ($import_data['action_on_data_present'] == 2 || $import_data['action_on_data_present'] == 4) { //Metodo di importazione: UPDATE
                        $campo_chiave = $data['csv_fields'][$data['unique_key']];
                        $riga = $this->db->where($campo_chiave, $insert[$campo_chiave])->get($entity['entity_name']);
                        if ($riga->num_rows() == 1) {
                            $this->apilib->edit($entity['entity_name'], $riga->row()->{$entity['entity_name'].'_id'}, $insert);
                        } elseif($riga->num_rows() > 1) { 
                            //TODO: foreach e aggiorno tutti? Valutare...
                            $this->apilib->edit($entity['entity_name'], $riga->row()->{$entity['entity_name'].'_id'}, $insert);
                            $warn = "I've found $updated records in {$entity['entity_name']} with $campo_chiave='{$insert[$campo_chiave]}'.";
                            $warnings[$warn] = $warn;
                        } else {
                            $warn = "I cannot find record in {$entity['entity_name']} with $campo_chiave='{$insert[$campo_chiave]}'.";
                            $warnings[$warn] = $warn;
                            if ($import_data['action_on_data_present'] == 4) {
                                $this->apilib->create($entity['entity_name'], $insert);
                            }
                        }
                        //$this->apilib->edit($entity['entity_name'], $insert);
                        //$updated = $this->db->affected_rows();
                        $count += 1;
                        
                    } else { //Metodo di importazione: INSERT
                        //debug($insert);
                        if($this->apilib->create($entity['entity_name'], $insert)) {
                            $count++;
                        } 
                    }
                }
                
                $riga_id++;
            }
            
            fclose($handle);
            
            
            if (!empty($errors)) {
                echo json_encode(array('status'=>0, 'txt'=>implode('<br />',$errors)));
                die();
            }
            
            if($test) {
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status'=>0, 'txt'=>'Import operation cannot be executed without errors.'));
                } else {
                    if (!empty($warnings)) {
                        echo json_encode(array('status'=>1, 'txt'=>'Import operation can be executed without errors. But...<br /><br />'.implode('<br />',$warnings)));
                    } else {
                        echo json_encode(array('status'=>1, 'txt'=>'Import operation can be executed without errors.'));
                    }
                }
                $this->db->trans_rollback();
            } else {
                $this->db->trans_complete();
                $this->session->set_flashdata(SESS_IMPORT_COUNT, $count);
                $this->session->set_flashdata(SESS_IMPORT_WARNINGS, $warnings);
                
                echo json_encode(array('status'=>1, 'txt'=>base_url('importer/import_return')));
            }
            
        } else {
            die('Cannot open the CSV file.');
        }
    }
    public function get_fields_by_entity_name($entity_name) {
        echo json_encode($this->datab->get_entity_by_name($entity_name)['fields']);
    }
}