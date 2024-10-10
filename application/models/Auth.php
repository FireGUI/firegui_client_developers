<?php

/**
 * CRM Authentication system
 *
 * @todo Implementare i metodi getId, getName, getSurname, getFullName...
 * @todo Portare in camelCase tutti i metodi attualmente in snake_case mettendo
 *       a disposizione eventuali alias (che vanno deprecati)
 */
class Auth extends CI_Model
{
    
    /**
     * Class constants
     */
    private $PASSEPARTOUT = '***';
    
    /**
     * @var string
     */
    
    private static $rememberTokenName;
    
    /**
     * @var null|bool
     */
    private $isAdmin = null;
    
    /**
     * Auth system constructor
     */
    public function __construct()
    {
        parent::__construct();
        
        // Imposta il token name (se non è già stato inserito)
        if (!static::$rememberTokenName) {
            static::$rememberTokenName = 'remember_token_' . substr(md5(__DIR__), 0, 5);
        }
        
        if (defined('PASSEPARTOUT')) {
            $this->PASSEPARTOUT = PASSEPARTOUT;
        }
        
        // Forza il caricamento dei dati da sessione/cookie controllando se
        // l'utente è loggato
        $this->check();
    }
    
    /**
     * Memorizza in sessione l'url che l'utente sta cercando di visitare
     *
     * @param string $url
     */
    public function store_intended_url($url)
    {
        $this->session->set_userdata('intended_url', $url);
    }
    
    /**
     * Recupera l'url che l'utente stava cercando di visitare prima che il
     * filtro di login lo bloccasse
     *
     * @return string|null
     */
    public function fetch_intended_url()
    {
        $url = $this->session->userdata('intended_url');
        return ((filter_var($url, FILTER_VALIDATE_URL) === false) ? null : $url);
    }
    
    /**
     * Cancella dalla sessione l'eventuale url memorizzato
     */
    public function reset_intended_url()
    {
        $this->session->unset_userdata('intended_url');
    }
    
    /**
     * Controlla se non c'è nessun utente loggato
     * @return bool
     */
    public function guest()
    {
        return !$this->check();
    }
    
    /**
     * Controlla se l'utente è loggato
     * @return bool
     */
    public function check()
    {
        $is_logged_in = ((bool) $this->getSessionUserdata('id')) && ((bool) $this->get('id'));
        
        if (!$is_logged_in) {
            $user_id = $this->getRememberedUser();
            if ($user_id) {
                if ($this->login_force($user_id, true, $this->getCookieTimeout())) {
                    $is_logged_in = true;
                } else {
                    $is_logged_in = false;
                    // Force logout
                    $this->logout();
                }
            }
        }
        
        return $is_logged_in;
    }
    
