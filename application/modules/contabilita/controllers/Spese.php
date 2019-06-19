<?php


class Spese extends MY_Controller {

    function __construct() {
        parent :: __construct();
    }


    private function stripP7MData($string) {

        //$newString=preg_replace('/[\x00\x04\x82\x03\xE8\x81]/', '', $string);
        $newString=preg_replace('/[[:^print:]]/', '', $string);

        // skip everything before the XML content
        $startXml = substr($newString, strpos($newString, '<?xml '));

        // skip everything after the XML content
        preg_match_all('/<\/.+?>/', $startXml, $matches, PREG_OFFSET_CAPTURE);
        $lastMatch = end($matches[0]);
        $str = substr($startXml, 0, $lastMatch[1]).$lastMatch[0];
        $startAll = strpos($str, "<Allegati");
        if($startAll !== false){
            $endAll = strpos($str, "</Allegati>"); 
            $str = substr($str, 0,$startAll).substr($str, ($endAll+11) );
        }

        $chiusura = '</p:FatturaElettronica>';
        $str = substr($str, 0, stripos($str, $chiusura)+strlen($chiusura));
        return $str;
    }

    /*
     * Elaborazione di file zip ricevuto dal sistema di interscambio
     *
     * Uno zip può contenere una fattura di spesa e quindi viene registrata
     *
     * Oppure può contenere una notifica di scarto che aggiornerà quindi la fattura in oggetto con l'eventuale errore.
     *
     */
    public function elaborazione_documenti_da_sdi($file_id = null) {

        // Recupero i file zip da elaborare
        if ($file_id) {
            $files = $this->apilib->search('documenti_contabilita_ricezione_sdi', ["documenti_contabilita_ricezione_sdi_stato_elaborazione = '1'"]);
        } else {
            $files = $this->apilib->search('documenti_contabilita_ricezione_sdi', ["documenti_contabilita_ricezione_sdi_stato_elaborazione = '1'"]);
        }

        if (count($files) < 1) {
            exit();
        }

        // Dir temporeanea
        $physicalDir = __DIR__ . "/../../../../uploads";
        if (!is_dir($physicalDir)) {
            mkdir($physicalDir, 0755, true);
        }

        foreach ($files as $file) {
            echo "<pre>";
            echo "<strong>Unzip del file: ".$file['documenti_contabilita_ricezione_sdi_nome_file']." </strong><br />";
            $zipname = $file['documenti_contabilita_ricezione_sdi_nome_file'];

            // Creo zip temporaneo
            $tmpZipFile = "{$physicalDir}/{$zipname}";

            file_put_contents($tmpZipFile, base64_decode($file['documenti_contabilita_ricezione_sdi_file_verificato']));
            $zip = new ZipArchive;

            // Elaboro lo zip

            if ($zip->open($tmpZipFile) == TRUE) {
                for ($i = 0; $i < $zip->numFiles; $i++) {

                    //var_dump($zip->getFromIndex($i));

                    $filename = $zip->getNameIndex($i);
                    echo "Elaboro il file: $filename <br />";

                    $tmpXmlFile = "{$physicalDir}/{$filename}";

                    if (strpos($filename, '.p7m') !== false) {
                        $content = $this->stripP7MData($zip->getFromIndex($i));
                    } else {
                        $content = $zip->getFromIndex($i);
                    }
                    //print_r($content);

                    try {
                        $xml = @new SimpleXMLElement($content);
                        //debug($xml);
                    } catch (Exception $e) {
                        print "<br /><br />*************** IMPOSSIBILE CONVERTIRE IL CONTENUTO IN XML VALIDO  ************************** <br /><br />";
                        echo $e->getMessage();
                        print "<br />";
                        foreach(libxml_get_errors() as $error) {
                            echo "\t", $error->message;
                        }
                        debug($content);
                        print "<br /><br />*************** CONTINUO...  ************************** <br /><br />";
                        continue;
                    }

                    // Cerco il tag per individuare quale dei 3 file sia quello che contiene realmente la fattura
                    echo "<br />";

                    if (isset($xml->FatturaElettronicaHeader)) {
                        echo "--------------------------- Individuata fattura <br />";

                        //print_r($content);

                        // ANAGRAFICA
                        $mittente['ragione_sociale'] = (string)$xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->Anagrafica->Denominazione;
                        $mittente['indirizzo'] =  (string)$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->Indirizzo;
                        $mittente['citta'] = (string)$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->Comune;
                        $mittente['cap'] = (string)$xml->FatturaElettronicaHeader->CedentePrestatore->Sede->CAP;
                        $mittente['partita_iva'] = (string)$xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
                        $mittente['codice_fiscale'] = (string)$xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;

                        $spesa['spese_fornitore'] = json_encode($mittente);
                        // DATI DOCUMENTO
                        $spesa['spese_numero'] = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Numero;
                        $spesa['spese_data_emissione'] = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->Data;

                        // IMPORTI
                        //$spesa['spese_imponibile'] = $xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo->ImponibileImporto;
                        $spesa['spese_totale'] = $xml->FatturaElettronicaBody->DatiGenerali->DatiGeneraliDocumento->ImportoTotaleDocumento;

                        // Sommo le imposte
                        $totale_imponibile = 0;
                        $totale_imposta = 0;
                        foreach ($xml->FatturaElettronicaBody->DatiBeniServizi->DatiRiepilogo as $riepilogo) {
                            $totale_imponibile = $totale_imponibile+$riepilogo->ImponibileImporto;
                            $totale_imposta = $totale_imposta+$riepilogo->Imposta;
                        }
                        $spesa['spese_iva'] = $totale_imposta;
                        $spesa['spese_imponibile'] = $totale_imponibile;

                        $spesa['spese_extra'] = $zipname." ".$filename;

                        //print_r($spesa);
                        $spesa_id = $this->apilib->create('spese', $spesa, false);

                        if ($spesa_id) {

                            // Aggiorno lo stato del file zip ricevuto come elaborato
                            $this->apilib->edit("documenti_contabilita_ricezione_sdi", $file['documenti_contabilita_ricezione_sdi_id'], ['documenti_contabilita_ricezione_sdi_stato_elaborazione' => 2]);

                            // Tento di inserire gli articoli
                            foreach ($xml->FatturaElettronicaBody->DatiBeniServizi->DettaglioLinee as $linea) {
                                    $articolo = array('spese_articoli_name' => $linea->Descrizione,
                                        'spese_articoli_prezzo' => $linea->PrezzoUnitario,
                                        'spese_articoli_quantita' => $linea->Quantita,
                                                    'spese_articoli_iva_perc' => $linea->AliquotaIVA,
                                                    'spese_articoli_spesa' => $spesa_id);
                                $this->apilib->create('spese_articoli', $articolo);
                            }


                            // Inserisco l'allegato così da poterlo scaricare leggibile
                            file_put_contents($tmpXmlFile, $content);
                            $allegato = array('spese_allegati_file' => $filename, 'spese_allegati_spesa' => $spesa_id);
                            $this->apilib->create('spese_allegati', $allegato);

                            // Inserisco l'xml parsato dal nostro convertitore per avere qualcosa di leggibile
                            file_put_contents($tmpXmlFile, $content);
                            $tmpXsl = $physicalDir . "/fatturaordinaria_v1.2.xsl";
                            $tmpHtml = "{$physicalDir}/{$filename}.html";

                            file_put_contents($tmpXsl, file_get_contents("https://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/fatturaordinaria_v1.2.xsl"));
                            exec("xsltproc -o {$tmpHtml} {$tmpXsl} {$tmpXmlFile}");
                            if (file_exists($tmpHtml)) {
                                $allegato = array('spese_allegati_file' => $filename.'.html', 'spese_allegati_spesa' => $spesa_id);
                                $this->apilib->create('spese_allegati', $allegato);
                            }

                        } else {
                            $this->apilib->edit("documenti_contabilita_ricezione_sdi", $file['documenti_contabilita_ricezione_sdi_id'], ['documenti_contabilita_ricezione_sdi_stato_elaborazione' => 3]);
                        }


                        echo "File elaborato, passo al successivo. <br />";
                        echo "<br />";
                        //print_r($xml);

                    } elseif (isset($xml->ListaErrori)) {

                        echo "--------------------------- Individuata una nota di scarto <br />";

                        $nomefilexml = (string)$xml->NomeFile;

                        $errori = "";

                        foreach ($xml->ListaErrori as $errore) {
                            $errori .= "Errore: " . (string)$errore->Errore->Codice . " " . (string)$errore->Errore->Descrizione . " " . (string)$errore->Errore->Suggerimento . " <br />";

                        }

                        $documento = $this->apilib->searchFirst('documenti_contabilita', ['documenti_contabilita_nome_file_xml' => $nomefilexml]);
                        if (!empty($documento['documenti_contabilita_id'])) {

                            echo "Trovato documento emesso con nome file: $nomefilexml";
                            $documento_id = $documento['documenti_contabilita_id'];

                            // Faccio update con gli errori
                            $this->apilib->edit('documenti_contabilita', $documento_id, ['documenti_contabilita_stato_invio_sdi_errore_gestito' => $documento['documenti_contabilita_stato_invio_sdi_errore_gestito'] . " " . $errori, 'documenti_contabilita_stato_invio_sdi' => 6]);

                            // Aggiorno lo stato del file zip ricevuto come elaborato
                            $this->apilib->edit("documenti_contabilita_ricezione_sdi", $file['documenti_contabilita_ricezione_sdi_id'], ['documenti_contabilita_ricezione_sdi_stato_elaborazione' => 2]);
                        } else {

                            echo "************ Nessun documento trovato con il nome file xml: $nomefilexml <br />";
                        }



                    } else {
                        echo "File scartato <br />";
                    }

                }
            }

            //exit();
        }
    }



