<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title>Password dimenticata?</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="MobileOptimized" content="320">

    <!-- CORE LEVEL STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/font-awesome/css/font-awesome.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/simple-line-icons/simple-line-icons.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/css/bootstrap.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/css/uniform.default.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css?v={$this->config->item('version')}"); ?>" />

    <!-- BEGIN PAGE LEVEL STYLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/pages/css/login-soft.css?v={$this->config->item('version')}"); ?>" />


    <!-- BEGIN THEME STYLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/css/components-md.css?v={$this->config->item('version')}"); ?>" id="style_components" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/css/plugins-md.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/layout/css/layout.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/layout/css/themes/darkblue.css?v={$this->config->item('version')}"); ?>" id="style_color" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/layout/css/custom.css?v={$this->config->item('version')}"); ?>" />

    <!-- JQUERY -->
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-migrate.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script>
        var base_url = <?php echo json_encode(base_url()); ?>;
        var base_url_admin = <?php echo json_encode(base_url_admin()); ?>;
        var base_url_template = <?php echo json_encode(base_url_template()); ?>;
        var base_url_scripts = <?php echo json_encode(base_url_scripts()); ?>;
        var base_url_uploads = <?php echo json_encode(base_url_uploads()); ?>;
    </script>
</head>



<body class="login">

    <div class="logo">
        <?php if ($this->settings === array()) : ?>
            <h2 class="text-danger">Your Company</h2>
        <?php elseif ($this->settings['settings_company_logo']) : ?>
            <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
        <?php else : ?>
            <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
        <?php endif; ?>
    </div>

    <div class="content">

        <h3>Password resettata</h3>
        <div class="alert alert-success">
            <p>Controlla la tua casella di posta, dove troverai la nuova password. Verrai reindirizzato alla pagina di login entro 30 secondi...</p>
        </div>

    </div>




    <div class="copyright"><?php /*powered by <a href="http://firegui.com" class="text-danger" target="_blank">FireGUI</a>*/ ?></div>



    <!-- COMMON PLUGINS -->
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-ui/jquery-ui.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js?v=" . $this->config->item('version')); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.blockui.min.js?v=" . $this->config->item('version')); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.cokie.min.js?v=" . $this->config->item('version')); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/jquery.uniform.min.js?v=" . $this->config->item('version')); ?>"></script>

    <!-- METRONIC SCRIPTS -->
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/scripts/metronic.js?v={$this->config->item('version')}"); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/admin/layout/scripts/layout.js?v={$this->config->item('version')}"); ?>"></script>
    <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/backstretch/jquery.backstretch.min.js?v={$this->config->item('version')}"); ?>"></script>

    <!-- CUSTOM COMPONENTS -->
    <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>

    <script>
        $(function() {

            Metronic.init();
            Layout.init();

            // init background slide images
            var images = [
                base_url_template + "template/crm-v2/assets/admin/pages/media/bg/1.jpg",
                base_url_template + "template/crm-v2/assets/admin/pages/media/bg/2.jpg",
                base_url_template + "template/crm-v2/assets/admin/pages/media/bg/3.jpg",
                base_url_template + "template/crm-v2/assets/admin/pages/media/bg/4.jpg",
            ];

            $.backstretch(images, {
                fade: 1000,
                duration: 8000
            });

        });

        setTimeout(function() {
            location.href = '<?php echo base_url('access'); ?>'
        }, 30000);
    </script>

</body>

</html>