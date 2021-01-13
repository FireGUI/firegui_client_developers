<?php


class Access extends MY_Controller
{
    const SALT = 'ofuh249fh97H98UG876GHOICUYEGRF98ygdfds';

    var $settings = NULL;


    function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin: *');
        if (!empty($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
            @header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"); //X-Requested-With
        }

        $this->settings = $this->db->get('settings')->row_array();
    }


    public function index()
    {
        $this->login();
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

        $this->load->view('layout/change-password');
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

        if (empty($data['users_users_email']) || empty($data['users_users_password'])) {
            echo json_encode(array('status' => 0, 'txt' => t('Insert a valid email and/or password')));
            die();
        }

        $success = $this->auth->login_attempt($data['users_users_email'], $data['users_users_password'], $remember, $timeout);

        //TODO: aggiungere gli auto right join in modo che se un utente è associato a un'altra entità (esempio: aziende che fanno login, dipendenti seven, ecc...)
        //prenda in automatico anche i dati delle entità collegate... Buttare ovviamente anche questi dati in sessione
        if ($success) {
            $redirection_url = $this->auth->fetch_intended_url();

            if (!$redirection_url or $redirection_url == base_url()) {
                $redirection_url = base_url('main/dashboard');
            }

            $this->auth->reset_intended_url();

            // Imposta il log di login
            $this->apilib->logSystemAction(Apilib::LOG_LOGIN);
            $output = json_encode(array('status' => 1, 'txt' => $redirection_url));
            $output = $this->apilib->runDataProcessing(LOGIN_ENTITY, 'login', $output);
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

        $this->load->library('email');
        $this->email->subject('Recupero password')->to($email)->from($senderMail, $senderName);

        $msg = [
            t("Hi, %s", 0, [$user[LOGIN_NAME_FIELD]]),
            t("this email was sent to you because you requested a reset of your password on %s.", 0, [$senderName]),
            t("If you have not requested a password reset, ignore this email, otherwise click on the link below"),
            "<a href='" . base_url("access/reset_password/{$userID}/{$hash}") . "'>" . base_url("access/reset_password/{$userID}/{$hash}") . "</a>"
        ];

        $this->email->message(implode(PHP_EOL, $msg));
        $success = $this->email->send();
        $this->email->print_debugger();

        if (!$success) {
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
        $this->db->update(LOGIN_ENTITY, array(LOGIN_PASSWORD_FIELD => md5($newPassword)), array(LOGIN_ENTITY . '_id' => $id));
        $user = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ENTITY . '_id' => $id))->row_array();
        $email = $user[LOGIN_USERNAME_FIELD];

        $senderName = DEFAULT_EMAIL_SENDER;
        $senderMail = DEFAULT_EMAIL_SYSTEM;

        $this->load->library('email');
        $this->email->subject(t('Password changed'))->to($email)->from($senderMail, $senderName);
        $msg = array(
            t("Hi, %s", 0, [$user[LOGIN_NAME_FIELD]]),
            t("your password on %s has been changed", 0, [$senderName]),
            '<br/>',
            t("Your new password is %s ", 0, [$newPassword]),
            '<br/>',
            t("You can now login by clicking link below:"),
            '<br/>',
            "<a href='" . base_url('access/login') . "'>" . base_url('access/login') . "</a>"
        );

        $this->email->message(implode(PHP_EOL, $msg));
        $success = $this->email->send();

        if (!$success) {
            show_error("Errore invio mail");
        }

        $this->load->view('layout/password-lost', array('pwd_resetted' => true, 'receiver' => $email));
    }
}
