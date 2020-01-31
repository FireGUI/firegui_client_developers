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
//$permissions['eval'] = @eval("echo 1;");

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



    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:300,400,700);

        body {
            margin: 0;
            font-size: 16px;
            font-family: 'Lato', sans-serif;
            text-align: center;
            color: #999;
        }

        .wrapper {
            width: 100%;
            padding: 0 20%;
            /*margin: 50px auto;*/
        }

        .logo {
            display: block;
            text-decoration: none;
            margin-bottom: 20px;
        }

        .logo img {
            margin-right: 1.25em;
        }

        p {
            margin: 0 0 5px;
        }

        p small {
            font-size: 13px;
            display: block;
            margin-bottom: 1em;
        }

        p.obs {
            margin-top: 20px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #31708f;
            background-color: #d9edf7;
            border-color: #bce8f1;
        }

        .icon-ok {
            color: #27ae60;
        }

        .icon-remove {
            color: #c0392b;
        }

        /*aggiunti da Andrea*/
        table {
            margin-bottom: 100px !important;
        }

        h2,
        .table_heading {
            color: #005977;
        }

        img {
            max-width: 35% !important;
            margin: 0 auto;
        }
    </style>
</head>



<body>
    <div class="wrapper">

        <img src="https://builder.firegui.com/images/logo_login.png" class="img-responsive" alt=""></img>
        <!--<h1>LOGO FIREGUI</h1>-->


        <h2>Check PHP Extensions</h1>

            <table class="table">
                <tr>
                    <td class="table_heading col-md-4"><b>Extensions</b></td>
                    <td class="table_heading col-md-4"><b>Result</b></td>
                    <td class="table_heading col-md-4"><b>Note</b></td>
                </tr>
                <tr>
                    <td>PHP </td>
                    <td><?php echo " " . ($requirements['php_version'] ? $res_true : $res_false); ?></td>
                    <td><?php echo " " . ($requirements['php_version'] ? '' : 'Your PHP versions is: ' . PHP_VERSION . ', we need at least PHP ' . $php_min_version); ?></td>
                </tr>
                <tr>
                    <td>Path folder writable </td>
                    <td><?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></td>
                    <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
                </tr>
                <tr>
                    <td>Mysqli PHP Extension </td>
                    <td><?php echo $requirements['mysqli'] ? $res_true : $res_false; ?></td>
                    <td><?php echo $requirements['mysqli'] ? '' : 'Mysqli is required!'; ?></td>
                </tr>
                <tr>
                    <td>OpenSSL PHP Extension </td>
                    <td><?php echo $requirements['openssl_enabled'] ? $res_true : $res_false; ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td>PDO PHP Extension </td>
                    <td><?php echo $requirements['pdo_enabled'] ? $res_true : $res_false; ?> </td>
                    <td></td>
                </tr>
                <tr>
                    <td>Mbstring PHP Extension </td>
                    <td><?php echo $requirements['mbstring_enabled'] ? $res_true : $res_false; ?> </td>
                    <td></td>
                </tr>
                <tr>
                    <td>XML PHP Extension </td>
                    <td> <?php echo $requirements['xml_enabled'] ? $res_true : $res_false; ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td>CTYPE PHP Extension </td>
                    <td><?php echo $requirements['ctype_enabled'] ? $res_true : $res_false; ?> </td>
                    <td></td>
                </tr>
                <tr>
                    <td>JSON PHP Extension </td>
                    <td><?php echo $requirements['json_enabled'] ? $res_true : $res_false; ?> </td>
                    <td></td>
                </tr>
                <tr>
                    <td>Mcrypt PHP Extension </td>
                    <td><?php echo $requirements['mcrypt_enabled'] ? $res_true : $res_false; ?> </td>
                    <td><?php echo $requirements['mcrypt_enabled'] ? '' : 'Suggested but not required'; ?></td>
                </tr>
                <tr>
                    <td>ImageMagick PHP Extension </td>
                    <td><?php echo $requirements['imagick'] ? $res_true : $res_false; ?></td>
                    <td><?php echo $requirements['imagick'] ? '' : 'Suggested but not required'; ?></td>
                </tr>
                <tr>
                    <td>Curl PHP Extension </td>
                    <td><?php echo $requirements['curl'] ? $res_true : $res_false; ?> </td>
                    <td><?php echo $requirements['curl'] ? '' : 'Not required, but highly recommended.'; ?></td>
                </tr>
                <tr>
                    <td>Zip PHP Extension </td>
                    <td><?php echo $requirements['zip'] ? $res_true : $res_false; ?> </td>
                    <td><?php echo $requirements['zip'] ? '' : 'Not required, but highly recommended.'; ?></td>
                </tr>
                <tr>
                    <td>GD PHP Extension </td>
                    <td><?php echo $requirements['gd'] ? $res_true : $res_false; ?> </td>
                    <td><?php echo $requirements['gd'] ? '' : 'Not required, but highly recommended.'; ?></td>
                </tr>
            </table>


            <!--<p>PHP <?php echo " " . ($requirements['php_version'] ? $res_true : $res_false); ?> (<?php echo PHP_VERSION; ?>)</p>
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
           <p>GD PHP Extension <?php echo $requirements['gd'] ? $res_true : $res_false; ?></p>-->



            <h2>Check Configurations</h1>
                <!--<table class="table">
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
                <p>post_max_size: <?php echo ini_get('post_max_size') ? $res_true : $res_false; ?> (value: <?php echo ini_get('post_max_size') ?>)</p>-->

                <table class="table">
                    <tr>
                        <td class="table_heading col-md-4"><b>Configuration</b></td>
                        <td class="table_heading col-md-4"><b>Result</b></td>
                        <td class="table_heading col-md-4"><b>Note</b></td>
                    </tr>
                    <tr>
                        <td>max_input_vars </td>
                        <td><?php echo ini_get('max_input_vars'); ?></td>
                        <td><?php echo (ini_get('max_input_vars') < 1200) ? 'We suggest you set this value to at least 1200' : ''; ?></td>
                    </tr>
                    <!--<tr>
                        <td>magic_quotes_gpc: </td>
                        <td><?php echo !ini_get('magic_quotes_gpc') ? $res_true : $res_false; ?> (value: <?php echo ini_get('magic_quotes_gpc') ?>)</td>
                        <td><?php echo $permissions['is_writable'] ? '' : 'Your path must be writable!'; ?></td>
                    </tr>
                    <tr>
                        <td>register_globals: </td>
                        <td><?php echo !ini_get('register_globals') ? $res_true : $res_false; ?> (value: <?php echo ini_get('register_globals') ?>)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>session.auto_start: </td>
                        <td><?php echo !ini_get('session.auto_start') ? $res_true : $res_false; ?> (value: <?php echo ini_get('session.auto_start') ?>)</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>mbstring.func_overload: </td>
                        <td><?php echo !ini_get('mbstring.func_overload') ? $res_true : $res_false; ?> (value: <?php echo ini_get('mbstring.func_overload') ?>) </td>
                        <td></td>
                    </tr>-->

                    <tr>
                        <td>upload_max_filesize: </td>
                        <td><?php echo ini_get('upload_max_filesize') ? $res_true : $res_false; ?> (value: <?php echo ini_get('upload_max_filesize') ?>)</td>
                        <td><?php echo (ini_get('upload_max_filesize') < 64) ? 'We suggest you set this value to at least 64' : ''; ?></td>
                    </tr>
                    <tr>
                        <td>post_max_size: </td>
                        <td><?php echo ini_get('post_max_size') ? $res_true : $res_false; ?> (value: <?php echo ini_get('post_max_size') ?>) </td>
                        <td><?php echo (ini_get('post_max_size') < 64) ? 'We suggest you set this value to at least 64' : ''; ?></td>
                    </tr>
                </table>





                <h2>Check Permissions</h1>

                    <table class="table">
                        <tr>
                            <td class="table_heading col-md-4"><b>Permission</b></td>
                            <td class="table_heading col-md-4"><b>Result</b></td>
                            <td class="table_heading col-md-4"><b>Note</b></td>
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
                        <tr>
                            <td>mkdir() function </td>
                            <td><?php echo $permissions['mkdir'] ? $res_true : $res_false; ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>rmdir() function </td>
                            <td><?php echo $permissions['rmdir'] ? $res_true : $res_false; ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>fopen(write) function </td>
                            <td><?php echo $permissions['fopen'] ? $res_true : $res_false; ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>unlink function </td>
                            <td><?php echo $permissions['unlink'] ? $res_true : $res_false; ?></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>eval function </td>
                            <td><?php echo check_disabled_function('eval') ? $res_true : $res_false; ?></td>
                            <td><?php echo check_disabled_function('eval') ? '' : 'Eval function is required!'; ?></td>
                        </tr>
                    </table>
                    <br />
                    <!--OK <p>Script path <?php echo $permissions['localpath']; ?></p>
                    OK <p>Path folder writable <?php echo $permissions['is_writable'] ? $res_true : $res_false; ?></p>
                    OK<p>mkdir() function <?php echo $permissions['mkdir'] ? $res_true : $res_false; ?></p>
                    OK<p>rmdir() function <?php echo $permissions['rmdir'] ? $res_true : $res_false; ?></p>
                    OK<p>fopen(write) function <?php echo $permissions['fopen'] ? $res_true : $res_false; ?></p>
                    OK<p>unlink function <?php echo $permissions['unlink'] ? $res_true : $res_false; ?></p>
                    OK<p>eval function <?php echo check_disabled_function('eval') ? $res_true : $res_false; ?></p>

                    <p>eval function <?php echo check_disabled_function('eval') ? $res_true : $res_false; ?></p>-->


    </div>
</body>

</html>