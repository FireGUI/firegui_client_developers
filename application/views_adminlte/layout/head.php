<?php

/**
 * File CSS tema
 */
$tprefix = empty($dati['title_prefix']) ? '' : $dati['title_prefix'];
$title = (empty($this->settings['settings_company_short_name']) ? 'MasterCRM' : $this->settings['settings_company_short_name']);
$tsuffix = 'CRM';
?>

<title><?php echo ucwords(trim(implode(' - ', array_filter([$tprefix, $title, $tsuffix])))); ?></title>

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description" />
<meta name="application-name" content="<?php echo $title; ?>" />
<meta name="msapplication-TileColor" content="#FFFFFF" />

<!-- CORE LEVEL STYLES -->
<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/@fortawesome/fontawesome-free/css/all.min.css?v={$this->config->item('version')}"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/Ionicons/css/ionicons.min.css?v={$this->config->item('version')}"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/jvectormap/jquery-jvectormap.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css?v={$this->config->item('version')}"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css?v={$this->config->item('version')}"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap-daterangepicker/daterangepicker.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css?v={$this->config->item('version')}"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte_custom/custom.css?v={$this->config->item('version')}"); ?>" />

<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/plugins/bootstrap-select/bootstrap-select.min.css?v={$this->config->item('version')}"); ?>"/>-->


<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("template/adminlte/bower_components/select2/dist/css/select2.css?v={$this->config->item('version')}"); ?>" />


<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/plugins/jquery-multi-select/css/multi-select.css?v={$this->config->item('version')}"); ?>"/>-->

<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/plugins/ion.rangeslider/css/ion.rangeSlider.Metronic.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/css/components-md.css.map?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/lib/timeline/build/css/timeline.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/plugins/fullcalendar/fullcalendar.min.css?v={$this->config->item('version')}"); ?>" />
<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/css/components-md.css?v={$this->config->item('version')}"); ?>"/>-->

<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/lib/bootstrap-colorselector/dist/bootstrap-colorselector.min.css"); ?>" />


<link rel="stylesheet" type="text/css" href="<?php echo base_url_template('template/adminlte/dist/css/jquery-ui.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template('template/adminlte/dist/css/jquery-ui.theme.css'); ?>" />


<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/dist/css/AdminLTE.min.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/dist/css/skins/_all-skins.min.css?v={$this->config->item('version')}"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/lib/lightbox2/css/lightbox.css"); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/lib/fancybox-2.1.5/jquery.fancybox.css?v=" . $this->config->item('version')); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template('template/crm-v2/assets/global/plugins/dropzone/css/dropzone.css'); ?>" />

<!-- STYLE OVERRIDE -->
<!--<link rel="stylesheet" type="text/css" href="<?php echo base_url_scripts("script/style.css?v={$this->config->item('version')}"); ?>" />-->

<?php if (defined('CUSTOM_CSS_PATH') && CUSTOM_CSS_PATH) : ?>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_admin(CUSTOM_CSS_PATH . "?v={$this->config->item('version')}"); ?>" />
<?php endif; ?>

<link rel="shortcut icon" href="<?php echo base_url('/favicon.ico'); ?>" />

<style>
    .main-header .sidebar-toggle {
        float: left;
        background-color: transparent;
        background-image: none;
        padding: 15px 15px;
        /* font-family: fontAwesome; */
        font-family: "Font Awesome\ 5 Free";
        /* cjr */
    }

    .main-header .sidebar-toggle:before {
        content: "\f0c9";
        font-weight: 900;
        /* cjr */
    }
</style>

<?php if (defined('CUSTOM_FAVICON') && CUSTOM_FAVICON) : ?>


    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo base_url_admin(CUSTOM_FAVICON); ?>-16x16.png">
    <!--<link rel="manifest" href="/manifest.json">-->
    <meta name="msapplication-TileImage" content="/<?php echo base_url_admin(CUSTOM_FAVICON); ?>-144x144.png">
    <meta name="theme-color" content="#ffffff">

<?php endif; ?>

<!-- JQUERY -->


<script>
    var base_url = <?php echo json_encode(base_url()); ?>;
    var base_url_admin = <?php echo json_encode(base_url_admin()); ?>;
    var base_url_template = <?php echo json_encode(base_url_template()); ?>;
    var base_url_scripts = <?php echo json_encode(base_url_scripts()); ?>;
    var base_url_uploads = <?php echo json_encode(base_url_uploads()); ?>;
</script>

<!-- HIGHCHARTS -->
<script src="<?php echo base_url_template("template/adminlte/bower_components/jquery/dist/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_template("script/js/jquery-migrate-3.0.0.min.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/highcharts-3.0.9/js/highcharts.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/highcharts-3.0.9/js/modules/exporting.js?v={$this->config->item('version')}"); ?>"></script>
<script src="<?php echo base_url_scripts("script/lib/highcharts-3.0.9/js/modules/funnel.js?v={$this->config->item('version')}"); ?>"></script>

<script>
    $(function() {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
            return {
                radialGradient: {
                    cx: 0.5,
                    cy: 0.3,
                    r: 0.7
                },
                stops: [
                    [0, color],
                    [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                ]
            };
        });
    });
</script>