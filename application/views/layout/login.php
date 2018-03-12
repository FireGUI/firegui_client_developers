<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
    <!-- BEGIN HEAD -->
    <head>
        <meta charset="utf-8" />
        <title>Login</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <meta name="MobileOptimized" content="320">

        <!-- CORE LEVEL STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/font-awesome/css/font-awesome.min.css?v={$this->config->item('version')}"); ?>"/>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/simple-line-icons/simple-line-icons.min.css?v={$this->config->item('version')}"); ?>"/>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/css/bootstrap.min.css?v={$this->config->item('version')}"); ?>"/>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/css/uniform.default.css?v={$this->config->item('version')}"); ?>"/>
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css?v={$this->config->item('version')}"); ?>"/>

        <!-- BEGIN PAGE LEVEL STYLES --> 
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/crm-v2/assets/admin/pages/css/login-soft.css?v={$this->config->item('version')}"); ?>"/>


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
            var base_url_template = <?php echo json_encode(base_url_template()); ?>;
        </script>
    </head>



    <body class="login">

        <div class="logo">
            <?php if ($this->settings === array()): ?>
                <h2 class="text-danger">Your Company</h2>
            <?php elseif ($this->settings['settings_company_logo']): ?>
                <img src="<?php echo base_url_template("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
            <?php else: ?>
                <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
            <?php endif; ?>
        </div>

        <div class="content">
            <form id="login" class="login-form formAjax" action="<?php echo base_url('access/login_start'); ?>" method="post">

                <h3 class="form-title">Entra nel tuo profilo</h3>

                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Indirizzo e-mail</label>
                    <div class="input-icon">
                        <i class="fa fa-envelope"></i>
                        <input class="form-control placeholder-no-fix" type="email" autocomplete="off" placeholder="Indirizzo e-mail" name="users_users_email"/>
                    </div>
                </div>

                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <div class="input-icon">
                        <i class="fa fa-lock"></i>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Password" name="users_users_password"/>
                    </div>
                </div>

                <div class="form-group">
                    <div class="controls">
                        <div id="msg_login" class="alert alert-danger hide"></div>
                    </div>
                </div>

                <div class="form-actions" style="border-bottom: none;">
                    <label class="checkbox">
                        <input type="checkbox" name="remember" value="1"/> Ricordami
                    </label>
                    <button type="submit" class="btn blue pull-right">
                        Login <i class="m-icon-swapright m-icon-white"></i>
                    </button>            
                </div>
                <div class="forget-password">
                    <h4>Hai dimenticato la tua password?</h4>
                    <p><a href="<?php echo base_url("access/recovery"); ?>">Clicca qui</a> per resettarla.</p>
                </div>
            </form>
        </div>




        <div class="copyright"><?php /*powered by <a href="http://h2-web.it" class="text-danger" target="_blank">H2 web</a>*/ ?></div>



        <!-- COMMON PLUGINS -->
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-ui/jquery-ui.min.js?v={$this->config->item('version')}"); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js?v={$this->config->item('version')}"); ?>" ></script>
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.blockui.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/jquery.cokie.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/uniform/jquery.uniform.min.js?v=" . $this->config->item('version')); ?>" ></script>

        <!-- METRONIC SCRIPTS -->
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/scripts/metronic.js?v={$this->config->item('version')}"); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/admin/layout/scripts/layout.js?v={$this->config->item('version')}"); ?>"></script> 
        <script type="text/javascript" src="<?php echo base_url_template("template/crm-v2/assets/global/plugins/backstretch/jquery.backstretch.min.js?v={$this->config->item('version')}"); ?>"></script> 

        <!-- CUSTOM COMPONENTS -->
        <script type="text/javascript" src="<?php echo base_url_template("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script> 

        <script>

            $(function () {

                Metronic.init();
                Layout.init();

                // init background slide images
                var images = [
                    base_url_template + "template/crm-v2/assets/admin/pages/media/bg/1.jpg",
                    base_url_template + "template/crm-v2/assets/admin/pages/media/bg/2.jpg",
                    base_url_template + "template/crm-v2/assets/admin/pages/media/bg/3.jpg",
                    base_url_template + "template/crm-v2/assets/admin/pages/media/bg/4.jpg",
                ];

                $.backstretch(images, {fade: 1000, duration: 8000});

            });

        </script>

    </body>
</html>