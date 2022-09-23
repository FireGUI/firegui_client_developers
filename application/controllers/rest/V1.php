<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class V1 extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        // Imposto l'apilib:
        // ---
        // 1)   L'apilib deve funzionare in modalità API per quanto riguarda i
        //      post-process
        // 2)   Annullo tutte le eventuali impostazioni sulla lingua perché qua
        //      non devo lavorare con le sessioni, ma con quello che gli passo
        //      nell'url: api/it-it/index/{entity}
        $this->apilib->setProcessingMode(Apilib::MODE_API_CALL);
        $this->apilib->setLanguage();

        header('Access-Control-Allow-Origin: *');
        @header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}"); //X-Requested-With

        if ($this->uri->segment(3) != 'help') {

            $token_data = $this->db->get_where('api_manager_tokens', ['api_manager_tokens_token' => '' . $this->getBearerToken()]);
            if ($token_data->num_rows() == 0) {
                $this->showError("Invalid bearer '" . $this->getBearerToken() . "'.", 1, 404);
                die();
            } else {
                $token = $token_data->row();
                $this->token_id = $token->api_manager_tokens_id;
                $this->token = $token->api_manager_tokens_token;

                //Aggiorno i conteggi
                $this->db->where('api_manager_tokens_id', $this->token_id)->update('api_manager_tokens', [
                    'api_manager_tokens_last_use_date' => date('Y-m-d H:m:s'),
                    'api_manager_tokens_requests' => (int) ($token->api_manager_tokens_requests) + 1,
                ]);
            }
        }

        // Niente profiler in API
        $this->output->enable_profiler(false);
    }

    private function check_request($method, $params)
    {
        // Controlla nuovamente se il metodo esiste (potrebbe essere cambiato
        // nell'if precedente)
        if (!method_exists($this, $method)) {
            $this->showError("Method unallowed", 1, 404);
            die();
        }

        //Verifico il codice di controllo
        // Disabled for v1.0
        switch ($method) {
            case 'index':
                $entity = @$params[0];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (!$this->checkEntityPermission($entity, 'R')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }

                break;
            case 'view':
                $entity = @$params[0];
                $id = @$params[1];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (empty($id)) {
                    $this->showError("Missing id param!");
                    exit;
                }
                if (!$this->checkEntityPermission($entity, 'R')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }
                break;
            case 'help':
            case 'entities':
            case 'describe':
                break;
            case 'create':
                $entity = @$params[0];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (!$this->checkEntityPermission($entity, 'I')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }
                break;
            case 'edit':
                $entity = @$params[0];
                $id = @$params[1];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (empty($id)) {
                    $this->showError("Missing id param!");
                    exit;
                }

                if (!$this->checkEntityPermission($entity, 'U')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }
                break;
            case 'delete':
                $entity = @$params[0];
                $id = @$params[1];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (empty($id)) {
                    $this->showError("Missing id param!");
                    exit;
                }
                if (!$this->checkEntityPermission($entity, 'D')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }
                break;
            case 'search':
                $entity = @$params[0];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (!$this->checkEntityPermission($entity, 'R')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }
                break;

            case 'count':
                $entity = @$params[0];
                if (empty($entity)) {
                    $this->showError("Missing entity param!");
                    exit;
                }
                if (!$this->checkEntityPermission($entity, 'R')) {
                    $this->showError("Permission denied on entity $entity!");
                    exit;
                }
                break;
            case 'login':

                break;
            default:
                $this->showError("Unrecognized method '$method'.", 1, 404);
                die();
                break;
        }
    }

    public function _remap($method, $params = [])
    {

        // Se il metodo non esiste allora provo ad utilizzare il chunk
        // corrispondente come segmento lingua
        if (!method_exists($this, $method)) {

            $lang = $this->datab->findLanguage($method);
            if ($lang) {
                $fallbackLang = $this->datab->getDefaultLanguage();
                $this->apilib->setLanguage($lang['id'], $fallbackLang['id']);

                // Ok, primo segmento lingua quindi il successivo è il metodo
                $method = array_shift($params);
            }
        }

        // Ho rimesso questo perchè non faceva più il controllo d'accesso all'entità/fields...
        $this->check_request($method, $params);

        // A questo punto posso chiamare
        call_user_func_array([$this, $method], $params);
    }

    // ==========================================
    // Rest actions
    // ==========================================

    /**
     * Mostra una lista di record dell'entità richiesta
     * @param string $entity    Il nome dell'entità
     * @param string $depth     Profondità relazioni
     */
    public function index($entity = null, $depth = 2)
    {
        $where = $this->getEntityWhere($entity);

        try {
            $output = $this->apilib->search($entity, $where, null, 0, null, 'ASC', $depth);
            $this->filterOutputFields($entity, $output);
            $this->logAction(__FUNCTION__, func_get_args(), $output);
            $this->showOutput($output);
        } catch (ApiException $e) {
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Ritorna un json con tutti i dati di una determinata entità
     * @param string $entity
     * @param int $id
     * @param int $depth
     */
    public function view($entity = null, $id = null, $depth = 2)
    {
        $where = implode(' AND ', array_filter([$this->getEntityWhere($entity), $entity . '.' . $entity . "_id = '{$id}'"]));
        try {
            $_output = $this->apilib->search($entity, $where, null, 0, null, 'ASC', $depth);
            $this->filterOutputFields($entity, $_output);
            $output = $_output[0];
            $this->logAction(__FUNCTION__, func_get_args(), $output);
            $this->showOutput($output);
        } catch (ApiException $e) {

            /** Salvo su log il database error nascondendolo all'utente */
            if ($e->getCode() == Apilib::ERR_INTERNAL_DB) {
                $this->logAction(__FUNCTION__, func_get_args(), $e->getPrevious()->getMessage());
            }

            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Crea un nuovo record con il post passato e lo ritorna via json
     * @param string $entity
     */
    public function create($entity = null, $output = 'json')
    {
        //Se sono arrivato qua, ho già fatto i controlli che possa scrivere su questa entità
        //Devo quindi solo controllare che i dati passati siano tutti accessibili in inserimento
        try {
            if ($this->checkFieldsPermissions($entity, 'I')) {

                $_outputData = [$this->apilib->create($entity)];
                $this->filterOutputFields($entity, $_outputData);
                $outputData = $_outputData;
                $this->logAction(__FUNCTION__, func_get_args(), $outputData);
                $this->buildOutput($output, $outputData);
            }
        } catch (ApiException $e) {
            $this->logAction(__FUNCTION__, func_get_args());
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Fa l'update del record con id passato aggiornando i dati da post
     * Ritorna i nuovi dati via json
     * @param string $entity
     */
    public function edit($entity = null, $id = null, $output = 'json')
    {
        //Se sono arrivato qua, ho già fatto i controlli che possa scrivere su questa entità
        //Devo quindi solo controllare che i dati passati siano tutti accessibili in update
        try {
            if ($this->checkFieldsPermissions($entity, 'U')) {
                //Verifico di poter accedere a questo record prima di consentire un update...
                if ($this->getEntityWhere($entity)) {
                    $where = implode(' AND ', array_filter([$this->getEntityWhere($entity), $entity . '.' . $entity . "_id = '{$id}'"]));
                } else {
                    $where = $entity . '.' . $entity . "_id = '{$id}'";
                }

                $check = $this->apilib->search($entity, $where, null, 0, null, 'ASC', 1);
                if (count($check) == 1) {
                    $_outputData = [$this->apilib->edit($entity, $id)];
                    $this->filterOutputFields($entity, $_outputData);
                    $outputData = $_outputData;
                    $this->logAction(__FUNCTION__, func_get_args(), $outputData);
                    $this->buildOutput($output, $outputData);
                } else {
                    $this->showError("Permission denied on record {$entity}.{$entity}_id = '{$id}'", 1, 404);
                }
            }
        } catch (ApiException $e) {
            $this->logAction(__FUNCTION__, func_get_args());
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    public function search($entity = null)
    {
        try {
            $limit = ($this->input->post('limit')) ? $this->input->post('limit') : null;
            $offset = ($this->input->post('offset')) ? $this->input->post('offset') : 0;
            $orderBy = ($this->input->post('orderby')) ? $this->input->post('orderby') : null;

            $orderDir = ($this->input->post('orderdir')) ? $this->input->post('orderdir') : 'ASC';
            $maxDepth = ($this->input->post('maxdepth')) ? $this->input->post('maxdepth') : 1;

            $postData = array_filter((array) $this->input->post('where'));
            if ($this->getEntityWhere($entity)) {
                $where = array_filter([$this->getEntityWhere($entity)]);
            } else {
                $where = [];
            }

            $postData = $this->apilib->runDataProcessing($entity, 'pre-search', $postData);

            //non uso le apilib altrimenti mi fa left join e non è detto che abbia i permessi per le altre entità... una soluzione potrebbe essere quella di ciclare tutti i permessi e rimuovere nella
            ////filterOutputFields anche le tabelle joinate, ma è un lavorone... per ora no
            $output = $this->apilib->search($entity, array_merge($where, $postData), $limit, $offset, $orderBy, $orderDir, $maxDepth);

            $this->filterOutputFields($entity, $output);

            $this->logAction(__FUNCTION__, func_get_args(), $output);
            $this->showOutput($output);
        } catch (ApiException $e) {

            /** Salvo su log il database error nascondendolo all'utente */
            if ($e->getCode() == Apilib::ERR_INTERNAL_DB) {
                $this->logAction(__FUNCTION__, func_get_args(), $e->getPrevious()->getMessage());
            }

            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    public function count($entity = null)
    {
        try {
            $limit = ($this->input->post('limit')) ? $this->input->post('limit') : null;
            $offset = ($this->input->post('offset')) ? $this->input->post('offset') : 0;
            $orderBy = ($this->input->post('orderby')) ? $this->input->post('orderby') : null;

            $orderDir = ($this->input->post('orderdir')) ? $this->input->post('orderdir') : 'ASC';
            $maxDepth = ($this->input->post('maxdepth')) ? $this->input->post('maxdepth') : 1;

            $postData = array_filter((array) $this->input->post('where'));
            if ($this->getEntityWhere($entity)) {
                $where = array_filter([$this->getEntityWhere($entity)]);
            } else {
                $where = [];
            }

            $postData = $this->apilib->runDataProcessing($entity, 'pre-search', $postData);

            //non uso le apilib altrimenti mi fa left join e non è detto che abbia i permessi per le altre entità... una soluzione potrebbe essere quella di ciclare tutti i permessi e rimuovere nella
            ////filterOutputFields anche le tabelle joinate, ma è un lavorone... per ora no
            $output = $this->apilib->count($entity, array_merge($where, $postData));

            //$this->filterOutputFields($entity, $output);

            $this->logAction(__FUNCTION__, func_get_args(), $output);
            $this->showOutput(['count' => $output]);
        } catch (ApiException $e) {

            /** Salvo su log il database error nascondendolo all'utente */
            if ($e->getCode() == Apilib::ERR_INTERNAL_DB) {
                $this->logAction(__FUNCTION__, func_get_args(), $e->getPrevious()->getMessage());
            }

            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Crea record multipli da post
     * @param string $entity
     * @param string $output
     */
    public function create_many($entity = null, $output = 'json')
    {

        try {
            $outputData = $this->apilib->createMany($entity, $this->input->post());
            $this->logAction(__FUNCTION__, func_get_args(), $outputData);
            $this->buildOutput($output, $outputData);
        } catch (ApiException $e) {
            $this->logAction(__FUNCTION__, func_get_args());
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Cancella il record selezionate
     * @param string $entity
     */
    public function delete($entity = null, $id = null, $output = 'json')
    {
        try {
            $this->apilib->delete($entity, $id);
            $this->logAction(__FUNCTION__, func_get_args(), array());
            $this->buildOutput($output, array());
        } catch (ApiException $e) {
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    public function login($entity = null)
    {
        if (!$entity) {
            $entity = LOGIN_ENTITY;
        }

        $postData = array_filter((array) $this->input->post());
        $getData = array_filter((array) $this->input->get());
        $data = array_merge($getData, $postData);

        //Login password and mail are mandatory
        if (!array_key_exists(LOGIN_PASSWORD_FIELD, $data) || !array_key_exists(LOGIN_USERNAME_FIELD, $data)) {
            $this->showError("Login fallito", Apilib::ERR_GENERIC, 200);
            exit;
        }

        // If defined a login active field, force where condition
        if (defined('LOGIN_ACTIVE_FIELD') && !empty(LOGIN_ACTIVE_FIELD)) {
            $data[LOGIN_ACTIVE_FIELD] = DB_BOOL_TRUE;
        }

        try {

            if ($entity) {
                $data = $this->apilib->runDataProcessing($entity, 'pre-login', $data);
            }

            $unprocessedOutput = $this->apilib->searchFirst($entity, $data, 0, null, 'ASC', 1);

            $this->logAction(__FUNCTION__, func_get_args());
            $output = $this->apilib->runDataProcessing($entity, 'login', $unprocessedOutput);

            if ($output) {
                $this->showOutput($output);
            } else {
                $this->showError("Login fallito", Apilib::ERR_GENERIC, 200);
            }
        } catch (ApiException $e) {
            /** Salvo su log il database error nascondendolo all'utente */
            if ($e->getCode() == Apilib::ERR_INTERNAL_DB) {
                $this->logAction(__FUNCTION__, func_get_args(), $e->getPrevious()->getMessage());
            }

            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    private function getEntityWhere($entity_name)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);
        $where = [
            "api_manager_permissions_token = '{$this->token_id}'",
            "api_manager_permissions_entity = '{$entity['entity_id']}'",
        ];
        $permission = $this->db->where(implode(" AND ", $where), null, false)->get('api_manager_permissions');

        if ($permission->num_rows() >= 1) {
            return $permission->row()->api_manager_permissions_where;
        } else {
            return '';
        }
    }

    private function checkFieldsPermissions($entity_name, $chmod)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);

        $where = [
            "api_manager_fields_permissions_token = '{$this->token_id}'",
            "api_manager_fields_permissions_field IN (SELECT fields_id FROM fields WHERE fields_entity_id = '{$entity['entity_id']}')",
        ];

        switch ($chmod) {
            case 'I': //insert
                //Prendo i campi per i quali non è possibile fare insert
                $fields_permissions = $this->db
                    ->where(implode(' AND ', $where), null, false)
                    ->where_in('api_manager_fields_permissions_chmod', ['0', '1', '2'])
                    ->join('fields', 'api_manager_fields_permissions.api_manager_fields_permissions_field = fields.fields_id', 'LEFT')
                    ->get('api_manager_fields_permissions')->result_array();
                break;
            case 'U': //insert
                //Prendo i campi per i quali non è possibile fare insert
                $fields_permissions = $this->db
                    ->where(implode(' AND ', $where), null, false)
                    ->where_in('api_manager_fields_permissions_chmod', ['0', '1', '3'])
                    ->join('fields', 'api_manager_fields_permissions.api_manager_fields_permissions_field = fields.fields_id', 'LEFT')
                    ->get('api_manager_fields_permissions')->result_array();
                break;

            default:
                throw new ApiException("Permission '$chmod' not recognized!");
                break;
        }

        foreach ($fields_permissions as $permission) {
            if (array_key_exists($permission['fields_name'], $this->input->post())) {
                $this->showError("Permission denied on field '{$permission['fields_name']}'!");
                exit;
            }
        }
        return true;
    }

    private function checkEntityPermission($entity_name, $chmod)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);
        $where = [
            "api_manager_permissions_token = '{$this->token_id}'",
            "api_manager_permissions_entity = '{$entity['entity_id']}'",
        ];
        $permission = $this->db->where(implode(" AND ", $where), null, false)->get('api_manager_permissions');
        //Se non ho impostato permessi specifici

        if ($permission->num_rows() == 0 || !$permission->row()->api_manager_permissions_chmod) {
            //Allora posso farci di tutto, perchè significa che non ho specificato nulla di particolare su questa entità...
            return true;
        } else {
            $permission_chmod = $permission->row()->api_manager_permissions_chmod;

            switch ($chmod) {
                case 'R': //Lettura
                    //Torno true quando ho un qualsiasi permesso diverso da 0, ovvero maggiore o uguale a 1
                    return ($permission_chmod >= 1);
                    break;
                case 'U': //Scrittura solo update
                    //Per poter fare update devo avere permessi 2 o 4

                    return in_array($permission_chmod, [2, 4]);
                case 'I': //Scrittura solo insert
                    return in_array($permission_chmod, [3, 4]);
                case 'D': //Delete
                    //Torno false perchè l'unico permesso che ho per cancellare è quando non viene impostato alcun permesso sull'entità
                    return false;
                    break;
                default:
                    throw new ApiException("Permission '$chmod' not recognized!");
                    break;
            }
        }

        return false;
    }

    private function filterOutputFields($entity_name, &$output)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);
        $fields_permissions = $this->db
            ->where('fields_entity_id', $entity['entity_id'])
            ->where("api_manager_fields_permissions_token = '{$this->token_id}'")
            ->where('api_manager_fields_permissions_chmod', '0')
            ->join('fields', 'api_manager_fields_permissions.api_manager_fields_permissions_field = fields.fields_id', 'LEFT')
            ->get('api_manager_fields_permissions')->result_array();

        $output = array_map(function ($data) use ($fields_permissions) {
            foreach ($fields_permissions as $field_permission) {
                unset($data[$field_permission['fields_name']]);
            }
            return $data;
        }, $output);
    }

    /**
     *
     * @param string $outputMode One of redirect|json
     * @param array $outputData
     */
    private function buildOutput($outputMode, array $outputData = array())
    {
        switch ($outputMode) {
            case 'redirect':
                $url = $this->input->get('url');
                if (filter_var($url, FILTER_VALIDATE_URL) === false) {
                    $this->showError('Per effetturare il redirect alla pagina desiderata è necessario passare $_GET[url] - [dati salvati correttamente]');
                } else {
                    redirect($url);
                }
                break;

            case 'json':
            default:
                $this->showOutput($outputData);
                break;
        }
    }

    /**
     * Ritorna in json i campi di un'entità
     * @param type $entity
     */
    public function describe($entity = null, $output = 'debug')
    {
        $details = $this->apilib->describe($entity);

        switch ($output) {
            case 'json':
                echo json_encode($details);
                break;

            case 'debug':
            default:
                echo '<pre>' . print_r($details, true) . '</pre>';
        }
    }

    public function entities()
    {

        $entities = $this->db
            ->order_by('entity_name')
            ->where("entity_id NOT IN (SELECT api_manager_permissions_entity FROM api_manager_permissions WHERE api_manager_permissions_token = '{$this->token_id}' AND api_manager_permissions_chmod = '0')")
            ->get('entity')->result_array();

        $tables = [];
        foreach ($entities as $entity) {
            $table = [
                'name' => $entity['entity_name'],
                'fields' => [],
            ];
            $fields_permissions = $this->db
                ->order_by('fields_name')
                ->where('fields_entity_id', $entity['entity_id'])
                ->where("fields_id NOT IN (SELECT api_manager_fields_permissions_field FROM api_manager_fields_permissions WHERE api_manager_fields_permissions_token = '{$this->token_id}' AND api_manager_fields_permissions_chmod = '0')")
                ->join('api_manager_fields_permissions', 'api_manager_fields_permissions.api_manager_fields_permissions_field = fields.fields_id', 'LEFT')
                ->get('fields')->result_array();

            foreach ($fields_permissions as $field) {
                $table['fields'][] = [
                    'name' => $field['fields_name'],
                    'type' => $field['fields_type'],
                    'required' => $field['fields_required'],
                    'multilangual' => $field['fields_multilingual'],
                    'permission' => ($field['api_manager_fields_permissions_chmod'] !== null) ? unserialize(FIELDS_PERMISSIONS)[$field['api_manager_fields_permissions_chmod']] : 'Read/Write/Delete',
                    'ref' => ($field['fields_ref']) ?: '',
                ];
            }

            $tables[] = $table;
        }

        $this->showOutput($tables);
    }

    public function debug($mode = 'html')
    {
        $post = $this->input->post() ?: array();
        $get = $this->input->get() ?: array();
        $files = $_FILES;

        switch ($mode) {
            case 'json':
                echo json_encode(array(
                    'post' => $post,
                    'get' => $get,
                    'files' => $files,
                ));
                break;

            case 'html':
            default:
                echo '<pre>';
                print_r($post);
                print_r($get);
                print_r($files);
                echo '</pre>';
        }
    }

    /**
     * Mostra schermata di aiuto
     */
    public function help()
    {
        header('Content-Type: text/html');
        $this->load->view('pages/api_manager/rest-help', array('errors' => $this->apilib->getApiMessages()));
    }
    private function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));

            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    private function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();

        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
    private function logAction($method, array $params, $output = array())
    {

        $serial_params = json_encode($params);
        $serial_get = json_encode($_GET);
        $serial_post = json_encode($_POST);
        $serial_files = json_encode($_FILES);
        $serial_output = json_encode($output);

        $this->db->insert('log_api', array(
            'log_api_method' => $method,
            'log_api_params' => $serial_params,
            'log_api_date' => date('Y-m-d H:i:s'),
            'log_api_ip_addr' => $_SERVER['REMOTE_ADDR'],
            'log_api_get' => $serial_get,
            'log_api_post' => $serial_post,
            'log_api_files' => $serial_files,
            'log_api_output' => $serial_output,
        ));
    }

    /**
     * Ritorna l'errore corrente o quello passato
     */
    private function showError($message, $code = 0, $httpStatus = 500)
    {
        if (!empty($this->token_id)) {

            $token = $this->db->get_where('api_manager_tokens', ['api_manager_tokens_id' => $this->token_id])->row();

            $this->db->where('api_manager_tokens_id', $this->token_id)->update('api_manager_tokens', [
                'api_manager_tokens_errors' => (int) ($token->api_manager_tokens_errors) + 1,
            ]);
        }

        $this->showOutput($message, $code);
    }

    /**
     * Ritorna l'output passato terminando lo script
     * @param array|string $message
     * @param int $status
     */
    private function showOutput($message = array(), $status = 0)
    {
        //TODO: check why if remove echo with the true parameters, view won't load

        echo $this->load->view('layout/json_return', [
            'json' => json_encode(
                array(
                    'status' => $status,
                    'message' => is_string($message) ? $message : null,
                    'data' => is_array($message) ? $message : array(),
                )
            ),
        ], true);

        // echo json_encode(array(
        //     'status' => $status,
        //     'message' => is_string($message) ? $message : null,
        //     'data' => is_array($message) ? $message : array()
        // ));
    }
}
