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
        $id = $this->input->post('fattura_id');
        $fattura = $this->input->post('fattura');
        $prodotti = $this->input->post('products')?: [];
        $prodottiEdit = $this->input->post('ed_products')?: [];
        
        if (count($prodotti) < 1 && count($prodottiEdit) < 1) {
            die(json_encode(array('status'=>0, 'txt'=>  'Inserisci almeno un prodotto.')));
        }
        
        
        $fattura['fatture_numero'] = (int) $fattura['fatture_numero'];
        $fattura['fatture_serie'] = trim($fattura['fatture_serie']);
        
        if ($fattura['fatture_numero'] < 1) {
            die(json_encode(array('status'=>0, 'txt'=> "Il numero fattura dev'essere un numero positivo")));
        }
        
        if (!$fattura['fatture_serie']) {
            die(json_encode(array('status'=>0, 'txt'=> "La serie di fatturazione è vuota")));
        }
        
        /*if ($this->db->get_where('fatture', array('fatture_numero' => $fattura['fatture_numero'], 'fatture_serie' => $fattura['fatture_serie']))->num_rows() > 0) {
            die(json_encode(array('status'=>0, 'txt'=> "Esiste già la fattura {$fattura['fatture_numero']}{$fattura['fatture_serie']}")));
        }*/

        $fattura['fatture_totale'] = 0;
        foreach ($prodotti as $k => $prodotto) {
            if (empty($prodotto['fatture_prodotti_quantita']) OR !is_numeric($prodotto['fatture_prodotti_quantita']) OR $prodotto['fatture_prodotti_quantita'] < 1) {
                $prodotti[$k]['fatture_prodotti_quantita'] = 1;
            }
            $fattura['fatture_totale'] += $prodotto['fatture_prodotti_quantita'] * $prodotto['fatture_prodotti_prezzo'] * (100-$prodotto['fatture_prodotti_sconto']) / 100;
        }

        foreach ($prodottiEdit as $idp => $prodotto) {
            if (empty($prodotto['fatture_prodotti_quantita']) OR !is_numeric($prodotto['fatture_prodotti_quantita']) OR $prodotto['fatture_prodotti_quantita'] < 1) {
                $prodottiEdit[$idp]['fatture_prodotti_quantita'] = 1;
            }
            $fattura['fatture_totale'] += $prodotto['fatture_prodotti_quantita'] * $prodotto['fatture_prodotti_prezzo'] * (100-$prodotto['fatture_prodotti_sconto']) / 100;
        }
        
        $this->db->trans_start();
        try {
            
            $f = $id ? $this->apilib->edit('fatture', $id, $fattura) : $this->apilib->create('fatture', $fattura);
            
            // Cancello tutti i prodotti della fattura che non sono editati
            if ($prodottiEdit && $id) {
                $this->db->where_not_in('fatture_prodotti_id', array_keys($prodottiEdit))->delete('fatture_prodotti', ['fatture_prodotti_fattura' => $id]);
            }
            
            if ($prodotti) {
                $this->apilib->createMany('fatture_prodotti', array_map(function($prodotto) use ($f) {
                    $prodotto['fatture_prodotti_iva'] = $prodotto['fatture_prodotti_iva'] ? : 22;
                    $prodotto['fatture_prodotti_fattura'] = $f['fatture_id'];
                    return $prodotto;
                }, $prodotti));
            }
            
            foreach ($prodottiEdit as $idp => $prodotto) {
                $prodotto['fatture_prodotti_iva'] = $prodotto['fatture_prodotti_iva'] ? : 22;
                $this->apilib->edit('fatture_prodotti', $idp, $prodotto);
            }
            
        } catch (Exception $ex) {
            die(json_encode(array('status'=>0, 'txt'=> $ex->getMessage())));
        }
        $this->db->trans_complete();
        die(json_encode(array('status' => 1, 'txt'=> base_url('fatture'))));
    }
    
}
