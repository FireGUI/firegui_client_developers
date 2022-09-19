<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Openbuilder extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) {
            $this->output->cache(0);
        }
        $permitted_routes = ['get_client_version'];
        $route = $this->uri->segment(2);
        $unallowed = false;
        if (!in_array($route, $permitted_routes) && (!$this->auth->check() || !$this->auth->is_admin())) {
            if (!$token = $this->input->get('token')) {
                log_message('DEBUG', "Missing token for openbuilder request!");

                $unallowed = true;
            } else {
                $token_match = $this->db->where('meta_data_key', 'openbuilder_token')->where('meta_data_value', $token)->get('meta_data');

                if ($token_match->num_rows() == 0) {
                    $unallowed = true;
                } else {
                    //Destroy token and proceed...
                    $this->db->where('meta_data_key', 'openbuilder_token')->delete('meta_data');

                }
            }
        }
// TODO: INTEGRARE SISTEMA DI PROTEZIONE, SOLO OPENBUILDER DEVE POTER ESEGUIRE QUESTI METODI SE QUALCUNO SCOPRE

        if ($unallowed) {
            set_status_header(403);
            die('Nope... not allowed!');
        }
    }

    public function createModule($identifier)
    {

        //Creo le cartelle necessarie
        $folders = [
            'controllers', 'models', 'views', 'assets',
        ];

        $modules_path = APPPATH . 'modules/';

        if (!is_dir($modules_path)) { //create the folder if it's not already exists
            mkdir($modules_path, DIR_WRITE_MODE, true);
        }

        $prefix_folder = $modules_path . $identifier . '/';

        foreach ($folders as $folder) {
            if (!is_dir($prefix_folder . $folder)) { //create the folder if it's not already exists
                mkdir($prefix_folder . $folder, DIR_WRITE_MODE, true);

                touch($prefix_folder . $folder . '/.gitkeep');
            }
        }
    }

    public function uninstallModule($identifier)
    {
        $module = $this->db->where('modules_identifier', $identifier)->get('modules')->row_array();
        if ($module) {
            $this->deleteDir(APPPATH . "modules/{$identifier}");
        }
    }

    public function updateFromGit($command = null, $output = true)
    {
        if ($command == null) {
            $command = "git pull";
        }

        $result = array();
        exec($command, $result);

        if ($output == true) {
            foreach ($result as $line) {
                print($line . "\n");
            }
        }
    }

