<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once __DIR__ . '/../helpers/general_helper.php';

class Apilib {
    
    const MODE_DIRECT = 1;
    const MODE_API_CALL = 2;
    const MODE_CRM_FORM = 3;
    
    const CACHE_TIME = 30;
    
    const ERR_INVALID_API_CALL  = 1;
    const ERR_NO_DATA_SENT      = 2;
    const ERR_REDIRECT_FAILED   = 3;
    const ERR_ENTITY_NOT_EXISTS = 4;
    const ERR_VALIDATION_FAILED = 5;
    const ERR_UPLOAD_FAILED     = 6;
    const ERR_INTERNAL_DB       = 7;
    const ERR_GENERIC           = 8;
    
    
    
    private $error = 0;
    private $errorMessage = '';
    private $errorMessages = [
        self::ERR_INVALID_API_CALL => 'Chiamata alle API non valida',
        self::ERR_NO_DATA_SENT => 'Nessun dato passato in input',
        self::ERR_REDIRECT_FAILED => 'Per effetturare il redirect alla pagina desiderata è necessario passare $_GET[url] - [dati salvati correttamente]',
        self::ERR_ENTITY_NOT_EXISTS => 'Entità specificata non esistente',
        self::ERR_VALIDATION_FAILED => 'Validazione fallita',
        self::ERR_UPLOAD_FAILED => 'Upload fallito',
        self::ERR_INTERNAL_DB => 'Si è verificato un errore nel server',
        self::ERR_GENERIC => 'Si è verificato un errore generico',
    ];
    
    private $originalPost = null;
    private $previousDebug;
    private $_loadedDataProcessors = [];
    private $processMode;
    
    
    private $currentLanguage = null;
    private $fallbackLanguage = null;




    public function __construct() {
        $this->load->model('crmentity');
        $this->load->driver('cache', $this->getCacheAdapter());
        $this->previousDebug = $this->db->db_debug;
        $this->processMode = self::MODE_DIRECT;
    }
    
    
    
    /**
     * Fallback per accedere alle proprietà di CI
     * @param mixed $name
     * @return mixed
     */
    public function __get($name) {
        $CI =& get_instance();
        return $CI->{$name};
    }
    
    
    /**
     * Attiva/disattiva le info di debug
     * @param bool $show
     */
    public function setDebug($show = true) {
        $this->db->db_debug = (bool) $show;
    }
    
    
    /**
     * Ripristina il vecchio valore del debug
     * database
     */
    public function restoreDebug() {
        $this->setDebug($this->previousDebug);
    }
    
    
    public function setProcessingMode($mode) {
        
        if (!in_array($mode, array(self::MODE_API_CALL, self::MODE_DIRECT, self::MODE_CRM_FORM))) {
            die('Modalità post-process non valida. Chiamare setProcessingMode con parametri Apilib::MODE_API_CALL, Apilib::MODE_DIRECT o Apilib::MODE_CRM_FORM');
        }
        
        $this->processMode = $mode;
    }
    
    
    public function setLanguage($langId = null, $fallbackLangId = null) {
        $this->currentLanguage = ((int) $langId)?:null;
        $this->fallbackLanguage = ((int) $fallbackLangId)?:null;
    }
    
    /**
     * Traduci un valore in json con le impostazioni lingua correnti
     * 
     * @param type $jsonEncodedValue
     * @return mixed
     */
    public function translate($jsonEncodedValue) {
        
        if (!$this->currentLanguage && !$this->fallbackLanguage) {
            return false;
        }
        
        return $this->getCrmEntity()->translateValue($jsonEncodedValue);
    }
    
    


    /**
     * @todo
     */
    public function get_token($user, $password) { return uniqid(); }
    
    
    
    public function getCacheAdapter() {
        $filename = APPPATH . 'cache/cache-controller';
        $defaultAdapter = array('adapter' => 'file', 'backup' => 'dummy');
        if (!file_exists($filename)) {
            file_put_contents($filename, serialize($defaultAdapter), LOCK_EX);
            return $defaultAdapter;
        }
        
        $controllerFileContents = file_get_contents($filename);
        $adapter = @unserialize($controllerFileContents);
        
        if (!is_array($adapter) OR !array_key_exists('adapter', $adapter)) {
            return $defaultAdapter;
        }
        
        return $adapter;
    }
    