    public function create_spesa() {

        $input = $this->input->post();
        //debug($input,true);
        if (!empty($input['spesa_id'])) {
            //die('NON GESTITO SALVATAGGIO IN MODIFICA.... Da completare!');
        }

        $this->load->library('form_validation');

        $this->form_validation->set_rules('spese_numero', 'numero documento', 'required');
        $this->form_validation->set_rules('spese_data_emissione', 'data emissione', 'required');
        $this->form_validation->set_rules('ragione_sociale', 'ragione sociale', 'required');
        //$this->form_validation->set_rules('indirizzo', 'indirizzo', 'required');
        /*$this->form_validation->set_rules('citta', 'città', 'required');
        $this->form_validation->set_rules('provincia', 'provincia', 'required');
        $this->form_validation->set_rules('codice_fiscale', 'codice fiscale', 'required');
        $this->form_validation->set_rules('partita_iva', 'partita iva', 'required');
        $this->form_validation->set_rules('cap', 'CAP', 'required');*/


        if ($this->form_validation->run() == FALSE)
        {

            echo json_encode(array(
                'status' => 0,
                'txt' => validation_errors(),
                'data' => ''
            ));
        }
        else
        {

            //debug($input, true);
            $dest_entity_name = $input['dest_entity_name'];

            // **************** DESTINATARIO ****************** //

            $dest_fields = array("ragione_sociale", "indirizzo", "citta", "provincia", "nazione", "codice_fiscale", "partita_iva", 'cap');
            foreach($input as $key => $value) {
                if (in_array($key, $dest_fields)) {
                    $destinatario_json[$key] = $value;
                    $destinatario_entity[$dest_entity_name . "_" . $key] = $value;
                }
            }

            // Serialize
            $spesa['spese_fornitore'] = json_encode($destinatario_json);

            // Se già censito lo collego altrimenti lo salvo se richiesto
            if ($input['dest_id']) {

                $documents['spese_'.$dest_entity_name."_id"] =  $input['dest_id'];

                //Se ho comunque richiesto la sovrascrittura dei dati
                if (isset($input['save_dest']) && $input['save_dest'] == "true") {
                    $this->apilib->edit($dest_entity_name, $input['dest_id'], $destinatario_entity);
                }

            } elseif (isset($input['save_dest']) && $input['save_dest'] == "true") {

                $dest_id = $this->apilib->create($dest_entity_name, $destinatario_entity, false);
                $documents['spese_'.$dest_entity_name."_id"] =  $dest_id;
            }

            // **************** DOCUMENTO ****************** //
            //$documents['spese_note'] = $input['documenti_note'];

            $spesa['spese_numero'] = $input['spese_numero'];

            $spesa['spese_valuta'] = $input['spese_valuta'];
            $spesa['spese_imponibile'] = $input['spese_imponibile'];

            $spesa['spese_totale'] = $input['spese_totale'];

            $spesa['spese_deduc_tasse'] = $input['spese_deduc_tasse'];
            $spesa['spese_rit_acconto'] = $input['spese_rit_acconto'];
            $spesa['spese_deduc_iva'] = $input['spese_deduc_iva'];

            //debug($input,true);

            $spesa['spese_anni_ammortamento'] = $input['spese_anni_ammortamento'];

            $spesa['spese_iva'] = $input['spese_iva'];
//            $spesa['documenti_metodo_pagamento'] = $input['documenti_metodo_pagamento'];
//            $spesa['documenti_conto_corrente'] = $input['documenti_conto_corrente'];
            $spesa['spese_data_emissione'] = $input['spese_data_emissione'];
            $spesa['spese_categoria'] = $input['spese_categoria'];
            $spesa['spese_centro_di_costo'] = (!empty($input['spese_centro_di_costo']))?$input['spese_centro_di_costo']:null;
            if (!empty($input['spesa_id'])) {
                $spesa_id = $input['spesa_id'];
                $this->apilib->edit('spese', $input['spesa_id'], $spesa);
            } else {
                $spesa_id = $this->apilib->create('spese', $spesa, false);
            }


            // **************** SCADENZE ******************* //
            if (!empty($input['spesa_id'])) {
                $this->db->delete('spese_scadenze', ['spese_scadenze_spesa' => $spesa_id]);
            }
            foreach ($input['scadenze'] as $scadenza) {
                if($scadenza['spese_scadenze_ammontare'] > 0){
                    $this->apilib->create('spese_scadenze', [
                        'spese_scadenze_ammontare' => $scadenza['spese_scadenze_ammontare'],
                        'spese_scadenze_scadenza' => $scadenza['spese_scadenze_scadenza'],
                        'spese_scadenze_saldato_con' => $scadenza['spese_scadenze_saldato_con'],
                        'spese_scadenze_data_saldo' => ($scadenza['spese_scadenze_data_saldo'])?:null,
                        'spese_scadenze_spesa' => $spesa_id
                    ]);
                }
            }

            // **************** PRODOTTI ****************** //
            if (!empty($input['spesa_id'])) {
                $this->db->delete('spese_articoli', ['spese_articoli_spesa' => $input['spesa_id']]);
            }
            foreach ($input['products'] as $prodotto) {
                if (!empty($prodotto['spese_articoli_name'])) {
                    $prodotto['spese_articoli_spesa'] = $spesa_id;
                    $this->apilib->create("spese_articoli", $prodotto);

                    if (!empty($input['censisci']) && $input['censisci']) {
                        //TODO...
                    }

                    if (!empty($input['movimenti']) && $input['movimenti']) {
                        //TODO...
                    }
                }
            }

            $session_files = (array)($this->session->userdata('files'));

            //debug($session_files,true);

            if (!empty($session_files)) {
                foreach ($session_files as $key => $file) {
                    if (!empty($file)) {
                        $this->apilib->create('spese_allegati', [
                            'spese_allegati_spesa' => $spesa_id,
                            'spese_allegati_file' => $file['path_local'],

                        ]);
                    }
                }
            }
            //debug($session_files,true);
            $this->session->set_userdata('files', []);

            echo json_encode(array('status' => 5, 'txt' => 'Spesa salvata correttamente'));

        }
    }

