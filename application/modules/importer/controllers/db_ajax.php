<?php

class Db_ajax extends MX_Controller {

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

        $this->settings = $this->db->get('settings')->row_array();
    }

    public function import_1() {
        $data = $this->input->post();

        if (empty($data) || !$data['entity_id']) {
            die();
        }
        
        $config['upload_path'] = './csv/';
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
            die();
        }
        
        
        //Read csv first line
        if (($handle = fopen($import_data['csv_file']['full_path'], "r")) !== FALSE) {
            $head = NULL;
            $body = array();
            while (($row = fgetcsv($handle, 0, "{$import_data['field_separator']}")) !== FALSE) {
                if($head === NULL) {
                    $head = $row; //First line is the header
                } else {
                    $body[] = $row; //The rest is body
                }   
            }
            fclose($handle);
            
            
            $csv_fields = array_filter($data['csv_fields']);
            $count = 0;
            
            if($test) {
                $this->db->trans_start();
            }
            
            
            $entity = $this->db->get_where('entity', array('entity_id'=>$import_data['entity_id']))->row_array();
            foreach ($body as $row) {
                $insert = array();
                foreach ($csv_fields as $k => $field) {
                    $insert[$field] = $row[$k];
                }
                if(!empty($insert)) {
                    if($this->db->insert($entity['entity_name'], $insert)) {
                        $count++;
                    }
                }
            }
            
            
            if($test) {
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status'=>0, 'txt'=>'Import operation cannot be executed without errors.'));
                } else {
                    echo json_encode(array('status'=>1, 'txt'=>'Import operation can be executed without errors.'));
                }
                $this->db->trans_rollback();
            } else {
                $this->session->set_flashdata(SESS_IMPORT_COUNT, $count);
                echo json_encode(array('status'=>1, 'txt'=>base_url('importer/import_return')));
            }
            
        } else {
            die('Cannot open the CSV file.');
        }
    }
}