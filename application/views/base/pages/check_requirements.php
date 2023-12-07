<?php

/**
 * OpenBuilder Requirement Checker
 */

$client_version = '1.8.7';
$php_min_version = '7.0';

$res_true = '<i class="fa fa-check text-success"></i>';
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
// php-Mcrypt
$requirements['mcrypt_enabled'] = extension_loaded("mcrypt_encrypt");
// php-ImageMagick
$requirements['php-imagick'] = extension_loaded("imagick");
// php-Curl
$requirements['php-curl'] = extension_loaded("curl");
// php-Zip
$requirements['php-zip'] = extension_loaded("zip");
// php-GD2
$requirements['php-gd'] = extension_loaded("gd");
// // zip
// $requirements['zip'] = checkShellCommand('zip');
// // unzip
// $requirements['unzip'] = checkShellCommand('unzip');
// // curl
// $requirements['curl'] = checkShellCommand('curl --help');


// function checkShellCommand($command)
// {
//     $returnValue = shell_exec("$command");
//     if (empty($returnValue)) {
//         return false;
//     } else {
//         return true;
//     }
// }

// mod_rewrite
/*$requirements['mod_rewrite_enabled'] = null;
if (function_exists('apache_get_modules')) {
    $requirements['mod_rewrite_enabled'] = in_array('mod_rewrite', apache_get_modules());
}*/

$time = time();
$permissions['localpath'] = dirname(__FILE__);
$permissions['mkdir'] = @mkdir('tmp_'.$time);
$permissions['rmdir'] = @rmdir('tmp_'.$time);
$permissions['is_writable'] = @is_writable($permissions['localpath']);
$permissions['fopen'] = @fopen("test_file".$time.".txt", "w");
$permissions['unlink'] = @unlink("test_file".$time.".txt");
//$permissions['eval'] = @eval("echo 1;");

function check_disabled_function($function) {
    $disabled = explode(',', ini_get('disable_functions'));
    return !in_array($function, $disabled);
}

