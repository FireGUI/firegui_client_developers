<?php

class Importer extends MX_Controller {

    var $template = array();
    var $settings = NULL;

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

        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }

    public function index() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $this->stampa('index', $dati);
    }

    public function import_start() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $dati['entities'] = $this->db->get('entity')->result_array();
        $this->stampa('import_1', $dati);
    }

    public function import_map() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $dati['import_data'] = $this->session->userdata(SESS_IMPORT_DATA);

        if (empty($dati['import_data'])) {
            redirect(base_url('importer/import_start'));
        }

        $dati['entity'] = $this->db->get_where('entity', array('entity_id' => $dati['import_data']['entity_id']))->result_array();
        $dati['fields'] = $this->db->get_where('fields', array('fields_entity_id' => $dati['import_data']['entity_id']))->result_array();
        
        //Per ogni field_ref, estraggo la mappatura della relativa entità
        foreach ($dati['fields'] as $key => $field) {
            //Se questo campo ha una relazione esterna con l'entità field_ref
            //debug($field,true);
            if ($field['fields_ref']) {
                $dati['fields'][$key][$field['fields_ref']] = $this->datab->get_entity_by_name($field['fields_ref']);
            }
        }
        
        //debug($dati,true);

        //Read csv first line
        if (($handle = fopen($dati['import_data']['csv_file']['full_path'], "r")) !== FALSE) {
            $dati['csv_head'] = fgetcsv($handle, 0, $dati['import_data']['field_separator']);
            $dati['csv_body'] = array();
            for($i=0; $i<5; $i++) {
                $dati['csv_body'][] = fgetcsv($handle, 0, $dati['import_data']['field_separator']);
            }
            fclose($handle);
        } else {
            die('Cannot open the CSV file.');
        }
        $this->stampa('import_2', $dati);
    }
    
    

    public function import_return() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $dati['count'] = $this->session->flashdata(SESS_IMPORT_COUNT);
        $dati['warnings'] = $this->session->flashdata(SESS_IMPORT_WARNINGS);
        
        $this->stampa('import_3', $dati);
    }
    
    
    
    
    
    
    

    private function stampa($view_file = NULL, $data = NULL) {
        $this->template['page'] = $this->load->view($view_file, array('dati' => $data), true);

        $this->template['head'] = $this->load->view('layout/head', array(), true);
        $this->template['header'] = $this->load->view('layout/header', array(), true);
        $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        $this->template['footer'] = $this->load->view('layout/footer', null, true);

        /*
         * Module-related assets extensions
         */
        $this->template['head'] .= $this->load->view('layout/module_css', array(), true);
        $this->template['footer'] .= $this->load->view('layout/module_js', null, true);

        //Build template
        $this->load->view('layout/main', $this->template);
    }

}