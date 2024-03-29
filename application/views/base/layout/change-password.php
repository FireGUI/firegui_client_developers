<?php
if ($this->datab->module_installed('user-extender')) {
    $user_manager = $this->apilib->searchFirst('users_manager_configurations');

    if(!empty($user_manager)){
        if($user_manager['users_manager_configurations_enable_password_validation'] == DB_BOOL_TRUE){
            $message_validation = $user_manager['users_manager_configurations_password_validation_message'];

        }
    }
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

    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte_custom/custom.css?v={$this->config->item('version')}"); ?>" />

    <?php
    $data['custom'] = [
        '.background_img' => [
            'background-image' => "linear-gradient(rgba(23, 23, 23, 0.3), rgba(18, 20, 23, 0.8)), url(" . ((!empty($season)) ? base_url("images/{$season}.jpg") : '') . ")!important"
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
    $this->layout->addDinamicStylesheet($data, "login.css");
    ?>
</head>

<body class="hold-transition login-page">
    <div class="background_img">
        <div class="login-box">
            <div class="logo">
                <div class="text-center">
                    <?php if ($this->settings === array()) : ?>
                        <h2 class="login-logo login-p"><?php e('Your Company'); ?></h2>
                    <?php elseif ($this->settings['settings_company_logo']) : ?>
                        <img src="<?php echo base_url_uploads("uploads/{$this->settings['settings_company_logo']}"); ?>" alt="logo" class="logo" />
                    <?php else : ?>
                        <h2 class="text-danger"><?php echo $this->settings['settings_company_short_name']; ?></h2>
                    <?php endif; ?>
                </div>
            </div>


            <div class="login-box-body">
                <?php /*if ($sent) : ?>
                    <h3 class="title login-p"><?php e("E-mail sent successfully"); ?></h3>
                    <div class="alert alert-success">
                        <p class="login-p"><?php e("We've sent you and email to"); ?> <strong><?php echo $receiver; ?></strong> <?php e("which contains the link for reset the password."); ?> </p>
                    </div>
                <?php elseif (!empty($pwd_resetted)) : ?>
                    <h3 class="title login-p"><?php e("Password resetted successfully"); ?></h3>
                    <div class="alert alert-success">
                        <p class="login-p"><?php e("Your new password has been sent to"); ?> <strong><?php echo $receiver; ?></strong></p>
                    </div>
                <?php else : */ ?>
                <form id="lost" class="forget-form rounded formAjax" action="<?php echo base_url('access/change_expired_password'); ?>" method="post" novalidate="novalidate">
                    <?php add_csrf(); ?>
                    <h4 class="title login-p text-center"><?php e("Change password"); ?></h4>
                    <p class="login-p text-center"><?php e("Every six months you have to change your password."); ?></p>
                    <p style="color:white;" class="text-center"><?php e($message_validation); ?></p>

                    <input class="form-control" type="hidden" autocomplete="off" value="<?php echo $actual_password; ?>" name="users_users_current_password">

                    <div class="input_container password_container">
                        <input type="password" class="form-control password_input" placeholder="<?php e('New password'); ?>" name="users_users_password">
                    </div>
                    <br>
                    <div class="input_container password_container">
                        <input type="password" class="form-control password_input" placeholder="<?php e('Confirm password'); ?>" name="users_users_confirm_password">
                    </div>                        

                    <div class="form-group">
                        <div class="controls">
                            <div id="msg_lost" class="alert alert-danger hide"></div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <div class="row">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-primary rounded_btn">
                                    <?php e('Change password'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                <?php //endif; 
                ?>
            </div>
        </div>
    </div>

    <!-- COMMON PLUGINS -->
    <script src="<?php echo base_url_template("template/adminlte/bower_components/jquery/dist/jquery.min.js?v={$this->config->item('version')}"); ?>"></script>
    <script src="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js?v=" . $this->config->item('version')); ?>"></script>
    <script src="<?php echo base_url_template("template/adminlte/plugins/iCheck/icheck.min.js?v={$this->config->item('version')}"); ?>"></script>

    <!-- CUSTOM COMPONENTS -->
    <script type="text/javascript" src="<?php echo base_url_scripts("script/js/submitajax.js?v={$this->config->item('version')}"); ?>"></script>
</body>

</html>