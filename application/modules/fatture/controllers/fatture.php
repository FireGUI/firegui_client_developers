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
        $dati['fatture'] = $this->apilib->search('fatture', [], null, null, 'fatture_data_creazione', 'desc');
        if ($dati['fatture']) {
            $dati['clienti'] = $this->crmentity->getEntityPreview(FATTURE_E_CUSTOMERS, sprintf('%s_id IN (%s)', FATTURE_E_CUSTOMERS, implode(',', array_key_map($dati['fatture'], 'fatture_cliente'))));
        } else {
            $dati['clienti'] = [];
        }
        
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
    
    public function edit($id = null) {
        if (!$id) {
            show_404();
        }
        $dati = $this->getDataFattura($id);
        
        if (!$dati['fattura']) {
            show_404();
        }
        
        $dati['serie'] = unserialize(FATTURAZIONE_SERIE_SUFFIX);
        $dati['metodi_pagamento'] = unserialize(FATTURAZIONE_METODI_PAGAMENTO);
        $this->stampa('make', $dati);
    }

    
    private function getDataFattura($id = null) {
        
        $fattura = $id? $this->apilib->view('fatture', $id, 1): array();
        if ($fattura) {
            $prodotti = $this->db->get_where('fatture_prodotti', ['fatture_prodotti_fattura' => $id])->result_array();
        }
        
        $data = array(
            'fatture_numero' => isset($fattura['fatture_numero'])? $fattura['fatture_numero']: null,
            'fatture_serie' => isset($fattura['fatture_serie'])? $fattura['fatture_serie']: null,
            'fatture_cliente' => isset($fattura['fatture_cliente'])? $fattura['fatture_cliente']: null,
            'fatture_scadenza_pagamento' => empty($fattura['fatture_scadenza_pagamento'])? null: dateFormat($fattura['fatture_scadenza_pagamento']),
            'fatture_metodo_pagamento' => isset($fattura['fatture_metodo_pagamento'])? $fattura['fatture_metodo_pagamento']: null,
            'fatture_pagato' => isset($fattura['fatture_pagato']) && $fattura['fatture_pagato'] === 't',
            'fatture_note' => isset($fattura['fatture_note'])? $fattura['fatture_note']: null,
        );
        
        foreach ($data as $k => $v) {
            if (is_null($v) && ($v = $this->input->get($k))) {
                $data[$k] = $v;
            }
        }
        
        $data['fattura'] = $fattura;
        $data['prodotti'] = empty($prodotti) ? []: $prodotti;
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