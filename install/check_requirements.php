<?php

/**
 * FireGUI Requirement Checker
 */

$client_version = '1.7.2.3';
$php_min_version = '7.0';

$res_true = '<i class="fa fa-check"></i>';
$res_false = '<i style="color: red" class="fa fa-times"></i>';
$strUnknown = '<i class="fa fa-question"></i>';

$requirements = array();
$requirements['php_version'] = version_compare(PHP_VERSION, $php_min_version, ">=");
// Mysql
$requirements['mysqli'] = extension_loaded("mysqli");
// OpenSSL PHP Extension
$requirements['openssl_enabled'] = extension_loaded("openssl");
// PDO PHP Extension
$requirements['pdo_enabled'] = defined('PDO::ATTR_DRIVER_NAME');
// Mbstring PHP Extension
$requirements['mbstring_enabled'] = extension_loaded("mbstring");
// XML PHP Extension
$requirements['xml_enabled'] = extension_loaded("xml");
// CTYPE PHP Extension
$requirements['ctype_enabled'] = extension_loaded("ctype");
// JSON PHP Extension
$requirements['json_enabled'] = extension_loaded("json");
// Mcrypt
$requirements['mcrypt_enabled'] = extension_loaded("mcrypt_encrypt");
// ImageMagick
$requirements['imagick'] = extension_loaded("imagick");
// Curl
$requirements['curl'] = extension_loaded("curl");
// Zip
$requirements['zip'] = extension_loaded("zip");
// GD2
$requirements['gd'] = extension_loaded("gd");

// mod_rewrite
$requirements['mod_rewrite_enabled'] = null;
if (function_exists('apache_get_modules')) {
    $requirements['mod_rewrite_enabled'] = in_array('mod_rewrite', apache_get_modules());
}

$time = time();
$permissions['localpath'] = dirname(__FILE__);
$permissions['mkdir'] = @mkdir('tmp_' . $time);
$permissions['rmdir'] = @rmdir('tmp_' . $time);
$permissions['is_writable'] = @is_writable($permissions['localpath']);
$permissions['fopen'] = @fopen("test_file" . $time . ".txt", "w");
$permissions['unlink'] = @unlink("test_file" . $time . ".txt");
$permissions['eval'] = @eval("echo 1;");

function check_disabled_function($function)
{
    $disabled = explode(',', ini_get('disable_functions'));
    return !in_array($function, $disabled);
}

function base_url_template($uri)
{
    echo '../' . $uri;
}

?>

<!doctype html>
<html lang="en">

<head>
    <!-- CORE LEVEL STYLES -->
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/font-awesome/css/font-awesome.min.css"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/bower_components/Ionicons/css/ionicons.min.css"); ?>" />

    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/dist/css/AdminLTE.min.css"); ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo base_url_template("template/adminlte/plugins/iCheck/square/blue.css"); ?>" />

    <link rel="shortcut icon" href="/favicon.ico" />

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
    <link href="//stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

</head>



