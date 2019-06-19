<?php

/* $Id: riba_cbi.inc.php,v 1.3 2009/01/30 10:22:47 devincen Exp $
  --------------------------------------------------------------------------
  Gazie - Gestione Azienda
  Copyright (C) 2006 - Antonio De Vincentiis Montesilvano (PE)
  (www.devincentiis.it)
  <http://gazie.sourceforge.net>
  --------------------------------------------------------------------------
  Questo programma e` free software;   e` lecito redistribuirlo  e/o
  modificarlo secondo i  termini della Licenza Pubblica Generica GNU
  come e` pubblicata dalla Free Software Foundation; o la versione 2
  della licenza o (a propria scelta) una versione successiva.

  Questo programma  e` distribuito nella speranza  che sia utile, ma
  SENZA   ALCUNA GARANZIA; senza  neppure  la  garanzia implicita di
  NEGOZIABILITA` o di  APPLICABILITA` PER UN  PARTICOLARE SCOPO.  Si
  veda la Licenza Pubblica Generica GNU per avere maggiori dettagli.

  Ognuno dovrebbe avere   ricevuto una copia  della Licenza Pubblica
  Generica GNU insieme a   questo programma; in caso  contrario,  si
  scriva   alla   Free  Software Foundation,  Inc.,   59
  Temple Place, Suite 330, Boston, MA 02111-1307 USA Stati Uniti.
  --------------------------------------------------------------------------

 * ****************************************************************************************
  Questa classe genera il file RiBa standard ABI-CBI passando alla funzione "creaFile" i due array di seguito specificati:
  $intestazione = array monodimensionale con i seguenti index:
  [0] = abi_assuntrice variabile lunghezza 5 numerico
  [1] = cab_assuntrice variabile lunghezza 5 numerico
  [2] = conto variabile lunghezza 12 alfanumerico
  [3] = data_creazione variabile lunghezza 6 numerico formato GGMAA
  [4] = nome_supporto variabile lunghezza 20 alfanumerico
  [5] = codice_divisa variabile lunghezza 1 alfanumerico opzionale default "E"
  [6] = ragione_soc1_creditore variabile lunghezza 24 alfanumerico
  [7] = ragione_soc2_creditore variabile lunghezza 24 alfanumerico
  [8] = indirizzo_creditore variabile lunghezza 24 alfanumerico
  [9] = cap_citta_prov_creditore variabile lunghezza 24 alfanumerico
  [10] = codice_fiscale_creditore variabile lunghezza 16 alfanumerico opzionale default ""
  $ricevute_bancarie = array bidimensionale con i seguenti index:
  [0] = numero ricevuta lunghezza 10 numerico
  [1] = scadenza lunghezza 6 numerico
  [2] = importo in centesimi di euro lunghezza 13 numerico
  [3] = nome debitore lunghezza 60 alfanumerico
  [4] = codice fiscale/partita iva debitore lunghezza 16 alfanumerico
  [5] = indirizzo debitore lunghezza 30 alfanumerico
  [6] = cap debitore lunghezza 5 numerico
  [7] = comune provincia debitore lunghezza 25 alfanumerico
  [8] = abi banca domiciliataria lunghezza 5 numerico
  [9] = cab banca domiciliataria lunghezza 5 numerico
  [10] = descrizione banca domiciliataria lunghezza 50 alfanumerico
  [11] = codice cliente attribuito dal creditore lunghezza 16 numerico
  [12] = descrizione del debito lunghezza 40 alfanumerico
 */


class RibaAbiCbi extends CI_Model {

    var $progressivo = 0;
    var $assuntrice;
    var $data;
    var $valuta;
    var $supporto;
    var $totale;
    var $creditore;

    function RecordIB($abi_assuntrice, $data_creazione, $nome_supporto, $codice_divisa) { //record di testa
        $this->assuntrice = str_pad($abi_assuntrice, 5, '0', STR_PAD_LEFT);
        $this->data = str_pad($data_creazione, 6, '0');
        $this->valuta = substr($codice_divisa, 0, 1);
        $this->supporto = str_pad($nome_supporto, 20, '*', STR_PAD_LEFT);
        return " IB     " . $this->assuntrice . $this->data . $this->supporto . str_repeat(" ", 74) . $this->valuta . str_repeat(" ", 6);
    }

    function Record14($scadenza, $importo, $abi_assuntrice, $cab_assuntrice, $conto, $abi_domiciliataria, $cab_domiciliataria, $codice_cliente) {
        $this->totale += $importo;
        return " 14" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . str_repeat(" ", 12) . $scadenza . "30000" . str_pad($importo, 13, '0', STR_PAD_LEFT) . "-" . str_pad($abi_assuntrice, 5, '0', STR_PAD_LEFT) . str_pad($cab_assuntrice, 5, '0', STR_PAD_LEFT) . str_pad($conto, 12) . str_pad($abi_domiciliataria, 5, '0', STR_PAD_LEFT) . str_pad($cab_domiciliataria, 5, '0', STR_PAD_LEFT) . str_repeat(" ", 12) . str_repeat(" ", 5) . "4" . str_pad($codice_cliente, 16) . str_repeat(" ", 6) . $this->valuta;
    }

    function Record20($ragione_soc1_creditore, $ragione_soc2_creditore, $indirizzo_creditore, $cap_citta_prov_creditore) {
        $this->creditore = str_pad($ragione_soc1_creditore, 24);
        return " 20" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . substr($this->creditore, 0, 24) . substr(str_pad($ragione_soc2_creditore, 24), 0, 24) . substr(str_pad($indirizzo_creditore, 24), 0, 24) . substr(str_pad($cap_citta_prov_creditore, 24), 0, 24) . str_repeat(" ", 14);
    }

    function Record30($nome_debitore, $codice_fiscale_debitore) {
        return " 30" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . substr(str_pad($nome_debitore, 60), 0, 60) . str_pad($codice_fiscale_debitore, 16, ' ') . str_repeat(" ", 34);
    }

    function Record40($indirizzo_debitore, $cap_debitore, $comune_provincia_debitore, $descrizione_domiciliataria = "") {
        return " 40" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . substr(str_pad($indirizzo_debitore, 30), 0, 30) . str_pad(intval($cap_debitore), 5, '0', STR_PAD_LEFT) . substr(str_pad($comune_provincia_debitore, 25), 0, 25) . substr(str_pad($descrizione_domiciliataria, 50), 0, 50);
    }

    function Record50($descrizione_debito, $codice_fiscale_creditore) {
        return " 50" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . substr(str_pad($descrizione_debito, 40), 0, 40) . str_repeat(" ", 50) . str_pad($codice_fiscale_creditore, 16, ' ') . str_repeat(" ", 4);
    }

    function Record51($numero_ricevuta_creditore) {
        return " 51" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . str_pad($numero_ricevuta_creditore, 10, '0', STR_PAD_LEFT) . substr($this->creditore, 0, 20) . str_repeat(" ", 80);
    }

    function Record70() {
        return " 70" . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . str_repeat(" ", 110);
    }

    function RecordEF() { //record di coda
        return " EF     " . $this->assuntrice . $this->data . $this->supporto . str_repeat(" ", 6) . str_pad($this->progressivo, 7, '0', STR_PAD_LEFT) . str_pad($this->totale, 15, '0', STR_PAD_LEFT) . str_repeat("0", 15) . str_pad($this->progressivo * 7 + 2, 7, '0', STR_PAD_LEFT) . str_repeat(" ", 24) . $this->valuta . str_repeat(" ", 6);
    }

    function creaFile($intestazione, $ricevute_bancarie) {
        $accumulatore = $this->RecordIB($intestazione[0], $intestazione[3], $intestazione[4], $intestazione[5]).PHP_EOL;
        foreach ($ricevute_bancarie as $value) { //estraggo le ricevute dall'array
            $this->progressivo ++;
            $accumulatore .= $this->Record14($value[1], $value[2], $intestazione[0], $intestazione[1], $intestazione[2], $value[8], $value[9], $value[11]).PHP_EOL;
            $accumulatore .= $this->Record20($intestazione[6], $intestazione[7], $intestazione[8], $intestazione[9]).PHP_EOL;
            $accumulatore .= $this->Record30($value[3], $value[4]).PHP_EOL;
            $accumulatore .= $this->Record40($value[5], $value[6], $value[7], $value[10]).PHP_EOL;
            $accumulatore .= $this->Record50($value[12], $intestazione[10]).PHP_EOL;
            $accumulatore .= $this->Record51($value[0]).PHP_EOL;
            $accumulatore .= $this->Record70().PHP_EOL;
        }
        $accumulatore .= $this->RecordEF();
        return $accumulatore;
    }
    
    private function extractIbanData($iban) {
        // IT 94 P 02008 12310 000101714112
        $iban = str_ireplace(' ', '', $iban);
        return [
            'sigla' => substr($iban, 0, 2),
            'controllo' => substr($iban, 2, 2),
            'cin' => substr($iban, 4, 1),
            'abi' => substr($iban, 5, 5),
            'cab' => substr($iban, 10, 5),
            'cc' => substr($iban, 15),
        ];
    }
    
