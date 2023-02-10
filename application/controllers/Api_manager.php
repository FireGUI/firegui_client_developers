<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Api_manager extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if ($this->auth->guest()) {
            // FIX: siamo nel controller main, quindi l'uri dovrebbe cominciare con main
            $uri = explode('/', uri_string());

            foreach ($uri as $k => $chunk) {
                if ($chunk === 'main') {
                    // Se il chunk è main allora sono 'apposto'
                    break;
                } else {
                    // Altrimenti è un prefisso che è già contato nel base_url, quindi lo unsetto
                    unset($uri[$k]);
                }
            }
            if ($this->input->get('source')) {
                $append = '?source=' . $this->input->get('source');
            } else {
                $append = '';
            }
            $redirection_url = base_url(implode('/', $uri));
            $this->auth->store_intended_url($redirection_url);
            redirect('access' . $append);
        }

        $this->apilib->setProcessingMode(Apilib::MODE_API_CALL);
        $this->apilib->setLanguage();

        // Niente profiler in API
        $this->output->enable_profiler(false);
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
        if (!$this->datab->is_admin()) {
            $pagina = '<h1 style="color: #cc0000;">Permission denied</h1>';
            $this->stampa($pagina);
            return;
        }

        $where = array();
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            // Se c'è un login field mi aspetto che sia un booleano e
            // dev'essere true
            $where[LOGIN_ACTIVE_FIELD] = DB_BOOL_TRUE;
        }
        $dati['users'] = $this->datab->get_entity_preview_by_name(LOGIN_ENTITY, $where);
        asort($dati['users']);

        $dati['tokens'] = $this->db
            ->join(LOGIN_ENTITY, LOGIN_ENTITY . '.' . LOGIN_ENTITY . '_id = api_manager_tokens.api_manager_tokens_user', 'LEFT')
            ->order_by('api_manager_tokens_id', 'DESC')
            ->get('api_manager_tokens')
            ->result_array();

        $dati['logs'] = $this->db->limit(100)->order_by('log_api_date', 'DESC')->get('log_api')->result_array();


        $dati['current_page'] = 'api_manager';

        $pagina = $this->load->view("pages/api_manager", array('dati' => $dati), true);
        $this->stampa($pagina);
    }

    public function add_token()
    {
        $token_data = $this->input->post();


        if (empty($token_data['api_manager_tokens_token'])) {
            $token_data['api_manager_tokens_token'] = $this->generate_public_token($token_data);
        }
        $token_data['api_manager_tokens_creation_date'] = date('Y-m-d H:m:s');

        if (empty($token_data['api_manager_tokens_ms_between_requests'])) {
            unset($token_data['api_manager_tokens_ms_between_requests']);
        }
        if (empty($token_data['api_manager_tokens_limit_per_minute'])) {
            unset($token_data['api_manager_tokens_limit_per_minute']);
        }

        $this->db->insert('api_manager_tokens', $token_data);
        //TODO: forzare i permessi di default, ovvero:
        //      - support table in sola lettura
        //      - campi creation date e modify date sola lettura
        //      - entità con campo delete, inibisco il permesso di cancellazione

        $this->showOutput(null, 2);
    }

    public function delete_token($token_id)
    {
        try {
            $this->db->where('api_manager_tokens_id', $token_id)->delete('api_manager_tokens');
        } catch (Exception $ex) {
            die($this->input->is_ajax_request() ? json_encode(['status' => 3, 'txt' => $ex->getMessage()]) : $ex->getMessage());
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode(['status' => 2]);
        } else {
            redirect(filter_input(INPUT_SERVER, 'HTTP_REFERER'));
        }
    }

    public function permissions($token_id)
    {
        $dati = [];
        $dati['token'] = $token_id;

        $pagina = $this->load->view("pages/api_manager/api_manager_permissions", array('dati' => $dati), true);

        echo $pagina;
    }

    public function get_fields_by_entity_name($entity_name)
    {
        $fields = $this->datab->get_entity_by_name($entity_name)['fields'];
        foreach ($fields as $key => $field) {
            $solo_nome_campo = str_ireplace($entity_name . '_', '', $field['fields_name']);
            $fields[$key]['fields_name_friendly'] = ucwords(str_replace('_', ' ', $solo_nome_campo));
        }

        echo json_encode($fields);
    }

    public function get_entity_permissions($token_id, $entity_name)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);
        echo json_encode(
            $this->db->get_where('api_manager_permissions', [
                'api_manager_permissions_token' => $token_id,
                'api_manager_permissions_entity' => $entity['entity_id'],
            ])->row_array()
        );
    }

    public function get_fields_permissions($token_id, $entity_name)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);
        echo json_encode(
            $this->db
                ->join('fields', 'fields.fields_id = api_manager_fields_permissions.api_manager_fields_permissions_field', 'LEFT')
                ->where('api_manager_fields_permissions_token', $token_id)
                ->where("api_manager_fields_permissions_field IN (SELECT fields_id FROM fields WHERE fields_entity_id = '{$entity['entity_id']}')", null, false)
                ->get('api_manager_fields_permissions')->result_array()
        );
    }

    public function get_permissions($token_id, $entity_name)
    {
        $entity = $this->datab->get_entity_by_name($entity_name);
        echo json_encode([
            'entity_permissions' => $this->db->get_where('api_manager_permissions', [
                'api_manager_permissions_token' => $token_id,
                'api_manager_permissions_entity' => $entity['entity_id'],
            ])->result_array(),
            'fields_permissions' => $this->db->get_where('api_manager_fields_permissions', [
                'api_manager_fields_permissions_token' => $token_id,
                "api_manager_fields_permissions_field IN (SELECT fields_id FROM fields WHERE fields_entity = '{$entity['entity_id']}')",
            ])->result_array()
        ]);
    }

    public function set_permissions($token_id)
    {
        $post = $this->input->post();

        $entity_name = $post['entity_name'];
        unset($post['entity_name']);

        $where = $post['entity_where'];
        unset($post['entity_where']);

        $entity_permission = $post['entity_permission'];
        unset($post['entity_permission']);

        if ($entity_name) {
            $entity = $this->datab->get_entity_by_name($entity_name);
        } else {
            $this->showOutput("Choose entity", 5);
            exit;
        }

        //Rimuovo i vecchi permessi
        $this->db
            ->where('api_manager_permissions_token', $token_id)
            ->where('api_manager_permissions_entity', $entity['entity_id'])
            ->delete('api_manager_permissions');

        switch ($entity_permission) {
            case '': //Tutti i permessi
                //Non faccio niente, non serve inserire nulla (null viene considerato come "tutti i permessi" in automatico)

                $this->db->insert('api_manager_permissions', [
                    'api_manager_permissions_token' => $token_id,
                    'api_manager_permissions_entity' => $entity['entity_id'],
                    'api_manager_permissions_chmod' => '',
                    'api_manager_permissions_where' => $where ?: ''
                ], true);
                break;
            case '0': //Nessun permesso
            case '1': //Lettura
            case '2': //Scrittura solo update
            case '3': //Scrittura solo insert
            case '4': //Scrittura insert e update
                $data_insert = [
                    'api_manager_permissions_token' => $token_id,
                    'api_manager_permissions_entity' => $entity['entity_id'],
                    'api_manager_permissions_chmod' => $entity_permission,

                ];
                if ($where) {
                    $data_insert['api_manager_permissions_where'] = $where;
                }
                $this->db->insert('api_manager_permissions', $data_insert, false);
                break;

            default:
                throw new ApiException(t('Permission not recognized'));
                break;
        }

        //salvo i permessi specifici per field

        foreach ($post as $field_name => $chmod) {
            $field = $this->datab->get_field_by_name($field_name);
            //Pulisco a prescindere
            $this->db
                ->where('api_manager_fields_permissions_token', $token_id)
                ->where('api_manager_fields_permissions_field', $field['fields_id'])
                ->delete('api_manager_fields_permissions');
            if ($chmod !== '') { //Tutti i permessi
                $this->db->insert('api_manager_fields_permissions', [
                    'api_manager_fields_permissions_token' => $token_id,
                    'api_manager_fields_permissions_field' => $field['fields_id'],
                    'api_manager_fields_permissions_chmod' => $chmod,
                ]);
            }
        }


        $this->showOutput(t('Permissions successfully saved!'), 5);
    }

    protected function generate_public_token($token_data)
    {
        return md5(API_MANAGER_PRIVATE_KEY . serialize($token_data) . time());
    }


    /**
     * Template renderer
     * ---
     * Accept a string in html format
     *
     * @param string $pagina
     */
    protected function stampa($pagina)
    {
        $this->template['head'] = $this->load->view('layout/head', array(), true);
        $this->template['header'] = $this->load->view('layout/header', array(), true);
        $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        $this->template['page'] = $pagina;
        $this->template['footer'] = $this->load->view('layout/footer', null, true);

        echo $this->load->view('layout/main', $this->template, true);
    }

    //DA QUI INIZIANO LE CHIAMATE API VERE E PROPRIE



    //FUNZIONI IN SUPPORTO
    /**
     * Ritorna l'errore corrente o quello passato
     */
    private function showError($message, $code, $httpStatus = 500)
    {
        $this->showOutput($message, $code);
    }

    /**
     * Ritorna l'output passato terminando lo script
     * @param array|string $message
     * @param int $status
     */
    private function showOutput($message = [], $status = 0)
    {
        echo json_encode(
            array(
                'status' => $status,
                'txt' => is_string($message) ? $message : null,
                'data' => is_array($message) ? $message : array()
            )
        );
    }
}