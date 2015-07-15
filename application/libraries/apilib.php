<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

include_once __DIR__ . '/../helpers/general_helper.php';

class Apilib {
    
    const MODE_DIRECT = 1;
    const MODE_API_CALL = 2;
    
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
    private $errorMessages = array(
        self::ERR_INVALID_API_CALL => 'Chiamata alle API non valida',
        self::ERR_NO_DATA_SENT => 'Nessun dato passato in input',
        self::ERR_REDIRECT_FAILED => 'Per effetturare il redirect alla pagina desiderata è necessario passare $_GET[url] - [dati salvati correttamente]',
        self::ERR_ENTITY_NOT_EXISTS => 'Entità specificata non esistente',
        self::ERR_VALIDATION_FAILED => 'Validazione fallita',
        self::ERR_UPLOAD_FAILED => 'Upload fallito',
        self::ERR_INTERNAL_DB => 'Si è verificato un errore nel server',
        self::ERR_GENERIC => 'Si è verificato un errore generico',
    );
    
    private $originalPost = null;
    private $previousDebug;
    private $_loadedDataProcessors = array();
    private $processMode;
    
    
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
        
        if (!in_array($mode, array(self::MODE_API_CALL, self::MODE_DIRECT))) {
            die('Modalità post-process non valida. Chiamare setProcessingMode con parametri Apilib::MODE_API_CALL e Apilib::MODE_DIRECT');
        }
        
