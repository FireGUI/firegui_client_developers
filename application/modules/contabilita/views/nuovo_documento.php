<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<style>
    #js_product_table > tbody > tr > td,
    #js_product_table > tbody > tr > th,
    #js_product_table > tfoot > tr > td,
    #js_product_table > tfoot > tr > th,
    #js_product_table > thead > tr > td {
        vertical-align: top;
    }

    .row {
        margin-left: 0px !important;
        margin-right: 0px !important;
    }

    button {
        outline: none;
        -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
    }

    .button_selected {
        opacity: 0.6;
    }

    .table_prodotti td {
        vertical-align: top;
    }

    .totali label {
        display: block;
        font-weight: normal;
        text-align: left;
    }

    .totali label span {
        font-weight: bold;
        float: right;
    }

    label {
        font-size: 0.8em;
    }

    .rcr-adjust {
        width: 40%;
        display: inline;
    }

    .rcr_label label {
        width: 100%;
    }

    .margin-bottom-5 {
        margin-bottom: 5px;
    }

    .margin-left-20 {
        margin-left: 20px;
    }

    small, .small {
        font-size: 75%;
    }
</style>

<?php


$dati['id'] = null;
$dati['fattura'] = null;
$dati['fatture_cliente'] = null;
$dati['serie'] = null;
$dati['fatture_numero'] = null;
$dati['fatture_serie'] = null;
$dati['fatture_scadenza_pagamento'] = null;
$dati['fatture_pagato'] = null;
$dati['prodotti'] = null;
$dati['fatture_note'] = null;

/*
 * Install constants
 */
define('MODULE_NAME', 'fatture');

/** Entità **/
defined('ENTITY_SETTINGS') OR define('ENTITY_SETTINGS', 'settings');
defined('FATTURE_E_CUSTOMERS') OR define('FATTURE_E_CUSTOMERS', 'clienti');

/** Parametri **/
defined('FATTURAZIONE_METODI_PAGAMENTO') OR define('FATTURAZIONE_METODI_PAGAMENTO', serialize(array('Bonifico', 'Paypal', 'Contanti', 'Sepa RID', 'RIBA')));

defined('FATTURAZIONE_URI_STAMPA') OR define('FATTURAZIONE_URI_STAMPA', null);

$elenco_iva = $this->apilib->search('iva', [], null, 0, 'iva_order');
$serie_documento = $this->apilib->search('documenti_contabilita_serie');
$conti_correnti = $this->apilib->search('conti_correnti');
$documento_id = ($value_id) ?: $this->input->get('documenti_contabilita_id');
$documenti_tipo = $this->apilib->search('documenti_contabilita_tipo');
$centri_di_costo = $this->apilib->search('centri_di_costo_ricavo');
$templates = $this->apilib->search('documenti_contabilita_template_pdf');
$valute = $this->apilib->search('valute', [], null, 0, 'valute_codice');
$clone = $this->input->get('clone');
$tipo_destinatario = $this->apilib->search('documenti_contabilita_tipo_destinatario');
    $rifDocId = '';
