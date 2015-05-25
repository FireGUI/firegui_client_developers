<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Get_ajax extends CI_Controller {

    var $template = array();

    function __construct() {
        parent :: __construct();
    }

    public function index() {
        exit();
    }
    
    
    public function layout_modal($layout_id, $value_id=null) {
        if( ! $layout_id || ! $this->datab->can_access_layout($layout_id)) {
            show_404();
        }
        
        $dati = $this->datab->build_layout($layout_id, $value_id);
        if(is_null($dati)) {
            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
        } else {
            $dati['current_page'] = "layout_{$layout_id}";
            $dati['show_title'] = FALSE;
            $modal = TRUE;
            $pagina = $this->load->view("pages/layout", array('dati' => $dati,'value_id' => $value_id, 'modal' => $modal), true);
        }
        
        $this->load->view("layout/modal_container", array(
            'title' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title'])),
            'subtitle' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_subtitle'])),
            'content' => $pagina,
            'footer' => NULL
        ));
    }
    
    
    public function select_ajax_search() {
        
        $search = str_replace("'", "''", $this->input->post('q'));
        $limit = $this->input->post('limit');
        $table = $this->input->post('table');
        $id = ($this->input->post('id')?: null);
        $referer = ($this->input->post('referer')?: null);
        
        /* Devo distinguere i due casi: support table e relazione */
        $entity = $this->datab->get_entity_by_name($table);
        
        // Non ho l'entity id quindi l'entità non esiste
        if(empty($entity['entity_id'])) {
            echo json_encode(array());
            return;
        }
        
        /** @todo    se $entity è di tipo ENTITY_TYPE_RELATION devo cercare in qualche modo di prendere l'entità relazionata */
        

        /**
         * Applico limiti permessi
         */
        $user_id = $this->auth->get(LOGIN_ENTITY.'_id');
        $where_limit = '';
        
        if ($user_id) {
            $operators = unserialize(OPERATORS);
            $field_limit = $this->db->query("SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = ? AND fields_entity_id = ?", [$user_id, $entity['entity_id']])->row_array();
            if( ! empty($field_limit)) {
                $field = $field_limit['fields_name'];
                $op = $field_limit['limits_operator'];
                $value = $field_limit['limits_value'];

                if(array_key_exists($op, $operators)) {
                    $sql_op = $operators[$op]['sql'];

                    switch ($op) {
                        case 'in':
                            $value = "('".implode("','", explode(',', $value))."')";
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
                $where_referer = trim($fReferer->fields_select_where);
            }
        }
        
        
        if($entity['entity_type'] == ENTITY_TYPE_SUPPORT_TABLE) {
            if($id !== NULL) {
                $row = $this->db->get_where($table, array($table.'_id'=>$id))->row_array();
                $result_json = array('id' => $row[$table.'_id'], 'name'  => $row[$table.'_value']);
            } else {
                // Il value di una support table è sempre una stringa quindi posso fare tranquillamente l'ilike
                $where = implode(' AND ', array_filter([
                    "{$table}_value ILIKE '%{$search}%'",
                    $where_limit,
                    $where_referer
                ]));
                
                $result_array = $this->db->query("SELECT * FROM {$table} WHERE {$where} ORDER BY {$table}_value ILIKE '{$search}' DESC, {$table}_value LIMIT {$limit}")->result_array();
                $result_json = array();
                foreach($result_array as $row) {
                    //Ho solo due campi per un record di support table: id e value
                    $result_json[] = array(
                        'id' => $row[$table.'_id'],
                        'name'  => $row[$table.'_value']
                    );
                }
            }
        } else {
            if($id !== NULL) {
                $row = $this->datab->get_entity_preview_by_name($table, "{$table}_id = {$id}");
                if(!empty($row)) {
                    reset($row);            // Puntatore array su prima posizione
                    $key = key($row);       // Ottengo la prima e unica chiave
                    $result_json = array('id' => $key, 'name'  => $row[$key]);
                }
            } else {
                //Devo prendere tutti i campi [preview (edit 14/10/2014)] dell'entità per poter creare il where su cui effettuare la ricerca
                $fields = $this->db->get_where('fields', array('fields_entity_id'=>$entity['entity_id'], 'fields_preview' => 't'))->result_array();
                $where = $this->datab->search_like($search, $fields);
                if($where && $where_limit) {
                    $where .= " AND ({$where_limit})";
                } elseif($where_limit) {
                    $where = $where_limit;
                }
                
                if ($where_referer) {
                    $where = ($where? "{$where} AND ({$where_referer})": $where_referer);
                }
                
                $result_array = $this->datab->get_entity_preview_by_name($table, $where, $limit);
                $result_json = array();
                if(!empty($result_array)) {
                    foreach($result_array as $id=>$name) {
                        $result_json[] = array('id' => $id, 'name'  => $name);
                    }
                }
            }
        }
        
        // Riordina per importanza i risultati - solo se ho una lista di
        // risultati e non uno singolo
        if(is_array($result_json) && !array_key_exists('id', $result_json)) {
            usort($result_json, function($val1, $val2) use($search) {

                // Ordine importanza:
                //  - Stringa === al search
                //  - Search compare prima
                $name1 = $val1['name'];
                $name2 = $val2['name'];
                if(strtolower($name1) === strtolower($search)) {
                    return -1;                      // $val1 minore
                } elseif(strtolower($name2) === strtolower($search)) {
                    return 1;                       // $val2 minore
                } else {
                    $firstOccurrence1 = stripos($name1, $search);
                    $firstOccurrence2 = stripos($name2, $search);

                    // Non so perché ma non esiste la stringa cercata nel valore 1/2
                    if($firstOccurrence1 === false) {
                        return 1;                   // $val2 minore
                    } elseif($firstOccurrence2 === false) {
                        return -1;                  // $val1 minore
                    } elseif($firstOccurrence1 < $firstOccurrence2) {
                        return -1;                  // $val1 minore
                    } elseif($firstOccurrence2 > $firstOccurrence1) {
                        return 1;                   // $val2 minore
                    } else {
                        return 0;                   // $val1 === $val2
                    }
                }
            });
        }
        
        // Ritorna il json alla vista
        echo json_encode($result_json);
    }
    
    
    
    
    public function get_distinct_values() {
        
        $search = str_replace("'", "''", $this->input->post('q'));
        $limit = $this->input->post('limit');
        $field_id = $this->input->post('field');
        $id = str_replace("'", "''", $this->input->post('id')? $this->input->post('id'): NULL);
        
        // Prepara un result vuoto
        $result_json = array();
        
        
        /* Devo distinguere i due casi: support table e relazione */
        $field = $this->db->from('fields')->join('entity', 'fields_entity_id = entity_id', 'left')->where('fields_id', $field_id)->get()->row_array();
         
        if(!empty($field)) {
            /**
             * Applico limiti permessi
             */
            $user_id = $this->auth->get(LOGIN_ENTITY.'_id');
            $operators = unserialize(OPERATORS);
            $field_limit = $this->db->query("SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = {$user_id} AND fields_id = {$field_id}")->row_array();
            $where_limit = '';
            if( ! empty($field_limit)) {
                $field = $field_limit['fields_name'];
                $op = $field_limit['limits_operator'];
                $value = $field_limit['limits_value'];

                if(array_key_exists($op, $operators)) {
                    $sql_op = $operators[$op]['sql'];

                    switch ($op) {
                        case 'in':
                            $value = "('".implode("','", explode(',', $value))."')";
                            break;

                        case 'like':
                            $value = "'%{$value}%'";
                            break;
                    }

                    $where_limit = "{$field} {$sql_op} {$value}";
                }
            }
            
            // Se ho un ref sicuramente nel campo avrò un id... non mi serve a molto, quindi le ricerche le faccio sulla tabella referenziata
            if($field['fields_ref']) {
                $table = $field['fields_ref'];
                $field_name_select = $field['fields_name'];
                $ref_entity = $this->datab->get_entity_by_name($table);
                
                // Sto prendendo da una support table - faccio ricerca su value - se invece prendessi una relazione non è chiaro su cosa fare la ricerca - TODO
                if($ref_entity['entity_type'] == ENTITY_TYPE_SUPPORT_TABLE) {
                    $field_name_search = $table.'_value';
                    
                    // Se ho passato l'id vuol dire che sto caricando quel record esatto
                    if($id) {
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
                if($id) {
                    
                    $where = "{$field_name_search} = '{$id}'";
                    if($where_limit) {
                        $where .= " AND {$where_limit}";
                    }
                    
                    $result_array = $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$table} WHERE {$where} LIMIT {$limit}")->row_array();
                } else {
                    /**
                     * Qua devo fare un fix - prima facevo
                     * $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$table} WHERE {$field_name_search} ILIKE '%{$search}%' AND {$where_limit} LIMIT {$limit}")->result_array();
                     * ma ora non posso più perché se il $field_name_search fosse un intero non posso usare l'ilike
                     */
                    switch(strtoupper($field['fields_type'])) {
                        case 'VARCHAR': case 'TEXT':
                            $where_search = "{$field_name_search} ILIKE '%{$search}%'";
                            break;
                        case 'INT': case 'FLOAT':
                            // Nel caso di INT o FLOAT voglio fare una uguaglianza numerica classica
                            if(is_numeric($search)) {
                                $where_search = "{$field_name_search} = '{$search}'";
                            }
                    }
                    
                    if(empty($where_search)) {
                        // FIERA DELL'ACCROCCHIO: in questo caso prendo tutti i possibili valori... che schifo di cosa.. se poi non c'è nessuno dei due where ne faccio uno fittizio..
                        $full_where = ($where_limit? "{$where_search} AND {$where_limit}": "1=1");
                    } else {
                        $full_where = ($where_limit? "{$where_search} AND {$where_limit}": $where_search);
                    }
                    
                    $result_array = $this->db->query("SELECT DISTINCT {$field_name_select} FROM {$table} WHERE {$full_where} ORDER BY {$field_name_select} LIMIT {$limit}")->result_array();
                }
            }
            
            if(isset($result_array)) {
                if($id) {
                    $result_json = array( 'id' => $result_array[$field_name_select], 'name'  => $result_array[$field_name_search] );
                } else {
                    // Ho fatto una ricerca quindi ritorno un array bidimenisonale
                    foreach($result_array as $row) {
                        //Due campi:
                        $result_json[] = array(
                            'id' => $row[$field_name_select],
                            'name'  => $row[$field_name_search]
                        );
                    }
                }
            }
            
            
        }
        
        echo json_encode($result_json);
        

        /*
        if($entity['entity_type'] == ENTITY_TYPE_SUPPORT_TABLE) {
            if($id !== NULL) {
                $row = $this->db->get_where($table, array($table.'_id'=>$id))->row_array();
                $result_json = array('id' => $row[$table.'_id'], 'name'  => $row[$table.'_value']);
            } else {
                $result_array = $this->db->query("SELECT * FROM {$table} WHERE {$table}_value ILIKE '%{$search}%' AND {$where_limit} LIMIT {$limit}")->result_array();
                $result_json = array();
                foreach($result_array as $row) {
                    //Due campi:
                    $result_json[] = array(
                        'id' => $row[$table.'_id'],
                        'name'  => $row[$table.'_value']
                    );
                }
            }
        } else {
            if($id !== NULL) {
                $row = $this->datab->get_entity_preview_by_name($table, "{$table}_id = {$id}");
                if(!empty($row)) {
                    reset($row);            // Puntatore array su prima posizione
                    $key = key($row);       // Ottengo la prima e unica chiave
                    $result_json = array('id' => $key, 'name'  => $row[$key]);
                }
            } else {
                //Devo prendere tutti i campi dell'entità
                $fields = $this->db->get_where('fields', array('fields_entity_id'=>$entity['entity_id']))->result_array();
                $where = $this->datab->search_like($search, $fields);
                $where .= ($where? " AND ({$where_limit})": $where_limit);
                
                $result_array = $this->datab->get_entity_preview_by_name($table, $where, $limit);
                $result_json = array();
                if(!empty($result_array)) {
                    foreach($result_array as $id=>$name) {
                        $result_json[] = array(
                            'id' => $id,
                            'name'  => $name
                        );
                    }
                }
            }
        }
            */
    }
    
    
    
    public function get_datatable_ajax($grid_id, $valueID=null) {
        //header("Cache-Control: no-cache, must-revalidate");
        
        if ($this->auth->guest()) {
            echo json_encode(array('iTotalRecords' => 0, 'iTotalDisplayRecords' => 0, 'sEcho' => null, 'aaData' => []));
            return;
        }

        $post_data = $this->input->post();

        /**
         * Info da datatable
         */
        $limit = isset($post_data['iDisplayLength']) ? $post_data['iDisplayLength']: 10;
        $offset = isset($post_data['iDisplayStart']) ? $post_data['iDisplayStart']: 0;
        $search = isset($post_data['sSearch']) ? str_replace("'", "''", $post_data['sSearch']): NULL;
        $s_echo = isset($post_data['sEcho']) ? $post_data['sEcho']: NULL;
        $order_col = isset($post_data['iSortCol_0']) ? $post_data['iSortCol_0']: 0;
        $order_dir = isset($post_data['sSortDir_0']) ? $post_data['sSortDir_0']: 'ASC';

        // Prendo i dati della grid
        $grid = $this->datab->get_grid($grid_id);
        
        $where = $this->datab->search_like($search, $grid['grids_fields']);
        
        if(preg_match('/(\()+(\))+/', $where)) {
            $where = '';
        }
        
        $order_by = (isset($grid['grids_fields'][$order_col]['fields_name']) && empty($grid['grids']['grids_order_by']))? "{$grid['grids_fields'][$order_col]['fields_name']} {$order_dir}": NULL;
        $grid_data = $this->datab->get_grid_data($grid, $valueID, $where, (is_numeric($limit) && $limit>0)? $limit: NULL, $offset, $order_by);

        
        $out_array = array();
        foreach ($grid_data['data'] as $dato) {
            $tr = array();
            foreach ($grid['grids_fields'] as $field) {
                /*$tr[] = $this->load->view('box/grid/td', array('field'=>$field, 'dato'=>$dato), TRUE);*/
                $tr[] = $this->datab->build_grid_cell($field, $dato);
            }
            
            // Controlla se ho delle action da stampare in fondo
            if($grid['grids']['grids_layout'] == 'datatable_ajax_inline') {
                $tr[] = $this->load->view('box/grid/inline_edit', array('id' => $dato[$grid_data['entity']['entity_name']."_id"]), TRUE);
                $tr[] = $this->load->view('box/grid/inline_delete', array('id' => $dato[$grid_data['entity']['entity_name']."_id"]), TRUE);
            } elseif(grid_has_action($grid['grids'])) {
                $tr[] = $this->load->view('box/grid/actions', array('links'=> $grid['grids']['links'], 'id' => $dato[$grid_data['entity']['entity_name']."_id"], 'row_data' => $dato), TRUE);
            }
            
            $out_array[] = $tr;
        }
        
        //$this->db->count_all($grid['grids']['entity_name']);
        $totalDisplayRecordData = $this->datab->get_grid_data($grid, $valueID, $where, NULL, 0, NULL, TRUE);
        $totalRecordsData = $this->datab->get_grid_data($grid, $valueID, NULL, NULL, 0, NULL, TRUE);
        
        $totalRecords = $totalRecordsData['data'];
        $totalDisplayRecord = $totalDisplayRecordData['data'];
        
        echo json_encode(array(
            'iTotalRecords' => $totalRecords,
            'iTotalDisplayRecords' => $totalDisplayRecord,
            'sEcho' => $s_echo,
            'aaData' => $out_array
        ));
    }
    
    public function get_map_markers($map_id, $value_id = NULL) {
        $data = $this->datab->get_map($map_id);
        
        $where = array();
        
        
        $fields = array();
        $latlng_field = NULL;
        foreach ($data['maps_fields'] as $field) {
            $fields[$field['fields_name']] = $field;
            if($field['maps_fields_type'] == 'latlng') {
                $latlng_field = $field['fields_name'];
            }
        }
        
        
        $post_where = (array) $this->input->post('where');
        if( ! empty($post_where)) {
            foreach($post_where as $post_cond) {
                $field = $post_cond['name'];
                $value = $post_cond['value'];
                if($value) {
                    switch ($fields[$field]['fields_type']) {
                        case 'VARCHAR': case 'TEXT':
                            $where[] = "{$field} ILIKE '%{$value}%'";
                            break;

                        default :
                            $where[] = "{$field} = '{$value}'";
                            break;

                    }
                }
            }
        }
        
        
        // Geography data
        $bounds = $this->input->post('bounds');
        if ($latlng_field !== NULL && $bounds && empty($where)) {

            $ne_lat = $bounds['ne_lat'];
            $ne_lng = $bounds['ne_lng'];
            $sw_lat = $bounds['sw_lat'];
            $sw_lng = $bounds['sw_lng'];
            
            if ($ne_lng < 180 && $ne_lat < 90 && $sw_lng > -180 && $sw_lat > -90) {
                $where[] = "ST_Intersects(ST_GeographyFromText('POLYGON(({$ne_lng} {$ne_lat},{$ne_lng} {$sw_lat},{$sw_lng} {$sw_lat},{$sw_lng} {$ne_lat},{$ne_lng} {$ne_lat}))'), {$latlng_field})";
            }
        }
        
        if($data['maps']['maps_where']) {
            $where[] = str_replace('{value_id}', $value_id, $data['maps']['maps_where']);
        }
        
        $order_by = (trim($data['maps']['maps_order_by']))? $data['maps']['maps_order_by']: NULL;
        $data_entity = $this->datab->get_data_entity($data['maps']['maps_entity_id'], 0, implode(' AND ', array_filter($where)), NULL, NULL, $order_by);
        

        $markers = array();
        if( ! empty($data_entity['data'])) {
            // Cerco un link se c'è qui per non fare una query ogni ciclo
            $link = $this->datab->get_detail_layout_link($data['maps']['maps_entity_id']);

            // Recupero tutti i lat e lon di ogni marker che mi serve per evitare di fare le query dopo
            $data_ids = array();
            foreach ($data_entity['data'] as $marker) {
                $data_ids[] = $marker[$data_entity['entity']['entity_name'].'_id'];
            }

            $geography = array();
            $this->db->select("{$data_entity['entity']['entity_name']}_id as id, ST_Y({$latlng_field}::geometry) AS lat, ST_X({$latlng_field}::geometry) AS lon")
                    ->from($data_entity['entity']['entity_name'])->where_in($data_entity['entity']['entity_name'].'_id', $data_ids);

            // Indicizzo i risultati per id
            foreach ($this->db->get()->result_array() as $result) {
                $geography[$result['id']] =  array( 'lat' => $result['lat'], 'lon' => $result['lon'] );
            }
            
            
            
            
            foreach ($data_entity['data'] as $marker) {

                $mark = array();
                foreach ($data['maps_fields'] as $field) {
                    if($field['maps_fields_type'] == 'description') {
                        // Posso avere più campi description
                        $mark['description'][] = $marker[$field['fields_name']];
                    } else {
                        $mark[$field['maps_fields_type']] = $marker[$field['fields_name']];
                    }
                }


                if(array_key_exists('description', $mark) and is_array($mark['description'])) {
                    $mark['description'] = implode('<br/>', $mark['description']);
                }



                // Get the id
                if (empty($mark['id'])) {
                    $mark['id'] = $marker[$data_entity['entity']['entity_name'] . "_id"];
                }
                
                
                if(empty($mark['title'])) {
                    $mark['title'] = '#'.$mark['id'];
                }
                
                
                
                

                // Elaboro le coordinate
                if(!empty($mark['latlng']) && isset($geography[$marker[$data_entity['entity']['entity_name'] . "_id"]])) {
                    $mark['lat'] = $geography[$marker[$data_entity['entity']['entity_name'] . "_id"]]['lat'];
                    $mark['lon'] = $geography[$marker[$data_entity['entity']['entity_name'] . "_id"]]['lon'];
                    $mark['link'] = ($link? $link.'/'.$mark['id']: '');
                    array_push($markers, $mark);
                }    
            }

            // eseguo eventuale post_process di tipo map rendering load marker
            $markers = $this->datab->run_post_process($data['maps']['maps_entity_id'], 'marker_load', $markers);
        }
        
        echo json_encode($markers);
    }
    
    

    public function get_calendar_events($calendar_id, $value_id=NULL) {
        $data = $this->datab->get_calendar($calendar_id);
        $where = array();
        $filter = array_filter((array) $this->input->post('filters'));
        
        
        /*if($data['calendars']['calendars_where']) {
            $where[] = $this->datab->replace_superglobal_data(str_replace('{value_id}', $value_id, $data['calendars']['calendars_where']));
        }*/
        
        
        // Prendi eventi sse compresi tra start ed end
        $ts_start = $this->input->post('start');
        $ts_end = $this->input->post('end');
        if(is_numeric($ts_start) && is_numeric($ts_end) && $ts_start && $ts_end) {
            $start = date('Y-m-d H:i:s', $ts_start);
            $end = date('Y-m-d H:i:s', $ts_end);
            
            // Prendi data di partenza
            $start_field = null;
            foreach ($data['calendars_fields'] as $field) {
                if($field['calendars_fields_type'] == 'start') {
                    $start_field = $field['fields_name'];
                }
            }
            
            if($start_field !== NULL) {
                $where[] = "{$start_field} BETWEEN '{$start}' AND '{$end}'";
            }
        }
        
        //$data_entity = $this->datab->get_data_entity($data['calendars']['calendars_entity_id'], 0, implode(' AND ', $where));
        $generatedWhere = $this->datab->generate_where('calendars', $calendar_id, $value_id, $where);
        $data_entity = $this->datab->get_data_entity($data['calendars']['calendars_entity_id'], 0, $generatedWhere);
        
        
        $previews = array();
        foreach ($data['calendars_fields'] as $field) {
            if($field['fields_ref']) {
                $ids = array_filter(array_unique(array_map(function($dato) use($field) { return $dato[$field['fields_name']]; }, $data_entity['data'])));
                if(empty($ids)) {
                    $previews[$field['fields_ref']] = array();
                } else {
                    $previews[$field['fields_ref']] = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id IN (".implode(',', $ids).")");
                }
            }
        }
        

        $events = array();
        foreach ($data_entity['data'] as $event) {
            $ev = array();
            foreach ($data['calendars_fields'] as $field) {
                if($field['calendars_fields_type']!=='title' || (!empty($event[$field['fields_name']]) && empty($ev['title']))) {
                    if($field['fields_ref'] && isset($previews[$field['fields_ref']][$event[$field['fields_name']]]) && in_array($field['calendars_fields_type'], array('title', 'description'))) {
                        $ev[$field['calendars_fields_type']] = $previews[$field['fields_ref']][$event[$field['fields_name']]];
                    } else {
                        $ev[$field['calendars_fields_type']] = $event[$field['fields_name']];
                    }
                }
            }
            
            
            // Non ha senso controllare il filtro se manca uno di questi due campi
            if(isset($ev['filter']) && isset($data['calendars']['calendars_filter_entity_id'])) {
                // Se settati il campo su cui filtrare e il valore del filtro, allora applico il filtro
                if(!in_array($ev['filter'], $filter)) {
                    continue;
                }
            }

            // Get the id
            if (!isset($ev['id']) || !$ev['id']) {
                $ev['id'] = $event[$data_entity['entity']['entity_name'] . "_id"];
            }

            // Date start & date end & all day parameters
            $ev['start'] = date('Y/m/d H:i', strtotime($ev['start']));
            $ev['end'] = date('Y/m/d H:i', strtotime($ev['end']));
            
            if(empty($ev['all_day'])) {
                $arr_start = explode(' ', $ev['start']);
                $arr_end = explode(' ', $ev['end']);
                $ev['allDay'] = ($arr_start[0] == $arr_end[0] && $arr_start[1] == '00:00' && $arr_end[1] == '23:59');
            } else {
                $ev['allDay'] = ($ev['all_day']==='t');
            }
            
            if(empty($ev['title'])) {
                $ev['title'] = 'No title';
            }
            
            if($ev['start'] && $ev['end']) {
                array_push($events, $ev);
            }
        }

        echo json_encode($events);
    }

    public function modal_form($form_id = '', $value_id = '') {
        
        $form = $this->datab->get_form($form_id);
        if ($value_id) {
            $form['forms']['edit_data'] = $this->datab->get_data_entity($form['forms']['entity_id'], 1, "{$form['forms']['entity_name']}_id = '$value_id'");
            $form['forms']['action_url'] = base_url("db_ajax/save_form/{$form['forms']['forms_id']}/true/$value_id");
        } else {
            $form['forms']['action_url'] = base_url("db_ajax/save_form/{$form['forms']['forms_id']}");
        }
        
        $viewData = array(
            'form' => $form,
            'value_id' => $value_id,
            'data' => $this->input->post()
        );
        
        echo $this->datab->getHookContent('pre-form', $form_id, $value_id?:null);
        $this->load->view('pages/layouts/forms/form_modal', $viewData);
        echo $this->datab->getHookContent('post-form', $form_id, $value_id?:null);
    }
    
    
    
    
    
    public function dropdown_notification_list() {
        $notifications = $this->datab->get_notifications(30, 0);
        
        echo json_encode(array(
            'view' => $this->load->view('box/notification_dropdown_item', array('notifications'=>$notifications), true),
            'count' => count($unread = array_filter($notifications, function($n) { return $n['notifications_read'] === 'f'; })),
            'errors' => count(array_filter($unread, function($n) { return $n['notifications_type']==0; }))
        ));
    }
    
    
    
    public function permission_table() {
        
        $identifier = $this->input->post('identifier');
        
        if(!$identifier) {
            return;
        }
        
        /** Considero i permessi come permessi utente se passo un numero come identificatore (lo user_id) */
        $dati['is_user_permissions'] = is_numeric($identifier) && $identifier > 0;
        $dati['is_create_group'] = is_numeric($identifier) && $identifier < 0;
        
        
        $dati['entities'] = $this->db->where_in('entity_type',array(ENTITY_TYPE_DEFAULT, ENTITY_TYPE_SUPPORT_TABLE))->get('entity')->result_array();
        $dati['modules'] = $this->db->get('modules')->result_array();
        
        
        if($dati['is_user_permissions']) {
            $dati['permissions'] = $this->db->get_where('permissions', array('permissions_user_id' => $identifier))->row_array();
        } else {
            $dati['permissions'] = $this->db->get_where('permissions', array('permissions_group' => $identifier))->row_array();
        }
        

        $dati['permissions_entities'] = array();
        $dati['permissions_modules'] = array();
        if(isset($dati['permissions']['permissions_id'])) {
            $permissions_entities = $this->db->get_where('permissions_entities', array('permissions_entities_permissions_id'=>$dati['permissions']['permissions_id']))->result_array();
            $permissions_modules = $this->db->get_where('permissions_modules', array('permissions_modules_permissions_id'=>$dati['permissions']['permissions_id']))->result_array();
            foreach($permissions_entities as $ent) {
                $dati['permissions_entities'][$ent['permissions_entities_entity_id']] = $ent['permissions_entities_value'];
            }

            foreach($permissions_modules as $mod) {
                $dati['permissions_modules'][$mod['permissions_modules_module_name']] = $mod['permissions_modules_value'];
            }
        }

        if($dati['is_user_permissions']) {
            $this->db->select('limits.*, fields.fields_entity_id AS entity_id')->from('limits')
                    ->join('fields', 'limits_fields_id = fields_id', 'left')
                    ->where('limits_user_id', $identifier);
            $dati['limits'] = array_merge(array(NULL), $this->db->get()->result_array());
        }
        $dati['groups'] = array_key_map($this->db->where('permissions_group IS NOT NULL AND permissions_user_id IS NULL')->get('permissions')->result_array(), 'permissions_group');



        $this->load->view('box/permissions/table', array('dati'=>$dati));
    }
    
    
    
    public function entity_fields() {
        $entity_id = $this->input->post('entity_id');
        $entity = $this->datab->get_entity($entity_id);
        $return = array();
        if( ! empty($entity)) {
            switch ($entity['entity_type']) {
                case ENTITY_TYPE_SUPPORT_TABLE:
                    $return = $this->db->from('fields')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left')
                    ->join('entity', 'entity.entity_id = fields.fields_entity_id', 'left')->where('fields_entity_id', $entity_id)->get()->result_array();

                    foreach ($return as $k => $row) {
                        if(empty($row['fields_draw_label'])) {
                            $row['fields_draw_label'] = ucwords(str_replace($entity['entity_name'].'_', '', $row['fields_name']));
                        }
                        $return[$k] = $row;
                    }
                    break;

                default :
                    $return = $this->datab->get_visible_fields($entity_id);
            }

        }
        echo json_encode($return);
    }
    
    
    
    public function get_field($id) {
        $this->db->from('fields')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left')
                ->join('entity', 'entity.entity_id = fields.fields_entity_id', 'left')
                ->where('fields_id', $id);
        
        echo json_encode($this->db->get()->row_array());
    }
    
    
    
    
    
    
    public function search_field_values() {
        // Leggi post e recupera field
        $field_id = $this->input->post('field_id');
        $search = $this->input->post('q');
        $id = $this->input->post('id');
        $return = array();
        
        $this->db->from('fields')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left')
                ->join('entity', 'entity.entity_id = fields.fields_entity_id', 'left')
                ->where('fields_id', $field_id);
        $field = $this->db->get()->row_array();
        if(empty($field)) {
            die();
        }
        
        if($field['fields_type'] === 'BOOL') {
            $ids = explode(',', trim($id));
            if(!$id || in_array('t', $ids)) {
                $return[] = array('id' => 't', 'name' => 'Si');
            }
            
            if(!$id || in_array('f', $ids)) {
                $return[] = array('id' => 'f', 'name' => 'No');
            }
        } else {
            // Se ho un id lo devo preparare ad utilizzarlo nell'in
            if($id) {
                $id = "'".implode("', '", explode(',', trim($id)))."'";
            }



            /**
             * 3 casi:
             * - Field semplice: faccio una ricerca nei valori del field richiesto
             * - Support table: cerco il value e ritorno gli id
             * - Relazione: cerco nei diversi field della relazione e ritorno gli id (ragionarci dopo)
             */
            $return = array();
            if( ! $field['fields_ref']) {
                // fields_ref vuoto indica un field semplice (caso 1)
                if($id) {
                    $results = $this->db->query("SELECT * FROM {$field['entity_name']} WHERE {$field['fields_name']} IN ({$id})")->result_array();
                } else {
                    $where = ($search? "WHERE {$field['fields_name']} ILIKE '%{$search}%'": '');
                    $results = $this->db->query("SELECT * FROM {$field['entity_name']} {$where} LIMIT 100")->result_array();
                }
                foreach($results as $row) {
                    if(!empty($row[$field['fields_name']])) {
                        $return[] = array( 'id' => $row[$field['fields_name']], 'name'  => $row[$field['fields_name']] );
                    }
                }
            } else {
                // Sono nel caso di una relazione oppure una support table (casi 2 e 3)
                $ref_entity = $this->datab->get_entity_by_name($field['fields_ref']);

                switch($ref_entity['entity_type']) {
                    case ENTITY_TYPE_SUPPORT_TABLE:
                        // Support table (caso 2)
                        if($id) {
                            $results = $this->db->query("SELECT * FROM {$ref_entity['entity_name']} WHERE {$ref_entity['entity_name']}_id IN ($id)")->result_array();
                        } else {
                            $where = ($search? "WHERE {$ref_entity['entity_name']}_value ILIKE '%{$search}%'": '');
                            $results = $this->db->query("SELECT * FROM {$ref_entity['entity_name']} {$where} LIMIT 100")->result_array();
                        }

                        foreach($results as $row) {
                            if(!empty($row[$field['fields_name']])) {
                                $return[] = array( 'id' => $row[$ref_entity['entity_name'].'_id'], 'name'  => $row[$ref_entity['entity_name'].'_value'] );
                            }
                        }
                        break;

                    case ENTITY_TYPE_RELATION:
                        break;
                    
                    default:
                        // Referenza su entità standard
                        if($id) {
                            $where = "{$ref_entity['entity_name']}_id IN ($id)";
                        } elseif ($search) {
                            $where = $this->datab->search_like($search, $this->datab->get_entity_fields($ref_entity['entity_id']));
                        } else {
                            $where = '';
                        }

                        $previewResult = $this->datab->get_entity_preview_by_name($ref_entity['entity_name'], $where);
                        foreach($previewResult as $id => $preview) {
                            $return[] = array( 'id' => $id, 'name'  => $preview );
                        }
                }
            }
        }
        
        
        echo json_encode($return);
    }
    
    
    
    public function filter_multiselect_data() {
        /**
         * Field to è il nome del campo da popolare - deve trovarsi o in una relazione o in una support table
         * From val è il valore su cui fitrare il to
         * Entity name è l'entità alla quale il to e il from appartengono
         */
        $field_name_to = rtrim($this->input->post('field_name_to'), '[]');
        $from_val = $this->input->post('field_from_val');
        
        $entity_name = $this->input->post('field_ref');
        $entity = $this->datab->get_entity_by_name($entity_name);
        $entity_id = $entity['entity_id'];
        
        
        if(!$from_val) {
            // Non ho selezionato nessun valore
            die();
        }


        // Bisogna verificare innanzitutto se il campo to fa riferimento ad una tabella esterna
        $field_to = $this->db->get_where('fields', array('fields_name' => $field_name_to, 'fields_ref' => $entity_name))->row_array();
        if (empty($field_to) || empty($field_to['fields_source'])) {
            die();
        }
        
        // Voglio il field della stessa entità del to puntato dalla source di quest'ultimo
        $field_from = $this->db->get_where('fields', array('fields_name'=>$field_to['fields_source'], 'fields_entity_id'=>$field_to['fields_entity_id']))->row_array();
        
        // Il from deve avere un ref - questo ref punta ad una tabella A, la stessa a cui deve puntare il field dell'entità da cui devo prendere i dati
        $field_filter = $this->db->get_where('fields', array('fields_entity_id'=>$entity_id, 'fields_ref' => $field_from['fields_ref']))->row_array();
        $field_name_filter = $field_filter['fields_name'];
        
        $preview = $this->datab->get_entity_preview_by_name($entity_name, "{$field_name_filter} = '{$from_val}'");
        echo json_encode($preview);
    }
    
    
        

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