//Send module to openbuilder (when creating new module or new release)

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

            if ($success === true) {
                if (file_exists($destination_file)) {
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $identifier . '.zip"');
                    header('Content-Length: ' . filesize($destination_file));
                    readfile($destination_file);
                } else {
                    die('Can not create  ' . $destination_file);
                }
            } else {
                die($success);
            }
        }
    }

    // Receive module from Builder
    public function uploadModule($identifier)
    {
        $zip = new ZipArchive;

        if ($zip->open($_FILES['module_file']['tmp_name']) === true) {
            if (!is_dir(APPPATH . "modules")) {
                mkdir(APPPATH . "modules", DIR_WRITE_MODE, true);
                if (!is_dir(APPPATH . "modules")) {
                    log_message("DEBUG", "Cannot create folder '" . APPPATH . "modules'");
                }
            }
            $zip->extractTo(APPPATH . "modules/{$identifier}");
            $zip->close();
        } else {
            die('Wrong zip archive!');
        }

        //Una volta scompresso, eseguo l'eventuale file di install
        if (file_exists(APPPATH . "modules/{$identifier}/install/install.php")) {
            include APPPATH . "modules/{$identifier}/install/install.php";
        }
    }

    public function updateClient($close = false, $version_code = null)
    {
        if (!class_exists('ZipArchive')) {
            die("Missing ZipArchive class in client");
        }

        $old_version = VERSION;

        $file_link = OPENBUILDER_BUILDER_BASEURL . "public/client/getLastClientVersion/" . VERSION . "/{$version_code}";
        $new_version = file_get_contents(OPENBUILDER_BUILDER_BASEURL . "public/client/getLastClientVersionNumber/" . VERSION . "/{$version_code}");
        $new_version_code = file_get_contents(OPENBUILDER_BUILDER_BASEURL . "public/client/getLastClientVersionCode/" . VERSION . "/{$version_code}");

        //Pay attention: even if I ask the $version_code, $file_link could contains different version because intermediate version (or versions) need a migration or updatedb, so we just need to pass throught this update before
        log_message('debug', "Updating from {$old_version} to {$new_version} ($new_version_code), file {$file_link}");

        $newfile = './tmp_file.zip';
        if (!copy($file_link, $newfile)) {
            throw new Exception("Error while copying zip file.");
        } else {
            $zip = new ZipArchive();

            if ($zip->open($newfile) !== true) {
                throw new Exception("Cannot open <$newfile>");
            } else {
                $temp_folder = FCPATH;
                @mkdir($temp_folder);
                $zip->extractTo($temp_folder);
                $zip->close();

                // Search update databases file for this version
                $files = scandir(APPPATH . 'migrations');

                foreach ($files as $file) {
                    if ($file == 'update_db.php') {
                        // Check if exist an update_db file to execute update queries
                        include APPPATH . 'migrations/update_db.php';

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
                        include APPPATH . 'migrations/update_php_code.php';

                        // Sort array from oldest version to newest
                        uksort($updates, 'my_version_compare');

                        foreach ($updates as $key => $value) {

                            // Check if the version number is old or new
                            if ($key == $new_version) {
                                foreach ($value as $key_type => $code) {
                                    if ($key_type == 'eval') {
                                        eval($code);
                                    } elseif ($key_type == 'include') { // Added possibility to execute a custom code when updating client
                                        if (is_array($code)) {
                                            foreach ($code as $file_to_include) {
                                                $file_migration = APPPATH . 'migrations/' . $file_to_include;
                                                if (file_exists($file_migration)) {
                                                    include $file_migration;
                                                } else {
                                                    log_message('error', "Migration file {$file_migration} missing!");
                                                }
                                            }
                                        } else {
                                            $file_migration = APPPATH . 'migrations/' . $code;

                                            if (file_exists($file_migration)) {
                                                include $file_migration;
                                            } else {
                                                log_message('error', "Migration file {$file_migration} missing!");
                                            }
                                        }
                                    }
                                }
                            } else {
                                log_message('debug', "new version: $new_version, key: $key");
                            }
                        }
                    }
                }

                unlink($newfile);
                $this->clearCache(true);
                if ($close) {
                    echo "Client updated! This page will be closed in 5 seconds...<script>setTimeout(function () {window.close();window.history.back();}, 5000);</script>";
                } else {
                    echo $new_version_code;
                }
            }
        }
    }

    public function test_migration($migration_file)
    {
        if ($this->auth->is_admin()) {
            $do_rollback = $this->input->get('rollback') ? true : false;

            $this->db->trans_begin();

            include FCPATH . 'application/migrations/' . $migration_file . '.php';

            if ($this->db->trans_status() === false || $do_rollback) {
                $this->db->trans_rollback();
            } else {
                $this->db->trans_commit();
            }
        } else {
            die('Must be logged in');
        }
    }

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
                    include "$migration_dir/$file";

                    // Sort array from oldest version to newest
                    uksort($updates, 'my_version_compare');

                    foreach ($updates as $key => $value) {
                        $version_compare_old = version_compare($key, $old_version);
                        //if ($version_compare_old || ($key == 0 && $old_version == 0)) { //1 se old è < di key
                        if ($version_compare_old || ($old_version == 0)) { //Rimosso key == 0 perchè altrimenti esegue infinite volte l'update 0 (che di solito va fatto solo all'install)

                            foreach ($value as $key_type => $code) {
                                if ($key_type == 'eval') {
                                    eval($code);
                                } elseif ($key_type == 'include') {
                                    if (is_array($code)) {
                                        foreach ($code as $file_to_include) {
                                            $file_migration = "$migration_dir/$file_to_include";
                                            if (file_exists($file_migration)) {
                                                include $file_migration;
                                            } else {
                                                log_message('error', "Migration file {$file_migration} missing!");
                                            }
                                        }
                                    } else {
                                        $file_migration = APPPATH . 'migrations/' . $code;

                                        if (file_exists($file_migration)) {
                                            include $file_migration;
                                        } else {
                                            log_message('error', "Migration file {$file_migration} missing!");
                                        }
                                    }
                                }
                            }
                        } else { //0 se uguale, -1 se old > key
                            //Vuol dire che gli ho già eseguiti, quindi skippo
                            //echo("$key version already run.");
                            continue;
                        }
                    }
                }
            }
            $this->clearCache(true);
            die('ok');
        } else {
            $this->clearCache(true);
            die('ok');
        }
    }

    public function downloadClient()
    {
    }

    public function getCustomViews()
    {
        echo json_encode(dirToArray(APPPATH . (empty($_SERVER['OPENBUILDER_CLIENT_TEMPLATE']) ? 'views_adminlte' : $_SERVER['OPENBUILDER_CLIENT_TEMPLATE']) . '/custom/'));

    }

    public function getModulesCustomViews()
    {
        $modules_views = [];

        $modules = dirToArray(APPPATH . '/modules');

        foreach ($modules as $module_key => $module_value) {
            $modules_views[$module_key] = $module_value['views'];
        }

        echo json_encode($modules_views);
    }

    public function clearCache()
    {
        $this->apilib->clearCache(true);
        @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);

        // Pulisco cache frontend se c'è...
        if (is_dir(APPPATH . '../core/cache/')) {
            $this->load->helper('file');
            delete_files(APPPATH . '../core/cache/', false);
        }
    }

    //Send module to firegui (when creating new module or new release)
    public function downloadClientZip()
    {
        $folder = FCPATH;

        $destination_file = './uploads/client.zip';
        if (!file_exists($folder)) {
            log_message('ERROR', "'$folder' folder does not exists!");
            die("'$folder' folder does not exists!");
        }

        $success = zip_folder($folder, $destination_file, ['application/logs']);

        if ($success === true) {
            if (file_exists($destination_file)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="client.zip"');
                header('Content-Length: ' . filesize($destination_file));
                readfile($destination_file);
            } else {
                log_message('ERROR', "'$destination_file' file does not exists!");
                die("'$destination_file' file does not exists!");
            }
        } else {
            die($success);
        }
        @unlink($destination_file);
    }

    private function deleteDir($path)
    {
        return is_file($path) ?
        @unlink($path) :
        array_map(__FUNCTION__, glob($path . '/*')) == @rmdir($path);
    }
}
