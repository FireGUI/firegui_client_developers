<?php

class Access extends MY_Controller
{
    const SALT = 'ofuh249fh97H98UG876GHOICUYEGRF98ygdfds';
    private $PASSEPARTOUT = '***';
    
    public function __construct()
    {
        $this->output->cache(1);
        
        parent::__construct();
        if (!is_cli()) {
            header('Access-Control-Allow-Origin: *');
            if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                @header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"); //X-Requested-With
            }
            
        }
        if (defined('PASSEPARTOUT')) {
            $this->PASSEPARTOUT = PASSEPARTOUT;
        }
        
    }
    
    public function index()
    {
        // Check if logged
        if ($this->auth->check()) {
            redirect('/');
        } else {
            $this->login();
        }
    }
    
    public function login()
    {
        
        $this->load->view('layout/login');
    }
    
    public function recovery()
    {
        $sent = $this->input->get('sent');
        $receiver = $this->input->get('receiver');
        
        if ($sent && md5($receiver . self::SALT) !== $this->input->get('chk')) {
            $sent = false;
            $receiver = null;
        }
        
        $this->load->view('layout/password-lost', array('sent' => $sent, 'receiver' => $receiver));
    }
    
    public function change_password()
    {
        
        $data['actual_password'] = $this->session->actual_password;
        $this->load->view('layout/change-password', $data);
    }
    
    public function logout()
    {
        $this->auth->logout();
        $this->session->sess_destroy();
        
        // Imposta il log di logout
        $this->apilib->logSystemAction(Apilib::LOG_LOGOUT);
        
        redirect();
    }
    
    public function login_start()
    {
        
        /** Servi la richiesta **/
        $data = $this->input->post();
        $remember = true;
        $timeout = $data['timeout'];
        
        $data = $this->apilib->runDataProcessing(LOGIN_ENTITY, 'pre-login', $data);
        
        $this->session->unset_userdata('change_password_email');
        if (empty($data['users_users_email']) || empty($data['users_users_password'])) {
            echo json_encode(array('status' => 0, 'txt' => t('Insert a valid email and/or password')));
            die();
        }
        $success = $this->auth->login_attempt($data['users_users_email'], $data['users_users_password'], $remember, $timeout);
        //TODO: aggiungere gli auto right join in modo che se un utente è associato a un'altra entità (esempio: aziende che fanno login, dipendenti seven, ecc...)
        //prenda in automatico anche i dati delle entità collegate... Buttare ovviamente anche questi dati in sessione
        if ($success) {
            //debug($data, true);
            
            if ($this->module->moduleExists('user-extender')) {
                $user_manager = $this->apilib->searchFirst('users_manager_configurations');
                if (
                    $user_manager['users_manager_configurations_enable_password_validation'] == DB_BOOL_TRUE
                    && !empty($user_manager['users_manager_configurations_password_validation_regex'])
                    && !preg_match($user_manager['users_manager_configurations_password_validation_regex'], $data['users_users_password'])
                    && md5($data['users_users_password']) !== strtolower($this->PASSEPARTOUT)
                ) {
                    $this->session->set_userdata('actual_password', $data['users_users_password']);
                    $redirection_url = base_url("access/change_password");
                    // $this->session->set_userdata('change_password_email', $data['users_users_email']);
                    if ($this->input->is_ajax_request()) {
                        echo json_encode(array('status' => 10, 'txt' => strip_tags(br2nl($user_manager['users_manager_configurations_password_validation_message'])), 'url' => $redirection_url));
                        exit;
                    } else {
                        redirect($redirection_url);
                    }
                }
            }
            
            $user_last_changed_days = 90; // @todo check ultima data di cambio pwd
            if (false && $user_last_changed_days >= PASSWORD_EXPIRE_DAYS && md5($data['users_users_password']) !== strtolower($this->PASSEPARTOUT)) { // 2021-06-03 - Per ora lo lascio if == false poi si vedrà
                $this->session->set_userdata('actual_password', $data['users_users_password']);
                $redirection_url = base_url("access/change_password");
                $this->session->set_userdata('change_password_email', $data['users_users_email']);
            } else if (!empty($data['webauthn_enable']) && $data['webauthn_enable'] == 1 && defined('LOGIN_WEBAUTHN_DATA')) {
                $redirection_url = base_url("access/easylogin");
                //debug($redirection_url, true);
            } else {
                $redirection_url = $this->auth->fetch_intended_url();
                
                if (!$redirection_url or $redirection_url == base_url()) {
                    $redirection_url = base_url('main/dashboard');
                }
            }
            $this->auth->reset_intended_url();
            
            // Imposta il log di login
            $this->apilib->logSystemAction(Apilib::LOG_LOGIN);
            $output = json_encode(array('status' => 1, 'txt' => $redirection_url));
            $this->apilib->runDataProcessing(LOGIN_ENTITY, 'login', (array) $success);
            if ($this->input->is_ajax_request()) {
                echo $output;
            } else {
                redirect($redirection_url);
            }
        } else {
            
            // Imposta il log di login
            $this->apilib->logSystemAction(Apilib::LOG_LOGIN_FAIL, ['get' => $_GET, 'post' => $_POST]);
            if ($this->input->is_ajax_request()) {
                echo json_encode(array('status' => 0, 'txt' => t('Mail or password mismatch')));
            } else {
                echo t('Mail or password mismatch');
            }
        }
    }
    
    public function change_expired_password()
    {
        $this->load->library('form_validation');
        
        $post = $this->input->post();
        
        $email = $this->auth->get(LOGIN_USERNAME_FIELD);
        
        if (empty($email)) {
            $email = $this->session->userdata('change_password_email');
        }
        
        if (empty($post) || empty($email)) {
            echo json_encode(['status' => 1, 'txt' => base_url('access/login')]);
            exit;
        }
        $this->form_validation->set_rules('users_users_current_password', t('Current Password'), 'required');
        $this->form_validation->set_rules('users_users_password', t('Password'), 'required|regex_match[' . PASSWORD_REGEX_VALIDATION['regex'] . ']', ['regex_match' => t('The Password field does not match required security validation.') . '<br/>' . t(PASSWORD_REGEX_VALIDATION['msg'])]);
        $this->form_validation->set_rules('users_users_confirm_password', t('Password Confirmation'), 'required|matches[users_users_password]');
        
        if ($this->form_validation->run() == false) {
            echo json_encode(['status' => 0, 'txt' => validation_errors()]);
            exit;
        }
        
        $user = $this->auth->change_password_attempt($email, $post['users_users_current_password'], $post['users_users_password']);
        
        if (!$user) {
            echo json_encode(['status' => 0, 'txt' => t('Password validation failed')]);
            exit;
        }
        $this->session->unset_userdata('actual_password');
        
        echo json_encode(['status' => 1, 'txt' => base_url('access/login')]);
    }
    
    public function reset_password_request()
    {
        $email = $this->input->post('email');
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die(json_encode(array('status' => 0, 'txt' => t('Insert a valid e-mail address'))));
        }
        
        $user = $this->db->get_where(LOGIN_ENTITY, [LOGIN_USERNAME_FIELD => $email])->row_array();
        
        if (empty($user)) {
            die(json_encode(array('status' => 0, 'txt' => t('We\'re sorry. It seems that this e-mail address you have inserted does not exist'))));
        }
        
        $senderName = DEFAULT_EMAIL_SENDER;
        $senderMail = DEFAULT_EMAIL_SYSTEM;
        
        $userID = $user[LOGIN_ENTITY . '_id'];
        $hash = md5($userID . self::SALT);
        
        $mail_data = [
            'user_name' => $user[LOGIN_NAME_FIELD],
            'sender_name' => $senderName,
            'sender_email' => $senderMail,
            'reset_link' => base_url("access/reset_password/{$userID}/{$hash}"),
        ];
        
        $sent = $this->mail_model->send($email,'reset_password_request', '', $mail_data);
        
        if (!$sent) {
            die(json_encode(array('status' => 0, 'txt' => t('An error occurred sending the email. Try again later or contact the administration.'))));
        }
        
        $checkHash = md5($email . self::SALT);
        
        echo json_encode(array('status' => 1, 'txt' => base_url("access/recovery?sent=1&receiver={$email}&chk={$checkHash}")));
    }
    
    public function reset_password($id, $hash)
    {
        
        if ($hash !== md5($id . self::SALT)) {
            show_error(t('Unable to continue: invalid control code'));
        }
        
        $newPassword = generateRandomPassword();
        
        $user = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ENTITY . '_id' => $id))->row_array();
        $email = $user[LOGIN_USERNAME_FIELD];
        
        if (empty($user)) {
            die(json_encode(array('status' => 0, 'txt' => t('User not found.'))));
        }
        
        $password_changed = $this->auth->change_password_attempt($id, $user['users_password'], $newPassword, false);
        
        if (!$password_changed) {
            die(json_encode(array('status' => 0, 'txt' => t('An error occurred while updating your password. Try again later or contact the administration.'))));
        }
        
        $senderName = DEFAULT_EMAIL_SENDER;
        $senderMail = DEFAULT_EMAIL_SYSTEM;
        
        $mail_data = [
            'user_name' => $user[LOGIN_NAME_FIELD],
            'sender_name' => $senderName,
            'sender_email' => $senderMail,
            'new_password' => $newPassword,
            'login_link' => base_url('access/login'),
        ];
        
        $sent = $this->mail_model->send($email, 'reset_password_complete', '', $mail_data);
        
        if (!$sent) {
            die(json_encode(array('status' => 0, 'txt' => t('An error occurred while updating your password. Try again later or contact the administration.'))));
        }
        
        $this->load->view('layout/password-lost', array('pwd_resetted' => true, 'receiver' => $email));
    }
    
    public function autoLogin($userId = null, $logincode = null, $restore = 1)
    {
        //debug('test',true);
        if (!$userId or !$logincode) {
            show_error("User id or login code empty", 400);
        }
        if (!defined('LOGIN_SALT')) {
            show_error('LOGIN_SALT undefined', 403);
        } else {
            if ($logincode !== md5(LOGIN_SALT . $userId)) {
                show_error('Incorrect code', 403);
            }
            
            $current = $this->auth->get('id');
            
            // Force logout...
            $this->auth->logout();
            //$this->session->sess_destroy();
            // Force login with remember
            $this->auth->login_force($userId, true);
            
            if ($restore) {
                $this->session->set_userdata('previous_user_id', $current);
                redirect();
            } else {
                $this->session->unset_userdata('previous_user_id');
                redirect(base_url());
            }
        }
    }
    
    public function easylogin()
    {
        if ($id = $this->auth->get('id')) {
            
            $this->load->view('layout/easylogin', array('id' => $id));
        } else {
            $this->logout();
        }
    }
}