    /**
     * Enable/Disable for the caching system
     * @param bool $enable
     * @return bool Booleano indicante successo/fallimento dell'operazione
     */
    public function toggleCachingSystem($enable = true) {
        $adapter = $enable? ['adapter' => 'file', 'backup' => 'dummy']: ['adapter' => 'dummy'];
        $out = file_put_contents(APPPATH . 'cache/cache-controller', serialize($adapter), LOCK_EX);
        return $out !== false;
    }
    
    
    /**
     * Check cache abilitata o meno
     * @return type
     */
    public function isCacheEnabled() {
        $adapter = $this->getCacheAdapter();
        return ($adapter['adapter'] !== 'dummy');
    }
    
    
    public function clearCache($testMode = false) {
        $files = glob(APPPATH . 'cache/*');
        $keep = array('cache-controller', 'index.html');
        $cleared = [];
        foreach ($files as $file) {
            if (is_file($file) && !in_array(($name=basename($file)), $keep)) {
                if (!$testMode) {
                    unlink($file);
                }
                $cleared[] = $name;
            }
        }
        
        return $cleared;
    }




    /***********************************
     * Rest actions
     */
    
    
    /**
     * Mostra una lista di record dell'entità richiesta
     * @param string $entity    Il nome dell'entità
     */
    public function index($entity=null, $depth = 2) {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        $cache_key = "api.list.{$entity}";
        if( ! ($out=$this->cache->get($cache_key))) {
            $out = $this->getCrmEntity($entity)->get_data_full_list(null, null, [], NULL, 0, NULL, FALSE, $depth);
            $this->cache->save($cache_key, $out, self::CACHE_TIME);
        }

        return $this->sanitizeList($out);
    }
    
    
    /**
     * Ritorna tutti i dati di una determinata entità
     * @param string $entity
     * @param int $id
     * @param int $maxDepthLevel
     */
    public function view($entity=null, $id=null, $maxDepthLevel = 2) {
        
        if(!$entity || !$id || !is_numeric($id)) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        $cache_key = "api.item.{$entity}.{$id}";
        if( ! ($out=$this->cache->get($cache_key))) {
            $out = $this->getCrmEntity($entity)->get_data_full($id, $maxDepthLevel);
            $this->cache->save($cache_key, $out, self::CACHE_TIME);
        }

        return $this->sanitizeRecord($out);
    }
    
    /**
     * Esegue una query pulita su database prendendo l'entità per id
     * 
     * @param string $entity
     * @param int $id
     */
    public function getById($entity, $id) {
        return $this->db->get_where($entity, [$entity.'_id' => $id])->row_array();
    }
    
    
    /**
     * Crea un nuovo record con il post passato e lo ritorna via json
     * @param string $entity
     */
    public function create($entity=null, $data=null) {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        $this->originalPost = $this->input->post();
        if(empty($data) OR !is_array($data)) {
            // Non ho passato dati, quindi prendo il post normalmente
            $data = $this->originalPost;
        } else {
            // Per ragioni di validazione sovrascrivo il post in quanto il validator
            // di CI 2.x va a cercare sempre nell'array post
            $_POST = $data;
        }
        
        
        if(empty($data) && empty($_FILES)) {
            $this->showError(self::ERR_NO_DATA_SENT);
        }
        
        if(!is_array($data)) {
            $data = (array) $data;
        }
        
        
        if($this->processData($entity, $data, false)) {
            $insert = $this->db->insert($entity, $data);
            if (!$insert) {
                $this->showError(self::ERR_GENERIC);
            }
            $id = $this->db->insert_id();

            $dati = $this->getById($entity, $id);
            $this->runDataProcessing($entity, 'insert', $dati);
            
            $this->cache->clean();
            
            
            // Prima di uscire voglio ripristinare il post precedentemente modificato
            $_POST = $this->originalPost;
            return $this->view($entity, $id);
        } else {
            $_POST = $this->originalPost;
            $this->showError();
        }
    }
    
    
    public function createMany($entity = null, $data = null) {
        
        // In questo caso $data è un array di righe multiple che verranno
        // inserite e validate singolarmente. Questo è necessario perché ho
        // bisogno degli ultimi record inseriti e dei rispettivi id per poter
        // eseguire regolarmente i pre e post processes.
        // ***
        // L'idea è quella di far partire una transazione in modo tale che se
        // uno fallisce falliscono tutti
        $this->db->trans_start();
        
        $output = [];
        foreach ($data as $row) {
            $output[] = $this->create($entity, $row);
        }
        
        $this->db->trans_complete();
        return $output;
    }
    
    
    
