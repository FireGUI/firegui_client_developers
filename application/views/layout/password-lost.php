<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!--> <html lang="en" class="no-js"> <!--<![endif]-->
    <!-- BEGIN HEAD -->
    <head>
        <meta charset="utf-8" />
        <title>Login - Master CRM</title>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta content="width=device-width, initial-scale=1.0" name="viewport" />
        <meta content="" name="description" />
        <meta content="" name="author" />
        <meta name="MobileOptimized" content="320">
        <!-- BEGIN GLOBAL MANDATORY STYLES -->          
        <link href="<?php echo base_url_template() ?>template/crm/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
        <!-- END GLOBAL MANDATORY STYLES -->
        <!-- BEGIN PAGE LEVEL STYLES --> 
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template() ?>template/crm/plugins/select2/select2_metro.css" />
        <!-- END PAGE LEVEL SCRIPTS -->
        <!-- BEGIN THEME STYLES --> 
        <link href="<?php echo base_url_template() ?>template/crm/css/style-metronic.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/css/style.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/css/style-responsive.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/css/plugins.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/css/themes/default.css" rel="stylesheet" type="text/css" id="style_color"/>
        <link href="<?php echo base_url_template() ?>template/crm/css/pages/login.css" rel="stylesheet" type="text/css"/>
        <link href="<?php echo base_url_template() ?>template/crm/css/custom.css" rel="stylesheet" type="text/css"/>
        <!-- END THEME STYLES -->
        
        <?php /*<link rel="shortcut icon" href="favicon.ico" />*/ ?>
    </head>
    <!-- BEGIN BODY -->
    <body class="login">
        <!-- BEGIN LOGO -->
        <div class="logo">
            <?php if ($this->settings === array()): ?>
                <h2 class="text-danger">Your Company</h2>
            <?php elseif ($this->settings['settings_company_logo']): ?>
                <img src="<?php echo base_url_template("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
            <?php else: ?>
                <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
            <?php endif; ?>
        <!--<img src="<?php echo base_url_template() ?>template/crm/img/logo-big.png" alt="" />--> 
        </div>
        <!-- END LOGO -->
        <!-- BEGIN LOGIN -->
        <div class="content">

            <?php if($sent): ?>
                    <h3>E-mail inviata correttamente</h3>
                    <p>Ti è stata inviata una mail all'indirizzo <strong><?php echo $receiver; ?></strong> contenente il link necessario per effettuare il reset della password.</p>
            <?php else: ?>
                <form class="forget-form formAjax" action="<?php echo base_url('access/reset_password_request'); ?>" method="post" novalidate="novalidate" style="display: block;">
                    <h3>Password dimenticata?</h3>
                    <p>Inserisci il tuo indirizzo e-mail per resettare la password</p>
                    <div class="form-group">
                        <div class="input-icon">
                            <i class="icon-envelope"></i>
                            <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Email" name="email">
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn green pull-right">
                            Submit <i class="m-icon-swapright m-icon-white"></i>
                        </button>            
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        
        <!-- END LOGIN -->
        <!-- BEGIN COPYRIGHT -->
        <div class="copyright">powered by <a href="http://h2-web.it" class="text-danger" target="_blank">H2 web</a></div>
        <!-- END COPYRIGHT -->
        <!-- BEGIN JAVASCRIPTS(Load javascripts at bottom, this will reduce page load time) -->
        <!-- BEGIN CORE PLUGINS -->   
        <!--[if lt IE 9]>
        <script src="assets/plugins/respond.min.js"></script>
        <script src="assets/plugins/excanvas.min.js"></script> 
        <![endif]-->   
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery-1.10.2.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery-migrate-1.2.1.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/plugins/bootstrap-hover-dropdown/twitter-bootstrap-hover-dropdown.min.js" type="text/javascript" ></script>
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery.blockui.min.js" type="text/javascript"></script>  
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery.cookie.min.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/plugins/uniform/jquery.uniform.min.js" type="text/javascript" ></script>
        <!-- END CORE PLUGINS -->
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery-validation/dist/jquery.validate.min.js" type="text/javascript"></script>	
        <script type="text/javascript" src="<?php echo base_url_template() ?>template/crm/plugins/select2/select2.min.js"></script>     
        <!-- END PAGE LEVEL PLUGINS -->
        <!-- BEGIN PAGE LEVEL SCRIPTS -->
        <script src="<?php echo base_url_template() ?>template/crm/scripts/app.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/scripts/login.js" type="text/javascript"></script> 
        <script src="<?php echo base_url_template() ?>script/js/submitajax.js" type="text/javascript"></script> 
        <!-- END PAGE LEVEL SCRIPTS --> 
        <script>
            jQuery(document).ready(function () {
                App.init();
                Login.init();
            });
        </script>
        <!-- END JAVASCRIPTS -->
    </body>
    <!-- END BODY -->
</html>