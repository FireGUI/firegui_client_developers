<?php

/**
 * OpenBuilder Client Core model
 *
 */
class Core extends CI_Model
{

    public function __construct()
    {
        parent::__construct();


        $this->load->model('utils', 'utils_base');

        // Load utils model
        if ($this->db->dbdriver == 'postgre') {
            $this->load->model('Utils/postgre_utils', 'utils');
        } else if ($this->db->dbdriver == 'mysqli') {
            $this->load->model('Utils/mysqli_utils', 'utils');
        }

        $this->settings = $this->apilib->searchFirst('settings');

    }


    public function clearCache()
    {
        $this->mycache->clearCache(true);
        @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);

        // Pulisco cache frontend se c'è...
        if (is_dir(APPPATH . '../core/cache/')) {
            $this->load->helper('file');
            delete_files(APPPATH . '../core/cache/', false);
        }
    }


    /**
     * checkUpdate Client from repository or get last version
     * @param mixed $repository_url
     * @param mixed $update_channel
     * @return bool|string
     */

    function checkUpdate($repository_url = null, $update_channel = 4, $updatePatches = false)
    {
        if (empty($repository_url)) {
            $repository_url = OPENBUILDER_ADMIN_BASEURL;
        }

        if ($updatePatches == true) {
            $checkPatch = file_get_contents($repository_url . "public/client/getLastPatch/" . VERSION . "/$update_channel");
            $last_version = json_decode($checkPatch, true)['clients_releases_version'];
        } else {
            $last_version = file_get_contents($repository_url . "public/client/getLastClientVersionNumber/" . VERSION . "/0/$update_channel");
        }

        if (version_compare($last_version, VERSION, '>')) {
            return $last_version;
        } else {
            return false;
        }
    }

    /**
     * From 2.3.9 UpdateDB method, invoked usually after update client
     * @return never
     */
    public function update()
    {
        log_message("debug", "Core: Start UPDATE Database from Utils");
        $this->utils->migrationProcess();

        $this->mycache->clearCache();
    }


    public function updatePatches($repository_url = null, $channel_update = 4)
    {
        echo_log('debug', "Start Updating patches...");
        $this->updateClient($repository_url, 0, $channel_update, true);
        echo_log('debug', "Finish update patches...");
        echo_log('debug', "Check for new patches...");
        $this->checkUpdate($repository_url, $channel_update, true);
        // Add trigger next update? $this->updateclient....
    }

    /**
     * From 2.3.9 Core method to update client
     * @param mixed $repository_url
     * @param mixed $version_code if empty use 0 not null
     * @param mixed $channel
     * @throws Exception
     * @return bool|string
     */
    public function updateClient($repository_url = null, $version_code = 0, $channel = 4, $updatePatches = false)
    {

        if (!class_exists('ZipArchive')) {
            log_message('error', "updateClient failed, ziparchive class is not exists");
            return false;
        }

        if (empty($repository_url)) {
            $repository_url = OPENBUILDER_ADMIN_BASEURL;
        }

        // Check Update in progress and set true
        if (is_update_in_progress()) {
            log_message('error', 'updateClient failed, other update is already in progress...');
            return false;
        }

        $old_version = VERSION;

        if ($updatePatches == true) {
            $patchInfo = file_get_contents($repository_url . "public/client/getLastPatch/" . VERSION . "/$channel");
            $patch = json_decode($patchInfo, true);
            $file_link = $repository_url . "uploads/" . $patch['clients_releases_file'];
            $new_version = $patch['clients_releases_version'];
            $new_version_code = $patch['clients_releases_version_code'];
        } else {
            $file_link = $repository_url . "public/client/getLastClientVersion/" . VERSION . "/{$version_code}/$channel";
            $new_version = file_get_contents($repository_url . "public/client/getLastClientVersionNumber/" . VERSION . "/{$version_code}/$channel");
            $new_version_code = file_get_contents($repository_url . "public/client/getLastClientVersionCode/" . VERSION . "/{$version_code}/$channel");
        }

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

                // Force update databse
                $this->update();

                unlink($newfile);
                $this->clearCache(true);

                return $new_version_code;

            }
        }
    }

    /**
     * checkClientVersion from repository
     * @param mixed $update_channel
     * @return bool|string
     */
    function checkModuleUpdate($identifier, $update_repository_url = null)
    {
        if ($update_repository_url === null) {
            $update_repository_url = defined('OPENBUILDER_ADMIN_BASEURL') ? OPENBUILDER_ADMIN_BASEURL : null;
        }
        if (!$update_repository_url) {
            log_message('error', 'No module repository url defined');
            return false;
        }
        $data = $this->getModuleRepositoryData($identifier, $update_repository_url);

        $current_module = $this->db->get_where('modules', ['modules_identifier' => $identifier])->row_array();
        if (version_compare($data['modules_repository_version_code'], $current_module['modules_version_code'], '>')) {
            return $data['modules_repository_version_code'];
        } else {
            return false;
        }
    }

    /**
     * From 2.3.9 Core method to update client
     * @param string $module_identifier Identifier of the module to be updated
     * 
     * @throws Exception
     * @return bool|string
     */
    public function updateModule($identifier, $update_repository_url = null)
    {
        if ($update_repository_url === null) {
            $update_repository_url = defined('OPENBUILDER_ADMIN_BASEURL') ? OPENBUILDER_ADMIN_BASEURL : null;
        }
        if (!$update_repository_url) {
            log_message('error', 'No module repository url defined');
            return false;
        }
        $this->load->model('core/modules_model', 'core_modules');

        //debug($this->core_modules,true);
        return $this->core_modules->updateModule($identifier, $update_repository_url);

    }
    public function getModuleRepositoryData($module_identifier, $update_repository_url = null)
    {
        $this->load->model('core/modules_model', 'core_modules');
        return $this->core_modules->getModuleRepositoryData($module_identifier, $update_repository_url);

    }


}