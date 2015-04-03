<?php

class Db_ajax extends MX_Controller {
    
    
    

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
    
    
    
    function create() {
        
        $fattura = $this->input->post('fattura');
        $prodotti = $this->input->post('products');
        
        if (count($prodotti) < 1) {
            die(json_encode(array('status'=>0, 'txt'=>  'Inserisci almeno un prodotto.')));
        }
        
        
        $fattura['fatture_numero'] = (int) $fattura['fatture_numero'];
        $fattura['fatture_serie'] = trim($fattura['fatture_serie']);
        
        if ($fattura['fatture_numero'] > 0) {
            die(json_encode(array('status'=>0, 'txt'=> "Il numero fattura dev'essere un numero positivo")));
        }
        
        if (!$fattura['fatture_serie']) {
            die(json_encode(array('status'=>0, 'txt'=> "La serie di fatturazione è vuota")));
        }
        
        if ($this->db->get_where('fatture', array('fatture_numero' => $fattura['fatture_numero'], 'fatture_serie' => $fattura['fatture_serie']))->num_rows() > 0) {
            die(json_encode(array('status'=>0, 'txt'=> "Esiste già la fattura {$fattura['fatture_numero']}{$fattura['fatture_serie']}")));
        }
        
        $this->db->trans_start();
        try {
            $f = $this->apilib->create('fatture', $fattura);
            $this->apilib->createMany('fatture_prodotti', array_map(function($prodotto) use ($f) {
                $prodotto['fatture_prodotti_fattura'] = $f['fatture_id'];
                if (empty($prodotto['fatture_prodotti_quantita']) OR !is_numeric($prodotto['fatture_prodotti_quantita']) OR $prodotto['fatture_prodotti_quantita'] < 1) {
                    $prodotto['fatture_prodotti_quantita'] = 1;
                }
                return $prodotto;
            }, $prodotti));
        } catch (Exception $ex) {
            die(json_encode(array('status'=>0, 'txt'=> $ex->getMessage())));
        }
        $this->db->trans_complete();
    }
    
}