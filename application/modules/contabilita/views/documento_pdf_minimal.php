<?php

//error_reporting(0);  //*********************** <!---------------- DISATTIVARE IN CASO DI DEBUG ------------------->

if (!isset($this->input->get("documento_id")) && !isset($documento_id)) {
    die("Documento non valido o non piu disponibile");
}
$id = (isset($documento_id)) ? $documento_id : $this->input->get("documento_id");

$documento = $this->apilib->view('documenti', $id);
$articoli = $this->apilib->search('documenti_articoli', ['documenti_articoli_documento' => $id], null, 0, "documenti_articoli_iva_perc");

$destinatario = json_decode($documento['documenti_destinatario'], true);

$scadenze = $this->apilib->search('documenti_scadenze', ['documenti_scadenze_documento' => $id]);

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
<html lang="en">
<head>
    <title>Documento</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css"
          href="<?php echo base_url(); ?>template/adminlte/bower_components/bootstrap/dist/css/bootstrap.css?v=1"/>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #5D6975;
            text-decoration: underline;
        }

        body {
            position: relative;
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            color: #001028;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-family: Arial;
        }

        header {
            padding: 10px 0;
            margin-bottom: 30px;
        }

        #logo {
            text-align: center;
            margin-bottom: 10px;
        }

        #logo img {
            width: 90px;
        }

        h1 {
            color: white;
            font-size: 2.4em;
            line-height: 1.4em;
            font-weight: normal;
            text-align: center;
            margin: 0 0 20px 0;
            background-color: #f65d20;
        }

        #project {
            float: left;
        }

        #project span {
            color: #5D6975;
            text-align: right;
            width: 52px;
            margin-right: 10px;
            display: inline-block;
            font-size: 0.8em;
        }

        #company {
            float: right;
            text-align: right;
        }

        #project div,
        #company div {
            white-space: nowrap;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table tr:nth-child(2n-1) td {
            background: #F5F5F5;
        }

        table th,
        table td {
            text-align: center;
        }

        table th {
            padding: 5px 20px;
            color: #5D6975;
            border-bottom: 1px solid #C1CED9;
            white-space: nowrap;
            font-weight: normal;
        }

        table .service,
        table .desc {
            text-align: left;
        }

        table td {
            padding: 20px;
            text-align: right;
        }

        table td.service,
        table td.desc {
            vertical-align: top;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
        }

        table td.grand {
            border-top: 1px solid #5D6975;;
        }

        #notices .notice {
            color: #5D6975;
            font-size: 1.2em;
        }

        footer {
            color: #5D6975;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #C1CED9;
            padding: 8px 0;
            text-align: center;
        }

        .m_logo {
            height: auto;
            width: 200px !important;
        }

        .table th {
            font-weight: bold;
            text-align: right;
        }

        .informativa {
            text-align: left;
        }

        .paginator {
            text-align: right;
        }

        .notices-box {
            min-height: 100px;
            margin-top: 5%;
            border-style: solid;
            border-width: 1px;
            border-color: #ddd;
            padding: 10px;
        }

        .payment-box {
            margin-top: 5%;
            border-style: solid;
            border-width: 1px;
            border-color: #ddd;
            padding: 10px;
        }
    </style>
</head>


<body>
<header class="clearfix">

    <!--        --><?php //debug($documento, true); ?>
    <div id="logo">
        <img src="http://www.h2web.it/images/h2web-logo.png" alt="" class="m_logo"/>
    </div>
    <h1>FATTURA</h1>
    <div id="company" class="clearfix">
        <div><?php echo $settings['settings_company_name']; ?></div>
        <div><?php echo $settings['settings_company_address']; ?><br/> <?php echo $settings['settings_company_city']; ?>
            (<?php echo $settings['settings_company_province']; ?>)
        </div>
        <div>CF <?php echo $settings['settings_company_codice_fiscale']; ?></div>
        <div>P.IVA <?php echo $settings['settings_company_vat_number']; ?></div>
    </div>
    <div id="project">
        <div>
            <span>NUMERO</span> <?php echo $documento['documenti_numero']; ?> <?php echo ($documento['documenti_numero']) ? '/' . $documento['documenti_serie'] : ''; ?>
            del <?php echo date('d-m-Y', strtotime($documento['documenti_data_emissione'])); ?></div>
        <div><span>CLIENTE</span> <?php echo $destinatario['ragione_sociale']; ?></div>
        <div><span>INDIRIZZO</span> <?php echo $destinatario['indirizzo']; ?>
            , <?php echo $destinatario['cap']; ?> <?php echo $destinatario['citta']; ?>
            (<?php echo $destinatario['provincia']; ?>)
        </div>
        <div><span>P. IVA</span> <?php echo $destinatario['partita_iva']; ?></div>
        <div><span>CF</span> <?php echo $destinatario['codice_fiscale']; ?></div>
    </div>
