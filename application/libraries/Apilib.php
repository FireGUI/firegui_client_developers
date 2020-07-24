<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once __DIR__ . '/../helpers/general_helper.php';

class Apilib
{

    /*
     * Class constants
     * ---
     * - Post process modes
     * - Cache seconds
     * - Error codes
     * - System log types
     */
    const MODE_DIRECT = 1;
    const MODE_API_CALL = 2;
    const MODE_CRM_FORM = 3;

    const CACHE_TIME = 3600;

    const ERR_INVALID_API_CALL  = 1;
    const ERR_NO_DATA_SENT      = 2;
    const ERR_REDIRECT_FAILED   = 3;
    const ERR_ENTITY_NOT_EXISTS = 4;
    const ERR_VALIDATION_FAILED = 5;
    const ERR_UPLOAD_FAILED     = 6;
    const ERR_INTERNAL_DB       = 7;    // Internal Server Error: Database Query
    const ERR_POST_PROCESS      = 8;    // Questo errore di norma non usa il messaggio standard
    const ERR_GENERIC           = 8;    // Internal Server Error: Generico. Genera report email

    const LOG_LOGIN         = 1;    // Login action
    const LOG_LOGIN_FAIL    = 2;    // Logout action
    const LOG_LOGOUT        = 3;    // Logout action
    const LOG_ACCESS        = 4;    // Daily access
    const LOG_CREATE        = 5;    // Apilib::create action
    const LOG_CREATE_MANY   = 6;    // Apilib::createMany action
    const LOG_EDIT          = 7;    // Apilib::edit action
    const LOG_DELETE        = 8;    // Apilib::delete action

    private $error = 0;
    private $errorMessage = '';
    private $errorMessages = [
        self::ERR_INVALID_API_CALL => 'Invalid API call',
        self::ERR_NO_DATA_SENT => 'No input data',
        self::ERR_REDIRECT_FAILED => 'To make redirect to desired page is necessary to pass $_GET[url] - [data sucessfully saved]',
        self::ERR_ENTITY_NOT_EXISTS => 'Specified entity not existent',
        self::ERR_VALIDATION_FAILED => 'Validation failed',
        self::ERR_UPLOAD_FAILED => 'Upload failed',
        self::ERR_INTERNAL_DB => 'A database error has occurred',
        self::ERR_POST_PROCESS => 'Post process error',
        self::ERR_GENERIC => 'A server error has occurred. Please try again later.',
    ];

    private $logTitlePatterns = [
        self::LOG_LOGIN       => "User {log_crm_user_name} has logged in",
        self::LOG_LOGIN_FAIL  => "User {log_crm_user_name} has logged in",
        self::LOG_LOGOUT      => "User {log_crm_user_name} has logged out",
        self::LOG_ACCESS      => "L'utente {log_crm_user_name} ha eseguito l'accesso",
        self::LOG_CREATE      => "New record ({log_crm_extra id}) on entity {log_crm_extra entity}",
        self::LOG_CREATE_MANY => "New bulk record creation on entity {log_crm_extra entity}",
        self::LOG_EDIT        => "Edited record ({log_crm_extra id}) on entity {log_crm_extra entity}",
        self::LOG_DELETE      => "Deleted record ({log_crm_extra id}) on entity {log_crm_extra entity}",
    ];

    private $originalPost = null;
    private $previousDebug;
    private $_loadedDataProcessors = [];
    private $processMode = self::MODE_DIRECT;
    private $processEnabled = true;

    /**
     * @var Crmentity
     */
    private $crmEntity; // Istanza crmEntity per usare la cache
    private $currentLanguage = null;
    private $fallbackLanguage = null;

    /**
     * Le relazioni pendenti da inserire dopo insert e dopo update
     * @var array|null
     */
    private $pendingRelations;



    public function __construct()
    {
        // Prima carica la libreria di cache e dopo carica la crm entity
        $this->load->driver('cache', $this->getCacheAdapter());
        $this->load->model('crmentity');

        $this->previousDebug = $this->db->db_debug;
        $this->crmEntity = $this->getCrmEntity();

        // Aggiungi tracciamento eccezioni non catchate, quantomeno per non
        // bloccare il sistema e che nessuno sappia nulla
        //set_exception_handler('crm_exception_handler');

        //Se impostato un tempo di cache diverso prendo quello
        if (defined('CACHE_TIME')) {
            $this->CACHE_TIME = CACHE_TIME;
        } else {
            $this->CACHE_TIME = self::CACHE_TIME;
        }
    }



    /**
     * Fallback per accedere alle proprietà di CI
     * @param mixed $name
     * @return mixed
     */
    public function __get($name)
    {
        $CI = &get_instance();
        return $CI->{$name};
    }


    /**
     * Attiva/disattiva le info di debug
     * @param bool $show
     */
    public function setDebug($show = true)
    {
        $this->db->db_debug = (bool) $show;
    }


    /**
     * Ripristina il vecchio valore del debug
     * database
     */
    public function restoreDebug()
    {
        $this->setDebug($this->previousDebug);
    }

    /**
     * Imposta il tipo di data process da chiamare
     * @param string $mode Una delle costanti di classe:
     *          - Apilib::MODE_API_CALL
     *          - Apilib::MODE_DIRECT
     *          - Apilib::MODE_CRM_FORM
     */
    public function setProcessingMode($mode)
    {

        if (!in_array($mode, array(self::MODE_API_CALL, self::MODE_DIRECT, self::MODE_CRM_FORM))) {
            die(t('Post-process method not valid. Call setProcessingMode with parameters Apilib::MODE_API_CALL, Apilib::MODE_DIRECT o Apilib::MODE_CRM_FORM'));
        }

        $this->processMode = $mode;
    }

    /**
     * Abilita/Disabilita i data process
     * @param bool $enable
     */
    public function enableProcessing($enable)
    {
        $this->processEnabled = (bool) $enable;
    }

    /**
     * Controlla stato abilitazione data process
     * @return bool
     */
    public function isEnabledProcessing()
    {
        return $this->processEnabled;
    }


    public function setLanguage($langId = null, $fallbackLangId = null)
    {
        $this->currentLanguage = ((int) $langId) ?: null;
        $this->fallbackLanguage = ((int) $fallbackLangId) ?: null;
    }

    public function getLanguage()
    {
        return $this->currentLanguage;
    }

    public function getFallbackLanguage()
    {
        return $this->fallbackLanguage;
    }

    /**
     * Traduci un valore in json con le impostazioni lingua correnti
     * 
     * @param type $jsonEncodedValue
     * @return mixed
     */
    public function translate($jsonEncodedValue)
    {

        if (!$this->currentLanguage && !$this->fallbackLanguage) {
            return false;
        }

        return $this->getCrmEntity()->translateValue($jsonEncodedValue);
    }




    /**
     * @todo
     */
    public function get_token($user, $password)
    {
        return uniqid();
    }



    public function getCacheAdapter()
    {
        $filename = APPPATH . 'cache/cache-controller';
        $defaultAdapter = array('adapter' => 'dummy'); //Default adapter dummy to disable cache by default
        if (!file_exists($filename)) {
            @file_put_contents_and_create_dir($filename, serialize($defaultAdapter), LOCK_EX);
            return $defaultAdapter;
        }

        $controllerFileContents = file_get_contents($filename);
        $adapter = @unserialize($controllerFileContents);

        if (!is_array($adapter) or !array_key_exists('adapter', $adapter)) {
            return $defaultAdapter;
        }

        return $adapter;
    }

    /**
     * Enable/Disable for the caching system
     * @param bool $enable
     * @return bool Booleano indicante successo/fallimento dell'operazione
     */
    public function toggleCachingSystem($enable = true)
    {
        if (!$enable) {
            $adapter = ['adapter' => 'dummy'];
        } elseif ($this->cache->apc->is_supported()) {
            $adapter = ['adapter' => 'apc', 'backup' => 'file'];
        } else {
            $adapter = ['adapter' => 'file', 'backup' => 'dummy'];
        }

        //$adapter = $enable? ['adapter' => 'file', 'backup' => 'dummy']: ['adapter' => 'dummy'];
        $out = file_put_contents(APPPATH . 'cache/cache-controller', serialize($adapter), LOCK_EX);
        return $out !== false;
    }


    /**
     * Check cache abilitata o meno
     * @return type
     */
    public function isCacheEnabled()
    {
        $adapter = $this->getCacheAdapter();
        return ($adapter['adapter'] !== 'dummy');
    }


