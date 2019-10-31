<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Firegui extends MY_Controller
{

    function __construct()
    {
        parent::__construct();

        // TODO: INTEGRARE SISTEMA DI PROTEZIONE, SOLO FIREGUI DEVE POTER ESEGUIRE QUESTI METODI SE QUALCUNO SCOPRE
    }

    public function createModule($identifier)
    {

        //Creo le cartelle necessarie
        $folders = [
            'controllers', 'models', 'views', 'assets'
        ];

        $modules_path = APPPATH . 'modules/';

        if (!is_dir($modules_path)) //create the folder if it's not already exists
        {
            mkdir($modules_path, DIR_WRITE_MODE, true);
        }

        $prefix_folder = $modules_path . $identifier . '/';

        foreach ($folders as $folder) {
            if (!is_dir($prefix_folder . $folder)) //create the folder if it's not already exists
            {
                mkdir($prefix_folder . $folder, DIR_WRITE_MODE, true);
            }
        }
    }

    public function uninstallModule($identifier)
    {
        // TODO: Pericoloso esporre un metodo del genere
        unlink(APPPATH . "modules/$identifier/");
    }

    function updateFromGit($command = null, $output = true)
    {

        if ($command == null) {
            $command = "git pull https://github.com/FireGUI/firegui_client_developers.git master";
        }

        $result = array();
        exec($command, $result);

        if ($output == true) {
            foreach ($result as $line) {
                print($line . "\n");
            }
        }
    }

    public function downloadModuleFolder($identifier)
    {
        $module = $this->db->where('modules_identifier', $identifier)->get('modules')->row_array();

        if (!$module) {
            die('Unauthorized');
        } else {

            $folder = APPPATH . 'modules/' . $identifier . '/';

            $destination_file = FCPATH . 'uploads/' . $identifier . '.zip';

            if (!file_exists($folder)) {
                die('Can not create module, folder not found: ' . $destination_file);
            }


            $success = zip_folder($folder, $destination_file);

            if ($success === TRUE) { //NON TORNA FALSE!!!!!!
                if (file_exists($destination_file)) {
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $identifier . '.zip"');
                    header('Content-Length: ' . filesize($destination_file));
                    //die(filesize($destination_file));
                    //ob_end_clean();
                    readfile($destination_file);
                    //unlink($destination_file);
                } else {
                    die('Can not create  ' . $destination_file);
                }
            } else {
                die($success);
            }
        }
    }


    public function uploadModule($identifier)
    {
        $zip = new ZipArchive;

        if ($zip->open($_FILES['module_file']['tmp_name']) === TRUE) {
            $zip->extractTo(APPPATH . "modules/{$identifier}");
            $zip->close();
        } else {
            die('Wrong zip archive!');
        }

        //Una volta scompresso, eseguo l'eventuale file di install
        if (file_exists(APPPATH . "modules/{$identifier}/install/install.php")) {
            include(APPPATH . "modules/{$identifier}/install/install.php");
        }
    }

    public function updateClient($close = false)
    {
        /*$versionDataJson = $this->input->post('client');
        $version_data = @json_decode($versionDataJson);*/ //DISMESSO ORA PRENDO IL FILE DIRETTAMENTE
        //var_dump($version_data);

        if (!class_exists('ZipArchive')) {
            die("Missing ZipArchive class in client");
        }

        $old_version = VERSION;

        $file_link = FIREGUI_BUILDER_BASEURL . "public/client/getLastClientVersion/" . VERSION;
        $new_version = file_get_contents(FIREGUI_BUILDER_BASEURL . "public/client/getLastClientVersionNumber/" . VERSION);
        $newfile = './tmp_file.zip';
        if (!copy($file_link, $newfile)) {
            throw new Exception("Error while copying zip file.");
        } else {

            $zip = new ZipArchive();

            if ($zip->open($newfile) !== TRUE) {
                throw new Exception("Cannot open <$newfile>");
            } else {
                $temp_folder = FCPATH;
                @mkdir($temp_folder);
                $zip->extractTo($temp_folder);
                $zip->close();


                // Search update databases file for this version
                $files = scandir(FCPATH . 'application/migrations');

                foreach ($files as $file) {
                    if ($file == 'update_db.php') {
                        // Check if exist an update_db file to execute update queries
                        include(FCPATH . 'application/migrations/update_db.php');

                        // Sort array from oldest version to newest
                        uksort($updates, 'my_version_compare');

                        foreach ($updates as $key => $value) {

                            // Check if the version number is old or new
                            if ($key == $new_version) {
                                foreach ($value as $query) {
                                    $this->db->query($query);
                                }
                            }
                        }
                    } elseif ($file == 'update_php_code.php') {
                        // Check if exist an update_db file to execute update queries
                        include(FCPATH . 'application/migrations/update_php_code.php');

                        // Sort array from oldest version to newest
                        uksort($updates, 'my_version_compare');

                        foreach ($updates as $key => $value) {

                            // Check if the version number is old or new
                            if ($key == $new_version) {
                                foreach ($value as $key_type => $code) {
                                    if ($key_type == 'eval') {
                                        eval($code);
                                    } elseif ($key_type == 'include') { //201910070447 - Matteo Puppis - Added possibility to execute a custom code when updating client
                                        if (is_array($code)) {
                                            foreach ($code as $file_to_include) {
                                                $file_migration = FCPATH . 'application/migrations/' . $file_to_include;
                                                if (file_exists($file_migration)) {
                                                    include($file_migration);
                                                } else {
                                                    log_message('error', "Migration file {$file_migration} missing!");
                                                }
                                            }
                                        } else {
                                            $file_migration = FCPATH . 'application/migrations/' . $code;

                                            if (file_exists($file_migration)) {
                                                include($file_migration);
                                            } else {
                                                log_message('error', "Migration file {$file_migration} missing!");
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                unlink($newfile);
                if ($close) {
                    echo "Client updated! This page will be closed in 5 seconds...<script>setTimeout(function () {window.close();}, 5000);</script>";
                } else {
                    echo 'ok';
                }
            }
        }
    }
    // public function test_update_php_code($version)
    // {
    //     $files = scandir(FCPATH . 'application/migrations');


    //     foreach ($files as $file) {
    //         if ($file == 'update_php_code.php') {
    //             // Check if exist an update_db file to execute update queries
    //             include(FCPATH . 'application/migrations/update_php_code.php');

    //             if (array_key_exists($version, $updates)) {
    //                 //debug($updates[$version], true);
    //                 foreach ($updates[$version] as $key_type => $code) {
    //                     if ($key_type == 'eval') {
    //                         eval($code);
    //                     } elseif ($key_type == 'include') { //201910070447 - Matteo Puppis - Added possibility to execute a custom code when updating client
    //                         //debug($code, true);
    //                         include(FCPATH . 'application/migrations/' . $code);
    //                     }
    //                 }
    //             }
    //         }
    //     }
    // }
    public function get_client_version()
    {
        $check = $this->db->where('meta_data_key', 'db_version')->get('meta_data')->row_array();
        if ($check) {
            echo VERSION;
        } else {
            echo VERSION;
        }
    }

    public function executeMigrations($module_identifier, $old_version, $new_version)
    {
        $migration_dir = APPPATH . "modules/$module_identifier/migrations";
        if (file_exists($migration_dir)) {
            $files = scandir($migration_dir);

            foreach ($files as $file) {
                if ($file == 'update_php_code.php') {
                    // Check if exist an update_db file to execute update queries
                    include("$migration_dir/$file");

                    // Sort array from oldest version to newest
                    uksort($updates, 'my_version_compare');
                    //debug($updates, true);

                    foreach ($updates as $key => $value) {
                        $version_compare_old = version_compare($key, $old_version);
                        if ($version_compare_old) { //1 se old è < di key
                            foreach ($value as $key_type => $code) {
                                if ($key_type == 'eval') {
                                    eval($code);
                                } elseif ($key_type == 'include') {
                                    if (is_array($code)) {
                                        foreach ($code as $file_to_include) {
                                            $file_migration = "$migration_dir/$file_to_include";
                                            if (file_exists($file_migration)) {
                                                include($file_migration);
                                            } else {
                                                log_message('error', "Migration file {$file_migration} missing!");
                                            }
                                        }
                                    } else {
                                        $file_migration = FCPATH . 'application/migrations/' . $code;

                                        if (file_exists($file_migration)) {
                                            include($file_migration);
                                        } else {
                                            log_message('error', "Migration file {$file_migration} missing!");
                                        }
                                    }
                                }
                            }
                        } else { //0 se uguale, -1 se old > key
                            //Vuol dire che gli ho già eseguiti, quindi skippo
                            echo ("$key version already run.");
                            continue;
                        }
                    }
                }
            }
            die('ok');
        } else {
            die('ok');
        }
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
