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

        $this->load->helper('offers');
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }
    
    
    
    function create_new() {
        
        $this->load->library('form_validation');
        $this->form_validation->set_rules('offer[offers_customer]', t('customer'), 'required');
        $this->form_validation->set_rules('offer[offers_user]', t('user'), 'required');
        $this->form_validation->set_rules('offer[offers_number]', t('offer number'), 'required');
        $validation_result = $this->form_validation->run();
        
        if(!$validation_result) {
            echo json_encode(array('status'=>0, 'txt'=>  validation_errors()));
            die();
        }
        
        
        $data = $this->input->post();
        $offer = $data['offer'];
        $offer_products = $data['products'];
        
        if(!empty($offer['offers_date'])) {
            $date_range = explode(' - ', $offer['offers_date']);
            $offer['offers_date_start'] = $date_range[0];
            $offer['offers_date_end'] = $date_range[1];
        }
        unset($offer['offers_date']);
        
        if(empty($offer['offers_discount'])) {
            unset($offer['offers_discount']);
        }
        
        /*
         * Check the offer number
         */
        if(isset($offer['offers_number'])) {
            $exists = $this->db->get_where('offers', array('offers_number'=>$offer['offers_number']))->num_rows();
            if($exists) {
                echo json_encode(array('status'=>0, 'txt'=>t('The offer number provided is already in use.')));
                die();
            }
        } else {
            $offer['offers_number'] = $this->db->select_max('offers_number', 'number')->get('offers')->row()->number+1;
        }
        
        $offer['offers_date_creation'] = date('d/m/Y');
        $this->db->insert('offers', $offer);
        $offer_id = $this->db->insert_id();
        
        foreach($offer_products as $product) {
            $product_record = $this->db->get_where(ENTITY_PRODUCTS, array(ENTITY_PRODUCTS.'_id'=>$product['offers_products_product_id']))->row_array();
            if(!empty($product_record)) {
                $product['offers_products_offer_id'] = $offer_id;
                $product['offers_products_name'] = $product_record[ENTITY_PRODUCTS_FIELD_NAME];
                $product['offers_products_code'] = $product_record[ENTITY_PRODUCTS_FIELD_CODE];
                $product['offers_products_price'] = $product_record[ENTITY_PRODUCTS_FIELD_PRICE];
                $this->db->insert('offers_products', $product);
            }
        }
        
        
        
        if(ENTITY_TASK) {
            $data = array();
            
            if(TASK_CUSTOMER) {
                $data[TASK_CUSTOMER] = $offer['offers_customer'];
            }
            
            if(TASK_DATE) {
                $data[TASK_DATE] = $offer['offers_date_end'];
            }
            
            if(TASK_TEXT) {
                $data[TASK_TEXT] = $offer['offers_notes']?
                        "Scadenza automatica offerta.\nNote:\n{$offer['offers_notes']}":
                        "Scadenza automatica offerta.";
            }
            
            if(TASK_USER) {
                $data[TASK_USER] = $offer['offers_user'];
            }
            
            if(!empty($data)) {
                $this->db->insert('tasks', $data);
            }
        }
        
        echo json_encode(array('status'=>2));
    }
    
}