    public function clearCache($testMode = false)
    {

        /*// Controllo che il parent della mia app directory sia la root
        $files = [];
        if (trim(dirname(APPPATH), DIRECTORY_SEPARATOR) !== trim(FCPATH, DIRECTORY_SEPARATOR)) {
            
            // Se così non fosse, allora sono in ambiente multiapp
            $applicationContainer = dirname(APPPATH);
            $di = new DirectoryIterator($applicationContainer);
            
            foreach ($di as $fileinfo) {
                if ($fileinfo->isDot() OR $fileinfo->isFile()) {
                    continue;
                }
                
                $cachedir = $fileinfo->getRealPath() . DIRECTORY_SEPARATOR . 'cache/';
                if (is_dir($cachedir)) {
                    $files = array_merge($files, glob($cachedir.'*'));
                }
            }
        }
        
        $keep = array('cache-controller', 'index.html');
        $cleared = [];
        foreach ($files as $file) {
            if (is_file($file) && !in_array(basename($file), $keep)) {
                if (!$testMode) {
                    unlink($file);
                }
                $cleared[] = substr($file, strlen(FCPATH));
            }
        }
        
        return $cleared;*/
        // 20200310 - Michael E. - Fix che riscrive il file cache-controller resettato da $this->cache->clean() (funzione nativa di Codeigniter) in quanto se abilitata la cache (quindi scrive dei parametri sul file cache-controller) e si pulisce la cache, il file viene resettato e quindi la cache disattivata

        $cache_controller = file_get_contents(APPPATH . 'cache/cache-controller');
        $this->cache->clean();
        file_put_contents(APPPATH . 'cache/cache-controller', $cache_controller);

        @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);
    }

    public function clearCacheKey($key = null)
    {
        if (!$key) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $this->cache->delete($key);
    }

    public function clearCacheRecord($entity = null, $id = null)
    {
        if (!$entity || !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        $cache_key = "apilib.item.{$entity}.{$id}";

        return $this->clearCacheKey($cache_key);
    }
    /***********************************
     * Rest actions
     */


    /**
     * Mostra una lista di record dell'entità richiesta
     * @param string $entity    Il nome dell'entità
     */
    public function index($entity = null, $depth = 2)
    {

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $cache_key = "apilib.list.{$entity}";
        if (!($out = $this->cache->get($cache_key))) {
            $out = $this->getCrmEntity($entity)->get_data_full_list(null, null, [], NULL, 0, NULL, null, FALSE, $depth);
            $this->cache->save($cache_key, $out, $this->CACHE_TIME);
        }

        return $this->sanitizeList($out);
    }


    /**
     * Ritorna tutti i dati di una determinata entità
     * @param string $entity
     * @param int $id
     * @param int $maxDepthLevel
     */
    public function view($entity = null, $id = null, $maxDepthLevel = 2)
    {

        if (!$entity || !$id || !is_numeric($id)) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $cache_key = "apilib.item.{$entity}.{$id}";
        if (!($out = $this->cache->get($cache_key))) {
            $out = $this->getCrmEntity($entity)->get_data_full($id, $maxDepthLevel);
            $this->cache->save($cache_key, $out, $this->CACHE_TIME);
        }

        return $this->sanitizeRecord($out);
    }

    /**
     * Esegue una query pulita su database prendendo l'entità per id
     * 
     * @param string $entity
     * @param int $id
     */
    public function getById($entity, $id)
    {

        if (!is_numeric($id) or !$id) {
            return [];
        }

        $query = $this->db->get_where($entity, [$entity . '_id' => $id]);
        if ($query instanceof CI_DB_result) {
            return $query->row_array();
        }

        throw new Exception(t('Unable to extract record %s from entity %s', 0, [$id, $entity]));
    }

    /**
     * Esegue una query pulita su database prendendo l'entità per un array di
     * id entità. Se l'array è vuoto non esegue la query
     * 
     * @param string $entity
     * @param int $ids
     * @return array
     */
    public function getByIds($entity, array $ids)
    {

        if (!$ids) {
            return [];
        }

        $query = $this->db->where_in($entity . '_id', $ids)->get($entity);
        if ($query instanceof CI_DB_result) {
            return $query->result_array();
        }

        throw new Exception(t('Unable to extract records from entity %s', 0, [$entity]));
    }

    /**
     * Esegue un update diretto sul database utilizzando l'id passato e i dati.
     * Al termine torna un booleano che indica l'esito dell'operazione
     * 
     * @param string $entity
     * @param int $id
     * @param array $data
     * @return boolean
     */
    public function updateById($entity, $id, array $data)
    {
        if (!is_numeric($id) or $id < 1) {
            return false;
        }

        $success = $this->db->update($entity, $data, [$entity . '_id' => $id]);
        //debug($this->db->last_query(),true);
        return $success; // && $this->db->affected_rows()>0;
    }


    /**
     * Crea un nuovo record con il post passato e lo ritorna via json
     * @param string $entity
     */
    public function create($entity = null, $data = null, $returnRecord = true)
    {

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $_data = $this->extractInputData($data);

        //MP: rimuovo i campi password passati vuoti...
        $fields = $this->crmEntity->getFields($entity);
        //debug($fields,true);
        foreach ($fields as $field) {
            //debug($field['fields_draw_html_type']);
            if ($field['fields_draw_html_type'] == 'input_password' && empty($_data[$field['fields_name']])) {
                unset($_data[$field['fields_name']]);
            }
            //Per i campi date e date_time, se sono su mysql li converto in formato mysql
            elseif ($this->db->dbdriver != 'postgre' && in_array($field['fields_type'], ['TIMESTAMP']) && !empty($_data[$field['fields_name']])) {
                //Valuto se la data ha anche ore e minuti
                if (array_key_exists(1, explode(' ', $_data[$field['fields_name']]))) {
                    $date = DateTime::createFromFormat("d/m/Y H:i", $_data[$field['fields_name']]);
                } else {
                    $date = DateTime::createFromFormat("d/m/Y", $_data[$field['fields_name']]);
                }

                if (!$date) {
                    throw new ApiException("Date format incorrect or unknown: '{$_data[$field['fields_name']]}'", self::ERR_INTERNAL_DB);
                    //debug($_data,true);
                }

                $_data[$field['fields_name']] = $date->format('Y-m-d H:i:s');
            }
        }

        if ($this->processData($entity, $_data, false)) {

            if (!$this->db->insert($entity, $_data)) {
                $this->showError(self::ERR_GENERIC);
            }

            $id = $this->db->insert_id();
            $this->savePendingRelations($id);

            $this->runDataProcessing($entity, 'insert', $this->runDataProcessing($entity, 'save', $this->getById($entity, $id)));

            //$this->cache->clean();
            $this->clearCache();
            // Prima di uscire voglio ripristinare il post precedentemente modificato
            $_POST = $this->originalPost;

            // Inserisco il log
            $this->logSystemAction(self::LOG_CREATE, ['entity' => $entity, 'id' => $id]);
            return $returnRecord ? $this->view($entity, $id) : $id;
        } else {
            $_POST = $this->originalPost;
            $this->showError();
        }
    }



    /**
     * Fa l'update del record con id passato aggiornando i dati da post
     * Ritorna i nuovi dati via json
     * @param string $entity
     */
    public function edit($entity = null, $id = null, $data = null, $returnRecord = true)
    {

        if (!$entity or !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $_data = $this->extractInputData($data);



        //MP: rimuovo i campi password passati vuoti...
        $fields = $this->crmEntity->getFields($entity);
        //debug($fields,true);
        foreach ($fields as $field) {
            //debug($field['fields_draw_html_type']);
            if ($field['fields_draw_html_type'] == 'input_password' && empty($_data[$field['fields_name']])) {
                unset($_data[$field['fields_name']]);
            }
            //Per i campi date e date_time, se sono su mysql li converto in formato mysql
            elseif ($this->db->dbdriver != 'postgre' && in_array($field['fields_type'], ['TIMESTAMP']) && !empty($_data[$field['fields_name']])) {
                //debug($_data[$field['fields_name']]);
                if (count(explode(' ', $_data[$field['fields_name']])) == 2) { //Se la data ha anche i secondi
                    if (count(explode('/', $_data[$field['fields_name']])) >= 2) { //In modifica le date vengono passate con la barra (bug datetime picker?)
                        $date = DateTime::createFromFormat("d/m/Y H:i", $_data[$field['fields_name']]);
                    } else {
                        $date = DateTime::createFromFormat("Y-m-d H:i:s", $_data[$field['fields_name']]);
                    }
                } else {
                    $date = DateTime::createFromFormat("d/m/Y", $_data[$field['fields_name']]);
                }



                $_data[$field['fields_name']] = $date->format('Y-m-d H:i:s');
            }
        }

        if ($this->processData($entity, $_data, true, $id)) {
            $oldData = $this->getById($entity, $id);
            //debug($id,true);


            if (!$this->updateById($entity, $id, $_data)) {
                $this->showError(self::ERR_GENERIC);
            }


            $this->savePendingRelations($id);

            //20170904 MP - Al pp save non arrivavano i dati old. Li mergio coi dati dell'entità
            $newData = $this->runDataProcessing(
                $entity,
                'save',
                array_merge(
                    $this->getById($entity, $id),
                    ['old' => $oldData,]
                )
            );
            $this->runDataProcessing($entity, 'update', [
                'new' => $newData,
                'old' => $oldData,
                'diff' => array_diff_assoc_recursive($newData, $oldData),
                'value_id' => $id
            ]);

            $this->clearCache();

            $_POST = $this->originalPost;

            // Inserisco il log
            $this->logSystemAction(self::LOG_EDIT, ['entity' => $entity, 'id' => $id]);
            return $returnRecord ? $this->view($entity, $id) : $id;
        } else {
            $_POST = $this->originalPost;
            $this->showError();
        }
    }


    public function createMany($entity = null, $data = null)
    {

        // In questo caso $data è un array di righe multiple che verranno
        // inserite e validate singolarmente. Questo è necessario perché ho
        // bisogno degli ultimi record inseriti e dei rispettivi id per poter
        // eseguire regolarmente i pre e post processes.
        // ***
        // L'idea è quella di far partire una transazione in modo tale che se
        // uno fallisce falliscono tutti
        /*$this->db->trans_start();
        
        $output = [];
        foreach ($data as $row) {
            $output[] = $this->create($entity, $row);
        }
        
        $this->db->trans_complete();
        return $output;*/

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $_data = array_filter($this->extractInputData($data));
        $groups = [];

        foreach ($_data as &$_item) {

            if (!is_array($_item)) {
                $this->showError(self::ERR_INVALID_API_CALL);
            }

            if (!$this->processData($entity, $_item, false)) {
                $_POST = $this->originalPost;
                $this->showError();
            }

            // Devo verificare che i record abbiano tutti lo stesso tipo di chiavi.
            // Devo raggruppare quindi i record in base alle chiavi che ha e dopo
            // eseguire un insert_batch per ciascun gruppo
            $keys = array_keys($_item);
            sort($keys);
            $groups[md5(serialize($keys))][] = $_item;
        }

        foreach ($groups as $items) {
            if (!$this->db->insert_batch($entity, $items)) {
                $this->showError(self::ERR_GENERIC);
            }
        }

        $idfield = $entity . '_id';
        $numChanged = count($_data);
        $newRecords = $this->db->limit($numChanged)->order_by($idfield, 'DESC')->get($entity)->result_array();

        $ids = [];
        foreach ($newRecords as $record) {
            $ids[] = $record[$idfield];
            $this->runDataProcessing($entity, 'insert', $this->runDataProcessing($entity, 'save', $record));
        }

        $this->clearCache();

        // Prima di uscire voglio ripristinare il post precedentemente modificato
        $_POST = $this->originalPost;

        // Inserisco il log
        $this->logSystemAction(self::LOG_CREATE_MANY, ['entity' => $entity, 'ids' => $ids]);
        return $ids;
    }


    /**
     * @param mixed $data
     * @return array
     */
    private function extractInputData($data)
    {

        $this->originalPost = $this->input->post();
        if (empty($data) or !is_array($data)) {
            // Non ho passato dati, quindi prendo il post normalmente
            $data = $this->originalPost;
        } else {
            // Per ragioni di validazione sovrascrivo il post in quanto il validator
            // di CI 2.x va a cercare sempre nell'array post
            $_POST = $data;
        }

        if (empty($data) && empty($_FILES)) {
            $this->showError(self::ERR_NO_DATA_SENT);
        }

        if (!is_array($data)) {
            $data = (array) $data;
        }

        /*foreach ($data as $key => $val) {
            if ($val === '') {
                unset($data[$key]);
            }
        }*/

        return $data;
    }



    /**
     * Cancella il record selezionate
     * @param string $entity
     */
    public function delete($entity = null, $id = null)
    {

        if (!$entity || !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        //20170608 - MP - integrazione soft-delete
        // Recupero i dati dell'entità
        try {
            $entity_data = $this->crmEntity->getEntity($entity);
        } catch (Exception $ex) {
            $this->error = self::ERR_VALIDATION_FAILED;
            $this->errorMessage = $ex->getMessage();
            return false;
        }

        // Gestione delle custom fields actions - recupero il dato dall'entità
        $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);

        $this->db->trans_start();
        $this->runDataProcessing($entity, 'pre-delete', ['id' => $id]);
        if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
            $this->db->where($entity . '_id', $id)->update($entity, [$entityCustomActions['soft_delete_flag'] => DB_BOOL_TRUE]);
        } else {
            $this->db->delete($entity, [$entity . '_id' => $id]);
        }
        $this->runDataProcessing($entity, 'delete', ['id' => $id]);
        $this->logSystemAction(self::LOG_DELETE, ['entity' => $entity, 'id' => $id]);
        $this->db->trans_complete();

        //$this->cache->clean();
        $this->clearCache();
    }
    /**
     * Alias function
     */
    public function cleanCache()
    {
        $this->clearCache();
    }

    /**
     * Ritorna in json i campi di un'entità
     * @param type $entity
     */
    public function describe($entity = null)
    {

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        try {
            $entity_data = $this->crmEntity->getEntity($entity);
        } catch (Exception $ex) {
            $this->showError(self::ERR_ENTITY_NOT_EXISTS);
        }

        $fields = $this->crmEntity->getVisibleFields($entity_data['entity_id']);
        return array_key_map($fields, 'fields_name');
    }
    public function tableList()
    {
        $entities = $this->db->order_by('entity_name')->get('entity')->result_array();

        $tables = [];
        foreach ($entities as $entity) {
            $table = [
                'name' => $entity['entity_name'],
                'fields' => []
            ];
            $fields = $this->db->order_by('fields_name')->get_where('fields', ['fields_entity_id' => $entity['entity_id']])->result_array();
            foreach ($fields as $field) {
                $table['fields'][] = $field['fields_name'] . ($field['fields_ref'] ? ' <small>[ref. from: <strong>' . $field['fields_ref'] . '</strong>]</small>' : '');
            }

            $tables[] = $table;
        }

        return $tables;
    }

    public function entityList()
    {

        $entity_type_default = defined('ENTITY_TYPE_DEFAULT') ? ENTITY_TYPE_DEFAULT : 1;
        $entities = $this->db->order_by('entity_name')->get_where('entity', ['entity_type' => $entity_type_default])->result_array();

        $tables = [];
        foreach ($entities as $entity) {
            $table = [
                'name' => $entity['entity_name'],
                'fields' => []
            ];
            $fields = $this->db->order_by('fields_name')->get_where('fields', ['fields_entity_id' => $entity['entity_id']])->result_array();
            foreach ($fields as $field) {
                $table['fields'][] = $field['fields_name'] . ($field['fields_ref'] ? ' <small>[ref. from: <strong>' . $field['fields_ref'] . '</strong>]</small>' : '');
            }

            $tables[] = $table;
        }

        return $tables;
    }



    public function supportList()
    {


        $entity_type_support = defined('ENTITY_TYPE_SUPPORT_TABLE') ? ENTITY_TYPE_SUPPORT_TABLE : 2;
        $support_tables = $this->db->order_by('entity_name')->get_where('entity', array('entity_type' => $entity_type_support))->result_array();

        $tables = [];
        foreach ($support_tables as $entity) {
            $tables[$entity['entity_name']] = array('fields' => [], 'values' => []);
            $fields = $this->db->order_by('fields_name')->get_where('fields', array('fields_entity_id' => $entity['entity_id']))->result_array();
            foreach ($fields as $field) {
                $tables[$entity['entity_name']]['fields'][] = $field['fields_name'];
            }

            $values = $this->db->order_by($entity['entity_name'] . '_id')->get($entity['entity_name'])->result_array();
            foreach ($values as $k => $value) {
                if ($k < 50) {
                    $tables[$entity['entity_name']]['values'][$value[$entity['entity_name'] . '_id']] = $value[$entity['entity_name'] . '_value'];
                } elseif ($k == 50) {
                    $tables[$entity['entity_name']]['values']['...'] = '...';
                    break;
                }
            }
        }

        return $tables;
    }



    public function search($entity = null, $input = [], $limit = null, $offset = 0, $orderBy = null, $orderDir = 'ASC',  $maxDepth = 2, $eval_cachable_fields = null, $additional_parameters = [])
    {

        //die('test');
        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        if (!is_array($input)) {
            $input = $input ? [$input] : [];
        }
        $group_by = array_get($additional_parameters, 'group_by', null);
        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        $cache_key = "apilib.search.{$entity}." . md5(serialize($input)) .         ($limit ? '.' . $limit : '') .         ($offset ? '.' . $offset : '') .          ($orderBy ? '.' . md5(serialize($orderBy)) : '') .         ($group_by ? '.' . md5(serialize($group_by)) : '') .          '.' .           md5(serialize($orderDir));



        if (!($out = $this->cache->get($cache_key))) {

            $where = [];
            if (isset($input['where'])) {
                $where[] = $input['where'];
                unset($input['where']);
            }

            foreach ($input as $key => $value) {
                if (is_array($value) && is_string($key)) {

                    // La chiave se è stringa indica il nome del campo,
                    // mentre il value se è array (fattibile solo da POST o PROCESS)
                    // fa un WHERE IN
                    $values = "'" . implode("','", $value) . "'";
                    $where[] = "{$key} IN ({$values})";
                } elseif (is_string($key)) {
                    $where[$key] = $value;
                } else {
                    // Ho una chiave numerica che potrebbe essere stata inserita
                    // da pre-process...
                    $where[] = $value;
                }
            }

            try {
                $this->load->model('crmentity');
                $entity_data = $this->crmentity->getEntity($entity);
            } catch (Exception $ex) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $ex->getMessage();
                return false;
            }

            $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);

            //20170608 - MP - Filtro per soft-delete se non viene specificato questo filtro nel where della grid
            if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
                //Se nel where c'è già un filtro specifico sul campo impostato come soft-delete, ignoro. Vuol dire che sto gestendo io il campo delete (es.: per mostrare un archivio o un history...)
                //Essendo $where un array di condizioni, senza perdere tempo a ciclare, lo implodo così analizzo la stringa (che poi di fatto è quello che fa dopo implodendo su " AND "
                if (stripos(implode(' ', $where), $entityCustomActions['soft_delete_flag']) === FALSE) {
                    $where[] = "({$entityCustomActions['soft_delete_flag']} =  '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)";
                }
            }

            $order_array = [];
            if ($orderBy) {
                // L'order by e l'order dir sono due stringhe di condizioni separate da due punti
                // campo_1:campo_2 ...
                // dir_1:dir_2 ...
                $order_fields = explode(':', $orderBy);
                $order_dirs = explode(':', $orderDir);

                // Elabora i due parametri affinché siano della stessa dimensione
                foreach ($order_fields as $k => $field) {
                    if (!$field) {
                        // Il campo va ignorato
                        continue;
                    } elseif (isset($order_dirs[$k])) {
                        // Mi assicuro che la direzione sia ASC o DESC
                        $direction = strtoupper($order_dirs[$k]);
                        $order_array[] = $field . ' ' . ($direction === 'DESC' ? $direction : '');
                    } else {
                        // Aggiungo con direzione ASC (default)
                        $order_array[] = $field;
                    }
                }
            }

            $order = empty($order_array) ? null : implode(', ', $order_array);
            //$order = $orderBy? $orderBy.' '.($orderDir==='ASC'? $orderDir: 'DESC'): null;

            try {

                $out = $this->getCrmEntity($entity)->get_data_full_list(null, null, $where, $limit ?: null, $offset, $order, false, $maxDepth, [], ['group_by' => $group_by]);
                $this->cache->save($cache_key, $out, $this->CACHE_TIME);
            } catch (Exception $ex) {
                //throw new ApiException('Si è verificato un errore nel server', self::ERR_INTERNAL_DB, $ex);
                throw new ApiException($ex->getMessage(), self::ERR_INTERNAL_DB, $ex);
            }
        }

        return $this->runDataProcessing($entity, 'search', $this->sanitizeList($out));
    }


    public function searchFirst($entity = null, $input = [], $offset = 0, $orderBy = null, $orderDir = 'ASC', $maxDepth = 1, $additional_parameters = [])
    {
        //$group_by = array_get($additional_parameters, 'group_by', null);
        //$out = $this->search($entity, $input, 1);
        //20151019 - Fix MP: aggiunti parametri come per la search (serve per passare 0 dalla api->login come profondità, ad esempio).
        $out = $this->search($entity, $input, 1, $offset, $orderBy, $orderDir, $maxDepth, [], $additional_parameters);
        return array_shift($out) ?: [];
    }



    public function count($entity = null, $input = [], $additional_parameters = [])
    {

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        $group_by = array_get($additional_parameters, 'group_by', null);
        if (is_string($input)) {
            $input = array($input);
        }

        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        $cache_key = "apilib.count.{$entity}." . md5(serialize($input));



        if (!($out = $this->cache->get($cache_key))) {
            $where = [];
            if (isset($input['where'])) {
                $where[] = $input['where'];
                unset($input['where']);
            }

            foreach ($input as $key => $value) {
                if (is_array($value) && is_string($key)) {

                    // La chiave se è stringa indica il nome del campo,
                    // mentre il value se è array (fattibile solo da POST o PROCESS)
                    // fa un WHERE IN
                    $values = "'" . implode("','", $value) . "'";
                    $where[] = "{$key} IN ({$values})";
                } elseif (is_string($key)) {
                    $where[$key] = $value;
                } else {
                    // Ho una chiave numerica che potrebbe essere stata inserita
                    // da pre-process...
                    $where[] = $value;
                }
            }

            $out = $this->getCrmEntity($entity)->get_data_full_list(null, null, $where, null, 0, null, true, 2, [], ['group_by' => $group_by]);
            $this->cache->save($cache_key, $out, $this->CACHE_TIME);
        }

        return $out;
    }



    /**
     * Torna l'array dei messaggi d'errore,
     * NB: a scopi di debug - help ecc
     */
    public function getApiMessages()
    {
        return $this->errorMessages;
    }




    /**
     * Lancia eccezione con errore passato o quello corrente
     */
    private function showError($errorCode = null)
    {

        if (!is_null($errorCode) && is_numeric($errorCode)) {
            $this->error = $errorCode;
        }

        /***
         * Se non c'è nessun messaggio presettato mettici quello di default per
         * il codice d'errore corrente - sempre se esiste
         */
        if (empty($this->errorMessage) && isset($this->errorMessages[$this->error])) {
            $this->errorMessage = t($this->errorMessages[$this->error]);
        }

        if (!$this->errorMessage) {
            // Condizione anomala: questo non capiterà mai, in quanto ogni
            // codice errore ha il suo messaggio, però la teniamo per debuggare
            // eventuali dimenticanze future
            $this->errorMessage = t('Unexpected error: unable to recover error code message for %s', 0, [$this->error]);
        }

        // Creo l'oggetto eccezione
        $exception = new ApiException($this->errorMessage, $this->error);

        // Genero report per gli internal server error `misteriosi`
        if ($this->error == self::ERR_GENERIC) {
            // Manda l'eccezione assieme al listing delle query
            // tipicamente questo errore non dovrebbe capitare in development
            // in quanto avrei il db_debug attivo, ma in produzione, quando le
            // query sono nascoste.
            crm_exception_handler($exception, false, true);
        }

        // Lancio eccezione
        throw $exception;
    }













    /**
     * Rimuovi tutti i dati relativi all'entità da
     * tutti gli elementi di una lista
     * ---
     * Se la lista ha una chiave data allora vuol dire che i veri
     * record sono li dentro
     * @deprecated since 06/06/2016     Sistemato crmentity affinché non torni più un sottoarray `data`
     */
    private function sanitizeList($data)
    {
        return $data;

        $data = isset($data['data']) ? $data['data'] : $data;

        if (is_array($data)) {
            return array_map(function ($item) {
                return is_array($item) ? $this->sanitizeRecord($item) : $item;
            }, $data);
        } else {
            return null;
        }
    }




    /**
     * Per ogni elemento ci metto 
     * ci metto solo i dati se presenti, eliminando quindi
     * le informazioni sui fields - per farlo faccio un sanitize list
     * @deprecated since 06/06/2016     Sistemato crmentity affinché non torni più un sottoarray `data`
     */
    private function sanitizeRecord($item)
    {
        return $item;

        if (is_array($item)) {
            return array_map(function ($value) {
                return is_array($value) ? $this->sanitizeList($value) : $value;
            }, $item);
        } else {
            return null;
        }
    }


    /**
     * Prepara i dati all'inserimento nel database
     * 
     * @param string|int $entity    Entity name/id sul quale effettuare le modifiche
     * @param array $dati           I dati da processare
     * @param int|null $id          Eventuale id con 
     * @param bool $exec_preprocess
     * @return array
     */
    public function prepareData($entity, array $dati, $id = null, $exec_preprocess = false)
    {

        // Salvo il flag di esecuzione post process e abilito/disabilito il
        // processing dei dati in input in base alle preferenze utente
        $is_executing_processing = $this->isEnabledProcessing();
        $this->enableProcessing($exec_preprocess);

        // Elaboro i dati in modo da ottenere i valori esatti da inserire su
        // database. Il metodo mi cambia i dati per riferimento
        $this->processData($entity, $dati, (bool) $id, $id);

        // Ripristino il vecchio valore di enable processing
        $this->enableProcessing($is_executing_processing);

        // Ritorno i dati
        return $dati;
    }



    /**
     * Torna un booleano che indica se i dati per l'entità sono validi
     */
    private function processData($entity, array &$dati, $editMode = false, $value_id = null)
    {

        // Recupero i dati dell'entità
        try {
            $entity_data = $this->crmEntity->getEntity($entity);
        } catch (Exception $ex) {
            $this->error = self::ERR_VALIDATION_FAILED;
            $this->errorMessage = $ex->getMessage();
            return false;
        }

        // Gestione delle custom fields actions - recupero il dato dall'entità
        $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);


        $originalData = $dati;

        if ($editMode) {

            if (is_array($value_id)) {
                // Value id deve contenere il mio record per intero. Estraggo
                // quindi l'id
                $dataDb = $value_id;
                $value_id = $dataDb[$entity . '_id'];
            } else {
                // Pre-fetch dei dati sennò fallisce la validazione
                //TODO: if db table has columns not present in fields, this will cause an error later... suggestion: use build_select to get only columns present in fields
                $dataDb = $this->db->get_where($entity, array($entity . '_id' => $value_id))->row_array();
            }

            $_POST = $dati = array_merge($dataDb, $dati);
        } else {
            $value_id = null;
        }

        $fields = $this->crmEntity->getFields($entity_data['entity_id']);
        //$fields = $this->db->join('fields_draw', 'fields_draw_fields_id=fields_id', 'left')->get_where('fields', ['fields_entity_id' => $entity_data['entity_id']])->result_array();

        //debug($dati, true);

        // Recupera dati di validazione
        foreach ($fields as $k => $field) {
            $fields[$k]['validations'] = $this->crmEntity->getValidations($field['fields_id']);
            //$fields[$k]['validations'] = $this->db->get_where('fields_validation', array('fields_validation_fields_id' => $field['fields_id']))->result_array();
        }

        /**
         * Validation
         */
        $rules = [];
        $rules_date_before = [];
        foreach ($fields as $field) {
            $rule = [];

            // Enter the required rule for the fields that require it
            // (a password is required only if creating the record for the
            // first time)
            if ($field['fields_required'] === DB_BOOL_TRUE && ($field['fields_default'] === '' || $field['fields_default'] === null)) {
                switch ($field['fields_draw_html_type']) {
                        // this because upload is made after validation rules
                    case 'upload':
                    case 'upload_image':
                        if (!array_key_exists($field['fields_name'], $_FILES)) {
                            $rule[] = 'required';
                        }
                        break;

                        // password is valuated as required only if not in edit mode,
                        //because in edit it doesnt mean that i want to change it
                    case 'input_password':
                        if (!$editMode) {
                            $rule[] = 'required';
                        }
                        break;

                        // By default field will be required
                    default:
                        $rule[] = 'required';
                        break;
                }
            }


            // Inserisci le altre regole di validazione
            foreach ($field['validations'] as $validation) {
                switch ($validation['fields_validation_type']) {

                        //Le validazioni che non richiedono parametri particolari
                    case 'valid_email':
                    case 'valid_emails':
                    case 'integer':
                    case 'numeric':
                    case 'is_natural':
                    case 'is_natural_no_zero':
                    case 'alpha':
                    case 'alpha_numeric':
                    case 'alpha_dash':
                        $rule[] = $validation['fields_validation_type'];
                        break;

                        //Le validazioni che hanno parametri semplici
                    case 'is_unique':
                        // Il caso unique ha una particolarità:
                        // nel caso di edit se uno NON ha cambiato il valore del campo,
                        // allora non includo la regola di unicità, perché fallirebbe sempre
                        // e il form validator di CI non è così intelligente da poter determinare che
                        // il valore inserito fa riferimento all'entità
                        if (!$editMode || $dataDb[$field['fields_name']] != $dati[$field['fields_name']]) {
                            $rule[] = "is_unique[{$validation['fields_validation_extra']}]";
                        }
                        break;

                    case 'decimal':
                    case 'min_length':
                    case 'max_length':
                    case 'exact_length':
                    case 'greater_than':
                    case 'less_than':
                        $rule[] = "{$validation['fields_validation_type']}[{$validation['fields_validation_extra']}]";
                        break;

                        // Validazioni complesse
                    case 'date_before':
                        $rules_date_before[] = [
                            'before' => $field['fields_name'],
                            'after' => $validation['fields_validation_extra'],
                            'message' => $validation['fields_validation_message'] ?: null
                        ];
                        break;

                    case 'date_after':
                        $rules_date_before[] = [
                            'before' => $validation['fields_validation_extra'],
                            'after' => $field['fields_name'],
                            'message' => $validation['fields_validation_message'] ?: null
                        ];
                        break;
                }
            }

            if (!empty($rule)) {
                $rules[] = array('field' => $field['fields_name'], 'label' => $field['fields_draw_label'], 'rules' => implode('|', $rule));
            }
        }

        /**
         * Eseguo il process di pre-validation
         */
        $_predata = $dati;
        $mode = $editMode ? 'update' : 'insert';
        $processed_predata_1 = $this->runDataProcessing($entity_data['entity_id'], "pre-validation-{$mode}", ['post' => $_predata, 'value_id' => $value_id, 'original_post' => $this->originalPost]);   // Pre-validation specifico
        $processed_predata_2 = $this->runDataProcessing($entity_data['entity_id'], 'pre-validation', $processed_predata_1);                                     // Pre-validation generico
        if (isset($processed_predata_2['post'])) {
            // Metto i dati processati nel post
            $_POST = $dati = $processed_predata_2['post'];
        }

        if (!empty($rules)) {
            // Non uso $this->form_validation (del controller) se già
            // inizializzato, ma ne creo uno nuovo all'occorrenza
            if (!class_exists('CI_Form_validation') && !class_exists('MY_Form_validation')) {
                //debug(class_exists('CI_Form_validation'),true);
                $this->load->library('form_validation');
            } else {
                $validatorClass = (class_exists('MY_Form_validation') ? 'MY_Form_validation' : 'CI_Form_validation');

                $this->form_validation = new $validatorClass;
            }
            //20180426 - MP - Col passaggio a CI3, non viene più verificato il $_POST dal form validator, ma direttamente il CI->input->method (che nel caso di "change_value" è GET, non POST).
            //Fortunatamente hanno previsto la possibilità di dire al form validator che non deve valutare $_POST, ma un array "validation_data" che si può settare con set_data. Ecco il perchè di questa modifica.
            $this->form_validation->set_data($_POST);
            $this->form_validation->set_rules($rules);

            if (!$this->form_validation->run()) {
                // Torno solo il primo errore
                $this->error = self::ERR_VALIDATION_FAILED;
                foreach ($rules as $rule) {
                    // Non appena lo trovo break - almeno uno ci dev'essere
                    $message = $this->form_validation->error($rule['field']);

                    if ($message) {
                        $this->errorMessage = strip_tags(html_entity_decode($message));
                        break;
                    }
                }

                return false;
            }
        }

        /*
         * Upload di eventuali file
         * ---
         * Con il parametro true, man mano che il sistema fa l'upload dei file,
         * unsetta la relativa chiave nella superglobal $_FILES per prevenire
         * errori di validazione in eventuali post process successivi
         * 
         * $result è il risultato dell'operazione: se è un array, allora posso
         * unirlo all'array $dati, altrimenti se è avvenuto un qualche errore di
         * upload, allora questo sarà === false (nota che gli errori sono già
         * stati notificati dalla funzione uploadAll, quindi mi basta fare un
         * return false;
         */
        $result = $this->uploadAll(true);
        if ($result === false) {
            return false;
        } elseif ($result && is_array($result)) {
            $dati = array_merge($dati, $result);
            $originalData = array_merge($originalData, $result);
        }


        /*
         * Elabora i dati prima del salvataggio in base a fields_draw_html_type e tipo
         * es: password => md5($data['password'])
         * 
         * Mi salvo i cossiddetti relation bundles (pacchetti di dati con i
         * quali gestisco le relazioni NxN) in una variabile locale in modo da
         * tenerla al sicuro da possibili update ricorsivi (cioè lanciati da
         * post process)... Una volta eseguito l'ultimo post process, questi
         * relation bundles andranno inseriti dentro alla variabile d'istanza
         * `pendingRelations`
         */
        $relationBundles = [];
        foreach ($fields as $field) {

            $name = $field['fields_name'];
            $value = isset($dati[$name]) ? $dati[$name] : null;
            $multilingual = $field['fields_multilingual'] == DB_BOOL_TRUE;

            $isRelation = $this->checkRelationsOnField($field, $value);
            if ($isRelation) {
                // Se il campo è una relazione allora dentro al $value ho il mio
                // relation bundle. Me lo salvo e proseguo con il processing
                $relationBundles[] = $value;
                unset($dati[$name]);
                continue;
            }

            // Evito di processare il campo se il campo non è stato passato
            // nell'input, perché significa che è già stato processato
            // precedentemente (questo dovrebbe velocizzare l'apilib)
            // per questa ottimizzazione non devo prendere i campi multilingua, 
            // perchè altrimenti non avrei il merge
            if ($editMode && !array_key_exists($name, $originalData) && !$multilingual) {
                continue;
            }

            if ($multilingual) {
                // Controllo multilingua
                // ---
                // Se il campo è multilingua devo passare come dato una stringa
                // codificata in json - se ho una lingua settata nell'apilib,
                // allora il value può anche non essere un array
                $toSaveJSON = [];

                if (isset($originalData[$name]) && (is_array($value) or !is_null($this->currentLanguage))) {
                    // Se siamo in modifica ed è impostato il vecchio campo (ed
                    // ovviamente è un json valido nel db), allora mi è
                    // possibile fare un merge di questi valori con quelli in
                    // ingresso
                    if ($editMode && isset($dataDb[$name])) {
                        $dbMultilingualJSON = json_decode($dataDb[$name], true);
                        if (is_array($dbMultilingualJSON)) {
                            $toSaveJSON = $dbMultilingualJSON;
                        }
                    }

                    // Se entro in questo if significa che ho una lingua
                    // settata, quindi l'eventuale $value stringa lo metto come
                    // lingua di default
                    if (!is_array($value)) {
                        $value = [$this->currentLanguage => $value];
                    }

                    // Quando inserisco i nuovi parametri, devo ciclarli e
                    // inserirli manualmente con la chiave corretta, in quanto
                    // un array_merge cambierebbe le chiavi numeriche (che per 
                    // noi sono essenziali dato che contengono il language_id)
                    foreach ($value as $langId => $lValue) {
                        //(attenzione $lValue è per riferimento)
                        if (!$this->sanitizeInput($field, $lValue, isset($originalData[$name][$langId]) ? $originalData[$name][$langId] : null)) {
                            return false;
                        }
                        $toSaveJSON[$langId] = $lValue;
                    }
                }

                // A questo punto rifaccio l'encode sulla variabile $value
                // solamente se ho effettivamente passato il campo, altrimenti
                // dentro a ... ho già il valore da salvare (perché non è mai
                // stato modificato da com'è salvato in db)
                $value = isset($originalData[$name]) ? json_encode($toSaveJSON) : $value;

                // Per i campi non multilingua semplicemente processo il valore
                // normalmente (attenzione $value è per riferimento)
            } elseif (!$this->sanitizeInput($field, $value, isset($originalData[$name]) ? $originalData[$name] : null)) {
                return false;
            }

            // Se alla fine di tutto questo processo il dato è a null, ma ha un
            // valore di default settato allora, tocca unsettare il
            // valore null in quanto prenderò quello di default (via DB)
            // ===
            // Inoltre rimuovo il campo anche se è null && required in modalità
            // di modifica
            $isNull = is_null($value);
            $hasDefault = trim($field['fields_default']);
            $isRequired = $field['fields_required'] === DB_BOOL_TRUE;

            //Quersta è la vecchia condizione di Alberto. Secondo me è corretto che se non è obbligatorio e uno lo lascia vuoto, venga settato a null comunque...
            //if ($isNull && ($hasDefault OR ($editMode && $isRequired))) {

            //            if ($name == 'projects_database_port') {
            //                var_dump($value);
            //                exit;
            //            }

            if (
                ($isNull && ($hasDefault or ($editMode && $isRequired)))
                //                    OR
                //                    (!$editMode && $value === '' && !$isRequired)
            ) {
                unset($dati[$name]);
            } elseif ($editMode && $value === '' && !$isRequired) {
                $dati[$name] = null;
            } else {
                $dati[$name] = $value;
            }
        }

        // Check custom validation rules
        foreach ($rules_date_before as $rule) {
            $before = $rule['before'];
            $after = $rule['after'];
            $message = $rule['message'];

            if (isset($dati[$before]) && isset($dati[$after]) && strtotime($dati[$after]) <= strtotime($dati[$before])) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $message ?: t("Start date must be antecedent as of end date");
                return false;
            }
        }

        /**
         * Run pre-action process
         */
        $processed_data_1 = $this->runDataProcessing($entity_data['entity_id'], "pre-{$mode}", ['post' => $dati, 'value_id' => $value_id, 'original_post' => $this->originalPost]); // Pre-process specifico
        $processed_data_2 = $this->runDataProcessing($entity_data['entity_id'], 'pre-save', $processed_data_1);                             // Pre-process generico
        if (isset($processed_data_2['post'])) {
            // Put data to be inserted into database
            $dati = $processed_data_2['post'];
        }

        // Unset entity id for security issue:
        if ($this->processMode !== self::MODE_DIRECT) {
            unset($dati[$entity . '_id']);
        }

        // Set creation date and/or edit date
        if (isset($entityCustomActions['create_time']) && !$editMode && empty($dati[$entityCustomActions['create_time']])) {
            $dati[$entityCustomActions['create_time']] = date('Y-m-d H:i:s');
        }
        if (isset($entityCustomActions['update_time']) && empty($originalData[$entityCustomActions['update_time']])) {
            $dati[$entityCustomActions['update_time']] = $editMode ? date('Y-m-d H:i:s') : null;
        }


        /*
         * Filtro i campi su cui sto per fare la query.
         * ---
         * Mando errore di validazione nel qual caso stia inserendo dei campi
         * non permessi che mi farebbero fallire la query
         * ---
         * Ovviamente se sono in modalità form crm non mando l'errore, ma
         * rimuovo semplicemente i campi in più
         */
        $invalidFields = array_diff(array_keys($dati), array_key_map($fields, 'fields_name'));
        if (($key = array_search($entity . '_id', $invalidFields)) !== false) {
            unset($invalidFields[$key]);
        }
        if ($invalidFields) {
            if ($this->processMode !== self::MODE_CRM_FORM) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = t("Fields %s are not accepted in entity '$entity'", 0, [implode(', ', $invalidFields)]);
                return false;
            }

            $dati = array_diff_key($dati, array_flip($invalidFields));
        }

        /*
         * Prima di uscire memorizzo i relationsBundle
         */
        $this->pendingRelations = $relationBundles;
        return true;
    }

    /**
     * Controlla se esiste una relazione valida sul campo specificato e se i
     * dati passati mi devono generare un inserimento di dati nella relazione.
     * Questo metodo controlla anche se esistono delle "relazioni finte" cioè
     * field_ref inseriti su campi STRINGA..
     * 
     * Nel qual caso esista una relazione "vera" associata al field, il metodo
     * torna true e modifica il valore originale con i dati della relazione e
     * i dati da inserire in db
     * 
     * @param string $field
     * @param array $dataToInsert
     * @return boolean
     */
    private function checkRelationsOnField($field, &$dataToInsert)
    {

        if (!$field['fields_ref']) {
            return false;
        }

        // In realtà il field ref dovrebbe puntare alla tabella pivot non
        // alla tabella con cui è relazionata ad esempio ho aziende <-> tags
        // il field ref di aziende non dovrebbe puntare a tags, ma ad
        // aziende_tags (il nome della relazione).
        // ====
        // Per mantenere la retrocompatibilità vengono cercate entrambe le varianti
        if (!is_array($dataToInsert)) {
            return false;
        }

        $relations = $this->db->where_in('relations_name', [$field['entity_name'] . '_' . $field['fields_ref'], $field['fields_ref']])->get('relations');
        if ($relations->num_rows() > 0) {
            $relation = $relations->row();

            // Se in un form, metto un campo relazione di un'altra entità,
            // allora probabilmente voglio andare ad inserirlo con un PP
            // Quindi ignoro il campo, assumendo che verrà gestito
            // manualmente
            if (!in_array($field['entity_name'], [$relation->relations_table_1, $relation->relations_table_2])) {
                return false;
            }

            $dataToInsert = array(
                'entity' => $relation->relations_name,
                'relations_field_1' => $relation->relations_field_1,
                'relations_field_2' => $relation->relations_field_2,
                'value' => $dataToInsert
            );

            return true;
        } elseif ($dataToInsert && in_array(strtoupper($field['fields_type']), ['VARCHAR', 'TEXT'])) {
            $dataToInsert = implode(',', $dataToInsert);
        }

        return false;
    }


    /**
     * Inserisci i dati delle relazioni pendenti sull'id passato
     * 
     * @param int $savedId
     * @return null
     */
    private function savePendingRelations($savedId)
    {

        if (!$this->pendingRelations or !$savedId) {
            return;
        }

        foreach ($this->pendingRelations as $relationBundle) {
            /* ==========================================================
             * Prima di inserire i dati nella relazione faccio un delete 
             * dei record con relations_field_1 uguale al mio insert id
             * che corrispondono ai valori vecchi.
             * --
             * Nel caso di una modifica si eliminano valori vecchi
             * nel caso di un inserimento non si elimina niente.
             * Corretto?
             * ========================================================= */
            $this->db->delete($relationBundle['entity'], [$relationBundle['relations_field_1'] => $savedId]);
            if (is_array($relationBundle['value']) && $relationBundle['value']) {

                // Se $relation['value'] è vuoto allora anche
                // $relationFullData sarà vuoto
                $relationFullData = array_map(function ($value) use ($relationBundle, $savedId) {
                    //TODO: MP 20191108 Non è detto che il field_1 sia il saveId, nulla vieta di invertire le due tabella. Sarebbe da fare un check per capire dove va l'id salvato e dove va invece ilvalore della multiselect...
                    return [
                        $relationBundle['relations_field_1'] => $savedId,
                        $relationBundle['relations_field_2'] => $value
                    ];
                }, $relationBundle['value']);

                $this->db->insert_batch($relationBundle['entity'], $relationFullData);
            }
        }
        $this->pendingRelations = null;
    }










    /**
     * Lancia il processo di valutazione/validazione di un campo. Questo metodo
     * ritorna un booleano indicante il successo o meno dell'operazione.
     * Il valore passato per riferimento, quindi se necessario effettuare
     * aggiustamenti per renderlo salvabile su database viene manipolato
     * direttamente da qui
     * 
     * @param array $field
     * @param mixed $value
     * @param array $originalValue
     * @return boolean
     */
    protected function sanitizeInput(array $field, &$value, $originalValue)
    {

        $typeSQL = strtoupper($field['fields_type']);
        $typeHTML = $field['fields_draw_html_type'];


        switch ($typeHTML) {
            case 'date':
            case 'date_time':
                if (($value = $this->filterInputDate($value, $typeHTML === 'date_time')) === false) {
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = "{$field['fields_draw_label']} non è una data valida";
                    return false;
                }
                break;

            case 'wysiwyg':
                if ($value) {
                    $bURL = (function_exists('base_url_admin') ? base_url_admin() : base_url());
                    $value = str_replace($bURL, '{base_url}', $value);
                }
                break;

            case 'input_password':
                // In modifica se nei dati passati nel post non c'è la
                // password, allora non devo calcolare la hash nuovamente
                $value = $originalValue ? md5($originalValue) : null;
                break;

            case 'upload':
            case 'upload_image':
                $value = $value ?: null;
                break;


            case 'polygon':
            case 'polygon_multi':
                // Lancia l'utility per filtrare input di tipo geography
                $value = $this->filterInputPolygonMulti($value);
                break;
            case 'polygon_collection':
                // Lancia l'utility per filtrare input di tipo geography
                $value = $this->filterInputPolygonCollection($value);

                break;
            case 'date_range':
                if ($typeSQL === 'DATERANGE' && !isValidDateRange($value)) {
                    $value = '[' . implode(',', array_map(function ($date) {
                        return date_toDbFormat($date);
                    }, explode(' - ', $value))) . ']';
                }
                break;
        }


        switch ($typeSQL) {
            case 'INT':
                if (!is_numeric($value) && ((int) $value == $value)) {
                    if ($value == '' && ($field['fields_required'] === DB_BOOL_FALSE or $field['fields_default'] !== '')) {
                        // Se il campo è stato lasciato vuoto e non è richiesto
                        // oppure (se è richiesto), ma ha un default... lo metto
                        // a null in modo che il sistema lo gestisca
                        // automaticamente
                        $value = null;
                    } else {
                        throw new ApiException(sprintf("Il campo %s dev'essere un intero", $field['fields_draw_label']));
                    }
                }

                if ($value && !preg_match('/^-?\d+$/', $value)) {
                    // Il valore non contiene solo numeri, quindi non è un 
                    // intero valido
                    throw new ApiException(sprintf("Il campo %s dev'essere inserito senza caratteri speciali (ad es. `,` e `.`)", $field['fields_draw_label']));
                }
                //----
                /*if(isset($value) && !is_numeric($value)) {
                    $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                }

                if(($value === '' OR $value === false) && $field['fields_required'] === DB_BOOL_FALSE ) {
                    $value = null;
                }*/
                break;

            case 'FLOAT':
                $float = str_replace(',', '.', $value);
                $value = (float) filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;

            case DB_BOOL_IDENTIFIER:
                if (isset($value)) {
                    // Se il valore è t/f ok, altrimenti prendi il valore
                    // booleano
                    $value = in_array($value, [DB_BOOL_TRUE, DB_BOOL_FALSE]) ? $value : ($value ? DB_BOOL_TRUE : DB_BOOL_FALSE);
                } elseif (!trim($field['fields_default'])) {
                    // Se invece è a null e non ho nessun tipo di default,
                    // allora lo imposto come false automaticamente (vd.
                    // checkbox che se le uso in modalità booleana, il false non
                    // è settato)
                    $value = DB_BOOL_FALSE;
                }
                break;

            case 'TIMESTAMP WITHOUT TIME ZONE':
                $value = $value ?: null;
                break;

            case 'DATERANGE':
                if (!isValidDateRange($value)) {
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = "{$field['fields_draw_label']} non è un date-range nel formato corretto";
                    return false;
                }
                break;
            case 'GEOGRAPHY':
                // Lancia l'utility per filtrare input di tipo geography
                $value = $this->filterInputGeo($value);
                break;
            case 'INT4RANGE':
            case 'INT8RANGE':
                // Lancia l'utility per filtrare input di tipo intrange
                $arrval = extract_intrange_data($value);

                if ($arrval) {
                    // Se uno dei due elementi fosse una stringa vuota
                    // (che è !== 0) significa che l'intervallo non ha limite e
                    // questa è una cosa buona e giusta
                    if ($arrval['from'] <= $arrval['to'] or $arrval['from'] === '' or $arrval['to'] === '') {
                        return $arrval['range'];
                    } else {
                        $this->error = self::ERR_VALIDATION_FAILED;
                        $this->errorMessage = "L'estremo inferiore di {$field['fields_draw_label']} non può essere maggiore dell'estremo superiore";
                        return false;
                    }
                } else {
                    $value = null;
                }
            case 'JSON':
                if ($value === '') {
                    // Il json non mi accetta stringhe vuote
                    $value = null;
                }
                break;
        }

        return true;
    }



    /**
     * Formattazione corretta di un input in una stringa date/datetime
     * 
     * @param mixed $value
     * @param boolean $withTime
     * @return string
     */
    protected function filterInputDate($value, $withTime)
    {
        if (!$value) {
            return null;
        } else {
            // Il campo non era vuoto, quindi valido la stringa e se
            // è vuota allora vuol dire che il formato era sbagliato
            $fn = $withTime ? 'dateTime_toDbFormat' : 'date_toDbFormat';
            $value = $fn($value);

            if (!$value) {
                return false;
            }

            return $value;
        }
    }

    /**
     * Formattazione corretta di un tipo geography
     * 
     * @param mixed $value
     * @return string|null
     * 
     * @todo Manca il controlloche il value passato non sia già una geography pronta. Verificare la presenza o meno del separatore tra lat e lon
     */
    protected function filterInputGeo($value)
    {

        if (isset($value['geo'])) {
            return $value['geo'];
        }

        $exp = [];
        if (isset($value['lat']) && isset($value['lng'])) {
            $exp = [$value['lat'], $value['lng']];
        } elseif (isset($value['lat']) && isset($value['lon'])) {
            $exp = [$value['lat'], $value['lon']];
        } elseif (is_array($value) && count($value) > 1) {
            $exp = array_values($value);
        } elseif (is_string($value)) {
            $nvalue = str_replace(',', ';', $value);
            $exp = (strpos($nvalue, ';') !== false) ? explode(';', $nvalue) : [];
        }

        if (isset($exp[0]) && isset($exp[1]) && $exp[0] !== '' && $exp[1] !== '') {
            if ($this->db->dbdriver == 'postgre') {
                return $this->db->query("SELECT ST_GeographyFromText('POINT({$exp[1]} {$exp[0]})') AS geography")->row()->geography;
            } else {
                return $this->db->query("SELECT ST_GeomCollFromText('POINT({$exp[0]} {$exp[1]})') AS geography")->row()->geography;
            }
        } else {
            // Ultima spiaggia: potrei aver passato una geography già pronta:
            // una geography non è una stringa numerica..
            return ($value && is_string($value) && !is_numeric($value)) ? $value : null;
        }
    }

    /**
     * Formattazione di un tipo di dato "Multipolygon"
     * 
     * 
     * @param mixed $value Attenzione che value deve essere nel formato array, con poligoni espressi in lon lat e non lat lon
     * @return string|null
     */
    public function filterInputPolygonMulti($value)
    {
        //debug($value,true);
        if (isset($value['multipolygon'])) {
            return $value['multipolygon'];
        }
        //FORMATO: ST_GeographyFromText('MULTIPOLYGON(((lon lat, lon lat), (.......)))') AS geography
        if (!is_array($value) || $value == array()) {
            //debug('test');
            return null;
        } else {

            $collections = array();
            //debug($value,true);
            if (array_key_exists('polygons', $value)) { //Processo prima i poligoni
                $value['polygons'] = array_map(
                    function ($polygon) {
                        $polygon_expl = explode(',', $polygon);
                        //Se il poligono non è chiuso (ultimo punto uguale al primo, come vuole postgis)
                        if ($polygon_expl[0] != $polygon_expl[count($polygon_expl) - 1]) {
                            $polygon_expl[] = $polygon_expl[0];
                        }

                        // Voglio assicurarmi che non ci siano due poligoni
                        // uguali: rimuovo quindi l'ultimo punto che è
                        // sicuramente === al primo e lo reinserisco
                        $last = array_pop($polygon_expl);
                        $polygon_expl = array_unique($polygon_expl);   // Array unique fa anche un sort, ma non tocca le chiavi, quindi basta fare un ksort del risultato e ci sono
                        ksort($polygon_expl);
                        $polygon_expl[] = $last;
                        $polygon_expl = array_values($polygon_expl);    // Normalizzo le chiavi

                        $polygon = implode(',', $polygon_expl);
                        //Aggiungo le parentesi aperta e chiusa
                        return '(' . $polygon . ')';
                    },
                    $value['polygons']
                );
            } else {
                $value['polygons'] = array();
            }
            if (array_key_exists('circles', $value)) { //Poi processo i cerchi
                $value['circles'] = array_map(
                    function ($circle) {
                        $circle_expl = is_array($circle) ? $circle : explode(',', $circle);
                        $center = $circle_expl[0];
                        $radius = $circle_expl[1];
                        //Creo la geometry polygon che rappresenta il cerchio
                        if ($this->db->dbdriver == 'postgre') {
                            $points = json_decode($this->db->query("SELECT ST_AsGeoJSON(ST_Buffer(ST_GeographyFromText('POINT($center)'), $radius)) as circle")->row()->circle, true)['coordinates'];
                        } else {
                            $points = json_decode($this->db->query("SELECT ST_AsGeoJSON(ST_Buffer(ST_GeomCollFromText('POINT($center)'), $radius)) as circle")->row()->circle, true)['coordinates'];
                        }
                        //debug($points,true);
                        $points_space_imploded = [];
                        foreach ($points[0] as $point) {
                            $points_space_imploded[] = implode(' ', $point);
                        }

                        $polygon_circle = implode(',', $points_space_imploded);

                        return '(' . $polygon_circle . ')';
                    },
                    $value['circles']
                );
            } else {
                $value['circles'] = array();
            }

            //debug($value['circles'],true);

            $merged_polygons = array_merge($value['circles'], $value['polygons']);

            //debug($merged_polygons,true);

            $multipolygon = "MULTIPOLYGON((" . implode(',', $merged_polygons) . "))";

            //Infine metto tutto dentro un unico calderone GEOMETRYCOLLECTION    
            if ($merged_polygons != array()) {

                // V3
                $debug = $this->db->db_debug;
                $this->db->db_debug = false;
                if ($this->db->dbdriver == 'postgre') {
                    $query = $this->db->query("SELECT ST_Multi(ST_BuildArea(ST_Collect(ST_CollectionExtract(ST_GeographyFromText('$multipolygon')::geometry, 3))))::geography AS geography");
                } else {
                    $query = $this->db->query("SELECT ST_Multi(ST_BuildArea(ST_Collect(ST_CollectionExtract(ST_GeographyFromText('$multipolygon')::geometry, 3))))::geography AS geography");
                    //debug($this->db->last_query());
                }


                if (!$query) {
                    //return null;  // Decommentare per task di importazione, poi ripristinare
                    //throw new ApiException('Il poligono disegnato non è valido');
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = "Il poligono disegnato non è valido";
                    return false;
                }

                $this->db->db_debug = $debug;
                return $query->row()->geography;

                // V2: questa lascia i buchi in caso di poligoni intersecati (a causa di ST_MakeValid)
                //return $this->db->query("SELECT ST_Multi(ST_BuildArea(ST_MakeValid(ST_Collect(ST_GeographyFromText('$multipolygon')::geometry))))::geography AS geography")->row()->geography;

                // V1: non ricordo perché, ma se abbiamo fatto una v2 ci dev'essere un motivo...
                /*$valid = $this->db->query("SELECT ST_IsValid(ST_Collect(ST_GeographyFromText('$multipolygon')::geometry)) AS valid")->row()->valid;
                //debug($multipolygon);
                
                if ($valid === 't') {
                    // La geometria ottenuta dall'aggregazione dei poligoni è corretta, quindi procedo
                    return $this->db->query("SELECT ST_Multi(ST_BuildArea(ST_Collect(ST_GeographyFromText('$multipolygon')::geometry)))::geography AS geography")->row()->geography;
                } else {
                    // La geometria non può essere ottimizzata, quindi prendo i poligoni così come sono
                    return $this->db->query("SELECT ST_GeographyFromText('$multipolygon') AS geography")->row()->geography;
                }*/
            } else {
                return null;
            }
        }
    }

    /**
     * Formattazione di un tipo di dato "GEOMETRYCOLLECTION"
     * 
     * 
     * @param array $value Attenzione che value deve essere nel formato array, con poligoni espressi in lon lat e non lat lon
     * @return string|null
     */
    protected function filterInputPolygonCollection(array $value)
    {
        debug('TODO...', true);
    }

    /**
     * Cloni del model datab del crm
     */
    protected function run_post_process($entity_id, $when, $data = [])
    {
        $this->runDataProcessing($entity_id, $when, $data);
    }

    public function runDataProcessing($entity_id, $pptype, $data = [])
    {

        if (!$this->processEnabled) {
            return $data;
        }

        if (!is_numeric($entity_id) && is_string($entity_id)) {
            $entity = $this->crmEntity->getEntity($entity_id);
            if (!$entity) {
                return $data;
            }
            $entity_id = $entity['entity_id'];
        }

        $this->preLoadDataProcessors();
        if (!empty($this->_loadedDataProcessors[$this->processMode][$entity_id][$pptype])) {
            foreach ($this->_loadedDataProcessors[$this->processMode][$entity_id][$pptype] as $function) {
                try {
                    eval($function['post_process_what']);
                } catch (Exception $ex) {
                    throw new ApiException($ex->getMessage(), self::ERR_POST_PROCESS, $ex);
                }
            }
        }

        return $data;
    }

    private function preLoadDataProcessors()
    {
        if (empty($this->_loadedDataProcessors)) {
            $process = $this->db->get('post_process')->result_array();

            $this->_loadedDataProcessors = [
                self::MODE_DIRECT   => [],
                self::MODE_API_CALL => [],
                self::MODE_CRM_FORM => [],
            ];

            foreach ($process as $function) {

                $e_id = $function['post_process_entity_id'];
                $type = $function['post_process_when'];

                if ($function['post_process_crm'] == DB_BOOL_TRUE) {
                    $this->_loadedDataProcessors[self::MODE_CRM_FORM][$e_id][$type][] = $function;
                }

                if ($function['post_process_api'] == DB_BOOL_TRUE) {
                    $this->_loadedDataProcessors[self::MODE_API_CALL][$e_id][$type][] = $function;
                }

                if ($function['post_process_apilib'] == DB_BOOL_TRUE) {
                    $this->_loadedDataProcessors[self::MODE_DIRECT][$e_id][$type][] = $function;
                }
            }
        }
    }




    /**
     * Upload di tutti i file dentro all'array $_FILES e opzionalmente rimuovili
     * dalla superglobal
     * 
     * @param type $clearFilesSuperglobal
     * @return boolean
     */
    private function uploadAll($clearFilesSuperglobal = false)
    {

        if (!$_FILES) {
            return [];
        }

        $output = [];
        $this->load->library('upload', [
            'upload_path' => FCPATH . 'uploads/',
            'allowed_types' => '*',
            'max_size' => defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10000,
            'encrypt_name' => true
        ]);

        foreach ($_FILES as $fieldName => $fileData) {

            if (empty($fileData['name'])) {
                continue;
            }

            $arrayMode = is_array($fileData['name']);
            if ($arrayMode) {
                // Qui chiamo ricorsivamente questo metodo sul sottoarray con
                // delle chiavi ottenute mediante uniqid(). Mi salvo una copia
                // di backup dell'array files da ripristinare dopo la chiamata
                // ricorsiva
                $_backup = $_FILES;
                $_FILES = [];

                // Calcolo la mappatura delle chiavi. Assumo che l'array files
                // non sia stato manipolato in modi strani e che quindi ci siano
                // le stesse chiavi dentro ai sottoarray [name], [type],
                // [tmp_name], [error] e [size]
                $keyMap = [];
                foreach (array_keys($fileData['name']) as $key) {
                    if (!$fileData['name'][$key]) {
                        continue;
                    }

                    $newKey = uniqid();
                    $keyMap[$newKey] = $key;
                    $_FILES[$newKey] = [
                        'name' =>     $fileData['name'][$key],
                        'type' =>     $fileData['type'][$key],
                        'tmp_name' => $fileData['tmp_name'][$key],
                        'error' =>    $fileData['error'][$key],
                        'size' =>     $fileData['size'][$key]
                    ];
                }


                // Chiamata ricorsiva... se fallisce (= ritorna false) allora
                // fallisce anche questa
                if (($out = $this->uploadAll(true)) === false) {
                    return false;
                }

                // Ripristino il $_FILES e rimappo con le vecchie chiavi i
                // valori
                $_FILES = $_backup;
                foreach ($out as $key => $filename) {
                    $oldKey = $keyMap[$key];
                    $output[$fieldName][$oldKey] = $filename;
                }
            } else {
                // In modalità normale procedo senza problemi
                if (!$this->upload->do_upload($fieldName)) {
                    // Errore upload
                    $this->error = self::ERR_UPLOAD_FAILED;
                    $this->errorMessage = $this->upload->display_errors();
                    return false;
                }

                // Upload ok
                $uploadData = $this->upload->data();

                defined('LOGIN_ENTITY') or @include __DIR__ . '/../config/enviroment.php';
                $uploadDepthLevel = defined('UPLOAD_DEPTH_LEVEL') ? (int) UPLOAD_DEPTH_LEVEL : 0;

                if ($uploadDepthLevel > 0) {
                    // Voglio comporre il nome locale in modo che se il nome del file fosse
                    // pippo.jpg la cartella finale sarà: ./uploads/p/i/p/pippo.jpg
                    $localFolder = '';
                    for ($i = 0; $i < $uploadDepthLevel; $i++) {
                        // Assumo che le lettere siano tutte alfanumeriche,
                        // alla fine le immagini sono tutte delle hash md5
                        $localFolder .= strtolower(isset($uploadData['file_name'][$i]) ? $uploadData['file_name'][$i] . DIRECTORY_SEPARATOR : '');
                    }

                    if (!is_dir(FCPATH . 'uploads/' . $localFolder)) {
                        mkdir(FCPATH . 'uploads/' . $localFolder, DIR_WRITE_MODE, true);
                    }

                    if (rename(FCPATH . 'uploads/' . $uploadData['file_name'], FCPATH . 'uploads/' . $localFolder . $uploadData['file_name'])) {
                        $uploadData['file_name'] = $localFolder . $uploadData['file_name'];
                    }
                }

                $output[$fieldName] = $uploadData['file_name'];
            }


            if ($clearFilesSuperglobal) {
                unset($_FILES[$fieldName]);
            }
        }

        return $output;
    }


    /**
     * Aggiungi una nuova voce al log di sistema con il flag system = false.
     * Metodo userland
     * 
     * @param int $type
     * @param string $titlePattern
     * @param array $extra
     */
    public function log($type, $titlePattern, array $extra)
    {
        $this->addLogEntry($type, $titlePattern, false, $extra);
    }


    /**
     * Aggiungi una nuova voce al log di sistema con il flag system = true
     * 
     * @internal Attenzione, questo metodo non andrebbe utilizzato, è pensato
     *           per il core del crm, in quanto il parametro extra deve essere
     *           riempito opportunamente. Usare Apilib::log piuttosto
     * 
     * @param int $type
     * @param array $extra
     * @throws UnexpectedValueException
     */
    public function logSystemAction($type, array $extra = [])
    {
        if ($type == self::LOG_ACCESS) {
            // Ho già loggato l'accesso di questo utente oggi? Allora non voglio rifarlo
            if ($this->db->dbdriver != 'postgre') {
                $exists = $this->db->query('
                    SELECT COUNT(*) AS count
                    FROM log_crm
                    WHERE log_crm_user_id = ? AND
                          (log_crm_type = ? OR log_crm_type = ?) AND
                          DATE(log_crm_time) = CURDATE()
                ', [$this->auth->get('id'), self::LOG_ACCESS, self::LOG_LOGIN])->row()->count > 0;
            } else {
                $exists = $this->db->query('
                    SELECT COUNT(*) AS count
                    FROM log_crm
                    WHERE log_crm_user_id = ? AND
                          (log_crm_type = ? OR log_crm_type = ?) AND
                          log_crm_time::DATE = CURRENT_DATE
                ', [$this->auth->get('id'), self::LOG_ACCESS, self::LOG_LOGIN])->row()->count > 0;
            }

            if ($exists) {
                return;
            }
        }

        // Il tipo è valido?
        if (!isset($this->logTitlePatterns[$type])) {
            throw new UnexpectedValueException(t("Type '%s' not valid", 0, [$type]));
        }

        // Siamo ok, possiamo inserire
        $this->addLogEntry($type, t($this->logTitlePatterns[$type]), true, $extra);
    }

    /**
     * @param int $type
     * @param string $title
     * @param bool $system
     * @param array $extra
     */
    private function addLogEntry($type, $title, $system, array $extra)
    {

        if (!is_numeric($type)) {
            throw new InvalidArgumentException('Cannot log the action: type must be numeric');
        }

        if (!$title) {
            throw new InvalidArgumentException('Cannot log the action: title is empty');
        }

        if (isset(get_instance()->auth) && defined('LOGIN_NAME_FIELD')) {
            $uid = $this->auth->get('id');
            $uname = trim(implode(' ', [$this->auth->get(LOGIN_NAME_FIELD), $this->auth->get(LOGIN_SURNAME_FIELD)])) ?: null;
        } else {
            $uid = $uname = null;
        }

        // Preparo array base
        $logEntry = [
            'log_crm_user_id' => $uid,
            'log_crm_user_name' => $uname,

            'log_crm_ip_addr' => filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?: 'N/A',
            'log_crm_user_agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') ?: null,
            'log_crm_referer' => filter_input(INPUT_SERVER, 'HTTP_REFERER') ?: null,

            'log_crm_time' => date('Y-m-d H:i:s'),  // Server time
            'log_crm_type' => (int) $type,
            'log_crm_system' => $system ? DB_BOOL_TRUE : DB_BOOL_FALSE,
            'log_crm_extra' => $extra,
        ];

        // Calcolo il titolo rimpiazzando i dati dal record principale
        $logEntry['log_crm_title'] = str_replace_placeholders($title, $logEntry);
        $logEntry['log_crm_extra'] = $logEntry['log_crm_extra'] ? json_encode($logEntry['log_crm_extra']) : null;

        $this->db->insert('log_crm', $logEntry);
    }




    /**
     * CrmEntity Factory
     * 
     * @param string $entity
     * @return \Crmentity
     */
    private function getCrmEntity($entity = null)
    {
        return new Crmentity($entity, [
            $this->currentLanguage,
            $this->fallbackLanguage
        ]);
    }
}

class ApiException extends Exception
{
}
