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
        //debug($post,true);
//*debug($post['field_permission'],true);
        // Remove all existing permissions for this token
        $this->db->where('api_manager_permissions_token', $token_id)->delete('api_manager_permissions');
        $this->db->where('api_manager_fields_permissions_token', $token_id)->delete('api_manager_fields_permissions');

        // Process entity permissions
        foreach ($post['entity_permission'] as $entity_name => $permission) {

            

            $entity = $this->datab->get_entity_by_name($entity_name);

            if (!$entity) {
                $this->showOutput("Entity not found: $entity_name", 5);
                continue;
            }

            $data_insert = [
                'api_manager_permissions_token' => $token_id,
                'api_manager_permissions_entity' => $entity['entity_id'],
                'api_manager_permissions_chmod' => is_numeric($permission)?$permission:null,
            ];

            if (!empty($post['entity_where'][$entity_name])) {
                $data_insert['api_manager_permissions_where'] = $post['entity_where'][$entity_name];
            }
            // if ($entity_name == 'customers') {
            //     debug($data_insert, true);
            // }
            $this->db->insert('api_manager_permissions', $data_insert);
        }

        // Process field permissions
        if (isset($post['field_permission'])) {
            $field_permissions_to_insert = [];

            foreach ($post['field_permission'] as $entity_name => $permissions) {
                $entity = $this->datab->get_entity_by_name($entity_name);

                if (!$entity) {
                    continue; // Skip if entity not found
                }

                foreach ($permissions as $permission_level => $field_names) {
                    foreach ($field_names as $field_name) {
                        $field = $this->datab->get_field_by_name($field_name);

                        if (!$field) {
                            continue; // Skip if field not found
                        }

                        $field_permissions_to_insert[] = [
                            'api_manager_fields_permissions_token' => $token_id,
                            'api_manager_fields_permissions_field' => $field['fields_id'],
                            'api_manager_fields_permissions_chmod' => $permission_level,
                        ];
                    }
                }
            }

            // Bulk insert field permissions
            if (!empty($field_permissions_to_insert)) {
                $this->db->insert_batch('api_manager_fields_permissions', $field_permissions_to_insert);
            }
        }

        $this->mycache->clearCache();

        $this->showOutput(t('Permissions successfully saved!'), 4);
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
    protected function stampa($pagina, $value_id = null)
    {
        $this->output->setTags($this->layout->getRelatedEntities());

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/head.php")) {
            $this->template['head'] = $this->load->view('custom/layout/head', array(), true);
        } else {
            $this->template['head'] = $this->load->view('layout/head', array(), true);
        }

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/header.php")) {
            $this->template['header'] = $this->load->view('custom/layout/header', array(), true);
        } else {
            $this->template['header'] = $this->load->view('layout/header', array(), true);
        }

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/sidebar.php")) {
            $this->template['sidebar'] = $this->load->view('custom/layout/sidebar', array(), true);
        } else {
            $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        }

        $this->template['page'] = $pagina;

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/footer.php")) {
            $this->template['footer'] = $this->load->view('custom/layout/footer', null, true);
        } else {
            $this->template['footer'] = $this->load->view('layout/footer', null, true);
        }

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/foot.php")) {
            $this->template['foot'] = $this->load->view('custom/layout/foot', null, true);
        } else {
            $this->template['foot'] = $this->load->view('layout/foot', null, true);
        }

        // foreach ($this->template as $key => $html) {
        //     $this->template[$key] = $this->layout->replaceTemplateHooks($html, $value_id);
        // }
        $page = $this->load->view('layout/main', $this->template, true);
        $page = $this->layout->replaceTemplateHooks($page, $value_id);
        $this->output->append_output($page);
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