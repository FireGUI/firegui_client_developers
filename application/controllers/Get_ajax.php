<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Get_ajax extends MY_Controller
{

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

            //debug($this->db->last_query(),true);

            if ($result->num_rows() == 0) {
                show_error("Layout '$layout_id' non trovato!");
            } else {
                $layout_id = $result->row()->layouts_id;
            }
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


        if (!$this->datab->can_access_layout($layout_id)) {
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
            $dati['show_title'] = FALSE;
            $modal = TRUE;
            $pagina = $this->load->view("pages/layout", array('dati' => $dati, 'value_id' => $value_id, 'modal' => $modal), true);
        }

        $this->load->view("layout/modal_container", array(
            'size' => $modalSize,
            'title' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title'])),
            'subtitle' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_subtitle'])),
            'content' => $pagina,
            'footer' => NULL
        ));
    }

    /**
     * Render di un form in modale
     * @param int $form_id
     * @param int $value_id
     */
    public function modal_form($form_id, $value_id = null)
    {
        // Check if i have form id or identifier
        if (!is_int($form_id)) {
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

        if (!$this->datab->get_form($form_id)) {
            $this->load->view("box/errors/missing_form", ['form_id' => $form_id]);
            return;
        }

        $viewData = array(
            'size' => $modalSize,
            'value_id' => $value_id,
            'form' => $this->datab->get_form($form_id, $value_id),
            'data' => $this->input->post()
        );

        echo $this->datab->getHookContent('pre-form', $form_id, $value_id ?: null);
        $this->load->view('pages/layouts/forms/form_modal', $viewData);
        echo $this->datab->getHookContent('post-form', $form_id, $value_id ?: null);
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
            if ($id !== NULL) {
                $row = $this->db->get_where($table, array($table . '_id' => $id))->row_array();
                $result_json = array('id' => $row[$table . '_id'], 'name' => $row[$table . '_value']);
            } else {
                // Il value di una support table è sempre una stringa quindi posso fare tranquillamente l'ilike
                $where = implode(' AND ', array_filter([
                    "{$table}_value ILIKE '%{$search}%'",
                    $where_limit,
                    $where_referer
                ]));

                $result_array = $this->db->query("SELECT * FROM {$table} WHERE {$where} ORDER BY {$table}_value ILIKE '{$search}' DESC, {$table}_value LIMIT {$limit}")->result_array();
                $result_json = array();
                foreach ($result_array as $row) {
                    //Ho solo due campi per un record di support table: id e value
                    $result_json[] = array(
                        'id' => $row[$table . '_id'],
                        'name' => $row[$table . '_value']
                    );
                }
            }
        } else {
            if ($id !== NULL) {
                $row = $this->datab->get_entity_preview_by_name($table, "{$table}_id = {$id}");
                if (!empty($row)) {
                    reset($row);            // Puntatore array su prima posizione
                    $key = key($row);       // Ottengo la prima e unica chiave
                    $result_json = array('id' => $key, 'name' => $row[$key]);
                }
            } else {
                //Devo prendere tutti i campi [preview (edit 14/10/2014)] dell'entità per poter creare il where su cui effettuare la ricerca
                $fields = $this->db->get_where('fields', array('fields_entity_id' => $entity['entity_id'], 'fields_preview' => DB_BOOL_TRUE))->result_array();
                $where = $this->datab->search_like($search, $fields);
                if ($where && $where_limit) {
                    $where .= " AND ({$where_limit})";
                } elseif ($where_limit) {
                    $where = $where_limit;
                }

                if ($where_referer) {
                    $where = ($where ? "{$where} AND ({$where_referer})" : $where_referer);
                }

                // 17/11/2015 - Voglio i filtri su ricerca apilib. Quelli dei
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

        //20170530 - Questa cosa la faccio solo se non ho impostato un campo di ordinamento predefinito nell'entità
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
                        return -1;                      // $val1 minore
                    } elseif (strtolower($name2) === strtolower($search)) {
                        return 1;                       // $val2 minore
                    } else {
                        $firstOccurrence1 = stripos($name1, $search);
                        $firstOccurrence2 = stripos($name2, $search);

                        // Non so perché ma non esiste la stringa cercata nel valore 1/2
                        if ($firstOccurrence1 === false) {
                            return 1;                   // $val2 minore
                        } elseif ($firstOccurrence2 === false) {
                            return -1;                  // $val1 minore
                        } elseif ($firstOccurrence1 < $firstOccurrence2) {
                            return -1;                  // $val1 minore
                        } elseif ($firstOccurrence2 > $firstOccurrence1) {
                            return 1;                   // $val2 minore
                        } else {
                            return 0;                   // $val1 === $val2
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
        echo json_encode($result_json);
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
        $id = str_replace("'", "''", $this->input->post('id') ? $this->input->post('id') : NULL);

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
                            'name' => $row[$field_name_search]
                        );
                    }
                }
            }
        }

        header('Content-Type: application/json');
        echo json_encode($result_json);
    }

    public function get_datatable_ajax($grid_id, $valueID = null)
    {

        if ($this->auth->guest()) {
            echo (json_encode(array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'sEcho' => null, 'aaData' => [])));
        } else {

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

            $preview_fields = $this->db->get_where('fields', array('fields_entity_id' => $grid['grids']['grids_entity_id'], 'fields_preview' => DB_BOOL_TRUE))->result_array();

            $where = $this->datab->search_like($search, array_merge($grid['grids_fields'], $preview_fields));

            if (preg_match('/(\()+(\))+/', $where)) {
                $where = '';
            }



            //Matteo: fix da cui sopra per prendere il default order
            //if ($order_col !== null && isset($grid['grids_fields'][$order_col]['fields_name'])) {
            if ($order_col !== null && $order_col !== false) {

                if ($has_bulk) {
                    $order_col -= 1;
                }

                if (isset($grid['grids_fields'][$order_col]['fields_name'])) {
                    //Se il campo è multilingua, forzo l'ordinamento per chiave lingua corrente
                    if ($grid['grids_fields'][$order_col]['fields_multilingual'] == DB_BOOL_TRUE) {
                        $order_by = "{$grid['grids_fields'][$order_col]['fields_name']}->>'{$this->datab->getLanguage()['id']}' {$order_dir}";
                    } elseif ($grid['grids_fields'][$order_col]['fields_type'] == 'JSON') {
                        $order_by = "{$grid['grids_fields'][$order_col]['fields_name']}::TEXT {$order_dir}";
                    } else {
                        $order_by = "{$grid['grids_fields'][$order_col]['fields_name']} {$order_dir}";
                    }
                } else {
                    //Se entro qui, verifico se il campo passato per l'ordinamento non sia per caso un eval cachable...
                    if ($grid['grids_fields'][$order_col]['grids_fields_eval_cache_type'] == 'query_equivalent') {
                        $order_by = "{$grid['grids_fields'][$order_col]['grids_fields_eval_cache_data']} {$order_dir}";
                    } else {
                        $order_by = null;
                    }
                }
            } else {
                $order_by = null;
            }

            //20191112 - MP - Added where_append in get ajax
            if ($where_append = $this->input->get('where_append')) {
                if ($where) {
                    $where .= ' AND ' . $where_append;
                } else {
                    $where = $where_append;
                }
            }

            $grid_data = $this->datab->get_grid_data($grid, $valueID, $where, (is_numeric($limit) && $limit > 0) ? $limit : NULL, $offset, $order_by);



            $out_array = array();
            foreach ($grid_data as $dato) {

                $tr = array();
                if ($has_bulk) {
                    $tr[] = '<input type="checkbox" class="js_bulk_check" value="' . $dato[$grid['grids']['entity_name'] . "_id"] . '" />';
                }
                foreach ($grid['grids_fields'] as $field) {

                    //debug($this->datab->build_grid_cell($field, $dato),true);

                    /* $tr[] = $this->load->view('box/grid/td', array('field'=>$field, 'dato'=>$dato), TRUE); */
                    $tr[] = $this->datab->build_grid_cell($field, $dato);
                }

                // Controlla se ho delle action da stampare in fondo
                if ($grid['grids']['grids_layout'] == 'datatable_ajax_inline') {
                    $tr[] = $this->load->view('box/grid/inline_edit', array('id' => $dato[$grid['grids']['entity_name'] . "_id"]), TRUE);
                    $tr[] = $this->load->view('box/grid/inline_delete', array('id' => $dato[$grid['grids']['entity_name'] . "_id"]), TRUE);
                } elseif ($grid['grids']['grids_layout'] == 'datatable_ajax_inline_form') {
                    $tr[] = $this->load->view('box/grid/inline_form_actions', array('id' => $dato[$grid['grids']['entity_name'] . "_id"]), TRUE);
                } elseif (grid_has_action($grid['grids'])) {
                    $tr[] = $this->load->view('box/grid/actions', array(
                        'links' => $grid['grids']['links'],
                        'id' => $dato[$grid['grids']['entity_name'] . "_id"],
                        'row_data' => $dato,
                        'grid' => $grid['grids'],
                    ), TRUE);
                }

                $out_array[] = $tr;
            }

            $totalRecords = $this->datab->get_grid_data($grid, $valueID, null, null, 0, null, true);
            $totalDisplayRecord = $this->datab->get_grid_data($grid, $valueID, $where, null, 0, null, true);




            echo json_encode(array(
                'iTotalRecords' => $totalRecords,
                'iTotalDisplayRecords' => $totalDisplayRecord,
                'sEcho' => $s_echo,
                'aaData' => $out_array
            ));
        }
    }

    public function get_map_markers($map_id, $value_id = NULL)
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


        $latlng_field = NULL;
        foreach ($data['maps_fields'] as $field) {
            $fields[$field['fields_name']] = $field;
            if ($field['maps_fields_type'] == 'latlng') {
                $latlng_field = $field['fields_name'];
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
        if ($this->db->dbdriver == 'postgre') {
            if ($latlng_field !== NULL && $bounds && !$isSearchMode) {

                $ne_lat = $bounds['ne_lat'];
                $ne_lng = $bounds['ne_lng'];
                $sw_lat = $bounds['sw_lat'];
                $sw_lng = $bounds['sw_lng'];

                if ($ne_lng < 180 && $ne_lat < 90 && $sw_lng > -180 && $sw_lat > -90) {
                    $where[] = "ST_Intersects(ST_GeographyFromText('POLYGON(({$ne_lng} {$ne_lat},{$ne_lng} {$sw_lat},{$sw_lng} {$sw_lat},{$sw_lng} {$ne_lat},{$ne_lng} {$ne_lat}))'), {$latlng_field})";
                }
            }
        } else {
            if ($latlng_field !== NULL && $bounds && !$isSearchMode) {

                $ne_lat = $bounds['ne_lat'];
                $ne_lng = $bounds['ne_lng'];
                $sw_lat = $bounds['sw_lat'];
                $sw_lng = $bounds['sw_lng'];

                if ($ne_lng < 180 && $ne_lat < 90 && $sw_lng > -180 && $sw_lat > -90) {
                    //TODO...
                    //$where[] = "ST_Intersects(ST_GeographyFromText('POLYGON(({$ne_lng} {$ne_lat},{$ne_lng} {$sw_lat},{$sw_lng} {$sw_lat},{$sw_lng} {$ne_lat},{$ne_lng} {$ne_lat}))'), {$latlng_field})";
                }
            }
        }

        if ($data['maps']['maps_where']) {
            $where[] = $this->datab->replace_superglobal_data(str_replace('{value_id}', $value_id, $data['maps']['maps_where']));
        }

        $order_by = (trim($data['maps']['maps_order_by'])) ? $data['maps']['maps_order_by'] : NULL;
        //$data_entity = $this->datab->get_data_entity($data['maps']['maps_entity_id'], 0, implode(' AND ', array_filter($where)), NULL, NULL, $order_by);
        $data_entity = $this->datab->getDataEntity($data['maps']['maps_entity_id'], implode(' AND ', array_filter($where)), NULL, NULL, $order_by, 1);


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
            } else {
                $this->db->select("{$data['maps']['entity_name']}_id as id, substring_index ( $latlng_field,';',1 )  AS lat, substring_index ( $latlng_field,';',-1 )  AS lon")
                    ->from($data['maps']['entity_name'])->where_in($data['maps']['entity_name'] . '_id', $data_ids);
            }


            // Indicizzo i risultati per id
            foreach ($this->db->get()->result_array() as $result) {
                $geography[$result['id']] = array('lat' => $result['lat'], 'lon' => $result['lon']);
            }

            //debug($geography, true);


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
                //debug($mark, true);
                if (!empty($mark['latlng']) && isset($geography[$marker[$data['maps']['entity_name'] . "_id"]])) {
                    $mark['lat'] = $geography[$marker[$data['maps']['entity_name'] . "_id"]]['lat'];
                    $mark['lon'] = $geography[$marker[$data['maps']['entity_name'] . "_id"]]['lon'];
                    $mark['link'] = ($link ? $link . '/' . $mark['id'] : '');
                    unset($mark['latlng']); // Questa chiave non serve più...
                    array_push($markers, $mark);
                }
            }

            // eseguo eventuale post_process di tipo map rendering load marker
            $markers = $this->datab->run_post_process($data['maps']['maps_entity_id'], 'marker_load', $markers);
        }

        //header("Cache-Control: no-cache, must-revalidate");
        header('Content-Type: application/json');
        echo json_encode($markers);
    }

    public function get_calendar_events($calendar_id, $value_id = NULL)
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
                        $start_field = $field['fields_name'];
                        break;
                    case 'end':
                        $end_field = $field['fields_name'];
                        break;
                }
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

        //debug($generatedWhere);

        //$data_entity = $this->datab->getDataEntity($data['calendars']['calendars_entity_id'], $generatedWhere, null, null, null, 1);
        /* $data_entity = $this->datab->get_data_entity($data['calendars']['calendars_entity_id'], 0, $generatedWhere); */
        //debug($data, true);
        $data_entity = $this->apilib->search($data['calendars']['entity_name'], $generatedWhere, null, null, null, null, 3);


        $previews = array();
        foreach ($data['calendars_fields'] as $field) {
            if ($field['fields_ref']) {
                $ids = array_filter(array_unique(array_map(function ($dato) use ($field) {
                    return $dato[$field['fields_name']];
                }, $data_entity)));

                if (empty($ids)) {
                    $previews[$field['fields_ref']] = array();
                } else {
                    //                    debug($field);
                    //                    debug($ids);
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
                //debug($ev['filter']);
                if (!in_array($ev['filter'], $filter)) {
                    //die('Escludo ');
                    continue;
                } else {
                    //die('NON escludo');
                }
            }

            // Get the id
            if (!isset($ev['id']) || !$ev['id']) {
                $ev['id'] = $event[$data['calendars']['entity_name'] . "_id"];
            }

            // Date start & date end & all day parameters
            //            $ev['start'] = date('Y/m/d H:i', strtotime($ev['start']));
            //            $ev['end'] = date('Y/m/d H:i', strtotime($ev['end']));
            // Store timezone
            $ev['start'] = date('c', strtotime($ev['start']));
            $ev['end'] = date('c', strtotime($ev['end']));

            if (empty($ev['all_day'])) {
                $arr_start = explode('T', $ev['start']);
                $arr_end = explode('T', $ev['end']);
                //Fix per efficiente che aveva un campo timestamp ma draw html type date, quindi salvava sempre e comunque 00:00:00 nell'ora fine.
                //$ev['allDay'] = ($arr_start[0] == $arr_end[0] && stripos($arr_start[1], '00:00:00') !== false && stripos($arr_end[1], '23:59:59') !== false);
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

        echo json_encode($events);
    }

    public function dropdown_notification_list()
    {
        // Se non sono loggato allora semplicemente uccido la richiesta
        if ($this->auth->guest()) {
            set_status_header(401); // Unauthorized
            die('Non sei loggato nel sistema');
        }

        $notifications = $this->datab->get_notifications(30, 0);

        //Check client version. If old add a notification on top
        if ($version = checkClientVersion()) {
            $notifications = array_merge([[
                'notifications_type' => NOTIFICATION_TYPE_SYSTEM,
                'notifications_id' => null,
                'notifications_user_id' => null,
                'notifications_message' => "[System] New client version ('" . VERSION . "' &lt; '{$version}') available!<br />Update now by click here.",
                'notifications_read' => DB_BOOL_FALSE,
                'notifications_date_creation' => date('Y-m-d h:i:s'),
                'notifications_link' => base_url('firegui/updateClient/1'),
                'href' => base_url('firegui/updateClient/1'),
                'label' => [
                    'class' => 'label-info',
                    'icon' => 'fas fa-globe-americas',
                ],
                'datespan' => date('d M')
            ]], $notifications);
        }

        echo json_encode(array(
            'view' => $this->load->view('box/notification_dropdown_item', array('notifications' => $notifications), true),
            'count' => count($unread = array_filter($notifications, function ($n) {
                return $n['notifications_read'] === DB_BOOL_FALSE;
            })),
            'errors' => count(array_filter($unread, function ($n) {
                return $n['notifications_type'] == 0;
            }))
        ));
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
            $dati['limits'] = array_merge(array(NULL), $this->db->get()->result_array());
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
                $return[] = array('id' => DB_BOOL_TRUE, 'name' => 'Si');
            }

            if (!$id || in_array(DB_BOOL_FALSE, $ids)) {
                $return[] = array('id' => DB_BOOL_FALSE, 'name' => 'No');
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
            $relatedEntity = $this->crmentity->getEntity($relation->relations_table_2);
            $entity_name = $relatedEntity['entity_name'];
            $entity_id = $relatedEntity['entity_id'];
        }
        $field_filter = $this->db->get_where('fields', array('fields_entity_id' => $entity_id, 'fields_ref' => $field_from['fields_ref']))->row_array();
        $field_name_filter = $field_filter['fields_name'];

        $where_referer = [];
        $where_referer[] = "{$field_name_filter} = '{$from_val}'";
        if (!empty($field_to['fields_select_where'])) {
            $where_referer[] = $this->datab->replace_superglobal_data(trim($field_to['fields_select_where']));
        }


        $preview = $this->datab->get_entity_preview_by_name($entity_name, implode(' AND ', $where_referer));
        echo json_encode($preview);
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
            'languages' => $all
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
                'error' => $ex->getMessage()
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
            'data' => ['id' => $id, 'preview' => $preview]
        ]);
    }

    /**
     * Ritorna un json con l'id dell'ultimo record 
     */
    public function getJsonRecord($entity, $id)
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
                'error' => $ex->getMessage()
            ]);
            die();
        }

        $record = $this->apilib->view($entity['entity_name'], $id);

        echo json_encode([
            'status' => 0,
            'data' => $record
        ]);
    }
}
