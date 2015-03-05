<meta charset="utf-8" />

<title><?php echo (empty($this->settings['settings_company_short_name'])? 'Master': $this->settings['settings_company_short_name'].' -'); ?> CRM</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport" />
<meta content="" name="description" />
<meta content="" name="author" />
<meta name="MobileOptimized" content="320" />


<?php /*if(!empty($this->settings['settings_company_logo'])): ?> Viene da schifo, ma non capisco perché non me la prende con imgn - ci penserò
    <link href="<?php echo base_url_template("uploads/{$this->settings['settings_company_logo']}"); ?>" rel="icon" type="image/x-icon" />
<?php endif;*/ ?>


<!-- BEGIN GLOBAL MANDATORY STYLES -->
<link href="<?php echo base_url_template("template/crm/plugins/font-awesome/css/font-awesome.min.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap/css/bootstrap.min.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/uniform/css/uniform.default.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- BEGIN PAGE LEVEL PLUGIN STYLES --> 
<link href="<?php echo base_url_template("template/crm/plugins/gritter/css/jquery.gritter.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-fileupload/bootstrap-fileupload.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/fullcalendar/fullcalendar/fullcalendar.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/jqvmap/jqvmap/jqvmap.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/jquery-easy-pie-chart/jquery.easy-pie-chart.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-datepicker/css/datepicker.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-timepicker/compiled/timepicker.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-datetimepicker/css/datetimepicker.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url_template("template/crm/plugins/bootstrap-colorpicker/css/colorpicker.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url_template("template/crm/plugins/select2/select2_metro.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" />

<!-- END PAGE LEVEL PLUGIN STYLES -->
<!-- BEGIN THEME STYLES --> 
<link href="<?php echo base_url_template("template/crm/css/style-metronic.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/css/style.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/css/style-responsive.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/css/plugins.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/css/pages/tasks.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/css/themes/default.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css" id="style_color"/>
<link href="<?php echo base_url_template("template/crm/css/custom.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>
<link href="<?php echo base_url_template("template/crm/plugins/data-tables/DT_bootstrap.css?v=".$this->config->item('version')); ?>" rel="stylesheet" />
<link href="<?php echo base_url_template("script/lib/leaflet-0.7.2/leaflet.css?v=".$this->config->item('version')); ?>" rel="stylesheet" />
<link href="<?php echo base_url_template("script/lib/marker-cluster/MarkerCluster.css?v=".$this->config->item('version')); ?>" rel="stylesheet" />
<link href="<?php echo base_url_template("script/lib/marker-cluster/MarkerCluster.Default.css?v=".$this->config->item('version')); ?>" rel="stylesheet" />
<link href="<?php echo base_url_template("script/lib/fancybox-2.1.5/jquery.fancybox.css?v=".$this->config->item('version')); ?>" rel="stylesheet" />
<!-- END THEME STYLES -->

<!-- STYLE OVERRIDE -->
<link href="<?php echo base_url_template("script/style.css?v=".$this->config->item('version')); ?>" rel="stylesheet" />
<link href="<?php echo base_url_template("script/lib/ckeditor/contents.css?v=".$this->config->item('version')); ?>" rel="stylesheet" type="text/css"/>



<script src="<?php echo base_url_template("template/crm/plugins/jquery-1.10.2.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("template/crm/plugins/jquery-migrate-1.2.1.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>

<script src="<?php echo base_url_template("template/crm/plugins/jquery-migrate-1.2.1.min.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>

<script src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/highcharts.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/modules/exporting.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>
<script src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/modules/funnel.js?v=".$this->config->item('version')); ?>" type="text/javascript"></script>

<script>
    var base_url = '<?php echo base_url(); ?>';
    var base_url_template = '<?php echo base_url_template(); ?>';
</script>

<script>
    // HighCharts
    
     $(function() {

        // Radialize the colors
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function(color) {
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