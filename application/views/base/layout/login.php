<?php
if (file_exists(VIEWPATH . 'custom/layout/login.php')) {
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
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/@fortawesome/fontawesome-free/css/all.min.css?v=" . VERSION); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/Ionicons/css/ionicons.min.css?v={$this->config->item('version')}"); ?>" />

    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/dist/css/AdminLTE.min.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/plugins/iCheck/square/blue.css?v={$this->config->item('version')}"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte_custom/custom.css?v={$this->config->item('version')}"); ?>" />

    <link rel="shortcut icon" href="/favicon.ico" />

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Bootstrap-select -->
    <link rel="stylesheet" href="<?php echo base_url("script/global/plugins/bootstrap-select/bootstrap-select.min.css?v={$this->config->item('version')}"); ?>">

    <?php $this->layout->addDinamicJavascript([
            //"var base_url = '" . base_url() . "';",
            //"var base_url_admin = '" . base_url_admin() . "';",
            //"var base_url_template = '" . base_url_template() . "';",
            //"var base_url_scripts = '" . base_url_scripts() . "';",
            //"var base_url_uploads = '" . base_url_uploads() . "';",
            "var lang_code = '" . ((!empty($lang['languages_code'])) ? $lang['languages_code'] : 'en-EN') . "';",
            "var lang_short_code = '" . ((!empty($lang['languages_code'])) ? (explode('-', $lang['languages_code'])[0]) : 'en') . "';",
        ], 'config.js'); ?>

    <?php

        if ($this->settings['settings_login_background']) {
            $data['custom'] = [
                '.background_img' => [
                    'background-image' => "linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(" . base_url("uploads/" . $this->settings['settings_login_background']) . ")!important"
                ]
            ];
        } else {
            $data['custom'] = [
                '.background_img' => [
                    'background-image' => "linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(" . ((!empty($season)) ? base_url("images/{$season}.jpg") : '') . ")!important"
                ]
            ];
        }



        if (defined('LOGIN_COLOR') && !empty(LOGIN_COLOR)) {
            $data['custom'] = array_merge([
                '.login-page, .register-page' => [
                    'background' => LOGIN_COLOR
                ]
            ], $data['custom']);
        }
        if (defined('LOGIN_TITLE_COLOR') && !empty(LOGIN_TITLE_COLOR)) {
            $data['custom'] = array_merge([
                '.logo h2' => [
                    'color' => LOGIN_TITLE_COLOR
                ]
            ], $data['custom']);
        }
        $this->layout->addDinamicStylesheet($data, "login.css");
        ?>


    <style>
    /* Media query per login box width responsive */
    @media (max-width: 768px) {

        .login-box-security {
            width: 90% !important;
            margin-top: 20px;
        }
    }

    .login-box-security {
        width: 550px;
    }

    .login_container {
        /*min-width: 450px;*/
        background: #ffffff;
        padding: 20px 30px;
        border-radius: 3px;
    }


    .login_logo {
        width: 100%;
        height: 45px;
        display: flex;
        justify-content: center;
    }

    .login_logo i {
        color: #3c8dbc;
        font-size: 36px;
    }

    .login_content .login_heading {
        font-weight: 600;
        font-size: 2.2rem;
        color: #000000;
    }

    .login_content .login_text {
        font-size: 1.5rem;
        color: #000000;
    }

    .login_actions {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        margin-top: 30px
    }

    .login_actions .main_actions {
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px
    }

    @media (max-width: 768px) {
        .login_actions .main_actions {
            width: 100%;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 20px;
            flex-direction: column;
        }

        .login_actions .main_actions input {
            width: 100% !important;
            margin-bottom: 15px;
        }
    }

    .login_actions .main_actions input {
        width: 45%;
    }

    .login_actions .main_actions .js_easylogin_ask {
        border: 0;
        background: #3c8dbc;
        color: #ffffff;
        font-size: 1.5rem;
        font-weight: 600;
        padding: 10px 15px;
        transition: all 0.25s ease-in;
    }

    .login_actions .main_actions .js_easylogin_ask:hover {
        background: #367fa9;
    }

    .login_actions .main_actions .js_easylogin_later {
        background: #ffffff;
        border: 1px solid #3c8dbc;
        color: #3c8dbc;
        font-size: 1.5rem;
        font-weight: 600;
        padding: 10px 15px;
        transition: all 0.25s ease-in;
    }

    .login_actions .main_actions .js_easylogin_later:hover {
        background: #3c8dbc;
        color: #ffffff;
    }

    .login_actions .last_action .js_easylogin_back {
        background: #ffffff;
        border: 0;
        color: #3c8dbc;
        font-weight: 600;
        padding: 5px 10px;
        transition: all 0.25s ease-in;
    }

    .login_actions .last_action .js_easylogin_back:hover {
        color: #367fa9;
    }

    .js_show_password {
        pointer-events: initial;
        cursor: pointer;
    }
    </style>