    /**
     * Fa l'update del record con id passato aggiornando i dati da post
     * Ritorna i nuovi dati via json
     * @param string $entity
     */
    public function edit($entity=null, $id=null, $data=null) {
        
        if( ! $entity || !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        
        $this->originalPost = $this->input->post();
        if(empty($data) || !is_array($data)) {
            // Non ho passato dati, quindi prendo il post normalmente
            $data = $this->originalPost;
        } else {
            // Per ragioni di validazione sovrascrivo il post in quanto il validator
            // di CI 2.x va a cercare sempre nell'array post
            $_POST = $data;
        }
        
        
        if(empty($data) && empty($_FILES)) {
            $this->showError(self::ERR_NO_DATA_SENT);
        }

        if(!is_array($data)) {
            $data = (array) $data;
        }


        if($this->processData($entity, $data, true, $id)) {
            $oldData = $this->getById($entity, $id);
            $this->db->update($entity, $data, [$entity.'_id' => $id]);
            $newData = $this->getById($entity, $id);

            $this->runDataProcessing($entity, 'update', [
                'new' => $newData,
                'old' => $oldData,
                'diff' => array_diff_assoc($newData, $oldData),
                'value_id' => $id
            ]);

            $this->cache->clean();
            
            $_POST = $this->originalPost;
            return $this->view($entity, $id);
        } else {
            $_POST = $this->originalPost;
            $this->showError();
        }
    }
    
    
    /**
     * Cancella il record selezionate
     * @param string $entity
     */
    public function delete($entity=null, $id=null) {
        
        if( ! $entity || !$id) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        $this->db->trans_start();
        $this->runDataProcessing($entity, 'pre-delete', ['id' => $id]);
        $this->db->delete($entity, [$entity.'_id' => $id]);
        $this->runDataProcessing($entity, 'delete', ['id' => $id]);
        $this->db->trans_complete();
        
        $this->cache->clean();
    }
    
    
    /**
     * Ritorna in json i campi di un'entità
     * @param type $entity
     */
    public function describe($entity=null) {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        $entity_data = $this->getEntityByName($entity);
        if(empty($entity_data)) {
            $this->showError(self::ERR_ENTITY_NOT_EXISTS);
        }

        $fields = $this->getFields($entity_data['entity_id']);
        return array_map(function($field) { return $field['fields_name']; }, array_filter($fields, function($field) { return $field['fields_visible'] === 't'; }));
    }
    
    
    public function entityList() {
        
        $entity_type_default = defined('ENTITY_TYPE_DEFAULT')? ENTITY_TYPE_DEFAULT: 1;
        $entities = $this->db->order_by('entity_name')->get_where('entity', ['entity_type' => $entity_type_default])->result_array();

        $tables = [];
        foreach($entities as $entity) {
            $table = [
                'name' => $entity['entity_name'],
                'fields' => []
            ];
            $fields = $this->db->order_by('fields_name')->get_where('fields', ['fields_entity_id' => $entity['entity_id']])->result_array();
            foreach($fields as $field) {
                $table['fields'][] = $field['fields_name'] . ($field['fields_ref']? ' <small>[ref. from: <strong>'.$field['fields_ref'] . '</strong>]</small>': '');
            }
            
            $tables[] = $table;
        }

        return $tables;
        
    }
    
    
    
    public function supportList() {
        
            
        $entity_type_support = defined('ENTITY_TYPE_SUPPORT_TABLE')? ENTITY_TYPE_SUPPORT_TABLE: 2;
        $support_tables = $this->db->order_by('entity_name')->get_where('entity', array('entity_type' => $entity_type_support))->result_array();

        $tables = [];
        foreach($support_tables as $entity) {
            $tables[$entity['entity_name']] = array('fields' => [], 'values' => []);
            $fields = $this->db->order_by('fields_name')->get_where('fields', array('fields_entity_id' => $entity['entity_id']))->result_array();
            foreach($fields as $field) {
                $tables[$entity['entity_name']]['fields'][] = $field['fields_name'];
            }

            $values = $this->db->order_by($entity['entity_name'].'_id')->get($entity['entity_name'])->result_array();
            foreach($values as $k => $value) {
                if($k < 50) {
                    $tables[$entity['entity_name']]['values'][$value[$entity['entity_name'].'_id']] = $value[$entity['entity_name'].'_value'];
                } elseif($k == 50) {
                    $tables[$entity['entity_name']]['values']['...'] = '...';
                    break;
                }
            }
        }

        return $tables;
    }
    
    
    
    public function search($entity=null, $input = [], $limit = null, $offset = 0, $orderBy = null, $orderDir = 'ASC', $maxDepth = 2) {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        if(!is_array($input)) {
            $input = $input? [$input]: [];
        }
        
        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        $cache_key = "api.search.{$entity}.".md5(serialize($input))
                .($limit? ".{$limit}":'').($offset? ".{$offset}": '').($orderBy? ".{$orderBy}.{$orderDir}": '');
        
        
        
        if( ! ($out=$this->cache->get($cache_key))) {
            
            $where = [];
            if(isset($input['where'])) {
                $where[] = $input['where'];
                unset($input['where']);
            }
            
            foreach ($input as $key => $value) {
                if(is_array($value) && is_string($key)) {

                    // La chiave se è stringa indica il nome del campo,
                    // mentre il value se è array (fattibile solo da POST o PROCESS)
                    // fa un WHERE IN
                    $values = "'".implode("','", $value)."'";
                    $where[] = "{$key} IN ({$values})";

                } elseif(is_string($key)) {
                    $where[$key] = $value;
                } else {
                    // Ho una chiave numerica che potrebbe essere stata inserita
                    // da pre-process...
                    $where[] = $value;
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
                        $order_array[] = $field . ' ' . (in_array($direction, ['ASC', 'DESC'])? $direction: 'ASC');
                    } else {
                        // Aggiungo con direzione ASC
                        $order_array[] = $field . ' ASC';
                    }
                }
            }
            
            $order = empty($order_array)? null: implode(', ', $order_array);
            //$order = $orderBy? $orderBy.' '.($orderDir==='ASC'? $orderDir: 'DESC'): null;
            
            try {
                $out = $this->getCrmEntity($entity)->get_data_full_list(null, null, $where, $limit?:null, $offset, $order, false, $maxDepth);
                $this->cache->save($cache_key, $out, self::CACHE_TIME);
            } catch (Exception $ex) {
                throw new ApiException('Si è verificato un errore nel server', self::ERR_INTERNAL_DB, $ex);
            }
        }
        
        return $this->runDataProcessing($entity, 'search', $this->sanitizeList($out));
    }
    
    
    public function searchFirst($entity=null, $input = []) {
        $out = $this->search($entity, $input, 1);
        return array_shift($out)?:[];
    }
    
    
    
