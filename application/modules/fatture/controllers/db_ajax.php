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

        $this->load->helper('offers');
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }
    
    
    
    function create_new() {
        
        //debug($_POST,1);
        $this->load->library('form_validation');
        $this->form_validation->set_rules('offer[offers_customer]', t('cliente'), 'required');
        $this->form_validation->set_rules('offer[offers_user]', t('agente'), 'required');
        $this->form_validation->set_rules('offer[offers_number]', t('numero offerta'), 'required');
        $this->form_validation->set_rules('offer[offers_date]', t('range di validità'), 'required');
        $validation_result = $this->form_validation->run();
        
        if(!$validation_result) {
            echo json_encode(array('status'=>0, 'txt'=>  validation_errors()));
            die();
        }
        
        
        $data = $this->input->post();
        $offer_id = $this->input->post('offer_id');
        $offer = $data['offer'];
//        $offer_products = array_filter($data['products'],function($product) { return !empty($product['offers_products_product_id']); });
        $offer_products = $data['products'];
        $product_to_keep = $this->input->post('offers_products');   // Pieno solo se siamo in modifica
        
        if(empty($offer_products) && empty($product_to_keep)) {
            echo json_encode(array('status'=>0, 'txt'=>  'Inserisci almeno un prodotto.'));
            die();
        }
        
        /*if(!empty($offer_products) && is_array($offer_products)) {  // I prodotti possono essere vuoti sse sto modificando l'offerta - condizione già controllata
            foreach($offer_products as $product) {
                if( ! $product['offers_products_product_id']) {
                    echo json_encode(array('status'=>0, 'txt'=>  'Prodotti non inseriti correttamente.'));
                    die();
                }
            }
        }*/
        
        
        
        $date_range = explode(' - ', $offer['offers_date']);
        if(count($date_range) != 2) {
            echo json_encode(array('status'=>0, 'txt'=>  'Data formattata non correttamente.'));
            die();
        }
        
        unset($offer['offers_date']);
        $offer['offers_date_start'] = $date_range[0];
        $offer['offers_date_end'] = $date_range[1];
        
        if(!$offer_id) {
            $offer['offers_date_creation'] = date('d/m/Y');
        }
        
        
        if(empty($offer['offers_discount'])) {
            unset($offer['offers_discount']);
        }
        
        if(empty($offer['offers_esito'])) {
            unset($offer['offers_esito']);
        }
        
        if(empty($offer['offers_mandante'])) {
            unset($offer['offers_mandante']);
        }
        
        /*
         * Check the offer number
         */
        if(isset($offer['offers_number'])) {
            if($offer_id) {
                $this->db->where('offers_id <>', $offer_id);
            }
            $exists = $this->db->get_where('offers', array('offers_number'=>$offer['offers_number']))->num_rows();
            if($exists) {
                echo json_encode(array('status'=>0, 'txt'=>t('Numero di offerta già utilizzato.')));
                die();
            }
        } else {
            $offer['offers_number'] = $this->db->select_max('offers_number', 'number')->get('offers')->row()->number+1;
        }
        
        if($offer_id) {
            $this->db->update('offers', $offer, array('offers_id' => $offer_id));
        } else {
            $this->db->insert('offers', $offer);
            $offer_id = $this->db->insert_id();
        }
        
        
        
        // Elimino dall'offerta tutti i prodotti che non stanno in offers_products
        if(!empty($product_to_keep) && is_array($product_to_keep)) {
            $this->db->where_not_in('offers_products_id', $product_to_keep);
        }
        $this->db->delete('offers_products', array('offers_products_offer_id' => $offer_id));
        
        // Elimino dagli accessori tutti quelli relativi ad un prodotto eliminato
        $this->db->where("offers_products_accessories_product_id NOT IN (SELECT offers_products_product_id FROM offers_products WHERE offers_products_offer_id = '{$offer_id}')");
        $this->db->delete('offers_products_accessories', array('offers_products_accessories_offer_id' => $offer_id));
        
        
        /* GESTIONE CON PRODOTTI E ACCESSORI PRODOTTO DA ENTITA'
        if(!empty($offer_products) && is_array($offer_products)) {
            foreach($offer_products as $product) {
                $product_record = $this->db->get_where(ENTITY_PRODUCTS, array(ENTITY_PRODUCTS.'_id'=>$product['offers_products_product_id']))->row_array();
                if(!empty($product_record)) {
                    $product['offers_products_offer_id'] = $offer_id;
                    $product['offers_products_name'] = $product_record[ENTITY_PRODUCTS_FIELD_NAME];
                    $product['offers_products_code'] = $product_record[ENTITY_PRODUCTS_FIELD_CODE];
                    if( !empty($product['offers_products_price'])) {
                        $product['offers_products_price'] = $product_record[ENTITY_PRODUCTS_FIELD_PRICE] > 0? $product_record[ENTITY_PRODUCTS_FIELD_PRICE]: 0;
                    }

                    $product['offers_products_price'] = (double) $product['offers_products_price'];
                    if(empty($product['offers_products_quantity'])) {
                        $product['offers_products_quantity'] = 1;
                    }
                    $this->db->insert('offers_products', $product);

                    $product_id = $product['offers_products_product_id'];

                    $accessories = $this->input->post('product_accessory');
                    if(array_key_exists($product_id, $accessories)) {
                        $related_accessories = $accessories[$product_id];

                        foreach($related_accessories as $accessory) {
                            if(! empty($accessory['sel_qty'])) {
                                $prod_acc = $this->db->get_where('prodotti_accessori', array('prodotti_accessori_id'=>$accessory['id']))->row_array();
                                if( ! empty($prod_acc)) {
                                    $data_acc = array(
                                        'offers_products_accessories_offer_id' => $offer_id,
                                        'offers_products_accessories_product_id' => $product_id,
                                        'offers_products_accessories_quantity' => $accessory['sel_qty'],
                                        'offers_products_accessories_name' => $prod_acc['prodotti_accessori_descrizione'],
                                        'offers_products_accessories_code' => $accessory['code'],
                                        'offers_products_accessories_price' => (double) $accessory['price'],
                                    );

                                    $this->db->insert('offers_products_accessories', $data_acc);
                                }
                            }
                        }
                    }


                }
            }
        }
         */
        if(!empty($offer_products) && is_array($offer_products)) {
            foreach($offer_products as $product) {
                $product['offers_products_offer_id'] = $offer_id;
                $product['offers_products_product_id'] = 0;
                if(empty($product['offers_products_price'])) {
                    $product['offers_products_price'] = 0;
                }

                $product['offers_products_price'] = (double) $product['offers_products_price'];
                if(empty($product['offers_products_quantity']) || !is_numeric($product['offers_products_quantity'])) {
                    $product['offers_products_quantity'] = 1;
                }
                
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
        
        
        
        // Prepara PDF

        $pdf_data['offer'] = $this->db->from('offers')->
                        join(ENTITY_CUSTOMERS, 'offers_customer = '.ENTITY_CUSTOMERS.'_id', 'left')->
                        join(ENTITY_USERS, 'offers_user = '.ENTITY_USERS.'_id', 'left')->
                        join(ENTITY_CITY, CUSTOMER_CITY.' = '.CITY_ID, 'left')->
                        join(ENTITY_PROV, CUSTOMER_PROV.' = '.PROV_ID, 'left')->
                        where('offers_id', $offer_id)->order_by('offers_id DESC')->get()->row_array();
        
        $pdf_data['products'] = $this->db->get_where('offers_products', array('offers_products_offer_id'=>$offer_id))->result_array();
        $prices = $this->db->query("
                SELECT offers_products_offer_id AS offer_id, offers_products_product_id AS product_id, (SUM(offers_products_price)+SUM(access.price))*SUM(offers_products_quantity) AS price
                FROM offers_products
                LEFT JOIN (
                    SELECT offers_products_accessories_offer_id AS offer_id, offers_products_accessories_product_id AS product_id, SUM(offers_products_accessories_price*offers_products_accessories_quantity) AS price
                    FROM offers_products_accessories
                    GROUP BY offers_products_accessories_offer_id, offers_products_accessories_product_id
                ) AS access ON (offers_products.offers_products_offer_id = access.offer_id AND offers_products.offers_products_product_id = access.product_id)
                WHERE offers_products_offer_id = '$offer_id'
                GROUP BY offers_products_offer_id, offers_products_product_id
        ")->result_array();

        foreach($pdf_data['products'] as $k=>$product) {
            foreach($prices as $price) {
                if($price['product_id'] == $product['offers_products_product_id']) {
                    $pdf_data['products'][$k]['offers_products_price'] = $price['price'];
                }
            }
        }
        
        ob_end_clean();
        $content = $this->load->view('pdf_offer', array('dati' => $pdf_data), true);
        require_once('./class/html2pdf/html2pdf.class.php');
        $html2pdf = new HTML2PDF('P', 'A4', 'it');
        $html2pdf->pdf->SetDisplayMode('fullpage');
        $html2pdf->WriteHTML($content);
        
        $pdf_path = "pdf/offerta_{$offer_id}.pdf";
        $html2pdf->Output($pdf_path, 'F');
        
        
        //echo json_encode(array('status'=>1, 'txt' => base_url("offers_ndr/pdf/generate/{$offer_id}")));
        echo json_encode(array('status'=>1, 'txt' => base_url("offers_ndr")));
    }
    
    
    
    function prodotti_mandante($mandante_id=NULL) {
        if($mandante_id) {
            echo json_encode($this->datab->get_entity_preview_by_name(ENTITY_PRODUCTS, "prodotti_mandante={$mandante_id}"));
        } else {
            echo json_encode($this->datab->get_entity_preview_by_name(ENTITY_PRODUCTS));
        }
    }
    
    
    
    function get_product_accessories($product_id) {
        $accessories = $this->db->get_where('prodotti_accessori', array('prodotti_accessori_prodotto' => $product_id))->result_array();
        echo json_encode($accessories);
    }
    
    
    function get_product_prices($product_id, $customer_id='') {
        $prodotti = $this->db->get_where('prodotti', array('prodotti_id' => $product_id))->row_array();
        
        if($customer_id) {
            $sconti = $this->db->get_where('sconti', array('sconti_mandante' => $prodotti['prodotti_mandante'], 'sconti_azienda' => $customer_id))->row_array();
        }
        
        if(isset($prodotti['prodotti_prezzo']) && !empty($sconti)) {
            $sconto = trim(str_replace('%', '', $sconti['sconti_sconto']));
            if(is_numeric($sconto)) {
                $prodotti['prodotti_prezzo'] = $prodotti['prodotti_prezzo']*(100-$sconto)/100;
            }
        }
        
        echo json_encode($prodotti);
    }
    
    
    
    
    public function mailto($offer_id) {
        $post = $this->input->post();
        
        if(empty($post['mail_from']) || empty($post['mail_to']) || empty($post['mail_subject'])) {
            die();
        } else {
            $this->db->insert('mail_queue', array(
                'mail_subject' => $post['mail_subject'],
                'mail_body' => $post['mail_text'],
                'mail_body' => $post['mail_text'],
                'mail_to' => $post['mail_to'],
            ));
            
            echo json_encode(array('status' => 2));
        }
    }
    
}