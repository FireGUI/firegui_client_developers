<?php
    
    /**
     * FireGUI Requirement Checker
     */
    
    $client_version = '1.8.5';
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
    
    function check_disabled_function($function) {
        $disabled = explode(',', ini_get('disable_functions'));
        return !in_array($function, $disabled);
    }
    
    function base_url_template($uri) {
        echo '../template/adminlte/bower_components/' . $uri;
    }

?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="favicon.ico">

        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>FireGUI Client Installer</title>

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
                /*margin-bottom: 100px !important;*/
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
        
        <script type="text/javascript">
            $(document).ready(function () {
                $('#smartwizard').smartWizard({
                    useURLhash: false,
                    keyNavigation: false,
                    toolbarSettings: {
                        toolbarPosition: 'none',
                    },
                    anchorSettings: {
                        anchorClickable: false, // Enable/Disable anchor navigation
                        enableAllAnchors: false, // Activates all anchors clickable all times
                        markDoneStep: true, // add done css
                        enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
                    },
                });

                $("#prev-btn").on("click", function () {
                    // Navigate previous
                    $('#smartwizard').smartWizard("prev");
                    return true;
                });

                $("#next-btn").on("click", function () {
                    // Navigate next
                    $('#smartwizard').smartWizard("next");
                    return true;
                });
            });
        </script>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <img src="https://builder.firegui.com/images/logo_login.png" class="img-responsive" alt="">
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div id="smartwizard">
                        <ul>
                            <li><a href="#check-requirements">Check<br/>Requirements</a></li>
                            <li><a href="#db-setup">Setup<br/>Database</a></li>
                            <li><a href="#done">Done<br/><small>Install complete</small></a></li>
                        </ul>

                        <div>
                            <div id="check-requirements" class="">
                                <?php
                                    $error_level = 0;
                                    $error_req = [];
        
                                    foreach($requirements as $key => $req)
                                    {
                                        if (!in_array($key, ['mcrypt_enabled', 'imagick'])) {
                                            if(!$req){
                                                $error_req[] = $key;
                                                $error_level += 1;
                                            }
                                        }
                                    }
                                    
                                    foreach ($permissions as $key => $permission) {
                                        if (!$permission) {
                                            $error_req[] = $key;
                                            $error_level += 1;
                                        }
                                    }
                                    
                                    if ($error_level == 0) :
                                ?>
                                
                                <div class="clearfix">
                                    <button class="btn btn-success pull-right" id="next-btn" type="button">Next <i
                                                class="fas fa-chevron-right"></i></button>
                                </div>
                                
                                <?php endif; ?>

                                <hr/>
                                
                                <?php require_once './includes/1_check_requirements.php' ?>

                                <hr/>

                                <?php if($error_level == 0): ?>
                                <div class="clearfix">
                                    <button class="btn btn-success pull-right" id="next-btn" type="button">Next <i
                                                class="fas fa-chevron-right"></i></button>
                                </div>
                                <?php endif; ?>
                            </div>

                            <div id="db-setup" class="">
                                <div class="clearfix">
                                    <button class="btn btn-primary" id="prev-btn" type="button"><i
                                                class="fas fa-chevron-left"></i> Back
                                    </button>
                                    <button class="btn btn-success pull-right" id="next-btn" type="button">Next <i
                                                class="fas fa-chevron-right"></i></button>
                                </div>

                                <hr/>
                                
                                <?php require_once './includes/2_check_db_connection.php' ?>

                                <hr/>

                                <div class="clearfix">
                                    <button class="btn btn-primary" id="prev-btn" type="button"><i
                                                class="fas fa-chevron-left"></i> Back
                                    </button>
                                    <button class="btn btn-success pull-right" id="next-btn" type="button">Next <i
                                                class="fas fa-chevron-right"></i></button>
                                </div>
                            </div>

                            <div id="done">
                                <h2 class="text-center text-uppercase">Install complete.</h2>

                                <h3>Remember to <b class="text-center text-uppercase">delete</b> the
                                    <code>install</code> folder before using your <b>Fire</b>GUI Client</h3>

                                <h4 class="text-center">Click below button to continue</h4>

                                <a href="http://<?php echo $_SERVER['HTTP_HOST']; ?>"
                                   class="btn btn-success center-block">Open <b>Fire</b>GUI Client</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>