<?php

class Webauthn extends MY_Controller
{


    var $WebAuthn = NULL;
    var $userVerification = 'required';
    var $requireResidentKey = true;
    var $typeUsb = true;
    var $typeNfc = true;
    var $typeBle = true;
    var $typeInt = true;

    function __construct()
    {
        parent::__construct();
        //$this->output->cache(20);
        // Controllo anche la current uri

        $server_name = explode('://', base_url())[1];
        $server_name = explode('/', $server_name)[0];

        $formats = [];
        $formats[] = 'android-key';
        $formats[] = 'android-safetynet';
        $formats[] = 'apple';
        $formats[] = 'fido-u2f';
        $formats[] = 'none';
        $formats[] = 'packed';
        $formats[] = 'tpm';

        $this->WebAuthn = new lbuchs\WebAuthn\WebAuthn('WebAuthn Library', $server_name, $formats);
        // Imposta il log di accesso giornaliero

    }

    public function getCreateArgs()
    {


        $crossPlatformAttachment = null;
        $createArgs = $this->WebAuthn->getCreateArgs('demo', 'demo', 'Demo Demolin', 20, $this->requireResidentKey, $this->userVerification, $crossPlatformAttachment);

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
            $reg = json_decode($user['users_webauthn_data'], null, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            //debug($reg, true);
            $ids[] = base64_decode($reg->credentialId);
        }

        if (count($ids) === 0) {
            throw new Exception('no registrations in session.');
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
            LOGIN_WEBAUTHN_DATA => json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE)
        ]);



        $msg = 'registration success.';
        if ($data->rootValid === false) {
            $msg = 'registration ok, but certificate does not match any of the selected root ca.';
        }

        $return = new stdClass();
        $return->success = true;
        $return->msg = $msg;
        $return->email = $user[LOGIN_USERNAME_FIELD];
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
        $challenge = $this->session->userdata(SESS_WEBAUTHN);
        $credentialPublicKey = null;
        debug($post);
        die('COMPLETARE! :D');


        // looking up correspondending public key of the credential id
        // you should also validate that only ids of the given user name
        // are taken for the login.
        if (is_array($_SESSION['registrations'])) {
            foreach ($_SESSION['registrations'] as $reg) {
                if ($reg->credentialId === $id) {
                    $credentialPublicKey = $reg->credentialPublicKey;
                    break;
                }
            }
        }

        if ($credentialPublicKey === null) {
            throw new Exception('Public Key for credential ID not found!');
        }

        // process the get request. throws WebAuthnException if it fails
        $WebAuthn->processGet($clientDataJSON, $authenticatorData, $signature, $credentialPublicKey, $challenge, null, $userVerification === 'required');

        $return = new stdClass();
        $return->success = true;
        print(json_encode($return));
    }
}
