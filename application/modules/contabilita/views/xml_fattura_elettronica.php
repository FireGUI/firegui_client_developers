<?php

//debug($fattura);

$this->load->config('geography');
$nazioniReversed = array_flip($this->config->item('nazioni'));

//die($dati['fattura']['documenti_contabilita_destinatario']);

$destinatario = json_decode($dati['fattura']['documenti_contabilita_destinatario'], true);
$contabilita_settings = $this->apilib->searchFirst('documenti_contabilita_settings');

$codice_fiscale = (!empty($contabilita_settings['documenti_contabilita_settings_company_codice_fiscale']) ? $contabilita_settings['documenti_contabilita_settings_company_codice_fiscale'] : die('Codice Fiscale non impostato.'));
/*
// Siamo sicuri di forzarlo a 0000000 se manca?
$codice_destinatario = (!empty($destinatario['codice_sdi']) ? $destinatario['codice_sdi'] : '0000000');

if ($codice_destinatario !== '0000000') {
    $pec = (!empty($destinatario['pec']) ? $destinatario['pec'] : die('Pec e codice destinatario non impostati.'));
} else {
    $pec = (!empty($destinatario['pec']) ? $destinatario['pec'] : '');
}
*/


// FIX Manuel 3 gennaio 2019: Se 0000000 = Privato quindi no check PEC e SDI. In alternativa uno dei due ci deve essere

if ($destinatario['codice_sdi'] !== '0000000' || !empty($contabilita_settings['partita_iva'])) {
    if (empty($destinatario['codice_sdi']) && empty($destinatario['pec'])) {
        die("Per le aziende la PEC o il Codice destinatario SDI devono essere compilati");
    }
}
$codice_destinatario = (!empty($destinatario['codice_sdi']) ? $destinatario['codice_sdi'] : '0000000');
$pec = (!empty($destinatario['pec']) ? $destinatario['pec'] : '');

/*echo "<pre>";
print_r($contabilita_settings);
exit();*/
$country = (!empty($contabilita_settings['documenti_contabilita_settings_company_country']) ? strtoupper($nazioniReversed[$contabilita_settings['documenti_contabilita_settings_company_country']]) : die('Nazione non impostata'));
$vat_num = (!empty($contabilita_settings['documenti_contabilita_settings_company_vat_number']) ? $contabilita_settings['documenti_contabilita_settings_company_vat_number'] : die('Partita iva mittente non impostata'));
$company_name = (!empty($contabilita_settings['documenti_contabilita_settings_company_name']) ? $contabilita_settings['documenti_contabilita_settings_company_name'] : die('Nome azienda non mittente impostato'));
$regime_fiscale = (!empty($contabilita_settings['documenti_contabilita_regimi_fiscali_valore']) ? $contabilita_settings['documenti_contabilita_regimi_fiscali_valore'] : die('Regime fiscale non definito'));
$company_address = (!empty($contabilita_settings['documenti_contabilita_settings_company_address']) ? $contabilita_settings['documenti_contabilita_settings_company_address'] : die('Indirizzo azienda mittente non impostato'));
$company_cap = (!empty($contabilita_settings['documenti_contabilita_settings_company_zipcode']) ? $contabilita_settings['documenti_contabilita_settings_company_zipcode'] : die('CAP azienda mittente non impostato'));
$company_city = (!empty($contabilita_settings['documenti_contabilita_settings_company_city']) ? strtoupper($contabilita_settings['documenti_contabilita_settings_company_city']) : die('Città azienda non impostata'));
$company_province = (!empty($contabilita_settings['documenti_contabilita_settings_company_province']) ? $contabilita_settings['documenti_contabilita_settings_company_province'] : die('Provincia azienda non impostata'));

