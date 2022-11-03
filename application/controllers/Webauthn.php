<?php

class Webauthn extends MY_Controller
{

    public $WebAuthn = null;
    public $userVerification = 'required';
    public $requireResidentKey = true;
    public $typeUsb = false;
    public $typeNfc = true;
    public $typeBle = true;
    public $typeInt = true;

    public function __construct()
    {
        parent::__construct();
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) {
            // $this->output->cache(0);
        }
        // Controllo anche la current uri

        $server_name = explode('://', base_url())[1];
        $server_name = explode('/', $server_name)[0];

        // $formats = [];
        // $formats[] = 'android-key';
        // $formats[] = 'android-safetynet';
        // $formats[] = 'apple';
        // $formats[] = 'fido-u2f';
        // $formats[] = 'none';
        // $formats[] = 'packed';
        // $formats[] = 'tpm';
        $formats = ['none'];

        $this->WebAuthn = new lbuchs\WebAuthn\WebAuthn('WebAuthn Library', $server_name, $formats);
    }

    public function getCreateArgs()
    {
        $post = trim(file_get_contents('php://input'));

        if ($post) {
            $post = json_decode($post, true);
        }

        //debug($post, true);

        $crossPlatformAttachment = null;
        if (($this->typeUsb || $this->typeNfc || $this->typeBle) && !$this->typeInt) {
            $crossPlatformAttachment = true;
        } else if (!$this->typeUsb && !$this->typeNfc && !$this->typeBle && $this->typeInt) {
            $crossPlatformAttachment = false;
        }
        //Force false for beta testing
        $crossPlatformAttachment = false;

        $createArgs = $this->WebAuthn->getCreateArgs($post['id'], $post['email'], $post['display_name'], 20, $this->requireResidentKey, $this->userVerification, $crossPlatformAttachment);

        //debug($createArgs, true);

        // save challange to session. you have to deliver it to processGet later.
        //$_SESSION['challenge'] = $this->WebAuthn->getChallenge();

        $this->session->set_userdata(SESS_WEBAUTHN, $this->WebAuthn->getChallenge());

        e_json($createArgs);
    }

    public function getGetArgs()
    {

        $post = trim(file_get_contents('php://input'));

        if ($post) {
            $post = json_decode($post, true);
        }
        $email = $post['email'];
        $users = $this->apilib->search(LOGIN_ENTITY, [LOGIN_USERNAME_FIELD => $email]);

        $ids = array();
        foreach ($users as $user) {
            $reg = json_decode($user['users_webauthn_data']);
            //debug($reg, true);
            $ids[] = base64_decode($reg->credentialId);
        }

        //debug($ids);
        if (count($ids) === 0) {
            throw new Exception('User not authorized');
        }

        $getArgs = $this->WebAuthn->getGetArgs($ids, 20, $this->typeUsb, $this->typeNfc, $this->typeBle, $this->typeInt, $this->userVerification);
        $this->session->set_userdata(SESS_WEBAUTHN, $this->WebAuthn->getChallenge());
        e_json($getArgs);
    }

    public function processCreate()
    {
        $post = trim(file_get_contents('php://input'));
        if ($post) {
            $post = json_decode($post);
        }
        //debug($post, true);

        $clientDataJSON = base64_decode($post->clientDataJSON);
        $attestationObject = base64_decode($post->attestationObject);
        $challenge = $this->session->userdata(SESS_WEBAUTHN);

        // processCreate returns data to be stored for future logins.
        // in this example we store it in the php session.
        // Normaly you have to store the data in a database connected
        // with the user name.
        $data = $this->WebAuthn->processCreate($clientDataJSON, $attestationObject, $challenge, $this->userVerification === 'required', true, false);

        $data->credentialId = base64_encode($data->credentialId);

        $user = $this->apilib->edit(LOGIN_ENTITY, $this->auth->get('id'), [
            LOGIN_WEBAUTHN_DATA => json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE),
        ]);

        $msg = 'registration success.';
        if ($data->rootValid === false) {
            $msg = 'registration ok, but certificate does not match any of the selected root ca.';
        }

        $return = new stdClass();
        $return->success = true;
        $return->msg = $msg;
        $return->data = json_encode([
            'id' => $user[LOGIN_ENTITY . '_id'],
            'email' => $user[LOGIN_USERNAME_FIELD],
            'display_name' => $user[LOGIN_NAME_FIELD],
        ]);
        e_json($return);
    }

    public function processGet()
    {
        $post = trim(file_get_contents('php://input'));
        if ($post) {
            $post = json_decode($post);
        }
        $clientDataJSON = base64_decode($post->clientDataJSON);
        $authenticatorData = base64_decode($post->authenticatorData);
        $signature = base64_decode($post->signature);
        $id = base64_decode($post->id);
        $credentialId = $post->id;
        $challenge = $this->session->userdata(SESS_WEBAUTHN);
        $credentialPublicKey = null;
        $email = $post->email;

        $query = "SELECT * FROM " . LOGIN_ENTITY . " WHERE JSON_EXTRACT(" . LOGIN_WEBAUTHN_DATA . ', \'$.credentialId\') = \'' . $credentialId . '\'';
        try { //Run with try catch beacause JSON_EXTRACT function available only from MariaDB 10.3.*
            $user = $this->db->query($query);
        } catch (Exception $e) {
            $user = false;
            $db_error = $this->db->error();
        }

        if ($user === false || !empty($db_error)) { //If not supported, try a simple match with the credentialId and username
            //DB does not support JSON data
            $sql_escape_credential_id = str_ireplace('/', '%', $credentialId);
            $query = "SELECT * FROM " . LOGIN_ENTITY . " WHERE " . LOGIN_USERNAME_FIELD . " = '$email' AND " . LOGIN_WEBAUTHN_DATA . " LIKE '%{$sql_escape_credential_id}%'";

            $user = $this->db->query($query);
        }

        if ($user->num_rows() != 1) {
            throw new Exception('Public Key for credential ID not found!');
        } else {
            $user_data = $user->row_array();
            $json_data = $user_data[LOGIN_WEBAUTHN_DATA];
            $credentialPublicKey = json_decode($json_data)->credentialPublicKey;

            // process the get request. throws WebAuthnException if it fails
            $this->WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, null, $this->userVerification === 'required');

            //Force login
            $this->auth->login_force($user_data[LOGIN_ENTITY . '_id']);

            $return = new stdClass();
            $return->success = true;
            e_json($return);
        }
    }
}
