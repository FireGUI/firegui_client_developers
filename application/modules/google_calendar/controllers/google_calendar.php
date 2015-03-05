<?php

class Google_calendar extends MX_Controller {

    var $template = array();
    var $settings = NULL;
    var $client = null;
    var $service = null;

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
        
        require realpath(dirname(__FILE__)).'/../google-api-calendar/autoload.php';
        
        $this->client = new Google_Client();
        // OAuth2 client ID and secret can be found in the Google Developers Console.
        $this->client->setClientId(GOOGLEAPP_CLIENTID);
        $this->client->setClientSecret(GOOGLEAPP_SECRET);
        $this->client->setRedirectUri(base_url() . 'google_calendar/oauth2callback');
        $this->client->setAccessType('offline');
        $this->client->addScope('https://www.googleapis.com/auth/calendar');
        
        $this->service = new Google_Service_Calendar($this->client);
    }
    
    public function index() {
        $dati['current_page'] = 'module_' . MODULE_NAME;
        $dati['month'] = date('n');
        $dati['year'] = date('Y');
        
        $dati['sincronizzazione'] = $this->db->get_where('google_calendar', array('google_calendar_utente' => $this->auth->get(LOGIN_ENTITY . "_id")))->row_array();
        
        $dati['link_autorizzazione'] = $this->client->createAuthUrl();
        $dati['client'] = $this->client;
        $dati['service'] = $this->service;
        $this->stampa('index', $dati);
    }
    
    public function oauth2callback() {
        $auth_code = $this->input->get('code');
        if ($auth_code) {
            //Controllo se per questo utente è già attiva una sincronizzazione
            $sincronizzazione = $this->db->get_where('google_calendar', array('google_calendar_utente' => $this->auth->get(LOGIN_ENTITY . "_id")));
            if ($sincronizzazione->num_rows() > 0) {
                debug("Errore! E' già presente una sincronizzazione per questo utente. Rimuoverla prima di procedere...", true);
            }
            
            $accessToken = $this->client->authenticate($auth_code);
            
            $this->db->insert('google_calendar', array(
                'google_calendar_utente' => $this->auth->get(LOGIN_ENTITY . "_id"),
                'google_calendar_auth_code' => $auth_code,
                'google_calendar_token' => $accessToken,
            ));
            redirect('google_calendar');
        } else {
            debug("Errore! auth_code mancante!", true);
        }
    }
    public function save_calendar($google_calendar_id) {
        $this->db->where('google_calendar_id', $google_calendar_id)->update('google_calendar', array('google_calendar_calendario' => $this->input->post('calendario')));
        echo json_encode(array('status'=>1, 'txt'=>base_url('google_calendar')));
        die();
    }
    public function delete_sincronizzazione($google_calendar_id) {
        $this->db->where('google_calendar_id', $google_calendar_id)->delete('google_calendar');
        redirect('google_calendar');
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