$company_ufficio_rea = (!empty($contabilita_settings['documenti_contabilita_settings_company_ufficio_rea']) ? $contabilita_settings['documenti_contabilita_settings_company_ufficio_rea'] : '');
$company_numero_rea = (!empty($contabilita_settings['documenti_contabilita_settings_company_numero_rea']) ? $contabilita_settings['documenti_contabilita_settings_company_numero_rea'] : '');
$company_capitale_sociale = (!empty($contabilita_settings['documenti_contabilita_settings_company_capitale_sociale']) ? $contabilita_settings['documenti_contabilita_settings_company_capitale_sociale'] : '');
$company_socio_unico = (!empty($contabilita_settings['documenti_contabilita_settings_socio_unico_value']) ? $contabilita_settings['documenti_contabilita_settings_socio_unico_value'] : '');
$company_stato_liquidazione = (!empty($contabilita_settings['documenti_contabilita_settings_stato_liquidazione_value']) ? $contabilita_settings['documenti_contabilita_settings_stato_liquidazione_value'] : '');

$dest_nazione = (strlen($destinatario['nazione']) > 2) ? strtoupper($nazioniReversed[$destinatario['nazione']]) : $destinatario['nazione'];
$dest_codicefiscale = ($destinatario['codice_fiscale']);

$dest_ragionesociale = ($destinatario['ragione_sociale']);

if ($dest_nazione == 'IT') {
    $dest_partitaiva = ($destinatario['partita_iva']);
} else {
    $dest_partitaiva = $dest_ragionesociale;
}

$dest_indirizzo = ($destinatario['indirizzo']);
$dest_cap = ($destinatario['cap']);
$dest_citta = ($destinatario['citta']);
$dest_provincia = ($destinatario['provincia']);

//$dest_nazione = (strtoupper($nazioniReversed[$destinatario['nazione']]));
$fattura_valuta = $dati['fattura']['documenti_contabilita_valuta'];
$fattura_dataemissione = date("Y-m-d", strtotime($dati['fattura']['documenti_contabilita_data_emissione']));
$fattura_numero = $dati['fattura']['documenti_contabilita_numero'];
$fattura_serie = $dati['fattura']['documenti_contabilita_serie'];
if ($fattura_serie) {
    $fattura_numero = "{$fattura_numero}/{$fattura_serie}";
}
$fattura_id = $dati['fattura']['documenti_contabilita_id'];
$articoli = $dati['fattura']['articoli'];
$fattura_metodopagamento = $dati['fattura']['documenti_contabilita_metodo_pagamento'];
$imponibile_scontato = $dati['fattura']['documenti_contabilita_imponibile_scontato'];
$fattura_totale = $dati['fattura']['documenti_contabilita_totale'];

$conto_corrente_iban = (!empty($dati['fattura']['conti_correnti_iban'])) ? $dati['fattura']['conti_correnti_iban'] : '';
$conto_corrente_nome_istituto = (!empty($dati['fattura']['conti_correnti_nome_istituto'])) ? $dati['fattura']['conti_correnti_nome_istituto'] : '';

switch ($dati['fattura']['documenti_contabilita_tipo']) {
    case 1:
    case '1':
        $fattura_tipo = 'TD01';
        break;
    case 4:
    case '4':
        $fattura_tipo = 'TD04';
        break;
    default:
        die("Tipo documento '{$dati['fattura']['documenti_contabilita_tipo']}:{$dati['fattura']['documenti_contabilita_tipo_value']}' non riconosciuto!");
        break;
}

$tipo_ritenuta = (!empty($dest_partitaiva)) ? 'RT02' : 'RT01'; //2 persone giuridiche, 1 persone fisiche

$fattura_scadenza = date("Y-m-d", strtotime($dati['fattura']['scadenze'][0]['documenti_contabilita_scadenze_scadenza']));

$segno = (($dati['fattura']['documenti_contabilita_tipo'] == 4 && false) ? '-' : '');

$_metodi_pagamento = $this->apilib->search('documenti_contabilita_metodi_pagamento');
$metodi_pagamento = array_key_value_map($_metodi_pagamento, 'documenti_contabilita_metodi_pagamento_id', 'documenti_contabilita_metodi_pagamento_codice');

$_iva = $this->apilib->search('iva');
$classi_iva = [];
foreach ($_iva as $i) {
    $classi_iva[$i['iva_id']] = $i;
}



if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    debug($dati, true);
}

