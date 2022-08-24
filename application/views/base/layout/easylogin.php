<?php
if (file_exists(VIEWPATH . 'custom/layout/easylogin.php')) {
    $this->load->view('custom/layout/easylogin');
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
        $this->layout->addDinamicStylesheet($data, "easylogin.css", true);
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
                justify-content: space-between;
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

            .login_actions .main_actions .js_easylogin_proceed {
                border: 0;
                background: #3c8dbc;
                color: #ffffff;
                font-size: 1.5rem;
                font-weight: 600;
                padding: 10px 15px;
                transition: all 0.25s ease-in;
            }

            .login_actions .main_actions .js_easylogin_proceed:hover {
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

            .login_actions .last_action .js_easylogin_never {
                background: #ffffff;
                border: 0;
                color: #3c8dbc;
                font-weight: 600;
                padding: 5px 10px;
                transition: all 0.25s ease-in;
            }

            .login_actions .last_action .js_easylogin_never:hover {
                color: #367fa9;
            }
        </style>

    </head>

    <body class="hold-transition login-page" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>" data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>" data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>" data-base_url_builder="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>">

        <div class="background_img">
            <!-- New login -->
            <div class="login-box login-box-security js_easylogin_page" data-user="<?php echo base64_encode(json_encode([
                                                                                        'id' => $this->auth->get('id'),
                                                                                        'email' => $this->auth->get(LOGIN_USERNAME_FIELD),
                                                                                        'display_name' => $this->auth->get(LOGIN_NAME_FIELD),
                                                                                    ])); ?>">
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
                                <?php echo e('Are you tired of passwords?'); ?>
                            </p>
                            <p class="login_text text-center">
                                <?php echo e('Depending on your device you will be able to login with your fingerprint, face recognition or PIN.'); ?>
                            </p>
                        </div>
                        <div class="login_actions">
                            <div class="main_actions">
                                <input type="button" class="js_easylogin_later" value="<?php echo e('Later'); ?>" />
                                <input type="button" class="js_easylogin_proceed" value="<?php echo e('Proceed'); ?> " />
                            </div>
                            <div class="last_action">
                                <input type="button" class="js_easylogin_never" value="<?php echo e('Don\'t ask me again'); ?>" />
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
        <script src="<?php echo base_url_scripts("script/js/grep_config.js?v=" . VERSION); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
        <script src="<?php echo base_url_template("template/adminlte/plugins/iCheck/icheck.min.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- CUSTOM COMPONENTS -->
        <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>
        <!-- Bootstrap-select -->
        <script src="<?php echo base_url("script/global/plugins/bootstrap-select/bootstrap-select.min.js?v={$this->config->item('version')}"); ?>"></script>


        <script src="<?php echo base_url("script/js/easylogin.js?v={$this->config->item('version')}"); ?>"></script>
    </body>

    </html>
<?php } ?>