<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Db_ajax extends MX_Controller {
    

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
        
        $post = $this->input->post();
        if( ! $post['value']) {
            die('Seleziona un valore.');
        }
        
        if( ! $post['entity_id']) {
            die('Seleziona entità.');
        }
        
        $this->load->library('upload', array(
            'upload_path' => './uploads/',
            'allowed_types' => '*',
            'max_size' => '50000',
            'encrypt_name' => false,
        ));
        
        $uploaded = $this->upload->do_upload('file');
        if( ! $uploaded) {
            debug($this->upload->display_errors());
            die();
        }
        
        $up_data = $this->upload->data();
        
        $file_name = $up_data['file_name'];
        $entity = $this->datab->get_entity($post['entity_id']);
        $id = $post['value'];
        
        // Unsetto il $_FILES['file'] in modo che non dia errore nell'apilib
        unset($_FILES['file']);
        
        
        /**
         * Media entity
         */
        $media_entity_name = 'media_'.$entity['entity_name'];
        $media_field_id = $media_entity_name.'_'.$entity['entity_name'].'_id';
        $media_field_file = $media_entity_name.'_file';
        $media_entity = $this->datab->get_entity_by_name($media_entity_name);
        if(empty($media_entity)) {
            die("{$media_entity_name} entity required.");
        }
        
        $n_fields = $this->db->where_in('fields_name', array($media_field_file, $media_field_id))->where('fields_entity_id', $media_entity['entity_id'])->count_all_results('fields');
        if($n_fields < 2) {
            die("{$media_field_id} e {$media_field_file} fields required for the entity {$media_entity_name}.");
        }
        
        try {
            $this->apilib->create($media_entity_name, [$media_field_id => $id, $media_field_file => $file_name]);
            //$this->apilib->create($media_entity_name, [$media_field_id => $id, $media_field_file => $file_name]);
            //$this->db->insert($media_entity_name, array( $media_field_id => $id, $media_field_file => $file_name ));
        } catch (Exception $ex) {
            die($ex->getMessage());
        }
    }
}