// function base_url_template($uri) {
//     echo '../template/adminlte/bower_components/'.$uri;
// }

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="favicon.ico">

    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>OpenBuilder Client Installer</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?php echo base_url_template('bootstrap/dist') ?>/css/bootstrap.min.css">

    <!-- Google Font -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">

    <!-- FontAwesome 5 -->
    <link rel="stylesheet" href="<?php echo base_url_template('@fortawesome/fontawesome-free') ?>/css/all.min.css">

    <link rel="stylesheet" href="<?php echo base_url_template('smart-wizard/dist') ?>/css/smart_wizard.min.css">

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="<?php echo base_url_template('jquery/dist') ?>/jquery.min.js"></script>

    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="<?php echo base_url_template('bootstrap/dist') ?>/js/bootstrap.min.js"></script>

    <!-- FontAwesome 5 -->
    <script src="<?php echo base_url_template('@fortawesome/fontawesome-free') ?>/js/all.min.js"></script>

    <script src="<?php echo base_url_template('smart-wizard/dist') ?>/js/jquery.smartWizard.min.js"></script>

    <style>
        a:hover,
        a:visited,
        a:focus {
            text-decoration: none !important;
        }

        a.collapsed {
            font-weight: bold;
        }

        @import url(//fonts.googleapis.com/css?family=Lato:300,400,700);

        body {
            margin: 0;
            font-size: 16px;
            font-family: 'Lato', sans-serif;
            text-align: center;
            color: #999;
            margin-bottom: 5rem;
            background-color: #f7f7f7;
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

        h2,
        .table_heading {
            color: #005977;
        }

        img {
            max-width: 35% !important;
            margin: 0 auto;
        }

        .sw-theme-default>ul.step-anchor>li.active>a {
            color: #8ab4f8 !important;
        }

        .sw-theme-default>ul.step-anchor>li>a,
        .sw-theme-default>ul.step-anchor>li>a:hover {
            color: white !important;
        }

        .sw-theme-default>ul.step-anchor>li.done>a {
            color: #6ebf6e !important;
        }
    </style>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#smartwizard').smartWizard({
                useURLhash: false,
                showStepURLhash: false,
                keyNavigation: false,
                toolbarSettings: {
                    toolbarPosition: 'none',
                },
                anchorSettings: {
                    anchorClickable: false,
                    enableAllAnchors: false,
                    markDoneStep: true,
                    enableAnchorOnDoneStep: false
                },
            });

            $(".prev-btn").on("click", function () {
                $('#smartwizard').smartWizard("prev");
                return true;
            });

            $(".next-btn").on("click", function () {
                $('#smartwizard').smartWizard("next");
                return true;
            });
        });
    </script>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-sm-12" style="margin-top:10px;">
                <img src="https://my.openbuilder.net/images/logo_dark.png" class="img-responsive" alt="">
            </div>

        </div>
        <div class="row">
            <div class="col-sm-12">
                <div id="smartwizard" style="border-top:3px solid #005977 ;border-radius:3px;">

                    <div>
                        <div id="check-requirements" class="">
                            <?php
                            $error_level = 0;
                            $error_req = [];

                            foreach($requirements as $key => $req) {
                                if(!in_array($key, ['mcrypt_enabled', 'php-imagick'])) {
                                    if(!$req) {
                                        $error_req[] = $key;
                                        $error_level += 1;
                                    }
                                }
                            }

                            foreach($permissions as $key => $permission) {
                                if(!$permission) {
                                    $error_req[] = $key;
                                    $error_level += 1;
                                }
                            }


                            ?>




                            <hr />

                            <?php if($error_level > 0): ?>
                                <div class="alert alert-danger">
                                    Please resolve all requirements before continue<br />
                                    <?php foreach($error_req as $error)
                                        echo '- ', $error, "<br>"; ?>
                                </div>
                            <?php endif; ?>

                            <div class="page-header">
                                <h2>PHP Extensions</h2>
                            </div>

                            <table class="table">
                                <tr>
                                    <td class="table_heading col-md-4"><b>Extensions</b></td>
                                    <td class="table_heading col-md-4"><b>Result</b></td>
                                    <td class="table_heading col-md-4"><b>Note</b></td>
                                </tr>
                                <tr>
                                    <td>PHP</td>
                                    <td>
                                        <?php echo " ".(isset($requirements['php_version']) ? $res_true : $res_false); ?>
                                    </td>
                                    <td>
                                        <?php echo " ".($requirements['php_version'] ? 'Your PHP versions is: '.PHP_VERSION : 'Your PHP versions is: '.PHP_VERSION.', we need at least PHP '.$php_min_version); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Path folder writable</td>
                                    <td>
                                        <?php echo (isset($permissions['is_writable']) && $permissions['is_writable']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($permissions['is_writable']) && $permissions['is_writable']) ? '' : 'Your path must be writable!'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Mysqli PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['mysqli']) && $requirements['mysqli']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['mysqli']) && $requirements['mysqli']) ? '' : 'Mysqli is required!'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>OpenSSL PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['openssl_enabled']) && $requirements['openssl_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>PDO PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['pdo_enabled']) && $requirements['pdo_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Mbstring PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['mbstring_enabled']) && $requirements['mbstring_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>XML PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['xml_enabled']) && $requirements['xml_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>CTYPE PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['ctype_enabled']) && $requirements['ctype_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>JSON PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['json_enabled']) && $requirements['json_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Mcrypt PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['mcrypt_enabled']) && $requirements['mcrypt_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['mcrypt_enabled']) && $requirements['mcrypt_enabled']) ? '' : 'Suggested but not required'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>ImageMagick PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['imagick']) && $requirements['imagick']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['php-imagick']) && $requirements['php-imagick']) ? '' : 'Suggested but not required'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Curl PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['php-curl']) && $requirements['php-curl']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['php-curl']) && $requirements['php-curl']) ? '' : 'Not required, but highly recommended.'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Zip PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['php-zip']) && $requirements['php-zip']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['php-zip']) && $requirements['php-zip']) ? '' : 'Php-zip extension is required'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>GD PHP Extension</td>
                                    <td>
                                        <?php echo (isset($requirements['php-gd']) && $requirements['php-gd']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['php-gd']) && $requirements['php-gd']) ? '' : 'Not required, but highly recommended.'; ?>
                                    </td>
                                </tr>
                            </table>

                            <div class="page-header">
                                <h2>PHP Configuration</h2>
                            </div>

                            <table class="table">
                                <tr>
                                    <td class="table_heading col-md-4"><b>Configuration</b></td>
                                    <td class="table_heading col-md-4"><b>Result</b></td>
                                    <td class="table_heading col-md-4"><b>Note</b></td>
                                </tr>
                                <tr>
                                    <td>max_input_vars</td>
                                    <td>
                                        <?php echo ini_get('max_input_vars'); ?>
                                    </td>
                                    <td>
                                        <?php echo (ini_get('max_input_vars') < 1200) ? 'We suggest you set this value to at least 1200' : ''; ?>
                                    </td>
                                </tr>
                                <?php /*
<tr>
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
</tr>
*/?>
                                <tr>
                                    <td>mod_rewrite</td>
                                    <td>
                                        <?php echo (isset($requirements['mod_rewrite_enabled']) && $requirements['mod_rewrite_enabled']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($requirements['mod_rewrite_enabled']) && $requirements['mod_rewrite_enabled']) ? $res_true : 'Extremely suggested if you run Apache/HTTPD'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>upload_max_filesize:</td>
                                    <td>
                                        <?php echo ini_get('upload_max_filesize') ? $res_true : $res_false; ?>
                                        (value:
                                        <?php echo ini_get('upload_max_filesize') ?>)
                                    </td>
                                    <td>
                                        <?php echo (ini_get('upload_max_filesize') < 64) ? 'We suggest you set this value to at least 64' : ''; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>post_max_size:</td>
                                    <td>
                                        <?php echo ini_get('post_max_size') ? $res_true : $res_false; ?>
                                        (value:
                                        <?php echo ini_get('post_max_size') ?>)
                                    </td>
                                    <td>
                                        <?php echo (ini_get('post_max_size') < 64) ? 'We suggest you set this value to at least 64' : ''; ?>
                                    </td>
                                </tr>
                            </table>

                            <div class="page-header">
                                <h2>PHP Permissions</h2>
                            </div>

                            <table class="table">
                                <tr>
                                    <td class="table_heading col-md-4"><b>Permission</b></td>
                                    <td class="table_heading col-md-4"><b>Result</b></td>
                                    <td class="table_heading col-md-4"><b>Note</b></td>
                                </tr>
                                <tr>
                                    <td>Script path</td>
                                    <td>
                                        <?php echo $permissions['localpath']; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>Path folder writable</td>
                                    <td>
                                        <?php echo (isset($permissions['is_writable']) && $permissions['is_writable']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo (isset($permissions['is_writable']) && $permissions['is_writable']) ? '' : 'Your path must be writable!'; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>mkdir() function</td>
                                    <td>
                                        <?php echo (isset($permissions['mkdir']) && $permissions['mkdir']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>rmdir() function</td>
                                    <td>
                                        <?php echo (isset($permissions['rmdir']) && $permissions['rmdir']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>fopen(write) function</td>
                                    <td>
                                        <?php echo (isset($permissions['fopen']) && $permissions['fopen']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>unlink function</td>
                                    <td>
                                        <?php echo (isset($permissions['unlink']) && $permissions['unlink']) ? $res_true : $res_false; ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td>eval function</td>
                                    <td>
                                        <?php echo check_disabled_function('eval') ? $res_true : $res_false; ?>
                                    </td>
                                    <td>
                                        <?php echo check_disabled_function('eval') ? '' : 'Eval function is required!'; ?>
                                    </td>
                                </tr>
                            </table>


                            <hr />


                        </div>



                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>