</head>

<body class="hold-transition login-page">
    <div class="background_img" style="overflow-y: scroll !important;">
        <div class="login-box main_login_box">
            <div class="logo" style="max-width:none!important;">
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
                        <input type="hidden" class="webauthn_enable" name="webauthn_enable" value="0" />

                        <input type="email" class="form-control" placeholder="<?php e('E-mail address'); ?>" name="users_users_email" />
                        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" placeholder="<?php e('Password'); ?>" name="users_users_password">
                        <span class="glyphicon glyphicon-eye-open form-control-feedback js_show_password"></span>
                    </div>

                    <div class="form-group">
                        <div class="controls">
                            <div id="msg_login" class="alert alert-danger hide"></div>
                        </div>
                    </div>

                    <div class="form-group disconnect">
                        <label class="control-label disconnect_label"><?php e('Disconnect after'); ?></label>
                        <select name="timeout" class="selectpicker">
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
                <?php if ($this->input->get('source') == 'openbuilder') : ?>
                <div class="box box-primary box-solid firegui-box">
                    <div class=" box-header with-border">
                        <h3 class="box-title"><?php e('Welcome to your client!'); ?></h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="remove" onclick="location.href='<?php echo base_url(); ?>';"><i class="fa fa-times"></i></button>
                        </div>
                    </div>

                    <div class="box-body">
                        <?php e('You can login with the same email and password you used to register in OpenBuilder.net. After that, you can create new users or change your password directly in your reserved area.'); ?>
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

        <!-- New login -->
        <div class="login-box login-box-security easylogin_box" style="display:none;">
            <div class="logo" style="max-width:none!important;">
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
                <div class="login_container">
                    <div class="login_logo">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <div class="login_content">
                        <p class="login_heading text-center">
                            <?php echo e('Hi,'); ?> <span class="js_easylogin_name"></span>!
                        </p>
                        <p class="login_text text-center">
                            <?php echo e('Depending on your device you will be able to login with your fingerprint, face recognition or PIN.'); ?>
                        </p>
                    </div>
                    <div class="login_actions">
                        <div class="main_actions">

                            <input type="button" class="js_easylogin_ask" value="<?php echo e('Proceed'); ?> " />
                        </div>
                        <div class="last_action">
                            <input type="button" class="js_easylogin_back" value="<?php echo e('Back to classic login page...'); ?>" />
                        </div>
                    </div>
                </div>
                <!--<input type="button" class="js_easylogin_later" value="Later..." />
                    <input type="button" class="js_easylogin_proceed" value="Proceed..." />
                    <br />
                    <input type="button" class="js_easylogin_never" value="Don't ask me again..." />-->
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
    <script src="<?php echo base_url("script/global/plugins/bootstrap-select/bootstrap-select.min.js?v={$this->config->item('version')}"); ?>"></script>



    <script src="<?php echo base_url("script/js/easylogin.js?v={$this->config->item('version')}"); ?>"></script>

    <script>
    $(function() {
        const password = $('[name="users_users_password"]');
        const handleShowPassword = $('.js_show_password');

        handleShowPassword.on("click", function() {
            if (password.attr('type') === 'password') {
                password.attr('type', 'text');
                $(this).removeClass('glyphicon-eye-open');
                $(this).addClass('glyphicon-eye-close');
            } else {
                password.attr('type', 'password');
                $(this).removeClass('glyphicon-eye-close');
                $(this).addClass('glyphicon-eye-open');
            }
        });
    })
    </script>
</body>

</html>
<?php } ?>