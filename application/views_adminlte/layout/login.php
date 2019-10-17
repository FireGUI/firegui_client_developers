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



        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        <style>
            <?php if (defined('LOGIN_COLOR') && !empty(LOGIN_COLOR)) : ?>.login-page,
            .register-page {
                background: <?php echo LOGIN_COLOR; ?>;
            }

            <?php endif; ?>
        </style>
    </head>



    <body class="hold-transition login-page">

        <div class="login-box">
            <div class="logo">
                <div class="text-center">
                    <?php if ($this->settings === array()) : ?>
                        <h2 class="login-logo">Your Company</h2>
                    <?php elseif ($this->settings['settings_company_logo']) : ?>
                        <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" style="max-width: 360px;" />
                    <?php else : ?>
                        <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
                    <?php endif; ?>
                </div>
            </div>

            <div class="login-box-body">
                <p class="login-box-msg"><?php e('Entra nel tuo profilo'); ?></p>

                <form id="login" class="login-form formAjax" action="<?php echo base_url('access/login_start'); ?>" method="post">
                    <div class="form-group has-feedback">
                        <input type="email" class="form-control" placeholder="Indirizzo e-mail" name="users_users_email" />
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" placeholder="Password" name="users_users_password">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>

                    <div class="form-group">
                        <div class="controls">
                            <div id="msg_login" class="alert alert-danger hide"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label"><?php e('Disconnetti dopo'); ?></label>
                        <select name="timeout">
                            <!--<option value="1" class="form-control input-sm select2">1 minuto</option>-->
                            <option value="5" class="form-control input-sm select2">5 minuti</option>
                            <option value="10" class="form-control input-sm select2">10 minuti</option>
                            <option value="30" class="form-control input-sm select2">30 minuti</option>
                            <option value="60" class="form-control input-sm select2">1 ora</option>
                            <option value="120" class="form-control input-sm select2">2 ore</option>
                            <option value="240" class="form-control input-sm select2" selected="selected">4 ore</option>
                            <option value="480" class="form-control input-sm select2">8 ore</option>
                            <option value="720" class="form-control input-sm select2">12 ore</option>
                            <option value="1440" class="form-control input-sm select2">1 giorno</option>
                            <option value="10080" class="form-control input-sm select2">7 giorni</option>
                            <option value="43200" class="form-control input-sm select2">1 mese</option>
                            <option value="518400" class="form-control input-sm select2">Mai</option>
                        </select>
                    </div>



                    <div class="form-actions">
                        <div class="row">
                            <div class="col-xs-8">
                                <div class="checkbox icheck" style="display:none;">
                                    <label>
                                        <input name="remember" value="1" type="checkbox"> <?php e('Ricordami'); ?>
                                    </label>
                                </div>

                            </div>
                            <!-- /.col -->
                            <div class="col-xs-4">
                                <button type="submit" class="btn btn-primary btn-block btn-flat">Login</button>
                            </div>
                            <!-- /.col -->
                        </div>
                    </div>
                </form>


                <!-- /.social-auth-links -->

                <div class="forget-password">
                    <h5><?php e('Hai dimenticato la tua password?'); ?></h5>
                    <p><a href="<?php echo base_url("access/recovery"); ?>"><?php e('Clicca qui'); ?></a> <?php e('per resettarla.'); ?></p>
                </div>


            </div>


            <div class="copyright"><?php /* powered by <a href="http://h2web.it" class="text-danger" target="_blank">H2 web</a> */ ?></div>
        </div>

        <!-- COMMON PLUGINS -->

        <script src="<?php echo base_url_template("template/adminlte/bower_components/jquery/dist/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>

        <script src="<?php echo base_url_template("template/adminlte/plugins/iCheck/icheck.min.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- CUSTOM COMPONENTS -->
        <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>


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