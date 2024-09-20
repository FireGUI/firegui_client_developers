<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class V1 extends MY_Controller
{
    private $request_type_mapping = [
        'index' => 'search',
        'view' => 'view',
        'create' => 'create',
        'edit' => 'edit',
        'delete' => 'delete',
        'search' => 'search',
        'count' => 'search'  // Assuming 'count' is similar to 'search' in terms of permissions
    ];
    private $_preloaded_permissions = [];
    public function __construct()
    {

        parent::__construct();
        set_log_scope('api');

        //$this->session->setTimeoutLock(1);

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

        $method = $this->uri->segment(3);

        if (!in_array($method, ['help', 'swagger'])) {

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

                $this->preloadPermissions($this->token_id);

                $this->processInput();
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
            case 'generateSwaggerDocumentation':
            case 'swagger':
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
            $output = $_output[0] ?? [];
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

    private function processInput()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $contentType = $this->input->get_request_header('Content-Type', TRUE);

            if (strpos($contentType, 'application/json') !== false) {
                $jsonData = json_decode($this->input->raw_input_stream, true);

                if ($jsonData) {
                    // Popola $_POST con i dati JSON
                    $_POST = array_merge($_POST, $jsonData);
                }
            }
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
        $start = microtime(true);
        
        try {
            $limit = ($this->input->post('limit')) ? $this->input->post('limit') : null;
            $offset = ($this->input->post('offset')) ? $this->input->post('offset') : 0;
            $orderBy = ($this->input->post('orderby')) ? $this->input->post('orderby') : null;

            $orderDir = ($this->input->post('orderdir')) ? $this->input->post('orderdir') : 'ASC';
            $maxDepth = ($this->input->post('maxdepth') || $this->input->post('maxdepth') === '0') ? $this->input->post('maxdepth') : 2;
            
            $postData = array_filter((array) $this->input->post('where'));
            if ($this->getEntityWhere($entity)) {
                $where = array_filter([$this->getEntityWhere($entity)]);
            } else {
                $where = [];
            }
            //debug($this->input->post(),true);
            $postData = $this->apilib->runDataProcessing($entity, 'pre-search', $postData);

            //non uso le apilib altrimenti mi fa left join e non è detto che abbia i permessi per le altre entità... una soluzione potrebbe essere quella di ciclare tutti i permessi e rimuovere nella
            ////filterOutputFields anche le tabelle joinate, ma è un lavorone... per ora no
            //debug($maxDepth);
            // debug(elapsed_time($start));
            $output = $this->apilib->search($entity, array_merge($where, $postData), $limit, $offset, $orderBy, $orderDir, $maxDepth);
            //debug($output,true);
            // debug(elapsed_time($start));
            
            $this->filterOutputFields($entity, $output);
            // debug(elapsed_time($start),true);
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
            $maxDepth = ($this->input->post('maxdepth') || $this->input->post('maxdepth') === '0') ? $this->input->post('maxdepth') : 2;

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
        
        $permission = $this->_preloaded_permissions[$entity_name] ?? null;
        //Se non ho impostato permessi specifici

        if ($permission == null || $permission['chmod'] == 0) {
            
            //Allora non posso fare nulla, perchè significa che non ho specificato nulla di particolare su questa entità...
            return false;

        } else {
            $permission_chmod = $permission['chmod'];

            switch ($chmod) {
                case 'R': //Lettura
                    //Torno true quando ho un qualsiasi permesso diverso da 0, ovvero maggiore o uguale a 1
                    //debug(($permission_chmod >= 1),true);
                    return ($permission_chmod >= 1);
                    break;
                case 'U': //Scrittura solo update
                    //Per poter fare update devo avere permessi 2 o 4

                    return in_array($permission_chmod, [2, 4, 5]);
                case 'I': //Scrittura solo insert
                    return in_array($permission_chmod, [3, 4, 5]);
                case 'D': //Delete
                    return in_array($permission_chmod, [5]);


                default:
                    throw new ApiException("Permission '$chmod' not recognized!");
                    break;
            }
        }

        return false;
    }

    private function getCurrentRequestType()
    {
        $called_method = $this->router->fetch_method();
        return $this->request_type_mapping[$called_method] ?? 'search';  // Default to 'search' if method not found
    }
    private function preloadPermissions ($token) {
        if (!empty($this->_preloaded_permissions)) {
            return;

        }
        $entities_permissions = $this->db
            ->join('entity', 'entity_id = api_manager_permissions_entity', 'LEFT')    
            ->where('api_manager_permissions_token', $token)
            ->get('api_manager_permissions')->result_array();
        // debug($this->db->last_query());
        // debug($entities_permissions,true);
        $fields_permissions = $this->db
            ->join('fields', 'fields_id = api_manager_fields_permissions_field', 'LEFT')
            ->join('entity', 'entity_id = fields_entity_id', 'LEFT')
            ->where('api_manager_fields_permissions_token', $token)->get('api_manager_fields_permissions')->result_array();

        foreach ($entities_permissions as $entity_permission) {
            $this->_preloaded_permissions[$entity_permission['entity_name']]['chmod'] = $entity_permission['api_manager_permissions_chmod'];
            $this->_preloaded_permissions[$entity_permission['entity_name']]['where'] = $entity_permission['api_manager_permissions_where'];
            //Aggiungo sempre l'id
            $this->_preloaded_permissions[$entity_permission['entity_name']]['fields'][$entity_permission['entity_name'] . '_id'] = 5;
        }

        foreach ($fields_permissions as $field_permission) {
            $this->_preloaded_permissions[$field_permission['entity_name']]['fields'][$field_permission['fields_name']] = $field_permission['api_manager_fields_permissions_chmod'];
        }
        
        
    }
    private function filterOutputFields($entity_name, &$output)
    { 
        //debug(count($output),true);
        $request_type = $this->getCurrentRequestType();

        $entity = $this->datab->get_entity_by_name($entity_name);
        if (!$output) {
            return $output;
        }
        $data_keys_to_keep = array_keys($output[0]);
        $_fields_entities = $this->db
            ->join('entity', 'entity_id = fields_entity_id', 'LEFT')
            ->where_in('fields_name', $data_keys_to_keep)
            ->get('fields')->result_array();
        $fields_entities_map = array_key_value_map($_fields_entities, 'fields_name', 'entity_name');
        
        //Aggiungo comunque i campi id che non sono in fields, ma so che esistono di default
        foreach ($this->db->get('entity')->result_array() as $entity) {
            $fields_entities_map[$entity['entity_name'] . '_id'] = $entity['entity_name'];
        }


        
        // Define allowed permission levels for each request type
        $allowed_permissions = [
            'search' => ['1', '2', '3', '4', '5'],
            'view' => ['1', '2', '3', '4', '5'],
            'create' => ['3', '4', '5'],
            'edit' => ['2', '4', '5'],
            'delete' => ['5']
        ];

        
        
        
        // Filter the output
        //debug($this->_preloaded_permissions,true);
        
        foreach ($data_keys_to_keep as $key => $field_name) {
            foreach ($this->_preloaded_permissions as $_perm_entity_name => $permission_data) {
                if (!empty($fields_entities_map[$field_name]) && $fields_entities_map[$field_name] == $_perm_entity_name) {
                    $fields_permissions = $permission_data['fields'] ?? [];
                
                    //Per prima covalue: sa verifico se l'entità è accessibile
                    if ($this->checkEntityPermission($_perm_entity_name, 'R')) {
                        if (count($fields_permissions) > 1) {//Se (oltre al campo id, sono stati definiti permessi custom)
                            // Check if the field has specific permissions
                            if (isset($fields_permissions[$field_name])) {
                                $field_permission = $fields_permissions[$field_name];
                                if (!in_array($field_permission, $allowed_permissions[$request_type])) {
                                    debug($field_name,true);
                                    unset($data_keys_to_keep[$key]);
                                }
                            } else {
                                //debug($field_permissions);
                                //debug($field_name, true);
                                //Se il campo è di questa entità, usetto
                                if (empty($fields_entities_map[$field_name]) || $fields_entities_map[$field_name] == $_perm_entity_name) {
                                    // debug($fields_permissions);
                                    // debug($field_name,true);
                                    unset($data_keys_to_keep[$key]);
                                }




                            }
                        } else {
                            //Non sono impostati permessi custom, quindi tengo tutto
                        }
                    } else {
                        
                        unset($data_keys_to_keep[$key]);
                        
                    }
                } else {
                    if (empty($fields_entities_map[$field_name])) {
                        unset($data_keys_to_keep[$key]);
                    }
                }
                

                

            }
            
        }
        $output = array_map(function ($row) use ($data_keys_to_keep) {
            return array_filter($row, function ($key) use ($data_keys_to_keep) {
                return in_array($key, $data_keys_to_keep);
            }, ARRAY_FILTER_USE_KEY);
        }, $output);
            
            return $output;
        
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
        if (strlen($serial_output) > 2000) {
            $serial_output = json_encode('***TOO MANY RECORDS***');
        }


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

    public function generateSwaggerJson()
    {
        $tabelle = $this->crmentity->getEntities();

        $swaggerJson = [
            'openapi' => '3.0.0',
            'info' => [
                    'title' => 'CRM API',
                    'version' => '1.0.0',
                    'description' => 'API for CRM system'
                ],
            'servers' => [
                [
                    'url' => base_url('rest/v1'),
                    'description' => 'CRM API Server'
                ]
            ],
            'security' => [
                ['bearerAuth' => []]
            ],
            'paths' => [],
            'components' => [
                    'securitySchemes' => [
                        'bearerAuth' => [
                            'type' => 'http',
                            'scheme' => 'bearer',
                            'bearerFormat' => 'JWT'
                        ]
                    ],
                    'schemas' => []
                ]
        ];

        foreach ($tabelle as $tabella) {
            if ($tabella['entity_type'] != 1) {
                continue;
            }
            $entityName = $tabella['entity_name'];
            $entityFields = $this->crmentity->getFields($tabella['entity_id']);



            // Add GET all (index) endpoint
            $swaggerJson['paths']["/index/{$entityName}"] = [
                'get' => [
                    'summary' => "Get all {$entityName}",
                    'security' => [['bearerAuth' => []]],
                    'responses' => [
                            '200' => [
                                'description' => 'Successful response',
                                'content' => [
                                        'application/json' => [
                                            'schema' => [
                                                'type' => 'array',
                                                'items' => [
                                                        '$ref' => "#/components/schemas/{$entityName}"
                                                    ]
                                            ]
                                        ]
                                    ]
                            ]
                        ]
                ]
            ];

            // Add POST (create) endpoint
            $swaggerJson['paths']["/create/{$entityName}"] = [
                'post' => [
                    'summary' => "Create a new {$entityName}",
                    'security' => [['bearerAuth' => []]],
                    'requestBody' => [
                            'required' => true,
                            'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => "#/components/schemas/{$entityName}"
                                        ]
                                    ]
                                ]
                        ],
                    'responses' => [
                        '201' => [
                            'description' => 'Created successfully',
                            'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => "#/components/schemas/{$entityName}"
                                        ]
                                    ]
                                ]
                        ]
                    ]
                ]
            ];

            // Add GET by ID (view) endpoint
            $swaggerJson['paths']["/view/{$entityName}/{id}"] = [
                'get' => [
                    'summary' => "Get a specific {$entityName}",
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer']
                            ]
                        ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                            'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => "#/components/schemas/{$entityName}"
                                        ]
                                    ]
                                ]
                        ]
                    ]
                ]
            ];

            // Add PUT (edit) endpoint
            $swaggerJson['paths']["/edit/{$entityName}/{id}"] = [
                'post' => [
                    'summary' => "Update a specific {$entityName}",
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer']
                            ]
                        ],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                                'application/json' => [
                                    'schema' => [
                                        '$ref' => "#/components/schemas/{$entityName}"
                                    ]
                                ]
                            ]
                    ],
                    'responses' => [
                        '200' => [
                            'description' => 'Updated successfully',
                            'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => "#/components/schemas/{$entityName}"
                                        ]
                                    ]
                                ]
                        ]
                    ]
                ]
            ];

            // Add DELETE endpoint
            $swaggerJson['paths']["/delete/{$entityName}/{id}"] = [
                'post' => [
                    'summary' => "Delete a specific {$entityName}",
                    'security' => [['bearerAuth' => []]],
                    'parameters' => [
                            [
                                'name' => 'id',
                                'in' => 'path',
                                'required' => true,
                                'schema' => ['type' => 'integer']
                            ]
                        ],
                    'responses' => [
                        '204' => [
                            'description' => 'Deleted successfully'
                        ]
                    ]
                ]
            ];

            $whereProperties = [];
            foreach ($entityFields as $field) {
                $fieldName = $field['fields_name'];
                $whereProperties["where[{$fieldName}]"] = [
                    'type' => 'string',
                    'description' => "Filter by {$fieldName}",
                    'default' => '',
                    'nullable' => true
                ];
            }


            $swaggerJson['paths']["/search/{$entityName}"] = [
                'post' => [
                    'summary' => "Search {$entityName} with filters",
                    'security' => [['bearerAuth' => []]],
                    'requestBody' => [
                            'required' => true,
                            'content' => [
                                    'application/x-www-form-urlencoded' => [
                                        'schema' => [
                                            'type' => 'object',
                                            'properties' => array_merge($whereProperties, [
                                                        'limit' => [
                                                            'type' => 'integer',
                                                            'description' => 'Number of records to return',
                                                            'default' => '',
                                                            'nullable' => true
                                                        ],
                                                        'offset' => [
                                                            'type' => 'integer',
                                                            'description' => 'Number of records to skip',
                                                            'default' => '',
                                                            'nullable' => true
                                                        ],
                                                        'orderby' => [
                                                            'type' => 'string',
                                                            'description' => 'Field to order by',
                                                            'default' => '',
                                                            'nullable' => true
                                                        ],
                                                        'orderdir' => [
                                                            'type' => 'string',
                                                            'enum' => ['ASC', 'DESC'],
                                                            'description' => 'Order direction',
                                                            'default' => '',
                                                            'nullable' => true
                                                        ],
                                                        'maxdepth' => [
                                                            'type' => 'integer',
                                                            'description' => 'Maximum depth of related entities to return',
                                                            'default' => 1
                                                        ]
                                                    ]),
                                        ]
                                    ]
                                ]
                        ],
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                            'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => [
                                                    '$ref' => "#/components/schemas/{$entityName}"
                                                ]
                                        ]
                                    ]
                                ]
                        ]
                    ]
                ]
            ];

            // Add schema with fields
            // Add SEARCH endpoint

            $swaggerJson['components']['schemas'][$entityName] = [
                'type' => 'object',
                'properties' => $this->getSwaggerProperties($entityFields)
            ];
        }

        return json_encode($swaggerJson, JSON_PRETTY_PRINT);
    }

    private function getSwaggerProperties($fields)
    {
        $properties = [];
        foreach ($fields as $field) {
            $property = [
                'type' => $this->mapFieldTypeToSwagger($field['fields_type']),
                'description' => $field['fields_name']
            ];

            if ($field['fields_required'] == '1') {
                $property['required'] = true;
            }

            if (!empty($field['fields_size'])) {
                $property['maxLength'] = intval($field['fields_size']);
            }

            $properties[$field['fields_name']] = $property;
        }
        return $properties;
    }

    private function mapFieldTypeToSwagger($fieldType)
    {
        $typeMap = [
            'varchar' => 'string',
            'text' => 'string',
            'int' => 'integer',
            'bigint' => 'integer',
            'float' => 'number',
            'double' => 'number',
            'date' => 'string',
            'datetime' => 'string',
            'boolean' => 'boolean',
        ];

        return $typeMap[$fieldType] ?? 'string';
    }

    public function generateSwaggerDocumentation()
    {
        // Verifica del token
        $token_data = $this->db->get_where('api_manager_tokens', ['api_manager_tokens_token' => '' . $this->getBearerToken()]);
        if ($token_data->num_rows() == 0) {
            $this->showError("Invalid bearer token.", 1, 401);
            return;
        }

        $token = $token_data->row();
        $this->token_id = $token->api_manager_tokens_id;

        $tabelle = $this->crmentity->getEntities();

        $swaggerJson = [
            'openapi' => '3.0.0',
            'info' => [
                    'title' => 'CRM API',
                    'version' => '1.0.0',
                    'description' => 'API for CRM system'
                ],
            'servers' => [
                [
                    'url' => base_url('rest/v1'),
                    'description' => 'CRM API Server'
                ]
            ],
            'security' => [
                ['bearerAuth' => []]
            ],
            'paths' => [],
            'components' => [
                    'securitySchemes' => [
                        'bearerAuth' => [
                            'type' => 'http',
                            'scheme' => 'bearer',
                            'bearerFormat' => 'JWT'
                        ]
                    ],
                    'schemas' => []
                ]
        ];

        foreach ($tabelle as $tabella) {
            if (!in_array($tabella['entity_type'], [1, 2])) {
                continue;
            }
            $entityName = $tabella['entity_name'];

            // Verifica dei permessi per l'entità
            if (!$this->checkEntityPermission($entityName, 'R')) {
                continue;
            }

            $entityFields = $this->crmentity->getFields($tabella['entity_id']);
            $allowedFields = $this->filterAllowedFields($entityName, $entityFields);

            if (empty($allowedFields)) {
                continue;
            }

            // Aggiungi i vari endpoint solo se l'entità ha i permessi necessari
            $this->addEntityEndpoints($swaggerJson, $entityName, $allowedFields);

            $swaggerJson['components']['schemas'][$entityName] = [
                'type' => 'object',
                'properties' => $this->getSwaggerProperties($allowedFields)
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($swaggerJson, JSON_PRETTY_PRINT);
    }

    private function filterAllowedFields($entityName, $fields)
    {
        $entity = $this->datab->get_entity_by_name($entityName);
        //Faccio pèrima una query. Se nessun permesso specifico per i campi è impostato allora ho accesso a tutto
        $fields_permissions_exists = $this->db->query("SELECT * FROM api_manager_fields_permissions WHERE api_manager_fields_permissions_token = '{$this->token_id}' AND api_manager_fields_permissions_field IN (SELECT fields_id FROM fields WHERE fields_entity_id = '{$entity['entity_id']}')")->num_rows();
        if ($fields_permissions_exists == 0) {
            return $fields;

        }
        $allowedFields = [];
        foreach ($fields as $field) {
            if ($this->checkFieldPermission($entityName, $field['fields_name'], 'R')) {
                // Aggiungi lo schema con i campi permessi

                $allowedFields[] = $field;
            }
        }
        return $allowedFields;
    }

    private function checkFieldPermission($entityName, $fieldName, $chmod)
    {
        $entity = $this->datab->get_entity_by_name($entityName);
        $where = [
            "api_manager_fields_permissions_token = '{$this->token_id}'",
            "api_manager_fields_permissions_field = (SELECT fields_id FROM fields WHERE fields_entity_id = '{$entity['entity_id']}' AND fields_name = '{$fieldName}')"
        ];

        $permission = $this->db->where(implode(" AND ", $where), null, false)
            ->get('api_manager_fields_permissions')
            ->row();
        // if ('customers' == $entityName) {
        //     debug($permission, true);
        // }
        if (!$permission) {
            return false; // Se non ci sono permessi specifici, consenti l'accesso
        }

        switch ($chmod) {
            case 'R':
                return $permission->api_manager_fields_permissions_chmod != '0';
            case 'W':
                return in_array($permission->api_manager_fields_permissions_chmod, ['2', '4', '5']);
            default:
                return false;
        }
    }

    private function addEntityEndpoints(&$swaggerJson, $entityName, $allowedFields)
    {
        $baseEndpoints = ['index', 'create', 'view', 'edit', 'delete', 'search'];
        $methodMap = [
            'index' => 'R',
            'create' => 'I',
            'view' => 'R',
            'edit' => 'U',
            'delete' => 'D',
            'search' => 'R'
        ];

        foreach ($baseEndpoints as $endpoint) {
            if ($this->checkEntityPermission($entityName, $methodMap[$endpoint])) {
                $method = $endpoint === 'create' || $endpoint === 'edit' || $endpoint === 'delete' || $endpoint === 'search' ? 'post' : 'get';
                $path = "/{$endpoint}/{$entityName}";
                if ($endpoint === 'view' || $endpoint === 'edit' || $endpoint === 'delete') {
                    $path .= "/{id}";
                }

                $swaggerJson['paths'][$path][$method] = $this->generateEndpointSchema($entityName, $endpoint, $allowedFields);
            }
        }
    }

    private function generateEndpointSchema($entityName, $endpoint, $allowedFields)
    {
        $schema = [
            'summary' => ucfirst($endpoint) . " {$entityName}",
            'security' => [['bearerAuth' => []]],
            'responses' => [
                '200' => [
                    'description' => 'Successful response',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$entityName}"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        if (in_array($endpoint, ['create', 'edit', 'search'])) {
            $schema['requestBody'] = [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => "#/components/schemas/{$entityName}"
                        ]
                    ],
                    'application/x-www-form-urlencoded' => [
                        'schema' => [
                            '$ref' => "#/components/schemas/{$entityName}"
                        ]
                    ]
                ]
            ];
        }

        if (in_array($endpoint, ['view', 'edit', 'delete'])) {
            $schema['parameters'] = [
                [
                    'name' => 'id',
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'integer']
                ]
            ];
        }

        if ($endpoint === 'search') {
            $schema['requestBody']['content']['application/json']['schema'] = [
                'type' => 'object',
                'properties' => $this->generateSearchProperties($allowedFields)
            ];
            $schema['requestBody']['content']['application/x-www-form-urlencoded']['schema'] = [
                'type' => 'object',
                'properties' => $this->generateSearchProperties($allowedFields)
            ];
        }

        return $schema;
    }

    private function generateSearchProperties($allowedFields)
    {
        $properties = [];
        foreach ($allowedFields as $field) {
            $fieldName = $field['fields_name'];
            $properties["where[{$fieldName}]"] = [
                'type' => 'string',
                'description' => "Filter by {$fieldName}",
                'default' => '',
                'nullable' => true
            ];
        }

        // Aggiungi proprietà comuni per la ricerca
        $commonProperties = [
            'limit' => [
                'type' => 'integer',
                'description' => 'Number of records to return',
                'default' => '',
                'nullable' => true
            ],
            'offset' => [
                'type' => 'integer',
                'description' => 'Number of records to skip',
                'default' => '',
                'nullable' => true
            ],
            'orderby' => [
                'type' => 'string',
                'description' => 'Field to order by',
                'default' => '',
                'nullable' => true
            ],
            'orderdir' => [
                'type' => 'string',
                'enum' => ['ASC', 'DESC'],
                'description' => 'Order direction',
                'default' => '',
                'nullable' => true
            ],
            'maxdepth' => [
                'type' => 'integer',
                'description' => 'Maximum depth of related entities to return',
                'default' => 1
            ]
        ];

        return array_merge($properties, $commonProperties);
    }

    public function swagger()
    {
        $this->load->view('pages/api_manager/swagger');
    }
}