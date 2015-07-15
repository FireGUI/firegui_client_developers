<?php


class Documents extends MX_Controller {
    

    var $template = array();
    var $settings = NULL;
    
    public function __construct() {
        parent::__construct();
        
        if($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Module not installed');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Access forbidden');
        }
        
        $this->load->model('docs');
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }
    
    
    
    public function index() {
        $dati['current_page'] = 'module_'.MODULE_NAME;
        $this->stampa('elfinder', $dati);
    }

    
    
    public function init_elfinder() {
        $this->load->helper('path');
        $opts = array(
            // 'debug' => true, 
            'roots' => array(
                array(
                    'driver' => 'LocalFileSystem',
                    'path' => set_realpath('uploads/file_manager'),
                    'URL' => site_url('uploads/file_manager') . '/'
                )
            )
        );
        
        
        /**
         * Crea le directories dove servono
         */
        foreach ($opts['roots'] as $root) {
            
            $path = $root['path'];
            
            if(!is_dir($path)) {
                mkdir($path);
            }
            
        }
        
        $this->load->library('elfinder_lib', $opts);
    }
    
    
    
    
    
    
    

    private function stampa($view_file=NULL, $data=NULL) {
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