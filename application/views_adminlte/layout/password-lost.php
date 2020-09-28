<?php
// What is today's date - number
$day = date("z");

//  Days of spring
$spring_starts = date("z", strtotime("March 21"));
$spring_ends   = date("z", strtotime("June 20"));

//  Days of summer
$summer_starts = date("z", strtotime("June 21"));
$summer_ends   = date("z", strtotime("September 22"));

//  Days of autumn
$autumn_starts = date("z", strtotime("September 23"));
$autumn_ends   = date("z", strtotime("December 20"));

//  If $day is between the days of spring, summer, autumn, and winter
if ($day >= $spring_starts && $day <= $spring_ends) :
    $season = "spring";
elseif ($day >= $summer_starts && $day <= $summer_ends) :
    $season = "summer";
elseif ($day >= $autumn_starts && $day <= $autumn_ends) :
    $season = "autumn";
else :
    $season = "winter";
endif;
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en" class="no-js">
<!--<![endif]-->
<!-- BEGIN HEAD -->

<head>
    <meta charset="utf-8" />
    <title><?php e('Password forget?'); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />
    <meta name="MobileOptimized" content="320">

    <!-- CORE LEVEL STYLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/font-awesome/css/font-awesome.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/Ionicons/css/ionicons.min.css?v={$this->config->item('version')}"); ?>" />

    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/dist/css/AdminLTE.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/plugins/iCheck/square/blue.css?v={$this->config->item('version')}"); ?>" />
    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <style>
        <?php if (defined('LOGIN_COLOR') && !empty(LOGIN_COLOR)) : ?>.login-page,
        .register-page {
            background: <?php echo LOGIN_COLOR; ?>;
        }

        <?php if (defined('LOGIN_TITLE_COLOR') && !empty(LOGIN_TITLE_COLOR)) : ?>.logo h2 {
            color: <?php echo LOGIN_TITLE_COLOR; ?>
        }

        <?php endif; ?><?php endif; ?>body {
            /*background: #121417!important;*/
            background: transparent !important;
            /*background-image: linear-gradient(rgba(23, 23, 23, 0.5), rgba(18, 20, 23, 0.5)), url('https://images.unsplash.com/photo-1485470733090-0aae1788d5af?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1391&q=80')!important;
                background-position: center!important; 
                background-repeat: no-repeat!important; 
                background-size: cover!important;
                min-height: 100%!important;*/
        }

        p {
            /*color: #121417!important;*/
            color: #fffffe !important;
        }

        .background_img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            /*background-image: linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.6)), url('https://images.unsplash.com/photo-1485470733090-0aae1788d5af?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=1391&q=80')!important;*/
            background-image: linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(<?php echo (!empty($season)) ? base_url("images/{$season}.jpg") : ''; ?>) !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            background-size: cover !important;
            min-height: 100% !important;
        }

        .login-box-body {
            background: none !important;
            /*color: #fffffe;*/
        }

        .title {
            color: #fffffe !important;
            text-align: center;
            padding: 0 20px 20px 20px;
        }

        input.form-control {
            border-radius: 20px;
            /*background-color: transparent;
                border: 1px solid #e3e3e3;
                border-radius: 30px;
                color: #2c2c2c;*/
        }

        div.form-group span {
            color: #121417 !important;
            margin-right: 10px;
        }

        .rounded_btn {
            width: 100% !important;
            border-radius: 20px !important;
            padding-top: 10px;
            padding-bottom: 10px;
            margin: 10px 0px;
            /*background-color: #24292e;
                border-color: #24292e!important;
                font-size: 16px;*/
            transition: background-color .45s ease;
        }

        /*.rounded_btn:active {
                background-color: #393e42!important;
            }
            .rounded_btn:hover {
                background-color: #393e42!important;
                border-color: #393e42!important;
            }*/

        .background_img {
            min-height: 100vh;
            max-height: 100%;
            top: 0;
            left: 0;
            padding: 0;
            color: #fff;
            position: absolute;
            overflow: hidden;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="background_img">
        <div class="login-box">
            <div class="logo">
                <div class="text-center">
                    <?php if ($this->settings === array()) : ?>
                        <h2 class="login-logo"><?php e('Your Company'); ?></h2>
                    <?php elseif ($this->settings['settings_company_logo']) : ?>
                        <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
                    <?php else : ?>
                        <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
                    <?php endif; ?>
                </div>
            </div>


            <div class="login-box-body">
                <?php if ($sent) : ?>
                    <h3 class="title"><?php e("E-mail sent successfully"); ?></h3>
                    <div class="alert alert-success">
                        <p><?php e("We've sent you and email to"); ?> <strong><?php echo $receiver; ?></strong> <?php e("which contains the link for reset the password."); ?> </p>
                    </div>
                <?php elseif ($pwd_resetted) : ?>
                    <h3 class="title"><?php e("Password resetted successfully"); ?></h3>
                    <div class="alert alert-success">
                        <p><?php e("Your new password has been sent to"); ?> <strong><?php echo $receiver; ?></strong></p>
                    </div>
                <?php else : ?>
                    <form id="lost" class="forget-form formAjax" action="<?php echo base_url('access/reset_password_request'); ?>" method="post" novalidate="novalidate" style="display: block;">
                        <?php add_csrf(); ?>
                        <h4 class="title"><?php e("Password lost?"); ?></h4>
                        <p><?php e("Type your e-mail address to reset the password."); ?></p>
                        <div class="form-group has-feedback">
                            <input class="form-control" type="text" autocomplete="off" placeholder="Email" name="email">
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>

                        <div class="form-group">
                            <div class="controls">
                                <div id="msg_lost" class="alert alert-danger hide"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-xs-12">
                                    <a class="btn btn-primary rounded_btn" href="<?php echo base_url('access/login'); ?>">
                                        <?php e('Login'); ?>
                                    </a>
                                </div>
                                <div class="col-xs-12 text-right">
                                    <button type="submit" class="btn btn-success rounded_btn">
                                        <?php e('Reset Password'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

        </div>

    </div>
    <div class="copyright"><?php /* powered by <a href="http://firegui.com" class="text-danger" target="_blank">FireGUI</a> */ ?></div>


    <!-- COMMON PLUGINS -->

    <script src="<?php echo base_url_template("template/adminlte/bower_components/jquery/dist/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>

    <script src="<?php echo base_url_template("template/adminlte/plugins/iCheck/icheck.min.js?v={$this->config->item('version')}"); ?>"></script>
    <!-- CUSTOM COMPONENTS -->
    <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>
</body>

</html>