<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/*
 *
 *  SI SUPPONE CHE NEL REF DI UN FIELDS CI SARà IL NOME TABELLA E NON L'ID O IL NOME DEL FIELDS CHE DEVE JOINARE... PERCHè? PERCHè SI è STABILITO, CHE OGNI TABELLA DI SUPPORTO O ENTITà AVRà PER FORZA
 * UN CAMPO NOMETABELLA_ID E QUINDI IN AUTOMATICO, INSERENDO IL NOME TABELLA VERRà PRESO QUEL CAMPO CONCATENANDO _ID... NON è UN ACCROCCHIO ANCHE SE LO PUò SEMBRARE...
 * TUTTO QUESTO FORSE PER EVITARE DI DOVE INSERIRE NEI FIELDS ANCHE LA TABELLA O ENTITà A CUI FANNO RIFERIMENTO ED IL CAMPO DA JOINARE...
 *
 */

class Main extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        // Check because Module manager can work without modules_manager_settings! (like when che project is new)
        $check_settings = $this->crmentity->entityExists('modules_manager_settings');

        if ($check_settings) {
            $this->settings = $this->apilib->searchFirst('modules_manager_settings');
            $this->token = $this->settings['modules_manager_settings_license_token'];
        } else {
            $this->settings['modules_manager_settings_modules_repository'] = null;
            $this->token = null;
        }

        $this->settings = $this->apilib->searchFirst('modules_manager_settings');

        $this->load->model('core');
        //$this->load->model('module-manager/modules_model');

    }



    public function check_module_version($module_identifier)
    {
        if (defined('ADMIN_PROJECT') && !empty(ADMIN_PROJECT)) {
            $project_id = ADMIN_PROJECT;
        } else {
            $project_id = 0;
        }

        $data = $this->core->getModuleRepositoryData($module_identifier, $this->settings['modules_manager_settings_modules_repository'], $project_id, $this->token);

        // Verifica se la decodifica è avvenuta correttamente
        if ($data) {
            echo json_encode($data);
        } else {
            // Se la decodifica non è avvenuta correttamente, mostra un errore
            echo json_encode(false);
        }

    }

    /**
     * Install module
     * @param mixed $identifier
     * @return void
     */
    public function install_module($identifier)
    {
        echo_log('info', "Start Install module '$identifier' without backup...");

        $repository_module = $this->core->installModule($identifier);

        if ($repository_module && !empty($repository_module['modules_repository_notification_message']) && trim($repository_module['modules_repository_notification_message']) != '') {

            //Se questo aggiornamento richiede una notifica, creo la notification (sfrutto il modulo core-notifications...)
            if ($this->datab->module_installed('core-notifications')) {
                $this->load->model('core-notifications/clientnotifications');
                $admin_users = $this->apilib->search('users', ["users_type_value = 'Admin' OR users_type IS NULL"]);

                foreach ($admin_users as $user) {
                    $user_id = $user['users_id'];
                    $this->clientnotifications->create(
                        array(
                            'notifications_type' => 5,
                            'notifications_user_id' => $user_id,
                            'notifications_title' => "Modulo {$repository_module['modules_repository_name']} aggiornato.",
                            'notifications_message' => $repository_module['modules_repository_notification_message'],
                        )
                    );
                }
            }
        }

        echo_log("debug", "Install module '$identifier' finished...");
        if ($repository_module) {
            echo_log("debug", "Installed succesfully!");
        } else {
            echo_log("debug", "Install failed!");
        }
    }

    /**
     * Update Module
     * @param mixed $identifier
     * @return void
     */
    public function update_module($identifier)
    {
        echo_log('info', "Start update module '$identifier' without backup...");

        $repository_module = $this->core->updateModule($identifier);

        if ($repository_module && !empty($repository_module['modules_repository_notification_message']) && trim($repository_module['modules_repository_notification_message']) != '') {
            //Se questo aggiornamento richiede una notifica, creo la notification (sfrutto il modulo core-notifications...)
            if ($this->datab->module_installed('core-notifications')) {
                $this->load->model('core-notifications/clientnotifications');
                $admin_users = $this->apilib->search('users', ["users_type_value = 'Admin' OR users_type IS NULL"]);
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

        echo_log("debug", "Update module '$identifier' finished...");
        if ($repository_module) {
            echo_log("debug", "Installed succesfully!");
        } else {
            echo_log("debug", "Install failed!");
        }
    }

    public function set_auto_update($modules_id, $new_val)
    {
        $this->db->where('modules_id', $modules_id)->update('modules', ['modules_auto_update' => $new_val]);
        $this->mycache->clearCache();
    }
    // public function stampa($pagina, $sidebar = true)
// {
//     $this->template['head'] = $this->load->view('layout/head', array(), true);
//     $this->template['header'] = $this->load->view('layout/header', ['projects' => $this->projects], true);
//     if ($sidebar) {
//         $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
//     } else {
//         $this->template['sidebar'] = "";
//     }
//     $this->template['page'] = $pagina;
//     $this->template['footer'] = $this->load->view('layout/footer', null, true);
//     $this->load->view('layout/main', $this->template);
// }

    public function show_details($module_identifier) {
        // $module = $this->db->get_where('modules', ['modules_identifier' => $module_identifier])->row_array();
        
        $module = $this->core->getModuleRepositoryDataFull($module_identifier);

        $this->load->view(
            "layout/modal_container",
            array(
                'size' => '',
                'title' => t('Module details') . ': <b>' . $module['modules_repository_name'] . '</b>',
                'subtitle' => '',
                'content' => $this->load->view('show_details', ['module' => $module], true),
                'footer' => null,
            )
        );

        
    }
}
