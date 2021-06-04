<?php
if (file_exists(VIEWPATH . 'custom/layout/easylogin.php')) {
    $this->load->view('custom/layout/easylogin');
} else {
    $image = rand(1, 6) . '.jpeg';


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
        <link rel="stylesheet" href="<?php echo base_url("script/global/plugins/bootstrap-select/bootstrap-select.min.css?v={$this->config->item('version')}"); ?>">

        <?php $this->layout->addDinamicJavascript([
            "var base_url = '" . base_url() . "';",
            "var base_url_admin = '" . base_url_admin() . "';",
            "var base_url_template = '" . base_url_template() . "';",
            "var base_url_scripts = '" . base_url_scripts() . "';",
            "var base_url_uploads = '" . base_url_uploads() . "';",
            "var lang_code = '" . ((!empty($lang['languages_code'])) ? $lang['languages_code'] : 'en-EN') . "';",
            "var lang_short_code = '" . ((!empty($lang['languages_code'])) ? (explode('-', $lang['languages_code'])[0]) : 'en') . "';",
        ], 'config.js'); ?>

        <?php
        $data['custom'] = [
            '.background_img' => [
                'background-image' => "linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(" . base_url("images/easylogin/{$image}") . ")!important"
            ]
        ];

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
                    <input type="button" class="js_easylogin_later" value="Later..." />
                    <input type="button" class="js_easylogin_proceed" value="Proceed..." />
                    <br />
                    <input type="button" class="js_easylogin_never" value="Don't ask me again..." />
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
    </body>

    </html>
<?php } ?>