    public function autocomplete($entity) {

        $input = $this->input->get_post('search');

        $count_total = 0;

        $input = trim($input);
        if (empty($input) OR strlen($input) < 3) {
            echo json_encode(['count_total' => -1]);
            return;
        }

        $results = [];

        $input = strtolower($input);

        if ($entity == 'fw_products') {
            $res = $this->apilib->search('fw_products', ["(LOWER(fw_products_name) LIKE '%{$input}%' OR fw_products_sku LIKE '{$input}%' OR CAST(fw_products_ean AS CHAR) = '{$input}')"]);
            //die("(LOWER(fw_products_name) LIKE '%{$input}%' OR fw_products_sku LIKE '{$input}%' OR CAST(fw_products_ean AS CHAR) = '{$input}')");
        } else if ($entity == 'clienti') {
            $res = $this->apilib->search('clienti', ["(LOWER(clienti_ragione_sociale) LIKE '%{$input}%')"]);
        } else if ($entity == 'fornitori') {
            $res = $this->apilib->search('fornitori', ["(LOWER(fornitori_nome) LIKE '%{$input}%' OR LOWER(fornitori_ragione_sociale) LIKE '%{$input}%')"]);
        }

        if ($res) {
            $count_total = count($res);
            $results = [
                'data' => $res
            ];
        }

        echo json_encode(['count_total' => $count_total, 'results' => $results]);


    }

