<?php
if (!function_exists('base_url_template')) {
    require APPPATH . 'config/crm_configurations.php';
}

$config = &get_config();
?>

<!DOCTYPE html>
<!-- 
Template Name: Metronic - Responsive Admin Dashboard Template build with Twitter Bootstrap 3.0
Version: 1.5
Author: KeenThemes
Website: http://www.keenthemes.com/
Purchase: http://themeforest.net/item/metronic-responsive-admin-dashboard-template/4021469?ref=keenthemes
-->
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
    <!-- BEGIN HEAD -->
    <head>
        <meta charset="utf-8" />
        <title>Master CRM</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <meta name="MobileOptimized" content="320">

        <!-- BEGIN GLOBAL MANDATORY STYLES -->          
        <link href="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->

        <?php /*
          <!-- BEGIN PAGE LEVEL PLUGIN STYLES -->
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/gritter/css/jquery.gritter.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-fileupload/bootstrap-fileupload.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/fullcalendar/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/jquery-easy-pie-chart/jquery.easy-pie-chart.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-datepicker/css/datepicker.css" rel="stylesheet" type="text/css" />
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-timepicker/compiled/timepicker.css" rel="stylesheet" type="text/css" />
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-datetimepicker/css/datetimepicker.css" rel="stylesheet" type="text/css" />
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/bootstrap-colorpicker/css/colorpicker.css" rel="stylesheet" type="text/css" />
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/select2/select2_metro.css" rel="stylesheet" type="text/css" />

          <!-- END PAGE LEVEL PLUGIN STYLES -->
          <!-- BEGIN THEME STYLES -->
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/style-metronic.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/style.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/style-responsive.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/plugins.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/pages/tasks.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/pages/error.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/css/custom.css" rel="stylesheet" type="text/css"/>
          <link href="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/data-tables/DT_bootstrap.css" rel="stylesheet" />

          <!-- END THEME STYLES -->
         */ ?>

        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>   
    </head>
    <!-- END HEAD -->
    <!-- BEGIN BODY -->
    <body class="page-404-3">
        <div class="page-inner">
            <img src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/img/pages/earth.jpg" class="img-responsive" alt="">
        </div>
        <div class="container error-404">
            <h1>404</h1>
            <h2>Houston, we have a problem.</h2>
            <h5><?php echo $heading; ?></h5>
            <p>
                <?php echo $message; ?>
            </p>
            <p>
                <a href="<?php echo $config['base_url']; ?>">Return home</a>
                <br>
            </p>
        </div>
        <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
        <!-- BEGIN CORE PLUGINS -->   
        <!--[if lt IE 9]>
        <script src="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/respond.min.js"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>template/crm-v2/assets/global/plugins/excanvas.min.js"></script> 
        <![endif]-->   

        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js" type="text/javascript" ></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>  
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/jquery.cookie.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/assets/global/plugins/uniform/jquery.uniform.min.js" type="text/javascript" ></script>

        <!-- END CORE PLUGINS -->
        <script src="<?php echo base_url_template($config['base_url']); ?>/template/crm-v2/scripts/app.js"></script>  
        <script>
            jQuery(document).ready(function () {
                App.init();
            });
        </script>
        <!-- END JAVASCRIPTS -->
    </body>
    <!-- END BODY -->
</html>
