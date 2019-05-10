<?php
if (!function_exists('base_url_admin')) {
    require APPPATH . 'config/config.php';
}

$config = &get_config();
?>



<?php ob_start(); ?>
<!DOCTYPE html>

<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
    <meta charset="utf-8"/>
    <title>Database error</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <meta content="" name="description"/>
    <meta content="" name="author"/>
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/uniform/css/uniform.default.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css"/>
    <!-- END GLOBAL MANDATORY STYLES -->
    <!-- BEGIN PAGE LEVEL STYLES -->
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/pages/css/error.css" rel="stylesheet" type="text/css"/>
    <!-- END PAGE LEVEL STYLES -->
    <!-- BEGIN THEME STYLES -->
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/css/components-md.css" id="style_components" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/global/css/plugins-md.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/layout/css/layout.css" rel="stylesheet" type="text/css"/>
    <link id="style_color" href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/layout/css/themes/darkblue.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/layout/css/custom.css" rel="stylesheet" type="text/css"/>
    <!-- END THEME STYLES -->
    <link rel="shortcut icon" href="favicon.ico"/>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="page-md page-404-3">
<div class="page-inner">
    <img src="<?php echo $config['base_url']; ?>/template/crm-v2/assets/admin/pages/media/pages/earth.jpg" class="img-responsive" alt="">
</div>
<div class="container error-404">
    <h1><i class="fa fa-exclamation-triangle" style="font-size: inherit"></i></h1>
    <h2>Houston, abbiamo un problema.</h2>
    <h5><?php echo $heading; ?></h5>
    <p style="max-width: 60%;max-height: 600px;overflow: scroll">
        <?php echo $message; ?>
    </p>
    <p>
        <a href="<?php echo $config['base_url']; ?>">Ritorna al sito</a>
        <br>
    </p>
</div>

<!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>



<?php
$html = ob_get_contents();
ob_end_clean();
echo $html;

session_start();
$md5 = md5($message);
$minExp = 10;
$time = time();

$sentRecently = (isset($_SESSION['dberr'][$md5]) && $time - $_SESSION['dberr'][$md5] <= $minExp*60);


$header = 'From: '.DEFAULT_EMAIL_SENDER.' <'.DEFAULT_EMAIL_SYSTEM.'>'.PHP_EOL;
$header .= "MIME-Version: 1.0".PHP_EOL;
$header .= "Content-Type: text/html; charset=\"iso-8859-1\"".PHP_EOL;
$header .= "Content-Transfer-Encoding: 7bit".PHP_EOL;

$fullMessage = array($html);
$fullMessage[] = 'URL ' . current_url();

if($_POST) {
    $fullMessage[] = '<br/>$_POST <pre>' . print_r($_POST, true) . '</pre>';
}

if($_GET) {
    $fullMessage[] = '<br/>$_GET <pre>' . print_r($_GET, true) . '</pre>';
}

if(isset($this) && isset($this->session)) {
    $fullMessage[] = '<br/>Sessione <pre>' . print_r($this->session->all_userdata(), true) . '</pre>';
}

$fullMessage[] = '<br/>$_SERVER <pre>' . print_r($_SERVER, true) . '</pre>';

log_error_slack(implode('<br/>', $fullMessage));
if(!$sentRecently && $_SERVER['REMOTE_ADDR'] !== 'XXXXXXXXXX' && gethostname() !== 'idra') {
    if (mail('debug@h2web.it', 'Errore database CRM', implode('<br/>', $fullMessage), $header)) {
        $_SESSION['dberr'][$md5] = $time;
    }
}?>