    public function count($entity=null, $input = []) {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        if(is_string($input)) {
            $input = array($input);
        }
        
        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        $cache_key = "api.count.{$entity}.".md5(serialize($input));
        
        
        
        if( ! ($out=$this->cache->get($cache_key))) {
            $where = [];
            if(isset($input['where'])) {
                $where[] = $input['where'];
                unset($input['where']);
            }
            
            foreach ($input as $key => $value) {
                if(is_array($value) && is_string($key)) {

                    // La chiave se è stringa indica il nome del campo,
                    // mentre il value se è array (fattibile solo da POST o PROCESS)
                    // fa un WHERE IN
                    $values = "'".implode("','", $value)."'";
                    $where[] = "{$key} IN ({$values})";

                } elseif(is_string($key)) {
                    $where[$key] = $value;
                } else {
                    // Ho una chiave numerica che potrebbe essere stata inserita
                    // da pre-process...
                    $where[] = $value;
                }
            }
            
            $out = $this->getCrmEntity($entity)->get_data_full_list(null, null, $where, null, 0, null, true);
            $this->cache->save($cache_key, $out, self::CACHE_TIME);
        }
        
        return $out;
    }
    
    
    
    /**
     * Torna l'array dei messaggi d'errore,
     * NB: a scopi di debug - help ecc
     */
    public function getApiMessages() {
        return $this->errorMessages;
    }
    
    
    
    
    /**
     * Lancia eccezione con errore passato o quello corrente
     */
    private function showError($errorCode = null) {
        
        if( ! is_null($errorCode) && is_numeric($errorCode)) {
            $this->error = $errorCode;
        }
        
        /***
         * Se non c'è nessun messaggio presettato mettici quello di default per
         * il codice d'errore corrente - sempre se esiste
         */
        if(empty($this->errorMessage) && isset($this->errorMessages[$this->error])) {
            $this->errorMessage = $this->errorMessages[$this->error];
        }
        
        
        if( ! $this->errorMessage) {
            // Condizione anomala
            $this->errorMessage = sprintf('Errore imprevisto (%s)', $this->error);
        }
        
        throw new ApiException($this->errorMessage, $this->error);
    }













