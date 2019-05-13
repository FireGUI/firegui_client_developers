<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Api extends MY_Controller {
    
    public function __construct() {
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
        header('Content-Type: application/json'); 

        // Niente profiler in API
        // Profiler
        $this->output->enable_profiler(false);
    }
    
    
    public function _remap($method, $params = []) {
        
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
        
        // Controlla nuovamente se il metodo esiste (potrebbe essere cambiato
        // nell'if precedente)
        if (!method_exists($this, $method)) {
            $this->showError("Risorsa non trovata", 404, 404);
            die();
        }
        
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
    public function index($entity = null, $depth = 2) {

        $this->logAction(__FUNCTION__, func_get_args());

        try {
            $output = $this->apilib->index($entity, $depth);
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
    public function view($entity = null, $id = null, $depth = 2) {

        try {
            $output = $this->apilib->view($entity, $id, $depth);
            $this->logAction(__FUNCTION__, func_get_args());
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
    public function create($entity = null, $output = 'json') {

        try {
            $outputData = $this->apilib->create($entity);
            $this->logAction(__FUNCTION__, func_get_args(), $outputData);
            $this->buildOutput($output, $outputData);
        } catch (ApiException $e) {
            $this->logAction(__FUNCTION__, func_get_args());
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Crea record multipli da post
     * @param string $entity
     * @param string $output
     */
    public function create_many($entity = null, $output = 'json') {

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
     * Fa l'update del record con id passato aggiornando i dati da post
     * Ritorna i nuovi dati via json
     * @param string $entity
     */
    public function edit($entity = null, $id = null, $output = 'json') {

        try {
            $outputData = $this->apilib->edit($entity, $id);
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
    public function delete($entity = null, $id = null, $output = 'json') {

        $this->logAction(__FUNCTION__, func_get_args());

        try {
            $this->apilib->delete($entity, $id);
            $this->buildOutput($output, array());
        } catch (ApiException $e) {
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    public function search($entity = null, $limit = null, $offset = 0, $orderBy = null, $orderDir = 'ASC', $maxDepth = 2) {
        try {
            $postData = array_filter((array) $this->input->post());
            $getData = array_filter((array) $this->input->get());

            $output = $this->apilib->search($entity, array_merge($getData, $postData), $limit, $offset, $orderBy, $orderDir, $maxDepth);
            $this->logAction(__FUNCTION__, func_get_args());
            $this->showOutput($output);
        } catch (ApiException $e) {

            /** Salvo su log il database error nascondendolo all'utente */
            if ($e->getCode() == Apilib::ERR_INTERNAL_DB) {
                $this->logAction(__FUNCTION__, func_get_args(), $e->getPrevious()->getMessage());
            }

            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    public function login($entity = null) {
        $postData = array_filter((array) $this->input->post());
        $getData = array_filter((array) $this->input->get());
        $data = array_merge($getData, $postData);

        try {

            if ($entity) {
                $data = $this->apilib->runDataProcessing($entity, 'pre-login', $data);
            }
            
            if (defined('LOGIN_ACTIVE_FIELD')) {
                $data[LOGIN_ACTIVE_FIELD] = DB_BOOL_TRUE;
            }
            
            //Matteo: passo depth 0 sennò al loginmi prende comunque tutte le entità correlate (troppe)
            $unprocessedOutput = $this->apilib->searchFirst($entity, $data, 0, null, 'ASC', 1);

            $this->logAction(__FUNCTION__, func_get_args());
            $output = $this->apilib->runDataProcessing($entity, 'login', $unprocessedOutput);
            
            if ($output) {
                $this->apilib->logSystemAction(Apilib::LOG_LOGIN);
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

    /**
     * 
     * @param string $outputMode One of redirect|json
     * @param array $outputData
     */
    private function buildOutput($outputMode, array $outputData = array()) {
        switch ($outputMode) {
            case 'redirect':
                $url = $this->input->get('url');
                if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
                    $this->showError('Per effetturare il redirect alla pagina desiderata è necessario passare $_GET[url] - [dati salvati correttamente]');
                } else {
                    redirect($url);
                }
                break;

            case 'json': default:
                $this->showOutput($outputData);
                break;
        }
    }

    /**
     * Ritorna in json i campi di un'entità
     * @param type $entity
     */
    public function describe($entity = null, $output = 'debug') {
        $details = $this->apilib->describe($entity);

        switch ($output) {
            case 'json':
                echo json_encode($details);
                break;

            case 'debug': default:
                echo '<pre>' . print_r($details, true) . '</pre>';
        }
    }

    public function entities() {
        $tables = $this->apilib->entityList();
        echo '<pre>' . print_r($tables, true) . '</pre>';
    }

    public function support() {
        $tables = $this->apilib->supportList();
        echo '<pre>' . print_r($tables, true) . '</pre>';
    }

    public function debug($mode = 'html') {
        $post = $this->input->post()? : array();
        $get = $this->input->get()? : array();
        $files = $_FILES;

        switch ($mode) {
            case 'json':
                echo json_encode(array(
                    'post' => $post,
                    'get' => $get,
                    'files' => $files
                ));
                break;

            case 'html':
            default :
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
    public function help() {
        header('Content-Type: text/html');
        $this->load->view('api-help', array('errors' => $this->apilib->getApiMessages()));
    }

    private function logAction($method, array $params, $output = array()) {

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
            'log_api_output' => $serial_output
        ));
    }

    /**
     * Ritorna l'errore corrente o quello passato
     */
    private function showError($message, $code, $httpStatus = 500) {
        //set_status_header($httpStatus);
        $this->showOutput($message, $code);
    }

    /**
     * Ritorna l'output passato terminando lo script
     * @param array|string $message
     * @param int $status
     */
    private function showOutput($message = array(), $status = 0) {
        echo json_encode(array(
            'status' => $status,
            'message' => is_string($message) ? $message : null,
            'data' => is_array($message) ? $message : array()
        ));
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */