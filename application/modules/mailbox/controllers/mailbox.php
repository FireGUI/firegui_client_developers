<?php

class Mailbox extends MX_Controller {

    var $template = array();
    var $settings = NULL;
    
    public function __construct() {
        parent::__construct();
        
        if($this->auth->guest() && stripos(uri_string(), 'loadMails') === false) {
            redirect('access');
        }

        if (!$this->datab->module_installed('mailbox')) {
            die('Module not installed');
        }

        if (!$this->datab->module_access('mailbox') && stripos(uri_string(), 'loadMails') === false) {
            die('Access forbidden');
        }
        
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
        $this->load->model('imap_mailbox');
    }
    
    
    
    public function index() {
        $data['configs'] = $this->imap_mailbox->getUserConfigs($this->auth->get('id'));
        $this->stampa('index', $data);
    }
    
    public function form($id = null) {
        $data['edit'] = !is_null($id) && is_numeric($id);
        $data['address'] = $data['edit']? $this->imap_mailbox->getConfig($id): null;
        $this->load->view('partials/form', array('data' => $data));
    }
    
    public function folders($id = null) {
        $data = array(
            'config_id' => $id,
            'folders' => $this->imap_mailbox->listMailboxFolders($id),
            'registered' => $this->imap_mailbox->listRegisteredFolders($id),
        );
        $this->load->view('partials/folders', array('data' => $data));
    }
    
    public function save_folders() {
        $configsId = $this->input->post('configs');
        $folders = (array) $this->input->post('folders');
        
        $fCreated = array();
        foreach (array_filter($folders) as $folder) {
            $f = $this->imap_mailbox->upsertFolder($configsId, $folder);
            $fCreated[] = $f['mailbox_configs_folders_name'];
        }
        
        if (!empty($fCreated)) {
            $this->db->where_not_in('mailbox_configs_folders_name', $fCreated);
        }
        
        $this->db->delete('mailbox_configs_folders', array('mailbox_configs_folders_config' => $configsId));
        echo json_encode(array('status' => 2));
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

    
    public function loadMails() {
        ignore_user_abort(true);
        set_time_limit(0);
        $updations = $this->imap_mailbox->fetchEmailsFromConfigs();
        $lines = [];
        foreach ($updations as $folderId => $emailAdded) {
            $folder = $this->db
                    ->join('mailbox_configs', 'mailbox_configs_folders_config = mailbox_configs_id')
                    ->get_where('mailbox_configs_folders', array('mailbox_configs_folders_id' => $folderId))->row();
            
            $lines[] = 'Added ' . $emailAdded . ' e-mails to ' . ($folder? "`{$folder->mailbox_configs_folders_alias}` [addr: {$folder->mailbox_configs_email}]": 'unknown folder');
            
        }
        
        echo '<pre>' . implode(PHP_EOL, $lines) . '</pre>';
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