    public function creaFileFromDocumenti($settings, $documenti) {
        $iban = $settings['documenti_contabilita_settings_iban'];
        $iban_data = $this->extractIbanData($iban);
        
//        debug($settings);
//        debug($documenti);
//        exit;
        
        $intestazione = [
            0 => $iban_data['abi'],        //         [0] = abi_assuntrice variabile lunghezza 5 numerico
            1 => $iban_data['cab'],        //         [1] = cab_assuntrice variabile lunghezza 5 numerico
            2 => $iban_data['cc'],                //         [2] = conto variabile lunghezza 12 alfanumerico
            3 => date('dmy'),                //         [3] = data_creazione variabile lunghezza 6 numerico formato GGMAA
            4 => 'XYZSI',                //         [4] = nome_supporto variabile lunghezza 20 alfanumerico
            5 => 'E',                //         [5] = codice_divisa variabile lunghezza 1 alfanumerico opzionale default "E"
            6 => $settings['documenti_contabilita_settings_company_name'],                //         [6] = ragione_soc1_creditore variabile lunghezza 24 alfanumerico
            7 => $settings['documenti_contabilita_settings_company_name'],                //         [7] = ragione_soc2_creditore variabile lunghezza 24 alfanumerico
            8 => $settings['documenti_contabilita_settings_company_address'],                //         [8] = indirizzo_creditore variabile lunghezza 24 alfanumerico
            9 => $settings['documenti_contabilita_settings_company_zipcode']." ".$settings['documenti_contabilita_settings_company_city']." ".$settings['documenti_contabilita_settings_company_province'],                //         [9] = cap_citta_prov_creditore variabile lunghezza 24 alfanumerico
            10 => $settings['documenti_contabilita_settings_company_codice_fiscale'],                //         [10] = codice_fiscale_creditore variabile lunghezza 16 alfanumerico opzionale default ""
        ];
        $ricevute = [];
        foreach ($documenti as $documento) {
            $dest = json_decode($documento['documenti_contabilita_destinatario'], true);
            if ($documento['documenti_contabilita_clienti_id']) {
                $dest = array_merge($dest,$this->apilib->view('clienti', $documento['documenti_contabilita_clienti_id']));
            }
            if (!empty($dest['iban'])) {
                $dest_iban_data = $this->extractIbanData($dest['iban']);
            } elseif (!empty($dest['clienti_iban'])) {
                $dest_iban_data = $this->extractIbanData($dest['clienti_iban']);
            } elseif (!empty($dest['clienti_abi']) && !empty($dest['clienti_cab']) && !empty($dest['clienti_cin']) && !empty($dest['clienti_cc'])) {
                $dest_iban_data = "IT{$dest['clienti_cin']}{$dest['clienti_abi']}{$dest['clienti_cab']}{$dest['clienti_cc']}";
            } else {
                //$dest_iban_data = $iban_data;
                die("Iban mancante per il cliente '{$dest['ragione_sociale']}'.");
            }
            $ricevute[] = [
                0 => $documento['documenti_contabilita_numero'],            //        [0] = numero ricevuta lunghezza 10 numerico
                1 => date('dmy', strtotime($documento['documenti_contabilita_scadenze_scadenza'])),            //        [1] = scadenza lunghezza 6 numerico
                2 => $documento['documenti_contabilita_scadenze_ammontare']*100,            //        [2] = importo in centesimi di euro lunghezza 13 numerico
                3 => $dest['ragione_sociale'],            //        [3] = nome debitore lunghezza 60 alfanumerico
                4 => $dest['partita_iva'],            //        [4] = codice fiscale/partita iva debitore lunghezza 16 alfanumerico
                5 => $dest['indirizzo'],            //        [5] = indirizzo debitore lunghezza 30 alfanumerico
                6 => $dest['cap'],            //        [6] = cap debitore lunghezza 5 numerico
                7 => $dest['provincia'],            //        [7] = comune provincia debitore lunghezza 25 alfanumerico
                8 => $iban_data['abi'],            //        [8] = abi banca domiciliataria lunghezza 5 numerico
                9 => $iban_data['cab'],            //        [9] = cab banca domiciliataria lunghezza 5 numerico
                10 => 'TEST1',            //        [10] = descrizione banca domiciliataria lunghezza 50 alfanumerico
                11 => 'TEST2',            //        [11] = codice cliente attribuito dal creditore lunghezza 16 numerico
                12 => 'TEST3',            //        [12] = descrizione del debito lunghezza 40 alfanumerico
            ];
        }
        
        return $this->creaFile($intestazione, $ricevute);
    }
}

?>