        $this->processMode = $mode;
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
        $adapter = $enable? array('adapter' => 'file', 'backup' => 'dummy'): array('adapter' => 'dummy');
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
        $cleared = array();
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
            $crmentity = new Crmentity($entity);
            $out = $crmentity->get_data_full_list(null, null, array(), NULL, 0, NULL, FALSE, $depth);
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
            $crmentity = new Crmentity($entity);
            $out = $crmentity->get_data_full($id, $maxDepthLevel);
            $this->cache->save($cache_key, $out, self::CACHE_TIME);
        }

        return $this->sanitizeRecord($out);
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
                die();
            }
            $id = $this->db->insert_id();

            $dati = $this->db->get_where($entity, array($entity.'_id' => $id))->row_array();
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
        
        $output = array();
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
            $old_data = $this->db->get_where($entity, array($entity.'_id' => $id))->row_array();
            $this->db->update($entity, $data, array($entity.'_id' => $id));
            $new_data = $this->db->get_where($entity, array($entity.'_id' => $id))->row_array();

            $this->runDataProcessing($entity, 'update', array('new' => $new_data, 'old' => $old_data, 'diff' => array_diff_assoc($new_data, $old_data), 'value_id' => $id));

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
        
        $this->runDataProcessing($entity, 'pre-delete', array('id' => $id));
        $this->db->delete($entity, array($entity.'_id' => $id));
        $this->runDataProcessing($entity, 'delete', array('id' => $id));
        
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
        $entities = $this->db->order_by('entity_name')->get_where('entity', array('entity_type' => $entity_type_default))->result_array();

        $tables = array();
        foreach($entities as $entity) {
            $table = array('name' => $entity['entity_name'], 'fields' => array());
            $fields = $this->db->order_by('fields_name')->get_where('fields', array('fields_entity_id' => $entity['entity_id']))->result_array();
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

        $tables = array();
        foreach($support_tables as $entity) {
            $tables[$entity['entity_name']] = array('fields' => array(), 'values' => array());
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
    
    
    
    public function search($entity=null, $input = array(), $limit = null, $offset = 0, $orderBy = null, $orderDir = 'ASC') {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        if(is_string($input)) {
            $input = array($input);
        }
        
        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        $cache_key = "api.search.{$entity}.".md5(serialize($input))
                .($limit? ".{$limit}":'').($offset? ".{$offset}": '').($orderBy? ".{$orderBy}.{$orderDir}": '');
        
        
        
        if( ! ($out=$this->cache->get($cache_key))) {
            $crmentity = new Crmentity($entity);

            $where = array();
            
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
            
            
            $order_array = array();
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
                        $order_array[] = $field . ' ' . (in_array($direction, array('ASC', 'DESC'))? $direction: 'ASC');
                    } else {
                        // Aggiungo con direzione ASC
                        $order_array[] = $field . ' ASC';
                    }
                }
            }
            
            $order = (empty($order_array)? null: implode(', ', $order_array));
            //$order = $orderBy? $orderBy.' '.($orderDir==='ASC'? $orderDir: 'DESC'): null;
            
            
            if(!$limit) {
                $limit = null;
            }
            
            try {
                $out = $crmentity->get_data_full_list(null, null, $where, $limit, $offset, $order);
                $this->cache->save($cache_key, $out, self::CACHE_TIME);
            } catch (Exception $ex) {
                throw new ApiException('Si è verificato un errore nel server', self::ERR_INTERNAL_DB, $ex);
            }
        }
        
        return $this->runDataProcessing($entity, 'search', $this->sanitizeList($out));
    }
    
    
    public function searchFirst($entity=null, $input = array()) {
        $out = $this->search($entity, $input, 1);
        return array_shift($out)?:array();
    }
    
    
    
    public function count($entity=null, $input = array()) {
        
        if( ! $entity) {
            $this->showError(self::ERR_INVALID_API_CALL);
        }
        
        if(is_string($input)) {
            $input = array($input);
        }
        
        $input = $this->runDataProcessing($entity, 'pre-search', $input);
        $cache_key = "api.count.{$entity}.".md5(serialize($input));
        
        
        
        if( ! ($out=$this->cache->get($cache_key))) {
            $crmentity = new Crmentity($entity);

            $where = array();
            
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
            
            
            $out = $crmentity->get_data_full_list(null, null, $where, null, 0, null, true);
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
            $this->errorMessage = 'Errore imprevisto';
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
        
        $fields = $this->db->from('fields')->join('fields_draw', 'fields_draw_fields_id=fields_id', 'left')->where('fields_entity_id', $entity_data['entity_id'])->get()->result_array();
        
        // Recupera dati di validazione
        foreach ($fields as $k => $field) {
            $fields[$k]['validations'] = $this->db->get_where('fields_validation', array('fields_validation_fields_id' => $field['fields_id']))->result_array();
        }

        /**
         * Validazione
         */
        $rules = array();
        $rules_date_before = array();
        foreach ($fields as $field) {
            $rule = array();
            
            // Inserisci la regola required per i campi che la richiedono
            // (una password è required solo se sto creando il record per la
            // prima volta)
            if ($field['fields_required'] === 't' && !$field['fields_default']) {
                
                switch ($field['fields_draw_html_type']) {
                    // Questo perchè l'upload viene giustamente fatto dopo il
                    // controllo delle regole di validazione
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
                        $rules_date_before[] = array('before' => $field['fields_name'], 'after' => $validation['fields_validation_extra'], 'message' => ($validation['fields_validation_message']? $validation['fields_validation_message']: NULL));
                        break;
                    
                    case 'date_after':
                        $rules_date_before[] = array('before' => $validation['fields_validation_extra'], 'after' => $field['fields_name'], 'message' => ($validation['fields_validation_message']? $validation['fields_validation_message']: NULL));
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
        $processed_predata = $this->runDataProcessing($entity_data['entity_id'], $editMode? 'pre-validation-update': 'pre-validation-insert', array('post' => $_predata, 'value_id' => $value_id));
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
        
        /**
         * Upload di eventuali file
         */
        $files_fields = array_keys($_FILES);
        if(!empty($files_fields)) {
            $this->load->library('upload', array(
                'upload_path' => './uploads/',
                'allowed_types' => '*', //non possiamo fare assunzioni sulla natura del file
                'max_size' => 50000,
                'encrypt_name' => TRUE,
            ));
            
            foreach ($files_fields as $field_name) {
                $file_array = $_FILES[$field_name];
                if (empty($file_array) || !$file_array['name']) {continue;}
                if (!$this->upload->do_upload($field_name)) {
                    /* Errore upload */
                    $this->error = self::ERR_UPLOAD_FAILED;
                    $this->errorMessage = $this->upload->display_errors();
                    return false;
                } else {
                    /* Upload ok */
                    $up_data = $this->upload->data();
                    $dati[$field_name] = $up_data['file_name'];
                    
                    // Unset della chiave del $_FILES per prevenire errori di
                    // validazione in eventuali post process successivi
                    unset($_FILES[$field_name]);
                }
            }
        }
        
        /**
         * Elabora i dati prima del salvataggio in base a fields_draw_html_type e tipo
         * es: password => md5($data['password'])
         */
        $post_process_relations = array();
        foreach ($fields as $field) {
            
            // Se non esiste il campo allora vuol dire che non era un required,
            // ma comunque stava nel form. Vuol dire che deve essere inserito vuoto
            if( !array_key_exists($field['fields_name'], $dati)) {
                continue;
                //$dati[$field['fields_name']] = NULL;  Tolto perché dava problemi nell'update
            }
            
            // Colgo l'occasione per vedere se ci sono field che si riferiscono a relazioni - questo è un passaggio che devo fare ora perché i vari controlli sui campi fallirebbero dato che ho un array
            if ($field['fields_ref']) {
                /**
                 * in realtà il field ref dovrebbe puntare alla tabella pivot non alla tabella con cui è relazionata
                 * ad esempio ho aziende <-> tags
                 * il field ref di aziende non dovrebbe puntare a tags, ma ad aziende_tags (il nome della relazione).
                 * ---
                 * Per mantenere la retrocompatibilità vengono cercate entrambe le varianti
                 */
                $dataToInsert = isset($dati[$field['fields_name']]) ? $dati[$field['fields_name']] : array();
                if (is_array($dataToInsert)) {
                    $relations = $this->db->where_in('relations_name', array($entity.'_'.$field['fields_ref'], $field['fields_ref']))->get('relations');
                    if ($relations->num_rows() > 0) {
                        $relation = $relations->row();
                        $post_process_relations[] = array(
                            'entity' => $relation->relations_name,
                            'relations_field_1' => $relation->relations_field_1,
                            'relations_field_2' => $relation->relations_field_2,
                            'value' => $dati[$field['fields_name']]
                        );
                        unset($dati[$field['fields_name']]);
                        continue;
                    } elseif ($dataToInsert && in_array($sql_type, array('VARCHAR', 'TEXT'))) {
                        $dati[$field['fields_name']] = implode(',', $dataToInsert);
                    }
                }
            }
            
            
            
            $sql_type = strtoupper($field['fields_type']);
            $html_type = $field['fields_draw_html_type'];
            
            switch ($html_type) {
                case 'date':
                    if(empty($dati[$field['fields_name']])) {
                        $dati[$field['fields_name']] = null;
                    } else {
                        // Il campo non era vuoto, quindi valido la stringa e se
                        // è vuota allora vuol dire che il formato era sbagliato
                        $dati[$field['fields_name']] = date_toDbFormat($dati[$field['fields_name']]);
                        if (!$dati[$field['fields_name']]) {
                            $this->error = self::ERR_VALIDATION_FAILED;
                            $this->errorMessage = "{$field['fields_draw_label']} non è una data valida";
                            return false;
                        }
                    }
                    break;
                case 'date_time':
                    if(empty($dati[$field['fields_name']])) {
                        $dati[$field['fields_name']] = null;
                    } else {
                        // Vale la stessa nota fatta per il [date]
                        $dati[$field['fields_name']] = dateTime_toDbFormat($dati[$field['fields_name']]);
                        if (!$dati[$field['fields_name']]) {
                            $this->error = self::ERR_VALIDATION_FAILED;
                            $this->errorMessage = "{$field['fields_draw_label']} non è una data valida";
                            return false;
                        }
                    }
                    break;
                case 'wysiwyg':
                    if(isset($dati[$field['fields_name']])) {
                        $bURL = (function_exists('base_url_template')? base_url_template(): base_url());
                        $dati[$field['fields_name']] = str_replace($bURL, '{base_url}', $dati[$field['fields_name']]);
                    }
                    break;
                case 'input_password':
                    
                    /**
                     * In modifica se nei dati passati nel post non c'è la password,
                     * allora non devo calcolare la hash nuovamente
                     */
                    if(empty($dati[$field['fields_name']]) || empty($originalData[$field['fields_name']])) {
                        // La password non è stata passata, quindi non mi serve inserirla o modificarla
                        unset($dati[$field['fields_name']]);
                    } else {
                        // Passata una nuova password - devo hasharla
                        $dati[$field['fields_name']] = md5($originalData[$field['fields_name']]);
                    }
                    
                    break;
                case 'map':
                    
                    $fieldData = $dati[$field['fields_name']];
                    $exp = array();
                    
                    if (is_array($fieldData)) {
                        if (isset($fieldData['geo'])) {
                            $dati[$field['fields_name']] = $fieldData['geo'];
                            break;
                        } elseif (isset($fieldData['lat']) && isset($fieldData['lng'])) {
                            $exp = array($fieldData['lat'], $fieldData['lng']);
                        } elseif (count($fieldData) > 1) {
                            $exp = array_values($fieldData);
                        } else {
                            unset($dati[$field['fields_name']]);
                            break;
                        }
                    } else {
                        $exp1 = (strpos($fieldData, ';') != false)? explode(';', $fieldData): array();
                        $exp2 = (strpos($fieldData, ',') != false)? explode(',', $fieldData): array();
                        
                        if (count($exp1) === 2) {
                            $exp = $exp1;
                        } elseif (count($exp2) === 2) {
                            $exp = $exp2;
                        } else {
                            unset($dati[$field['fields_name']]);
                            break;
                        }
                    }
                    
                    $dati[$field['fields_name']] = empty($exp)? null: $this->db->query("SELECT ST_GeographyFromText('POINT({$exp[1]} {$exp[0]})') AS geography")->row()->geography;
                    break;
                    
                case 'date_range':
                    if($sql_type === 'DATERANGE' && !isValidDateRange($dati[$field['fields_name']])) {
                        $dati[$field['fields_name']] = '['.implode(',', array_map(function($date) { return date_toDbFormat($date); }, explode(' - ', $dati[$field['fields_name']]))).']';
                    }
                    break;
            }
            
            
            switch ($sql_type) {
                case 'INT':
                    if($dati[$field['fields_name']] && !is_numeric($dati[$field['fields_name']])) {
                        // FIX: non voglio fare il sanitize dell'input se è già una stringa numerica
                        $dati[$field['fields_name']] = filter_var($dati[$field['fields_name']], FILTER_SANITIZE_NUMBER_INT);
                    }
                    
                    if( $dati[$field['fields_name']] === '' && $field['fields_required'] === 'f' ) {
                        $dati[$field['fields_name']] = null;
                    }
                    break;
                 
                case 'FLOAT':
                    $dati[$field['fields_name']] = (float) filter_var(str_replace(',', '.', $dati[$field['fields_name']]), FILTER_SANITIZE_NUMBER_FLOAT,FILTER_FLAG_ALLOW_FRACTION);
                    break;
                 
                case 'BOOL':
                    if(isset($dati[$field['fields_name']])) {
                        if( ! in_array($dati[$field['fields_name']], array('t', 'f'))) {
                            $dati[$field['fields_name']] = ($dati[$field['fields_name']]? 't': 'f');
                        }
                    } else {
                        $dati[$field['fields_name']] = 'f';
                    }
                    break;
                    
                case 'TIMESTAMP WITHOUT TIME ZONE':
                    if( isset($dati[$field['fields_name']]) && !$dati[$field['fields_name']] ) {
                        unset($dati[$field['fields_name']]);
                    }
                    break;
                case 'DATERANGE':
                    if(!isValidDateRange($dati[$field['fields_name']])) {
                        $this->error = self::ERR_VALIDATION_FAILED;
                        $this->errorMessage = "{$field['fields_draw_label']} non è un date-range nel formato corretto";
                        return false;
                    }
                    break;
            }
            
        }
        
        // Controllo delle regole custom di validazione
        foreach($rules_date_before as $rule) {
            $before = $rule['before'];
            $after = $rule['after'];
            $message = $rule['message'];
            
            if(isset($dati[$before]) && isset($dati[$after]) && strtotime($dati[$after]) <= strtotime($dati[$before])) {
                if( ! $message) {
                    $message = "La data di inizio dev'essere antecedente alla data di fine";
                }
                
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $message;
                return false;
            }
        }
        
        /**
         * Eseguo il process di pre-action
         */
        $processed_data = $this->runDataProcessing($entity_data['entity_id'], $editMode? 'pre-update': 'pre-insert', array('post' => $dati, 'value_id' => $value_id));
        if(isset($processed_data['post'])) {
            // Metto i dati processati nell'array da inserire su db
            $dati = $processed_data['post'];
        }
        
        // Unsetta l'id entità - per sicurezza, non si sa mai cosa viene mandato tramite api
        unset($dati[$entity.'_id']);
        
        /*
         * Filtro i campi su cui sto per fare la query.
         * Unsetto quelli in più - prima recupero un array con i nomi dei campi
         * reali, poi dalle chiavi che sto inseredo tolgo quelle valide - ciò
         * che resta sono quelle in più - le unsetto
         */
        $entityFields = array_key_map($this->db->get_where('fields', ['fields_entity_id' => $entity_data['entity_id']])->result_array(), 'fields_name');
        $invalidFields = array_diff(array_keys($dati), $entityFields);
        if ($invalidFields) {
            $this->error = self::ERR_VALIDATION_FAILED;
            $this->errorMessage = "I seguenti campi non sono accettati: " . implode(', ', $invalidFields);
            return false;
        }
        
        return true;
    }
    
    
    
    
    /**
     * Cloni del model datab del crm
     */
    protected function run_post_process($entity_id, $when, $data = array()) {
        $this->runDataProcessing($entity_id, $when, $data);
    }
    
    public function runDataProcessing($entity_id, $when, $data = array()) {
        
        if( ! is_numeric($entity_id) && is_string($entity_id)) {
            $entity = $this->getEntityByName($entity_id);
            $entity_id = $entity['entity_id'];
        }

        if (!isset($this->_loadedDataProcessors[$entity_id][$when])) {
            
            switch ($this->processMode) {
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
        return ($entity instanceof CI_DB_result && $entity->num_rows() > 0)? $entity->row_array(): array();
    }
    
    private function getFields($entity_id) {
        $query = $this->db->query("SELECT * FROM fields WHERE fields_entity_id = '{$entity_id}'");
        return ($query instanceof CI_DB_result && $query->num_rows() > 0)? $query->result_array(): array();
    }
}




class ApiException extends Exception {}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */