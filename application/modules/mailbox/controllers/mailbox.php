<?php

class Mailbox extends MX_Controller {

    var $template = array();
    var $settings = NULL;
    
    public function __construct() {
        parent::__construct();
        
        if($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed('mailbox')) {
            die('Module not installed');
        }

        if (!$this->datab->module_access('mailbox')) {
            die('Access forbidden');
        }
        
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }
    
    
    
    public function index() {
        $data['configs'] = $this->apilib->search('mailbox_configs', array('mailbox_configs_user' => $this->auth->get('id')));
        $this->stampa('index', $data);
    }
    
    public function form($id = null) {
        $data['edit'] = !is_null($id) && is_numeric($id);
        $data['address'] = $data['edit']? $this->apilib->view('mailbox_configs', $id): null;
        $this->load->view('partials/form', array('data' => $data));
    }
    
    public function folders($id = null) {
        
        $this->load->model('imap_mailbox');
        
        
        $data = array(
            'config_id' => $id,
            'folders' => $this->imap_mailbox->listMailboxFolders($id)
        );
        
        
        $this->load->view('partials/folders', array('data' => $data));
    }
    
    public function address($id = null) {
        
        if ($id) {
            $configs = $this->apilib->create('mailbox_configs', $this->input->post('configs'));
        } else {
            $this->apilib->edit('mailbox_configs', $id, $this->input->post('configs'));
            $configs = $this->apilib->view('mailbox_configs', $id);
        }
        echo json_encode(array('status' => 2));
    }

    
    
    

    private function stampa($view_file=NULL, $data=NULL) {
        
        $data['current_page'] = empty($data['current_page'])?'module_mailbox': $data['current_page'];
        
        $this->template['page'] = $this->load->view($view_file, array('dati'=>$data), true);
        
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
