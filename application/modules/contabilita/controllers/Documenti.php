<?php

class Documenti extends MX_Controller
{

    function __construct()
    {
        parent:: __construct();
        $this->settings = $this->db->get('settings')->row_array();
        $this->contabilita_settings = $this->apilib->searchFirst('documenti_contabilita_settings');
    }

    public function test()
    {
        die('test');
    }

    public function create_document()
    {

        $input = $this->input->post();

        //debug($input, true);

        if (!empty($input['documento_id'])) {
            //die('NON GESTITO SALVATAGGIO IN MODIFICA.... Da completare!');
        }

        $this->load->library('form_validation');

        $this->form_validation->set_rules('documenti_contabilita_tipo', 'Tipo documento', 'required');
        $this->form_validation->set_rules('documenti_contabilita_numero', 'Numero documento', 'required');
        $this->form_validation->set_rules('documenti_contabilita_data_emissione', 'data emissione', 'required');

        //Barbatrucco matteo: non è detto che sia 1 nel caso di riga eliminata (può partire da 2, da 3 o altro...)
        $chiave = 1;
        foreach (@$input['products'] as $key => $p) {
            $chiave = $key;
            break;
        }
    
    
        $this->form_validation->set_rules('products[' . $chiave . '][documenti_contabilita_articoli_name]', 'nome prodotto', 'required');
        $this->form_validation->set_rules('ragione_sociale', 'ragione sociale', 'required');
        $this->form_validation->set_rules('indirizzo', 'indirizzo', 'required');
        $this->form_validation->set_rules('citta', 'città', 'required');
        $this->form_validation->set_rules('nazione', 'nazione', 'required');
        $this->form_validation->set_rules('provincia', 'provincia', 'required|max_length[2]');
        $this->form_validation->set_rules('codice_fiscale', 'codice fiscale', 'required');
        $this->form_validation->set_rules('cap', 'CAP', 'required');

        //Verifico che il numero di fattura e la serie rispettino le regole di fatturazione
        $numero = $this->input->post('documenti_contabilita_numero');
        $serie = $this->input->post('documenti_contabilita_serie');
        $tipo = $this->input->post('documenti_contabilita_tipo');

        // DATA EMISSIONE
        if ($this->db->dbdriver != 'postgre') {

            //debug($input['documenti_contabilita_data_emissione'],true);

            $date = DateTime::createFromFormat("d/m/Y", $input['documenti_contabilita_data_emissione']);
            $data_emissione = $date->format('Y-m-d H:i:s');
            $year = $date->format('Y');
            $filtro_anno = "AND YEAR(documenti_contabilita_data_emissione) = $year";
        } else {
            //$data_emissione = $input['documenti_contabilita_data_emissione'];
            $date = DateTime::createFromFormat("d/m/Y", $input['documenti_contabilita_data_emissione']);
            $data_emissione = $date->format('Y-m-d H:i:s');
            $year = $date->format('Y');
            $filtro_anno = "AND date_part('year', documenti_contabilita_data_emissione) = '$year'";
        }


        //Controllo se esiste una fattura con stesso numero
        if (!empty($input['documento_id'])) {
            $exists = $this->db->query("SELECT * FROM documenti_contabilita WHERE documenti_contabilita_serie = '$serie' AND documenti_contabilita_numero = '$numero' AND documenti_contabilita_id <> '{$input['documento_id']}' AND documenti_contabilita_tipo = '{$tipo}' $filtro_anno")->num_rows();
            if ($exists) {
                echo json_encode(array(
                    'status' => 0,
                    'txt' => "Esiste già un documento con questo numero!",
                    'data' => ''
                ));
                exit;
            }
        } else {
            $exists = $this->db->query("SELECT * FROM documenti_contabilita WHERE documenti_contabilita_serie = '$serie' AND documenti_contabilita_numero = '$numero' AND documenti_contabilita_tipo = '{$tipo}' $filtro_anno")->num_rows();
            if ($exists) {
                echo json_encode(array(
                    'status' => 0,
                    'txt' => "Esiste già un documento con questo numero!",
                    'data' => ''
                ));
                exit;
            }
        }


        if ($this->db->dbdriver != 'postgre') {
            $filtro_data = "AND date(documenti_contabilita_data_emissione) < date('{$data_emissione}')";
        } else {
            $filtro_data = "AND documenti_contabilita_data_emissione::date < '{$data_emissione}'::date";
        }
        $exists_with_number_next = $this->db->query("SELECT * FROM documenti_contabilita WHERE documenti_contabilita_serie = '$serie' AND documenti_contabilita_numero > $numero AND documenti_contabilita_tipo = '{$tipo}' $filtro_data $filtro_anno");

        if ($exists_with_number_next->num_rows()) {
            $fattura = $exists_with_number_next->row();
            echo json_encode(array(
                'status' => 0,
                'txt' => "Esiste una fattura (la numero '{$fattura->documenti_contabilita_numero}' del '{$fattura->documenti_contabilita_data_emissione}') con numero maggiore ma data inferiore!",
                'data' => ''
            ));
            exit;
        }

        if ($this->db->dbdriver != 'postgre') {
            $filtro_data = "AND date(documenti_contabilita_data_emissione) > date('{$data_emissione}')";
        } else {
            $filtro_data = "AND documenti_contabilita_data_emissione::date > '{$data_emissione}'::date";
        }

        if (!empty($input['documento_id'])) {
            $exists_with_date_next = $this->db->query("SELECT * FROM documenti_contabilita WHERE 
                documenti_contabilita_serie = '$serie' 
                AND documenti_contabilita_numero < $numero 
                AND documenti_contabilita_tipo = '{$tipo}' 
                $filtro_data
                $filtro_anno
                AND documenti_contabilita_id <> '{$input['documento_id']}'");
        } else {
            $exists_with_date_next = $this->db->query("SELECT * FROM documenti_contabilita WHERE 
                documenti_contabilita_serie = '$serie' 
                AND documenti_contabilita_numero < $numero 
                AND documenti_contabilita_tipo = '{$tipo}' 
                $filtro_data
                $filtro_anno");
        }
        if ($exists_with_date_next->num_rows()) {
            $fattura = $exists_with_date_next->row();
            echo json_encode(array(
                'status' => 0,
                'txt' => "Esiste una fattura (la numero '{$fattura->documenti_contabilita_numero}' del '{$fattura->documenti_contabilita_data_emissione}') con numero minore ma data superiore!",
                'data' => ''
            ));
            exit;
        }

        if ($this->form_validation->run() == FALSE) {

            echo json_encode(array(
                'status' => 0,
                'txt' => validation_errors(),
                'data' => ''
            ));
        } else {

            //debug($input, true);

            $dest_entity_name = $input['dest_entity_name'];

            // **************** DESTINATARIO ****************** //

            $dest_fields = array("ragione_sociale", "indirizzo", "citta", "provincia", "nazione", "codice_fiscale", "partita_iva", 'cap', 'pec', 'codice_sdi','nome_banca', 'iban');
            foreach ($input as $key => $value) {
                if (in_array($key, $dest_fields)) {
                    $destinatario_json[$key] = $value;
                    $destinatario_entity[$dest_entity_name . "_" . $key] = $value;
                }
            }

            // Serialize
            $documents['documenti_contabilita_destinatario'] = json_encode($destinatario_json);

            // Se già censito lo collego altrimenti lo salvo se richiesto
            if ($input['dest_id']) {

                $documents['documenti_contabilita_' . $dest_entity_name . "_id"] = $input['dest_id'];

                //Se ho comunque richiesto la sovrascrittura dei dati
                if (isset($input['save_dest']) && $input['save_dest'] == "true") {
                    $this->apilib->edit($dest_entity_name, $input['dest_id'], $destinatario_entity);
                }
            } elseif (isset($input['save_dest']) && $input['save_dest'] == "true") {

                $dest_id = $this->apilib->create($dest_entity_name, $destinatario_entity, false);
                $documents['documenti_contabilita_' . $dest_entity_name . "_id"] = $dest_id;
            }

            // **************** DOCUMENTO ****************** //
            //debug($input);

            $documents['documenti_contabilita_note_interne'] = $input['documenti_contabilita_note'];
            $documents['documenti_contabilita_tipo'] = $input['documenti_contabilita_tipo'];
            $documents['documenti_contabilita_numero'] = $input['documenti_contabilita_numero'];
            $documents['documenti_contabilita_serie'] = $input['documenti_contabilita_serie'];
            $documents['documenti_contabilita_valuta'] = $input['documenti_contabilita_valuta'];
            $documents['documenti_contabilita_tasso_di_cambio'] = $input['documenti_contabilita_tasso_di_cambio'];
            $documents['documenti_contabilita_metodo_pagamento'] = $input['documenti_contabilita_metodo_pagamento'];
            $documents['documenti_contabilita_conto_corrente'] = ($input['documenti_contabilita_conto_corrente']) ?: null;
            $documents['documenti_contabilita_data_emissione'] = $data_emissione;
            $documents['documenti_contabilita_formato_elettronico'] = (!empty($input['documenti_contabilita_formato_elettronico']) ? $input['documenti_contabilita_formato_elettronico'] : DB_BOOL_FALSE);
            $documents['documenti_contabilita_extra_param'] = ($input['documenti_contabilita_extra_param']) ?: null;
            $documents['documenti_contabilita_rif_documento_id'] = ($input['documenti_contabilita_rif_documento_id']) ?: null;
            $documents['documenti_contabilita_da_sollecitare'] = (!empty($input['documenti_contabilita_da_sollecitare']) ? $input['documenti_contabilita_da_sollecitare'] : DB_BOOL_FALSE);
            //debug($input['documenti_contabilita_competenze'],true);

            $documents['documenti_contabilita_rivalsa_inps_perc'] = $input['documenti_contabilita_rivalsa_inps_perc'];

            $documents['documenti_contabilita_cassa_professionisti_perc'] = $input['documenti_contabilita_cassa_professionisti_perc'];

            //Accompagnatoria/DDT
            $documents['documenti_contabilita_fattura_accompagnatoria'] = (!empty($input['documenti_contabilita_fattura_accompagnatoria']) ? $input['documenti_contabilita_fattura_accompagnatoria'] : DB_BOOL_FALSE);
            $documents['documenti_contabilita_n_colli'] = ($input['documenti_contabilita_n_colli'] ?: null);
            $documents['documenti_contabilita_peso'] = ($input['documenti_contabilita_peso'] ?: null);
            $documents['documenti_contabilita_luogo_destinazione'] = $input['documenti_contabilita_luogo_destinazione'];
            $documents['documenti_contabilita_trasporto_a_cura_di'] = $input['documenti_contabilita_trasporto_a_cura_di'];
            $documents['documenti_contabilita_causale_trasporto'] = $input['documenti_contabilita_causale_trasporto'];
            $documents['documenti_contabilita_annotazioni_trasporto'] = $input['documenti_contabilita_annotazioni_trasporto'];
            $documents['documenti_contabilita_ritenuta_acconto_perc'] = $input['documenti_contabilita_ritenuta_acconto_perc'];
            $documents['documenti_contabilita_ritenuta_acconto_perc_imponibile'] = $input['documenti_contabilita_ritenuta_acconto_perc_imponibile'];
            $documents['documenti_contabilita_porto'] = $input['documenti_contabilita_porto'];
            $documents['documenti_contabilita_vettori_residenza_domicilio'] = $input['documenti_contabilita_vettori_residenza_domicilio'];
            $documents['documenti_contabilita_data_ritiro_merce'] = $input['documenti_contabilita_data_ritiro_merce'];
            $documents['documenti_contabilita_tipo_destinatario'] = (!empty($input['documenti_contabilita_tipo_destinatario'])) ? $input['documenti_contabilita_tipo_destinatario'] : null;

            //Pagamento
            $documents['documenti_contabilita_accetta_paypal'] = (!empty($input['documenti_contabilita_accetta_paypal']) ? $input['documenti_contabilita_accetta_paypal'] : DB_BOOL_FALSE);
            $documents['documenti_contabilita_split_payment'] = (!empty($input['documenti_contabilita_split_payment']) ? $input['documenti_contabilita_split_payment'] : DB_BOOL_FALSE);

            $documents['documenti_contabilita_centro_di_ricavo'] = (!empty($input['documenti_contabilita_centro_di_ricavo']) ? $input['documenti_contabilita_centro_di_ricavo'] : null);
            $documents['documenti_contabilita_template_pdf'] = (!empty($input['documenti_contabilita_template_pdf']) ? $input['documenti_contabilita_template_pdf'] : null);

            //Importi

            $documents['documenti_contabilita_totale'] = $input['documenti_contabilita_totale'];
            $documents['documenti_contabilita_iva'] = $input['documenti_contabilita_iva'];
            $documents['documenti_contabilita_competenze'] = ($input['documenti_contabilita_competenze']) ?: 0;
            $documents['documenti_contabilita_rivalsa_inps_valore'] = $input['documenti_contabilita_rivalsa_inps_valore'];
            $documents['documenti_contabilita_competenze_lordo_rivalsa'] = $input['documenti_contabilita_competenze_lordo_rivalsa'];
            $documents['documenti_contabilita_cassa_professionisti_valore'] = $input['documenti_contabilita_cassa_professionisti_valore'];
            $documents['documenti_contabilita_imponibile'] = $input['documenti_contabilita_imponibile'];
            $documents['documenti_contabilita_imponibile_scontato'] = $input['documenti_contabilita_imponibile_scontato'];
            $documents['documenti_contabilita_ritenuta_acconto_valore'] = $input['documenti_contabilita_ritenuta_acconto_valore'];
            $documents['documenti_contabilita_ritenuta_acconto_imponibile_valore'] = $input['documenti_contabilita_ritenuta_acconto_imponibile_valore'];
            $documents['documenti_contabilita_importo_bollo'] = $input['documenti_contabilita_importo_bollo'];
            $documents['documenti_contabilita_iva_json'] = $input['documenti_contabilita_iva_json'];
            $documents['documenti_contabilita_imponibile_iva_json'] = $input['documenti_contabilita_imponibile_iva_json'];
            $documents['documenti_contabilita_sconto_percentuale'] = $input['documenti_contabilita_sconto_percentuale'];

            $documents['documenti_contabilita_causale_pagamento_ritenuta'] = $input['documenti_contabilita_causale_pagamento_ritenuta'];

            if (!empty($input['documento_id'])) {
                $documento_id = $input['documento_id'];

                //debug($documents);
                $documento = $this->apilib->view('documenti_contabilita', $documento_id);

                //debug($documento, true);
                $this->db->where('documenti_contabilita_id', $input['documento_id'])->update('documenti_contabilita', $documents);

//                $this->apilib->edit("documenti_contabilita", $input['documento_id'], $documents); // Come mai è stato commentato e ora si usa update su db diretto? non vanno cosi i post process
//                
//                debug($this->apilib->view('documenti_contabilita'));
            } else {
                //debug($documents,true);
                $documents['documenti_contabilita_stato'] = 1;
                $documento = $this->apilib->create('documenti_contabilita', $documents);
                $documento_id = $documento['documenti_contabilita_id'];
            }


            // **************** SCADENZE ******************* //
            if (!empty($input['documento_id'])) {
                $this->db->delete('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $input['documento_id']]);
            }
            
                      
            
            foreach ($input['scadenze'] as $scadenza) {
                if ($scadenza['documenti_contabilita_scadenze_ammontare'] > 0) {
                    if (!empty($scadenza['documenti_contabilita_scadenze_scadenza'])) {

                        //debug($scadenza['documenti_contabilita_scadenze_saldato_con']);

                        if ($this->db->dbdriver != 'postgre') {
                            $date = DateTime::createFromFormat("d/m/Y", $scadenza['documenti_contabilita_scadenze_scadenza']);
                            $scadenza['documenti_contabilita_scadenze_scadenza'] = $date->format('Y-m-d H:i:s');
                        } else {
                            //$data_emissione = $input['documenti_contabilita_data_emissione'];
                            $date = DateTime::createFromFormat("d/m/Y", $scadenza['documenti_contabilita_scadenze_scadenza']);
                            $scadenza['documenti_contabilita_scadenze_scadenza'] = $date->format('Y-m-d H:i:s');
                        }
                    }
                    //debug($scadenza);
                    $this->apilib->create('documenti_contabilita_scadenze', [
                        'documenti_contabilita_scadenze_ammontare' => $scadenza['documenti_contabilita_scadenze_ammontare'],
                        'documenti_contabilita_scadenze_scadenza' => $scadenza['documenti_contabilita_scadenze_scadenza'],
                        'documenti_contabilita_scadenze_saldato_con' => (!empty($scadenza['documenti_contabilita_scadenze_saldato_con'])) ? $scadenza['documenti_contabilita_scadenze_saldato_con'] : null,
                        'documenti_contabilita_scadenze_data_saldo' => (!empty($scadenza['documenti_contabilita_scadenze_data_saldo'])) ? $scadenza['documenti_contabilita_scadenze_data_saldo'] : null,
                        'documenti_contabilita_scadenze_documento' => $documento_id
                    ]);
                }

            }

            // **************** PRODOTTI ****************** //
            if (!empty($input['documento_id'])) {
                $this->db->delete('documenti_contabilita_articoli', ['documenti_contabilita_articoli_documento' => $input['documento_id']]);
            }

            $raw_iva = $this->db->get('iva')->result_array();
            $iva = array_combine(
                array_map(function ($_iva) {
                    return $_iva['iva_id'];
                }, $raw_iva), array_map(function ($_iva) {
                return $_iva['iva_valore'];
            }, $raw_iva));
            //debug($iva,true);
            
            
            foreach ($input['products'] as $prodotto) {
                //debug($prodotto);
                //unset($prodotto['documenti_contabilita_articoli_id']);
                $prodotto['documenti_contabilita_articoli_documento'] = $documento_id;
                //Mi arriva l'id dell'iva, quindi recupero il valore
//                debug($iva);
//                debug($prodotto);
                $prodotto['documenti_contabilita_articoli_iva_perc'] = $iva[$prodotto['documenti_contabilita_articoli_iva_id']];
                $this->apilib->create("documenti_contabilita_articoli", $prodotto);
            }

            if ($documents['documenti_contabilita_formato_elettronico'] == DB_BOOL_TRUE) {

                $this->load->model('contabilita/docs');
                $this->docs->generate_xml($documento);
                
                //die('test');

            } else {
                // Storicizzo PDF
                if ($documents['documenti_contabilita_template_pdf']) {
                    $content_html = $this->apilib->view('documenti_contabilita_template_pdf', $documents['documenti_contabilita_template_pdf']);
                    $pdfFile = $this->layout->generate_pdf($content_html['documenti_contabilita_template_pdf_html'], "portrait", "", ['documento_id' => $documento_id], 'contabilita', TRUE);
                } else {
                    $pdfFile = $this->layout->generate_pdf("documento_pdf", "portrait", "", ['documento_id' => $documento_id], 'contabilita');
                }

                if (file_exists($pdfFile)) {
                    $contents = file_get_contents($pdfFile, true);
                    $pdf_b64 = base64_encode($contents);
                    $this->apilib->edit("documenti_contabilita", $documento_id, ['documenti_contabilita_file' => $pdf_b64]);
                }
            }


            //TODO: XML per fattura elettronica

            echo json_encode(array('status' => 1, 'txt' => base_url('main/layout/contabilita_dettaglio_documento/' . $documento_id.'?first_save=1')));
        }
    }

    public function edit_scadenze()
    {
        $input = $this->input->post();
        $documento_id = $input['documento_id'];

        $this->db->delete('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $documento_id]);

        foreach ($input['scadenze'] as $scadenza) {
            $this->apilib->create('documenti_contabilita_scadenze', [
                'documenti_contabilita_scadenze_ammontare' => $scadenza['documenti_contabilita_scadenze_ammontare'],
                'documenti_contabilita_scadenze_scadenza' => $scadenza['documenti_contabilita_scadenze_scadenza'],
                'documenti_contabilita_scadenze_saldato_con' => $scadenza['documenti_contabilita_scadenze_saldato_con'],
                'documenti_contabilita_scadenze_data_saldo' => ($scadenza['documenti_contabilita_scadenze_data_saldo']) ?: null,
                'documenti_contabilita_scadenze_documento' => $documento_id
            ]);
        }

        echo json_encode(array('status' => 2));
    }

    public function autocomplete($entity = null)
    {
        if (!$entity) {
            echo json_encode(['count_total' => 0, 'results' => []]);
            die();
        }
        $input = $this->input->get_post('search');

        $count_total = 0;

        $input = trim($input);
        if (empty($input) OR strlen($input) < 2) {
            echo json_encode(['count_total' => -1]);
            return;
        }

        $results = [];

        $input = strtolower($input);
        
        $input = str_ireplace("'", "''", $input);

        $res = "";
        
        if ($entity == $this->contabilita_settings['documenti_contabilita_settings_autocomplete_entita']) {
            $campo_preview = $this->contabilita_settings['documenti_contabilita_settings_autocomplete_campo_preview'];
            $campo_codice = $this->contabilita_settings['documenti_contabilita_settings_autocomplete_campo_codice'];
            $res = $this->apilib->search($entity, ["(LOWER($campo_preview) LIKE '%{$input}%' OR $campo_preview LIKE '{$input}%') OR (LOWER($campo_codice) LIKE '%{$input}%' OR $campo_codice LIKE '{$input}%')"]);
            //die("(LOWER(fw_products_name) LIKE '%{$input}%' OR fw_products_sku LIKE '{$input}%' OR CAST(fw_products_ean AS CHAR) = '{$input}')");
        } else if ($entity == 'clienti') {
            $res = $this->apilib->search('clienti', ["(LOWER(clienti_ragione_sociale) LIKE '%{$input}%')"]);
        } else if ($entity == 'fornitori') {
            $res = $this->apilib->search('fornitori', ["(LOWER(fornitori_nome) LIKE '%{$input}%' OR LOWER(fornitori_ragione_sociale) LIKE '%{$input}%')"]);
        } else if ($entity == 'vettori') {
            $res = $this->apilib->search('vettori', ["(LOWER(vettori_ragione_sociale) LIKE '%{$input}%')"]);
        }

        if ($res) {
            $count_total = count($res);
            $results = [
                'data' => $res
            ];
        }

        echo json_encode(['count_total' => $count_total, 'results' => $results]);
    }

    public function numeroSucessivo($tipo, $serie = null)
    {

        $data_emissione = $this->input->post('data_emissione');
        $data_emissione = DateTime::createFromFormat("d/m/Y", $data_emissione);
        $year = $data_emissione->format('Y');


        if ($serie) {
            $serie_where = " AND documenti_contabilita_serie = '$serie'";
        } else {
            $serie_where = " AND (documenti_contabilita_serie IS NULL OR documenti_contabilita_serie = '')";
        }
        if ($this->db->dbdriver != 'postgre') {
            $next = $this->db->query("SELECT MAX(documenti_contabilita_numero) + 1 as numero FROM documenti_contabilita WHERE documenti_contabilita_tipo = '$tipo' $serie_where AND YEAR(documenti_contabilita_data_emissione) = $year")->row()->numero;
        } else {
            $next = $this->db->query("SELECT MAX(documenti_contabilita_numero::int4)::int4 + 1 as numero FROM documenti_contabilita WHERE documenti_contabilita_tipo = '$tipo' $serie_where AND date_part('year', documenti_contabilita_data_emissione) = '$year'")->row()->numero;
        }


        echo ($next) ?: 1;
    }

    public function uploadImage($prodotto)
    {

        if (!isset($_FILES['prodotti_immagini_immagine']) && isset($_FILES['file'])) {
            $_FILES['prodotti_immagini_immagine'] = $_FILES['file'];
        }

        if (!isset($_FILES['prodotti_immagini_immagine']['type']) OR !in_array($_FILES['prodotti_immagini_immagine']['type'], ['image/jpeg', 'image/png'])) {
            die('Tipo file non supportato');
        }

        unset($_FILES['file']);

        try {
            $newMedia = $this->apilib->create('prodotti_immagini', ['prodotti_immagini_prodotto' => $prodotto]);
        } catch (Exception $ex) {
            set_status_header(500);
            die($ex->getMessage());
        }

        echo json_encode($newMedia);
    }

    public function ajax_get_templates($template_id, $documento_id)
    {
        $documento = $this->apilib->view('documenti_contabilita', $documento_id);
        $result = $this->apilib->view('documenti_contabilita_mail_template', $template_id);

        if ($documento['documenti_contabilita_clienti_id']) {

            $destinatario = $this->apilib->view('clienti', $documento['documenti_contabilita_clienti_id']);
            if ($destinatario['clienti_email']) {
                $result['email_destinatario'] = $destinatario['clienti_email'];
            }

        }

        echo json_encode($result);
    }

    public function tassoDiCambio($valuta_id) {
        $settings = $this->apilib->searchFirst('documenti_contabilita_settings');
        $tasso = $this->apilib->searchFirst('tassi_di_cambio', [
            'tassi_di_cambio_valuta_2' => $valuta_id,
            'tassi_di_cambio_valuta_1' => $settings['documenti_contabilita_settings_valuta_base']
        ], 0, 'tassi_di_cambio_creation_date', 'DESC');
        
        if (empty($tasso)) {
            echo json_encode([]);
        } else {
            echo json_encode($tasso);
        }
        
    }
    
    public function print_pdf($documento_id, $field_name = 'documenti_contabilita_file')
    {

        $documento = $this->apilib->view('documenti_contabilita', $documento_id);


        if($documento['documenti_contabilita_formato_elettronico'] == DB_BOOL_TRUE){
            $field_name = 'documenti_contabilita_file_preview';
        }
        
        if (!empty($documento_id)) {

            if ($this->input->get('regenerate')) {
                $pdfFile = $this->layout->generate_pdf(($this->input->get('view')) ?: "documento_pdf", "portrait", "", ['documento_id' => $documento_id], 'contabilita');

                if (file_exists($pdfFile)) {
                    $contents = file_get_contents($pdfFile, true);
                    $pdf_b64 = base64_encode($contents);
                    $this->apilib->edit("documenti_contabilita", $documento_id, [$field_name => $pdf_b64]);
                }

                $documento = $this->apilib->view('documenti_contabilita', $documento_id);
            }

            if ($this->input->get('html')) {

                die($contents);
            }
            header('Content-Type: application/pdf');
            header('Content-disposition: inline; filename="'.$documento['documenti_contabilita_tipo_value'].'_'.$documento['documenti_contabilita_numero'].$documento['documenti_contabilita_serie'].'.pdf"');

            echo base64_decode($documento[$field_name]);
        } else {
            echo "Errore, documenti non esistente";
        }
    }

    public function xml_fattura_elettronica($id)
    {
        
        $dati['fattura'] = $this->apilib->view('documenti_contabilita', $id);
        $dati['fattura']['articoli'] = $this->apilib->search('documenti_contabilita_articoli', ['documenti_contabilita_articoli_documento' => $id]);
        $dati['fattura']['scadenze'] = $this->apilib->search('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $id]);
        foreach ($dati['fattura']['scadenze'] as $key => $scadenza) {
            if ($scadenza['documenti_contabilita_scadenze_ammontare'] == '0.00') {
                unset($dati['fattura']['scadenze'][$key]);
            }
        }
//        echo '<pre>';
//        print_r($this->db->where('documenti_contabilita_id', $id)->get('documenti_contabilita')->row_array());
//        die();
        //$pagina = $this->load->view("pages/layouts/custom_views/contabilita/xml_fattura_elettronica", compact('dati'), true);
        //die('test');
        $pagina = $this->load->module_view("contabilita/views", 'xml_fattura_elettronica', ['dati' => $dati], true);
        header("Content-Type:text/xml");
        echo $pagina;
    }

    public function visualizza_fattura_elettronica($id)
    {
        $this->load->helper('download');

        $dati['fattura'] = $this->apilib->view('documenti_contabilita', $id);
        $settings = $this->apilib->searchFirst('documenti_contabilita_settings');

        $filename = date('Ymd-H-i-s');
        $physicalDir = __DIR__ . "/../../uploads";

        // Create a temporary file with the view html
        $tmpHtml = "{$physicalDir}/{$filename}.html";
        $tmpXml = "{$physicalDir}/{$filename}.xml";
        $tmpXsl = $physicalDir . "/fatturaordinaria_v1.2.xsl";

        if (!is_dir($physicalDir)) { 
            mkdir($physicalDir, 0755, true);
        }

        // Se non ce foglio xsl salvarlo.. Attenzione che se la SOGEI lo aggiorna bisogna modificarlo qui.
        /*if (!file_exists($tmpXsl)) {
            file_put_contents($tmpXsl, file_get_contents("https://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/fatturaordinaria_v1.2.xsl"));
        }*/
        // Scarico sempre il file di stile
        file_put_contents($tmpXsl, file_get_contents("https://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/fatturaordinaria_v1.2.xsl"));

        // Creo xml temporaneo
        file_put_contents($tmpXml, base64_decode($dati['fattura']['documenti_contabilita_file']), LOCK_EX);

        // Tento di generarlo
        //echo "xsltproc -o {$tmpHtml} {$tmpXsl} {$tmpXml}"; // Per debug comando decommentare
        exec("xsltproc -o {$tmpHtml} {$tmpXsl} {$tmpXml}");

        // Check se html generato correttamente lo mostro altrimenti carico xml perchè potrebbe avere dei die con gli errori
        if (file_exists($tmpHtml)) {
            // Storicizzo la preview
            $pdfFile = $this->layout->generate_pdf(file_get_contents($tmpHtml), "portrait", "", [], null, TRUE);
            $this->apilib->edit("documenti_contabilita", $id, ['documenti_contabilita_file_preview_xml' => base64_encode(file_get_contents($pdfFile))]);

            header('Content-Type: application/pdf');
            echo file_get_contents($pdfFile);
        } else {
            echo file_get_contents($tmpXml);
        }
    }

    public function download_fattura_elettronica($id)
    {
        $this->load->helper('download');

        $dati['fattura'] = $this->apilib->view('documenti_contabilita', $id);
        $settings = $this->apilib->searchFirst('documenti_contabilita_settings');

        $file = base64_decode($dati['fattura']['documenti_contabilita_file']);

        $prefisso = "IT" . $settings['documenti_contabilita_settings_company_vat_number'];
        $serie_numero = $dati['fattura']['documenti_contabilita_serie'] . $dati['fattura']['documenti_contabilita_numero'];

        $algoritmo = str_pad($serie_numero, 5, '0', STR_PAD_LEFT);

        $nomefile = $prefisso . "_" . $algoritmo . ".xml";

        force_download($nomefile, $file);
    }
    
    
    
    // Funzione richiamabile anche dall'esterno per cambiare lo stato ad
    public function change_sdi_status($documento, $status, $extra = null)
    {

        $data = (!empty($this->input->post())) ? $this->input->post() : array("documenti_contabilita_stato_invio_sdi" => $status, "documenti_contabilita_stato_invio_sdi_errore_gestito" => $extra);

        if (is_numeric($documento)) {
            $this->apilib->edit("documenti_contabilita", $documento, $data);
        } else {

            $zipname = $documento;
            $documento = $this->apilib->searchFirst("documenti_contabilita", ["documenti_contabilita_nome_zip_sdi" => $zipname]);
            $this->apilib->edit("documenti_contabilita", $documento['documenti_contabilita_id'], $data);
        }

    }

    // Cron da processare ogni 5 minuti. Non farlo prima di  minuti per sicurezza in quanto il nome file contiene il minuto di creazione
    public function cron_documenti_da_processare_sdi()
    {
        $documenti = $this->apilib->search("documenti_contabilita", ["documenti_contabilita_stato_invio_sdi" => 2, "documenti_contabilita_formato_elettronico" => DB_BOOL_TRUE], 1);

        echo "Trovati: " . count($documenti) . " da processare \n";
        if (!empty($documenti)) {
            foreach ($documenti as $documento) {
                echo "Processo documento:  " . $documento['documenti_contabilita_id'] . "\n";
                $this->send_to_sdiftp($documento['documenti_contabilita_id']);
            }
        }
    }

    // Funzione per inviare manualmente un documento al nostro server centralizzato
    public function send_to_sdiftp($documento_id)
    {
        $this->load->library('ftp');

        // Dir temporeanea
        $physicalDir = __DIR__ . "/../../uploads";
        if (!is_dir($physicalDir)) {
            mkdir($physicalDir, 0755, true);
        }

        // Estrazione documento
        $documento = $this->apilib->view('documenti_contabilita', $documento_id);

        if (!empty($documento)) {
            $this->change_sdi_status($documento_id, '3');
        } else {
            die('Documento id non valido');
        }
        $settings = $this->apilib->searchFirst('documenti_contabilita_settings');
        $file = base64_decode($documento['documenti_contabilita_file']);

        // Composizione del nome file
        $prefisso = "IT" . $settings['documenti_contabilita_settings_company_vat_number'];
        $serie_numero = $documento['documenti_contabilita_serie'] . $documento['documenti_contabilita_numero'];
        $algoritmo = str_pad($serie_numero, 5, '0', STR_PAD_LEFT);
        $xmlfilename = $prefisso . "_" . $algoritmo . ".xml";

        // Creazione documento xml temporaneo
        /*$tmpDocXml = "{$physicalDir}/{$xmlfilename}";
        file_put_contents($tmpDocXml, $file, LOCK_EX);*/

        // Generazione nome file zip
        $partita_iva = $settings['documenti_contabilita_settings_company_vat_number'];
        $data_gregoriana = (date('Y')) . (str_pad(date('z') + 1, 3, '0', STR_PAD_LEFT));
        $ora = date('Hi');
        $incrementale = "001";

        $xmlquadname = "FI.$partita_iva.$data_gregoriana.$ora.$incrementale.xml";
        $zipname = "FI.$partita_iva.$data_gregoriana.$ora.$incrementale.zip";

        // Creazione file di quadratura xml temporaneo TODO: Da rifare con generazione xml fatta bene
        /*$file_quadratura = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <ns2:FileQuadraturaFTP xmlns:ns2="http://www.fatturapa.it/sdi/ftp/v2.0" versione="2.0">
                <IdentificativoNodo>' . $partita_iva . '</IdentificativoNodo>
                <DataOraCreazione>' . date('Y-m-d') . 'T' . date('H:i:s') . '.105Z</DataOraCreazione>
                <NomeSupporto>' . $zipname . '</NomeSupporto>
                <NumeroFile>
                        <File>
                                <Tipo>FA</Tipo>
                                <Numero>1</Numero>
                        </File>
                </NumeroFile>
                </ns2:FileQuadraturaFTP>';*/

        $file_quadratura = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
                <ns2:FileQuadraturaFTP xmlns:ns2="http://www.fatturapa.it/sdi/ftp/v2.0" versione="2.0">
                <IdentificativoNodo>' . $partita_iva . '</IdentificativoNodo>
                <DataOraCreazione>'.date('c').'</DataOraCreazione>
                <NomeSupporto>' . $zipname . '</NomeSupporto>
                <NumeroFile>
                        <File>
                                <Tipo>FA</Tipo>
                                <Numero>1</Numero>
                        </File>
                </NumeroFile>
                </ns2:FileQuadraturaFTP>';
        /*$tmpQuadXml = "{$physicalDir}/{$xmlquadname}";
        file_put_contents($tmpQuadXml, $file_quadratura, LOCK_EX);*/

        // Creo lo zip
        $tmpZipFile = "{$physicalDir}/{$zipname}";
        $zip = new ZipArchive();
        if ($zip->open($tmpZipFile, ZipArchive::CREATE) !== TRUE) {
            $this->change_sdi_status($documento_id, '4', "Zip file creation failed. Cannot open zip file");
            exit("cannot open <$tmpZipFile>\n");
        }
        $zip->addFromString($xmlquadname, $file_quadratura);
        $zip->addFromString($xmlfilename, $file);
        $zip->close();


        // Configurazioni FTP TODO: Portare in costanti
        $config['hostname'] = '195.201.22.125';
        $config['username'] = 'docftpuser';
        $config['password'] = 'nB@NGs9u292';
        $config['debug'] = TRUE;

        // Connession ed upload ... TODO Verificare eventuali errori di upload
        $this->ftp->connect($config);
        if ($this->ftp->upload($tmpZipFile, "./ftp_temp_dir/" . $zipname, FTP_BINARY, 0775)) {
            $this->change_sdi_status($documento_id, '8');
        } else {
            $this->change_sdi_status($documento_id, '4', "Invio FTP al server centralizzato non riuscito.");
        }
        $this->ftp->close();

        // Aggiorno il documento indicando il nome zip utilizzato per l'invio del documento e anche il nome del file xml per fare match piu facilmente con le notifiche di scarto
        $this->apilib->edit("documenti_contabilita", $documento_id, ["documenti_contabilita_nome_zip_sdi" => $zipname, "documenti_contabilita_nome_file_xml" => $xmlfilename]);

    }

    public function generaRiba() {
        $ids = json_decode($this->input->post('ids'));
        
        $documenti = $this->apilib->search('documenti_contabilita_scadenze', "documenti_contabilita_scadenze_id IN (".implode(',',$ids).")");
        
        //debug($ids,true);
        
        $this->load->model('contabilita/ribaabicbi');
        $file_content = $this->ribaabicbi->creaFileFromDocumenti($this->apilib->searchFirst('documenti_contabilita_settings'), $documenti);
        
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=riba.dat");
        echo $file_content;
        die();
        
    }
    
    public function downloadZip() {
        $ids = json_decode($this->input->post('ids'));
        
        //debug($ids);
        
        $fatture = $this->apilib->search('documenti_contabilita', ['documenti_contabilita_id IN ('.implode(',',$ids).')']);
        
        //debug($fatture,true);
        $this->load->helper('download');
        $this->load->library('zip');
        $dest_folder = FCPATH."uploads";
        

        $destination_file = "{$dest_folder}/fatture.zip";


        //die('test');
        
        //Ci aggiungo il json e la versione, poi rizippo il pacchetto...
        $zip = new ZipArchive;
        
        if ($zip->open($destination_file, ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE) {
            exit("cannot open <$destination_file>\n");
        }
        
        foreach ($fatture as $fattura) {
            //debug($fattura,true);
            $xml_content = base64_decode($fattura['documenti_contabilita_file']);
            $pdf_content = base64_decode($fattura['documenti_contabilita_file_preview']);
            $zip->addFromString("xml/{$fattura['documenti_contabilita_numero']}{$fattura['documenti_contabilita_serie']}.xml",$xml_content);
            $zip->addFromString("pdf/{$fattura['documenti_contabilita_numero']}{$fattura['documenti_contabilita_serie']}.pdf",$pdf_content);
            
        }
        
        
        $zip->close();

        force_download('fatture.zip', file_get_contents($destination_file));
        
        
        
    }
    public function print_all() {
        $ids = json_decode($this->input->post('ids'));
        
        $documenti = $this->apilib->search('documenti_contabilita', ["documenti_contabilita_id IN (".implode(',', $ids).")"]);
        
        
        $files = [];
        foreach ($documenti as $key => $documento) {
            if($documento['documenti_contabilita_formato_elettronico'] == DB_BOOL_TRUE){
                $field_name = 'documenti_contabilita_file_preview';
            } else {
                $field_name = 'documenti_contabilita_file';
            }
            //var_dump(base64_decode($documento[$field_name]));
            file_put_contents(FCPATH.$key.'.pdf', base64_decode($documento[$field_name]));
            $files[] = FCPATH.$key.'.pdf';
            
        }
        $output = '';
        //echo "pdfunite ".implode(' ', $files)." ".FCPATH."merge.pdf";
        exec("pdfunite ".implode(' ', $files)." ".FCPATH."documenti.pdf",$output);     
        
        
        foreach ($documenti as $key => $documento) {
            unlink(FCPATH.$key.'.pdf');
        }
        $fp = fopen(FCPATH."documenti.pdf",'rb');
        header("Content-Type: application/force-download");
        header("Content-Length: " . filesize(FCPATH."documenti.pdf"));
        header("Content-Disposition: attachment; filename=documenti.pdf");
        fpassthru($fp);
        unlink(FCPATH."documenti.pdf");
        exit;
        
    }
}

?>
