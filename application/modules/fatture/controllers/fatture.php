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

        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }

    /**
     * Lista fatture
     */
    public function index() {
        $dati['fatture'] = $this->db->get('fatture')->result_array();
        $this->stampa('index', $dati);
    }

    /**
     * Crea nuova fattura
     */
    public function crea() {
        $dati = $this->getDataFattura();
        $dati['serie'] = unserialize(FATTURAZIONE_SERIE_SUFFIX);
        $dati['metodi_pagamento'] = unserialize(FATTURAZIONE_METODI_PAGAMENTO);
        $this->stampa('make', $dati);
    }

    
    private function getDataFattura($id = null) {
        
        $fattura = $id? $this->apiliv->view('fatture', $id): array();
        
        $data = array(
            'fatture_cliente' => isset($fattura['fatture_cliente'])? $fattura['fatture_cliente']: null,
            'fatture_scadenza_pagamento' => empty($fattura['fatture_scadenza_pagamento'])? null: dateFormat($fattura['fatture_scadenza_pagamento']),
            'fatture_metodo_pagamento' => null,
            'fatture_pagato' => isset($fattura['fatture_pagato']) && $fattura['fatture_pagato'] === 't'
        );
        
        foreach ($data as $k => $v) {
            if (is_null($v) && ($v = $this->input->get($k))) {
                $data[$k] = $v;
            }
        }
        
        $data['fattura'] = $fattura;
        $data['id'] = $id?:null;
        
        return $data;
    }
    
    
    private function stampa($view_file = NULL, $data = NULL) {
        
        if (!isset($data['current_page'])) {
            $data['current_page'] = 'module_' . MODULE_NAME;
        }
        
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