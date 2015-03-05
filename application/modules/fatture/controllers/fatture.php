<?php

class Fatture extends MX_Controller {

    var $template = array();
    var $settings = NULL;

    public function __construct() {
        parent::__construct();
        if ($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Modulo non installato');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Accesso vietato');
        }

        $this->load->helper('fatture');
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }

    public function index() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $dati['fatture'] = $this->db->get('fatture')->result_array();
        $this->stampa('index', $dati);
    }
    
    
    
    public function crea() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $dati['customers'] = $this->datab->get_entity_preview_by_name(ENTITY_CUSTOMERS);
        $this->stampa('make', $dati);
    }

    public function modifica($offer_id) {
        $dati['current_page'] = 'module_' . MODULE_NAME;

        $dati['offer_id'] = $offer_id;
        $dati['offer'] = $this->db->get_where('offers', array('offers_id'=>$offer_id))->row_array();
        $dati['offer_products'] = $this->db->get_where('offers_products', array('offers_products_offer_id' => $offer_id))->result_array();
        $dati['offer_products_accessories'] = $this->db->get_where('offers_products_accessories', array('offers_products_accessories_offer_id' => $offer_id))->result_array();
        
        $dati['users'] = $this->datab->get_entity_preview_by_name(ENTITY_USERS);
        $dati['customers'] = $this->datab->get_entity_preview_by_name(ENTITY_CUSTOMERS);
        $dati['products'] = $this->datab->get_entity_preview_by_name(ENTITY_PRODUCTS);
        $dati['mandanti'] = $this->datab->get_entity_preview_by_name('mandanti');
        $this->stampa('make', $dati);
    }
    
    
    public function mailto($offer_id) {
        
        $dati['offer_id'] = $offer_id;
        $dati['offer'] = $this->db->get_where('offers', array('offers_id'=>$offer_id))->row_array();
        $dati['offer_realnumber'] = get_offer_number($dati['offer']['offers_number'], $dati['offer']['offers_date_start']);
        
        $dati['offer_products'] = $this->db->get_where('offers_products', array('offers_products_offer_id' => $offer_id))->result_array();
        $dati['offer_products_accessories'] = $this->db->get_where('offers_products_accessories', array('offers_products_accessories_offer_id' => $offer_id))->result_array();
        
        // Ottieni email ufficio acquisti
        $nominativo = $this->db->limit(1)->get_where('nominativi', array('nominativi_azienda' => $dati['offer']['offers_customer'], 'nominativi_ruolo' => 1));
        $mandante = $this->db->limit(1)->get_where('mandanti', array('mandanti_id' => $dati['offer']['offers_mandante']));
        
        
        $dati['defaults'] = array(
            'mail_from' => $this->auth->get(LOGIN_USERNAME_FIELD),
            'mail_to' => ($nominativo->num_rows() > 0? $nominativo->row()->nominativi_email: NULL),
            'mail_cc' => ($mandante->num_rows() > 0 && isset($mandante->row()->mandanti_email_cc)? $mandante->row()->mandanti_email_cc: NULL),
            'mail_subject' => "Offerta N.{$dati['offer_realnumber']} del ".date('d/m/Y', strtotime($dati['offer']['offers_date_creation'])),
        );
        
        
        
        
        $this->load->view('email_send', compact('dati'));
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