if ($documento_id) {

    $documento = $this->apilib->view('documenti_contabilita', $documento_id);
    //debug($documento,true);
    $documento['articoli'] = $this->apilib->search('documenti_contabilita_articoli', ['documenti_contabilita_articoli_documento' => $documento_id]);
    $documento['scadenze'] = $this->apilib->search('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $documento_id]);
    $documento['documenti_contabilita_destinatario'] = json_decode($documento['documenti_contabilita_destinatario'], true);
    $documento['entity_destinatario'] = ($documento['documenti_contabilita_fornitori_id']) ? 'fornitori' : 'clienti';
    //debug($documento);
    
    $rifDoc = $documento['documenti_contabilita_rif_documento_id'];
    
    
    if($clone){
        if(!empty($rifDoc)){
            if($documento_id == $rifDoc){
                $rifDocId = $rifDoc;
            } else {
                $rifDocId = $documento_id;
            }
        } else {
            $rifDocId = $documento_id;
        }
    } else {
        if(!empty($rifDoc)){
            $rifDocId = $rifDoc;
        }
    }
} elseif ($this->input->post('ddt_ids') || $this->input->get('ddt_id')) {
    $ids = json_decode($this->input->post('ddt_ids'), true);
    if ($this->input->post('bulk_action') == 'Genera fattura distinta') {
        //Apro una tab per ogni ddt selezionato e gli passo il ddt
        foreach ($ids as $key => $id) {
            if ($key == 0) {
                //Il primo lo skippo perchè lo processerò qua...
                continue;
            }
            ?>
            <script>
                window.open('<?php echo base_url(); ?>main/layout/nuovo_documento/?ddt_id=<?php echo $id; ?>', '_blank');
            </script>
            <?php
        }
        //Una volta aperte le tab (una per ddt) continuo con questo, quindi tolgo gli altri da ids...
        $ids = [$ids[0]];
    } else {
        if (!$ids) { //Se non arrivano in post, sono delle tab, una per ddocumento... quindi lo prendo da get
            $ids = [$this->input->get('ddt_id')];
        }
    }
        
    $clone = true;
    $documento_id = $ids[0];
    $documento = $this->apilib->view('documenti_contabilita', $documento_id);
    $documento['documenti_contabilita_tipo'] = 1;
    //debug($documento,true);
    $documento['articoli'] = $this->apilib->search('documenti_contabilita_articoli', ["documenti_contabilita_articoli_documento IN (".implode(',', $ids).")"],null,0, 'documenti_contabilita_numero');
    foreach ($documento['articoli'] as $key => $articolo) {
        //debug($articolo,true);
        $data = substr($articolo['documenti_contabilita_data_emissione'], 0, 10);
        $data = date('d/m/Y', strtotime($data));
        $documento['articoli'][$key]['documenti_contabilita_articoli_descrizione'] = "DDT N° {$articolo['documenti_contabilita_numero']} del {$data}";
    }
    $documento['scadenze'] = $this->apilib->search('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $documento_id]);
    $documento['documenti_contabilita_destinatario'] = json_decode($documento['documenti_contabilita_destinatario'], true);
    $documento['entity_destinatario'] = ($documento['documenti_contabilita_fornitori_id']) ? 'fornitori' : 'clienti';
    //debug($this->input->post('ddt_ids'));
} elseif ($this->input->get('documenti_contabilita_clienti_id')) {
    $_cliente = $this->apilib->view('clienti', $this->input->get('documenti_contabilita_clienti_id'));
    $documento['documenti_contabilita_clienti_id'] = $this->input->get('documenti_contabilita_clienti_id');
    //debug($documento,true);
    $cliente = [];
    foreach ($_cliente as $field => $value) {
        $field = str_ireplace('clienti_', '', $field);
        $cliente[$field] = $value;
    }
    $documento['documenti_contabilita_destinatario'] = $cliente;
    $documento['entity_destinatario'] = 'clienti';
}

$impostazioni = $this->apilib->searchFirst('documenti_contabilita_settings');
//debug($impostazioni); 
$entita = $impostazioni['documenti_contabilita_settings_autocomplete_entita'];
$campo_codice = $impostazioni['documenti_contabilita_settings_autocomplete_campo_codice'];
$campo_unita_misura = $impostazioni['documenti_contabilita_settings_autocomplete_campo_unita_misura'];
$campo_preview = $impostazioni['documenti_contabilita_settings_autocomplete_campo_preview'];
$campo_prezzo = $impostazioni['documenti_contabilita_settings_autocomplete_campo_prezzo'];
$campo_prezzo_fornitore = $impostazioni['documenti_contabilita_settings_autocomplete_prezzo_fornitore'];
$campo_iva = $impostazioni['documenti_contabilita_settings_autocomplete_campo_iva'];
$campo_descrizione = $impostazioni['documenti_contabilita_settings_autocomplete_campo_descrizione'];
$campo_sconto = $impostazioni['documenti_contabilita_settings_autocomplete_campo_sconto'];
$campo_id = (empty($impostazioni['documenti_contabilita_settings_autocomplete_campo_id'])) ? $entita . '_id' : $impostazioni['documenti_contabilita_settings_autocomplete_campo_id'];

$metodi_pagamento = $this->apilib->search('documenti_contabilita_metodi_pagamento');
    

?>


<form class="formAjax" id="new_fattura" action="<?php echo base_url('contabilita/documenti/create_document'); ?>">

    <?php if ($documento_id && !$clone): ?>
        <input name="documento_id" type="hidden" value="<?php echo $documento_id; ?>"/>
    <?php endif; ?>

    <input type="hidden" name="documenti_contabilita_totale"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_totale']) ? number_format((float)$documento['documenti_contabilita_totale'], 2,'.','') : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_iva"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_iva']) ? number_format((float)$documento['documenti_contabilita_iva'], 2,'.','') : ''; ?>"/>

    <input type="hidden" name="documenti_contabilita_competenze"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_competenze']) ? number_format((float)$documento['documenti_contabilita_competenze'], 2,'.','') : ''; ?>"/>

    <input type="hidden" name="documenti_contabilita_rivalsa_inps_valore"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_rivalsa_inps_valore']) ? number_format((float)$documento['documenti_contabilita_rivalsa_inps_valore'], 2,'.','') : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_competenze_lordo_rivalsa"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_competenze_lordo_rivalsa']) ? number_format((float)$documento['documenti_contabilita_competenze_lordo_rivalsa'], 2,'.','') : ''; ?>"/>

    <input type="hidden" name="documenti_contabilita_cassa_professionisti_valore"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_cassa_professionisti_valore']) ? number_format((float)$documento['documenti_contabilita_cassa_professionisti_valore'], 2,'.','') : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_imponibile"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_imponibile']) ? number_format((float)$documento['documenti_contabilita_imponibile'], 3,',','') : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_imponibile_scontato"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_imponibile_scontato']) ? number_format((float)$documento['documenti_contabilita_imponibile_scontato'], 3,',','') : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_ritenuta_acconto_valore"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_ritenuta_acconto_valore']) ? number_format((float)$documento['documenti_contabilita_ritenuta_acconto_valore'], 2,'.','') : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_ritenuta_acconto_imponibile_valore"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_ritenuta_acconto_imponibile_valore']) ? number_format((float)$documento['documenti_contabilita_ritenuta_acconto_imponibile_valore'], 2,'.','') : ''; ?>"/>

    <input type="hidden" name="documenti_contabilita_iva_json"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_iva_json']) ? $documento['documenti_contabilita_iva_json'] : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_imponibile_iva_json"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_imponibile_iva_json']) ? $documento['documenti_contabilita_imponibile_iva_json'] : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_extra_param"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_extra_param']) ? $documento['documenti_contabilita_extra_param'] : ''; ?>"/>
    <input type="hidden" name="documenti_contabilita_luogo_destinazione_id"
           value="<?php echo ($documento_id && $documento['documenti_contabilita_luogo_destinazione_id']) ? $documento['documenti_contabilita_luogo_destinazione_id'] : ''; ?>"/>
    
    <div class="row">
        <div class="col-md-12" style="margin-bottom:20px;">
            <label>Tipo di documento:</label>
            <div class="btn-group">
                <?php foreach ($documenti_tipo as $tipo): ?>
                    <button type="button"
                            class="btn <?php if ($documento_id && ($documento_id && $documento['documenti_contabilita_tipo'] == $tipo['documenti_contabilita_tipo_id'])): ?>btn-primary<?php else: ?>btn-default<?php endif; ?> js_btn_tipo"
                            data-tipo="<?php echo $tipo['documenti_contabilita_tipo_id']; ?>"><?php echo $tipo['documenti_contabilita_tipo_value']; ?></button>
                <?php endforeach; ?>


                <input type="hidden" name="documenti_contabilita_tipo" class="js_documenti_contabilita_tipo"
                       value="<?php if ($documento_id && $documento['documenti_contabilita_tipo']): ?><?php echo $documento['documenti_contabilita_tipo']; ?><?php else: ?><?php echo 1;?><?php endif; ?>"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4" style="background-color:#eeeeee;">
            <h4>Dati del <span
                        class="js_dest_type"><?php if ($documento_id && $documento['documenti_contabilita_fornitori_id']): ?>cliente<?php else : ?>fornitore<?php endif; ?></span>
            </h4>

            <input type="hidden" name="dest_entity_name"
                   value="<?php if ($documento_id && $documento['documenti_contabilita_fornitori_id']): ?>fornitori<?php else : ?>clienti<?php endif; ?>"/>
            <input id="js_dest_id" type="hidden" name="dest_id"
                   value="<?php if (($this->input->get('documenti_contabilita_clienti_id') || $documento_id) && $documento['documenti_contabilita_clienti_id']): ?><?php echo($documento['documenti_contabilita_clienti_id'] ?: $documento['documenti_contabilita_fornitori_id']); ?><?php endif; ?>"/>

            <div class="row">
                <div class="form-group">
                    <?php foreach($tipo_destinatario as $tipo_dest): ?>
                    <div class="col-sm-4">
                        <label>
                            <input type="radio" name="documenti_contabilita_tipo_destinatario" id="js_tipo_destinatario" <?php if (!empty($documento['documenti_contabilita_tipo_destinatario']) && $documento['documenti_contabilita_tipo_destinatario'] == $tipo_dest['documenti_contabilita_tipo_destinatario_id']) : ?> checked="checked"<?php endif; ?> value="<?php echo $tipo_dest['documenti_contabilita_tipo_destinatario_id']; ?>"> <?php echo $tipo_dest['documenti_contabilita_tipo_destinatario_value']; ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input id="search_cliente" type="text" name="ragione_sociale"
                               class="form-control js_dest_ragione_sociale" placeholder="Ragione sociale"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["ragione_sociale"]; ?><?php endif; ?>"
                               autocomplete="off"/>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="indirizzo" class="form-control js_dest_indirizzo"
                               placeholder="Indirizzo"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["indirizzo"]; ?><?php endif; ?>"/>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="citta" class="form-control js_dest_citta" placeholder="Città"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["citta"]; ?><?php endif; ?>"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="nazione" maxlength="2" class="form-control js_dest_nazione" placeholder="Nazione"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario']) && (strlen($documento['documenti_contabilita_destinatario']["nazione"]) < 3)): ?><?php echo $documento['documenti_contabilita_destinatario']["nazione"]; ?><?php else: ?><?php echo "Italia"; ?><?php endif; ?>"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <input type="text" name="cap" class="form-control js_dest_cap" placeholder="CAP"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["cap"]; ?><?php endif; ?>"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div clasS="form-group">
                        <input type="text" name="provincia" class="form-control js_dest_provincia"
                               placeholder="Provincia" maxlength="2"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["provincia"]; ?><?php endif; ?>"/>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="partita_iva" class="form-control js_dest_partita_iva"
                               placeholder="P.IVA"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["partita_iva"]; ?><?php endif; ?>"/>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="codice_fiscale" class="form-control js_dest_codice_fiscale"
                               placeholder="Codice fiscale"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario'])): ?><?php echo $documento['documenti_contabilita_destinatario']["codice_fiscale"]; ?><?php endif; ?>"/>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="codice_sdi" class="form-control js_dest_codice_sdi"
                               placeholder="Codice destinatario (per privati 0000000)"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario']['codice_sdi'])): ?><?php echo $documento['documenti_contabilita_destinatario']['codice_sdi']; ?><?php endif; ?>"/>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="text" name="pec" class="form-control js_dest_pec"
                               placeholder="Indirizzo pec"
                               value="<?php if (!empty($documento['documenti_contabilita_destinatario']['pec'])): ?><?php echo $documento['documenti_contabilita_destinatario']['pec']; ?><?php endif; ?>"/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label id="js_label_rubrica">Salva in rubrica</label> <input type="checkbox" class="minimal"
                                                                                     name="save_dest" value="true"/>

                    </div>

                </div>
            </div>

        </div>

        <div class="col-md-8">
            <div class="row" style="background-color:#e0eaf0;">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Dati <span class="js_doc_type">documento</span></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Numero: </label> <input type="text" name="documenti_contabilita_numero"
                                                           class="form-control documenti_contabilita_numero"
                                                           placeholder="Numero documento"
                                                           value="<?php if (!empty($documento['documenti_contabilita_numero']) && !$clone) : ?><?php echo $documento['documenti_contabilita_numero']; ?><?php endif; ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label>Serie: </label><br/>
                        <div class="btn-group">
                            <?php foreach ($serie_documento as $serie): ?>
                                <button type="button"
                                        class="btn js_btn_serie btn-info <?php if (!empty($documento['documenti_contabilita_serie']) && $documento['documenti_contabilita_serie'] == $serie['documenti_contabilita_serie_value']) : ?>button_selected<?php endif; ?>"
                                        data-serie="<?php echo $serie['documenti_contabilita_serie_value']; ?>">
                                    /<?php echo $serie['documenti_contabilita_serie_value']; ?></button>
                            <?php endforeach; ?>
                            <input type="hidden" class="js_documenti_contabilita_serie"
                                   name="documenti_contabilita_serie"
                                   value="<?php if (!empty($documento['documenti_contabilita_serie'])) : ?><?php echo $documento['documenti_contabilita_serie']; ?><?php endif; ?>"/>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Data emissione: </label>
                            <?php //debug($documento); ?>
                            <div class="input-group js_form_datepicker date ">
                                <input type="text"
                                       name="documenti_contabilita_data_emissione"
                                       class="form-control"
                                       placeholder="Data emissione"
                                       value="<?php if (!empty($documento['documenti_contabilita_data_emissione']) && !$clone) : ?><?php echo date('d/m/Y', strtotime($documento['documenti_contabilita_data_emissione'])); ?><?php else : ?><?php echo date('d/m/Y'); ?><?php endif; ?>"
                                       data-name="documenti_contabilita_data_emissione"/> <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" style="display:none">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3"><?php //debug($documento['documenti_contabilita_valuta']); ?>
                        <label style="min-width:80px">Valuta: </label> <select name="documenti_contabilita_valuta"
                                                                               class="select2 form-control documenti_contabilita_valuta">
                            <?php foreach ($valute as $key => $valuta): ?>
                                <option data-id="<?php echo $valuta['valute_id']; ?>" value="<?php echo $valuta['valute_codice']; ?>" <?php if (($valuta['valute_id'] == $impostazioni['documenti_contabilita_settings_valuta_base'] && empty($documento_id)) || (!empty($documento['documenti_contabilita_valuta']) && strtoupper($documento['documenti_contabilita_valuta']) == strtoupper($valuta['valute_codice']))) : ?> selected="selected"<?php endif; ?>><?php echo $valuta['valute_nome']; ?> - <?php echo $valuta['valute_simbolo']; ?></option>
                            <?php endforeach; ?>

                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label style="min-width:80px">Tasso di cambio (<?php echo $impostazioni['valute_simbolo']; ?>): </label> 
                        <input type="text" class="form-control documenti_contabilita_tasso_di_cambio" name="documenti_contabilita_tasso_di_cambio" value="<?php if (empty($documento_id) || empty($documento['documenti_contabilita_tasso_di_cambio'])) : ?>1<?php else: ?><?php echo $documento['documenti_contabilita_tasso_di_cambio']; ?><?php endif; ?>">
                        
                    </div>
                    
                </div>
                <div class="col-md-3">
                    <label style="min-width:80px">Template Documento: </label> <select
                            name="documenti_contabilita_template_pdf" class="select2 form-control">
                        <option value="" <?php if ((empty($documento_id)) || empty($documento['documenti_contabilita_template_pdf']) || $documento['documenti_contabilita_template_pdf'] == '') : ?> selected="selected"<?php endif; ?>>Template base</option>
                        <?php foreach ($templates as $template): ?>
                            <option value='<?php echo $template['documenti_contabilita_template_pdf_id']; ?>' <?php if ((!empty($documento_id)) && (!empty($documento['documenti_contabilita_template_pdf']) && $documento['documenti_contabilita_template_pdf'] == $template['documenti_contabilita_template_pdf_id'])) : ?> selected="selected"<?php endif; ?>><?php echo $template['documenti_contabilita_template_pdf_nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                        <label style="min-width:80px">Centro di ricavo: </label> 
                        <select name="documenti_contabilita_centro_di_ricavo" class="select2 form-control">
                            <?php foreach ($centri_di_costo as $centro): ?>
                                <option value="<?php echo $centro['centri_di_costo_ricavo_id']; ?>"<?php if (($centro['centri_di_costo_ricavo_id'] == '1' && empty($documento_id)) || (!empty($documento['documenti_contabilita_centro_di_ricavo']) && $documento['documenti_contabilita_centro_di_ricavo'] == $centro['centri_di_costo_ricavo_id'])) : ?> selected="selected"<?php endif; ?>><?php echo $centro['centri_di_costo_ricavo_nome']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <div class="col-md-3" style="display:none;">
                    <label style="min-width:80px;">Rif. Documento: </label>
                    <input type="text" class="form-control" name="documenti_contabilita_rif_documento_id" value="<?php echo $rifDocId; ?>">
                </div>
            </div>
            <div class="row" style="background-color:#e0e8d1;">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Informazioni pagamento</h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <select name="documenti_contabilita_metodo_pagamento" class="select2 form-control">
                                <option value="">Metodo di pagamento</option>

                                <?php foreach ($metodi_pagamento as $metodo_pagamento) : ?>
                                    <option value="<?php echo $metodo_pagamento['documenti_contabilita_metodi_pagamento_valore']; ?>" <?php if (!empty($documento['documenti_contabilita_metodo_pagamento']) && $documento['documenti_contabilita_metodo_pagamento'] == $metodo_pagamento['documenti_contabilita_metodi_pagamento_valore']): ?> selected="selected"<?php endif; ?>>
                                        <?php echo ucfirst($metodo_pagamento['documenti_contabilita_metodi_pagamento_valore']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <!--
                                <option value="contanti"<?php if (!empty($documento['documenti_contabilita_metodo_pagamento']) && $documento['documenti_contabilita_metodo_pagamento'] == 'contanti') : ?> selected="selected"<?php endif; ?>>
                                    Contanti
                                </option>
                                <option value="bonifico bancario"<?php if (empty($documento['documenti_contabilita_metodo_pagamento']) || (!empty($documento['documenti_contabilita_metodo_pagamento']) && $documento['documenti_contabilita_metodo_pagamento'] == 'bonifico bancario')) : ?> selected="selected"<?php endif; ?>>
                                    Bonifico bancario
                                </option>
                                <option value="assegno"<?php if (!empty($documento['documenti_contabilita_metodo_pagamento']) && $documento['documenti_contabilita_metodo_pagamento'] == 'assegno') : ?> selected="selected"<?php endif; ?>>
                                    Assegno
                                </option>
                                <option value="riba"<?php if (!empty($documento['documenti_contabilita_metodo_pagamento']) && $documento['documenti_contabilita_metodo_pagamento'] == 'riba') : ?> selected="selected"<?php endif; ?>>
                                    RiBA
                                </option>
                                <option value="sepa_rid"<?php if (!empty($documento['documenti_contabilita_metodo_pagamento']) && $documento['documenti_contabilita_metodo_pagamento'] == 'sepa_rid') : ?> selected="selected"<?php endif; ?>>
                                    SEPA RID
                                </option>
                                -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <select name="documenti_contabilita_conto_corrente" class="select2 form-control">
                                <option value="">Scegli conto corrente....</option>

                                <?php foreach ($conti_correnti as $key => $conto): ?>
                                    <option value="<?php echo $conto['conti_correnti_id']; ?>" <?php if ((empty($documento_id) && $key == 0) || (!empty($documento['documenti_contabilita_conto_corrente']) && $documento['documenti_contabilita_conto_corrente'] == $conto['conti_correnti_id'])) : ?> selected="selected"<?php endif; ?>><?php echo $conto['conti_correnti_nome_istituto']; ?></option>

                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">

                    <div class="col-md-4">
                        <div class="form-group">
                            <span>
                                <label><strong>Formato elettronico</strong></label>
                                <input type="checkbox" class="minimal" name="documenti_contabilita_formato_elettronico"
                                       value="<?php echo DB_BOOL_TRUE; ?>"
                                    <?php if (!empty($documento['documenti_contabilita_formato_elettronico']) && $documento['documenti_contabilita_formato_elettronico'] == DB_BOOL_TRUE) : ?> checked="checked"<?php endif; ?>/>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <span>
                            <label>Accetta pagamento paypal</label> <input type="checkbox" class="minimal"
                                                                           name="documenti_contabilita_accetta_paypal"
                                                                           value="<?php echo DB_BOOL_TRUE; ?>"
                                    <?php if (!empty($documento['documenti_contabilita_accetta_paypal']) && $documento['documenti_contabilita_accetta_paypal'] == DB_BOOL_TRUE) : ?> checked="checked"<?php endif; ?>/>
                            </span>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <span class="margin-left-20">
                            <label>Applica Split Payment</label>
                            <input type="checkbox" class="minimal"
                                   name="documenti_contabilita_split_payment" value="<?php echo DB_BOOL_TRUE; ?>"
                                <?php if (!empty($documento['documenti_contabilita_split_payment']) && $documento['documenti_contabilita_split_payment'] == DB_BOOL_TRUE) : ?> checked="checked"<?php endif; ?> />
                            </span>
                    </div>

                </div>

            </div>

            <div class="row" style="background-color:#e0eaf0;">
                <div class="col-md-12">
                    <h4>Rivalsa, Cassa INPS e Ritenuta d’acconto</h4>
                </div>
                <div class="row rcr_label">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Rivalsa INPS: </label> <input type="text" class="form-control rcr-adjust"
                                                                 name="documenti_contabilita_rivalsa_inps_perc"
                                                                 value="<?php if (!empty($documento['documenti_contabilita_rivalsa_inps_perc'])) : ?><?php echo number_format((float)$documento['documenti_contabilita_rivalsa_inps_perc'], 2,'.',''); ?><?php else: ?>0<?php endif; ?>"/>
                            %
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Cassa professionisti: </label> <input type="text" class="form-control rcr-adjust"
                                                                         name="documenti_contabilita_cassa_professionisti_perc"
                                                                         value="<?php if (!empty($documento['documenti_contabilita_cassa_professionisti_perc'])) : ?><?php echo number_format((float)$documento['documenti_contabilita_cassa_professionisti_perc'], 2,'.',''); ?><?php else: ?>0<?php endif; ?>"/>
                            %
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ritenuta d'acconto: </label> <input type="text" class="form-control rcr-adjust"
                                                                       name="documenti_contabilita_ritenuta_acconto_perc"
                                                                       value="<?php if (!empty($documento['documenti_contabilita_ritenuta_acconto_perc'])) : ?><?php echo number_format((float)$documento['documenti_contabilita_ritenuta_acconto_perc'], 2,'.',''); ?><?php else: ?>0<?php endif; ?>"/>
                            %
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>% sull'imponibile: </label> <input type="text" class="form-control rcr-adjust"
                                                                      name="documenti_contabilita_ritenuta_acconto_perc_imponibile"
                                                                      value="<?php if (!empty($documento['documenti_contabilita_ritenuta_acconto_perc_imponibile'])) : ?><?php echo number_format((float)$documento['documenti_contabilita_ritenuta_acconto_perc_imponibile'], 2,'.',''); ?><?php else: ?>100<?php endif; ?>"/>
                        </div>
                    </div>

                </div>
                <div class="row rcr_label">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Importo bollo: </label> <input type="text" class="form-control rcr-adjust"
                                                                  name="documenti_contabilita_importo_bollo"
                                                                  value="<?php if (!empty($documento['documenti_contabilita_importo_bollo'])) : ?><?php echo number_format((float)$documento['documenti_contabilita_importo_bollo'], 2,'.',''); ?><?php else: ?>0<?php endif; ?>"/>
                            €
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Causale Pag. Rit.: </label>
                            <select name="documenti_contabilita_causale_pagamento_ritenuta" class="select2 form-control">
                                <option value="" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "") : ?>selected="selected"<?php endif; ?>></option>
                                <option value="A" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "A") : ?>selected="selected"<?php endif; ?>>A</option>
                                <option value="B" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "B") : ?>selected="selected"<?php endif; ?>>B</option>
                                <option value="C" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "C") : ?>selected="selected"<?php endif; ?>>C</option>
                                <option value="D" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "D") : ?>selected="selected"<?php endif; ?>>D</option>
                                <option value="E" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "E") : ?>selected="selected"<?php endif; ?>>E</option>
                                <option value="F" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "F") : ?>selected="selected"<?php endif; ?>>F</option>
                                <option value="G" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "G") : ?>selected="selected"<?php endif; ?>>G</option>
                                <option value="H" <?php if (!empty($documento['documenti_contabilita_causale_pagamento_ritenuta']) && $documento['documenti_contabilita_causale_pagamento_ritenuta'] == "H") : ?>selected="selected"<?php endif; ?>>H</option>
                            </select>
                            <!-- todo da completare in base alle richieste -->
                        </div>
                    </div>
                </div>
            </div>


        </div>

    </div>
    <div class="row">
        <div class="col-md-12">


            <hr/>


            <div class="row">
                <div class="col-md-12">


                    <table id="js_product_table" class="table table-condensed table-striped table_prodotti">
                        <thead>
                            <tr>
                                <th width="30">Codice</th>
                                <th>Nome prodotto</th>
                                <th width="110">U.M.</th>
                                <th width="30">Quantit&agrave;</th>
                                <th width="90">Prezzo</th>
                                <th width="90">Sconto %</th>
                                <th width="75">IVA</th>
                                <th width="90">Importo</th>
                                <th width="35"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hidden">
                                <td><input type="text"
                                           class="form-control input-sm js_documenti_contabilita_articoli_codice js_autocomplete_prodotto"
                                           data-id="1" data-name="documenti_contabilita_articoli_codice" />
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control input-sm js_documenti_contabilita_articoli_name js_autocomplete_prodotto"
                                           data-id="1" data-name="documenti_contabilita_articoli_name" />
                                    <small>Descrizione aggiuntiva:</small>
                                    <textarea class="form-control input-sm js_documenti_contabilita_articoli_descrizione"
                                              data-name="documenti_contabilita_articoli_descrizione" 
                                              style="width:100%;" row="2"></textarea>
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control input-sm text-right js_documenti_contabilita_articoli_unita_misura"
                                           data-name="documenti_contabilita_articoli_unita_misura" 
                                           placeholder="(facoltativo)"/>
                                </td>
                                <td><input type="text"
                                           class="form-control input-sm js_documenti_contabilita_articoli_quantita"
                                           data-name="documenti_contabilita_articoli_quantita" value="1" /></td>
                                <td>
                                    <input type="text"
                                           class="form-control input-sm text-right js_documenti_contabilita_articoli_prezzo decimal"
                                           data-name="documenti_contabilita_articoli_prezzo" value="0.00" />
                                    <small style="text-align:center;display:block;">Imponibile<br/><span
                                                class="js_riga_imponibile">0.00</span></small>
                                </td>
                                <td>
                                    <input type="text"
                                           class="form-control input-sm text-right js_documenti_contabilita_articoli_sconto"
                                           data-name="documenti_contabilita_articoli_sconto" value="0" />
                                </td>
                                <td>
