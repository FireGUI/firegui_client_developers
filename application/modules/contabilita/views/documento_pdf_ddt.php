<?php

//error_reporting(0);  //*********************** <!---------------- DISATTIVARE IN CASO DI DEBUG ------------------->

if (empty($this->input->get("documento_id")) && empty($documento_id)) {
    die("Documento non valido o non piu disponibile");
}
$id = (isset($documento_id)) ? $documento_id : $this->input->get("documento_id");

$documento = $this->apilib->view('documenti_contabilita', $id);

$segno = '';
if ($documento['documenti_contabilita_tipo'] == 4) {//Se è una nota di credito
    $segno = '-';
}

unset($documento['documenti_contabilita_file']);

$articoli = $this->apilib->search('documenti_contabilita_articoli', ['documenti_contabilita_articoli_documento' => $id], null, 0, "documenti_contabilita_articoli_iva_perc");

$destinatario = json_decode($documento['documenti_contabilita_destinatario'], true);

$scadenze = $this->apilib->search('documenti_contabilita_scadenze', ['documenti_contabilita_scadenze_documento' => $id]);

$settings = $this->apilib->searchFirst('documenti_contabilita_settings');

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

if(!empty($this->input->get('debug')) && $this->input->get('debug') == 1){
    debug($documento, true);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<HTML>
<HEAD>
    <META http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <META http-equiv="X-UA-Compatible" content="IE=8">
    <TITLE>bcl_1625778273.htm</TITLE>
    <META name="generator" content="BCL easyConverter SDK 5.0.140">
    <STYLE type="text/css">

        body {margin-top: 0px;margin-left: 0px;}

        #page_1 {position:relative; overflow: hidden;margin: 32px 0px 26px 38px;padding: 0px;border: none;width: 756px;height: 1065px;}

        #page_1 #p1dimg1 {position:absolute;top:0px;left:0px;z-index:-1;width:724px;height:1065px;}
        #page_1 #p1dimg1 #p1img1 {width:724px;height:1065px;}




        .dclr {clear:both;float:none;height:1px;margin:0px;padding:0px;overflow:hidden;}

        .ft0{font: 9px 'Arial';line-height: 12px;}
        .ft1{font: bold 11px 'Arial';line-height: 14px;}
        .ft2{font: 8px 'Arial';line-height: 10px;}
        .ft3{font: 1px 'Arial';line-height: 13px;}
        .ft4{font: 1px 'Arial';line-height: 12px;}
        .ft5{font: 1px 'Arial';line-height: 1px;}
        .ft6{font: bold 12px 'Arial';line-height: 15px;}
        .ft7{font: 12px 'Arial';line-height: 15px;}
        .ft8{font: 1px 'Arial';line-height: 5px;}
        .ft9{font: 1px 'Arial';line-height: 4px;}
        .ft10{font: 11px 'Arial';line-height: 14px;}
        .ft11{font: 12px 'Arial';line-height: 13px;}
        .ft12{font: 1px 'Arial';line-height: 14px;}
        .ft13{font: 12px 'Arial';line-height: 14px;}
        .ft14{font: italic 12px 'Arial';line-height: 15px;}
        .ft15{font: 1px 'Arial';line-height: 10px;}
        .ft16{font: 9px 'Arial';line-height: 11px;}
        .ft17{font: 1px 'Arial';line-height: 11px;}
        .ft18{font: 1px 'Arial';line-height: 3px;}
        .ft19{font: 7px 'Arial';line-height: 7px;}
        .ft20{font: bold 9px 'Arial';line-height: 11px;}

        .p0{text-align: left;padding-left: 370px;margin-top: 4px;margin-bottom: 0px;}
        .p1{text-align: left;padding-left: 370px;margin-top: 0px;margin-bottom: 0px;}
        .p2{text-align: left;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p3{text-align: left;padding-left: 1px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p4{text-align: left;padding-left: 2px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p5{text-align: left;padding-left: 14px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p6{text-align: left;padding-left: 7px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p7{text-align: right;padding-right: 20px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p8{text-align: right;padding-right: 144px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p9{text-align: left;padding-left: 6px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p10{text-align: left;padding-left: 70px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p11{text-align: left;padding-left: 46px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p12{text-align: left;padding-left: 5px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p13{text-align: left;padding-left: 3px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p14{text-align: right;padding-right: 71px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p15{text-align: left;padding-left: 22px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p16{text-align: right;padding-right: 37px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p17{text-align: right;padding-right: 11px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p18{text-align: left;padding-left: 43px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p19{text-align: right;padding-right: 61px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p20{text-align: left;padding-left: 27px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p21{text-align: left;padding-left: 36px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p22{text-align: right;padding-right: 17px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p23{text-align: left;padding-left: 8px;margin-top: 454px;margin-bottom: 0px;}
        .p24{text-align: left;padding-left: 8px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p25{text-align: left;padding-left: 73px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p26{text-align: left;padding-left: 13px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p27{text-align: left;padding-left: 4px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p28{text-align: left;padding-left: 9px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p29{text-align: left;padding-left: 117px;margin-top: 0px;margin-bottom: 0px;white-space: nowrap;}
        .p30{text-align: left;padding-left: 5px;margin-top: 36px;margin-bottom: 0px;}
        .p31{text-align: justify;padding-left: 5px;padding-right: 42px;margin-top: 2px;margin-bottom: 0px;}

        .td0{padding: 0px;margin: 0px;width: 82px;vertical-align: bottom;}
        .td1{padding: 0px;margin: 0px;width: 73px;vertical-align: bottom;}
        .td2{padding: 0px;margin: 0px;width: 27px;vertical-align: bottom;}
        .td3{padding: 0px;margin: 0px;width: 131px;vertical-align: bottom;}
        .td4{padding: 0px;margin: 0px;width: 55px;vertical-align: bottom;}
        .td5{border-right: #000000 1px solid;border-top: #000000 1px solid;padding: 0px;margin: 0px;width: 77px;vertical-align: bottom;}
        .td6{border-top: #000000 1px solid;padding: 0px;margin: 0px;width: 9px;vertical-align: bottom;}
        .td7{border-right: #000000 1px solid;border-top: #000000 1px solid;padding: 0px;margin: 0px;width: 89px;vertical-align: bottom;}
        .td8{border-top: #000000 1px solid;padding: 0px;margin: 0px;width: 172px;vertical-align: bottom;}
        .td9{border-top: #000000 1px solid;padding: 0px;margin: 0px;width: 2px;vertical-align: bottom;}
        .td10{border-right: #000000 1px solid;padding: 0px;margin: 0px;width: 77px;vertical-align: bottom;}
        .td11{padding: 0px;margin: 0px;width: 9px;vertical-align: bottom;}
        .td12{border-right: #000000 1px solid;padding: 0px;margin: 0px;width: 89px;vertical-align: bottom;}
        .td13{padding: 0px;margin: 0px;width: 172px;vertical-align: bottom;}
        .td14{padding: 0px;margin: 0px;width: 2px;vertical-align: bottom;}
        .td15{border-right: #000000 1px solid;border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 77px;vertical-align: bottom;}
        .td16{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 9px;vertical-align: bottom;}
        .td17{border-right: #000000 1px solid;border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 89px;vertical-align: bottom;}
        .td18{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 172px;vertical-align: bottom;}
        .td19{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 2px;vertical-align: bottom;}
        .td20{padding: 0px;margin: 0px;width: 87px;vertical-align: bottom;}
        .td21{padding: 0px;margin: 0px;width: 90px;vertical-align: bottom;}
        .td22{padding: 0px;margin: 0px;width: 177px;vertical-align: bottom;}
        .td23{padding: 0px;margin: 0px;width: 349px;vertical-align: bottom;}
        .td24{padding: 0px;margin: 0px;width: 186px;vertical-align: bottom;}
        .td25{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 82px;vertical-align: bottom;}
        .td26{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 73px;vertical-align: bottom;}
        .td27{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 27px;vertical-align: bottom;}
        .td28{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 131px;vertical-align: bottom;}
        .td29{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 55px;vertical-align: bottom;}
        .td30{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 78px;vertical-align: bottom;}
        .td31{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 90px;vertical-align: bottom;}
        .td32{padding: 0px;margin: 0px;width: 78px;vertical-align: bottom;}
        .td33{padding: 0px;margin: 0px;width: 262px;vertical-align: bottom;}
        .td34{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 100px;vertical-align: bottom;}
        .td35{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 177px;vertical-align: bottom;}
        .td36{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 186px;vertical-align: bottom;}
        .td37{padding: 0px;margin: 0px;width: 260px;vertical-align: bottom;}
        .td38{padding: 0px;margin: 0px;width: 40px;vertical-align: bottom;}
        .td39{padding: 0px;margin: 0px;width: 164px;vertical-align: bottom;}
        .td40{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 260px;vertical-align: bottom;}
        .td41{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 40px;vertical-align: bottom;}
        .td42{border-bottom: #000000 1px solid;padding: 0px;margin: 0px;width: 164px;vertical-align: bottom;}

        .tr0{height: 13px;}
        .tr1{height: 12px;}
        .tr2{height: 20px;}
        .tr3{height: 5px;}
        .tr4{height: 4px;}
        .tr5{height: 49px;}
        .tr6{height: 14px;}
        .tr7{height: 41px;}
        .tr8{height: 51px;}
        .tr9{height: 10px;}
        .tr10{height: 15px;}
        .tr11{height: 16px;}
        .tr12{height: 11px;}
        .tr13{height: 18px;}
        .tr14{height: 3px;}
        .tr15{height: 19px;}

        .t0{width: 719px;margin-left: 1px;margin-top: 4px;font: 12px 'Arial';}
        .t1{width: 724px;margin-top: 23px;font: 9px 'Arial';}

    </STYLE>
</HEAD>

<BODY>
<DIV id="page_1">
    <DIV id="p1dimg1">
        <IMG src="data:image/jpg;base64,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RQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFRyRs7wss0kYR9zKoXEg2kbWyCcZIPGDlRzjIMlFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUVz//AAnfg/8A6GvQ/wDwYw//ABVH/Cd+D/8Aoa9D/wDBjD/8VQB0FFc//wAJ34P/AOhr0P8A8GMP/wAVR/wnfg//AKGvQ/8AwYw//FUAdBRXP/8ACd+D/wDoa9D/APBjD/8AFUf8J34P/wChr0P/AMGMP/xVAHQUVz//AAnfg/8A6GvQ/wDwYw//ABVH/Cd+D/8Aoa9D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qf8AQ565/wB+bL/5Ho/4R7VP+hz1z/vzZf8AyPXQUUAc/wD8I9qn/Q565/35sv8A5Ho/4R7VP+hz1z/vzZf/ACPXQUUAc/8A8I9qn/Q565/35sv/AJHo/wCEe1T/AKHPXP8AvzZf/I9dBRQBz/8Awj2qf9Dnrn/fmy/+R6K6CigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWj7Z4w/wCgFof/AIOZv/kWugooA5/7Z4w/6AWh/wDg5m/+RaPtnjD/AKAWh/8Ag5m/+Ra6CigDn/tnjD/oBaH/AODmb/5Fo+2eMP8AoBaH/wCDmb/5FroKKAOf+2eMP+gFof8A4OZv/kWiugooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA+/wCivgCigD7/AKK+AKKAPv8Aor4AooA//9k=" id="p1img1"></DIV>


    <DIV class="dclr"></DIV>
    <P class="p0 ft0">Tipo documento</P>
    <P class="p0 ft1">DOCUMENTO DI TRASPORTO (D.d.t.)</P>
    <P class="p1 ft2">D.P.R. 472 del 14.08.1996 - D.P.R. 696 del 21.12.1996</P>
    <TABLE cellpadding=0 cellspacing=0 class="t0">
        <TR>
            <TD class="tr0 td0"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td1"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td2"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td3"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td4"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr1 td5"><P class="p3 ft0">numero</P></TD>
            <TD class="tr1 td6"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td7"><P class="p4 ft0">data</P></TD>
            <TD class="tr1 td8"><P class="p5 ft0">pagina</P></TD>
            <TD class="tr1 td9"><P class="p2 ft4">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr2 td0"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td1"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td2"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td3"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td4"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td10"><P class="p6 ft6">0000458</P></TD>
            <TD class="tr2 td11"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td12"><P class="p7 ft6">07/11/2018</P></TD>
            <TD class="tr2 td13"><P class="p8 ft7">1</P></TD>
            <TD class="tr2 td14"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr3 td0"><P class="p2 ft8">&nbsp;</P></TD>
            <TD class="tr3 td1"><P class="p2 ft8">&nbsp;</P></TD>
            <TD class="tr3 td2"><P class="p2 ft8">&nbsp;</P></TD>
            <TD class="tr3 td3"><P class="p2 ft8">&nbsp;</P></TD>
            <TD class="tr3 td4"><P class="p2 ft8">&nbsp;</P></TD>
            <TD class="tr4 td15"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td16"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td17"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td18"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td19"><P class="p2 ft9">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr5 td0"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr5 td1"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr5 td2"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr5 td3"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr5 td4"><P class="p9 ft10">Spett.le</P></TD>
            <TD colspan=2 class="tr5 td20"><P class="p3 ft7">S.I.T.T.A. SRL</P></TD>
            <TD class="tr5 td21"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr5 td13"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr5 td14"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr0 td0"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td1"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td2"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td3"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td4"><P class="p2 ft3">&nbsp;</P></TD>
            <TD colspan=3 class="tr0 td22"><P class="p3 ft11">VIA CASCINA RINALDI, 37</P></TD>
            <TD class="tr0 td13"><P class="p2 ft3">&nbsp;</P></TD>
            <TD class="tr0 td14"><P class="p2 ft3">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr6 td0"><P class="p2 ft12">&nbsp;</P></TD>
            <TD class="tr6 td1"><P class="p2 ft12">&nbsp;</P></TD>
            <TD class="tr6 td2"><P class="p2 ft12">&nbsp;</P></TD>
            <TD class="tr6 td3"><P class="p2 ft12">&nbsp;</P></TD>
            <TD class="tr6 td4"><P class="p2 ft12">&nbsp;</P></TD>
            <TD colspan=4 class="tr6 td23"><P class="p3 ft13">33048 SAN GIOVANNI AL NATISONE (UD)</P></TD>
            <TD class="tr6 td14"><P class="p2 ft12">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr7 td0"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td1"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td2"><P class="p2 ft5">&nbsp;</P></TD>
            <TD colspan=2 class="tr7 td24"><P class="p10 ft10">Luogo di destinazione</P></TD>
            <TD colspan=3 rowspan=2 class="tr8 td22"><P class="p11 ft14">STESSO</P></TD>
            <TD class="tr7 td13"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td14"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr9 td0"><P class="p2 ft15">&nbsp;</P></TD>
            <TD class="tr9 td1"><P class="p2 ft15">&nbsp;</P></TD>
            <TD class="tr9 td2"><P class="p2 ft15">&nbsp;</P></TD>
            <TD class="tr9 td3"><P class="p2 ft15">&nbsp;</P></TD>
            <TD class="tr9 td4"><P class="p2 ft15">&nbsp;</P></TD>
            <TD class="tr9 td13"><P class="p2 ft15">&nbsp;</P></TD>
            <TD class="tr9 td14"><P class="p2 ft15">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr10 td25"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td26"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td27"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td28"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td29"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td30"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td16"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td31"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td18"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr11 td14"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr12 td0"><P class="p12 ft16">cod. cliente</P></TD>
            <TD class="tr12 td1"><P class="p13 ft16">p. iva</P></TD>
            <TD class="tr12 td2"><P class="p2 ft17">&nbsp;</P></TD>
            <TD class="tr12 td3"><P class="p14 ft16">codice fiscale</P></TD>
            <TD class="tr12 td4"><P class="p2 ft2">rif. Vs. ordine</P></TD>
            <TD class="tr12 td32"><P class="p2 ft17">&nbsp;</P></TD>
            <TD class="tr12 td11"><P class="p2 ft17">&nbsp;</P></TD>
            <TD colspan=2 class="tr12 td33"><P class="p15 ft16">nostro rappresentante</P></TD>
            <TD class="tr12 td14"><P class="p2 ft17">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr13 td25"><P class="p16 ft7">000032</P></TD>
            <TD colspan=2 class="tr13 td34"><P class="p4 ft7">00415830306</P></TD>
            <TD class="tr13 td28"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td29"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td30"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td16"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td31"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td18"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td19"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr1 td0"><P class="p12 ft0">trasporto a cura</P></TD>
            <TD class="tr1 td1"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td2"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td3"><P class="p17 ft0">inizio trasporto o consegna</P></TD>
            <TD class="tr1 td4"><P class="p2 ft4">&nbsp;</P></TD>
            <TD colspan=3 class="tr1 td22"><P class="p18 ft0">causale del trasporto</P></TD>
            <TD class="tr1 td13"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td14"><P class="p2 ft4">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr13 td25"><P class="p12 ft7">VETTORE</P></TD>
            <TD class="tr13 td26"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td27"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td28"><P class="p19 ft7">07/11/2018</P></TD>
            <TD class="tr13 td29"><P class="p2 ft5">&nbsp;</P></TD>
            <TD colspan=3 class="tr13 td35"><P class="p18 ft7">Conto Vendita</P></TD>
            <TD class="tr13 td18"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr13 td19"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr1 td0"><P class="p12 ft0">documenti allegati</P></TD>
            <TD class="tr1 td1"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td2"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td3"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td4"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td32"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td11"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td21"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td13"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td14"><P class="p2 ft4">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr2 td25"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td26"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td27"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td28"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td29"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td30"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td16"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td31"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td18"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr2 td19"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr1 td0"><P class="p20 ft2">cod. art.</P></TD>
            <TD class="tr1 td1"><P class="p21 ft2">quantità</P></TD>
            <TD class="tr1 td2"><P class="p3 ft2">u.m.</P></TD>
            <TD class="tr1 td3"><P class="p19 ft2">descrizione articolo</P></TD>
            <TD class="tr1 td4"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td32"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td11"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td21"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td13"><P class="p2 ft4">&nbsp;</P></TD>
            <TD class="tr1 td14"><P class="p2 ft4">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr14 td25"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td26"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td27"><P class="p2 ft18">&nbsp;</P></TD>
            <TD colspan=2 class="tr14 td36"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td30"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td16"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td31"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td18"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td19"><P class="p2 ft18">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr7 td0"><P class="p3 ft7">S01</P></TD>
            <TD class="tr7 td1"><P class="p22 ft7">7.710</P></TD>
            <TD class="tr7 td2"><P class="p2 ft7">KG</P></TD>
            <TD colspan=2 class="tr7 td24"><P class="p2 ft7">SEGATURA E TRUCCIOLI</P></TD>
            <TD class="tr7 td32"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td11"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td21"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td13"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr7 td14"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
    </TABLE>
    <P class="p23 ft0">convenzioni particolari</P>
    <TABLE cellpadding=0 cellspacing=0 class="t1">
        <TR>
            <TD class="tr1 td37"><P class="p24 ft0">aspetto esteriore di beni</P></TD>
            <TD class="tr1 td38"><P class="p2 ft0">n. colli</P></TD>
            <TD class="tr1 td37"><P class="p25 ft0">porto</P></TD>
            <TD class="tr1 td39"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr6 td37"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr6 td38"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr6 td37"><P class="p25 ft13">FRANCO NS.SEDE</P></TD>
            <TD class="tr6 td39"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr4 td40"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td41"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td40"><P class="p2 ft9">&nbsp;</P></TD>
            <TD class="tr4 td42"><P class="p2 ft9">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr1 td37"><P class="p24 ft0">vettori: residenza e domicilio</P></TD>
            <TD class="tr1 td38"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr1 td37"><P class="p26 ft0">data ritiro merce</P></TD>
            <TD class="tr1 td39"><P class="p27 ft0">firma del vettore</P></TD>
        </TR>
        <TR>
            <TD class="tr15 td37"><P class="p24 ft7">AUTOTRS. DRIUTTI S.A.S.</P></TD>
            <TD class="tr15 td38"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr15 td37"><P class="p26 ft7">07/11/2018</P></TD>
            <TD class="tr15 td39"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr10 td37"><P class="p28 ft7">via C. PERCOTO, 7</P></TD>
            <TD class="tr10 td38"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td37"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr10 td39"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr11 td37"><P class="p28 ft7">33044 S.LORENZO DI MANZANO (UD)</P></TD>
            <TD class="tr11 td38"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr11 td37"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr11 td39"><P class="p2 ft5">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr14 td40"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td41"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td40"><P class="p2 ft18">&nbsp;</P></TD>
            <TD class="tr14 td42"><P class="p2 ft18">&nbsp;</P></TD>
        </TR>
        <TR>
            <TD class="tr0 td37"><P class="p6 ft19">annotazioni o variazioni</P></TD>
            <TD class="tr0 td38"><P class="p2 ft5">&nbsp;</P></TD>
            <TD class="tr0 td37"><P class="p29 ft0">firma del conducente</P></TD>
            <TD class="tr0 td39"><P class="p2 ft0">firma del destinatario</P></TD>
        </TR>
    </TABLE>
    <P class="p30 ft19"><SPAN class="ft20">CONTROLLATE: </SPAN>I VOSTRI DATI ANAGRAFICI E FISCALI. NULLA RICEVENDO CI ESONERIAMO DA OGNI RESPONSABILITA' AGLI EFFETTI DELL'ART. 37 COMMA <NOBR>8-9</NOBR> D.L. 233 DEL 04.07.2008.</P>
    <P class="p31 ft0">Non si accettano reclami trascorsi 8 gg. dal ricevimento della merce. La merce viaggia a rischio e pericolo del committente anche se venduta franco destino. In caso di ritardo nel pagamento decorreranno gli interessi commerciali. Per ogni controversia è competente il foro di Udine. La merce rimane di nostra proprieta' fino al totale pagamento della fattura.</P>
</DIV>
</BODY>
</HTML>