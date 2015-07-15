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
            <?php if($this->settings===array()): ?>
                <h2 class="text-danger">Your Company</h2>
            <?php elseif($this->settings['settings_company_logo']): ?>
                <img src="<?php echo base_url_template("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
            <?php else: ?>
                <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
            <?php endif; ?>
            <!--<img src="<?php echo base_url_template() ?>template/crm/img/logo-big.png" alt="" />--> 
        </div>
        <!-- END LOGO -->
        <!-- BEGIN LOGIN -->
        <div class="content">
            <!-- BEGIN LOGIN FORM -->
            <form id="login" class="login-form formAjax" action="<?php echo base_url('access/login_start'); ?>" method="post">
                
                <h3 class="form-title">Login to your account</h3>
                
                <div class="alert alert-error hide">
                    <button class="close" data-dismiss="alert"></button>
                    <span>Enter any username and password.</span>
                </div>
                
                <div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9">Indirizzo e-mail</label>
                    <div class="input-icon">
                        <i class="icon-envelope"></i>
                        <input class="form-control placeholder-no-fix" type="email" autocomplete="off" placeholder="Indirizzo e-mail" name="users_users_email"/>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <div class="input-icon">
                        <i class="icon-lock"></i>
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
                    <button type="submit" class="btn green pull-right">
                        Login <i class="m-icon-swapright m-icon-white"></i>
                    </button>            
                </div>
                <div class="forget-password">
                    <h4>Hai dimenticato la tua password?</h4>
                    <p><a href="<?php echo base_url("access/recovery"); ?>">Clicca qui</a> per resettarla.</p>
                </div>
                <?php /*
                <div class="forget-password">
                    <h4>Forgot your password ?</h4>
                    <p>
                        no worries, click <a href="javascript:;"  id="forget-password">here</a>
                        to reset your password.
                    </p>
                </div>
                <div class="create-account">
                    <p>
                        Don't have an account yet ?&nbsp; 
                        <a href="javascript:;" id="register-btn" >Create an account</a>
                    </p>
                </div>
                 */ ?>
            </form>
            <!-- END LOGIN FORM -->
            
            
            
            <!-- BEGIN FORGOT PASSWORD FORM -->
            <form class="forget-form" action="index.html" method="post">
                <h3 >Forget Password ?</h3>
                <p>Enter your e-mail address below to reset your password.</p>
                <div class="form-group">
                    <div class="input-icon">
                        <i class="icon-envelope"></i>
                        <input class="form-control placeholder-no-fix" type="text" autocomplete="off" placeholder="Email" name="email" />
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" id="back-btn" class="btn">
                        <i class="m-icon-swapleft"></i> Back
                    </button>
                    <button type="submit" class="btn green pull-right">
                        Submit <i class="m-icon-swapright m-icon-white"></i>
                    </button>            
                </div>
            </form>
            <!-- END FORGOT PASSWORD FORM -->
            <!-- BEGIN REGISTRATION FORM -->
            <form class="register-form" action="index.html" method="post">
                <h3 >Sign Up</h3>
                <p>Enter your personal details below:</p>
                <div class="form-group">
                    <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                    <label class="control-label visible-ie8 visible-ie9">Email</label>
                    <div class="input-icon">
                        <i class="icon-envelope"></i>
                        <input class="form-control placeholder-no-fix" type="text" placeholder="Email" name="email"/>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Password</label>
                    <div class="input-icon">
                        <i class="icon-lock"></i>
                        <input class="form-control placeholder-no-fix" type="password" autocomplete="off" id="register_password" placeholder="Password" name="password"/>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="control-label visible-ie8 visible-ie9">Re-type Your Password</label>
                    <div class="controls">
                        <div class="input-icon">
                            <i class="icon-ok"></i>
                            <input class="form-control placeholder-no-fix" type="password" autocomplete="off" placeholder="Re-type Your Password" name="rpassword"/>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="tnc"/> I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>  
                    <div id="register_tnc_error"></div>
                </div>
                <div class="form-actions">
                    <button id="register-back-btn" type="button" class="btn">
                        <i class="m-icon-swapleft"></i>  Back
                    </button>
                    <button type="submit" id="register-submit-btn" class="btn green pull-right">
                        Sign Up <i class="m-icon-swapright m-icon-white"></i>
                    </button>            
                </div>
            </form>
            <!-- END REGISTRATION FORM -->
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
        <script src="<?php echo base_url_template() ?>script/js/submitajax.js" type="text/javascript"></script> 
        <script src="<?php echo base_url_template() ?>template/crm/plugins/jquery-validation/dist/jquery.validate.min.js" type="text/javascript"></script>	
        <script type="text/javascript" src="<?php echo base_url_template() ?>template/crm/plugins/select2/select2.min.js"></script>     
        <!-- END PAGE LEVEL PLUGINS -->
        <!-- BEGIN PAGE LEVEL SCRIPTS -->
        <script src="<?php echo base_url_template() ?>template/crm/scripts/app.js" type="text/javascript"></script>
        <script src="<?php echo base_url_template() ?>template/crm/scripts/login.js" type="text/javascript"></script> 
        <!-- END PAGE LEVEL SCRIPTS --> 
        <script>
            jQuery(document).ready(function() {
                App.init();
                Login.init();
            });
        </script>
        <!-- END JAVASCRIPTS -->
    </body>
    <!-- END BODY -->
</html>