    public function numeroSucessivo($tipo, $serie) {
        $next = $this->db->query("SELECT count(*) + 1 as numero FROM documenti WHERE documenti_tipo = '$tipo' AND documenti_serie = '$serie'")->row()->numero;
        echo $next;
    }

    public function addFile() {
        //debug($_FILES, true);
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $filename = md5($_FILES['file']['name']).'.'.$ext;
        $uploadDepthLevel = defined('UPLOAD_DEPTH_LEVEL') ? (int) UPLOAD_DEPTH_LEVEL : 0;

        if ($uploadDepthLevel > 0) {
            // Voglio comporre il nome locale in modo che se il nome del file fosse
            // pippo.jpg la cartella finale sarà: ./uploads/p/i/p/pippo.jpg
            $localFolder = '';
            for ($i = 0; $i < $uploadDepthLevel; $i++) {
                // Assumo che le lettere siano tutte alfanumeriche,
                // alla fine le immagini sono tutte delle hash md5
                $localFolder .= strtolower(isset($filename[$i]) ? $filename[$i] . DIRECTORY_SEPARATOR : '');
            }

            if (!is_dir(FCPATH . 'uploads/' . $localFolder)) {
                mkdir(FCPATH . 'uploads/' . $localFolder, DIR_WRITE_MODE, true);
            }
        }

        $this->load->library('upload', array(
            'upload_path' => FCPATH . 'uploads/'.$localFolder,
            'allowed_types' => '*',
            'max_size' => '50000',
            'encrypt_name' => false,
            'file_name' => $filename,
        ));

        $uploaded = $this->upload->do_upload('file');
        if (!$uploaded) {
            debug($this->upload->display_errors());
            die();
        }

        $up_data = $this->upload->data();
        $up_data['path_local'] = $localFolder.$filename;
        $session = (array)($this->session->userdata('files'));
        $session[] = $up_data;
        $this->session->set_userdata('files', $session);
        usleep(100);
        echo json_encode(['status' => 1, 'file' => $up_data]);
    }

    public function removeFile($id) {
        $this->apilib->delete('spese_allegati', $id);
    }



}

?>
