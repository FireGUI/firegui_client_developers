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
</head>

<body class="hold-transition login-page">
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
                <h3><?php e("E-mail sent successfully"); ?></h3>
                <div class="alert alert-success">
                    <p><?php e("We've sent you and email to"); ?> <strong><?php echo $receiver; ?></strong> <?php e("which contains the link for reset the password."); ?> </p>
                </div>
            <?php else : ?>
                <form id="lost" class="forget-form formAjax" action="<?php echo base_url('access/reset_password_request'); ?>" method="post" novalidate="novalidate" style="display: block;">
                    <h4><?php e("Password lost?"); ?></h4>
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
                            <div class="col-xs-6">
                                <a class="btn btn-primary" href="<?php echo base_url('access/login'); ?>">
                                    <?php e('Login'); ?>
                                </a>
                            </div>
                            <div class="col-xs-6 text-right">
                                <button type="submit" class="btn btn-success">
                                    <?php e('Reset Password'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
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