<?php
if (file_exists(__DIR__ . '/../custom/layout/login.php')) {
    $this->load->view('custom/layout/login');
} else {
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
        <title>Login</title>
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

        <link rel="shortcut icon" href="/favicon.ico" />

        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        <!-- Bootstrap-select -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/css/bootstrap-select.min.css">

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

            p,
            h5 {
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
                background-image: linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(<?php echo base_url("images/background.jpg"); ?>) !important;
                background-position: center !important;
                background-repeat: no-repeat !important;
                background-size: cover !important;
                min-height: 100% !important;
            }

            .login-box-body {
                background: none !important;
                /*color: #fffffe;*/
            }

            .disconnect {
                width: 100%;
                display: flex;
                justify-content: center;
                align-items: baseline;
                margin-top: 40px;
            }

            .disconnect_label {
                /*width: 50%;*/
            }

            .form-group select {
                color: #121417 !important;
                background-color: transparent !important;
                color: #fffffe !important;
                border: 1px solid #fffffe;
                width: 30%;
            }

            .transparent {
                background: transparent !important;
                color: white !important;
            }

            .form-group label {
                margin-right: 10px;
                color: #fffffe !important;
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

            /*add rules to bootstrap-select*/
            .dropdown-toggle {
                border-radius: 20px;
            }

            .bootstrap-select {
                max-width: 40% !important;
            }

            .bootstrap-select>.dropdown-toggle {
                background-color: transparent !important;
                color: #fffffe !important;
            }

            .bootstrap-select>.dropdown-toggle>.bs-caret>.caret {
                color: #fffffe !important;
            }

            .dropdown-menu>.active>a {
                background-color: #3c8dbc !important;
            }

            .dropdown-menu>.active>a>span {
                color: #fffffe !important;
            }

            /*end boostrap-select customization*/

            .forget_password {
                width: 100%;
                display: flex;
                /*justify-content: space-between;*/
                justify-content: center;
                align-items: center;
                flex-direction: column;
            }

            .password_title {}

            .password_reset p a {
                color: #fffffe !important;
                font-weight: 700;
            }

            .rounded_btn {
                width: 100% !important;
                border-radius: 20px !important;
                padding-top: 10px;
                padding-bottom: 10px;
                margin: 30px 0px;
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
                            <h2 class="login-logo"><?php e('La tua azienda'); ?></h2>
                        <?php elseif ($this->settings['settings_company_logo']) : ?>
                            <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
                        <?php else : ?>
                            <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="login-box-body">
                    <p class="login-box-msg"><?php e('Enter in your profile'); ?></p>

                    <form id="login" class="login-form formAjax" action="<?php echo base_url('access/login_start'); ?>" method="post">
                        <?php if ($this->input->get('source') == 'firegui') : ?>
                            <div class="box box-success box-solid">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><?php e('Welcome to your client!'); ?></h3>

                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="remove" onclick="location.href='<?php echo base_url(); ?>';"><i class="fa fa-times"></i></button>
                                    </div>
                                    <!-- /.box-tools -->
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body">
                                    You can login with the same email and password you used to register in FireGUI.com. After that, you can create new users or change your password directly in your reserved area.
                                    <br />
                                    <br /><em>ps.: if you don't want to see this message simply <a href="<?php echo base_url(); ?>">click here</a></em>
                                </div>
                                <!-- /.box-body -->
                            </div>

                        <?php endif; ?>

                        <div class="form-group has-feedback">
                            <input type="email" class="form-control" placeholder="<?php e('E-mail address'); ?>" name="users_users_email" />
                            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                        </div>
                        <div class="form-group has-feedback">
                            <input type="password" class="form-control" placeholder="<?php e('Password'); ?>" name="users_users_password">
                            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                        </div>

                        <div class="form-group">
                            <div class="controls">
                                <div id="msg_login" class="alert alert-danger hide"></div>
                            </div>
                        </div>

                        <div class="form-group disconnect">
                            <label class="control-label disconnect_label"><?php e('Disconnect after'); ?></label>
                            <select name="timeout" class="selectpicker" data-style="btn-default">
                                <!--<option value="1" class="form-control input-sm select2">1 minuto</option>-->
                                <option value="5" class="input-sm option_style">5 <?php e('minutes'); ?></option>
                                <option value="10" class="input-sm option_style">10 <?php e('minutes'); ?></option>
                                <option value="30" class="input-sm option_style">30 <?php e('minutes'); ?></option>
                                <option value="60" class="input-sm option_style">1 <?php e('hour'); ?></option>
                                <option value="120" class="input-sm option_style">2 <?php e('hours'); ?></option>
                                <option value="240" class="input-sm option_style" selected="selected">4 <?php e('hours'); ?></option>
                                <option value="480" class="input-sm option_style">8 <?php e('hours'); ?></option>
                                <option value="720" class="input-sm option_style">12 <?php e('hours'); ?></option>
                                <option value="1440" class="input-sm option_style">1 <?php e('day'); ?></option>
                                <option value="10080" class="input-sm option_style">7 <?php e('days'); ?></option>
                                <option value="43200" class="input-sm option_style">1 <?php e('month'); ?></option>
                                <option value="518400" class="input-sm option_style"><?php e('Never'); ?></option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <!--<div class="row">
                                <div class="col-xs-8">
                                    <div class="checkbox icheck" style="display:none;">
                                        <label>
                                            <input name="remember" value="1" type="checkbox"> <?php e('Remember me'); ?>
                                        </label>
                                    </div>

                                </div>
                                <div class="col-xs-4">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat rounded_btn"><?php e('Login'); ?></button>
                                </div>
                            </div>-->
                            <div class="row">
                                <div class="col-xs-12">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat rounded_btn"><?php e('Login'); ?></button>
                                </div>
                                <!-- /.col -->
                            </div>
                        </div>
                    </form>
                    <!-- /.social-auth-links -->
                    <div class="forget_password">
                        <div class="password_title">
                            <h5><?php e('Forgot your password?'); ?></h5>
                        </div>
                        <div class="password_reset">
                            <p><a href="<?php echo base_url("access/recovery"); ?>"><?php e('Click here'); ?></a> <?php e('to reset it.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="copyright"><?php /* powered by <a href="http://firegui.com" class="text-danger" target="_blank">FireGUI</a> */ ?></div>
            </div>
        </div><!-- /.background_img -->


        <!-- COMMON PLUGINS -->
        <script src="<?php echo base_url_template("template/adminlte/bower_components/jquery/dist/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/plugins/iCheck/icheck.min.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- CUSTOM COMPONENTS -->
        <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- Bootstrap-select -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.9/dist/js/bootstrap-select.min.js"></script>

        <script>
            $(function() {
                $('input').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%' /* optional */
                });
            });
        </script>
    </body>

    </html>
<?php } ?>