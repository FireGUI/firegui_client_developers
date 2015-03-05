<?php

class Offers extends MX_Controller {

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

        $this->load->helper('offers');
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }

    public function index() {
        $dati['current_page'] = 'module_' . MODULE_NAME;

        $dati['offers'] = $this->db->
                        from('offers')->
                        join(ENTITY_CUSTOMERS, 'offers_customer = '.ENTITY_CUSTOMERS.'_id', 'left')->
                        join(ENTITY_USERS, 'offers_user = '.ENTITY_USERS.'_id', 'left')->
                        order_by('offers_id DESC')->
                        get()->result_array();
        
        $dati['offers_expired'] = $this->db->
                        from('offers')->
                        join(ENTITY_CUSTOMERS, 'offers_customer = '.ENTITY_CUSTOMERS.'_id', 'left')->
                        join(ENTITY_USERS, 'offers_user = '.ENTITY_USERS.'_id', 'left')->
                        where('offers_date_end < NOW()')->
                        order_by('offers_date_end DESC')->
                        get()->result_array();
        
        $dati['users'] = $this->datab->get_entity_preview_by_name(ENTITY_USERS);
        $dati['customers'] = $this->datab->get_entity_preview_by_name(ENTITY_CUSTOMERS);

        $this->stampa('index', $dati);
    }
    public function expired_offers() {
        $dati['current_page'] = 'module_' . MODULE_NAME;

        $dati['offers'] = $this->db->
                        from('offers')->
                        join(ENTITY_CUSTOMERS, 'offers_customer = '.ENTITY_CUSTOMERS.'_id', 'left')->
                        join(ENTITY_USERS, 'offers_user = '.ENTITY_USERS.'_id', 'left')->
                        order_by('offers_id DESC')->
                        get()->result_array();
        
        $dati['offers_expired'] = $this->db->
                        from('offers')->
                        join(ENTITY_CUSTOMERS, 'offers_customer = '.ENTITY_CUSTOMERS.'_id', 'left')->
                        join(ENTITY_USERS, 'offers_user = '.ENTITY_USERS.'_id', 'left')->
                        where('offers_date_end < NOW()')->
                        order_by('offers_date_end DESC')->
                        get()->result_array();
        
        $dati['users'] = $this->datab->get_entity_preview_by_name(ENTITY_USERS);
        $dati['customers'] = $this->datab->get_entity_preview_by_name(ENTITY_CUSTOMERS);

        $this->stampa('expired_offers', $dati);
    }

    public function create_offer() {
        $dati['current_page'] = 'module_' . MODULE_NAME;

        $dati['offer_number'] = $this->db->select_max('offers_number', 'number')->get('offers')->row()->number + 1;
        $dati['users'] = $this->datab->get_entity_preview_by_name(ENTITY_USERS);
        $dati['customers'] = $this->datab->get_entity_preview_by_name(ENTITY_CUSTOMERS);
        $dati['products'] = $this->datab->get_entity_preview_by_name(ENTITY_PRODUCTS);
        $this->stampa('make', $dati);
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