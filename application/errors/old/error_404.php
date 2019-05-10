<?php
if (!function_exists('base_url_admin')) {
    require APPPATH . 'config/config.php';
}

$config = &get_config();
?>



<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
    <!--<![endif]-->
    <!-- BEGIN HEAD -->
    <head>
        <meta charset="utf-8"/>
        <title>404 | Pagina non trovata</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
        <meta http-equiv="Content-type" content="text/html; charset=utf-8">
        <meta content="" name="description"/>
        <meta content="" name="author"/>
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        <!-- BEGIN PAGE LEVEL STYLES -->
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/pages/css/error.css" rel="stylesheet" type="text/css"/>
        <!-- END PAGE LEVEL STYLES -->
        <!-- BEGIN THEME STYLES -->
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/css/components-md.css" id="style_components" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/css/plugins-md.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
        <link id="style_color" href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
        <!-- END THEME STYLES -->
        <link rel="shortcut icon" href="favicon.ico"/>
    </head>
    <!-- END HEAD -->
    <!-- BEGIN BODY -->
    <body class="page-md page-404-3">
        <div class="page-inner">
            <img src="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/pages/media/pages/earth.jpg" class="img-responsive" alt="">
        </div>
        <div class="container error-404">
            <h1>404</h1>
            <h2>Houston, abbiamo un problema.</h2>
            <h5><?php echo $heading; ?></h5>
            <p>
                <?php 
                if (function_exists('log_error_slack')) {
                    log_error_slack($message);
                }
                echo $message; ?>
            </p>
            <p>
                <a href="<?php echo $config['base_url']; ?>">Ritorna al sito</a>
                <br>
            </p>
        </div>

        <!-- END JAVASCRIPTS -->
    </body>
    <!-- END BODY -->
</html>