    /**
     * Rimuovi tutti i dati relativi all'entità da
     * tutti gli elementi di una lista
     * ---
     * Se la lista ha una chiave data allora vuol dire che i veri
     * record sono li dentro
     */
    private function sanitizeList($data) {
        
        $data = isset($data['data'])? $data['data']: $data;
        
        if(is_array($data)) {
            return array_map(function($item) {
                return is_array($item)? $this->sanitizeRecord($item): $item;
            }, $data);
        } else {
            return null;
        }
    }
    
    
    

    /**
     * Per ogni elemento ci metto 
     * ci metto solo i dati se presenti, eliminando quindi
     * le informazioni sui fields - per farlo faccio un sanitize list
     */
    private function sanitizeRecord($item) {
        if(is_array($item)) {
            return array_map(function($value) {
                return is_array($value)? $this->sanitizeList($value): $value;
            }, $item);
        } else {
            return null;
        }
    }
    
    
    
    
    
    
    /**
     * Torna un booleano che indica se i dati per l'entità sono validi
     */
    private function processData($entity, array &$dati, $editMode=false, $value_id = null) {
        $entity_data = $this->getEntityByName($entity);
        if (empty($entity_data)) {
            $this->error = self::ERR_VALIDATION_FAILED;
            $this->errorMessage = sprintf("Entity non trovata: '%s'", $entity);
            return false;
        }
        
        $originalData = $dati;
        if($editMode) {
            // Pre-fetch dei dati sennò fallisce la validazione
            $dataDb = $this->db->get_where($entity, array($entity.'_id' => $value_id))->row_array();
            $_POST = $dati = array_merge($dataDb, $dati);
        }
        
        $fields = $this->db->join('fields_draw', 'fields_draw_fields_id=fields_id', 'left')->get_where('fields', ['fields_entity_id' => $entity_data['entity_id']])->result_array();
        
        // Recupera dati di validazione
        foreach ($fields as $k => $field) {
            $fields[$k]['validations'] = $this->db->get_where('fields_validation', array('fields_validation_fields_id' => $field['fields_id']))->result_array();
        }

        /**
         * Validazione
         */
        $rules = [];
        $rules_date_before = [];
        foreach ($fields as $field) {
            $rule = [];
            
            // Inserisci la regola required per i campi che la richiedono
            // (una password è required solo se sto creando il record per la
            // prima volta)
            if ($field['fields_required'] === 't' && !$field['fields_default']) {
                
                switch ($field['fields_draw_html_type']) {
                    // Questo perchè l'upload viene giustamente fatto dopo il
                    // controllo delle regole di validazione
                    case 'upload':
                    case 'upload_image':
                        if (!array_key_exists($field['fields_name'], $_FILES)) {
                            $rule[] = 'required';
                        }
                        break;
                        
                    // Una password va valutata come required sse sono in
                    // creazione perché in edit non è detto che con una modifica
                    // voglia cambiarla
                    case 'input_password':
                        if (!$editMode) {
                            $rule[] = 'required';
                        }
                        break;
                       
                    // Di default avanti tutta!! Il campo sarà required
                    default:
                        $rule[] = 'required';
                }
            }
            
            
            // Inserisci le altre regole di validazione
            foreach ($field['validations'] as $validation) {
                switch ($validation['fields_validation_type']) {
                    
                    //Le validazioni che non richiedono parametri particolari
                    case 'valid_email': case 'valid_emails': case 'integer': case 'numeric': case 'is_natural': case 'is_natural_no_zero': case 'alpha': case 'alpha_numeric': case 'alpha_dash':
                        $rule[] = $validation['fields_validation_type'];
                        break;
                    
                    //Le validazioni che hanno parametri semplici
                    case 'is_unique': 
                        // Il caso unique ha una particolarità:
                        // nel caso di edit se uno NON ha cambiato il valore del campo,
                        // allora non includo la regola di unicità, perché fallirebbe sempre
                        // e il form validator di CI non è così intelligente da poter determinare che
                        // il valore inserito fa riferimento all'entità
                        if(!$editMode || $dataDb[$field['fields_name']] != $dati[$field['fields_name']]) {
                            $rule[] = "is_unique[{$validation['fields_validation_extra']}]";
                        }
                        break;
                    
                    case 'decimal': case 'min_length': case 'max_length': case 'exact_length': case 'greater_than': case 'less_than':
                        $rule[] = "{$validation['fields_validation_type']}[{$validation['fields_validation_extra']}]";
                        break;
                    
                    // Validazioni complesse
                    case 'date_before':
                        $rules_date_before[] = [
                            'before' => $field['fields_name'],
                            'after' => $validation['fields_validation_extra'],
                            'message' => $validation['fields_validation_message'] ? : null
                        ];
                        break;
                    
                    case 'date_after':
                        $rules_date_before[] = [
                            'before' => $validation['fields_validation_extra'],
                            'after' => $field['fields_name'],
                            'message' => $validation['fields_validation_message'] ? : null
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
        $processed_predata = $this->runDataProcessing($entity_data['entity_id'], $editMode? 'pre-validation-update': 'pre-validation-insert', ['post' => $_predata, 'value_id' => $value_id]);
        if(isset($processed_predata['post'])) {
            // Metto i dati processati nel post
            $_POST = $dati = $processed_predata['post'];
        }
        
        if (!empty($rules)) {
            
            // ************************ ACCROCCHIO *****************************
            // FIX: non voglio usare il validator di CI direttamente (quello del
            // controller, dato che potrebbe essere sporcato da eventuali altre
            // validazioni precedenti, ma ne creo un'istanza al volo)
            if( !class_exists('CI_Form_validation') && !class_exists('MY_Form_validation')) {
                
                // Classe non ancora caricata, quindi posso usare il validator
                // del controller
                $this->load->library('form_validation');
                
            } else {
                
                // Classe già caricata, quindi ne creo uno nuovo
                $validatorClass = (class_exists('MY_Form_validation')? 'MY_Form_validation': 'CI_Form_validation');
                $this->form_validation = new $validatorClass;
                
            }
            
            
            
            
            
            $this->form_validation->set_rules($rules);
            if (!$this->form_validation->run()) {
                
                /***
                 * Voglio tornare solo il primo errore
                 */
                $this->error = self::ERR_VALIDATION_FAILED;
                foreach($rules as $rule) {
                    // Non appena lo trovo break - almeno uno ci dev'essere
                    $message = $this->form_validation->error($rule['field']);
                    
                    if($message) {
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
        
        
        /**
         * Elabora i dati prima del salvataggio in base a fields_draw_html_type e tipo
         * es: password => md5($data['password'])
         */
                
        foreach ($fields as $field) {
            
            $name = $field['fields_name'];
            $value = isset($dati[$name]) ? $dati[$name]: null;
            $multilingual = $field['fields_multilingual'] == 't';
            
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
                
                if (isset($originalData[$name]) && (is_array($value) OR !is_null($this->currentLanguage))) {
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
                        if (!$this->sanitizeInput($field, $lValue, isset($originalData[$name][$langId])? $originalData[$name][$langId]: null)) {
                            return false;
                        }
                        $toSaveJSON[$langId] = $lValue;
                    }
                }

                // A questo punto rifaccio l'encode sulla variabile $value
                $value = json_encode($toSaveJSON);
                
            // Per i campi non multilingua semplicemente processo il valore
            // normalmente (attenzione $value è per riferimento)
            } elseif(!$this->sanitizeInput($field, $value, isset($originalData[$name])? $originalData[$name]: null)) {
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
            $isRequired = $field['fields_required']==='t';
            
            if ($isNull && ($hasDefault OR ($editMode && $isRequired))) {
                unset($dati[$name]);
            } else {
                $dati[$name] = $value;
            }
        }
        
        // Controllo delle regole custom di validazione
        foreach($rules_date_before as $rule) {
            $before = $rule['before'];
            $after = $rule['after'];
            $message = $rule['message'];
            
            if(isset($dati[$before]) && isset($dati[$after]) && strtotime($dati[$after]) <= strtotime($dati[$before])) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $message?: "La data di inizio dev'essere antecedente alla data di fine";
                return false;
            }
        }
        
        /**
         * Eseguo il process di pre-action
         */
        $processed_data = $this->runDataProcessing($entity_data['entity_id'], $editMode? 'pre-update': 'pre-insert', ['post' => $dati, 'value_id' => $value_id]);
        if(isset($processed_data['post'])) {
            // Metto i dati processati nell'array da inserire su db
            $dati = $processed_data['post'];
        }
        
        // Unsetta l'id entità - per sicurezza, non si sa mai cosa viene mandato tramite api
        unset($dati[$entity.'_id']);
        
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
        if ($invalidFields) {
            if ($this->processMode !== self::MODE_CRM_FORM) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = sprintf("I campi %s non sono accettati", implode(', ', $invalidFields));
                return false;
            }
            
            $dati = array_diff_key($dati, array_flip($invalidFields));
        }
        
        return true;
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
    protected function sanitizeInput(array $field, &$value, $originalValue) {
        
        $typeSQL = strtoupper($field['fields_type']);
        $typeHTML = $field['fields_draw_html_type'];
        
        
        switch ($typeHTML) {
            case 'date':
            case 'date_time':
                if (($value = $this->filterInputDate($value, $typeHTML==='date_time')) === false) {
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = "{$field['fields_draw_label']} non è una data valida";
                    return false;
                }
                break;

            case 'wysiwyg':
                if($value) {
                    $bURL = (function_exists('base_url_template')? base_url_template(): base_url());
                    $value = str_replace($bURL, '{base_url}', $value);
                }
                break;

            case 'input_password':
                // In modifica se nei dati passati nel post non c'è la
                // password, allora non devo calcolare la hash nuovamente
                $value = $originalValue? md5($originalValue): null;
                break;


            case 'map':
                // Lancia l'utility per filtrare input di tipo geography
                $value = $this->filterInputGeo($value);
                break;

            case 'date_range':
                if($typeSQL === 'DATERANGE' && !isValidDateRange($value)) {
                    $value = '['.implode(',', array_map(function($date) { return date_toDbFormat($date); }, explode(' - ', $value))).']';
                }
                break;
        }


        switch ($typeSQL) {
            case 'INT':
                if (!is_numeric($value) && ((int) $value == $value)) {
                    if($value == '' && ($field['fields_required'] === 'f' OR $field['fields_default'])) {  
                        // Se il campo è stato lasciato vuoto e non è richiesto
                        // oppure (se è richiesto), ma ha un default... lo metto
                        // a null in modo che il sistema lo gestisca
                        // automaticamente
                        $value = null;
                    } else {
                        throw new Exception(sprintf("Il campo %s dev'essere un intero", $field['fields_name']));
                    }
                }
                //----
                /*if(isset($value) && !is_numeric($value)) {
                    $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                }

                if(($value === '' OR $value === false) && $field['fields_required'] === 'f' ) {
                    $value = null;
                }*/
                break;

            case 'FLOAT':
                $float = str_replace(',', '.', $value);
                $value = (float) filter_var($float, FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
                break;

            case 'BOOL':
                if (isset($value)) {
                    // Se il valore è t/f ok, altrimenti prendi il valore
                    // booleano
                    $value = in_array($value, ['t', 'f']) ? $value: ($value ? 't': 'f');
                } elseif (!trim($field['fields_default'])) {
                    // Se invece è a null e non ho nessun tipo di default,
                    // allora lo imposto come false automaticamente (vd.
                    // checkbox che se le uso in modalità booleana, il false non
                    // è settato)
                    $value = 'f';
                }
                break;

            case 'TIMESTAMP WITHOUT TIME ZONE':
                $value = $value? : null;
                break;

            case 'DATERANGE':
                if(!isValidDateRange($value)) {
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = "{$field['fields_draw_label']} non è un date-range nel formato corretto";
                    return false;
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
    protected function filterInputDate($value, $withTime) {
        if(!$value) {
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
     */
    protected function filterInputGeo($value) {
        
        if (isset($value['geo'])) {
            return $value['geo'];
        }
        
        $exp = [];
        if (isset($value['lat']) && isset($value['lng'])) {
            $exp = [$value['lat'], $value['lng']];
        } elseif (is_array($value) && count($value) > 1) {
            $exp = array_values($value);
        } elseif (is_string($value)) {
            $nvalue = str_replace(',', ';', $value);
            $exp = (strpos($nvalue, ';') !== false)? explode(';', $nvalue): [];
        }
        
        if (isset($exp[0]) && isset($exp[1])) {
            return $this->db->query("SELECT ST_GeographyFromText('POINT({$exp[1]} {$exp[0]})') AS geography")->row()->geography;
        } else {
            return null;
        }
        
    }
    
    
    
    /**
     * Cloni del model datab del crm
     */
    protected function run_post_process($entity_id, $when, $data = []) {
        $this->runDataProcessing($entity_id, $when, $data);
    }
    
    public function runDataProcessing($entity_id, $when, $data = []) {
        
        if( ! is_numeric($entity_id) && is_string($entity_id)) {
            $entity = $this->getEntityByName($entity_id);
            $entity_id = $entity['entity_id'];
        }

        if (!isset($this->_loadedDataProcessors[$entity_id][$when])) {
            
            switch ($this->processMode) {
                case self::MODE_CRM_FORM:
                    $modeField = 'post_process_crm';
                    break;
                
                case self::MODE_API_CALL:
                    $modeField = 'post_process_api';
                    break;
                
                case self::MODE_DIRECT:
                    $modeField = 'post_process_apilib';
                    break;
                
                default:
                    // Modalità non settata correttamente, non eseguo nessun
                    // process, non ho tempo di pensare ad una soluzione migliore
                    return;
            }
            
            $this->_loadedDataProcessors[$entity_id][$when] = $this->db->get_where('post_process', array(
                'post_process_entity_id' => $entity_id,
                'post_process_when' => $when,
                $modeField => 't',
            ))->result_array();
        }
        
        if (!empty($this->_loadedDataProcessors[$entity_id][$when])) {
            foreach ($this->_loadedDataProcessors[$entity_id][$when] as $function) {
                try {
                    eval($function['post_process_what']);
                } catch (Exception $ex) {
                    throw new ApiException($ex->getMessage(), $ex->getCode()?:self::ERR_GENERIC, $ex);
                }
            }
        }

        return $data;
    }
    
    private function getEntityByName($entity_name) {
        $entity = $this->db->query("SELECT * FROM entity WHERE entity_name = '{$entity_name}'");
        return ($entity instanceof CI_DB_result && $entity->num_rows() > 0)? $entity->row_array(): [];
    }
    
    private function getFields($entity_id) {
        $query = $this->db->query("SELECT * FROM fields WHERE fields_entity_id = '{$entity_id}'");
        return ($query instanceof CI_DB_result && $query->num_rows() > 0)? $query->result_array(): [];
    }
    
    
    /**
     * Upload di tutti i file dentro all'array $_FILES e opzionalmente rimuovili
     * dalla superglobal
     * 
     * @param type $clearFilesSuperglobal
     * @return boolean
     */
    private function uploadAll($clearFilesSuperglobal = false) {
        
        if (!$_FILES) {
            return [];
        }
        
        $output = [];
        $this->load->library('upload', [
            'upload_path' => FCPATH . 'uploads/',
            'allowed_types' => '*',
            'max_size' => 50000,
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
                $output[$fieldName] = $uploadData['file_name'];
                
            }
                

            if ($clearFilesSuperglobal) {
                unset($_FILES[$fieldName]);
            }
        }
        
        return $output;
    }
    
    
    /**
     * CrmEntity Factory
     * 
     * @param string $entity
     * @return \Crmentity
     */
    private function getCrmEntity($entity = null) {
        return new Crmentity($entity, [
            $this->currentLanguage,
            $this->fallbackLanguage
        ]);
    }
    
}




class ApiException extends Exception {}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
