<?php
/**
 * File CSS tema
 */
$theme = (defined('THEME_CSS_PATH') && THEME_CSS_PATH) ? THEME_CSS_PATH : 'template/crm-v2/assets/admin/layout/css/themes/darkblue.css';

$tprefix = empty($dati['title_prefix'])? '': $dati['title_prefix'];
$title = (empty($this->settings['settings_company_short_name']) ? 'MasterCRM' : $this->settings['settings_company_short_name']);
$tsuffix = 'CRM';

?>
<meta charset="utf-8" />
<title><?php echo ucwords(trim(implode(' - ', array_filter([$tprefix, $title, $tsuffix])))); ?></title>

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta content="" name="description"/>


<!-- CORE LEVEL STYLES -->
<link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/font-awesome/css/font-awesome.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/simple-line-icons/simple-line-icons.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/css/bootstrap.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/css/uniform.default.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css?v={$this->config->item('version')}"); ?>"/>

<!-- BEGIN PLUGINS STYLES --> 
<?php /*
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/gritter/css/jquery.gritter.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jqvmap/jqvmap/jqvmap.css?v=" . $this->config->item('version')); ?>"/>
 */ ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-colorpicker/css/colorpicker.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css?v={$this->config->item('version')}"); ?>"/>

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-fileinput/bootstrap-fileinput.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/fullcalendar/fullcalendar.min.css?v=" . $this->config->item('version')); ?>"/>

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-select/bootstrap-select.min.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/select2/select2.css?v=" . $this->config->item('version')); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-multi-select/css/multi-select.css?v=" . $this->config->item('version')); ?>" />

<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/extensions/Scroller/css/dataTables.scroller.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css?v={$this->config->item('version')}"); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/datatables/plugins/bootstrap/dataTables.bootstrap.css?v={$this->config->item('version')}"); ?>"/>

<!-- LIBRARIES STYLES -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/leaflet-0.7.2/leaflet.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/leaflet-0.7.2/leaflet.draw.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/marker-cluster/MarkerCluster.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/marker-cluster/MarkerCluster.Default.css?v=" . $this->config->item('version')); ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/fancybox-2.1.5/jquery.fancybox.css?v=" . $this->config->item('version')); ?>"/>
<?php /*<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/ckeditor/contents.css?v={$this->config->item('version')}"); ?>"/> */ ?>
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/timeline/build/css/timeline.css"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/lib/lightbox2/css/lightbox.css"); ?>" />



<!-- THEME STYLES --> 
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/css/components-md.css?v={$this->config->item('version')}"); ?>" id="style_components" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/css/plugins-md.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/layout/css/layout.css?v={$this->config->item('version')}"); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("{$theme}?v={$this->config->item('version')}"); ?>" id="style_color" />
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/layout/css/custom.css?v={$this->config->item('version')}"); ?>" />

<!-- STYLE OVERRIDE -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url_template("script/style.css?v={$this->config->item('version')}"); ?>" />

<?php if(defined('CUSTOM_CSS_PATH') && CUSTOM_CSS_PATH): ?>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template(CUSTOM_CSS_PATH . "?v={$this->config->item('version')}"); ?>" />
<?php endif; ?>

<!-- JQUERY -->
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-migrate.min.js?v={$this->config->item('version')}"); ?>"></script>
<script>
    var base_url = <?php echo json_encode(base_url()); ?>;
    var base_url_template = <?php echo json_encode(base_url_template()); ?>;
</script>

<!-- HIGHCHARTS -->
<script type="text/javascript" src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/highcharts.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/modules/exporting.js?v={$this->config->item('version')}"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url_template("script/lib/highcharts-3.0.9/js/modules/funnel.js?v={$this->config->item('version')}"); ?>"></script>
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