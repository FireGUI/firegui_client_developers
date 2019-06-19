<?php

//error_reporting(0);  //*********************** <!---------------- DISATTIVARE IN CASO DI DEBUG ------------------->

if (empty($this->input->get("documento_id")) && empty($documento_id)) {
    die("Documento non valido o non piu disponibile");
}
$id = (isset($documento_id)) ? $documento_id : $this->input->get("documento_id");

$documento = $this->apilib->view('documenti_contabilita', $id);

unset($documento['documenti_contabilita_file']);

//debug($documento,true);

$articoli = $this->apilib->search('documenti_contabilita_articoli', ['documenti_contabilita_articoli_documento' => $id], null, 0, "documenti_contabilita_articoli_iva_perc");

$iva = [];
foreach ($articoli as $articolo) {
    if (!empty($articolo['iva_descrizione'])) {
        $iva[] = ['valore' => $articolo['iva_valore'], 'descrizione' => $articolo['iva_descrizione']];
    }

}


$destinatario = json_decode($documento['documenti_contabilita_destinatario'], true);

$scadenze = $this->apilib->search('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $id]);

$settings = $this->apilib->search('settings')[0];

$valuta = array(
    'AED' => '&#1583;.&#1573;', // ?
    'AFN' => '&#65;&#102;',
    'ALL' => '&#76;&#101;&#107;',
    'AMD' => '',
    'ANG' => '&#402;',
    'AOA' => '&#75;&#122;', // ?
    'ARS' => '&#36;',
    'AUD' => '&#36;',
    'AWG' => '&#402;',
    'AZN' => '&#1084;&#1072;&#1085;',
    'BAM' => '&#75;&#77;',
    'BBD' => '&#36;',
    'BDT' => '&#2547;', // ?
    'BGN' => '&#1083;&#1074;',
    'BHD' => '.&#1583;.&#1576;', // ?
    'BIF' => '&#70;&#66;&#117;', // ?
    'BMD' => '&#36;',
    'BND' => '&#36;',
    'BOB' => '&#36;&#98;',
    'BRL' => '&#82;&#36;',
    'BSD' => '&#36;',
    'BTN' => '&#78;&#117;&#46;', // ?
    'BWP' => '&#80;',
    'BYR' => '&#112;&#46;',
    'BZD' => '&#66;&#90;&#36;',
    'CAD' => '&#36;',
    'CDF' => '&#70;&#67;',
    'CHF' => '&#67;&#72;&#70;',
    'CLF' => '', // ?
    'CLP' => '&#36;',
    'CNY' => '&#165;',
    'COP' => '&#36;',
    'CRC' => '&#8353;',
    'CUP' => '&#8396;',
    'CVE' => '&#36;', // ?
    'CZK' => '&#75;&#269;',
    'DJF' => '&#70;&#100;&#106;', // ?
    'DKK' => '&#107;&#114;',
    'DOP' => '&#82;&#68;&#36;',
    'DZD' => '&#1583;&#1580;', // ?
    'EGP' => '&#163;',
    'ETB' => '&#66;&#114;',
    'EUR' => '&#8364;',
    'FJD' => '&#36;',
    'FKP' => '&#163;',
    'GBP' => '&#163;',
    'GEL' => '&#4314;', // ?
    'GHS' => '&#162;',
    'GIP' => '&#163;',
    'GMD' => '&#68;', // ?
    'GNF' => '&#70;&#71;', // ?
    'GTQ' => '&#81;',
    'GYD' => '&#36;',
    'HKD' => '&#36;',
    'HNL' => '&#76;',
    'HRK' => '&#107;&#110;',
    'HTG' => '&#71;', // ?
    'HUF' => '&#70;&#116;',
    'IDR' => '&#82;&#112;',
    'ILS' => '&#8362;',
    'INR' => '&#8377;',
    'IQD' => '&#1593;.&#1583;', // ?
    'IRR' => '&#65020;',
    'ISK' => '&#107;&#114;',
    'JEP' => '&#163;',
    'JMD' => '&#74;&#36;',
    'JOD' => '&#74;&#68;', // ?
    'JPY' => '&#165;',
    'KES' => '&#75;&#83;&#104;', // ?
    'KGS' => '&#1083;&#1074;',
    'KHR' => '&#6107;',
    'KMF' => '&#67;&#70;', // ?
    'KPW' => '&#8361;',
    'KRW' => '&#8361;',
    'KWD' => '&#1583;.&#1603;', // ?
    'KYD' => '&#36;',
    'KZT' => '&#1083;&#1074;',
    'LAK' => '&#8365;',
    'LBP' => '&#163;',
    'LKR' => '&#8360;',
    'LRD' => '&#36;',
    'LSL' => '&#76;', // ?
    'LTL' => '&#76;&#116;',
    'LVL' => '&#76;&#115;',
    'LYD' => '&#1604;.&#1583;', // ?
    'MAD' => '&#1583;.&#1605;.', //?
    'MDL' => '&#76;',
    'MGA' => '&#65;&#114;', // ?
    'MKD' => '&#1076;&#1077;&#1085;',
    'MMK' => '&#75;',
    'MNT' => '&#8366;',
    'MOP' => '&#77;&#79;&#80;&#36;', // ?
    'MRO' => '&#85;&#77;', // ?
    'MUR' => '&#8360;', // ?
    'MVR' => '.&#1923;', // ?
    'MWK' => '&#77;&#75;',
    'MXN' => '&#36;',
    'MYR' => '&#82;&#77;',
    'MZN' => '&#77;&#84;',
    'NAD' => '&#36;',
    'NGN' => '&#8358;',
    'NIO' => '&#67;&#36;',
    'NOK' => '&#107;&#114;',
    'NPR' => '&#8360;',
    'NZD' => '&#36;',
    'OMR' => '&#65020;',
    'PAB' => '&#66;&#47;&#46;',
    'PEN' => '&#83;&#47;&#46;',
    'PGK' => '&#75;', // ?
    'PHP' => '&#8369;',
    'PKR' => '&#8360;',
    'PLN' => '&#122;&#322;',
    'PYG' => '&#71;&#115;',
    'QAR' => '&#65020;',
    'RON' => '&#108;&#101;&#105;',
    'RSD' => '&#1044;&#1080;&#1085;&#46;',
    'RUB' => '&#1088;&#1091;&#1073;',
    'RWF' => '&#1585;.&#1587;',
    'SAR' => '&#65020;',
    'SBD' => '&#36;',
    'SCR' => '&#8360;',
    'SDG' => '&#163;', // ?
    'SEK' => '&#107;&#114;',
    'SGD' => '&#36;',
    'SHP' => '&#163;',
    'SLL' => '&#76;&#101;', // ?
    'SOS' => '&#83;',
    'SRD' => '&#36;',
    'STD' => '&#68;&#98;', // ?
    'SVC' => '&#36;',
    'SYP' => '&#163;',
    'SZL' => '&#76;', // ?
    'THB' => '&#3647;',
    'TJS' => '&#84;&#74;&#83;', // ? TJS (guess)
    'TMT' => '&#109;',
    'TND' => '&#1583;.&#1578;',
    'TOP' => '&#84;&#36;',
    'TRY' => '&#8356;', // New Turkey Lira (old symbol used)
    'TTD' => '&#36;',
    'TWD' => '&#78;&#84;&#36;',
    'TZS' => '',
    'UAH' => '&#8372;',
    'UGX' => '&#85;&#83;&#104;',
    'USD' => '&#36;',
    'UYU' => '&#36;&#85;',
    'UZS' => '&#1083;&#1074;',
    'VEF' => '&#66;&#115;',
    'VND' => '&#8363;',
    'VUV' => '&#86;&#84;',
    'WST' => '&#87;&#83;&#36;',
    'XAF' => '&#70;&#67;&#70;&#65;',
    'XCD' => '&#36;',
    'XDR' => '',
    'XOF' => '',
    'XPF' => '&#70;',
    'YER' => '&#65020;',
    'ZAR' => '&#82;',
    'ZMK' => '&#90;&#75;', // ?
    'ZWL' => '&#90;&#36;',
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Documento</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css"
          href="<?php echo base_url(); ?>template/adminlte/bower_components/bootstrap/dist/css/bootstrap.css?v=1"/>
    <style>


        .intestazione_azienda {
            text-align: center;
        }

        .intestazione_azienda p {
            line-height: 5px;
            font-size: small;
        }

        .intestazione_azienda hr {
            width: 50%;
            margin: 5px auto 5px auto;
        }

        .denominazioni_sx_title hr {
            margin: 5px auto 5px auto;
        }

        .margin_denominazioni {
            margin-top: 50px;

        }

        .denominazioni_sx_title {
            font-weight: bold;
            font-size: larger;
        }

        .denominazioni_sx_subtitle {
            font-weight: bold;
        }

        .inversione_contabile {
            margin-top: 30px;
            font-style: italic;
        }

        .inversione_contabile {
            margin-top: 50px;
            font-style: italic;
        }

        .tabella_importi hr {
            margin: 5px auto 10px auto;
        }

        .tot-price {
            text-align: right;
            padding-right: 30px;
            font-size: larger;
            font-weight: bold;
        }

        .tot-impo {
            font-size: larger;
            font-weight: 400;
        }

        .iban-row {
            margin-top: 20px;
            font-size: larger;
            font-weight: 400;
        }

        .credito-col {
            font-size: larger;
            font-weight: bold;
        }

        .pagamento-bottom {
            font-weight: bold;
        }

        #bottom-stuff {
            margin-top: 50px;
        }

        .subtitle-bg {
            width: 70%;
            text-align: center;
            color: white;
            background-color: #0b94ea;
            border-radius: 5px;
        }

        .commessa-bg {
            width: 97%;
            text-align: center;
            color: white;
            background-color: #0b94ea;
            border-radius: 5px;
        }

        .right {
            text-align: right;
        }

        .invoice-recap{
            padding-right: 63px;
        }
    </style>
</head>
<body>
​
<div class="row">
    <div class="col-md-4">
        <h1>Fattura n° <?php echo $documento['documenti_contabilita_numero']; ?></h1>
        <h3>del <?php echo date('d-m-Y', strtotime($documento['documenti_contabilita_data_emissione'])); ?></h3>
        <h4>pag. 1/2</h4>

    </div>
    <div class="col-md-7 intestazione_azienda">
        <h1>Varini S.r.l</h1>
        <p>Impianti termici civili ed industriali</p>
        <p>Vapore - Aria - Condizionamento</p>
        <hr>
        <p>Via Castello Motti, 1 - Gavassa - 42122 - (RE)</p>
        <p>Tel. 335/6561382 - e-mail: varinisrl@libero.it</p>
        <p>PEC: varinisrl@cert.cna.it</p>
        <hr>
        <p>C.F e P.Iva 02137540353 R.E.A di RE n° 254848</p>
        <p>- Cap.Soc. 40.000I.V.</p>
    </div>
</div>
​
<div class="row invoice-main-content">
    <div class="col-md-4">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 denominazioni_sx_title">
                        <div class="subtitle-bg">
                            CLIENTE
                        </div>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 denominazioni_sx_subtitle">
                        Nome
                    </div>
                    <div class="col-md-8">
                        <?php echo $destinatario['ragione_sociale']; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 denominazioni_sx_subtitle">
                        Strada
                    </div>
                    <div class="col-md-8">
                        <?php echo $destinatario['indirizzo']; ?>
                        <br/><?php echo $destinatario['cap']; ?> <?php echo $destinatario['citta']; ?>
                        (<?php echo $destinatario['provincia']; ?>)
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 denominazioni_sx_subtitle">
                        Cod.Fis.
                    </div>
                    <div class="col-md-8">
                        <?php echo $destinatario['codice_fiscale']; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 denominazioni_sx_subtitle">
                        Part. IVA
                    </div>
                    <div class="col-md-8">
                        <?php echo $destinatario['partita_iva']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 ">
                <div class="row">
                    <div class="col-md-12 denominazioni_sx_title margin_denominazioni">
                        <div class="subtitle-bg">
                            DESTINATARIO
                        </div>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 denominazioni_sx_subtitle">
                        presso
                    </div>
                    <div class="col-md-8">
                        <?php echo $documento['documenti_contabilita_luogo_destinazione']; ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 denominazioni_sx_subtitle">
                        a cura di
                    </div>
                    <div class="col-md-8">
                        <?php echo $documento['documenti_contabilita_trasporto_a_cura_di']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-10 inversione_contabile">
                <?php if (!empty($iva) || $documento['documenti_contabilita_split_payment'] == DB_BOOL_TRUE) : ?>
                    <table>
                        <tr class="">
                            <td colspan="6">Esenzioni iva:</td>
                        </tr>
                        <?php foreach ($iva as $i) : ?>
                            <tr class="">
                                <td colspan="6"><?php echo $i['valore']; ?>% - <?php echo $i['descrizione']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($documento['documenti_contabilita_split_payment'] == DB_BOOL_TRUE): ?>
                            <tr>
                                <td colspan="6"><strong>Questa fattura applica lo "Split Payment", pertanto l'IVA dovrà
                                        essere versata dal cliente ai sensi dell'art. 17-ter, DPR n. 633/72.</strong>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-8 tabella_importi">
        <div class="row">
            <div class="col-md-12 denominazioni_sx_title commessa-bg">
                Descrizione Commessa
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                Sostituzione misuratori d'energia nell'ambito d'appalto 'Lavori di posa quadri elettrici telecontrollo
                sst' per IREN ENERGIA SPA - CIG.: 66279369CE
            </div>
        </div>
        <div class="row">
            <table class="col-md-12 table table-cleared">
                <tr class="table_title">
                    <th>U/M</th>
                    <th>Q</th>
                    <th>Tipo<br/>interv</th>
                    <th>SST</th>
                    <th>DESCRIZIONE</th>
                    <th>Prezzo<br/>unitario</th>
                    <th>Importo</th>
                </tr>
                <?php foreach ($articoli as $articolo): ?>
<!--                                        --><?php //debug($documento); ?>
                    <tr class="t_rows">
                        <td>n</td>
                        <td><?php echo $articolo['documenti_contabilita_articoli_quantita']; ?></td>
                        <td>4.1</td>
                        <td><?php echo $articolo['documenti_contabilita_articoli_codice']; ?></td>
                        <td><?php echo $articolo['documenti_contabilita_articoli_name']; ?></td>
                        <td><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $articolo['documenti_contabilita_articoli_prezzo']; ?></td>
                        <td><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $articolo['documenti_contabilita_articoli_importo_totale']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="row invoice-recap">
            <table class="col-md-12">
                <tr>
                    <td></td>
                    <td></td>

                    <td></td>

                    <td colspan="2" class="right">Competenze</td>
                    <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_competenze']; ?></td>
                </tr>
                <?php if ($documento['documenti_contabilita_rivalsa_inps_valore']) : ?>
                    <tr>
                        <td></td>
                        <td></td>

                        <td></td>

                        <td colspan="2" class="right">Rivalsa
                            INPS <?php echo $documento['documenti_contabilita_rivalsa_inps_perc']; ?>%
                        </td>
                        <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_rivalsa_inps_valore']; ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($documento['documenti_contabilita_competenze_lordo_rivalsa']) : ?>
                    <tr>
                        <td></td>
                        <td></td>

                        <td></td>

                        <td colspan="2" class="right">Competenze (al lordo della rivalsa)</td>
                        <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_competenze_lordo_rivalsa']; ?></td>
                    </tr>
                <?php endif; ?>

                <?php if ($documento['documenti_contabilita_cassa_professionisti_valore']) : ?>
                    <tr>
                        <td></td>
                        <td></td>

                        <td></td>

                        <td colspan="2" class="right">Cassa
                            professionisti <?php echo $documento['documenti_contabilita_cassa_professionisti_perc']; ?></td>
                        <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_cassa_professionisti_valore']; ?></td>
                    </tr>
                <?php endif; ?>


                <tr>
                    <td></td>
                    <td></td>

                    <td></td>
                    <td colspan="2" class="right">Imponibile</td>
                    <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_imponibile']; ?></td>
                </tr>


                <?php if ($documento['documenti_contabilita_ritenuta_acconto_valore']) : ?>
                    <tr>
                        <td></td>
                        <td></td>

                        <td></td>
                        <td colspan="2" class="right">Ritenuta d'acconto
                            -<?php echo $documento['documenti_contabilita_ritenuta_acconto_perc']; ?>
                            di <?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?> <?php echo $documento['documenti_contabilita_ritenuta_acconto_perc_imponibile']; ?></td>
                        <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_ritenuta_acconto_valore']; ?></td>
                    </tr>
                <?php endif; ?>

                <?php //foreach ($iva_totale as $key => $value): ?>
                <?php foreach (json_decode($documento['documenti_contabilita_iva_json']) as $percentuale => $importo): ?>
                    <?php //debug($key); ?>
                    <?php //debug($value); ?>
                    <tr>
                        <td></td>
                        <td></td>

                        <td></td>
                        <td></td>
                        <td class="right">IVA <?php echo $percentuale; ?>%</td>
                        <td class="right"><?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $importo; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
<div id="bottom-stuff">
    <div class="row">
        <div class="col-md-8 pagamento-bottom">
            Pagamento:
        </div>
    </div>
    <div class="row iban-row">
        <div class="col-md-12">
            COD. IBAN: <?php echo $documento['conti_correnti_iban']; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 credito-col">
            <?php echo $documento['conti_correnti_nome_istituto']; ?>
        </div>
        <div class="col-md-2 tot-impo">
            TOTALE
        </div>
        <div class="col-md-2 tot-price">
            <?php echo $valuta[$documento['documenti_contabilita_valuta']]; ?><?php echo $documento['documenti_contabilita_totale']; ?>
        </div>
    </div>
</div>
</body>
</html>


