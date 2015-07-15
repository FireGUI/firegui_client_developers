<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Messages extends MX_Controller {
    
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

        $this->settings = $this->db->get('settings')->row_array();
    }
    
    
    
    public function index() {
        $this->inbox();
    }
    
    
    public function view($message_id=null) {
        if(!$message_id) {
            show_404();
        }
        
        $dati['view'] = $message_id;
        $this->inbox($dati);
    }
    
    
    
    public function inbox($dati=array()) {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $this->stampa('inbox', $dati);
    }
    
    
    
    public function send() {
        $data = $this->input->post();
        if(!$data[MESSAGES_TABLE_JOIN_FIELD]) {
            die(json_encode(array('status'=>t('non hai inserito un destinatario',1))));
        }
        
        if(!$data[MESSAGES_TABLE_TEXT_FIELD]) {
            die(json_encode(array('status'=>t('inserisci un messaggio',1))));
        }
        
        $data[MESSAGES_TABLE_FROM_FIELD] = $this->auth->get(MESSAGES_USER_TABLE.'_id');
        $this->db->insert(MESSAGES_TABLE, $data);
        echo json_encode(array('status'=>2));
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