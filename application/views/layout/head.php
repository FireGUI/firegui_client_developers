<?php
$tplColorHead = defined('COLOR_HEADER') ? COLOR_HEADER : null;
$tplColorSide = defined('COLOR_SIDEBAR') ? COLOR_SIDEBAR : null;
$tplColorCont = defined('COLOR_CONTAINER') ? COLOR_CONTAINER : null;
?>
<meta charset="utf-8" />

<title><?php echo (empty($this->settings['settings_company_short_name']) ? 'Master' : $this->settings['settings_company_short_name'] . ' -'); ?> CRM</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta content="" name="description" />
<meta content="" name="author" />
<meta name="MobileOptimized" content="320" />


<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/font-awesome/css/font-awesome.min.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap/css/bootstrap.min.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/uniform/css/uniform.default.css?v=" . $this->config->item('version')); ?>"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL PLUGIN STYLES --> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/gritter/css/jquery.gritter.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-fileupload/bootstrap-fileupload.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/fullcalendar/fullcalendar/fullcalendar.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/jqvmap/jqvmap/jqvmap.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/jquery-easy-pie-chart/jquery.easy-pie-chart.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-datepicker/css/datepicker.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-timepicker/compiled/timepicker.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-datetimepicker/css/datetimepicker.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/bootstrap-colorpicker/css/colorpicker.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/select2/select2_metro.css?v=" . $this->config->item('version')); ?>" />

<!-- END PAGE LEVEL PLUGIN STYLES -->
<!-- BEGIN THEME STYLES --> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/style-metronic.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/style.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/style-responsive.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/plugins.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/pages/tasks.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/themes/default.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/css/custom.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm/plugins/data-tables/DT_bootstrap.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/leaflet-0.7.2/leaflet.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/marker-cluster/MarkerCluster.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/marker-cluster/MarkerCluster.Default.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/fancybox-2.1.5/jquery.fancybox.css?v=" . $this->config->item('version')); ?>"/>
<!-- END THEME STYLES -->

<!-- STYLE OVERRIDE -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/style.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/ckeditor/contents.css?v=" . $this->config->item('version')); ?>"/>



<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/jquery-1.10.2.min.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/jquery-migrate-1.2.1.min.js?v=" . $this->config->item('version')); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_template("template/crm/plugins/jquery-migrate-1.2.1.min.js?v=" . $this->config->item('version')); ?>"></script>

<script type="text/javascript" src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/highcharts.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/modules/exporting.js?v=" . $this->config->item('version')); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/modules/funnel.js?v=" . $this->config->item('version')); ?>"></script>


<?php
if ($tplColorHead OR $tplColorSide OR $tplColorCont) {
    
    // Inizializza array di stili
    $style = [];
    
    // Header
    if ($tplColorHead) {
        $style[] = ".header {background-color: {$tplColorHead}!important;}";
    }
    
    // Sidebar/Footer/Colore base
    if ($tplColorSide) {
        $style[] = "body {background-color: {$tplColorSide} !important;}";
        $style[] = ".page-sidebar {background-color: {$tplColorSide} !important;}";
        $style[] = ".page-sidebar .sidebar-search input {background-color: {$tplColorSide} !important;}";
    }
    
    // Container
    if ($tplColorCont) {
        $style[] = ".page-content {background-color: {$tplColorCont} !important;}";
    }
    
    // Stampa lo stile
    if ($style) {
        echo '<style>' . PHP_EOL . implode(PHP_EOL, $style) . PHP_EOL . '</style>';
    }
}
?>



<script>
    var base_url = '<?php echo base_url(); ?>';
    var base_url_template = '<?php echo base_url_template(); ?>';
</script>

<script>
    $(function () {
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [
                    [0, color],
                    [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                ]
            };
        });
    });
</script>