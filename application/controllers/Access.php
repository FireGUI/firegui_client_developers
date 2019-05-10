<?php


class Access extends MY_Controller {
    
    const SALT = 'ofuh249fh97H98UG876GHOICUYEGRF98ygdfds';
    
    var $settings = NULL;

    
    function __construct() {
        parent :: __construct();
        
        $this->settings = $this->db->get('settings')->row_array();
    }
    
    
    public function index() {
        $this->login();
    }
    
    
    
    public function login() {
        
            $this->load->view('layout/login');
        
        
    }
    
    
    public function recovery() {
        $sent = $this->input->get('sent');
        $receiver = $this->input->get('receiver');
        
        if($sent && md5($receiver . self::SALT) !== $this->input->get('chk')) {
            $sent = false;
            $receiver = null;
        }
        
        $this->load->view('layout/password-lost', array('sent' => $sent, 'receiver' => $receiver));
    }
    
    
    public function logout() {
        $this->auth->logout();
        $this->session->sess_destroy();
        
        // Imposta il log di logout
        $this->apilib->logSystemAction(Apilib::LOG_LOGOUT);
        
        redirect();
    }
    
    
    
    public function login_start() {
        
        /** Servi la richiesta **/
        $data = $this->input->post();
        $remember = true;//!empty($data['remember']);
        $timeout = $data['timeout'];
        
        $data = $this->apilib->runDataProcessing(LOGIN_ENTITY, 'pre-login', $data);
        
        if(empty($data['users_users_email']) || empty($data['users_users_password'])) {
            echo json_encode(array('status'=>0, 'txt'=>'Inserisci indirizzo e-mail e password'));
            die();
        }
        
        $success = $this->auth->login_attempt($data['users_users_email'], $data['users_users_password'], $remember, $timeout);
        
        //TODO: Matteo: aggiungere gli auto right join in modo che se un utente è associato a un'altra entità (esempio: aziende che fanno login, dipendenti seven, ecc...)
        //prenda in automatico anche i dati delle entità collegate... Buttare ovviamente anche questi dati in sessione
        
        
        if($success) {
            $redirection_url = $this->auth->fetch_intended_url();
            
            if (!$redirection_url OR $redirection_url == base_url()) {
                $redirection_url = base_url('main/dashboard');
            }
            
            $this->auth->reset_intended_url();
            
            // Imposta il log di login
            $this->apilib->logSystemAction(Apilib::LOG_LOGIN);
            $output = json_encode(array( 'status'=>1, 'txt'=>$redirection_url ));
            $output = $this->apilib->runDataProcessing(LOGIN_ENTITY, 'login', $output);
            
            echo $output;
            
        } else {
            
            // Imposta il log di login
            $this->apilib->logSystemAction(Apilib::LOG_LOGIN_FAIL, ['get' => $_GET, 'post' => $_POST]);
            echo json_encode(array( 'status'=>0, 'txt'=>'E-mail o password non corrispondenti' ));
        }
    }
    
    
    public function reset_password_request() {
        $email = $this->input->post('email');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die(json_encode(array( 'status'=>0, 'txt'=> 'Inserisci un indirizzo e-mail valido.')));
        }

        $user = $this->db->get_where(LOGIN_ENTITY, [LOGIN_USERNAME_FIELD => $email])->row_array();

        if (empty($user)) {
            die(json_encode(array( 'status'=>0, 'txt'=> 'Spiacenti, l\'indirizzo inserito non è registrato.' )));
        }

        $senderName = DEFAULT_EMAIL_SENDER;
        $senderMail = DEFAULT_EMAIL_SYSTEM;

        $userID = $user[LOGIN_ENTITY . '_id'];
        $hash = md5($userID . self::SALT);



        $this->load->library('email');
        $this->email->subject('Recupero password')->to($email)->from($senderMail, $senderName);
        $msg = [
            "Ciao {$user[LOGIN_NAME_FIELD]},",
            "questa mail ti è stata inviata perché hai richiesto un reset della tua password su {$senderName}.",
            "Se non hai richiesto un reset della password ignora questa e-mail, altrimenti clicca sul link sottostante",
            base_url("access/reset_password/{$userID}/{$hash}")
        ];

        $this->email->message(implode(PHP_EOL, $msg));
        $success = $this->email->send();
        
        
        
        if (!$success) {
            die(json_encode(array( 'status'=>0, 'txt'=> 'Si è verificato un errore inviando la mail. Riprovare più tardi o contattare l\'amministrazione.' )));
        }

        $checkHash = md5($email . self::SALT);
        
        echo json_encode(array( 'status'=>1, 'txt'=>base_url("access/recovery?sent=1&receiver={$email}&chk={$checkHash}") ));
        //redirect(base_url("access/recovery?sent=1&receiver={$email}&chk={$checkHash}"));
    }

    public function reset_password($id, $hash) {

        if ($hash !== md5($id . self::SALT)) {
            show_error('Impossibile proseguire: codice di controllo non valido');
        }
        
        $newPassword = generateRandomPassword();
        $this->db->update(LOGIN_ENTITY, array(LOGIN_PASSWORD_FIELD => md5($newPassword)), array(LOGIN_ENTITY.'_id' => $id));
        $user = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ENTITY.'_id' => $id))->row_array();
        $email = $user[LOGIN_USERNAME_FIELD];
        
        $senderName = DEFAULT_EMAIL_SENDER;
        $senderMail = DEFAULT_EMAIL_SYSTEM;
        
        $this->load->library('email');
        $this->email->subject('Password cambiata')->to($email)->from($senderMail, $senderName);
        $msg = array(
            "Ciao {$user[LOGIN_NAME_FIELD]},",
            "la tua password su {$senderName} è stata cambiata.",
            "La tua nuova password è {$newPassword}"
        );

        $this->email->message(implode(PHP_EOL, $msg));
        $success = $this->email->send();

        if (!$success) {
            show_error("Errore invio mail");
        }

        redirect(base_url('access/password_resetted'));
    }
        
    public function password_resetted() {
        $this->load->view('layout/password_resetted');
    }
        
        

}

?>
