<?php
if (file_exists(VIEWPATH . 'custom/layout/login.php')) {
    $this->load->view('custom/layout/login');

    exit;
}

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
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

    <title><?php e('Login'); ?></title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo base_url("assets/plugins/fontawesome-free/css/all.min.css"); ?>">

    <!-- Theme style -->
    <link rel="stylesheet" href="<?php echo base_url("assets/plugins/core/css/adminlte.min.css"); ?>">

    <!-- Bootstrap Select 1.14  -->
    <link rel="stylesheet" href="<?php echo base_url("assets/plugins/bootstrap-select/css/bootstrap-select.min.css"); ?>">

    <?php
    $this->layout->addDinamicJavascript([
        //"var base_url = '" . base_url() . "';",
        //"var base_url_admin = '" . base_url_admin() . "';",
        //"var base_url_template = '" . base_url_template() . "';",
        //"var base_url_scripts = '" . base_url_scripts() . "';",
        //"var base_url_uploads = '" . base_url_uploads() . "';",
        "var lang_code = '" . ((!empty($lang['languages_code'])) ? $lang['languages_code'] : 'en-EN') . "';",
        "var lang_short_code = '" . ((!empty($lang['languages_code'])) ? (explode('-', $lang['languages_code'])[0]) : 'en') . "';",
    ], 'config.js');

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
            ],
            '.hide' => [
                'display' => 'none'
            ]
        ], $data['custom']);

        $this->layout->addDinamicStylesheet($data, "login.css");
    }
    ?>
</head>

<body class="hold-transition login-page background_img" style=" background-repeat: no-repeat; background-size: cover;">
    <div class="login-box">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header text-center">
                <?php if ($this->settings['settings_company_logo']) : ?>
                    <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" class="logo" />
                <?php else : ?>
                    <a href="<?php echo base_url() ?>" class="h1"><?php echo $this->settings['settings_company_short_name'] ?? t('La tua azienda'); ?></a>
                <?php endif; ?>
            </div>

            <div class="card-body login-card-body rounded-lg">
                <p class="login-box-msg"><?php e('Enter in your profile'); ?></p>

                <form method="POST" action="<?php echo base_url('access/login_start'); ?>" class="formAjax" id="login">
                    <?php add_csrf(); ?>

                    <div class="input-group mb-3">
                        <input type="hidden" class="webauthn_enable" name="webauthn_enable" value="0" />

                        <input type="email" class="form-control" placeholder="<?php e('E-mail address'); ?>" name="users_users_email">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-envelope"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" placeholder="<?php e('Password'); ?>" name="users_users_password">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div id="msg_login" class="alert alert-danger mb-4 hide"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="control-label disconnect_label"><?php e('Disconnect after'); ?></label>
                            <select name="timeout" class="form-control form-control-sm" data-width="50%" data-size="5">
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
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block my-2"><?php e('Login'); ?></button>
                        </div>
                    </div>
                </form>

                <div class="mt-3">
                    <div><?php e('Forgot your password?'); ?></div>
                    <div><a href="<?php echo base_url("access/recovery"); ?>"><?php e('Click here'); ?></a> <?php e('to reset it.'); ?></div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.login-box -->

    <!-- jQuery -->
    <script src="<?php echo base_url("assets/plugins/jquery/jquery.min.js"); ?>"></script>

    <!-- Bootstrap 4 -->
    <script src="<?php echo base_url("assets/plugins/core/js/bootstrap.bundle.min.js"); ?>"></script>

    <!-- AdminLTE App -->
    <script src="<?php echo base_url("assets/plugins/core/js/adminlte.min.js"); ?>"></script>

    <!-- Bootstrap Select 1.14 -->
    <script src="<?php echo base_url("assets/plugins/bootstrap-select/js/bootstrap-select.min.js"); ?>"></script>

    <!-- Custom Components -->
    <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>

    <!-- Easylogin -->
    <script src="<?php echo base_url("assets/js/core/easylogin.js?v={$this->config->item('version')}"); ?>"></script>
</body>

</html>