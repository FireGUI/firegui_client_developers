<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Api extends CI_Controller {

    /**
     * @var Apilib
     */
    public $apilib;

    public function __construct() {
        parent::__construct();
        $this->load->library('apilib');
        $this->apilib->setDebug(false);
        $this->apilib->setProcessingMode(Apilib::MODE_API_CALL);
        header('Access-Control-Allow-Origin: *');
    }

    /*     * *********************************
     * Rest actions
     */

    /**
     * Mostra una lista di record dell'entità richiesta
     * @param string $entity    Il nome dell'entità
     */
    public function index($entity = null) {

        $this->logAction(__FUNCTION__, func_get_args());

        try {
            $output = $this->apilib->index($entity);
            $this->showOutput($output);
        } catch (ApiException $e) {
            $this->showError($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Ritorna un json con tutti i dati di una determinata entità
     * @param string $entity
     * @param int $id
     */
    public function view($entity = null, $id = null) {

        try {
            $output = $this->apilib->view($entity, $id);
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

    public function search($entity = null, $limit = null, $offset = 0, $orderBy = null, $orderDir = 'ASC') {
        try {
            $postData = array_filter((array) $this->input->post());
            $getData = array_filter((array) $this->input->get());

            $output = $this->apilib->search($entity, array_merge($getData, $postData), $limit, $offset, $orderBy, $orderDir);
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

            $unprocessedOutput = $this->apilib->searchFirst($entity, $data);

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

    public function clear_cache($realDelete = 0) {
        $testMode = !((bool) $realDelete);
        $cleared = $this->apilib->clearCache($testMode);

        if ($testMode) {
            echo "<span style='color:red'>In modalit&agrave; test i file non vengono rimossi, ma solo elencati (per rimuoverli aggiungere parametro 1 sull'URL).</span>";
        }

        echo '<pre>'
        . 'Sono stati rimossi ' . count($cleared) . ' file:'
        . PHP_EOL
        . ($cleared ? ' - ' : '') . implode(PHP_EOL . ' - ', $cleared)
        . '</pre>';
    }

    /**
     * Mostra schermata di aiuto
     */
    public function help() {
        $this->load->view('api-help', array('errors' => $this->apilib->getApiMessages()));
    }

    private function logAction($method, array $params, $output = array()) {

        $serial_params = serialize($params);
        $serial_get = serialize($_GET);
        $serial_post = serialize($_POST);
        $serial_files = serialize($_FILES);
        $serial_output = serialize($output);

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