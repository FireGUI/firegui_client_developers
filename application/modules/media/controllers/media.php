<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Media extends MX_Controller {
    

    var $template = array();
    var $settings = NULL;
    
    public function __construct() {
        parent::__construct();
        
        if($this->auth->guest()) {
            redirect('access');
        }
        
        /**
        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Module not installed');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Access forbidden');
        }
        */
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }
    
    

    
    
    public function upload() {
        /**
         * Sono interessato solo a quelle entità per cui è definita
         * l'entità media_{nome_entità}
         */
        $data['current_page'] = 'module_media';
        $data['entities'] = array();
        $entities = $this->db->get_where('entity', array('entity_type'=>ENTITY_TYPE_DEFAULT))->result_array();
        foreach ($entities as $entity) {
            $media_table = 'media_'.$entity['entity_name'];
            $has_media_table = $this->db->get_where('entity', array('entity_name'=>$media_table))->num_rows();
            if($has_media_table) {
                $data['entities'][] = $entity;
            }
        }
        $this->stampa('upload', $data);
    }
    
    
    
    
    public function modal_upload($media_entity_name, $record_id) {
        if(strpos($media_entity_name, 'media_') !== 0) {
            // Se l'entità non inizia con media allora devo valutare se esiste una media entity
            $media_entity = $this->datab->get_entity_by_name("media_{$media_entity_name}");
            if(!empty($media_entity)) {
                $entity_name = $media_entity_name;
            }
        } else {
            // Se l'entità inizia con media devo estrarre il nome dell'entità effettiva
            $entity_name = preg_replace('/media_/', '', $media_entity_name, 1);
        }
        
        if(empty($entity_name)) {
            $data = array('title' => 'Entità media non valida', 'content' => '');
        } else {
            
            $dati['entity'] = $this->datab->get_entity_by_name($entity_name);
            $dati['value_id'] = $record_id;
            
            $previews = $this->datab->get_entity_preview_by_name($entity_name, "{$entity_name}_id = '{$record_id}'", 1);
            $preview = isset($previews[$record_id])? $previews[$record_id]: NULL;
            
            $data = array(
                'title' => $preview? "Carica nuovi media per {$preview}": 'Carica nuovi media',
                'content' => $this->load->view('modal_upload', compact('dati'), TRUE)
            );
        }
        
        $this->load->view('layout/modal_container', $data);
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