<body>
    <div class="wrapper">

        <h1>LOGO FIREGUI</h1>


        <h2>Check PHP Extensions</h1>

            <table class="table">
                <tr>
                    <td>Extensions</td>
                    <td>Result</td>
                    <td>Note</td>
                </tr>
                <tr>
                    <td>PHP </td>
                    <td><?php echo " " . ($requirements['php_version'] ? $res_true : $res_false); ?></td>
                    <td><?php echo " " . ($requirements['php_version'] ? 'Ok!' : 'Your PHP versions is: ' . PHP_VERSION . ', we need at least PHP ' . $php_min_version); ?></td>
                </tr>
                <tr>
                    <td>Path folder writable </td>
                    <td><?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></td>
                    <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
                </tr>
            </table>
            <p>PHP <?php echo " " . ($requirements['php_version'] ? $res_true : $res_false); ?> (<?php echo PHP_VERSION; ?>)</p>
            <p>Mysqli PHP Extension <?php echo $requirements['mysqli'] ? $res_true : $res_false; ?></p>
            <p>OpenSSL PHP Extension <?php echo $requirements['openssl_enabled'] ? $res_true : $res_false; ?></p>
            <p>PDO PHP Extension <?php echo $requirements['pdo_enabled'] ? $res_true : $res_false; ?></p>
            <p>Mbstring PHP Extension <?php echo $requirements['mbstring_enabled'] ? $res_true : $res_false; ?></p>
            <p>XML PHP Extension <?php echo $requirements['xml_enabled'] ? $res_true : $res_false; ?></p>
            <p>CTYPE PHP Extension <?php echo $requirements['ctype_enabled'] ? $res_true : $res_false; ?></p>
            <p>JSON PHP Extension <?php echo $requirements['json_enabled'] ? $res_true : $res_false; ?></p>
            <p>Mcrypt PHP Extension <?php echo $requirements['mcrypt_enabled'] ? $res_true : $res_false; ?></p>
            <p>ImageMagick PHP Extension <?php echo $requirements['imagick'] ? $res_true : $res_false; ?></p>
            <p>Curl PHP Extension <?php echo $requirements['curl'] ? $res_true : $res_false; ?></p>
            <p>Zip PHP Extension <?php echo $requirements['zip'] ? $res_true : $res_false; ?></p>
            <p>GD PHP Extension <?php echo $requirements['gd'] ? $res_true : $res_false; ?></p>





            <h2>Check Configurations</h1>
                <table class="table">
                    <tr>
                        <td>Configuration</td>
                        <td>Result</td>
                        <td>Note</td>
                    </tr>
                    <tr>
                        <td>max_input_vars </td>
                        <td><?php echo ini_get('max_input_vars'); ?></td>
                        <td><?php echo (ini_get('max_input_vars') < 1200) ? 'We suggest you set this value to at least 1200' : 'Ok!'; ?></td>
                    </tr>
                    <tr>
                        <td>Path folder writable </td>
                        <td><?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></td>
                        <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
                    </tr>
                </table>
                <p>magic_quotes_gpc: <?php echo !ini_get('magic_quotes_gpc') ? $res_true : $res_false; ?> (value: <?php echo ini_get('magic_quotes_gpc') ?>)</p>
                <p>register_globals: <?php echo !ini_get('register_globals') ? $res_true : $res_false; ?> (value: <?php echo ini_get('register_globals') ?>)</p>
                <p>session.auto_start: <?php echo !ini_get('session.auto_start') ? $res_true : $res_false; ?> (value: <?php echo ini_get('session.auto_start') ?>)</p>
                <p>mbstring.func_overload: <?php echo !ini_get('mbstring.func_overload') ? $res_true : $res_false; ?> (value: <?php echo ini_get('mbstring.func_overload') ?>)</p>
                <p>max_input_vars: <?php echo (ini_get('max_input_vars') >= 1000) ? $res_true : $res_false; ?> (value: <?php echo ini_get('max_input_vars') ?>, min. 1000)</p>
                <p>upload_max_filesize: <?php echo ini_get('upload_max_filesize') ? $res_true : $res_false; ?> (value: <?php echo ini_get('upload_max_filesize') ?>)</p>
                <p>post_max_size: <?php echo ini_get('post_max_size') ? $res_true : $res_false; ?> (value: <?php echo ini_get('post_max_size') ?>)</p>







                <h2>Check Permissions</h1>

                    <table class="table">
                        <tr>
                            <td>Permission</td>
                            <td>Result</td>
                            <td>Note</td>
                        </tr>
                        <tr>
                            <td>Script path </td>
                            <td><?php echo $permissions['localpath']; ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Path folder writable </td>
                            <td><?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></td>
                            <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
                        </tr>
                    </table>
                    <br />
                    <p>Script path <?php echo $permissions['localpath']; ?></p>
                    <p>Path folder writable <?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></p>
                    <p>mkdir() function <?php echo $permissions['mkdir'] ? $res_true : $res_false; ?></p>
                    <p>rmdir() function <?php echo $permissions['rmdir'] ? $res_true : $res_false; ?></p>
                    <p>fopen(write) function <?php echo $permissions['fopen'] ? $res_true : $res_false; ?></p>
                    <p>unlink function <?php echo $permissions['unlink'] ? $res_true : $res_false; ?></p>
                    <p>eval function <?php echo check_disabled_function('eval') ? $res_true : $res_false; ?></p>

                    <p>eval function <?php echo check_disabled_function('eval') ? $res_true : $res_false; ?></p>


    </div>
</body>

</html>