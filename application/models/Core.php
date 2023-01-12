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
     * Core method to update client
     * @param mixed $repository_url
     * @param mixed $version_code if empty use 0 not null
     * @param mixed $channel
     * @throws Exception
     * @return bool|string
     */
    public function updateClient($repository_url = null, $version_code = 0, $channel = 4)
    {

        if (!class_exists('ZipArchive')) {
            log_message('error', "updateClient failed, ziparchive class is not exists");
            return false;
        }

        if (empty($repository_url)) {
            $repository_url = OPENBUILDER_BUILDER_BASEURL;
        }

        // Check Update in progress and set true
        if (is_update_in_progress()) {
            log_message('error', 'updateClient failed, other update is already in progress...');
            return false;
        }

        $old_version = VERSION;
        $file_link = $repository_url . "public/client/getLastClientVersion/" . VERSION . "/{$version_code}/$channel";
        $new_version = file_get_contents($repository_url . "public/client/getLastClientVersionNumber/" . VERSION . "/{$version_code}/$channel");
        $new_version_code = file_get_contents($repository_url . "public/client/getLastClientVersionCode/" . VERSION . "/{$version_code}/$channel");

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

                return $new_version_code;

            }
        }
    }
}