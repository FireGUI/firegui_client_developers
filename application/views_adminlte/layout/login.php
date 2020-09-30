<?php
if (file_exists(__DIR__ . '/../custom/layout/login.php')) {
    $this->load->view('custom/layout/login');
} else {
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
        <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte_custom/custom.css?v={$this->config->item('version')}"); ?>" />

        <link rel="shortcut icon" href="/favicon.ico" />

        <!-- Google Font -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

        <!-- Bootstrap-select -->
        <link rel="stylesheet" href="<?php echo base_url("script/global/plugins/bootstrap-select/bootstra-select.min.css?v={$this->config->item('version')}"); ?>">

        <style>
            <?php if (defined('LOGIN_COLOR') && !empty(LOGIN_COLOR)) : ?>.login-page,
            .register-page {
                background: <?php echo LOGIN_COLOR; ?>;
            }

            <?php if (defined('LOGIN_TITLE_COLOR') && !empty(LOGIN_TITLE_COLOR)) : ?>.logo h2 {
                color: <?php echo LOGIN_TITLE_COLOR; ?>
            }

            <?php endif; ?><?php endif; ?>.background_img {
                background-image: linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(<?php echo (!empty($season)) ? base_url("images/{$season}.jpg") : ''; ?>) !important;
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
                            <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" class="logo" />
                        <?php else : ?>
                            <h2 class=" text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="login-box-body">
                    <p class="login-box-msg login-p"><?php e('Enter in your profile'); ?></p>

                    <form id="login" class="login-form rounded formAjax" action="<?php echo base_url('access/login_start'); ?>" method="post">
                        <?php add_csrf(); ?>

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
                            <div class="row">
                                <div class="col-xs-12">
                                    <button type="submit" class="btn btn-primary btn-block btn-flat rounded_btn"><?php e('Login'); ?></button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="forget_password">
                        <div class="password_title">
                            <h5 class="login-p"><?php e('Forgot your password?'); ?></h5>
                        </div>
                        <div class="password_reset">
                            <p class="login-p"><a href="<?php echo base_url("access/recovery"); ?>"><?php e('Click here'); ?></a> <?php e('to reset it.'); ?></p>
                        </div>
                    </div>
                    <?php if ($this->input->get('source') == 'firegui') : ?>
                        <div class="box box-primary box-solid firegui-box">
                            <div class=" box-header with-border">
                                <h3 class="box-title"><?php e('Welcome to your client!'); ?></h3>

                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="remove" onclick="location.href='<?php echo base_url(); ?>';"><i class="fa fa-times"></i></button>
                                </div>
                            </div>

                            <div class="box-body">
                                <?php e('You can login with the same email and password you used to register in FireGUI.com. After that, you can create new users or change your password directly in your reserved area.'); ?>
                                <br />
                                <br />
                                <em>
                                    <?php e('ps.: if you don\'t want to see this message simply'); ?> <a href="<?php echo base_url(); ?>"><?php e('click here'); ?></a>
                                </em>
                            </div>
                        </div>

                    <?php endif; ?>
                </div>
            </div>
        </div>



        <!-- COMMON PLUGINS -->
        <script src="<?php echo base_url_template("template/adminlte/bower_components/jquery/dist/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/plugins/iCheck/icheck.min.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- CUSTOM COMPONENTS -->
        <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- Bootstrap-select -->
        <script src="<?php echo base_url("script/global/plugins/bootstrap-select/bootstra-select.min.js?v={$this->config->item('version')}"); ?>"></script>
    </body>

    </html>
<?php } ?>