?><?php echo '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<p:FatturaElettronica versione="FPR12" xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                      xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2"
                      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                      xsi:schemaLocation="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2 http://www.fatturapa.gov.it/export/fatturazione/sdi/fatturapa/v1.2/Schema_del_file_xml_FatturaPA_versione_1.2.xsd">
    <FatturaElettronicaHeader>
        <DatiTrasmissione>
            <IdTrasmittente>
                <IdPaese><?php echo $country; ?></IdPaese>
                <IdCodice><?php echo $codice_fiscale; ?></IdCodice>
            </IdTrasmittente>

            <ProgressivoInvio><?php echo str_pad($dati['fattura']['documenti_contabilita_progressivo_invio'], 10, '0', STR_PAD_LEFT); ?></ProgressivoInvio>
            <?php /* <!--            valore fisso--> */ ?>
            <FormatoTrasmissione>FPR12</FormatoTrasmissione>
            <?php /* <!--            Da rivedere, non chiaro--> */ ?>
            <CodiceDestinatario><?php echo $codice_destinatario; ?></CodiceDestinatario>
            <?php /* <!--            Il campo sembra essere standard, http://www.fatturapa.gov.it/export/fatturazione/it/c-13.htm--> */ ?>
            <?php if (!empty($pec)) : ?>
                <PECDestinatario><?php echo $pec; ?></PECDestinatario>
            <?php endif; ?>
            <?php /* <!--<ContattiTrasmittente>
                <Telefono></Telefono>
                <Email></Email>
            </ContattiTrasmittente>--> */ ?>

        </DatiTrasmissione>
        <CedentePrestatore>
            <DatiAnagrafici>
                <IdFiscaleIVA>
                    <IdPaese><?php echo $country; ?></IdPaese>
                    <IdCodice><?php echo $vat_num; ?></IdCodice>
                </IdFiscaleIVA>

                <?php /* <!--<CodiceFiscale></CodiceFiscale>--> */ ?>
                <Anagrafica>
                    <Denominazione><?php echo htmlspecialchars($company_name); ?></Denominazione>
                    <?php /* <!--<Nome></Nome>--> */ ?>
                    <?php /* <!--<Cognome></Cognome>--> */ ?>
                    <?php /* <!--<Titolo></Titolo>
                    <CodEORI></CodEORI>--> */ ?>
                </Anagrafica>

                <RegimeFiscale><?php echo $regime_fiscale; ?></RegimeFiscale>
            </DatiAnagrafici>
            <Sede>
                <Indirizzo><?php echo htmlspecialchars($company_address); ?></Indirizzo>
                <CAP><?php echo $company_cap; ?></CAP>
                <Comune><?php echo $company_city; ?></Comune>
                <Provincia><?php echo strtoupper($company_province); ?></Provincia>
                <Nazione><?php echo $country; ?></Nazione>
            </Sede>
            <?php if (!empty($company_numero_rea)): ?>
                <IscrizioneREA>
                    <Ufficio><?php echo strtoupper($company_ufficio_rea); ?></Ufficio>
                    <NumeroREA><?php echo $company_numero_rea; ?></NumeroREA>
                    <CapitaleSociale><?php echo number_format($company_capitale_sociale, 2, '.', ''); ?></CapitaleSociale>
                    <SocioUnico><?php echo $company_socio_unico; ?></SocioUnico>
                    <StatoLiquidazione><?php echo $company_stato_liquidazione; ?></StatoLiquidazione>
                </IscrizioneREA>
            <?php endif; ?>
            <?php /* <!--<RiferimentoAmministrazione><?php echo $company_name; ?></RiferimentoAmministrazione>--> */ ?>
        </CedentePrestatore>

        <CessionarioCommittente>
            <DatiAnagrafici>
                <?php if (!empty($dest_partitaiva)): ?>
                    <IdFiscaleIVA>
                        <IdPaese><?php echo $dest_nazione; ?></IdPaese>
                        <IdCodice><?php echo $dest_partitaiva; ?></IdCodice>
                    </IdFiscaleIVA>
                <?php endif; ?>
                <?php if ($dest_nazione == 'IT') : ?>
                    <CodiceFiscale><?php echo $dest_codicefiscale; ?></CodiceFiscale>
                <?php endif; ?>
                <Anagrafica>
                    <Denominazione><?php echo htmlspecialchars($dest_ragionesociale); ?></Denominazione>
                    <?php /* <!--<Nome></Nome>--> */ ?>
                    <?php /* <!--<Cognome></Cognome>--> */ ?>
                    <?php /* <!--<Titolo></Titolo>
                    <CodEORI></CodEORI>--> */ ?>
                </Anagrafica>
            </DatiAnagrafici>
            <Sede>
                <Indirizzo><?php echo $dest_indirizzo; ?></Indirizzo>
                <?php /* <!--<NumeroCivico></NumeroCivico>--> */ ?>
                <CAP><?php echo $dest_cap; ?></CAP>
                <Comune><?php echo $dest_citta; ?></Comune>
                <Provincia><?php echo strtoupper($dest_provincia); ?></Provincia>
                <Nazione><?php echo $dest_nazione; ?></Nazione>
            </Sede>
        </CessionarioCommittente>

        <?php /* <!--<TerzoIntermediarioOSoggettoEmittente>
            <DatiAnagrafici>
                <IdFiscaleIVA>
                    <IdPaese></IdPaese>
                    <IdCodice></IdCodice>
                </IdFiscaleIVA>
                <CodiceFiscale></CodiceFiscale>
                <Anagrafica>
                    <Denominazione></Denominazione>
                    <Nome></Nome>
                    <Cognome></Cognome>
                    <Titolo></Titolo>
                    <CodEORI></CodEORI>
                </Anagrafica>
            </DatiAnagrafici>
        </TerzoIntermediarioOSoggettoEmittente>--> */ ?>
        <?php /* <!--<SoggettoEmittente></SoggettoEmittente>--> */ ?>

    </FatturaElettronicaHeader>
    <FatturaElettronicaBody>
        <DatiGenerali>
            <DatiGeneraliDocumento>
                <?php /* <!-- TODO: Creazione campo --> */ ?>
                <TipoDocumento><?php echo $fattura_tipo; ?></TipoDocumento>
                <Divisa><?php echo $fattura_valuta; ?></Divisa>
                <Data><?php echo $fattura_dataemissione; ?></Data>
                <Numero><?php echo $fattura_numero; ?></Numero>
                <?php if ($dati['fattura']['documenti_contabilita_sconto_percentuale'] != 0): ?>
                    <ScontoMaggiorazione>
                        <Tipo>SC</Tipo>
                        <Percentuale><?php echo number_format($dati['fattura']['documenti_contabilita_sconto_percentuale'], 2, '.', ''); ?></Percentuale>
                        <Importo><?php echo number_format($dati['fattura']['documenti_contabilita_competenze'] - $dati['fattura']['documenti_contabilita_imponibile_scontato'], 2, '.', ''); ?></Importo>
                    </ScontoMaggiorazione>
                <?php endif; ?>
                <ImportoTotaleDocumento><?php echo number_format($fattura_totale, 2, '.', ''); ?></ImportoTotaleDocumento>

                <?php if ($dati['fattura']['documenti_contabilita_ritenuta_acconto_valore'] != 0): ?>
                    <DatiRitenuta>
                        <TipoRitenuta><?php echo $tipo_ritenuta; ?></TipoRitenuta>
                        <ImportoRitenuta><?php echo number_format($dati['fattura']['documenti_contabilita_ritenuta_acconto_valore'], 2, '.', ''); ?></ImportoRitenuta>
                        <AliquotaRitenuta><?php echo number_format($dati['fattura']['documenti_contabilita_ritenuta_acconto_perc'], 2, '.', ''); ?></AliquotaRitenuta>
                        <CausalePagamento><?php echo $dati['fattura']['documenti_contabilita_causale_pagamento_ritenuta']; ?></CausalePagamento>
                    </DatiRitenuta>
                <?php endif; ?>

                <?php if ($dati['fattura']['documenti_contabilita_importo_bollo'] != 0): ?>
                    <DatiBollo>
                        <BolloVirtuale>SI</BolloVirtuale>
                        <ImportoBollo><?php echo number_format($dati['fattura']['documenti_contabilita_importo_bollo'], 2, '.', ''); ?></ImportoBollo>
                    </DatiBollo>
                <?php endif; ?>

                <?php /* <!--<DatiCassaPrevidenziale>
                    <TipoCassa></TipoCassa>
                    <AlCassa></AlCassa>
                    <ImportoContributoCassa></ImportoContributoCassa>
                    <ImponibileCassa></ImponibileCassa>
                    <AliquotaIVA></AliquotaIVA>
                    <Ritenuta></Ritenuta>
                    <Natura></Natura>
                    <RiferimentoAmministrazione></RiferimentoAmministrazione>
                </DatiCassaPrevidenziale>--> */ ?>

                <?php /* <!--
                <Arrotondamento></Arrotondamento>

                <Causale></Causale>
                <Art73></Art73>--> */ ?>
            </DatiGeneraliDocumento>

            <?php /* <!--            <DatiOrdineAcquisto>
                <RiferimentoNumeroLinea></RiferimentoNumeroLinea>
                <IdDocumento><?php echo $fattura_id; ?></IdDocumento>
                <Data></Data>
                <NumItem><?php echo $fattura_id; ?></NumItem>
                <CodiceCommessaConvenzione></CodiceCommessaConvenzione>
                <CodiceCUP></CodiceCUP>
                <CodiceCIG></CodiceCIG>
            </DatiOrdineAcquisto>
            <DatiContratto>
                <RiferimentoNumeroLinea>
                <IdDocumento></IdDocumento>
                <Data></Data>
                <NumItem></NumItem>
                <CodiceCommessaConvenzione></CodiceCommessaConvenzione>
                <CodiceCUP></CodiceCUP>
                <CodiceCIG></CodiceCIG>
            </DatiContratto>
            <DatiConvenzione>
                <RiferimentoNumeroLinea></RiferimentoNumeroLinea>
                <IdDocumento></IdDocumento>
                <Data></Data>
                <NumItem></NumItem>
                <CodiceCommessaConvenzione></CodiceCommessaConvenzione>
                <CodiceCUP></CodiceCUP>
                <CodiceCIG></CodiceCIG>
            </DatiConvenzione>
            <DatiRicezione>
                <RiferimentoNumeroLinea></RiferimentoNumeroLinea>
                <IdDocumento></IdDocumento>
                <Data></Data>
                <NumItem></NumItem>
                <CodiceCommessaConvenzione></CodiceCommessaConvenzione>
                <CodiceCUP></CodiceCUP>
                <CodiceCIG></CodiceCIG>
            </DatiRicezione>
            <DatiFattureCollegate>
                <RiferimentoNumeroLinea></RiferimentoNumeroLinea>
                <IdDocumento></IdDocumento>
                <Data></Data>
                <NumItem></NumItem>
                <CodiceCommessaConvenzione></CodiceCommessaConvenzione>
                <CodiceCUP></CodiceCUP>
                <CodiceCIG></CodiceCIG>
            </DatiFattureCollegate>
            <DatiSAL>
                <RiferimentoFase></RiferimentoFase>
            </DatiSAL>
            <DatiDDT>
                <NumeroDDT></NumeroDDT>
                <DataDDT></DataDDT>
                <RiferimentoNumeroLinea></RiferimentoNumeroLinea>
            </DatiDDT>
            <DatiTrasporto>
                <DatiAnagraficiVettore>
                    <IdFiscaleIVA>
                        <IdPaese></IdPaese>
                        <IdCodice></IdCodice>
                    </IdFiscaleIVA>
                    <CodiceFiscale></CodiceFiscale>
                    <Anagrafica>
                        <Denominazione></Denominazione>
                        <Nome></Nome>
                        <Cognome></Cognome>
                        <Titolo></Titolo>
                        <CodEORI></CodEORI>
                    </Anagrafica>
                    <NumeroLicenzaGuida></NumeroLicenzaGuida>
                </DatiAnagraficiVettore>
                <MezzoTrasporto></MezzoTrasporto>
                <CausaleTrasporto></CausaleTrasporto>
                <NumeroColli></NumeroColli>
                <Descrizione></Descrizione>
                <UnitaMisuraPeso></UnitaMisuraPeso>
                <PesoLordo></PesoLordo>
                <PesoNetto></PesoNetto>
                <DataOraRitiro></DataOraRitiro>
                <DataInizioTrasporto></DataInizioTrasporto>
                <TipoResa></TipoResa>
                <IndirizzoResa>
                    <Indirizzo></Indirizzo>
                    <NumeroCivico></NumeroCivico>
                    <CAP></CAP>
                    <Comune></Comune>
                    <Provincia></Provincia>
                    <Nazione></Nazione>
                </IndirizzoResa>
                <DataOraConsegna></DataOraConsegna>
            </DatiTrasporto>
            <NormaDiRiferimento></NormaDiRiferimento>
            <FatturaPrincipale>
                <NumeroFatturaPrincipale></NumeroFatturaPrincipale>
                <DataFatturaPrincipale></DataFatturaPrincipale>
            </FatturaPrincipale>--> */ ?>

        </DatiGenerali>
        <DatiBeniServizi>
            <?php foreach ($articoli as $key => $articolo): ?>

                <DettaglioLinee>
                    <NumeroLinea><?php echo $key + 1; ?></NumeroLinea>
                    <?php /* <!--<TipoCessionePrestazione></TipoCessionePrestazione>--> */ ?>
                    <?php /* <!--<CodiceArticolo>
                    <CodiceTipo></CodiceTipo>
                    <CodiceValore></CodiceValore>
                </CodiceArticolo>--> */ ?>

                    <Descrizione><?php echo $articolo['documenti_contabilita_articoli_name'] . ' - ' . $articolo['documenti_contabilita_articoli_descrizione']; ?></Descrizione>
                    <Quantita><?php echo number_format($articolo['documenti_contabilita_articoli_quantita'], 2, '.', ''); ?></Quantita>
                    <UnitaMisura><?php echo ($articolo['documenti_contabilita_articoli_unita_misura']) ?: 'Pz'; ?></UnitaMisura>

                    <?php /* <!--<DataInizioPeriodo></DataInizioPeriodo>
                <DataFinePeriodo></DataFinePeriodo>--> */ ?>
                    <?php
                    $prezzo_senza_seri = number_format($articolo['documenti_contabilita_articoli_prezzo'], 8, '.', '');
                    $prezzo_esploso = explode('.', $prezzo_senza_seri);
                    $parte_decimale = $prezzo_esploso[1];
                    $parte_intera = $prezzo_esploso[0];

                    while (strrpos($parte_decimale, '0') === strlen($parte_decimale) - 1 && strlen($parte_decimale) > 2) {

                        $parte_decimale = rtrim($parte_decimale, '0');
                    }


                    $prezzo_esploso[1] = $parte_decimale;
                    $prezzo_senza_seri = implode('.', $prezzo_esploso);
                    ?>

                    <PrezzoUnitario><?php echo number_format($prezzo_senza_seri,max([2, strlen($parte_decimale)]),'.',''); ?></PrezzoUnitario>
                    <?php if ($articolo['documenti_contabilita_articoli_sconto'] > 0 || ($dati['fattura']['documenti_contabilita_sconto_percentuale'] > 0)) : ?>
                        <ScontoMaggiorazione>

                            <Tipo>SC</Tipo>
                            <Percentuale><?php echo number_format($articolo['documenti_contabilita_articoli_sconto'] + $dati['fattura']['documenti_contabilita_sconto_percentuale'], 2, '.', ''); ?></Percentuale>
                            <!--<Percentuale><?php echo number_format($articolo['documenti_contabilita_articoli_sconto'], 2, '.', ''); ?></Percentuale>-->
                        </ScontoMaggiorazione>
                    <?php endif; ?>
                    <PrezzoTotale><?php echo $segno . number_format(($articolo['documenti_contabilita_articoli_prezzo'] / 100 * (100 - ($articolo['documenti_contabilita_articoli_sconto'] + $dati['fattura']['documenti_contabilita_sconto_percentuale']))) * $articolo['documenti_contabilita_articoli_quantita'], 2, '.', ''); ?></PrezzoTotale>
                    <!--<PrezzoTotale><?php echo $segno . number_format(($articolo['documenti_contabilita_articoli_prezzo'] / 100 * (100 - ($articolo['documenti_contabilita_articoli_sconto']))) * $articolo['documenti_contabilita_articoli_quantita'], 2, '.', ''); ?></PrezzoTotale>-->
                    <AliquotaIVA><?php echo number_format($articolo['iva_valore'], 2, '.', ''); ?></AliquotaIVA>

                    <?php if(!empty($articolo['iva_codice'])) : ?>
                        <Natura><?php echo $articolo['iva_codice']; ?></Natura>
                        <?php /* <RiferimentoAmministrazione><?php echo $articolo['iva_descrizione']; ?></RiferimentoAmministrazione>*/ ?>
                    <?php endif; ?>
                    <?php /* <!--<Ritenuta></Ritenuta>

                <AltriDatiGestionali>
                    <TipoDato></TipoDato>
                    <RiferimentoTesto></RiferimentoTesto>
                    <RiferimentoNumero></RiferimentoNumero>
                    <RiferimentoData></RiferimentoData>
                </AltriDatiGestionali>--> */ ?>
                </DettaglioLinee>
            <?php endforeach; ?>
            
            
            <?php foreach (json_decode($dati['fattura']['documenti_contabilita_iva_json'], true) as $iva_id => $__iva): $aliquota = $__iva[0]; $iva = $__iva[1]; ?>
                <DatiRiepilogo>
                    <AliquotaIVA><?php echo number_format($aliquota, 2, '.', ''); ?></AliquotaIVA>

                    <?php if (!$aliquota): ?>
                        <Natura><?php echo $classi_iva[$iva_id]['iva_codice']; ?></Natura>

                    <?php endif; ?>

                    <?php /* <!--<SpeseAccessorie></SpeseAccessorie>
                <Arrotondamento></Arrotondamento>--> */ ?>

                    <?php
                    //devo fare così per capire quant'è la base imponibile di questa classe iva sulla quale è stata calcolata l'imposta totale)
                    $imponibile = 0;
                    //debug($articoli, true);
                    foreach ($articoli as $articolo) {
                        if ($articolo['documenti_contabilita_articoli_iva_id'] == $iva_id) {
                            if ($articolo['documenti_contabilita_articoli_applica_sconto'] == 't') {
                                $imponibile += (($articolo['documenti_contabilita_articoli_prezzo'] * $articolo['documenti_contabilita_articoli_quantita']) / 100) * (100 - $articolo['documenti_contabilita_articoli_sconto']) / 100 * (100 - $dati['fattura']['documenti_contabilita_sconto_percentuale']);
                            } else {
                                $imponibile += (($articolo['documenti_contabilita_articoli_prezzo'] * $articolo['documenti_contabilita_articoli_quantita']) / 100) * (100 - $dati['fattura']['documenti_contabilita_sconto_percentuale']);
                            }
                        }
                    }
                    ?>

                    <ImponibileImporto><?php echo number_format($imponibile, 2, '.', ''); ?></ImponibileImporto>
                    <Imposta><?php echo number_format($iva, 2, '.', ''); ?></Imposta>
                    <EsigibilitaIVA><?php if ($dati['fattura']['documenti_contabilita_split_payment'] == DB_BOOL_TRUE) : ?>S<?php else: ?>I<?php endif;?></EsigibilitaIVA>
                    <?php if (!empty($classi_iva[$iva_id]['iva_descrizione'])) : ?>
                        <RiferimentoNormativo><?php echo $classi_iva[$iva_id]['iva_descrizione']; ?></RiferimentoNormativo>
                    <?php elseif ($dati['fattura']['documenti_contabilita_split_payment'] == DB_BOOL_TRUE): ?>
                        <RiferimentoNormativo>Emessa ai sensi dell'articolo 17 ter DPR 633/1972 e s.m.i.</RiferimentoNormativo>
                    <?php endif; ?>    
                    
                </DatiRiepilogo>
            <?php endforeach; ?>
        </DatiBeniServizi>

        <?php /* <!--<DatiVeicoli>
            <Data></Data>
            <TotalePercorso></TotalePercorso>
        </DatiVeicoli>--> */ ?>
        <?php foreach ($dati['fattura']['scadenze'] as $key => $scadenza) : ?>
            <?php if($scadenza['documenti_contabilita_scadenze_ammontare'] != '0.00'): ?>
                <DatiPagamento>
                    <CondizioniPagamento><?php if (count($dati['fattura']['scadenze']) > 1 && $key == 0) : //Acconto?>TP03<?php elseif (count($dati['fattura']['scadenze']) > 1) : //A rate?>TP01<?php else: //Soluzione unice?>TP02<?php endif; ?></CondizioniPagamento>
                    <DettaglioPagamento>
                        <?php /* <!--<Beneficiario></Beneficiario>--> */ ?>
                        <ModalitaPagamento><?php echo $metodi_pagamento[$scadenza['documenti_contabilita_scadenze_saldato_con']]; ?></ModalitaPagamento>
                        <?php /* <!--<DataRiferimentoTerminiPagamento></DataRiferimentoTerminiPagamento>
                    <GiorniTerminiPagamento></GiorniTerminiPagamento>--> */ ?>
                        <?php if (count($dati['fattura']['scadenze']) > 1) : //A rate?>

                            <DataScadenzaPagamento><?php echo date('Y-m-d', strtotime($scadenza['documenti_contabilita_scadenze_scadenza'])); ?></DataScadenzaPagamento>
                        <?php endif; ?>
                        <ImportoPagamento><?php echo $segno . number_format($scadenza['documenti_contabilita_scadenze_ammontare'], 2, '.', ''); ?></ImportoPagamento>
                        <?php /* <!--<CodUfficioPostale></CodUfficioPostale>
                    <CognomeQuietanzante></CognomeQuietanzante>
                    <NomeQuietanzante></NomeQuietanzante>
                    <CFQuietanzante></CFQuietanzante>
                    <TitoloQuietanzante></TitoloQuietanzante>
                    <IstitutoFinanziario></IstitutoFinanziario>-->*/ ?>
                        <?php if(!empty($conto_corrente_nome_istituto)): ?><IstitutoFinanziario><?php echo $conto_corrente_nome_istituto; ?></IstitutoFinanziario><?php endif; ?>
                        <?php if(!empty($conto_corrente_iban)): ?><IBAN><?php echo $conto_corrente_iban; ?></IBAN><?php endif; ?>

                        <?php    /*<!--<ABI></ABI>
                    <CAB></CAB>
                    <BIC></BIC>
                    <ScontoPagamentoAnticipato></ScontoPagamentoAnticipato>
                    <DataLimitePagamentoAnticipato></DataLimitePagamentoAnticipato>
                    <PenalitaPagamentiRitardati></PenalitaPagamentiRitardati>
                    <DataDecorrenzaPenale></DataDecorrenzaPenale>
                    --> */ ?>
                        <?php if (1==5 && $dati['fattura']['documenti_contabilita_note_interne']) : ?>
                            <CodicePagamento><?php //echo substr($dati['fattura']['documenti_contabilita_note_interne'], 0, 59); ?></CodicePagamento>
                        <?php endif; ?>
                    </DettaglioPagamento>
                </DatiPagamento>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php /* <!--<Allegati>
            <NomeAttachment></NomeAttachment>
            <AlgoritmoCompressione></AlgoritmoCompressione>
            <FormatoAttachment></FormatoAttachment>
            <DescrizioneAttachment></DescrizioneAttachment>
            <Attachment></Attachment>
        </Allegati>--> */ ?>
    </FatturaElettronicaBody>
</p:FatturaElettronica>