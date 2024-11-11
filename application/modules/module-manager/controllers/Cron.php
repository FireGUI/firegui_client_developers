<?php
class Cron extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->settings = $this->apilib->searchFirst('modules_manager_settings');

        $this->_license_token = $this->settings['modules_manager_settings_license_token'];
        $this->_project_id = (defined('ADMIN_PROJECT') && !empty(ADMIN_PROJECT)) ? ADMIN_PROJECT : 0;
    }


    /**
     * AutoUpdate modules. Run between 1 and 4 am 
     */
    public function autoUpdateModules($limit = 5, $force = false)
    {
        echo_log('info', 'Start autoUpdateModules...');

        // Check if invoked from cli...
        if (!is_cli()) {
            echo_log('error', "autoUpdateModules failed... this cron works only via cli");
            return false;
        }

        if (!$force) {
            // Check work time execution
            $hour = date('H');
            if ($hour < 1 || $hour > 4) {
                echo_log('info', 'Stop autoUpdateModules, this is not a good time to execute.');
                return false;
            }


        }


        // First check
        $settings = $this->apilib->searchFirst('modules_manager_settings');

        // if (empty($settings['modules_manager_settings_modules_repository'])) {
        //     echo_log('error', "I can not update modules, auto update module repository is not defined");
        //     return false;
        // }

        $autoUpdateModules = $this->db->limit($limit)->order_by('modules_last_update', 'ASC')->get_where('modules', ['modules_auto_update' => DB_BOOL_TRUE])->result_array();

        if ($autoUpdateModules) {
            $this->load->model('core');
            $tempDir = FCPATH . "tmp";
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Filename new dump
            $dump_filename = "backup-db-" . date("d-m-Y") . ".sql.gz";
            // Clear old backup
            $max_old_backup_days = defined('AUTO_UPDATE_MAX_BACKUPS_DAYS') && AUTO_UPDATE_MAX_BACKUPS_DAYS > 0 ? AUTO_UPDATE_MAX_BACKUPS_DAYS : 5;
            delete_older_files($tempDir, $max_old_backup_days, 'backup-db');


            // // Start Dump
            if (generate_dump($tempDir, $dump_filename) != true) {
                echo_log('error', "Dump failed... update stopped. Check logs.");
                return false;
            } else {
                echo_log('info', "Dump generated.");
            }



            // Start Backup files
            echo_log('info', "Start backup files...");

            $path = FCPATH;
            echo_log('error', "Zip path: " . $path);
            $exclude_dirs = array(
                "uploads",
                "tmp",
                ".git",
                "logs",
                "application/logs",
                "application/cache",
            );
            $destination = $tempDir . "/last_backup.zip";
            ci_zip_folder(FCPATH, $destination, $exclude_dirs);

            // Check exists zip file and size if already 100mb
            echo_log("debug", "File backup zip created: " . $destination);
            echo_log("debug", "File zip size: " . filesize($destination));
            if (!file_exists($destination) || filesize($destination) < 100000000) {
                echo_log('error', "Zip backup failed... update failed.");
                return false;
            }
            $update_repository_url = defined('OPENBUILDER_ADMIN_BASEURL') ? OPENBUILDER_ADMIN_BASEURL : null;

            foreach ($autoUpdateModules as $module) {
                $identifier = $module['modules_identifier'];
                $this->db->where('modules_identifier', $identifier)->update('modules', ['modules_last_update' => date('Y-m-d H:i:s')]);
                $this->mycache->clearCache();
                //debug($module,true);

                // Check version
                if ($this->core->checkModuleUpdate($identifier, $update_repository_url, $this->_project_id, $this->_license_token) == false) {
                    echo_log('error', "Module '$identifier' is already updated.");
                    continue;
                }
                echo_log('debug', "Running module '$identifier' update.");

                $repository_module = $this->core->updateModule($module['modules_identifier']);

                if ($repository_module && !empty($repository_module['modules_repository_notification_message']) && trim($repository_module['modules_repository_notification_message']) != '') {
                    //Se questo aggiornamento richiede una notifica, creo la notification (sfrutto il modulo core-notifications...)
                    if ($this->datab->module_installed('core-notifications')) {
                        $this->load->model('core-notifications/clientnotifications');
                        $admin_users = $this->apilib->search('users', ['users_type_value' => 'Admin']);
                        foreach ($admin_users as $user) {
                            $user_id = $user['users_id'];
                            $notifica = array(
                                'notifications_type' => 5,
                                'notifications_user_id' => $user_id,
                                'notifications_title' => "Modulo {$repository_module['modules_repository_name']} aggiornato.",
                                'notifications_message' => $repository_module['modules_repository_notification_message'],
                            );
                            if ($this->db->get_where('notifications', $notifica)->num_rows() == 0) {
                                $this->clientnotifications->create(
                                    $notifica
                                );
                            }
                            
                        }
                    }
                }

                echo_log('debug', "Finished module '$identifier' update.");
            }
        }
    }

}