</header>
<main>
    <div class="table-responsive-sm table_articoli">

        <table class="table table-cleared">
            <?php
            $iva_perc_before = "";
            $iva_totale = array();
            $totale_imponibile = 0;
            ?>

            <?php foreach ($articoli as $articolo): ?>

                <?php if ($articolo['documenti_articoli_iva_perc'] != $iva_perc_before or !$iva_perc_before): ?>
                    <?php $iva_totale[$articolo['documenti_articoli_iva_perc']] = 0; ?>
                    <tr class="table_title">
                        <th>Codice</th>
                        <th>Prodotto</th>
                        <th class="right">Prezzo</th>
                        <th class="center">Quantità</th>
                        <th class="right">IVA <?php echo $articolo['documenti_articoli_iva_perc']; ?>%</th>
                        <th class="right">Importo</th>
                    </tr>
                    <?php $iva_perc_before = $articolo['documenti_articoli_iva_perc']; ?>
                <?php endif; ?>

                <?php $iva_totale[$articolo['documenti_articoli_iva_perc']] = $iva_totale[$articolo['documenti_articoli_iva_perc']] + $articolo['documenti_articoli_iva']; ?>
                <tr class="t_rows">
                    <td class="left"><?php echo $articolo['documenti_articoli_codice']; ?></td>
                    <td class="left strong"><?php echo $articolo['documenti_articoli_name']; ?>
                        <br>
                        <small><?php echo $articolo['documenti_articoli_descrizione']; ?></small>
                    </td>
                    <td class="right"><?php echo $valuta[$documento['documenti_valuta']]; ?><?php echo $articolo['documenti_articoli_prezzo']; ?><?php echo ($articolo['documenti_articoli_sconto']) ? "<br /><small>Sconto " . $articolo['documenti_articoli_sconto'] . '% </small>' : ''; ?></td>

                    <td class="center"><?php echo $articolo['documenti_articoli_quantita']; ?></td>
                    <td class="right"><?php echo $valuta[$documento['documenti_valuta']]; ?><?php echo $articolo['documenti_articoli_iva']; ?></td>
                    <td class="right"><?php echo $valuta[$documento['documenti_valuta']]; ?><?php echo $articolo['documenti_articoli_importo_totale']; ?></td>
                </tr>
            <?php endforeach; ?>


            <tr>
                <td></td>
                <td></td>

                <td></td>
                <td></td>
                <td class="right">Imponibile</td>
                <td class="right"><?php echo $valuta[$documento['documenti_valuta']]; ?><?php echo $documento['documenti_totale'] - $documento['documenti_contabilita_iva']; ?></td>
            </tr>

            <?php foreach ($iva_totale as $key => $value): ?>
                <tr>
                    <td></td>
                    <td></td>

                    <td></td>
                    <td></td>
                    <td class="right">IVA <?php echo $key; ?>%</td>
                    <td class="right"><?php echo $valuta[$documento['documenti_valuta']]; ?><?php echo $value; ?></td>
                </tr>
            <?php endforeach; ?>

            <tr class="blu">
                <td></td>
                <td class="left"></td>

                <td class="center"></td>

                <td class="right" colspan="2" style="padding: 10px;"><strong style="margin-bottom: 1px;">Netto a
                        pagare</strong></td>
                <td class="right" style="font-size:1.4em;">
                    <strong><?php echo $valuta[$documento['documenti_valuta']]; ?><?php echo $documento['documenti_totale']; ?></strong>
                </td>
            </tr>
        </table>

        <div class="payment-box">
            <div class="payment_method">
                <strong>MODALITA' DI PAGAMENTO</strong>
            </div>
            <br> <br>
            <div class="row payments">
                <div class="col-md-4 bank">
                    <div class="banktext">
                        <strong><?php echo ucfirst($documento['documenti_metodo_pagamento']); ?></strong><br>

                        <?php if ($documento['documenti_conto_corrente']): ?>
                            <strong>Banca </strong> <?php echo $documento['conti_correnti_nome_istituto']; ?> <br/>
                            <strong>IBAN</strong> <?php echo $documento['conti_correnti_iban']; ?><br/>
                            <strong>SWIFT</strong> <?php echo $documento['conti_correnti_swift']; ?><br/>
                            <strong>Intestazione</strong> <?php echo $documento['conti_correnti_intestatario']; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($documento['documenti_tipo'] != 5) : ?>
                    <div class="col-md-8 payments2">
                        <strong>Scadenze di pagamento</strong><br>
                        <table class="table table-cleared">

                            <?php foreach ($scadenze as $scadenza): ?>
                                <?php $data_saldo = strtotime($scadenza['documenti_scadenze_data_saldo']);
                                $data_scadenza = strtotime($scadenza['documenti_scadenze_scadenza']);
                                $data_saldo = strtotime($scadenza['documenti_scadenze_data_saldo']); ?>
                                <?php if ($scadenza['documenti_scadenze_data_saldo']): ?>

                                    <tr>
                                        <td>
                                            <?php echo $valuta[$documento['documenti_valuta']]; ?> <?php echo $scadenza['documenti_scadenze_ammontare']; ?>
                                            saldati in data <?php echo date('d-m-Y', $data_saldo); ?>
                                            <?php echo ($scadenza['documenti_scadenze_saldato_con']) ? "con " . $scadenza['documenti_scadenze_saldato_con'] : ""; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <td>
                                            <?php echo $valuta[$documento['documenti_valuta']]; ?> <?php echo $scadenza['documenti_scadenze_ammontare']; ?>
                                            da saldare entro il <?php echo date('d-m-Y', $data_scadenza); ?>
                                            <?php echo ($scadenza['documenti_scadenze_saldato_con']) ? "con " . $scadenza['documenti_scadenze_saldato_con'] : ""; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>

                        </table>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <div id="notices" class="notices-box">
            <div>NOTE:</div>
            <div class="notice"><?php echo $documento['documenti_note_interne']; ?></div>
        </div>
</main>
<footer>
    <div class="informativa">
        Ai sensi del D.lgs. 196/2003 Vi informiamo che i Vs. dati saranno utilizzati esclusivamente per i fini
        connessi ai rapporti commerciali tra di noi in essere. Vi preghiamo di controllare i Vs. dati
        anagrafici, la P.IVA e il Cod. Fiscale. Non ci riteniamo responsabili di eventuali errori.
    </div>
    <div class="row">
        <div class="col-md-12 paginator">
            Pagina 1/1
        </div>
    </div>
</footer>
</body>
</html>