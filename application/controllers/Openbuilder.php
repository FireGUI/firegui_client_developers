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
            //$this->output->cache(0);
        }
        $permitted_routes = ['get_client_version', 'get_status']; // TODO: GET_STATUS MUST BE PUBLIC?
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

    /*
     * Method called via OpenBuilder/Partner portal to get progeject information and status
     *
     */
    public function get_status()
    {
        // Client version
        $data['client_version'] = VERSION;

        // Cron Check
        $data['crons_last_execution'] = $this->db->query("SELECT crons_last_execution FROM crons ORDER BY crons_last_execution DESC LIMIT 1")->row()->crons_last_execution;

        // All Settings (to get autoupdate informations and more)
        $data['settings'] = $this->apilib->search("settings", [], 1);

        // All Modules
        $data['modules'] = $this->db->query("SELECT * FROM modules")->result_array();

        // Last Log Crm
        $data['last_log_crm'] = $this->db->query("SELECT * FROM log_crm ORDER BY log_crm_id DESC LIMIT 1")->row_array();

        // COUNT ci_sessions and log_crm
        $data['count']['ci_sessions'] = $this->db->query("SELECT COUNT(*) AS c FROM ci_sessions")->row()->c;
        $data['count']['log_crm'] = $this->db->query("SELECT COUNT(*) AS c FROM log_crm")->row()->c;

        // Free space
        $free_space = disk_free_space(".");
        $free_space = $free_space / (1024 * 1024 * 1024);
        $data['free_space'] = number_format($free_space, 2);

        $free_space = disk_free_space("./uploads/");
        $free_space = $free_space / (1024 * 1024 * 1024);
        $data['free_space_uploads'] = number_format($free_space, 2);

        e_json($data);
    }
    public function createModule($identifier)
    {
        //Creo le cartelle necessarie
        $folders = [
            'controllers',
            'models',
            'views',
            'assets',
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

            // Create temp directory
            if (!file_exists(FCPATH . 'uploads/modules_temp')) {
                mkdir(FCPATH . 'uploads/modules_temp', 0755, true);
            }

            $destination_file = FCPATH . 'uploads/modules_temp/' . $identifier . '.zip';

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
        $zip = new ZipArchive();

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

    /**
     * Update Client, method invoked from Builder
     * @param mixed $close
     * @param mixed $version_code
     * @param mixed $channel
     * @return void
     */
    public function updateClient($close = false, $version_code = 0, $channel = 4)
    {

        $this->load->model('core');
        $output = $this->core->updateClient(null, $version_code, $channel);

        if ($output !== false) {
            if ($close) {
                echo "Client updated! This page will be closed in 5 seconds...<script>setTimeout(function () {window.close();window.history.back();}, 5000);</script>";
            } else {
                echo $output;
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
        $this->load->model('core/modules_model', 'core_modules');


        $return = $this->core_modules->run_migrations($module_identifier, $old_version, $new_version);
        die('ok');
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
        $this->load->model('core');
        $this->core->clearCache();
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