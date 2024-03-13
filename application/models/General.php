<?php


class General extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
    }

    public function get_database_connection($params, $save_to_session = false)
    {

        $config_current_db = array(
            'dsn' => '',
            'dbprefix' => '',
            'pconnect' => TRUE,
            // Disattivato per problematiche creazione progetto durante fase di install, con postges. Si può riabilitare ma attenzione alle installazioni postgres verificare che funzionino correttamente
            'db_debug' => true,
            //Mettendo False  se c'è un errore connessione il codice procede oltre al $connection_obj e quindi posso gestirmelo, Se metto true il load->database va in error_php, Pero se metto true è piu comodo perche mi entra sempre nell error_db
            'cache_on' => FALSE,
            'cachedir' => '',
            'char_set' => 'utf8',
            'dbcollat' => 'utf8_general_ci',
            'swap_pre' => '',
            //'autoinit' => FALSE //L'autoinit non esiste più da CI3!

        );

        $config_current_db['username'] = $params['projects_database_user'];
        $config_current_db['password'] = $params['projects_database_password'];
        $config_current_db['hostname'] = $params['projects_database_host'];
        $config_current_db['database'] = $params['projects_database_name'];
        if (!empty($params['projects_database_port'])) {
            $config_current_db['port'] = $params['projects_database_port'];
        }

        $config_current_db['dbdriver'] = strtolower($params['projects_database_driver_value']);

        if ($save_to_session == True) {
            $this->set_database_session($params);
        }

        if (!defined('DB_BOOL_TRUE')) {
            if ($config_current_db['dbdriver'] == 'postgre') {
                define('DB_BOOL_TRUE', 't');
                define('DB_BOOL_FALSE', 'f');
                define('DB_INTEGER_IDENTIFIER', 'INT');
                define('DB_BOOL_IDENTIFIER', 'BOOL');
            } else {
                define('DB_BOOL_TRUE', '1'); //Mettere 1 per mysql
                define('DB_BOOL_FALSE', '0'); //Mettere 0 per mysql
                define('DB_INTEGER_IDENTIFIER', 'integer');
                define('DB_BOOL_IDENTIFIER', 'BOOLEAN');
            }
        }

        $connection_obj = $this->load->database($config_current_db, true);
        return $connection_obj;
        // Catch di eventuali errori e set flashdata
        /*if ($connection_obj->conn_id == FALSE) {
        $params['db_connection_error'] = $this->db->error()['message'];
        $this->session->set_flashdata(DATABASE_CONNECTION_ERROR, $params);
        // Svuoto sessione database parameters
        $this->set_database_session(null);
        redirect(base_url());
        } else {
        return $connection_obj;
        }*/
    }

    public function set_database_session($params)
    {
        $this->session->set_userdata(DATABASE_PARAMETERS, $params);
    }

    public function check_project_ownership($project_id)
    {
        if ($this->db->get_where('projects', ['projects_id' => $project_id, 'projects_customer' => $this->auth->get('id')])->num_rows() != 1) {
            throw new Exception("Project '$project_id' not owned by '{$this->auth->get('id')}'!");
        }
    }

    // public function generateToken()
    // {
    //     $token = generateRandomPassword(20, true);

    //     $result = $this->selected_db->insert('meta_data', ['meta_data_key' => 'openbuilder_token', 'meta_data_value' => $token]);

    //     return $token;
    // }

    public function checkClientConnection($hash)
    {
        if (empty($hash)) {
            $output = json_encode(array('status' => 0, 'msg' => "Project hash is empty. Something wrong"));
        } elseif (!$this->auth->check()) {
            $output = json_encode(array('status' => 0, 'msg' => "No builder connection found. Login to your Builder account"));
        } else {

            $_hash = base64_decode($hash);
            $json_hash = json_decode($_hash, true);


            //debug('INTERVENIRE QUI, PRIMA DI CONNETTERSI DAVVERO AL PROGETTO (Salvando in sessione i dati), VERIICARE CHE IL TOKEN PASSATO SIA PRESENTE NEL DB', true);

            // Get project info
            $project = $this->db
                ->join('projects_database_driver', '(projects_database_driver_id = projects_database_driver)')
                ->join('clients_releases', '(clients_releases_id = projects_client_release)', 'left')
                ->get_where('projects', [
                    'projects_database_name' => $json_hash[0],
                    //TOLTO perchè un progetto può avere nel database.php localhost mentre builder avrà l'host corretto quindi diverso
                    //'projects_database_host' => $json_hash[1],
                    'projects_database_user' => $json_hash[2],
                    'md5(projects_database_password)' => $json_hash[3]
                ]);

            //die($this->db->last_query());

            if ($project->num_rows() < 1) {
                //die($this->db->last_query());
                $output = ['status' => 0, 'msg' => "Project not found, check db config in Builder project settings."];
            } else {
                $project = $project->row_array();
                // Check ownership
                if ($project['projects_customer'] != $this->auth->get('id')) {
                    $output = ['status' => 0, 'msg' => "Projects customer and builder user logged does not match"];
                } else {
                    $output = ['status' => 1];
                    $this->general->set_database_session($project);
                }
            }
        }

        return $output;
    }

    /*
    ------------------ Jobs
    */
    public function new_system_job($type, $what, $project_id, $user_created, $user_to_notify, $extra = null, $status = null, $send_monitor_alert = false)
    {

        // Temporary
        if ($send_monitor_alert == true) {
            send_monitor_alert('New Open Builder System Job', "Type: " . $type . " What: " . $what . " Project ID: " . $project_id . " ($user_created) with extra params: " . $extra, 'info');
        }

        $job['system_jobs_creation_date'] = date('Y-m-d H:i');
        $job['system_jobs_type'] = $type;
        $job['system_jobs_what'] = $what;
        $job['system_jobs_project'] = $project_id;
        $job['system_jobs_user_created'] = $user_created;
        $job['system_jobs_user_to_notify'] = $user_to_notify;
        $job['system_jobs_extra'] = $extra;
        if ($status == null) {
            $job['system_jobs_status'] = JOB_STATUS_TO_DO;
        } else {
            $job['system_jobs_status'] = $status;
        }
        $this->db->insert('system_jobs', $job);

        $this->addLog("New system job", array('what' => $what));
    }


    public function addLog($type, array $extra = null, $customer_id = null)
    {

        // Fix from builder
        return false;

        // Preparo array base
        $logEntry = [
            'user_logs_customer_id' => ($customer_id) ? $customer_id : $this->auth->get('customers_id'),
            'user_logs_ip' => filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?: 'N/A',
            'user_logs_browser' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') ?: null,
            'user_logs_referer' => filter_input(INPUT_SERVER, 'HTTP_REFERER') ?: null,
            'user_logs_action' => $type,
            'user_logs_creation_date' => date('Y-m-d H:i')
        ];

        // Calcolo il titolo rimpiazzando i dati dal record principale

        $logEntry['user_logs_extra_data'] = $extra ? json_encode($extra) : null;

        $this->db->insert('user_logs', $logEntry);
    }

   
}