<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Get_ajax extends MY_Controller
{
    public function __construct()
    {

        parent::__construct();

        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        // if ($this->input->is_ajax_request()) {
        //     $this->output->enable_profiler(false);
        // }

    }
    /**
     * Render di un layout in modale
     * @param int $layout_id
     * @param int $value_id
     */
    public function modal_layout($layout_id, $value_id = null)
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        //Se non è un numero, vuol dire che sto passando un url-key
        if (!is_numeric($layout_id)) {
            $result = $this->db->where('layouts_identifier', $layout_id)->get('layouts');

            if ($result->num_rows() == 0) {
                show_error("Layout '$layout_id' non trovato!");
            } else {
                $layout_id = $result->row()->layouts_id;
            }
        }

        //If layout is module dependent, preload translations
        $layout = $this->layout->getLayout($layout_id);
        if ($layout['layouts_module']) {
            $this->lang->language = array_merge($this->lang->language, $this->module->loadTranslations($layout['layouts_module'], array_values($this->lang->is_loaded)[0]));
            $this->layout->setLayoutModule($layout['layouts_module']);
        }

        // La richiesta non è ajax? Rimando al main/layout standard
        if (!$this->input->is_ajax_request()) {
            $gets = $this->input->get();
            $suffix = '';

            if ($gets) {
                foreach ($gets as $k => $v) {
                    $suffix .= "{$k}={$v}";
                }
            }

            if ($suffix) {
                $suffix = '?' . $suffix;
            }

            redirect(base_url("main/layout/{$layout_id}/{$value_id}{$suffix}"));
        }

        if (!$this->datab->can_access_layout($layout_id, $value_id)) {
            $this->layout->setLayoutModule();
            show_404();
        }

        $modalSize = $this->input->get('_size');
        if (!in_array($modalSize, ['small', 'large', 'extra'])) {
            $modalSize = null;
        }

        $dati = $this->datab->build_layout($layout_id, $value_id);
        if (is_null($dati)) {
            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
        } else {
            $dati['current_page'] = "layout_{$layout_id}";
            $dati['show_title'] = false;
            $dati['related_entities'] = $this->layout->getRelatedEntities();
            //debug($dati['related_entities']);
            $dati['layout_id'] = $layout_id;
            $dati['value_id'] = $value_id;
            $modal = true;
            $pagina = $this->load->view("pages/layout", array('dati' => $dati, 'value_id' => $value_id, 'modal' => $modal), true);
        }

        // Standard modal
        if ($this->input->get('_mode') == 'side_view') {
            e_json(['pagina' => $pagina, 'dati' => $dati]);
        } else {
            $this->load->view(
                "layout/modal_container",
                array(
                    'size' => $modalSize,
                    'title' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title'])),
                    'subtitle' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_subtitle'])),
                    'content' => $pagina,
                    'footer' => null,
                )
            );
        }
        $this->layout->setLayoutModule();
    }

    /**
     * Render di un form in modale
     * @param int $form_id
     * @param int $value_id
     */
    public function modal_form($form_id, $value_id = null, $duplicated = false)
    {
        // Check if i have form id or identifier
        if (!is_numeric($form_id)) {
            $form_id = $this->datab->get_form_id_by_identifier($form_id);
        }
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('No log in session found');
        }

        $modalSize = $this->input->get('_size');
        if (!in_array($modalSize, ['small', 'large', 'extra'])) {
            $modalSize = null;
        }

        $post_ids = $this->input->post('ids');
        if (empty($value_id) && !(empty($post_ids))) {
            $value_id = $this->input->post('ids');
        }
        if ($form_entity_module = $this->db->query("SELECT * FROM forms LEFT JOIN entity ON (forms_entity_id = entity_id) WHERE forms_id = '{$form_id}'")->row()->entity_module) {
            $this->lang->language = array_merge($this->lang->language, $this->module->loadTranslations($form_entity_module, array_values($this->lang->is_loaded)[0]));
            $this->layout->setLayoutModule($form_entity_module);
        }
        $form = $this->datab->get_form($form_id, $value_id);
        if ($duplicated == true) {
            $action_form = $form['forms']['action_url'];
            $parts = explode("/", $action_form); // Dividi l'URL in base al carattere "/"
            $key = array_search("true", $parts); // Trova l'indice della stringa "true"
            $result = implode("/", array_slice($parts, 0, $key)); // Unisci tutte le parti dell'URL fino all'indice "true"
            $form['forms']['action_url'] = $result;
        }
        if (!$form) {
            $this->load->view("box/errors/missing_form", ['form_id' => $form_id]);
            return;
        }

        if ($this->datab->can_write_entity($form['forms']['forms_entity_id'])) {
            $viewData = array(
                'size' => $modalSize,
                'value_id' => $value_id,
                'form' => $form,
                'data' => $this->input->post(),
            );

            $content = $this->datab->getHookContent('pre-form', $form_id, $value_id ?: null);

            if ($this->input->get('_mode') == 'side_view') {
                $content .= $this->load->view('pages/layouts/forms/form_modal_sideview', $viewData, true);
            } else {
                $content .= $this->load->view('pages/layouts/forms/form_modal', $viewData, true);
            }
            $content .= $this->datab->getHookContent('post-form', $form_id, $value_id ?: null);

            $pagina = $this->load->view('layout/content_return', ['content' => $content], true);

            $this->layout->setLayoutModule();
        } else {
            $content = '<div class="modal fade modal-scroll" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">';
            $content .= str_repeat('&nbsp;', 3) . t('You are not allowed to do this.');
            $content .= '</div></div></div>';
            $pagina = $this->load->view('layout/content_return', ['content' => $content], true);
            $this->layout->setLayoutModule();
        }
        if ($this->input->get('_mode') != 'side_view') {
            echo $pagina;
        } else {
            $dati['related_entities'] = $this->layout->getRelatedEntities();
            e_json(['pagina' => $pagina, 'dati' => $dati]);
        }
    }
    public function get_layout_box_content($lb_id, $value_id = null)
    {
        $layout_box = $this->layout->getLayoutBox($lb_id); //$this->db->get_where('layouts_boxes', ['layouts_boxes_id' => $lb_id])->row_array();

        $layout_id = $this->input->get('layout_id');
        if ($layout_id) {
            $this->layout->addLayout($layout_id);
        }


        $layout['content'] = $this->datab->getBoxContent($layout_box, $value_id, null);

        //debug($layout, true);

        // Fa il wrap degli hook pre e post che devono esistere per ogni
        // componente ad eccezione di custom views e custom php code
        // ---
        // Gli hook per il layout non vengono definiti da qua ma vengono
        // presi globali all'inizio del build layout
        $hookSuffix = $layout_box['layouts_boxes_content_type'];
        $hookRef = $layout_box['layouts_boxes_content_ref'];

        if ($hookSuffix && is_numeric($hookRef) && $hookSuffix !== 'layout') {
            $layout['content'] = $this->datab->getHookContent('pre-' . $hookSuffix, $hookRef, $value_id) .
                $layout['content'] .
                $this->datab->getHookContent('post-' . $hookSuffix, $hookRef, $value_id);
        }
        $this->load->view('layout/content_return', ['content' => $layout['content']]);

    }
    /**
     * Alias di modal_form
     * @see Get_ajax::modal_form
     */
    public function form_modal($form_id, $value_id = null)
    {
        $this->modal_form($form_id, $value_id);
    }

    /**
     * Alias di modal_layout
     * @see Get_ajax::modal_layout
     */
    public function layout_modal($layout_id, $value_id = null)
    {
        $this->modal_layout($layout_id, $value_id);
    }

    public function select_ajax_search()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        //Per retrocompatibilità, metronic passava q, mentre adminlte (col nuovo select2) passa q[term]
        if ($this->input->post('q[term]')) {
            $search = str_replace("'", "''", trim($this->input->post('q[term]')));
        } else {
            $search = str_replace("'", "''", trim($this->input->post('q')));
        }

        $limit = $this->input->post('limit');
        $table = $this->input->post('table');
        $id = ($this->input->post('id') ?: null);
        $referer = ($this->input->post('referer') ?: null);

        if (!$table) {
            set_status_header(401); // Unauthorized
            die('Entità non specificata');
        }

        /* Devo distinguere i due casi: support table e relazione */
        $entity = $this->datab->get_entity_by_name($table);

        // Non ho l'entity id quindi l'entità non esiste
        if (empty($entity['entity_id'])) {
            echo json_encode(array());
            return;
        }

        /** @todo    se $entity è di tipo ENTITY_TYPE_RELATION devo cercare in qualche modo di prendere l'entità relazionata */
        /**
         * Applico limiti permessi
         */
        $user_id = $this->auth->get(LOGIN_ENTITY . '_id');
        $where_limit = '';

        if ($user_id) {
            $operators = unserialize(OPERATORS);
            $field_limit = $this->db->query("SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = ? AND fields_entity_id = ?", [$user_id, $entity['entity_id']])->row_array();
            if (!empty($field_limit)) {
                $field = $field_limit['fields_name'];
                $op = $field_limit['limits_operator'];
                $value = $field_limit['limits_value'];

                if (array_key_exists($op, $operators)) {
                    $sql_op = $operators[$op]['sql'];

                    switch ($op) {
                        case 'in':
                            $value = "('" . implode("','", explode(',', $value)) . "')";
                            break;

                        case 'like':
                            $value = "'%{$value}%'";
                            break;
                    }

                    $where_limit = "{$field} {$sql_op} {$value}";
                }
            }
        }

        // Inizializza l'array dei risultati
        $result_json = array();

        $where_referer = '';
        if ($referer) {
            $fReferer = $this->db->get_where('fields', array('fields_name' => $referer, 'fields_ref' => $table))->row();
            if (!empty($fReferer->fields_select_where)) {
                $where_referer = $this->datab->replace_superglobal_data(trim($fReferer->fields_select_where));
            }
        }

        if ($entity['entity_type'] == ENTITY_TYPE_SUPPORT_TABLE) {
            if ($id !== null) {
                $row = $this->db->get_where($table, array($table . '_id' => $id))->row_array();
                $result_json = array('id' => $row[$table . '_id'], 'name' => $row[$table . '_value']);
            } else {
                // Il value di una support table è sempre una stringa quindi posso fare tranquillamente l'ilike
                if ($this->db->dbdriver != 'postgre') {
                    $where = implode(' AND ', array_filter([
                        "{$table}_value LIKE '%{$search}%'",
                        $where_limit,
                        $where_referer,
                    ]));

                    $result_array = $this->db->query("SELECT * FROM {$table} WHERE {$where} ORDER BY {$table}_value LIKE '{$search}' DESC, {$table}_value LIMIT {$limit}")->result_array();
                } else {
                    $where = implode(' AND ', array_filter([
                        "{$table}_value ILIKE '%{$search}%'",
                        $where_limit,
                        $where_referer,
                    ]));

                    $result_array = $this->db->query("SELECT * FROM {$table} WHERE {$where} ORDER BY {$table}_value ILIKE '{$search}' DESC, {$table}_value LIMIT {$limit}")->result_array();
                }

                $result_json = array();
                foreach ($result_array as $row) {
                    //Ho solo due campi per un record di support table: id e value
                    $result_json[] = array(
                        'id' => $row[$table . '_id'],
                        'name' => $row[$table . '_value'],
                    );
                }
            }
        } else {
            if ($id !== null) {

                $row = $this->datab->get_entity_preview_by_name($table, "{$table}_id = {$id}");

                if (!empty($row)) {
                    reset($row); // Puntatore array su prima posizione
                    $key = key($row); // Ottengo la prima e unica chiave
                    $result_json = array('id' => $key, 'name' => $row[$key]);
                }
            } else {
                //Devo prendere tutti i campi [preview (edit 14/10/2014)] dell'entità per poter creare il where su cui effettuare la ricerca
                $fields = $this->db
                    ->where('fields_entity_id', $entity['entity_id'])
                    ->where("(fields_preview = 1 OR fields_searchable = 1)", null, false)
                    ->get('fields')
                    ->result_array();



                //Check if a preview field is related to an entity, so add that entity preview fields in $fields
                foreach ($fields as $key => $field) {
                    if ($field['fields_ref'] && $field['fields_ref_auto_left_join']) {
                        //debug($field['fields_ref']);
                        $fields[$key]['support_fields'] = array_values(
                            array_filter(
                                $this->crmentity->getFields($field['fields_ref']),
                                function ($field) {
                                    return $field['fields_preview'] == DB_BOOL_TRUE || (!empty($field['fields_searchable']) && $field['fields_searchable'] == DB_BOOL_TRUE);
                                }
                            )
                        );
                    }
                }
                //debug($fields,true);
                $where = $this->datab->search_like($search, $fields);

                if ($where && $where_limit) {
                    $where .= " AND ({$where_limit})";
                } elseif ($where_limit) {
                    $where = $where_limit;
                }

                if ($where_referer) {
                    $where = ($where ? "{$where} AND ({$where_referer})" : $where_referer);
                }

                // Voglio i filtri su ricerca apilib. Quelli dei
                // post-process quindi prima mi prendo tutti gli id facendo un
                // apilib search e poi chiamo getEntityPreview con WHERE id IN (...)
                $preSearchRecords = $this->apilib->search($table, $where, $limit, null, null, null, 1);
                $result_json = array();
                if ($preSearchRecords) {
                    $idKey = "{$table}_id";
                    $whereIdInList = sprintf('%s IN (%s)', $idKey, implode(',', array_key_map($preSearchRecords, $idKey)));

                    $result_array = $this->datab->get_entity_preview_by_name($table, $whereIdInList, $limit);
                    if (!empty($result_array)) {
                        foreach ($result_array as $id => $name) {
                            $result_json[] = array('id' => $id, 'name' => $name);
                        }
                    }
                }
            }
        }

        // Questa cosa la faccio solo se non ho impostato un campo di ordinamento predefinito nell'entità
        $entityCustomActions = empty($entity['entity_action_fields']) ? [] : json_decode($entity['entity_action_fields'], true);
        if (empty($entityCustomActions['order_by_asc']) && empty($entityCustomActions['order_by_desc'])) {

            // Riordina per rilevanza i risultati [se ho ricerca > 2] oppure per
            // nome [se ricerca < 3] - solo se ho una lista di risultati e non uno
            // singolo
            $isOrderableResult = is_array($result_json) && !array_key_exists('id', $result_json);
            if ($isOrderableResult && $search && strlen($search) > 2) {
                usort($result_json, function ($val1, $val2) use ($search) {

                    // Ordine importanza:
                    //  - Stringa === al search
                    //  - Search compare prima
                    $name1 = $val1['name'];
                    $name2 = $val2['name'];
                    if (strtolower($name1) === strtolower($search)) {
                        return -1; // $val1 minore
                    } elseif (strtolower($name2) === strtolower($search)) {
                        return 1; // $val2 minore
                    } else {
                        $firstOccurrence1 = stripos($name1, $search);
                        $firstOccurrence2 = stripos($name2, $search);

                        // Non so perché ma non esiste la stringa cercata nel valore 1/2
                        if ($firstOccurrence1 === false) {
                            return 1; // $val2 minore
                        } elseif ($firstOccurrence2 === false) {
                            return -1; // $val1 minore
                        } elseif ($firstOccurrence1 < $firstOccurrence2) {
                            return -1; // $val1 minore
                        } elseif ($firstOccurrence2 > $firstOccurrence1) {
                            return 1; // $val2 minore
                        } else {
                            return 0; // $val1 === $val2
                        }
                    }
                });
            } elseif ($isOrderableResult) {
                usort($result_json, function ($val1, $val2) {
                    $name1 = $val1['name'];
                    $name2 = $val2['name'];
                    return ($name1 < $name2) ? -1 : 1;
                });
            }
        }

        // Ritorna il json alla vista
        $this->load->view('layout/json_return', ['json' => json_encode($result_json)]);
    }

    public function get_distinct_values()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $search = str_replace("'", "''", $this->input->post('q'));
        $limit = $this->input->post('limit');
        $field_id = $this->input->post('field');
        $id = str_replace("'", "''", $this->input->post('id') ? $this->input->post('id') : null);

        // Prepara un result vuoto
        $result_json = array();

        /* Devo distinguere i due casi: support table e relazione */
        $field = $this->db->from('fields')->join('entity', 'fields_entity_id = entity_id', 'left')->where('fields_id', $field_id)->get()->row_array();

        if (!empty($field)) {
            /**
             * Applico limiti permessi
             */
            $user_id = $this->auth->get(LOGIN_ENTITY . '_id');
            $operators = unserialize(OPERATORS);
            $field_limit = $this->db->query("SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = {$user_id} AND fields_id = {$field_id}")->row_array();
            $where_limit = '';
            if (!empty($field_limit)) {
                $field = $field_limit['fields_name'];
                $op = $field_limit['limits_operator'];
                $value = $field_limit['limits_value'];

                if (array_key_exists($op, $operators)) {
                    $sql_op = $operators[$op]['sql'];

                    switch ($op) {
                        case 'in':
                            $value = "('" . implode("','", explode(',', $value)) . "')";
                            break;

                        case 'like':
                            $value = "'%{$value}%'";
                            break;
                    }

                    $where_limit = "{$field} {$sql_op} {$value}";
                }
            }

            // Se ho un ref sicuramente nel campo avrò un id... non mi serve a molto, quindi le ricerche le faccio sulla tabella referenziata
            if ($field['fields_ref']) {
                $table = $field['fields_ref'];
                $field_name_select = $field['fields_name'];
                $ref_entity = $this->datab->get_entity_by_name($table);

                // Sto prendendo da una support table - faccio ricerca su value - se invece prendessi una relazione non è chiaro su cosa fare la ricerca - TODO
                if ($ref_entity['entity_type'] == ENTITY_TYPE_SUPPORT_TABLE) {
                    $field_name_search = $table . '_value';

                    // Se ho passato l'id vuol dire che sto caricando quel record esatto
                    if ($id) {
                        $result_array = $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$field['entity_name']} LEFT JOIN {$table} ON {$table}_id = {$field_name_select} WHERE {$field_name_search} = '{$id}' AND {$where_limit} LIMIT {$limit}")->row_array();
                    } else {
                        $result_array = $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$field['entity_name']} LEFT JOIN {$table} ON {$table}_id = {$field_name_select} WHERE {$field_name_search} ILIKE '%{$search}%' AND {$where_limit} LIMIT {$limit}")->result_array();
                    }
                }
            } else {
                $table = $field['entity_name'];
                $field_name_select = $field['fields_name'];
                $field_name_search = $field['fields_name'];

                // Se ho passato l'id vuol dire che sto caricando quel record esatto
                if ($id) {
                    $where = "{$field_name_search} = '{$id}'";
                    if ($where_limit) {
                        $where .= " AND {$where_limit}";
                    }

                    $result_array = $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$table} WHERE {$where} LIMIT {$limit}")->row_array();
                } else {
                    /**
                     * Qua devo fare un fix - prima facevo
                     * $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$table} WHERE {$field_name_search} ILIKE '%{$search}%' AND {$where_limit} LIMIT {$limit}")->result_array();
                     * ma ora non posso più perché se il $field_name_search fosse un intero non posso usare l'ilike
                     */
                    switch (strtoupper($field['fields_type'])) {
                        case 'VARCHAR':
                        case 'TEXT':
                            $where_search = "{$field_name_search} ILIKE '%{$search}%'";
                            break;
                        case DB_INTEGER_IDENTIFIER:
                        case 'INT':
                        case 'FLOAT':
                            // Nel caso di INT o FLOAT voglio fare una uguaglianza numerica classica
                            if (is_numeric($search)) {
                                $where_search = "{$field_name_search} = '{$search}'";
                            }
                    }

                    if (empty($where_search)) {
                        // Workaround to force having non-empty filter
                        $full_where = ($where_limit ? "{$where_search} AND {$where_limit}" : "1=1");
                    } else {
                        $full_where = ($where_limit ? "{$where_search} AND {$where_limit}" : $where_search);
                    }

                    $result_array = $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$table} WHERE {$full_where} ORDER BY {$field_name_select} LIMIT {$limit}")->result_array();
                }
            }

            if (isset($result_array)) {
                if ($id) {
                    $result_json = array('id' => $result_array[$field_name_select], 'name' => $result_array[$field_name_search]);
                } else {
                    // Ho fatto una ricerca quindi ritorno un array bidimenisonale
                    foreach ($result_array as $row) {
                        //Due campi:
                        $result_json[] = array(
                            'id' => $row[$field_name_select],
                            'name' => $row[$field_name_search],
                        );
                    }
                }
            }
        }
        $this->load->view('layout/json_return', ['json' => json_encode($result_json)]);

    }

    public function get_datatable_ajax($grid_id, $valueID = null)
    {
        if ($this->auth->guest()) {
            $this->load->view('layout/json_return', [
                'json' => json_encode(
                    array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'sEcho' => null, 'aaData' => []),
                )
            ]);

        } else {
            $referer = ($_SERVER['HTTP_REFERER'] ?? '');

            //Set current layout the same as the layout who caused this call
            if ($referer) {
                //catch layout id
                $ref_expl = explode('/', explode('?', $referer)[0]);
                $pointer = 0;
                $layout_id = null;
                while ($pointer < count($ref_expl) && stripos($ref_expl[$pointer], 'layout') === false) {
                    $pointer++;
                }
                if ($pointer < count($ref_expl)) {
                    //Layout id is the next parameter
                    $layout_id = $ref_expl[$pointer + 1];
                    if (!is_numeric($layout_id)) {
                        $layout_id = $this->layout->getLayoutByIdentifier($layout_id);
                    }
                }
                $this->layout->addLayout($layout_id);
            }

            /**
             * Info da datatable
             */
            $limit = $this->input->post('iDisplayLength') ?: 10;
            $offset = $this->input->post('iDisplayStart') ?: 0;
            $search = $this->input->post('sSearch') ?: null;
            $s_echo = $this->input->post('sEcho') ?: null;

            //$order_col = $this->input->post('iSortCol_0') ?: null;
            $order_col = $this->input->post('iSortCol_0');
            $order_dir = $this->input->post('sSortDir_0') ?: null;




            // Prendo i dati della grid
            $grid = $this->datab->get_grid($grid_id);

            $has_bulk = !empty($grid['grids']['grids_bulk_mode']);

            $preview_fields = $this->db->join('entity', 'fields_entity_id = entity_id')->get_where(
                'fields',
                array('fields_entity_id' => $grid['grids']['grids_entity_id'], 'fields_preview' => DB_BOOL_TRUE)
            )
                ->result_array();

            $where = $this->datab->search_like($search, array_merge($grid['grids_fields'], $preview_fields));

            //debug($where, true);

            if (preg_match('/(\()+(\))+/', $where)) {
                $where = '';
            }

            // fix da cui sopra per prendere il default order
            //if ($order_col !== null && isset($grid['grids_fields'][$order_col]['fields_name'])) {
            if ($order_col !== null && $order_col !== false) {
                if ($has_bulk) {
                    $order_col -= 1;
                }

                if (isset($grid['grids_fields'][$order_col]['fields_name'])) {
                    //Se il campo è multilingua, forzo l'ordinamento per chiave lingua corrente
                    if ($grid['grids_fields'][$order_col]['fields_multilingual'] == DB_BOOL_TRUE) {
                        if ($this->db->dbdriver == 'postgre') {
                            $order_by = "{$grid['grids_fields'][$order_col]['fields_name']}->>'{$this->datab->getLanguage()['id']}' {$order_dir}";
                        } else {
                            $order_by = "JSON_EXTRACT({$grid['grids_fields'][$order_col]['fields_name']}, \"$.{$this->datab->getLanguage()['id']}\") {$order_dir}";
                        }
                    } elseif ($grid['grids_fields'][$order_col]['fields_type'] == 'JSON') {
                        $order_by = "{$grid['grids_fields'][$order_col]['fields_name']}::TEXT {$order_dir}";
                    } else {
                        $order_by = "{$grid['grids_fields'][$order_col]['fields_name']} {$order_dir}";
                    }
                } else {
                    //Se entro qui, verifico se il campo passato per l'ordinamento non sia per caso un eval cachable...
                    if ($grid['grids_fields'][$order_col]['grids_fields_eval_cache_type'] == 'query_equivalent' || !empty($grid['grids_fields'][$order_col]['grids_fields_eval_cache_data'])) {
                        $order_by = "{$grid['grids_fields'][$order_col]['grids_fields_eval_cache_data']} {$order_dir}";
                    } else {
                        $order_by = null;
                    }
                }
            } else {
                $order_by = null;
            }

            $group_by = ($grid['grids']['grids_group_by']) ?: null;

            //Arrivato qui mi salvo in sessione sia la colonna di ordinamento, sia l'eventuale ricerca, così posso ripescarla in fase di export xml
            $grids_ajax_params = $this->session->userdata('grids_ajax_params');
            if (empty($grids_ajax_params)) {
                $grids_ajax_params = [];
            }
            $grids_ajax_params[$grid_id] = [
                'search' => $search,
                'order_by' => $order_by,
            ];
            $this->session->set_userdata('grids_ajax_params', $grids_ajax_params);
            //debug($grids_ajax_params,true);

            // Added where_append in get ajax
            if ($where_append = $this->input->get('where_append')) {
                if ($where) {
                    $where .= ' AND ' . $where_append;
                } else {
                    $where = $where_append;
                }
            }
            //debug($where);
            $grid_data = $this->datab->get_grid_data($grid, $valueID, $where, (is_numeric($limit) && $limit > 0) ? $limit : null, $offset, $order_by, false, ['group_by' => $group_by, 'search' => $search, 'preview_fields' => $preview_fields]);



            $out_array = array();
            $prev_row = [];
            foreach ($grid_data as $dato) {
                $dato['value_id'] = $valueID;
                $tr = array();
                if ($prev_row) {
                    $dato['prev_row'] = $prev_row;
                } else {
                    $dato['prev_row'] = false;
                }
                if ($has_bulk) {
                    $tr[] = '<input type="checkbox" class="js_bulk_check" value="' . $dato[$grid['grids']['entity_name'] . "_id"] . '" />';
                }
                foreach ($grid['grids_fields'] as $field) {

                    $tr[] = $this->datab->build_grid_cell($field, $dato);
                }
                //Unset to avoi override
                unset($dato['value_id']);
                // Controlla se ho delle action da stampare in fondo
                if ($grid['grids']['grids_layout'] == 'datatable_ajax_inline') {
                    $tr[] = $this->load->view('box/grid/inline_edit', array('id' => $dato[$grid['grids']['entity_name'] . "_id"]), true);
                    $tr[] = $this->load->view('box/grid/inline_delete', array('id' => $dato[$grid['grids']['entity_name'] . "_id"]), true);
                } elseif (($grid['grids']['grids_layout'] == 'datatable_ajax_inline_form' || $grid['grids']['grids_inline_edit'] == DB_BOOL_TRUE) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) {
                    $tr[] = $this->load->view('box/grid/inline_form_actions', array(
                        'id' => $dato[$grid['grids']['entity_name'] . "_id"],
                        'links' => $grid['grids']['links'],
                        'row_data' => $dato,
                        'grid' => $grid['grids'],
                    ), true);
                } elseif (grid_has_action($grid['grids']) && $grid['grids']['grids_actions_column'] == DB_BOOL_TRUE) {
                    $tr[] = $this->load->view('box/grid/actions', array(
                        'links' => $grid['grids']['links'],
                        'id' => $dato[$grid['grids']['entity_name'] . "_id"],
                        'row_data' => $dato,
                        'grid' => $grid['grids'],
                    ), true);
                }
                unset($dato['prev_row']);
                $prev_row = $dato;
                $out_array[] = $tr;
            }

            $totalRecords = $this->datab->get_grid_data($grid, $valueID, null, null, 0, null, true, ['group_by' => $grid['grids']['grids_group_by']]);
            $totalDisplayRecord = $this->datab->get_grid_data($grid, $valueID, $where, null, 0, null, true, ['group_by' => $grid['grids']['grids_group_by']]);
            //debug($this->load->capture_profiler_output(),true);
            $this->load->view('layout/json_return', [
                'json' => json_encode(
                    array(
                        'iTotalRecords' => $totalRecords,
                        'iTotalDisplayRecords' => $totalDisplayRecord,
                        'sEcho' => $s_echo,
                        'aaData' => $out_array,
                        'profiler' => ($this->input->get('_profiler')) ? $this->load->capture_profiler_output() : false
                    )
                )
            ]);

        }
    }

    public function get_map_markers($map_id, $value_id = null)
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $data = $this->datab->get_map($map_id);

        $where = array_filter((array) $this->datab->generate_where("maps", $map_id, $value_id));

        $fields = array();
        $isSearchMode = false;
        $post_where = (array) $this->input->post('where');

        $latlng_field = null;
        foreach ($data['maps_fields'] as $field) {
            $fields[$field['fields_name']] = $field;
            if ($field['maps_fields_type'] == 'latlng') {
                $latlng_field = $field['fields_name'];
            } elseif ($field['maps_fields_type'] == 'lat') {
                $latfield = $field['fields_name'];
            } elseif ($field['maps_fields_type'] == 'lon') {
                $lonfield = $field['fields_name'];
            }
        }

        if (!empty($post_where)) {
            foreach ($post_where as $post_cond) {
                $field = $post_cond['name'];
                $value = $post_cond['value'];
                if ($value) {
                    switch ($fields[$field]['fields_type']) {
                        case 'VARCHAR':
                        case 'TEXT':
                            $where[] = "{$field} ILIKE '%{$value}%'";
                            $isSearchMode = true;
                            break;

                        default:
                            $where[] = "{$field} = '{$value}'";
                            $isSearchMode = true;
                            break;
                    }
                }
            }
        }

        // Geography data
        $bounds = $this->input->post('bounds');
        if (!is_array($bounds)) {
            $bounds = json_decode($bounds, true);
        }
        if ($this->db->dbdriver == 'postgre') {
            if ($latlng_field !== null && $bounds && !$isSearchMode) {
                $ne_lat = $bounds['ne_lat'];
                $ne_lng = $bounds['ne_lng'];
                $sw_lat = $bounds['sw_lat'];
                $sw_lng = $bounds['sw_lng'];

                if ($ne_lng < 180 && $ne_lat < 90 && $sw_lng > -180 && $sw_lat > -90) {
                    $where[] = "ST_Intersects(ST_GeographyFromText('POLYGON(({$ne_lng} {$ne_lat},{$ne_lng} {$sw_lat},{$sw_lng} {$sw_lat},{$sw_lng} {$ne_lat},{$ne_lng} {$ne_lat}))'), {$latlng_field})";
                }
            }
        } else {
            if ($latlng_field !== null && $bounds && !$isSearchMode) {
                //debug($bounds,true);
                $ne_lat = $bounds['ne_lat'];
                $ne_lng = $bounds['ne_lng'];
                $sw_lat = $bounds['sw_lat'];
                $sw_lng = $bounds['sw_lng'];

                if ($ne_lng < 180 && $ne_lat < 90 && $sw_lng > -180 && $sw_lat > -90) {
                    $where[] = "(
                        (
                            SUBSTRING_INDEX(
                                TRIM(
                                    REPLACE(
                                        REPLACE({$latlng_field}, ',', ';'),
                                        ' ',
                                        ''
                                    )
                                ),
                                ';', 
                                1
                            ) BETWEEN {$sw_lat} AND {$ne_lat}
                        )
                        AND 
                        (
                            SUBSTRING_INDEX(
                                TRIM(
                                    REPLACE(
                                        REPLACE({$latlng_field}, ',', ';'),
                                        ' ',
                                        ''
                                    )
                                ),
                                ';', 
                                -1
                            ) BETWEEN {$sw_lng} AND {$ne_lng}
                        )
                    )";
                }
            }

        }

        //debug($where,true);

        $order_by = (trim($data['maps']['maps_order_by'])) ? $data['maps']['maps_order_by'] : null;
        $data_entity = $this->datab->getDataEntity($data['maps']['maps_entity_id'], implode(' AND ', array_filter($where)), null, null, $order_by, 2, false, [], ['group_by' => null]);
        //debug($where,true);
        $markers = array();
        if (!empty($data_entity)) {
            // Cerco un link se c'è qui per non fare una query ogni ciclo
            $link = $this->datab->get_detail_layout_link($data['maps']['maps_entity_id']);

            // Recupero tutti i lat e lon di ogni marker che mi serve per evitare di fare le query dopo
            $data_ids = array();
            foreach ($data_entity as $marker) {
                $data_ids[] = $marker[$data['maps']['entity_name'] . '_id'];
            }

            $geography = array();
            if ($this->db->dbdriver == 'postgre') {
                $this->db->select("{$data['maps']['entity_name']}_id as id, ST_Y({$latlng_field}::geometry) AS lat, ST_X({$latlng_field}::geometry) AS lon")
                    ->from($data['maps']['entity_name'])->where_in($data['maps']['entity_name'] . '_id', $data_ids);

                $result = $this->db->get();

                //TODO: in this result there are no joined tables... we need to use apilib instead of query to managed join automatically

                // Indicizzo i risultati per id
                foreach ($result->result_array() as $result) {
                    $geography[$result['id']] = array('lat' => $result['lat'], 'lon' => $result['lon']);
                }
            } else {
                //20210502 - MP - Deprecated
                // if ($latlng_field) {
                //     $this->db->select("{$data['maps']['entity_name']}_id as id, substring_index ( $latlng_field,';',1 )  AS lat, substring_index ( $latlng_field,';',-1 )  AS lon")
                //         ->from($data['maps']['entity_name'])->where_in($data['maps']['entity_name'] . '_id', $data_ids);
                // } else {
                //     $this->db->select("{$data['maps']['entity_name']}_id as id, $latfield  AS lat, $lonfield  AS lon")
                //         ->from($data['maps']['entity_name'])->where_in($data['maps']['entity_name'] . '_id', $data_ids);
                // }
            }

            foreach ($data_entity as $marker) {
                $mark = array();
                foreach ($data['maps_fields'] as $field) {
                    if ($field['maps_fields_type'] == 'description') {
                        // Posso avere più campi description
                        $mark['description'][] = $marker[$field['fields_name']];
                    } else {
                        $mark[$field['maps_fields_type']] = $marker[$field['fields_name']];
                    }
                }

                if (array_key_exists('description', $mark) and is_array($mark['description'])) {
                    $mark['description'] = implode('<br/>', $mark['description']);
                }

                // Get the id
                if (empty($mark['id'])) {
                    $mark['id'] = $marker[$data['maps']['entity_name'] . "_id"];
                }

                if (empty($mark['title'])) {
                    $mark['title'] = '#' . $mark['id'];
                }

                // Elaboro le coordinate
                if ((!empty($mark['latlng']) || !empty($mark['lat']))) {
                    if (isset($geography[$marker[$data['maps']['entity_name'] . "_id"]])) {
                        $mark['lat'] = $geography[$marker[$data['maps']['entity_name'] . "_id"]]['lat'];
                        $mark['lon'] = $geography[$marker[$data['maps']['entity_name'] . "_id"]]['lon'];
                    } elseif ($latlng_field) {
                        if (stripos($marker[$latlng_field], ',') !== false) {
                            $latlng_expl = explode(',', $marker[$latlng_field]);
                            $mark['lat'] = trim($latlng_expl[0]);
                            $mark['lon'] = trim($latlng_expl[1]);
                        } elseif (stripos($marker[$latlng_field], ';') !== false) {
                            $latlng_expl = explode(';', $marker[$latlng_field]);
                            $mark['lat'] = trim($latlng_expl[0]);
                            $mark['lon'] = trim($latlng_expl[1]);
                        } else {
                            continue;
                        }
                    }

                    $mark['link'] = ($link ? $link . '/' . $mark['id'] : '');
                    unset($mark['latlng']); // Questa chiave non serve più...
                    //debug($mark, true);
                    array_push($markers, $mark);
                }
            }
            //debug($markers);
            // eseguo eventuale post_process di tipo map rendering load marker
            $markers = $this->datab->run_post_process($data['maps']['maps_entity_id'], 'marker_load', $markers);
            //debug($markers);
        }

        // header('Content-Type: application/json');
        // echo json_encode($markers);
        $this->load->view('layout/json_return', ['json' => json_encode($markers)]);

    }

    public function get_calendar_events($calendar_id, $value_id = null)
    {

        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $data = $this->datab->get_calendar($calendar_id);
        $where = array();
        $filter = array_filter((array) $this->input->post('filters'));

        // Prendi eventi sse compresi tra start ed end
        $ts_start = $this->input->post('start');
        $ts_end = $this->input->post('end');



        if ($ts_start && $ts_end) {
            $start = dateTime_toDbFormat($ts_start);
            $end = dateTime_toDbFormat($ts_end);

            // Prendi data di partenza e fine
            $start_field = $end_field = null;
            foreach ($data['calendars_fields'] as $field) {
                switch ($field['calendars_fields_type']) {
                    case 'start':
                    case 'date_start':
                        $start_field = $field['fields_name'];
                        break;
                    case 'end':
                    case 'date_end':
                        $end_field = $field['fields_name'];
                        break;
                }
            }
            if ($start_field && !$end_field) {
                $end_field = $start_field;
            }

            if ($start_field && $end_field) {
                if ($this->db->dbdriver != 'postgre') {
                    $where[] = "(
                                ($start_field BETWEEN '$ts_start' AND '$ts_end') OR
                                ($start_field BETWEEN '$ts_end' AND '$ts_start') OR
                                ($end_field BETWEEN '$ts_start' AND '$ts_end') OR
                                ($end_field BETWEEN '$ts_end' AND '$ts_start')
                            )";
                } else {
                    $where[] = "'[{$start},{$end}]'::DATERANGE && (CASE WHEN ({$start_field}<{$end_field})
                        THEN ('[' || {$start_field} || ',' || {$end_field} || ']')::DATERANGE
                        ELSE ('[' || {$end_field} || ',' || {$start_field} || ']')::DATERANGE
                    END)";
                }
            }
        }

        $generatedWhere = $this->datab->generate_where('calendars', $calendar_id, $value_id, $where);

        //debug($generatedWhere, true);

        if (!empty($data['calendars']['calendars_custom_query'])) {
            $data_entity = $this->datab->getDataEntityByQuery($data['calendars']['calendars_entity_id'], $data['calendars']['calendars_custom_query'], $generatedWhere);
        } else {



            // Esegui la chiamata
            $data_entity = $this->apilib->search($data['calendars']['entity_name'], $generatedWhere, null, null, null, null, 3);


        }

        $previews = array();
        foreach ($data['calendars_fields'] as $field) {
            if ($field['fields_ref']) {

                $ids = array_filter(array_unique(array_map(function ($dato) use ($field) {
                    if (!array_key_exists($field['fields_name'], $dato)) {
                        return '';
                    }
                    return $dato[$field['fields_name']];
                }, $data_entity)));

                if (empty($ids)) {
                    $previews[$field['fields_ref']] = array();
                } else {
                    $previews[$field['fields_ref']] = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id IN (" . implode(',', $ids) . ")");
                }
            }
        }

        $events = array();

        foreach ($data_entity as $event) {
            $ev = array();
            foreach ($data['calendars_fields'] as $field) {
                if ($field['calendars_fields_type'] !== 'title' || (!empty($event[$field['fields_name']]) && empty($ev['title']))) {
                    if ($field['fields_ref'] && isset($previews[$field['fields_ref']][$event[$field['fields_name']]]) && in_array($field['calendars_fields_type'], array('title', 'description'))) {
                        $ev[$field['calendars_fields_type']] = $previews[$field['fields_ref']][$event[$field['fields_name']]];
                    } else {
                        $ev[$field['calendars_fields_type']] = $event[$field['fields_name']];
                    }
                }
            }

            // Non ha senso controllare il filtro se manca uno di questi due campi
            if (isset($ev['filter']) && isset($data['calendars']['calendars_filter_entity_id'])) {
                // Se settati il campo su cui filtrare e il valore del filtro, allora applico il filtro
                if (!in_array($ev['filter'], $filter)) {
                    continue;
                }
            }

            // Get the id
            if (!isset($ev['id']) || !$ev['id']) {
                $ev['id'] = $event[$data['calendars']['entity_name'] . "_id"];
            }
            //debug($ev);
            // Store timezone
            //Se non è mappato uno start nell'evento
            if (!array_key_exists('start', $ev)) {
                //Assumo che sia mappato un date start
                $ev['start'] = substr($ev['date_start'], 0, 10);

                if (array_key_exists('hours_start', $ev) && $ev['hours_start'] != '') {
                    $ev['start'] = "{$ev['start']} {$ev['hours_start']}";
                }

            }
            if (!array_key_exists('end', $ev)) {
                //Assumo che sia mappato un date end
                $ev['end'] = substr($ev['date_end'], 0, 10);
                if (array_key_exists('hours_end', $ev) && $ev['hours_end'] != '') {
                    $ev['end'] = "{$ev['end']} {$ev['hours_end']}";
                }
            }

            if (array_key_exists('date_start', $ev)) {
                $hours_start = '00:00';
                $hours_start_seconds = ':00';
                if (array_key_exists('hours_start', $ev)) {
                    $hours_start_ex = explode(':', $ev['hours_start']);
                    if (count($hours_start_ex) == 3) {
                        $hours_start_seconds = "";
                    }
                    $hours_start = $ev['hours_start'];
                }

                $hours_end = '00:00';
                $hours_end_seconds = ':00';
                if (array_key_exists('hours_end', $ev)) {
                    $hours_end_ex = explode(':', $ev['hours_end']);
                    if (count($hours_end_ex) == 3) {
                        $hours_end_seconds = "";
                    }
                    $hours_end = $ev['hours_end'];
                }

                $ev['start'] = (new DateTime($ev['start']))->format("Y-m-d\T{$hours_start}{$hours_start_seconds}");

                if (!array_key_exists('date_end', $ev)) {
                    $ev['end'] = (new DateTime($ev['start']))->modify('+1 hour')->format("Y-m-d\T{$hours_end}{$hours_end_seconds}");
                } else {
                    $ev['end'] = (new DateTime($ev['end']))->format("Y-m-d\T{$hours_end}{$hours_end_seconds}");
                }
                if (
                    ((new DateTime($ev['start']))->format("Y-m-d") == (new DateTime($ev['end']))->format("Y-m-d"))
                    && ($hours_end < $hours_start)
                ) {
                    $ev['end'] = (new DateTime($ev['end']))->modify('+1 day')->format('Y-m-d\TH:i:s');
                }
            } else {
                $ev['start'] = (new DateTime($ev['start']))->format('Y-m-d\TH:i:s');

                if (!empty($ev['end'])) {
                    $ev['end'] = (new DateTime($ev['end']))->format('Y-m-d\TH:i:s');
                } else {
                    $ev['end'] = (new DateTime($ev['start']))->modify('+1 hour')->format('Y-m-d\TH:i:s');
                }
            }

            if (empty($ev['all_day'])) {
                $arr_start = explode('T', $ev['start']);
                $arr_end = explode('T', $ev['end']);
                //Fix per efficiente che aveva un campo timestamp ma draw html type date, quindi salvava sempre e comunque 00:00:00 nell'ora fine.
                $ev['allDay'] = ($arr_start[0] == $arr_end[0] && stripos($arr_start[1], '00:00:00') !== false && ((stripos($arr_end[1], '23:59:59') !== false) || (stripos($arr_end[1], '00:00:00') !== false)));
            } else {
                $ev['allDay'] = ($ev['all_day'] === DB_BOOL_TRUE);
            }

            if (empty($ev['title'])) {
                $ev['title'] = 'No title';
            }

            if ($ev['start'] && $ev['end']) {
                array_push($events, $ev);
            }
        }



        //        echo json_encode($events);
        $this->load->view('layout/json_return', ['json' => json_encode($events)]);

    }

    public function permission_table()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $identifier = $this->input->post('identifier');

        if (!$identifier) {
            return;
        }

        /** Considero i permessi come permessi utente se passo un numero come identificatore (lo user_id) */
        if (is_numeric($identifier)) {
            $dati['mode'] = ($identifier > 0) ? 'user' : 'group.create';
        } else {
            $dati['mode'] = 'group.edit';
        }

        $dati['entities'] = $this->db->where_in('entity_type', [ENTITY_TYPE_DEFAULT, ENTITY_TYPE_SUPPORT_TABLE])->get('entity')->result_array();
        $dati['modules'] = $this->datab->get_modules();

        try {
            $dati['permissions'] = ($dati['mode'] == 'group.create') ? [] : $this->datab->getPermission($identifier);
        } catch (Exception $ex) {
            $dati['permissions'] = [];
        }

        $dati['permissions_entities'] = array();
        $dati['permissions_modules'] = array();
        if (isset($dati['permissions']['permissions_id'])) {
            $permissions_entities = $this->db->get_where('permissions_entities', array('permissions_entities_permissions_id' => $dati['permissions']['permissions_id']))->result_array();
            $permissions_modules = $this->db->get_where('permissions_modules', array('permissions_modules_permissions_id' => $dati['permissions']['permissions_id']))->result_array();
            foreach ($permissions_entities as $ent) {
                $dati['permissions_entities'][$ent['permissions_entities_entity_id']] = $ent['permissions_entities_value'];
            }

            foreach ($permissions_modules as $mod) {
                $dati['permissions_modules'][$mod['permissions_modules_module_name']] = $mod['permissions_modules_value'];
            }
        }

        if ($dati['mode'] == 'user') {
            $this->db->select('limits.*, fields.fields_entity_id AS entity_id')->from('limits')
                ->join('fields', 'limits_fields_id = fields_id', 'left')
                ->where('limits_user_id', $identifier);
            $dati['limits'] = array_merge(array(null), $this->db->get()->result_array());
        }

        $dati['groups'] = array_key_map($this->db->where('permissions_group IS NOT NULL AND permissions_user_id IS NULL')->get('permissions')->result_array(), 'permissions_group');
        $this->load->view('box/permissions/table', array('dati' => $dati));
    }

    public function entity_fields()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $entity_id = $this->input->post('entity_id');
        $entity = $this->datab->get_entity($entity_id);
        $return = array();
        if (!empty($entity)) {
            switch ($entity['entity_type']) {
                case ENTITY_TYPE_SUPPORT_TABLE:
                    $return = $this->db->from('fields')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left')
                        ->join('entity', 'entity.entity_id = fields.fields_entity_id', 'left')->where('fields_entity_id', $entity_id)->get()->result_array();

                    foreach ($return as $k => $row) {
                        if (empty($row['fields_draw_label'])) {
                            $row['fields_draw_label'] = ucwords(str_replace($entity['entity_name'] . '_', '', $row['fields_name']));
                        }
                        $return[$k] = $row;
                    }
                    break;

                default:
                    $return = $this->datab->get_visible_fields($entity_id);
            }
        }
        echo json_encode($return);
    }

    public function get_field($id)
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $this->db->from('fields')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left')
            ->join('entity', 'entity.entity_id = fields.fields_entity_id', 'left')
            ->where('fields_id', $id);

        echo json_encode($this->db->get()->row_array());
    }

    public function search_field_values()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        // Leggi post e recupera field
        $field_id = $this->input->post('field_id');
        $search = $this->input->post('q');
        $id = $this->input->post('id');
        $return = array();

        $this->db->from('fields')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left')
            ->join('entity', 'entity.entity_id = fields.fields_entity_id', 'left')
            ->where('fields_id', $field_id);
        $field = $this->db->get()->row_array();
        if (empty($field)) {
            die();
        }

        if ($field['fields_type'] === DB_BOOL_IDENTIFIER) {
            $ids = explode(',', trim($id));
            if (!$id || in_array(DB_BOOL_TRUE, $ids)) {
                $return[] = array('id' => DB_BOOL_TRUE, 'name' => t('Yes'));
            }

            if (!$id || in_array(DB_BOOL_FALSE, $ids)) {
                $return[] = array('id' => DB_BOOL_FALSE, 'name' => t('No'));
            }
        } else {
            // Se ho un id lo devo preparare per utilizzarlo nel
            // WHERE ... IN ...
            if ($id) {
                $id = "'" . implode("', '", explode(',', trim($id))) . "'";
            }

            /**
             * 3 casi:
             * - Field semplice: faccio una ricerca nei valori del field richiesto
             * - Support table: cerco il value e ritorno gli id
             * - Relazione: cerco nei diversi field della relazione e ritorno gli id (ragionarci dopo)
             */
            $return = array();
            if (!$field['fields_ref']) {
                // fields_ref vuoto indica un field semplice (caso 1)
                if ($id) {
                    $results = $this->db->query("SELECT * FROM {$field['entity_name']} WHERE {$field['fields_name']} IN ({$id})")->result_array();
                } else {
                    $where = ($search ? "WHERE {$field['fields_name']} ILIKE '%{$search}%'" : '');
                    $results = $this->db->query("SELECT * FROM {$field['entity_name']} {$where} LIMIT 100")->result_array();
                }
                foreach ($results as $row) {
                    if (!empty($row[$field['fields_name']])) {
                        $item = ['id' => $row[$field['fields_name']], 'name' => $row[$field['fields_name']]];
                        $return[$item['id']] = $item;
                    }
                }
            } else {
                // Sono nel caso di una relazione oppure una support table (casi 2 e 3)
                $ref_entity = $this->datab->get_entity_by_name($field['fields_ref']);

                switch ($ref_entity['entity_type']) {
                    case ENTITY_TYPE_SUPPORT_TABLE:
                        // Support table (caso 2)
                        if ($id) {
                            $results = $this->db->query("SELECT * FROM {$ref_entity['entity_name']} WHERE {$ref_entity['entity_name']}_id IN ($id)")->result_array();
                        } else {
                            $where = ($search ? "WHERE {$ref_entity['entity_name']}_value ILIKE '%{$search}%'" : '');
                            $results = $this->db->query("SELECT * FROM {$ref_entity['entity_name']} {$where} LIMIT 100")->result_array();
                        }

                        foreach ($results as $row) {
                            if (!empty($row[$field['fields_name']])) {
                                $item = ['id' => $row[$ref_entity['entity_name'] . '_id'], 'name' => $row[$ref_entity['entity_name'] . '_value']];
                                $return[$item['id']] = $item;
                            }
                        }
                        break;

                    case ENTITY_TYPE_RELATION:
                        // ???
                        break;

                    default:
                        // Referenza su entità standard
                        if ($id) {
                            $where = "{$ref_entity['entity_name']}_id IN ($id)";
                        } elseif ($search) {
                            $where = $this->datab->search_like($search, $this->datab->get_entity_fields($ref_entity['entity_id']));
                        } else {
                            $where = '';
                        }

                        $previewResult = $this->datab->get_entity_preview_by_name($ref_entity['entity_name'], $where);
                        foreach ($previewResult as $id => $preview) {
                            $return[$id] = ['id' => $id, 'name' => $preview];
                        }
                }
            }
        }

        echo json_encode(array_values($return));
    }

    public function filter_multiselect_data()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        /**
         * Field to è il nome del campo da popolare - deve trovarsi o in una relazione o in una support table
         * From val è il valore su cui fitrare il to
         * Entity name è l'entità alla quale il to e il from appartengono
         */
        $field_name_to = rtrim($this->input->post('field_name_to'), '[]');
        $from_val = $this->input->post('field_from_val');

        $entity = $this->crmentity->getEntity($this->input->post('field_ref'));

        if (!$entity) {
            set_status_header(401); // Unauthorized
            die('Entità non specificata');
        }

        $entity_name = $entity['entity_name'];
        $entity_id = $entity['entity_id'];

        if (!$from_val) {
            // Non ho selezionato nessun valore
            die();
        }

        // Bisogna verificare innanzitutto se il campo to fa riferimento ad una tabella esterna
        $field_to = $this->db->get_where('fields', array('fields_name' => $field_name_to, 'fields_ref' => $entity_name))->row_array();
        if (empty($field_to) || empty($field_to['fields_source'])) {
            die();
        }

        // Voglio il field della stessa entità del to puntato dalla source di quest'ultimo
        $field_from = $this->db->get_where('fields', array('fields_name' => $field_to['fields_source'], 'fields_entity_id' => $field_to['fields_entity_id']))->row_array();

        // Il from deve avere un ref - questo ref punta ad una tabella A, la stessa a cui deve puntare il field dell'entità da cui devo prendere i dati
        if ($entity['entity_type'] == ENTITY_TYPE_RELATION) {
            $relatingEntity = $this->crmentity->getEntity($field_to['fields_entity_id']);
            $relation = $this->db->get_where('relations', ['relations_name' => $entity['entity_name'], 'relations_table_1' => $relatingEntity['entity_name']])->row();

            // Devo sostituire i campi
            // $entity_id
            // Altrimenti lui prova a pescarmi i campi della tabella pivot
            $relatedEntityFrom = $this->crmentity->getEntity($field_from['fields_ref']);

            if ($relatedEntityFrom['entity_type'] == ENTITY_TYPE_RELATION) {
                $relation_sub = $this->db->get_where('relations', ['relations_name' => $field_from['fields_ref']])->row_array();
                $relatedEntityFromSub = $this->crmentity->getEntity($relation_sub['relations_table_2']);
                $fields_entity = $this->crmentity->getEntity($relation->relations_table_2);
                $field_filter = $this->db->get_where('fields', [
                    'fields_ref' => $relatedEntityFromSub['entity_name'],
                    'fields_entity_id' => $fields_entity['entity_id'],
                ])->row_array();
                $entity_name = $relation->relations_table_2;
                $entity_id = $fields_entity['entity_id'];
            } else {
                $field_to_ref = $field_to['fields_ref'];
                //Check if ref field to is also a relation
                $relation_to_sub = $this->db->get_where('relations', ['relations_name' => $field_to_ref])->row_array();
                if ($relation_to_sub) {
                    $entity_relation_to = $this->crmentity->getEntity($relation_to_sub['relations_table_2']);
                    $entity_name = $relation_to_sub['relations_table_2'];

                    $entity_id = $entity_relation_to['entity_id'];
                    $field_filter = $this->db->get_where('fields', array('fields_entity_id' => $entity_id, 'fields_ref' => $field_from['fields_ref']))->row_array();
                } else {
                    $entity_name = $relatedEntityFrom['entity_name'];
                    $entity_id = $relatedEntityFrom['entity_id'];
                    $field_filter = $this->db->get_where('fields', array('fields_entity_id' => $entity_id, 'fields_ref' => $field_from['fields_ref']))->row_array();
                }
            }

            $field_name_filter = $field_filter['fields_name'];
        } else {
            $fields_filters = $this->db->order_by('fields_ref_auto_left_join', 'DESC')->get_where('fields', array('fields_entity_id' => $entity_id, 'fields_ref' => $field_from['fields_ref']))->result_array();
            //$field_name_filter = $field_filter['fields_name'];
        }

        $where_referer = [];
        if (!empty($fields_filters)) {
            $where_referer_multiple = [];
            foreach ($fields_filters as $field_filter) {
                $field_name_filter = $field_filter['fields_name'];
                if (is_array($from_val)) {
                    $where_referer_multiple[] = "{$field_name_filter} IN ('" . implode("','", $from_val) . "')";
                } else {
                    $where_referer_multiple[] = "{$field_name_filter} = '{$from_val}'";
                }

            }
            $where_referer[] = '(' . implode(' OR ', $where_referer_multiple) . ')';
        } else {
            if (is_array($from_val)) {
                $where_referer[] = "{$field_name_filter} IN ('" . implode("','", $from_val) . "')";
            } else {
                $where_referer[] = "{$field_name_filter} = '{$from_val}'";
            }
        }
        if (!empty($field_to['fields_select_where'])) {
            $where_referer[] = $this->datab->replace_superglobal_data(trim($field_to['fields_select_where']));
        }

        $preview = $this->datab->get_entity_preview_by_name($entity_name, implode(' AND ', $where_referer));
        $return_data_keep_sort = [];
        foreach ($preview as $id => $preview_label) {
            $return_data_keep_sort[] = ['id' => $id, 'value' => $preview_label];
        }
        echo json_encode($return_data_keep_sort);
    }

    public function langInfo()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $cur = $this->datab->getLanguage();
        $all = $this->datab->getAllLanguages();

        echo json_encode([
            'current' => $cur,
            'languages' => $all,
        ]);
    }

    /**
     * Ritorna un json con l'id dell'ultimo record
     */
    public function getLastRecord()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        try {
            $entity = $this->crmentity->getEntity($this->input->get('entity'));
        } catch (Exception $ex) {
            set_status_header(400);
            echo json_encode([
                'status' => 1,
                'error' => $ex->getMessage(),
            ]);
            die();
        }

        $idField = $entity['entity_name'] . '_id';
        $result = $this->db->order_by($idField, 'desc')->limit(1)
            ->get($entity['entity_name'])->row_array();

        $id = $preview = null;

        if (isset($result[$idField])) {
            $id = (int) $result[$idField];
            $previews = $this->crmentity->getEntityPreview($entity['entity_id'], sprintf('%s = %d', $idField, $id));
            if (isset($previews[$id])) {
                // Potrebbe succedere che non venga trovata per qualche motivo [?]
                $preview = $previews[$id];
            }
        }

        echo json_encode([
            'status' => 0,
            'data' => ['id' => $id, 'preview' => $preview],
        ]);
    }

    /**
     * Ritorna un json con l'id dell'ultimo record
     */
    public function getJsonRecord($entity, $id, $depth = 2)
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        try {
            $entity = $this->crmentity->getEntity($entity);
        } catch (Exception $ex) {
            set_status_header(400);
            echo json_encode([
                'status' => 1,
                'error' => $ex->getMessage(),
            ]);
            die();
        }

        $record = $this->apilib->view($entity['entity_name'], $id, $depth);

        echo json_encode([
            'status' => 0,
            'data' => $record,
        ]);
    }

    public function get_log_api_item($id = null)
    {
        $out = array();

        if ($id) {
            $out = $this->db->get_where('log_api', array('log_api_id' => $id))->row_array();

            if (@unserialize($out['log_api_params'])) {
                $params = @unserialize($out['log_api_params']);
                $post = @unserialize($out['log_api_post']);
                $get = @unserialize($out['log_api_get']);
                $files = @unserialize($out['log_api_files']);
                $output = @unserialize($out['log_api_output']);
            } else {
                $params = json_decode($out['log_api_params']);
                $post = json_decode($out['log_api_post']);
                $get = json_decode($out['log_api_get']);
                $files = json_decode($out['log_api_files']);
                $output = json_decode($out['log_api_output']);
            }

            $out['log_api_params'] = $params ? print_r($params, true) : '';
            $out['log_api_post'] = $post ? print_r($post, true) : '';
            $out['log_api_get'] = $get ? print_r($get, true) : '';
            $out['log_api_files'] = $files ? print_r($files, true) : '';
            $out['log_api_output'] = $output ? print_r($output, true) : '';
        }

        echo json_encode($out);
    }

    public function image_from_base64()
    {
        $post = $this->input->post();

        if (empty($post) || empty($post['base64'])) {
            die(json_encode(['status' => 0, 'txt' => t('No base64 given')]));
        }

        $image = base64_decode($post['base64']);

        if ($data = getimagesizefromstring($image)) {
            $ext = mime2ext($data['mime']);

            if (!file_exists(FCPATH . 'uploads/editor') || !is_dir(FCPATH . 'uploads/editor')) {
                @mkdir(FCPATH . 'uploads/editor');
            }

            $basepath = 'uploads/editor/' . md5($image) . '.' . $ext;
            $filepath = FCPATH . $basepath;

            file_put_contents($filepath, $image);

            die(json_encode(['status' => 0, 'txt' => base_url($basepath)]));
        } else {
            die(json_encode(['status' => 0, 'txt' => t('Error')]));
        }
    }

    public function search_autocomplete_groupby($requested_field)
    {
        $post = $this->security->xss_clean($this->input->post());

        if (empty($post) || empty($post['keyword'])) {
            // die(e_json(['status' => 0, 'txt' => t('No data passed')]));
        }

        $keyword = trim(strip_tags($post['keyword']));
        $requested_field = trim(strip_tags($requested_field));

        $field_db = $this->db->join('entity', 'entity_id = fields_entity_id')->where('fields_name', $requested_field)->get('fields')->row_array();

        if (empty($field_db)) {
            die(e_json(['status' => 0, 'txt' => t('Field not found')]));
        }

        try {
            $results = $this->db->query("SELECT $requested_field AS result FROM {$field_db['entity_name']} WHERE $requested_field IS NOT NULL AND $requested_field <> '' AND $requested_field LIKE '%{$keyword}%' GROUP BY $requested_field ORDER BY $requested_field ASC")->result_array();

            e_json(['status' => 1, 'txt' => $results]);
        } catch (Exception $e) {
            die(e_json(['status' => 0, 'txt' => $e->getMessage()]));
        }
    }

    public function preview_pdf($base64_path)
    {
        $path = base64_decode($base64_path);

        $this->load->view('pages/iframe_pdf_preview', ['path' => $path]);
    }

    public function test_getData()
    {
        $menu = $this->db->get('menu')->result_array();
        $layouts = $this->db->get('layouts')->result_array();
        $layouts_boxes = $this->db->get('layouts_boxes')->result_array();

        $jsonData = array(
            'menuData' => $menu,
            'layoutData' => $layouts,
            'layoutBoxData' => $layouts_boxes
        );

        // Imposta le intestazioni HTTP per indicare che stai restituendo JSON
        header('Content-Type: application/json');

        // Restituisci i dati in formato JSON
        echo json_encode($jsonData);
    }
}