<?php //debug($impostazioni); ?>
                                    <select class="form-control input-sm text-right js_documenti_contabilita_articoli_iva_id"
                                            data-name="documenti_contabilita_articoli_iva_id">
                                        <?php foreach ($elenco_iva as $iva): ?>
                                            <option value="<?php echo $iva['iva_id']; ?>"
                                                    data-perc="<?php echo $iva['iva_valore']; ?>"<?php if ($iva['iva_id'] == $impostazioni['documenti_contabilita_settings_iva_default']): ?> selected="selected"<?php endif; ?>><?php echo $iva['iva_label']; ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <input type="hidden"
                                           class="form-control input-sm text-right js_documenti_contabilita_articoli_iva"
                                           data-name="documenti_contabilita_articoli_iva" value="0"/>

                                    <input type="hidden" class="js_documenti_contabilita_articoli_prodotto_id"
                                           data-name="documenti_contabilita_articoli_prodotto_id"/>
                                </td>

                                <td>
                                    <input type="text" class="form-control input-sm text-right js-importo decimal"
                                           data-name="documenti_contabilita_articoli_importo_totale" value="0"
                                           />

                                    <input type="checkbox" class="_form-control js-applica_ritenute"
                                           data-name="documenti_contabilita_articoli_applica_ritenute"
                                           value="<?php echo DB_BOOL_TRUE; ?>" checked="checked"/>
                                    <small>Appl. ritenute</small>
                                    <br/> <input type="checkbox" class="_form-control js-applica_sconto"
                                                 data-name="documenti_contabilita_articoli_applica_sconto"
                                                 value="<?php echo DB_BOOL_TRUE; ?>" checked="checked"/>
                                    <small>Appl. sconto</small>
                                </td>

                                <td class="text-center">
                                    <button type="button"
                                            class="btn  btn-danger btn-xs js_remove_product">
                                        <span class="fa fa-remove"></span>
                                    </button>
                                </td>
                            </tr>
                            <?php if (isset($documento['articoli']) && $documento['articoli']): ?>
                                <?php foreach ($documento['articoli'] as $k => $prodotto): ?>

                                    <?php //debug($prodotto); ?>

                                    <!-- DA RIVEDEER POTREBBERO MANCARE DEI CAMPI QUANDO SI FARA L EDIT -->
                                    <tr>
                                        <td><input type="text" class="form-control input-sm js_autocomplete_prodotto"
                                                    data-id="<?php echo $k; ?>"
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_codice]"
                                                   value="<?php echo $prodotto['documenti_contabilita_articoli_codice']; ?>"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control input-sm js_autocomplete_prodotto"
                                                    data-id="<?php echo $k; ?>"
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_name]"
                                                   value="<?php echo str_replace('"', '&quot;', $prodotto['documenti_contabilita_articoli_name']); ?>" />
                                            <small>Descrizione aggiuntiva:</small>
                                            <textarea
                                                    class="form-control input-sm js_documenti_contabilita_articoli_descrizione"
                                                    name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_descrizione]"
                                                    
                                                    style="width:100%;"
                                                    row="2"><?php echo $prodotto['documenti_contabilita_articoli_descrizione']; ?></textarea>
                                        </td>
                                        <td>
                                            <input type="text"
                                                   class="form-control input-sm text-right js_documenti_contabilita_articoli_unita_misura"
                                                   
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_unita_misura]"
                                                   placeholder="(facoltativo)"
                                                   value="<?php echo $prodotto['documenti_contabilita_articoli_unita_misura']; ?>"
                                            />
                                        </td>
                                        <td><input type="text"
                                                   class="form-control input-sm js_documenti_contabilita_articoli_quantita"
                                                   
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_quantita]"
                                                   value="<?php echo $prodotto['documenti_contabilita_articoli_quantita']; ?>"
                                                   placeholder="1"/></td>
                                        <td>
                                            <input type="text"
                                                   class="form-control input-sm text-right js_documenti_contabilita_articoli_prezzo decimal"
                                                   
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_prezzo]"
                                                   value="<?php echo number_format((float)$prodotto['documenti_contabilita_articoli_prezzo'], 3,'.',''); ?>"
                                                   placeholder="0.00"/>
                                            <small style="text-align:center;display:block;">
                                                Imponibile<br/><span
                                                        class="js_riga_imponibile"><?php echo number_format($prodotto['documenti_contabilita_articoli_prezzo'] * $prodotto['documenti_contabilita_articoli_quantita'], 2, '.', ''); ?></span>
                                            </small>
                                        </td>
                                        <td><input type="text"
                                                   class="form-control input-sm text-right js_documenti_contabilita_articoli_sconto"
                                                   
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_sconto]"
                                                   value="<?php echo number_format((float)$prodotto['documenti_contabilita_articoli_sconto'], 2,'.',''); ?>"
                                                   placeholder="0"/></td>
                                        <td>
                                            <select class="form-control input-sm text-right js_documenti_contabilita_articoli_iva_id"
                                                    name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_iva_id]">
                                                <?php foreach ($elenco_iva as $iva): ?>
                                                    <option value="<?php echo $iva['iva_id']; ?>"
                                                            data-perc="<?php echo $iva['iva_valore']; ?>"<?php if ($iva['iva_id'] == $prodotto['documenti_contabilita_articoli_iva_id']) : ?> selected="selected"<?php endif; ?>><?php echo $iva['iva_label']; ?></option>
                                                <?php endforeach; ?>
                                            </select> <input type="hidden"
                                                             class="form-control input-sm text-right js_documenti_contabilita_articoli_iva"
                                                             name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_iva]"
                                                             value="0"/> <input type="hidden"
                                                                                class="js_documenti_contabilita_articoli_prodotto_id"
                                                                                name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_prodotto_id]"
                                                                                value="<?php echo $prodotto['documenti_contabilita_articoli_prodotto_id']; ?>"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control input-sm text-right js-importo decimal"
                                                   name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_importo_totale]"
                                                   placeholder="0" /> <input type="checkbox"
                                                                                         class="_form-control js-applica_ritenute"
                                                                                         name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_applica_ritenute]"
                                                                                         value="<?php echo DB_BOOL_TRUE; ?>"<?php if ($prodotto['documenti_contabilita_articoli_applica_ritenute'] == DB_BOOL_TRUE) : ?> checked="checked"<?php endif; ?> />
                                            <small>Appl. ritenute</small>
                                            <br/> <input type="checkbox" class="_form-control js-applica_sconto"
                                                         name="products[<?php echo $k + 1; ?>][documenti_contabilita_articoli_applica_sconto]"
                                                         value="<?php echo DB_BOOL_TRUE; ?>"<?php if ($prodotto['documenti_contabilita_articoli_applica_sconto'] == DB_BOOL_TRUE) : ?> checked="checked"<?php endif; ?> />
                                            <small>Appl. sconto</small>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-danger btn-xs js_remove_product">
                                                <span class="fa fa-remove"></span>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td>
                                    <button id="js_add_product" type="button" class="btn btn-primary btn-sm">+ Aggiungi
                                        prodotto
                                    </button>
                                </td>
                                <td colspan="3"></td>
                                <td class="totali" colspan="4" style="background: #faf6ea">

                                    <label>Competenze: <span class="js_competenze">€ 0</span></label> <label class="competenze_scontate">Competenze Scontate: <span class="js_competenze_scontate">€ 0</span></label> <label>Sconto
                                        percentuale: <input type="text" name="documenti_contabilita_sconto_percentuale"
                                                            class="js_sconto_totale"
                                                            value="<?php if (!empty($documento['documenti_contabilita_sconto_percentuale'])) : ?><?php echo number_format((float)$documento['documenti_contabilita_sconto_percentuale'], 2,'.',''); ?><?php else: ?>0<?php endif; ?>"/></label>

                                    <label class="js_rivalsa"></label> <label class="js_competenze_rivalsa"></label>

                                    <label class="js_cassa_professionisti"></label> <label class="js_imponibile"></label>

                                    <label class="js_ritenuta_acconto"></label>

                                    <label class="js_tot_iva">IVA: <span class="___js_tot_iva">€ 0</span></label>

                                    <label class="js_split_payment"></label>

                                    <label>Totale da saldare: <span class="js_tot_da_saldare">€ 0</span></label>

                                </td>
                            </tr>
                        </tfoot>
                    </table>


                </div>
            </div>

            <hr/>
            <div class="row margin-bottom-5 col-md-12">
                <div class="form-group">
                    <label> <input type="checkbox" class="minimal js_fattura_accompagnatoria_checkbox"
                                   name="documenti_contabilita_fattura_accompagnatoria"
                                   value="<?php echo DB_BOOL_TRUE; ?>" <?php if (!empty($documento['documenti_contabilita_fattura_accompagnatoria']) && $documento['documenti_contabilita_fattura_accompagnatoria'] == DB_BOOL_TRUE) : ?> checked="checked"<?php endif; ?>>
                        Dati trasporto </label>
                </div>
            </div>
            <div class="row js_fattura_accompagnatoria_row hide">
                <div class="col-md-2">
                    <div class="form-group">
                        <label>N. Colli: </label> <input type="text" class="form-control" placeholder="1"
                                                         name="documenti_contabilita_n_colli"
                                                         value="<?php echo (!empty($documento['documenti_contabilita_n_colli'])) ? number_format((float)$documento['documenti_contabilita_n_colli'], 0,',','') : ''; ?>"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>Peso: </label> <input type="text" class="form-control" placeholder="0 kg"
                                                     name="documenti_contabilita_peso"
                                                     value="<?php echo (!empty($documento['documenti_contabilita_peso'])) ? number_format((float)$documento['documenti_contabilita_peso'], 2,'.','') : ''; ?>"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Trasporto a cura di: </label> <input type="text" class="form-control"
                                                                    placeholder="Azienda di trasporti"
                                                                    name="documenti_contabilita_trasporto_a_cura_di"
                                                                    value="<?php echo (!empty($documento['documenti_contabilita_trasporto_a_cura_di'])) ? $documento['documenti_contabilita_trasporto_a_cura_di'] : ''; ?>"/>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <label>Data Ritiro Merce: </label>
                        <div class="input-group js_form_datepicker date ">
                            <input type="text"
                                   name="documenti_contabilita_data_ritiro_merce"
                                   class="form-control"
                                   placeholder="Data Ritiro Merce"
                                   value="<?php if (!empty($documento['documenti_contabilita_data_ritiro_merce']) && !$clone) : ?><?php echo $documento['documenti_contabilita_data_ritiro_merce']; ?><?php else : ?><?php echo date('d/m/Y'); ?><?php endif; ?>"
                                   data-name="documenti_contabilita_data_ritiro_merce"/> <span class="input-group-btn">
                                    <button class="btn btn-default" type="button" style="display:none">
                                        <i class="fa fa-calendar"></i>
                                    </button>
                                </span>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Porto: </label> <input type="text" class="form-control" placeholder="Porto"
                                                      name="documenti_contabilita_porto"
                                                      value="<?php echo (!empty($documento['documenti_contabilita_porto'])) ? $documento['documenti_contabilita_porto'] : ''; ?>"/>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Luogo di destinazione: </label> <textarea class="form-control"
                                                                         placeholder="Luogo di Destinazione" rows="3"
                                                                         name="documenti_contabilita_luogo_destinazione"><?php echo (!empty($documento['documenti_contabilita_luogo_destinazione'])) ? $documento['documenti_contabilita_luogo_destinazione'] : ''; ?></textarea>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Vettori: residenza e domicilio: </label> <textarea class="form-control"
                                                                                  placeholder="Annotazioni" rows="3"
                                                                                  name="documenti_contabilita_vettori_residenza_domicilio"><?php echo (!empty($documento['documenti_contabilita_vettori_residenza_domicilio'])) ? $documento['documenti_contabilita_vettori_residenza_domicilio'] : ''; ?></textarea>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Causale di trasporto: </label> <textarea class="form-control"
                                                                        placeholder="Causale trasporto" rows="3"
                                                                        name="documenti_contabilita_causale_trasporto"><?php echo (!empty($documento['documenti_contabilita_causale_trasporto'])) ? $documento['documenti_contabilita_causale_trasporto'] : ''; ?></textarea>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Annotazioni: </label> <textarea class="form-control" placeholder="Annotazioni" rows="3"
                                                               name="documenti_contabilita_annotazioni_trasporto"><?php echo (!empty($documento['documenti_contabilita_annotazioni_trasporto'])) ? $documento['documenti_contabilita_annotazioni_trasporto'] : ''; ?></textarea>
                    </div>
                </div>
            </div>

            <hr/>

            <div class="row">
                <div class="col-md-5">
                    <textarea name="documenti_contabilita_note" rows="10" class="form-control"
                              placeholder="Note pagamento [opzionali]"><?php if ($documento_id) : ?><?php echo $documento['documenti_contabilita_note_interne']; ?><?php endif; ?></textarea>
                </div>
                <div class="col-md-7 scadenze_box" style="background-color:#eeeeee;">
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Scadenza pagamento</h4>
                        </div>
                    </div>

                    <div class="row js_rows_scadenze">
                        <?php if ($documento_id && !$clone) : ?>
                            <?php foreach ($documento['scadenze'] as $key => $scadenza) : ?>
                                <div class="row row_scadenza">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Ammontare</label> <input type="text"
                                                                            name="scadenze[<?php echo $key; ?>][documenti_contabilita_scadenze_ammontare]"
                                                                            class="form-control documenti_contabilita_scadenze_ammontare decimal"

                                                                            placeholder="Ammontare"
                                                                            value="<?php echo number_format((float)$scadenza['documenti_contabilita_scadenze_ammontare'], 2,'.',''); ?>"
                                                                            data-name="documenti_contabilita_scadenze_ammontare"/>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Scadenza</label>
                                            <div class="input-group js_form_datepicker date ">
                                                <input type="text"
                                                       name="scadenze[<?php echo $key; ?>][documenti_contabilita_scadenze_scadenza]"
                                                       class="form-control"
                                                       placeholder="Scadenza"
                                                       value="<?php echo date('d/m/Y', strtotime($scadenza['documenti_contabilita_scadenze_scadenza'])); ?>"
                                                       data-name="documenti_contabilita_scadenze_scadenza"/> <span
                                                        class="input-group-btn">
                                                    <button class="btn btn-default" type="button"
                                                            style="display:none"><i
                                                                class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Metodo di pagamento</label> <select
                                                    name="scadenze[<?php echo $key; ?>][documenti_contabilita_scadenze_saldato_con]"

                                                    class="_select2 form-control _js_table_select2 _js_table_select2<?php echo $key; ?>"
                                                    data-name="documenti_contabilita_scadenze_saldato_con"
                                            >

                                                <?php foreach ($metodi_pagamento as $metodo_pagamento) : ?>
                                                    <option value="<?php echo $metodo_pagamento['documenti_contabilita_metodi_pagamento_id']; ?>" <?php if (stripos($scadenza['documenti_contabilita_scadenze_saldato_con'], $metodo_pagamento['documenti_contabilita_metodi_pagamento_id']) !== false): ?> selected="selected"<?php endif; ?>>
                                                        <?php echo ucfirst($metodo_pagamento['documenti_contabilita_metodi_pagamento_valore']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                                <!--
                                                <option value="Contanti" <?php if ($scadenza['documenti_contabilita_scadenze_saldato_con'] == 'Contanti'): ?> selectefd="selected"<?php endif; ?>>
                                                    Contanti
                                                </option>
                                                <option<?php if ($scadenza['documenti_contabilita_scadenze_saldato_con'] == 'Bonifico bancario'): ?> selectefd="selected"<?php endif; ?>>
                                                    Bonifico bancario
                                                </option>
                                                <option<?php if ($scadenza['documenti_contabilita_scadenze_saldato_con'] == 'Assegno'): ?> selectefd="selected"<?php endif; ?>>
                                                    Assegno
                                                </option>
                                                <option<?php if ($scadenza['documenti_contabilita_scadenze_saldato_con'] == 'RiBA'): ?> selectefd="selected"<?php endif; ?>>
                                                    RiBA
                                                </option>
                                                <option<?php if ($scadenza['documenti_contabilita_scadenze_saldato_con'] == 'Sepa RID'): ?> selectefd="selected"<?php endif; ?>>
                                                    Sepa RID
                                                </option>-->
                                            </select>

                                            <script>$('.js_table_select2<?php echo $key; ?>').val('<?php echo strtolower($scadenza['documenti_contabilita_scadenze_saldato_con']); ?>').trigger('change.select2');</script>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Data saldo</label>
                                            <div class="input-group js_form_datepicker date  field_68">
                                                <input type="text"
                                                       class="form-control documenti_contabilita_scadenze_data_saldo"
                                                       id="empty_date"
                                                       name="scadenze[<?php echo $key; ?>][documenti_contabilita_scadenze_data_saldo]"
                                                       data-name="documenti_contabilita_scadenze_data_saldo"
                                                       value="<?php echo ($scadenza['documenti_contabilita_scadenze_data_saldo']) ? date('d/m/Y', strtotime($scadenza['documenti_contabilita_scadenze_data_saldo'])) : ''; ?>"
                                                >

                                                <span class="input-group-btn">
                                                    <button class="btn btn-default" type="button" style="display:none;"><i
                                                                class="fa fa-calendar"></i></button>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : $key = -1; ?>
                        <?php endif; ?>
                        <div class="row row_scadenza">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Ammontare</label> <input type="text"

                                                                    name="scadenze[<?php echo $key + 1; ?>][documenti_contabilita_scadenze_ammontare]"
                                                                    class="form-control documenti_contabilita_scadenze_ammontare decimal"
                                                                    placeholder="Ammontare" value=""
                                                                    data-name="documenti_contabilita_scadenze_ammontare"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Scadenza</label>
                                    <div class="input-group js_form_datepicker date ">
                                        <input type="text"
                                               name="scadenze[<?php echo $key + 1; ?>][documenti_contabilita_scadenze_scadenza]"
                                               class="form-control"
                                               placeholder="Scadenza" value="<?php echo date('d/m/Y'); ?>"
                                               data-name="documenti_contabilita_scadenze_scadenza"/> <span
                                                class="input-group-btn">
                                            <button class="btn btn-default" type="button" style="display:none"><i
                                                        class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Metodo di pagamento</label>


                                    <select
                                            name="scadenze[<?php echo $key + 1; ?>][documenti_contabilita_scadenze_saldato_con]"
                                            class="select2 form-control js_table_select2"
                                            data-name="documenti_contabilita_scadenze_saldato_con">

                                        <?php foreach ($metodi_pagamento as $metodo_pagamento) : ?>
                                            <option value="<?php echo $metodo_pagamento['documenti_contabilita_metodi_pagamento_id']; ?>" <?php if ($metodo_pagamento['documenti_contabilita_metodi_pagamento_codice'] == 'MP05') : //bonifico?> selected="selected"<?php endif; ?>>
                                                <?php echo ucfirst($metodo_pagamento['documenti_contabilita_metodi_pagamento_valore']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Data saldo</label>
                                    <div class="input-group js_form_datepicker date  field_68">
                                        <input type="text" class="form-control"
                                               name="scadenze[<?php echo $key + 1; ?>][documenti_contabilita_scadenze_data_saldo]"
                                               id="empty_date"
                                               data-name="documenti_contabilita_scadenze_data_saldo" value="">

                                        <span class="input-group-btn">
                                            <button class="btn btn-default" type="button" style="display:none"><i
                                                        class="fa fa-calendar"></i></button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <?php /*
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <button style="display:none;" id="js_add_scadenza" class="btn btn-primary btn-sm">+ Aggiungi scadenza</button>
                        </div>
                    </div> */ ?>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <div id="msg_new_fattura" class="alert alert-danger hide"></div>
                </div>
            </div>
        </div>
    </div>


    <div class="form-actions fluid">
        <div class="col-md-offset-8 col-md-4">
            <div class="pull-right">
                <a href="<?php echo base_url(); ?>" class="btn default">Annulla</a>
                <button type="submit" class="btn btn-primary">Salva
                </button>
            </div>
        </div>

    </div>
    </div>
</form>

<script>

    $(".js_fattura_accompagnatoria_checkbox").change(function () {

        if ($(this).is(':checked')) {
            $(".js_fattura_accompagnatoria_row").removeClass('hide');
        } else {
            $(".js_fattura_accompagnatoria_row").addClass('hide');
        }


//        if (!$( ".js_fattura_accompagnatoria_row" ).hasClass('hide')) {
//            $( ".js_fattura_accompagnatoria_row" ).addClass('hide');
//        } else {
//            $( ".js_fattura_accompagnatoria_row" ).removeClass('hide');
//        }
    });

    $(".js_fattura_accompagnatoria_checkbox").trigger('change');
</script>

<script>
    
    var ricalcolaPrezzo = function (prezzo, prodotto) {
        //console.log('dentro funzione originale');
        return prezzo;
    }
    
    /****************** AUTOCOMPLETE Destinatario *************************/
    function initAutocomplete(autocomplete_selector) {

        autocomplete_selector.autocomplete({
            source: function (request, response) {

                $.ajax({
                    method: 'post',
                    url: base_url + "contabilita/documenti/autocomplete/<?php echo $entita; ?>",
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    /*search: function( event, ui ) {
                        loading(true);
                    },*/
                    success: function (data) {
                        var collection = [];
                        loading(false);

//                        console.log(autocomplete_selector.data("id"));
//                        if (data.count_total == 1) {
//                            popolaProdotto(data.results.data[0], autocomplete_selector.data("id"));
//                        } else {

                        $.each(data.results.data, function (i, p) {
                            <?php if ($campo_codice) : ?>
                            collection.push({
                                "id": p.<?php echo $campo_id; ?>,
                                "label": <?php if ($campo_preview) : ?>p.<?php echo $campo_codice; ?> + ' - ' + p.
                                <?php echo $campo_preview; ?><?php else: ?>'*impostare campo preview*'<?php endif; ?>,
                                "value": p
                            });
                            <?php else : ?>
                            collection.push({
                                "id": p.<?php echo $campo_id; ?>,
                                "label": <?php if ($campo_preview) : ?>p.
                                <?php echo $campo_preview; ?><?php else: ?>'*impostare campo preview*'<?php endif; ?>,
                                "value": p
                            });
                            <?php endif; ?>

                        });
//                        }

                        //console.log(collection);
                        response(collection);
                    }
                });
            },
            minLength: 2,
            select: function (event, ui) {
                // fix per disabilitare la ricerca con il tab
                if (event.keyCode === 9)
                    return false;

                //console.log(ui.item.value);
                popolaProdotto(ui.item.value, autocomplete_selector.data("id"));
                return false;
            }
        });
    }

    function popolaProdotto(prodotto, rowid) {
        <?php if ($campo_codice) : ?>
        $("input[name='products[" + rowid + "][documenti_contabilita_articoli_codice]']").val(prodotto['<?php echo $campo_codice; ?>']);
        <?php endif; ?>
        <?php if ($campo_unita_misura) : ?>
        $("input[name='products[" + rowid + "][documenti_contabilita_articoli_unita_misura]']").val(prodotto['<?php echo $campo_unita_misura; ?>']);
        <?php endif; ?>    
            
        <?php if ($campo_preview) : ?>
        $("input[name='products[" + rowid + "][documenti_contabilita_articoli_name]']").val(prodotto['<?php echo $campo_preview; ?>']);
        <?php endif; ?>
        <?php if ($campo_descrizione) : ?>
        $("textarea[name='products[" + rowid + "][documenti_contabilita_articoli_descrizione]']").html(prodotto['<?php echo $campo_descrizione; ?>']);
        <?php endif; ?>
            
        <?php if ($campo_prezzo) : ?>
            if ($('.js_documenti_contabilita_tipo').val() == 6 && '<?php echo $campo_prezzo_fornitore; ?>' != '') { //Se è un ordine fornitore e ho impostato un campo prezzo_fornitore nei settings
                prodotto['<?php echo $campo_prezzo_fornitore; ?>'] = ricalcolaPrezzo(prodotto['<?php echo $campo_prezzo_fornitore; ?>'],prodotto).toString().replace(',', '.');
                $("input[name='products[" + rowid + "][documenti_contabilita_articoli_prezzo]']").val(parseFloat(prodotto['<?php echo $campo_prezzo_fornitore; ?>'])).trigger('change');
            } else {
                prodotto['<?php echo $campo_prezzo; ?>'] = ricalcolaPrezzo(prodotto['<?php echo $campo_prezzo; ?>'],prodotto).toString().replace(',', '.');
                $("input[name='products[" + rowid + "][documenti_contabilita_articoli_prezzo]']").val(parseFloat(prodotto['<?php echo $campo_prezzo; ?>'])).trigger('change');
            }
        <?php endif; ?>
        <?php if ($campo_sconto) : ?>
        $("input[name='products[" + rowid + "][documenti_contabilita_articoli_sconto]']").val(prodotto['<?php echo $campo_sconto; ?>']).trigger('change');
        <?php endif; ?>

        <?php if ($campo_iva) : ?>

        if (isNaN(parseInt(prodotto['<?php echo $campo_iva; ?>']))) {
            //$("select[name='products["+rowid+"][documenti_contabilita_articoli_iva_id]']").val('0');
        } else {
//            console.log(parseInt(prodotto['<?php echo $campo_iva; ?>']));
//            console.log("select[name='products["+rowid+"][documenti_contabilita_articoli_iva_id]'] option[value='"+parseInt(prodotto['<?php echo $campo_iva; ?>'])+"']");
            $("select[name='products[" + rowid + "][documenti_contabilita_articoli_iva_id]'] option[value='" + parseInt(prodotto['<?php echo $campo_iva; ?>']) + "']").attr('selected', 'selected').trigger('change');
        }
        <?php endif; ?>
        $("input[name='products[" + rowid + "][documenti_contabilita_articoli_prodotto_id]']").val(prodotto['<?php echo $campo_id; ?>']).trigger('change');

        $("input[name='products[" + rowid + "][documenti_contabilita_articoli_quantita]']").val(1).trigger('change');

        calculateTotals();
    }


    $(document).ready(function () {


        /****************** AUTOCOMPLETE Destinatario *************************/
        $("#search_cliente").autocomplete({
            source: function (request, response) {
                $.ajax({
                    method: 'post',
                    url: base_url + "contabilita/documenti/autocomplete/" + $('[name="dest_entity_name"]').val(),
                    dataType: "json",
                    data: {
                        search: request.term
                    },
                    minLength: 0,
                    /*search: function( event, ui ) {
                        loading(true);
                    },*/
                    success: function (data) {
                        var collection = [];
                        loading(false);

//                        if (data.count_total == 1) {
//
//                            popolaCliente(data.results.data[0]);
//                        } else {

                        $.each(data.results.data, function (i, p) {
                            console.log(p);
                            if ($('[name="dest_entity_name"]').val() == 'clienti') {
                                collection.push({"id": p.clienti_id, "label": p.clienti_ragione_sociale, "value": p});
                            } else {
                                collection.push({"id": p.clienti_id, "label": p.fornitori_nome, "value": p});
                            }
                        });
//                        }

                        //console.log(collection);
                        response(collection);
                    }
                });
            },
            minLength: 3,
//            focus: function (event, ui) {
//                return false;
//            },
            select: function (event, ui) {
                // fix per disabilitare la ricerca con il tab
                if (event.keyCode === 9)
                    return false;

                //console.log(ui.item.value);
                if ($('[name="dest_entity_name"]').val() == 'clienti') {
                    popolaCliente(ui.item.value);
                } else {
                    popolaFornitore(ui.item.value);
                }

                //drawProdotto(ui.item.value, true);
                return false;
            }
        });


        function popolaCliente(cliente) {
            //Cambio la label
            $('#js_label_rubrica').html('Modifica e sovrascrivi anagrafica');
            $('[name="documenti_contabilita_metodo_pagamento"]').val(cliente['documenti_contabilita_metodi_pagamento_valore']);
            $('[data-name="documenti_contabilita_scadenze_saldato_con"]').val(cliente['documenti_contabilita_metodi_pagamento_valore']);
            $('.js_dest_ragione_sociale').val(cliente['clienti_ragione_sociale']);
            $('.js_dest_indirizzo').val(cliente['clienti_indirizzo']);
            $('.js_dest_citta').val(cliente['clienti_citta']);
            $('.js_dest_nazione').val(cliente['clienti_nazione']);
            $('.js_dest_cap').val(cliente['clienti_cap']);
            $('.js_dest_provincia').val(cliente['clienti_provincia']);
            $('.js_dest_partita_iva').val(cliente['clienti_partita_iva']);
            $('.js_dest_codice_fiscale').val(cliente['clienti_codice_fiscale']);
            $('.js_dest_codice_sdi').val(cliente['clienti_codice_sdi']);
            $('.js_dest_pec').val(cliente['clienti_pec']);
            $('.js_dest_nome_banca').val(cliente['clienti_nome_banca']);
            $('.js_dest_iban').val(cliente['clienti_iban']);
            $('#js_dest_id').val(cliente['clienti_id']).trigger('change');
        }

        function popolaFornitore(fornitore) {
            //Cambio la label
            $('#js_label_rubrica').html('Modifica e sovrascrivi anagrafica');
            $('[name="documenti_contabilita_metodo_pagamento"]').val(fornitore['documenti_contabilita_metodi_pagamento_valore']);
            $('.js_dest_ragione_sociale').val(fornitore['fornitori_nome']);
            $('.js_dest_indirizzo').val(fornitore['fornitori_indirizzo']);
            $('.js_dest_citta').val(fornitore['fornitori_citta']);
            $('.js_dest_nazione').val(fornitore['fornitori_nazione']);
            $('.js_dest_cap').val(fornitore['fornitori_cap']);
            $('.js_dest_provincia').val(fornitore['fornitori_provincia']);
            $('.js_dest_partita_iva').val(fornitore['fornitori_partita_iva']);
            $('.js_dest_codice_fiscale').val(fornitore['fornitori_telefono']);
            $('#js_dest_id').val(fornitore['fornitori_id']);
        }


        initAutocomplete($('.js_autocomplete_prodotto'));

        $('.js_select2').each(function () {
            var select = $(this);
            var placeholder = select.attr('data-placeholder');
            select.select2({
                placeholder: placeholder ? placeholder : '',
                allowClear: true
            });
        });

        <?php if ($documento_id) : ?>
        calculateTotals(<?php echo (!$clone) ? $documento_id : ''; ?>);
        
        $('#js_dest_id').filter(function() {return !this.value;}).trigger('change');
        
        <?php endif; ?>
    });
</script>


<script>
    
    $(document).ready(function () {
        var tipo_documento = $('.js_documenti_contabilita_tipo').val();
        $('.js_btn_tipo').click(function (e) {
            var tipo = $(this).data('tipo');

            //Cambio eventuali label
            $('.scadenze_box').show();
            switch (tipo) {
                case 6:
                    $('.js_dest_type').html('fornitore');
                    $('[name="dest_entity_name"]').val('fornitori');
                    if (tipo_documento != tipo) {

                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', false);
                        $.uniform.update();
                    }
                    break;
                case 3:
                    if (tipo_documento != tipo) {

                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', false);
                        $.uniform.update();
                    }
                    break;
                case 1:
                case 2:
                case 4:
                    if (tipo_documento != tipo<?php if (!$documento_id) : ?> || true<?php endif; ?>) {

                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', true);
                        $.uniform.update();
                    }
                    break;
                case 7:
                    if (tipo_documento != tipo) {

                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', false);
                        $.uniform.update();
                    }
                    $('.js_dest_type').html('cliente');
                    $('.scadenze_box').show();
                    $('[name="dest_entity_name"]').val('clienti');
                    break;
                case 5:
                    if (tipo_documento != tipo) {

                        //Nascondo blocco scadenze
                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', false);
                        $.uniform.update();
                    }
                    $('.js_dest_type').html('cliente');
                    $('.scadenze_box').hide();
                    $('[name="dest_entity_name"]').val('clienti');
                    break;
                case 8: //DDT

                    $('.scadenze_box').hide();
                    if (tipo_documento != tipo) {
                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', false);
                        $.uniform.update();
                    }
                    break;
                default:
                    if (tipo_documento != tipo) {

                        $('[name=documenti_contabilita_formato_elettronico]').prop('checked', false);
                        $.uniform.update();
                    }
                    break;
            }

            $('.js_btn_tipo').removeClass('btn-primary');
            $('.js_btn_tipo').addClass('btn-default');
            $(this).addClass('btn-primary');
            $(this).removeClass('btn-default');
            $('.js_documenti_contabilita_tipo').val(tipo).trigger('change');
            if (tipo_documento != tipo) {
                getNumeroDocumento();
            }
            tipo_documento = tipo;

            //getNumeroDocumento();
        });
        $('.js_btn_tipo[data-tipo="'+$('.js_documenti_contabilita_tipo').val()+'"]').trigger('click');
    });
    $('.documenti_contabilita_valuta').on('change', function () {
        
        //Se il tasso di cambio è uguale al default, nascondo il tasso di cambio perchè non ha senso
        if ($('option:selected', $(this)).data('id') == '<?php echo $impostazioni['documenti_contabilita_settings_valuta_base']; ?>') {
            $('.documenti_contabilita_tasso_di_cambio').parent().hide();
        } else {
            //Ajax per chiedere il tasso di cambio
            $.ajax({
                method: 'get',
                dataType: "json",
                url: base_url + "contabilita/documenti/tassoDiCambio/"+$('option:selected', $(this)).data('id'),
                success: function (data) {
                    $('.documenti_contabilita_tasso_di_cambio').val(data.tassi_di_cambio_tasso); 
                    console.log(data);
                }
            });
            $('.documenti_contabilita_tasso_di_cambio').parent().show();
        }
    });
    $('.documenti_contabilita_valuta').trigger('change');
    

    function getNumeroAjax(tipo, serie) {
        $.ajax({
            method: 'post',
            data: {data_emissione: $('[name="documenti_contabilita_data_emissione"]').val()},
            url: base_url + "contabilita/documenti/numeroSucessivo/" + $('.js_documenti_contabilita_tipo').val() + '/' + $('.js_documenti_contabilita_serie').val(),
            success: function (numero) {
                $('[name="documenti_contabilita_numero"]').val(numero);
            }
        });
    }

    function getNumeroDocumento() {
        var is_modifica = !isNaN($('[name="documento_id"]').val());
        var tipo = $('.js_btn_tipo.btn-primary').data('tipo');
        var serie = $('.js_btn_serie.button_selected').data('serie');
        if (is_modifica) {
            if (tipo == '<?php echo (empty($documento['documenti_contabilita_tipo'])) ? 'XXX' : $documento['documenti_contabilita_tipo']; ?>' && serie == '<?php echo (empty($documento['documenti_contabilita_serie'])) ? 'XXX' : $documento['documenti_contabilita_serie']; ?>') {
                $('[name="documenti_contabilita_numero"]').val(<?php echo (!empty($documento['documenti_contabilita_numero'])) ? $documento['documenti_contabilita_numero'] : ''; ?>);
            } else {
                getNumeroAjax(tipo, serie);
            }
        } else {
            getNumeroAjax(tipo, serie);
        }
    }

    $('.js_btn_serie').click(function (e) {
        if ($(this).hasClass('button_selected')) {
            $('.js_btn_serie').removeClass('button_selected');
            $('.js_documenti_contabilita_serie').val('');
        } else {
            $('.js_btn_serie').removeClass('button_selected');
            $(this).addClass('button_selected');

            $('.js_documenti_contabilita_serie').val($(this).data('serie'));
        }
        getNumeroDocumento();
    });
    
    if (!$('.js_documenti_contabilita_tipo').val()) {
        $('.js_btn_tipo').first().trigger('click');
    }
    $('[name="documenti_contabilita_data_emissione"]').on('change', function () {
        //getNumeroDocumento();
    });

    <?php if (empty($documento['documenti_contabilita_numero']) || $clone) : ?>
    $('.js_btn_tipo[data-tipo="'+$('.js_documenti_contabilita_tipo').val()+'"]').trigger('click');
    getNumeroDocumento();
    //$('.js_btn_serie').first().trigger('click');
    <?php endif ;?>


    var totale = 0;
    var totale_iva = 0;
    var competenze = 0;
    var competenze_scontate = 0;
    var competenze_no_ritenute = 0;
    var iva_perc_max = 0;
    var rivalsa_inps_percentuale = 0;
    var rivalsa_inps_valore = 0;

    var competenze_con_rivalsa = 0;

    var cassa_professionisti_perc = 0;
    var cassa_professionisti_valore = 0;

    var imponibile = 0;

    var ritenuta_acconto_perc = 0;
    var ritenuta_acconto_perc_sull_imponibile = 0;

    function reverseRowCalculate(tr) {
        //Calcolo gli importi basandomi sul totale...
        var qty = parseFloat($('.js_documenti_contabilita_articoli_quantita', tr).val());
        var sconto = parseFloat($('.js_documenti_contabilita_articoli_sconto', tr).val());
        var iva = parseFloat($('.js_documenti_contabilita_articoli_iva_id option:selected', tr).data('perc'));

        if (isNaN(qty)) {
            qty = 0;
        }
        if (isNaN(sconto)) {
            sconto = 0;
        }
        if (isNaN(iva)) {
            iva = 0;
        }

        var importo_ivato = parseFloat($('.js-importo', tr).val());

        //Applico lo sconto al rovescio
        var importo = parseFloat(importo_ivato / ((100 + iva) / 100));
        var importo_ricalcolato = parseFloat(importo_ivato - ((importo_ivato / 100) * sconto));


        //console.log(importo);

        $('.js-importo', tr).val(importo_ricalcolato.toFixed(2));
        $('.js_documenti_contabilita_articoli_prezzo', tr).val(importo.toFixed(2));
//
        calculateTotals();
    }

    function calculateTotals(documento_id) {
        totale = 0;
        iva_perc_max = 0;
        totale_iva = 0;
        totale_iva_divisa = {};
        totale_imponibile_divisa = {};
        competenze = 0;
        competenze_scontate = 0;
        competenze_no_ritenute = 0;
        sconto_totale = $('.js_sconto_totale').val();
        if(sconto_totale == 0){
            $('label.competenze_scontate').hide();
        } else {
            $('label.competenze_scontate').show();
        }
        $('#js_product_table tbody tr:not(.hidden)').each(function () {
            var qty = parseFloat($('.js_documenti_contabilita_articoli_quantita', $(this)).val());
            var prezzo = parseFloat($('.js_documenti_contabilita_articoli_prezzo', $(this)).val());
            var sconto = parseFloat($('.js_documenti_contabilita_articoli_sconto', $(this)).val());
            var iva = parseFloat($('.js_documenti_contabilita_articoli_iva_id option:selected', $(this)).data('perc'));
            var iva_id = parseFloat($('.js_documenti_contabilita_articoli_iva_id option:selected', $(this)).val());
            var appl_ritenute = $('.js-applica_ritenute', $(this)).is(':checked');
            var appl_sconto = $('.js-applica_sconto', $(this)).is(':checked');

            //console.log(appl_ritenute);

            iva_perc_max = Math.max(iva_perc_max, iva);
            if (iva_perc_max == iva) {
                
                iva_id_perc_max = iva_id;
            }

            if (isNaN(qty)) {
                qty = 0;
            }
            if (isNaN(prezzo)) {
                prezzo = 0;
            }
            if (isNaN(sconto)) {
                sconto = 0;
            }
            if (isNaN(iva)) {
                iva = 0;
            }
//            console.log(qty);
//            console.log(prezzo);
//            console.log(sconto);
//            console.log(iva);
            var totale_riga = prezzo * qty;
            var totale_riga_scontato = (totale_riga / 100) * (100 - sconto);
            var totale_riga_scontato_con_sconto_totale = totale_riga_scontato;

            //competenze += totale_riga_scontato;

            if (appl_sconto) {
                competenze += totale_riga_scontato;
                competenze_scontate += (totale_riga_scontato * (100 - sconto_totale) / 100) ;
                totale_riga_scontato_con_sconto_totale = parseFloat(totale_riga_scontato * (100 - sconto_totale) / 100) ;
            } else {
                competenze += totale_riga_scontato;
                competenze_scontate += totale_riga_scontato;
            }
            var totale_riga_scontato_ivato = parseFloat((totale_riga_scontato_con_sconto_totale * (100 + iva) ) / 100);

            if (totale_riga_scontato_ivato != totale_riga_scontato_con_sconto_totale) {
//                 console.log(totale_riga_scontato_ivato);
//                 console.log(totale_riga_scontato_con_sconto_totale);
            }

            if (!appl_ritenute) {
                competenze_no_ritenute += totale_riga_scontato_con_sconto_totale;
            }

            if (totale_iva_divisa[iva_id] == undefined) {
                console.log(totale_iva_divisa);
                totale_iva_divisa[iva_id] = [iva, parseFloat((totale_riga_scontato_con_sconto_totale / 100) * iva)];
                totale_imponibile_divisa[iva_id] = [iva, totale_riga_scontato_con_sconto_totale];
            } else {
                totale_iva_divisa[iva_id][1] += parseFloat((totale_riga_scontato_con_sconto_totale / 100) * iva);
                totale_imponibile_divisa[iva_id][1] += totale_riga_scontato_con_sconto_totale;
            }
//            console.log(totale_riga);
//            console.log(totale_riga_scontato);
//            console.log(totale_riga_scontato_con_sconto_totale);
//            
//console.log(totale_iva_divisa);

            totale_iva += parseFloat((totale_riga_scontato_con_sconto_totale / 100) * iva);
            totale += totale_riga_scontato_ivato;


            $('.js-importo', $(this)).val(totale_riga_scontato_ivato.toFixed(2));
            $('.js_documenti_contabilita_articoli_iva', $(this)).val(parseFloat((totale_riga_scontato / 100) * iva).toFixed(2));
            $('.js_riga_imponibile', $(this)).html(parseFloat(totale_riga).toFixed(2));

        });




        rivalsa_inps_percentuale = parseFloat($('[name="documenti_contabilita_rivalsa_inps_perc"]').val());
        rivalsa_inps_valore = parseFloat(((competenze_scontate - competenze_no_ritenute) / 100) * rivalsa_inps_percentuale);

        competenze_con_rivalsa = competenze_scontate + rivalsa_inps_valore;

        cassa_professionisti_perc = parseFloat($('[name="documenti_contabilita_cassa_professionisti_perc"]').val());
        cassa_professionisti_valore = parseFloat(((competenze_con_rivalsa - competenze_no_ritenute) / 100) * cassa_professionisti_perc);

        imponibile = competenze_con_rivalsa + cassa_professionisti_valore;

        var applica_split_payment = $('[name="documenti_contabilita_split_payment"]').is(':checked');

        var totale_imponibili_iva_diverse_da_max = 0;
        var totale_iva_diverse_da_max = 0;
        for (var iva_id in totale_iva_divisa) {
            if (totale_iva_divisa[iva_id][0] != iva_perc_max) {
                if (totale_iva_divisa[iva_id][0] != 0) {
                    totale_imponibili_iva_diverse_da_max += parseFloat((totale_iva_divisa[iva_id][1] / totale_iva_divisa[iva_id][0]) * 100);
                } else {
                    totale_imponibili_iva_diverse_da_max += totale_imponibile_divisa[iva_id][1];

                }
                totale_iva_diverse_da_max += parseFloat(totale_iva_divisa[iva_id][1]);
            }
        }

        //Aggiungo alla iva massima, ciò che manca tenendo conto delle modifiche ai totali dovute a rivalsa e cassa
//        console.log(imponibile);
//        console.log(totale_imponibili_iva_diverse_da_max);
        console.log(iva_perc_max);
        console.log(iva_id_perc_max);
        console.log(totale_iva_divisa);
        totale_iva_divisa[iva_id_perc_max][1] = parseFloat(((imponibile - totale_imponibili_iva_diverse_da_max) / 100) * iva_perc_max);

//        alert('imponibile '+imponibile);
//        alert('totale' + totale);
//        alert('totale ivato' + totale_iva);
//        alert('competenze scontate' + competenze_scontate);
//        alert('???' + competenze_scontate / 100 * 22);

        //Valuto le ritenute
        ritenuta_acconto_perc = parseFloat($('[name="documenti_contabilita_ritenuta_acconto_perc"]').val());
        ritenuta_acconto_perc_sull_imponibile = parseFloat($('[name="documenti_contabilita_ritenuta_acconto_perc_imponibile"]').val());
        ritenuta_acconto_valore_sull_imponibile = ((competenze_con_rivalsa - competenze_no_ritenute) / 100) * ritenuta_acconto_perc_sull_imponibile;
        totale_ritenuta = (ritenuta_acconto_valore_sull_imponibile / 100) * ritenuta_acconto_perc;

        //console.log(totale_iva_divisa);
        totale = imponibile + totale_iva_diverse_da_max + totale_iva_divisa[iva_id_perc_max][1] - totale_ritenuta;

        $('[name="documenti_contabilita_rivalsa_inps_valore"]').val(rivalsa_inps_valore);
        $('[name="documenti_contabilita_competenze_lordo_rivalsa"]').val(competenze_con_rivalsa);
        if (rivalsa_inps_percentuale && rivalsa_inps_valore > 0) {
            $('.js_rivalsa').html('Rivalsa INPS ' + rivalsa_inps_percentuale + '% <span>€ ' + rivalsa_inps_valore.toFixed(2) + '</span>').show();
            $('.js_competenze_rivalsa').html('Competenze (al lordo della rivalsa)<span>€ ' + competenze_con_rivalsa.toFixed(2) + '</span>').show();
        } else {
            $('.js_rivalsa').hide();
            $('.js_competenze_rivalsa').hide();
        }

        $('[name="documenti_contabilita_cassa_professionisti_valore"]').val(cassa_professionisti_valore);
        $('[name="documenti_contabilita_imponibile"]').val(imponibile.toFixed(2));
        $('[name="documenti_contabilita_imponibile_scontato"]').val(competenze_scontate.toFixed(2));

        if (cassa_professionisti_perc && cassa_professionisti_valore > 0) {
            $('.js_cassa_professionisti').html('Cassa professionisti ' + cassa_professionisti_perc + '% <span>€ ' + cassa_professionisti_valore.toFixed(2) + '</span>').show();
            $('.js_imponibile').html('Imponibile <span>€ ' + imponibile.toFixed(2) + '</span>').show();
        } else {
            $('.js_cassa_professionisti').hide();
            $('.js_imponibile').hide();
        }


        $('[name="documenti_contabilita_ritenuta_acconto_valore"]').val(totale_ritenuta);
        $('[name="documenti_contabilita_ritenuta_acconto_imponibile_valore"]').val(ritenuta_acconto_valore_sull_imponibile);
        if (ritenuta_acconto_perc > 0 && ritenuta_acconto_perc_sull_imponibile > 0 && totale_ritenuta > 0) {
            $('.js_ritenuta_acconto').html('Ritenuta d\'acconto -' + ritenuta_acconto_perc + '% di &euro; ' + ritenuta_acconto_valore_sull_imponibile.toFixed(2) + '<span>€ ' + totale_ritenuta.toFixed(2) + '</span>').show();
        } else {
            $('.js_ritenuta_acconto').hide();
        }

        $('[name="documenti_contabilita_competenze"]').val(competenze);
        $('.js_competenze').html('€ ' + competenze.toFixed(2));
        $('.js_competenze_scontate').html('€ ' + competenze_scontate.toFixed(2));

        $(".js_tot_iva:not(:first)").remove();
        $(".js_tot_iva:first").hide();


        $('[name="documenti_contabilita_iva_json"]').val(JSON.stringify(totale_iva_divisa));
        $('[name="documenti_contabilita_imponibile_iva_json"]').val(JSON.stringify(totale_imponibile_divisa));

        for (var iva_id in totale_iva_divisa) {

            //console.log(totale_iva_divisa);

            $(".js_tot_iva:last").clone().insertAfter(".js_tot_iva:last").show();
            $('.js_tot_iva:last').html(`IVA (` + (totale_iva_divisa[iva_id][0]) + `%): <span>€ ` + totale_iva_divisa[iva_id][1].toFixed(2) + `</span>`);//'€ '+totale_iva.toFixed(2));
        }

        if (applica_split_payment) {
            $('.js_split_payment').html('Iva non dovuta (split payment) <span>€ -' + (totale_iva_diverse_da_max + totale_iva_divisa[iva_id_perc_max][1]).toFixed(2) + '</span>').show();
            totale -= (totale_iva_diverse_da_max + totale_iva_divisa[iva_id_perc_max][1]);
        } else {
            $('.js_split_payment').hide();
        }

        $('.js_tot_da_saldare').html('€ ' + totale.toFixed(2));

        $('[name="documenti_contabilita_totale"]').val(totale.toFixed(2));
        $('[name="documenti_contabilita_iva"]').val(totale_iva.toFixed(2));

        if (isNaN(documento_id)) {
            $('.documenti_contabilita_scadenze_ammontare').val(totale.toFixed(2));
            $('.documenti_contabilita_scadenze_ammontare:first').trigger('change');
        } else {
            //$('.documenti_contabilita_scadenze_ammontare:last').closest('.row_scadenza').remove();
            $('.documenti_contabilita_scadenze_ammontare:last').trigger('change');
        }

    }

    function increment_scadenza() {
        var counter_scad = $('.row_scadenza').length;
        var rows_scadenze = $('.js_rows_scadenze');
        // Fix per clonare select inizializzata
        if ($('.js_table_select2').filter(':first').data('select2')) {
            $('.js_table_select2').filter(':first').select2('destroy');
        } else {

        }

        var newScadRow = $('.row_scadenza').filter(':first').clone();
        $('.documenti_contabilita_scadenze_data_saldo', newScadRow).val('');
        // Fix per clonare select inizializzata
        $('.js_table_select2').filter(':first').select2();

        /* Line manipulation begin */
        //newScadRow.removeClass('hidden');
        $('input, select, textarea', newScadRow).each(function () {
            var control = $(this);
            var name = control.attr('data-name');
            control.attr('name', 'scadenze[' + counter_scad + '][' + name + ']').removeAttr('data-name');
        });

        $('.js_table_select2', newScadRow).select2({
            //placeholder: "Seleziona prodotto",
            allowClear: true
        });

        $('.js_form_datepicker input', newScadRow).datepicker({
            todayBtn: 'linked',
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            weekStart: 1,
            language: 'it'
        });

        /* Line manipulation end */
        counter_scad++;
        newScadRow.appendTo(rows_scadenze);
    }

    $(document).ready(function () {
        var table = $('#js_product_table');
        var body = $('tbody', table);
        var rows = $('tr', body);
        var increment = $('#js_add_product', table);

        var rows_scadenze = $('.js_rows_scadenze');
        //var increment_scadenza = $('#js_add_scadenza');


        var firstRow = rows.filter(':first');
        var counter = rows.length;

        $('#new_fattura').on('change', '[name="documenti_contabilita_split_payment"], [name="documenti_contabilita_rivalsa_inps_perc"],[name="documenti_contabilita_cassa_professionisti_perc"],[name="documenti_contabilita_ritenuta_acconto_perc"],[name="documenti_contabilita_ritenuta_acconto_perc_imponibile"]', function () {
            calculateTotals();
        });

        table.on('change', '.js-applica_ritenute,.js-applica_sconto, .js_documenti_contabilita_articoli_quantita, .js_documenti_contabilita_articoli_prezzo, .js_documenti_contabilita_articoli_sconto, .js_documenti_contabilita_articoli_iva_id',
            function () {
                //console.log('dentro');
                setTimeout("calculateTotals()", 500);
            });

        table.on('change', '.js-importo', function () {

            reverseRowCalculate($(this).closest('tr'));
        });

        // Aggiungi prodotto
        increment.on('click', function () {
            var newRow = firstRow.clone();

            /* Line manipulation begin */
            newRow.removeClass('hidden');
            $('input, select, textarea', newRow).each(function () {
                var control = $(this);
                var name = control.attr('data-name');
                if (name) {
                    control.attr('name', 'products[' + counter + '][' + name + ']').removeAttr('data-name');
                }
                //control.val("");
            });

            $('.js_table_select2', newRow).select2({
                placeholder: "Seleziona prodotto",
                allowClear: true
            });
            $('.js_autocomplete_prodotto', newRow).data('id', counter);
            initAutocomplete($('.js_autocomplete_prodotto', newRow));

            /* Line manipulation end */

            counter++;
            newRow.appendTo(body);
        });


        table.on('click', '.js_remove_product', function () {
            $(this).parents('tr').remove();
            calculateTotals();
        });
        $('#offerproducttable .js_remove_product').on('click', function () {
            $(this).parents('tr').remove();
        });

        $('.js_sconto_totale').on('change', function () {
            calculateTotals();
        });

        //Se cambio una scadenza ricalcolo il parziale di quella sucessiva, se c'è. Se non c'è la creo.
        rows_scadenze.on('change', '.documenti_contabilita_scadenze_ammontare', function () {
            //Se la somma degli ammontare è minore del totale procedo
            var totale_scadenze = 0;
            $('.documenti_contabilita_scadenze_ammontare').each(function () {
                totale_scadenze += parseFloat($(this).val());
            });

            /*
             * La logica è questa:
             * 1. se le scadenza superano l'importo totale, metto a posto togliendo ricorsivamente la riga sucessiva finchè non entro nel caso 2
             * 2. se le scadenza non superano l'importo totale, tolgo tutte le righe sucessiva all'ultima modificata, ne creo una nuova e forzo importo corretto sull'ultima
             */
            next_row_exists = $(this).closest('.row_scadenza').next('.row_scadenza').length != 0;

            if (totale_scadenze < totale) {
                if (next_row_exists) {
                    //console.log('Rimuovo tutte le righe dopo e ritriggherò, così entra nell\'if precedente...');
                    $(this).closest('.row_scadenza').next('.row_scadenza').remove();
                    $(this).trigger('change');
                } else {
                    //console.log('Non esiste scadenza successiva. Creo...');
                    //$('#js_add_scadenza').trigger('click');
                    increment_scadenza();
                    next_row = $(this).closest('.row_scadenza').next('.row_scadenza');
                    $('.documenti_contabilita_scadenze_ammontare', next_row).val((totale - totale_scadenze).toFixed(2));
                }
            } else {
                if (next_row_exists) {
                    //console.log('Rimuovo tutte le righe dopo e ritriggherò, così entra nell\'if precedente...');
                    $(this).closest('.row_scadenza').next('.row_scadenza').remove();
                    $(this).trigger('change');
                } else {
                    //console.log('Non esiste scadenza successiva. Tutto a posto ma nel dubbio forzo questa = alla differenza tra totale e totale scadenze');
                    $(this).val((totale - (totale_scadenze - $(this).val())).toFixed(2));

                }
            }

        });

        if (rows.length < 2) {
            increment.click();
        }
    });
</script>


<script>
    $(document).ready(function () {
        // trigger click on add product when tabkey is pressed and focus on last codice
        $('#js_add_product').on('keyup', function(e) {
            $(this).trigger('click');
            $('.js_documenti_contabilita_articoli_codice:last').focus();
        });
        
        //se il selettore è su "Non ancora saldato", il campo "Data saldo" viene svuotata
        $(".select2").on("change", function () {
            //console.log('entrato');
            if ($('#empty_select').val() == "") {
                //console.log('entrato if');
                $("#empty_date").val("");
            }
        });

        $('#js_dtable').dataTable({
            aoColumns: [null, null, null, null, null, null, null, {bSortable: false}],
            aaSorting: [[0, 'desc']]
        });
        $('#js_dtable_wrapper .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        $('#js_dtable_wrapper .dataTables_length select').addClass("form-control input-xsmall"); // modify table per page dropdown

        $('.decimal').keyup(function () {
            var val = $(this).val().replace(',', '.');
            if (isNaN(val)) {
                val = val.replace(/[^0-9\.]/g, '');
                if (val.split('.').length > 2)
                    val = val.replace(/\.+$/, "");
            }
            $(this).val(val);
        });
    });
</script>
<!-- END Module Related Javascript -->
