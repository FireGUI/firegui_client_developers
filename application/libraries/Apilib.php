<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

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

    const MODE_MAPPING = [
        1 => 'direct',
        2 => 'api',
        3 => 'form',
    ];

    const ERR_INVALID_API_CALL = 1;
    const ERR_NO_DATA_SENT = 2;
    const ERR_REDIRECT_FAILED = 3;
    const ERR_ENTITY_NOT_EXISTS = 4;
    const ERR_VALIDATION_FAILED = 5;
    const ERR_UPLOAD_FAILED = 6;
    const ERR_INTERNAL_DB = 7; // Internal Server Error: Database Query
    const ERR_POST_PROCESS = 8; // Questo errore di norma non usa il messaggio standard
    const ERR_GENERIC = 8; // Internal Server Error: Generico. Genera report email

    const LOG_LOGIN = 1; // Login action
    const LOG_LOGIN_FAIL = 2; // Logout action
    const LOG_LOGOUT = 3; // Logout action
    const LOG_ACCESS = 4; // Daily access
    const LOG_CREATE = 5; // Apilib::create action
    const LOG_CREATE_MANY = 6; // Apilib::createMany action
    const LOG_EDIT = 7; // Apilib::edit action
    const LOG_DELETE = 8; // Apilib::delete action

    private $not_deferrable_pp = [
        'pre-login',
        'login',
        'pre-search',
        'search',
        'pre-save',
        'pre-delete',
        'pre-update',
        'pre-insert',
        'pre-validation-update',
        'pre-validation-insert',
        'pre-validation-save',
        'pre-validation'
    ];
    private $deferrable_pp = [
        'update',
        'delete',
        'insert',
        'save'
    ];
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
        self::LOG_LOGIN => "User {log_crm_user_name} has logged in",
        self::LOG_LOGIN_FAIL => "User {log_crm_user_name} failed to log in",
        self::LOG_LOGOUT => "User {log_crm_user_name} has logged out",
        self::LOG_ACCESS => "User {log_crm_user_name} succesfully access",
        self::LOG_CREATE => "New record ({log_crm_extra id}) on entity {log_crm_extra entity}",
        self::LOG_CREATE_MANY => "New bulk record creation on entity {log_crm_extra entity}",
        self::LOG_EDIT => "Edited record ({log_crm_extra id}) on entity {log_crm_extra entity}",
        self::LOG_DELETE => "Deleted record ({log_crm_extra id}) on entity {log_crm_extra entity}",
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
        //TODO: extend my_cache_file driver to work also with other driver...
        $this->load->model('crmentity');
        $this->crmEntity = $this->crmentity; //Backward compatibiliy
        $this->load->driver('Cache/drivers/MY_Cache_file', null, 'mycache');

        //debug(get_class_methods($this->cache), true);
        $this->previousDebug = $this->db->db_debug;
        //$this->crmEntity = $this->getCrmEntity();


        $this->cache_config = $this->mycache->getCurrentConfig();

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

        $cache_key = "apilib/apilib.list.{$entity}";
        if (!$this->isCacheEnabled() || !($out = $this->mycache->get($cache_key))) {
            $out = $this->crmentity->get_data_full_list($entity, null, [], null, 0, null, null, false, $depth);
            if ($this->isCacheEnabled()) {
                $tags = $this->mycache->buildTagsFromEntity($entity);
                $this->mycache->save($cache_key, $out, $this->mycache->CACHE_TIME, $tags);
            }
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

        $cache_key = "apilib/apilib.item.{$entity}.{$id}";
        if (!$this->isCacheEnabled() || !($out = $this->mycache->get($cache_key))) {
            $out = $this->crmentity->get_data_full($entity, $id, $maxDepthLevel);
            if ($this->isCacheEnabled()) {
                $tags = $this->mycache->buildTagsFromEntity($entity, $id);
                $this->mycache->save($cache_key, $out, $this->mycache->CACHE_TIME, $tags);
            }
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

        //debug($data, true);

        $success = $this->db->update($entity, $data, [$entity . '_id' => $id]);

        return $success;
    }

    /**
     * Crea un nuovo record con il post passato e lo ritorna via json
     * @param string $entity
     */
    public function create($entity = null, $data = null, $returnRecord = true, $direct_db = false)
    {
        if ($direct_db) {
            $output = $this->db->insert($entity, $data);
            $this->mycache->clearCacheTags([$entity]);
            return $output;
        }

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $_data = $this->extractInputData($data);

        //rimuovo i campi password passati vuoti...
        $fields = $this->crmEntity->getFields($entity);

        $this->autoFillSourceFields($fields, $_data);

        foreach ($fields as $field) {
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
                }

                $_data[$field['fields_name']] = $date->format('Y-m-d H:i:s');
            }
        }
        //debug($_data);
        if ($this->processData($entity, $_data, false)) {
            //debug($_data, true);
            if (!$this->db->insert($entity, $_data)) {
                $this->showError(self::ERR_GENERIC);
            }

            $id = $this->db->insert_id();

            $this->savePendingRelations($id);

            $this->runDataProcessing($entity, 'insert', $this->runDataProcessing($entity, 'save', $this->view($entity, $id)));

            $this->mycache->clearEntityCache($entity);

            // Prima di uscire voglio ripristinare il post precedentemente modificato
            $_POST = $this->originalPost;

            // Inserisco il log
            //20211028 - Deprecated because now creations are managed by logActivity
            //20220809 - Restored
            $this->logSystemAction(self::LOG_CREATE, ['entity' => $entity, 'id' => $id]);

            if (defined('LOG_ENTITIES_ARRAY') && in_array($entity, LOG_ENTITIES_ARRAY)) {
                $entity_data = $this->crmentity->getEntity($entity);

                $this->logActivity(self::LOG_CREATE, [
                    'entity_data' => $entity_data,
                    'data_id' => $id,
                    'json_data' => json_encode(['data' => $_data, ['post' => $_POST]]),
                    'entity_full_data' => $this->crmentity->getEntityFullData($entity_data['entity_id']),

                ]);
            }

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
    public function edit($entity = null, $id = null, $data = null, $returnRecord = true, $direct_db = false)
    {
        if ($direct_db) {
            $output = $this->db->where($entity . '_id', $id)->update($entity, $data);
            $this->mycache->clearCacheTags([$entity]);
            return $output;
        }

        if (!$entity or !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        $_data = $this->extractInputData($data);

        // rimuovo i campi password passati vuoti...
        $fields = $this->crmEntity->getFields($entity);
        $this->autoFillSourceFields($fields, $_data);
        foreach ($fields as $field) {
            if ($field['fields_draw_html_type'] == 'input_password' && empty($_data[$field['fields_name']])) {
                unset($_data[$field['fields_name']]);
            }
            //Per i campi date e date_time, se sono su mysql li converto in formato mysql
            elseif ($this->db->dbdriver != 'postgre' && in_array($field['fields_type'], ['TIMESTAMP']) && !empty($_data[$field['fields_name']])) {
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

            if (!$this->updateById($entity, $id, $_data)) {
                $this->showError(self::ERR_GENERIC);
            }

            $this->savePendingRelations($id);
            $this->updateMultiuploadsRef($entity, $id, $_data);

            if ($this->db->CACHE) {
                $this->db->CACHE->delete_all();
            }

            // Al pp save non arrivavano i dati old. Li mergio coi dati dell'entità
            $newData = $this->runDataProcessing(
                $entity,
                'save',
                array_merge(
                    $this->getById($entity, $id),
                    ['old' => $oldData]
                )
            );
            $data_for_processing = [
                'new' => $newData,
                'old' => $oldData,
                'diff' => array_diff_assoc_recursive($newData, $oldData),
                'value_id' => $id,
            ];
            $this->runDataProcessing($entity, 'update', $data_for_processing);

            //TODO: change with specific tags
            $this->mycache->clearEntityCache($entity);

            $_POST = $this->originalPost;

            // Inserisco il log
            //20211028 - Deprecated and managed by logActivity
            //20220809 Restored
            $this->logSystemAction(self::LOG_EDIT, ['entity' => $entity, 'id' => $id]);
            if (
                defined('LOG_ENTITIES_ARRAY') && in_array($entity, LOG_ENTITIES_ARRAY)
            ) {
                $entity_data = $this->crmentity->getEntity($entity);

                $this->logActivity(self::LOG_EDIT, [
                    'entity_data' => $entity_data,
                    'data_id' => $id,
                    'json_data' => json_encode(
                        array_merge(
                            ['data' => $_data, 'post' => $_POST],
                            $data_for_processing
                        )
                    ),
                    'entity_full_data' => $this->crmentity->getEntityFullData($entity_data['entity_id']),
                ]);
            }

            return $returnRecord ? $this->view($entity, $id) : $id;
        } else {
            $_POST = $this->originalPost;
            $this->showError();
        }
    }

    /*
    Usefull to centralize saving methods in on call
    */
    public function save($entity = null, $data, $id = null)
    {
        if ($id) {
            return $this->edit($entity, $id, $data);
        } else {
            return $this->create($entity, $data);
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

        $this->mycache->clearEntityCache($entity);

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

        return $data;
    }
    private function getFieldSourceValue($field, $value, $fields)
    {
        $entity_id = $field['fields_entity_id'];
        $entity = $this->datab->get_entity($entity_id); //field referencing entity
        //$entity_fields = $this->datab->get_entity_fields($entity_id); //field referencing entity fields
        $field_ref = $field['fields_ref']; //projects
        $field_source = $field['fields_source']; //tickets_customer_id
        $field_source_data = false;
        foreach ($fields as $_field) {
            if ($_field['fields_name'] == $field_source) {
                $field_source_data = $_field;
                break;
            }
        }
        if (!$field_source_data) {
            return false;
            //throw new ApiException("Field source '{$field_source}' not found in entity {$entity['entity_name']}.");
        }
        $field_source_ref = $field_source_data['fields_ref'];

        if (!$field_source_ref) {
            return false;
            //throw new ApiException("Field source ref of {$field_source_data['fields_name']} not found.");
        }
        //debug($field_ref, true);
        //Search the field in $field_ref that reference $field_source_ref
        $field_referencing_source_ref = false;
        foreach ($this->datab->get_entity_fields($field_ref) as $_field) {
            if ($_field['fields_ref'] == $field_source_ref) {
                $field_referencing_source_ref = $_field;
            }
        }
        if (!$field_referencing_source_ref) {
            return false;
            //throw new ApiException("Field referencing '$field_source_ref' not found.");
        }
        $field_referencing_source_ref_name = $field_referencing_source_ref['fields_name'];
        $ref_value = $this->db
            ->select($field_referencing_source_ref['fields_name'])
            ->where($field_ref . '_id', $value)
            ->get($field_ref)
            ->row()->$field_referencing_source_ref_name;

        return $ref_value;
    }
    private function autoFillSourceFields($fields, &$data)
    {
        foreach ($fields as $field) {
            if ($field['fields_source'] && empty($data[$field['fields_source']])) {
                if (!empty($data[$field['fields_name']])) {
                    $fill_value =
                        $this->getFieldSourceValue($field, $data[$field['fields_name']], $fields);

                    //Retry for all fields because now &$data has changed and potential has more fields values
                    if ($fill_value) {
                        $data[$field['fields_source']] = $fill_value;
                        $this->autoFillSourceFields($fields, $data);
                    }
                } else {
                    continue;
                }
            }
        }
    }

    /**
     * Delete selected record
     * @param string $entity
     */
    public function delete($entity = null, $id = null)
    {
        //Accept id as array so I expect to have all data (ex: foreach $rows as $row... $this->apilib->delete('entity_name', $row));
        if (is_array($id)) {
            $id = $id[$entity . '_id'];
        }
        if (!$entity || !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }



        // integrazione soft-delete
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
        $record = $this->apilib->view($entity, $id);
        $this->runDataProcessing($entity, 'pre-delete', ['id' => $id, 'data' => $record, 'entity' => $entity]);
        if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
            $this->db->where($entity . '_id', $id)->update($entity, [$entityCustomActions['soft_delete_flag'] => DB_BOOL_TRUE]);
        } else {
            // $entity_fields = $this->db
            //     ->join('entity', 'fields.fields_entity_id = entity.entity_id')
            //     ->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id')
            //     ->like('fields_draw_html_type', 'upload')
            //     ->where('entity_name', $entity)
            //     ->get('fields')->result_array();

            // $content = $this->view($entity, $id);

            // $files_to_delete = [];

            // if (!empty($entity_fields)) {
            //     foreach ($entity_fields as $field) {
            //         $field_name = $field['fields_name'];

            //         if (!empty($content[$field_name])) {
            //             if (is_array($content[$field_name])) { // is multupload with relation
            //                 foreach ($content[$field_name] as $file) {
            //                     $files_to_delete[] = $file;
            //                 }
            //             } else {
            //                 if (is_valid_json($content[$field_name])) { // is multupload json
            //                     $files = json_decode($content[$field_name], true);

            //                     foreach ($files as $file) {
            //                         $files_to_delete[] = $file['path_local'];
            //                     }
            //                 } else { // is single file upload (normal/image)
            //                     $files_to_delete[] = $content[$field_name];
            //                 }
            //             }
            //         }
            //     }
            // }

            // if (!empty($files_to_delete)) {
            //     $this->load->model('uploads');

            //     $this->uploads->removeUploads($files_to_delete, false);
            // }

            $this->db->delete($entity, [$entity . '_id' => $id]);
        }
        $this->runDataProcessing($entity, 'delete', ['id' => $id, 'data' => $record, 'entity' => $entity]);
        $this->logSystemAction(self::LOG_DELETE, ['entity' => $entity, 'id' => $id, 'data' => json_encode($record)]);
        $this->db->trans_complete();

        //TODO: clear only cache entity could work only if entity tags will be related to grids pointing (or left joining) this entity, to enable clear also grid data cache
        //        $this->clearEntityCache($entity);
        $this->mycache->clearEntityCache($entity);

        //debug('STOP DELETED!', true);

        return ['id' => $id, 'data' => $record, 'entity' => $entity];
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
    // public function tableList()
    // {

    //     $entities = $this->db->order_by('entity_name')->get('entity')->result_array();

    //     $tables = [];
    //     foreach ($entities as $entity) {
    //         $table = [
    //             'name' => $entity['entity_name'],
    //             'fields' => [],
    //         ];
    //         $fields = $this->db->order_by('fields_name')->get_where('fields', ['fields_entity_id' => $entity['entity_id']])->result_array();
    //         foreach ($fields as $field) {
    //             $table['fields'][] = $field['fields_name'] . ($field['fields_ref'] ? ' <small>[ref. from: <strong>' . $field['fields_ref'] . '</strong>]</small>' : '');
    //         }

    //         $tables[] = $table;
    //     }

    //     return $tables;
    // }

    //Rewrited function by chatgpt3!
    public function tableList()
    {
        $query = $this->db->query("
        SELECT e.entity_name, f.fields_name, f.fields_ref
        FROM entity e
        LEFT JOIN fields f ON f.fields_entity_id = e.entity_id
        ORDER BY e.entity_name, f.fields_name
    ");

        $results = $query->result_array();

        $tables = [];
        foreach ($results as $row) {
            $name = $row['entity_name'];
            if (!isset($tables[$name])) {
                $tables[$name] = [
                    'name' => $name,
                    'fields' => [],
                ];
            }
            $tables[$name]['fields'][] = $row['fields_name'] . ($row['fields_ref'] ? ' <small>[ref. from: <strong>' . $row['fields_ref'] . '</strong>]</small>' : '');
        }

        return array_values($tables);
    }

    public function entityList()
    {
        $entity_type_default = defined('ENTITY_TYPE_DEFAULT') ? ENTITY_TYPE_DEFAULT : 1;
        $entities = $this->db->order_by('entity_name')->get_where('entity', ['entity_type' => $entity_type_default])->result_array();

        $tables = [];
        foreach ($entities as $entity) {
            $table = [
                'name' => $entity['entity_name'],
                'fields' => [],
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

    public function isCacheEnabled()
    {
        $return = !empty($this->cache_config['apilib']['active']) && $this->mycache->isCacheEnabled();

        return $return;
    }

    public function search($entity = null, $input = [], $limit = null, $offset = 0, $orderBy = null, $orderDir = 'ASC', $maxDepth = 2, $eval_cachable_fields = null, $additional_parameters = [])
    {

        if (!$entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }

        if (!is_array($input)) {
            $input = $input ? [$input] : [];
        }
        $group_by = array_get($additional_parameters, 'group_by', null);

        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        // rimuovo la chiave entity per evitare che applichi un filtro AND `entity` = 'customers'
        unset($input['entity']);
        $cache_key = "apilib/apilib.search.{$entity}." . md5(serialize($input)) . ($limit ? '.' . $limit : '') . ($offset ? '.' . $offset : '') . ($orderBy ? '.' . md5(serialize($orderBy)) : '') . ($group_by ? '.' . md5(serialize($group_by)) : '') . '.' . md5(serialize($orderDir));

        if (!$this->isCacheEnabled() || !($out = $this->mycache->get($cache_key))) {
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
                //$this->load->model('crmentity');

                $entity_data = $this->crmentity->getEntity($entity);
            } catch (Exception $ex) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $ex->getMessage();
                return false;
            }

            $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);

            // Filtro per soft-delete se non viene specificato questo filtro nel where della grid
            if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
                //Se nel where c'è già un filtro specifico sul campo impostato come soft-delete, ignoro. Vuol dire che sto gestendo io il campo delete (es.: per mostrare un archivio o un history...)
                //Essendo $where un array di condizioni, senza perdere tempo a ciclare, lo implodo così analizzo la stringa (che poi di fatto è quello che fa dopo implodendo su " AND "
                if (stripos(implode(' ', $where), $entityCustomActions['soft_delete_flag']) === false) {
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

            try {


                $out = $this->crmEntity->get_data_full_list($entity, null, $where, $limit ?: null, $offset, $order, false, $maxDepth, [], ['group_by' => $group_by]);

                if ($this->isCacheEnabled()) {
                    $this->mycache->save($cache_key, $out, $this->mycache->CACHE_TIME, $this->mycache->buildTagsFromEntity($entity));
                }
            } catch (Exception $ex) {
                //throw new ApiException('Si è verificato un errore nel server', self::ERR_INTERNAL_DB, $ex);
                throw new ApiException($ex->getMessage(), self::ERR_INTERNAL_DB, $ex);
            }
        }

        return $this->runDataProcessing($entity, 'search', $this->sanitizeList($out));
    }

    public function searchFirst($entity = null, $input = [], $offset = 0, $orderBy = null, $orderDir = 'ASC', $maxDepth = 2, $additional_parameters = [])
    {
        if (!is_array($input)) {
            throw new ApiException("Passed input is not an array!");
        }



        // aggiunti parametri come per la search (serve per passare 0 dalla api->login come profondità, ad esempio).
        //debug($entity);
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
        // rimuovo la chiave entity per evitare che applichi un filtro AND `entity` = 'customers'
        unset($input['entity']);
        $cache_key = "apilib/apilib.count.{$entity}." . md5(serialize($input));

        if (!$this->isCacheEnabled() || !($out = $this->mycache->get($cache_key))) {
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
            $entity_data = $this->crmEntity->getEntity($entity);
            $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);

            //debug($entityCustomActions, true);

            // Filtro per soft-delete se non viene specificato questo filtro nel where della grid
            if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
                //Se nel where c'è già un filtro specifico sul campo impostato come soft-delete, ignoro. Vuol dire che sto gestendo io il campo delete (es.: per mostrare un archivio o un history...)
                //Essendo $where un array di condizioni, senza perdere tempo a ciclare, lo implodo così analizzo la stringa (che poi di fatto è quello che fa dopo implodendo su " AND "
                if (stripos(implode(' ', $where), $entityCustomActions['soft_delete_flag']) === false) {
                    $where[] = "({$entityCustomActions['soft_delete_flag']} =  '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)";
                }
            }

            $out = $this->crmentity->get_data_full_list($entity, null, $where, null, 0, null, true, 2, [], ['group_by' => $group_by]);
            if ($this->isCacheEnabled()) {
                $this->mycache->save($cache_key, $out, $this->mycache->CACHE_TIME, $this->mycache->buildTagsFromEntity($entity));
            }
        }

        return $out;
    }
    public function clearCache($drop_template_files = false, $key = null)
    {
        return $this->mycache->clearCache($drop_template_files, $key);
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
        //debug('foo');
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
     * @param array $data           Data to be processed
     * @param int|null $id          Eventuale id con
     * @param bool $exec_preprocess
     * @return array
     */
    public function prepareData($entity, array $data, $id = null, $exec_preprocess = false)
    {

        // Salvo il flag di esecuzione post process e abilito/disabilito il
        // processing dei dati in input in base alle preferenze utente
        $is_executing_processing = $this->isEnabledProcessing();
        $this->enableProcessing($exec_preprocess);

        // Elaboro i dati in modo da ottenere i valori esatti da inserire su
        // database. Il metodo mi cambia i dati per riferimento
        $this->processData($entity, $data, (bool) $id, $id);

        // Ripristino il vecchio valore di enable processing
        $this->enableProcessing($is_executing_processing);

        // Ritorno i dati
        return $data;
    }

    /**
     * Torna un booleano che indica se i dati per l'entità sono validi
     */
    private function processData($entity, array &$data, $editMode = false, $value_id = null)
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

        $fields = $this->db->join('entity', 'entity_id=fields_entity_id', 'left')

            ->get_where('fields', ['entity_name' => $entity_data['entity_name']])
            ->result_array();
        //Xss clean
        $data = $this->security->xss_clean($data, $fields);

        $originalData = $data;

        if ($editMode) {
            if (is_array($value_id)) {
                // Value id deve contenere il mio record per intero. Estraggo
                // quindi l'id
                $dataDb = $value_id;
                $value_id = $dataDb[$entity . '_id'];
            } else {
                // Pre-fetch dei dati sennò fallisce la validazione
                //@todo if db table has columns not present in fields, this will cause an error later... suggestion: use build_select to get only columns present in fields
                $dataDb = $this->db->get_where($entity, array($entity . '_id' => $value_id))->row_array();
            }

            $_POST = $data = array_merge($dataDb, $data);
        } else {
            $value_id = null;
        }

        $fields = $this->crmEntity->getFields($entity_data['entity_id']);

        // Recupera dati di validazione
        foreach ($fields as $k => $field) {
            $fields[$k]['validations'] = $this->crmEntity->getValidations($field['fields_id']);
        }

        /**
         * Validation
         */
        $rules = [];
        $rules_date_before = [];
        foreach ($fields as $field) {
            $rule = [];

            //in fase di creazione devo validarli tutti (quelli required, non quelli soft-required se non passati ovviamente)
            if (in_array($field['fields_name'], array_keys($originalData)) || !$editMode) { //Valido solo i nuovi campi passati dal form, non quelli vecchi già salvati
                // Enter the required rule for the fields that require it
                // (a password is required only if creating the record for the
                // first time)
                if (
                    ($field['fields_required'] == FIELD_REQUIRED && ($field['fields_default'] === '' || $field['fields_default'] === null) ||
                        //If field is soft-required and passed in $originalData
                        $field['fields_required'] == FIELD_SOFT_REQUIRED && array_key_exists($field['fields_name'], $originalData)
                    )
                ) {
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
                            if (!$editMode || $dataDb[$field['fields_name']] != $data[$field['fields_name']]) {
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
                                'message' => $validation['fields_validation_message'] ?: null,
                            ];
                            break;

                        case 'date_after':
                            $rules_date_before[] = [
                                'before' => $validation['fields_validation_extra'],
                                'after' => $field['fields_name'],
                                'message' => $validation['fields_validation_message'] ?: null,
                            ];
                            break;
                    }
                }
            }
            if (!empty($rule)) {
                $rules[] = array('field' => $field['fields_name'], 'label' => $field['fields_draw_label'], 'rules' => implode('|', $rule));
            }
        }

        /**
         * Eseguo il process di pre-validation
         */

        $_predata = $data;
        $mode = $editMode ? 'update' : 'insert';
        $processed_predata_1 = $this->runDataProcessing($entity_data['entity_id'], "pre-validation-{$mode}", ['post' => $_predata, 'value_id' => $value_id, 'original_post' => $this->originalPost]); // Pre-validation specifico
        $processed_predata_2 = $this->runDataProcessing($entity_data['entity_id'], 'pre-validation', $processed_predata_1); // Pre-validation generico
        if (isset($processed_predata_2['post'])) {
            // Metto i dati processati nel post
            $_POST = $data = $processed_predata_2['post'];
        }

        if (!empty($rules)) {
            // Non uso $this->form_validation (del controller) se già
            // inizializzato, ma ne creo uno nuovo all'occorrenza
            if (!class_exists('CI_Form_validation') && !class_exists('MY_Form_validation')) {
                $this->load->library('form_validation');
            } else {
                $validatorClass = (class_exists('MY_Form_validation') ? 'MY_Form_validation' : 'CI_Form_validation');

                $this->form_validation = new $validatorClass;
            }
            // Col passaggio a CI3, non viene più verificato il $_POST dal form validator, ma direttamente il CI->input->method (che nel caso di "change_value" è GET, non POST).
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
         * unirlo all'array $data, altrimenti se è avvenuto un qualche errore di
         * upload, allora questo sarà === false (nota che gli errori sono già
         * stati notificati dalla funzione uploadAll, quindi mi basta fare un
         * return false;
         */
        $result = $this->uploadAll(true);
        if ($result === false) {
            return false;
        } elseif ($result && is_array($result)) {
            $data = array_merge($data, $result);
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
            $value = isset($data[$name]) ? $data[$name] : null;
            $multilingual = $field['fields_multilingual'] == DB_BOOL_TRUE;

            $isRelation = $this->checkRelationsOnField($field, $value);
            if ($isRelation) {
                // Se il campo è una relazione allora dentro al $value ho il mio
                // relation bundle. Me lo salvo e proseguo con il processing
                $relationBundles[] = $value;
                unset($data[$name]);
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
            $isRequired = $field['fields_required'] === FIELD_REQUIRED;

            //Quersta è la vecchia condizione di Alberto. Secondo me è corretto che se non è obbligatorio e uno lo lascia vuoto, venga settato a null comunque...
            if (
                ($isNull && ($hasDefault or ($editMode && $isRequired)))
            ) {
                unset($data[$name]);
            } elseif ($editMode && $value === '' && !$isRequired) {
                $data[$name] = null;
            } else {
                $data[$name] = $value;
            }

            //if (!$editMode && empty($data[$name]) && $this->db->dbdriver == 'postgre' && in_array(strtolower($field['fields_type']), [DB_INTEGER_IDENTIFIER, 'integer', 'int4', 'int'])) {
//            if (!$editMode && empty($data[$name]) && (array_key_exists($name, $data) && $data[$name] !== '0') && in_array(strtolower($field['fields_type']), [DB_INTEGER_IDENTIFIER, 'integer', 'int4', 'int', 'double', 'float'])) {

            // Michael / Matteo - 25/07/2023 - Aggiunti float e double in quanto non è giusto che se non è required venga comunque forzato a 0.
            // inoltre è anche sbagliato che tutto questo non venga eseguito (anche per gli int) in fase di edit del record.
            // perciò ho duplicato la riga togliendo il !$editMode e commentata quella vecchia versione

            // if (in_array(strtolower($field['fields_type']), [DB_INTEGER_IDENTIFIER, 'integer', 'int4', 'int'])) {
            //     $is_zero = $data[$name] !== '0' && $data[$name] !== '0';
            // } else {
            //     $is_zero = $data[$name] !== '0.00' && $data[$name] !== '0.00';
            // }

            //if (empty($data[$name]) && (array_key_exists($name, $data) && $is_zero) && in_array(strtolower($field['fields_type']), [DB_INTEGER_IDENTIFIER, 'integer', 'int4', 'int', 'double', 'float'])) {
            //20230803 - MP - Rimesso tutto come prima... va risolto con molta calma e molti test prima di risolvere il problema dei form che salvano 0 invece di null...
            if (!$editMode && empty($data[$name]) && (array_key_exists($name, $data) && $data[$name] !== '0') && in_array(strtolower($field['fields_type']), [DB_INTEGER_IDENTIFIER, 'integer', 'int4', 'int'])) {

                if (array_key_exists($name, $data)) {
                    //Avoid numbers to be 0 in case of empty. Forced to null
                    $data[$name] = null;
                } else {
                    //Unset to force database to set the default value
                    unset($data[$name]);
                }
            }
        }

        // Check custom validation rules
        foreach ($rules_date_before as $rule) {
            $before = $rule['before'];
            $after = $rule['after'];
            $message = $rule['message'];

            if (isset($data[$before]) && isset($data[$after]) && strtotime($data[$after]) <= strtotime($data[$before])) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $message ?: t("Start date must be antecedent as of end date");
                return false;
            }
        }

        /**
         * Run pre-action process
         */
        $processed_data_1 = $this->runDataProcessing($entity_data['entity_id'], "pre-{$mode}", ['post' => $data, 'value_id' => $value_id, 'original_post' => $this->originalPost]); // Pre-process specifico
        $processed_data_2 = $this->runDataProcessing($entity_data['entity_id'], 'pre-save', $processed_data_1); // Pre-process generico
        if (isset($processed_data_2['post'])) {
            // Put data to be inserted into database
            $data = $processed_data_2['post'];
        }

        // Unset entity id for security issue:
        if ($this->processMode !== self::MODE_DIRECT) {
            unset($data[$entity . '_id']);
        }

        // Set creation date and/or edit date
        if (isset($entityCustomActions['create_time']) && !$editMode && empty($data[$entityCustomActions['create_time']])) {
            $data[$entityCustomActions['create_time']] = date('Y-m-d H:i:s');
        }
        if (isset($entityCustomActions['update_time']) && empty($originalData[$entityCustomActions['update_time']])) {
            $data[$entityCustomActions['update_time']] = $editMode ? date('Y-m-d H:i:s') : null;
        }

        if (isset($entityCustomActions['created_by']) && !$editMode && empty($data[$entityCustomActions['created_by']])) {
            $data[$entityCustomActions['created_by']] = (!empty($this->auth->get('id'))) ? $this->auth->get('id') : null;
        }
        if (isset($entityCustomActions['edited_by']) && $editMode && empty($data[$entityCustomActions['edited_by']])) {
            $data[$entityCustomActions['edited_by']] = (!empty($this->auth->get('id'))) ? $this->auth->get('id') : null;
        }
        $fields_names = array_key_map($fields, 'fields_name');
        //Check scope
        if (!$editMode && in_array($entity . '_insert_scope', $fields_names)) {
            $data[$entity . '_insert_scope'] = self::MODE_MAPPING[$this->processMode];
        } elseif ($editMode && in_array($entity . '_edit_scope', $fields_names)) {
            $data[$entity . '_edit_scope'] = self::MODE_MAPPING[$this->processMode];
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
        $invalidFields = array_diff(array_keys($data), array_key_map($fields, 'fields_name'));
        if (($key = array_search($entity . '_id', $invalidFields)) !== false) {
            unset($invalidFields[$key]);
        }
        if ($invalidFields) {
            if ($this->processMode !== self::MODE_CRM_FORM) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = t("Fields %s are not accepted in entity '$entity'", 0, [implode(', ', $invalidFields)]);
                return false;
            }

            $data = array_diff_key($data, array_flip($invalidFields));
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
                'value' => $dataToInsert,
            );

            return true;
        } elseif ($dataToInsert && in_array(strtoupper($field['fields_type']), ['VARCHAR', 'TEXT'])) {
            $dataToInsert = implode(',', $dataToInsert);
        }

        return false;
    }
    private function updateMultiuploadsRef($entity_name, $record_id, $data)
    {
        $entity = $this->datab->get_entity($entity_name);
        $fields = $entity['fields'];
        foreach ($fields as $field) {
            //debug($field);
            if (in_array($field['fields_draw_html_type'], ['multi_upload'])) {
                if ($field['fields_ref'] && !empty($data[$field['fields_name']]) && $data[$field['fields_name']]) {
                    $relations = $this->db->where('relations_name', $field['fields_ref'])->get('relations');

                    if ($relations->num_rows() == 0) { //Allora il campo non punta a una relazione ma a una tabella diretta
                        //Cerco allora la tabella
                        $file_table = $field['fields_ref'];
                        $entity_data = $this->datab->get_entity_by_name($file_table);
                        //Cerco il campo file e lo uso per inserire
                        $field_related = false;
                        foreach ($entity_data['fields'] as $_field) {
                            if ($_field['fields_ref'] == $entity_name) {
                                $field_related = $_field;
                            }
                        }
                        if (!$field_related) {
                            //debug($file_table);
                            echo json_encode(['status' => 0, 'txt' => "Entity '$file_table' don't have any field related to '$entity_name')!"]);
                            exit;
                        }
                        $files_ids = json_decode($data[$field['fields_name']], true);
                        //debug($files_ids, true);
                        foreach ($files_ids as $file_id) {
                            $this->db
                                ->where($file_table . '_id', $file_id)
                                ->update($file_table, [
                                    $field_related['fields_name'] => $record_id,
                                ]);
                        }
                    } else {
                        //Already managed by savePendingRelations method
                    }
                }
            }
        }
        //debug($entity, true);
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
            //20210427 - MP - fix get old data and delete only records that are not in the new multiselect values... insert new record and leave intact old records...
            $old_data = $this->db->where($relationBundle['relations_field_1'], $savedId)->get($relationBundle['entity'])->result_array();

            foreach ($old_data as $old) {
                if (!in_array($old[$relationBundle['relations_field_2']], $relationBundle['value'])) {
                    //Delete from db if related field2 has been removed from multiselect
                    $this->db->delete($relationBundle['entity'], [
                        $relationBundle['relations_field_1'] => $savedId,
                        $relationBundle['relations_field_2'] => $old[$relationBundle['relations_field_2']],
                    ]);
                } else {
                    //Delete from $relationBundle['value'] so that after it will be not inserted twice
                    unset($relationBundle['value'][array_search($old[$relationBundle['relations_field_2']], $relationBundle['value'])]);
                }
            }

            // debug($old_data);
            // debug($relationBundle['value'], true);

            //$this->db->delete($relationBundle['entity'], [$relationBundle['relations_field_1'] => $savedId]);
            if (is_array($relationBundle['value']) && $relationBundle['value']) {

                // Se $relation['value'] è vuoto allora anche
                // $relationFullData sarà vuoto
                $relationFullData = array_map(function ($value) use ($relationBundle, $savedId) {
                    //@todo Non è detto che il field_1 sia il saveId, nulla vieta di invertire le due tabella. Sarebbe da fare un check per capire dove va l'id salvato e dove va invece ilvalore della multiselect...
                    return [
                        $relationBundle['relations_field_1'] => $savedId,
                        $relationBundle['relations_field_2'] => $value,
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
                    $this->errorMessage = "{$field['fields_draw_label']} is not a valid date";
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

            case 'multiple_values':
                if (!empty($value)) {
                    if (is_array($value)) {
                        foreach ($value as $key => $val) {
                            if (empty($val)) {
                                unset($value[$key]);
                            }
                        }
                        if (count($value) > 0) {
                            $value = json_encode($value);
                        } else {
                            $value = "";
                        }
                    }
                }
                break;
            case 'multiple_key_values':
                if (!empty($value)) {
                    if (is_array($value)) {
                        // Delete empty rows
                        foreach ($value as $key => $val) {
                            if (empty($val['key']) && empty($val['value'])) {
                                unset($value[$key]);
                            }
                        }
                        if (count($value) > 0) {
                            $value = json_encode($value);
                        } else {
                            $value = "";
                        }
                    } else {
                        $this->error = self::ERR_VALIDATION_FAILED;
                        $this->errorMessage = "{$field['fields_draw_label']} must be an array";
                        return false;
                    }
                }
                break;

            case 'todo':
                if (!empty($value)) {
                    if (is_array($value)) {
                        // Delete empty rows
                        foreach ($value as $key => $val) {
                            if (empty($val['checked'])) {
                                $value[$key]['checked'] = DB_BOOL_FALSE;
                            }

                            if (empty($val['value'])) {
                                unset($value[$key]);
                            } else {
                                $value[$key]['value'] = trim($value[$key]['value']);
                            }
                        }
                        if (count($value) > 0) {
                            $value = json_encode($value);
                        } else {
                            $value = "";
                        }
                    } else {
                        $this->error = self::ERR_VALIDATION_FAILED;
                        $this->errorMessage = "{$field['fields_draw_label']} must be an array";
                        return false;
                    }
                }
                break;
        }

        switch ($typeSQL) {
            case 'INT':
                if (!is_numeric($value) && ((int) $value == $value)) {
                    if ($value == '' && ($field['fields_required'] !== FIELD_REQUIRED or $field['fields_default'] !== '')) {
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
                break;

            case 'FLOAT':
            case 'DOUBLE':
                if ($field['fields_draw_html_type'] == 'input_money') {
                    if (substr($value, -3, -2) === '.') {
                        $value = substr_replace($value, ',', -3, -2);
                    }

                    $value = str_replace('.', '', $value);

                }

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
            // no break
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
        if (isset($value['multipolygon'])) {
            return $value['multipolygon'];
        }
        //FORMATO: ST_GeographyFromText('MULTIPOLYGON(((lon lat, lon lat), (.......)))') AS geography
        if (!is_array($value) || $value == array()) {
            return null;
        } else {
            $collections = array();

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
                        $polygon_expl = array_unique($polygon_expl); // Array unique fa anche un sort, ma non tocca le chiavi, quindi basta fare un ksort del risultato e ci sono
                        ksort($polygon_expl);
                        $polygon_expl[] = $last;
                        $polygon_expl = array_values($polygon_expl); // Normalizzo le chiavi
    
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

            $merged_polygons = array_merge($value['circles'], $value['polygons']);

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
                }

                if (!$query) {
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = "Il poligono disegnato non è valido";
                    return false;
                }

                $this->db->db_debug = $debug;
                return $query->row()->geography;
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

        if (empty($data['entity']) && is_array($data)) {
            $data['entity'] = $entity['entity_name'] ?? $entity_id;
        }

        $this->preLoadDataProcessors();

        $dataProcessToRun = array_merge(
            !empty($this->_loadedDataProcessors[$this->processMode][$entity_id][$pptype]) ? $this->_loadedDataProcessors[$this->processMode][$entity_id][$pptype] : [],
            !empty($this->_loadedDataProcessors[$this->processMode]['-1'][$pptype]) ? $this->_loadedDataProcessors[$this->processMode]['-1'][$pptype] : [],
        );

        if (!empty($dataProcessToRun)) {
            foreach ($dataProcessToRun as $function) {
                if (!in_array($pptype, array_merge($this->not_deferrable_pp, $this->deferrable_pp))) {
                    debug($pptype);
                    debug($function);
                    debug($data, true);
                }

                if (!is_maintenance() && in_array($pptype, $this->deferrable_pp) && $function['post_process_background'] == DB_BOOL_TRUE) {
                    /*
                    '_queue_pp_date' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'DEFAULT_STRING' => false],
                    '_queue_pp_execution_date' => ['type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP', 'DEFAULT_STRING' => false],
                    '_queue_pp_code' => ['type' => 'TEXT'],
                    '_queue_pp_executed' => ['type' => 'BOOLEAN', 'default' => false],
                    '_queue_pp_data' => ['type' => 'TEXT'],
                    */
                    $this->db->insert('_queue_pp', [
                        '_queue_pp_date' => date('Y-m-d H:i:s'),
                        '_queue_pp_code' => $function['post_process_what'],
                        '_queue_pp_executed' => DB_BOOL_FALSE,
                        '_queue_pp_data' => json_encode([
                            'data' => $data,
                            '_session' => $_SESSION,
                            '_query_string' => $_SERVER['QUERY_STRING'],
                            '_post' => $this->input->post(),
                            'original_post' => $this->originalPost
                        ]),
                        '_queue_pp_event_data' => json_encode($function)
                    ]);
                } else {
                    try {
                        if (empty($function['fi_events_post_process_id'])) {
                            $this->runEvent($function, $data);
                        } else {
                            //TODO: deprecated... use onlu fi_events table
                            eval($function['post_process_what']);
                        }
                    } catch (Exception $ex) {
                        throw new ApiException($ex->getMessage(), self::ERR_POST_PROCESS, $ex);
                    }
                }


            }
        }
        if (!empty($data['entity']) && is_array($data)) {
            unset($data['entity']);
        }
        return $data;
    }
    private function runEvent($function, $data)
    {
        switch ($function['fi_events_action']) {
            case 'notify':
                $this->load->model('fi_events/notify');
                $this->notify->init($function, $data)->run();
                break;
            default:
                debug("Event action '{$function['fi_events_action']}' not recognized!");
                break;
        }
    }
    private function preLoadDataProcessors()
    {
        if (empty($this->_loadedDataProcessors)) {
            $process = $this->db
                ->where('fi_events_type', 'database')
                ->join('post_process', 'post_process_id = fi_events_post_process_id', 'LEFT')
                ->get('fi_events')
                ->result_array();

            $this->_loadedDataProcessors = [
                self::MODE_DIRECT => [],
                self::MODE_API_CALL => [],
                self::MODE_CRM_FORM => [],
            ];

            foreach ($process as $function) {
                if (empty($function['fi_events_post_process_id'])) { //New fi_events structure

                    $e_id = $function['fi_events_ref_id'];
                    $type = $function['fi_events_when'];
                    $crm = $function['fi_events_crm'];
                    $api = $function['fi_events_api'];
                    $apilib = $function['fi_events_apilib'];
                } else {
                    $e_id = $function['post_process_entity_id'];
                    $type = $function['post_process_when'];
                    $crm = $function['post_process_crm'];
                    $api = $function['post_process_api'];
                    $apilib = $function['post_process_apilib'];
                }

                if (empty($e_id)) {
                    $e_id = '-1';
                }

                if ($crm == DB_BOOL_TRUE) {
                    $this->_loadedDataProcessors[self::MODE_CRM_FORM][$e_id][$type][] = $function;
                }

                if ($api == DB_BOOL_TRUE) {
                    $this->_loadedDataProcessors[self::MODE_API_CALL][$e_id][$type][] = $function;
                }

                if ($apilib == DB_BOOL_TRUE) {
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
            'encrypt_name' => true,
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
                        'name' => $fileData['name'][$key],
                        'type' => $fileData['type'][$key],
                        'tmp_name' => $fileData['tmp_name'][$key],
                        'error' => $fileData['error'][$key],
                        'size' => $fileData['size'][$key],
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

    public function logActivity($type, array $extra = [])
    {

        // Il tipo è valido?
        if (!isset($this->logTitlePatterns[$type])) {
            throw new UnexpectedValueException(t("Type '%s' not valid", 0, [$type]));
        }

        // Siamo ok, possiamo inserire
        $this->addLogActivity($type, true, $extra);
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

            'log_crm_time' => date('Y-m-d H:i:s'),
            // Server time
            'log_crm_type' => (int) $type,
            'log_crm_system' => $system ? DB_BOOL_TRUE : DB_BOOL_FALSE,
            'log_crm_extra' => $extra,
        ];

        // Calcolo il titolo rimpiazzando i dati dal record principale
        $logEntry['log_crm_title'] = str_replace_placeholders($title, $logEntry);
        $logEntry['log_crm_extra'] = $logEntry['log_crm_extra'] ? json_encode($logEntry['log_crm_extra']) : null;

        $this->db->insert('log_crm', $logEntry);
    }

    private function addLogActivity($type, $system, array $extra)
    {
        if (!is_numeric($type)) {
            throw new InvalidArgumentException('Cannot log the activity: type must be numeric');
        }

        if (isset(get_instance()->auth) && defined('LOGIN_NAME_FIELD')) {
            $uid = $this->auth->get('id');
            $uname = trim(implode(' ', [$this->auth->get(LOGIN_NAME_FIELD), $this->auth->get(LOGIN_SURNAME_FIELD)])) ?: null;
        } else {
            $uid = $uname = null;
        }
        extract($extra);

        if ($type == self::LOG_CREATE) {
            $description = $this->fi_activity->getDescriptionCreate($uname);
        } elseif ($type == self::LOG_EDIT) {
            $decode = json_decode($json_data, true);
            $new = $decode['new'];
            $old = $decode['old'];
            $diff = $decode['diff'];

            $description = $this->fi_activity->getDescriptionEditFull($uname, $decode, $extra);
        }

        // Preparo array base
        $logactivity = [
            'fi_activities_user_id' => $uid,
            'fi_activities_user_name' => $uname,
            'fi_activities_entity_id' => $entity_data['entity_id'],
            'fi_activities_entity_name' => $entity_data['entity_name'],
            'fi_activities_data_id' => $data_id,
            'fi_activities_description' => $description,
            'fi_activities_ip_addr' => filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?: 'N/A',
            'fi_activities_user_agent' => filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') ?: null,
            'fi_activities_referer' => filter_input(INPUT_SERVER, 'HTTP_REFERER') ?: null,

            'fi_activities_date' => date('Y-m-d H:i:s'),
            // Server time
            'fi_activities_type' => (int) $type,

            //'fi_activities_json_data' => $json_data,
        ];

        $this->db->insert('fi_activities', $logactivity);
    }

    /**
     * CrmEntity Factory
     *
     * @param string $entity
     * @return \Crmentity
     */
    private function getCrmEntity($entity = null)
    {
        // return new Crmentity($entity, [
        //     $this->currentLanguage,
        //     $this->fallbackLanguage,
        // ]);
        return $this->crmEntity;
    }

}

class ApiException extends Exception
{
}