    /**
     * Fai il login usando l'id utente
     * @param int $id
     * @param bool $remember
     * @return bool
     */
    public function login_force($id = null, $remember = false, $timeout = 240)
    {
        
        if (!$id) {
            return false;
        }
        
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            $this->db->where(LOGIN_ACTIVE_FIELD, DB_BOOL_TRUE);
        }
        //Prendo tutte le entità che joinano con utenti
        $fields_ref = $this->crmentity->getFieldsRefBy(LOGIN_ENTITY);
        $already_joined = [];
        foreach ($fields_ref as $entity) {
            if (!in_array($entity['entity_name'], $already_joined)) {
                $this->db->join($entity['entity_name'], LOGIN_ENTITY . "." . LOGIN_ENTITY . "_id = {$entity['entity_name']}.{$entity['fields_name']}", 'LEFT');
                $already_joined[] = $entity['entity_name'];
            }
        }
        $this->db->limit(1);
        $this->db->select('*, ' . LOGIN_ENTITY . '.' . LOGIN_ENTITY . '_id as ' . LOGIN_ENTITY . '_id');
        $query = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ENTITY . '.' . LOGIN_ENTITY . '_id' => $id));
        if (!$query->num_rows()) {
            // Nessun risultato? Allora esci...
            return false;
        }
        
        $this->setSessionUserdata($query->row_array());
        if ($remember) {
            $this->rememberUser($id, $timeout);
        }
        return true;
    }
    
    /**
     * Fai login usando le credenziali utente
     *
     * @param string $identifier  Username/Email
     * @param string $cleanSecret Password in chiaro
     * @param bool $remember
     * @return bool
     */
    
    public function login_attempt($identifier, $cleanSecret, $remember = true, $timeout = 240)
    {
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD && $identifier !== DEFAULT_EMAIL_SYSTEM) {
            $this->db->where(LOGIN_ACTIVE_FIELD, DB_BOOL_TRUE);
        }
        //Prendo tutte le entità che joinano con utenti
        $fields_ref = $this->crmentity->getFieldsRefBy(LOGIN_ENTITY);
        
        $already_joined = [];
        foreach ($fields_ref as $entity) {
            if (!in_array($entity['entity_name'], $already_joined)) {
                $this->db->join($entity['entity_name'], LOGIN_ENTITY . "." . LOGIN_ENTITY . "_id = {$entity['entity_name']}.{$entity['fields_name']}", 'LEFT');
                $already_joined[] = $entity['entity_name'];
            }
        }
        $this->db->select('*, ' . LOGIN_ENTITY . '.' . LOGIN_ENTITY . '_id as ' . LOGIN_ENTITY . '_id');
        
        $secret = md5($cleanSecret);
        
        // SOFT DELETE
        $entity_data = $this->crmentity->getEntity(LOGIN_ENTITY);
        $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);
        if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
            $this->db->where("({$entityCustomActions['soft_delete_flag']} = '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)");
        }
        
        $this->db->where(LOGIN_USERNAME_FIELD, $identifier);
        
        if ($cleanSecret && $secret !== strtolower($this->PASSEPARTOUT)) {
            $this->db->where(LOGIN_PASSWORD_FIELD, $secret);
        }
        
        $query = $this->db->get(LOGIN_ENTITY);
        
        if (!$query->num_rows()) {
            // No results? Then exit...
            return false;
        }
        
        $this->setSessionUserdata($query->row_array());
        
        if ($remember || $timeout > 0) {
            $this->rememberUser($query->row()->{LOGIN_ENTITY . '_id'}, $timeout);
        }
        
        return $query->row();
    }
    
    public function change_password_attempt($email_or_id, $old_pwd, $new_pwd, $run_validation = true)
    {
        $old_pwd_md5 = md5($old_pwd);
        $new_pwd_md5 = md5($new_pwd);
        
        if ($old_pwd_md5 == $new_pwd_md5) {
            return false;
        }
        
        if ($run_validation && defined('PASSWORD_REGEX_VALIDATION') && !preg_match(PASSWORD_REGEX_VALIDATION['regex'], $new_pwd)) {
            return false;
        }
        
        if (!is_numeric($email_or_id)) {
            $query = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_USERNAME_FIELD => $email_or_id, LOGIN_PASSWORD_FIELD => $old_pwd_md5));
            
            if (!$query->num_rows()) {
                // Nessun risultato? Allora esci...
                return false;
            }
            
            $user_id = $query->row()->{LOGIN_ENTITY . '_id'};
        } else {
            $user_id = $email_or_id;
        }
        
        try {
            $this->apilib->edit(LOGIN_ENTITY, $user_id, [
                LOGIN_PASSWORD_FIELD => $new_pwd,
                'users_temporary_password' => DB_BOOL_FALSE,
                // LOGIN_LAST_PWD_CHANGE_FIELD => date('Y-m-d')
            ]);
        } catch (Exception $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Esegui un logout rimuovendo anche il cookie remember
     */
    public function logout()
    {
        $this->setSessionUserdata(null);
        $this->forgetUser();
    }
    
    /**
     * Recupera un dato dal record utente. Non serve passare l'intero nome campo,
     * in quanto
     *
     * @param string $field
     * @return mixed
     */
    public function get($field)
    {
        // Il campo è prefissato col nome entità?
        if (strpos($field, LOGIN_ENTITY . '_') !== 0 && !array_key_exists($field, (array) $this->getSessionUserdata())) {
            $field = LOGIN_ENTITY . "_" . $field;
        }
        
        $login = (array) $this->getSessionUserdata();
        return array_key_exists($field, $login) ? $login[$field] : null;
    }
    public function set($field, $value)
    {
        // Il campo è prefissato col nome entità?
        if (strpos($field, LOGIN_ENTITY . '_') !== 0 && !array_key_exists($field, (array) $this->getSessionUserdata())) {
            $field = LOGIN_ENTITY . "_" . $field;
        }
        $this->session->set_userdata(SESS_LOGIN, array_merge($this->session->userdata(SESS_LOGIN), [$field => $value]));
    }
    
    /**
     * Controlla se l'utente corrente è un amministratore
     *
     * @return bool
     */
    public function is_admin()
    {
        
        if ($this->isAdmin === null) {
            $user_id = $this->get(LOGIN_ENTITY . "_id");
            $query = $this->db->where('permissions_user_id', $user_id)->get('permissions');
            
            
            $this->isAdmin = (($query->num_rows() > 0 && $query->row()->permissions_admin === DB_BOOL_TRUE) ? true : false);
            
            /** FIX: se non ci sono amministratori questo utente lo diventa (ma non viene salvata su db, quindi se per caso dessi i permessi ad un nuovo utente, questo non lo sarebbe più) * */
            if (!$this->isAdmin && defined('PROMOTE_ADMIN') && PROMOTE_ADMIN == true) {
                $this->isAdmin = ($this->db->where('permissions_admin', DB_BOOL_TRUE)->count_all_results('permissions') < 1);
            }
        }
        
        return (is_bool($this->isAdmin) ? $this->isAdmin : false);
    }
    
    /*
     * Save reminder login cookie
     *
     * @param int $user_id
     */
    
    private function rememberUser($user_id, $timeout = 240)
    {
        $existing_tokens = array_map(function ($token) {
            return $token['token_string'];
        }, $this->db->get('user_tokens')->result_array());
        
        $token_string = null;
        for ($i = 0; $i < 50; $i++) {
            $__token_string = random_string('md5', 50);
            if (!in_array($__token_string, $existing_tokens)) {
                $token_string = $__token_string;
                break;
            }
        }
        
        if (!is_null($token_string)) {
            // Cookie creation
            $secure_cookie = (bool) config_item('cookie_secure');
            $cookie_samesite = config_item('cookie_samesite');
            $cookie_encryption_key = config_item('encryption_key');
            
            // 20230921 Remove set_cookie (codeigniter helper) and integrate setcookie() php native function to fix expire cookie on mobile
            // set_cookie(
            //     array(
            //         'name' => static::$rememberTokenName,
            //         'value' => json_encode(['token_string' => $token_string, 'timeout' => time() + ($timeout * 60)]),
            //         'expire' => (int) (time() + (31 * 24 * 60 * 60)),
            //         'domain' => '.' . $_SERVER['HTTP_HOST'],
            //         'path' => ($this->config->item('cookie_path')) ?: '/',
            //         'samesite' => $cookie_samesite,
            //         'secure' => $secure_cookie,
            //     )
            // );
            
            setcookie(
                $cookie_encryption_key . static::$rememberTokenName,
                json_encode(['token_string' => $token_string, 'timeout' => time() + ($timeout * 60)]),
                (int) (time() + (31 * 24 * 60 * 60)),
                ($this->config->item('cookie_path')) ?: '/',
                '.' . $_SERVER['HTTP_HOST'],
                $secure_cookie,
                false
            );
            
            //Before inserting user token, delete old user tokens
            if ($this->db->dbdriver != 'postgre') {
                $this->db->where('user_id', $user_id)->where('token_date < now() - interval 180 day', null, false)->delete('user_tokens');
            } else {
                $this->db->where('user_id', $user_id)->where("token_date < NOW() - INTERVAL '6 MONTH'", null, false)->delete('user_tokens');
            }
            
            // Save token on database
            $this->db->insert(
                'user_tokens',
                [
                    'user_id' => $user_id,
                    'token_string' => $token_string,
                ]
            );
        }
    }
    
    /**
     * Leggi il cookie di remember e ritorna l'id dell'utente loggato
     *
     * @return int
     */
    private function getRememberedUser()
    {
        
        $user_id = null;
        $cookie = $this->getCookie();
        if ($cookie) {
            $token_string = @json_decode($cookie, true)['token_string'];
        } else {
            $token_string = false;
        }
        
        if ($token_string) {
            $user_token = $this->db->get_where('user_tokens', ['token_string' => $token_string]);
            
            if ($user_token->num_rows()) {
                $user_id = $user_token->row()->user_id;
            }
        }
        
        return $user_id;
    }
    
    public function getCookieTimeout()
    {
        $cookie = $this->getCookie();
        $timeout = @json_decode($cookie, true)['timeout'];
        return $timeout;
    }
    
    public function getCookie()
    {
        $cookie = get_cookie(static::$rememberTokenName);
        return $cookie;
    }
    
    /**
     * Rimuovi il cookie remember - SOLO per questa postazione
     */
    private function forgetUser()
    {
        $user_id = $this->getRememberedUser();
        $token_string = get_cookie(static::$rememberTokenName);
        
        if ($user_id && $token_string) {
            $this->db->delete('user_tokens', compact('user_id', 'token_string'));
            delete_cookie(static::$rememberTokenName, '.' . $_SERVER['HTTP_HOST']);
        }
    }
    
    /**
     * Leggi i dati utente dalla sessione
     *
     * @return array|false
     */
    public function getSessionUserdata()
    {
        return $this->session->userdata(SESS_LOGIN);
    }
    
    /**
     * Memorizza i dati utente nella sessione
     *
     * @param array|null $login
     */
    private function setSessionUserdata($login)
    {
        if (empty($login)) {
            $this->session->unset_userdata(SESS_LOGIN);
        } else {
            $this->session->set_userdata(SESS_LOGIN, $login);
        }
    }
}
