<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * 
 *  SI SUPPONE CHE NEL REF DI UN FIELDS CI SARà IL NOME TABELLA E NON L'ID O IL NOME DEL FIELDS CHE DEVE JOINARE... PERCHè? PERCHè SI è STABILITO, CHE OGNI TABELLA DI SUPPORTO O ENTITà AVRà PER FORZA
 * UN CAMPO NOMETABELLA_ID E QUINDI IN AUTOMATICO, INSERENDO IL NOME TABELLA VERRà PRESO QUEL CAMPO CONCATENANDO _ID... NON è UN ACCROCCHIO ANCHE SE LO PUò SEMBRARE...
 * TUTTO QUESTO FORSE PER EVITARE DI DOVE INSERIRE NEI FIELDS ANCHE LA TABELLA O ENTITà A CUI FANNO RIFERIMENTO ED IL CAMPO DA JOINARE...
 * 
 */

class Datab extends CI_Model {

    var $template = array();
    private $_entity_detail_layouts = array();      // Serve per fare una sorta di cache altrimenti viene effettuata la stessa query un centinaio di volte in "get_detail_layout_link"
    private $_entities = array();                   // Idem

    function __construct() {
        parent :: __construct();
    }

    /**
     * Metodi per entità e campi
     */
    public function get_entity($entity_id) {
        if (is_numeric($entity_id) && $entity_id > 0) {
            if (empty($this->_entities[$entity_id])) {
                $entity = $this->db->query("SELECT * FROM entity WHERE entity_id = '$entity_id'")->row_array();
                $this->_entities[$entity_id] = $entity;
            }

            return $this->_entities[$entity_id];
        } else {
            return array();
        }
    }

    public function get_entity_by_name($entity_name) {
        
        if(!$entity_name) {
            die("Nome entità richiesto");
        }
        
        foreach ($this->_entities as $entity) {
            if ($entity['entity_name'] == $entity_name) {
                return $entity;
            }
        }

        $entity = $this->db->query("SELECT * FROM entity WHERE entity_name = '$entity_name'")->row_array();
        if (empty($entity)) {
            debug("L'entità {$entity_name} non esiste");
        } else {
            $this->_entities[$entity['entity_id']] = $entity;
        }
        return $entity;
    }

    public function get_data_entity($entity_id, $only_visible_fields = 0, $where = NULL, $limit = NULL, $offset = 0, $order_by = NULL, $count = FALSE) {
        $dati['entity'] = $this->get_entity($entity_id);
        $dati['relations'] = $this->db->query("SELECT relations_name FROM relations WHERE relations_table_1 = '{$dati['entity']['entity_name']}'")->result_array();
        $dati['visible_fields'] = $this->db->query("
            SELECT *
            FROM fields
                LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                LEFT JOIN entity ON entity.entity_id = fields.fields_entity_id
            WHERE fields_entity_id = '{$entity_id}' AND fields_draw_display_none = 'f'")->result_array();

        if (count($dati['visible_fields']) < 1) {
            debug("Entità {$dati['entity']['entity_name']} (id = {$entity_id}) senza campi");
            return false;
        }
        $visible_fields = $this->fields_implode($dati['visible_fields']);

        // Estraggo i campi visibili anche di eventuali tabelle da joinare
        foreach ($dati['visible_fields'] as $k => $campo) {
            if ($campo['fields_ref']) {
                $entity = $this->get_entity_by_name($campo['fields_ref']);
                if (empty($entity)) {
                    // L'entità non esiste più quindi svuoto il fields_ref
                    $dati['visible_fields'][$k]['fields_ref'] = '';
                } else {
                    $visible_fields_supports = $this->get_visible_fields($entity['entity_id']);
                    $dati['visible_fields'] = array_merge($dati['visible_fields'], $visible_fields_supports);
                    if ($fields = $this->fields_implode($visible_fields_supports)) {
                        $visible_fields = $visible_fields . "," . $fields;
                    }
                }
            }
        }

        // Costruisco la query
        $this->db->select($dati['entity']['entity_name'] . "_id" . ',' . $visible_fields); // Gli forzo anche l'id 
        
        // Mi assicuro che il where stringa contenga altre cose oltre che parentesi, spazi bianchi, ecc...
        if (is_string($where) && trim($where, " \t\n\r\0\x0B()")) {
            // Attenzione!! Se il primo e l'ultimo carattere sono parentesi tonde,
            // allora non serve wrappeggiare il where stringhiforme perché è già
            // wrappeggiato in codesta maniera
            $this->db->where(($where[0] === '(' && $where[strlen($where) - 1] === ')') ? "({$where})" : $where);
        } elseif (is_array($where) && count($where) > 0) {
            // Attenzione!! Devo distinguere da where con chiave numerica a
            // quelli con chiave a stringa: dei primi ignoro la chiave, mentre
            // dei secondi faccio un where(key, value);
            array_walk($where, function($value, $key) {
                if (is_numeric($key)) {
                    $this->db->where($value);
                } elseif (is_string($key)) {
                    $this->db->where($key, $value);
                }
            });
        }
        /*if ($where) {
            $this->db->where("({$where})");
        }*/

        $this->db->from($dati['entity']['entity_name']);
        $joined_tables = array($dati['entity']['entity_name']);
        $to_join_later = array();

        // Aggiungo in automatico i join SUPPONENDO che il campo da joinare, nella tabella sarà nometabella_id ********
        $permission_entities = array($entity_id);   // Lista delle entità su cui devo applicare i limiti
        $post_process_data_entity = array();
        foreach ($dati['visible_fields'] as $key => $campo) {
            // I campi che hanno un ref li joino solo se non sono in realtà legati a delle relazioni Se invece sono delle relazioni faccio select dei dati
            if (($campo['fields_ref'] && !in_array(array('relations_name' => $campo['fields_ref']), $dati['relations']))) {
                
                // Dal 12/01/2015 abbiamo fatto una modifica che permette le
                // cosiddette fake relationships che consistono nell'inserire
                // una serie di id separati da virgola in un singolo campo
                // varchar/text, quindi se il campo non è un INT non lo joino
                if ($campo['fields_type'] !== 'INT') {
                    continue;
                }
                
                if(in_array($campo['fields_ref'], $joined_tables)) {
                    
                    // Prendo dopo solo quelle entità che sono referenziate
                    // direttamente da un campo dell'entità principale
                    // (quella del from) - eventuali altri join verranno presi in
                    // una chiamata ricorsiva
                    if($campo['fields_entity_id'] == $entity_id) {
                        $to_join_later[$campo['fields_name']] = $campo['fields_ref'];
                    }
                    continue;
                } else {
                    $jTable = $campo['fields_ref'];
                    $jCondition = "{$campo['fields_ref']}.{$campo["fields_ref"]}_id = {$campo['entity_name']}.{$campo['fields_name']}";
                    
                    // Applica la JOIN
                    $this->db->join($jTable, $jCondition, 'left');
                    $joined_tables[] = $jTable;
                    
                    // Devo fare il controllo dei limiti sui field ref
                    // L'entità joinata è in $campo[fields_ref] che è il nome dell'entità
                    // quindi devo prendere l'id
                    $ent = $this->get_entity_by_name($jTable);
                    $permission_entities[] = $ent['entity_id'];
                }
                
            } elseif (($campo['fields_ref'] && in_array(array('relations_name' => $campo['fields_ref']), $dati['relations']))) {
                $relation_to = $this->db->query("SELECT relations_table_2 FROM relations WHERE relations_name = '{$campo['fields_ref']}'")->row()->relations_table_2;
                $ent = $this->get_entity_by_name($relation_to);
                $post_process_data_entity[$key]['entity_id'] = $ent['entity_id'];
            }
        }


        /**
         * Applico limiti permessi
         */
        $user_id = (int) $this->auth->get(LOGIN_ENTITY . '_id');
        $operators = unserialize(OPERATORS);
        $field_limits = empty($permission_entities)? array(): $this->db->query("
                        SELECT *
                        FROM limits JOIN fields ON (limits_fields_id = fields_id)
                        WHERE limits_user_id = '{$user_id}' AND
                            fields_entity_id IN (".implode(',', array_unique($permission_entities)).") AND
                            limits_operator IN ('".implode("','", array_keys($operators))."')
                ")->result_array();

        foreach ($field_limits as $field_limit) {
            $field = $field_limit['fields_name'];
            $op = $field_limit['limits_operator'];
            $value = $field_limit['limits_value'];
            $sql_op = $operators[$op]['sql'];

            // Modifico i value in alcuni casi particolari
            switch ($op) {
                case 'in':
                    if( ! trim($value)) {
                        continue 2;
                    }
                    $value = "('" . implode("','", explode(',', $value)) . "')";
                    break;

                case 'like':
                    $value = "'%{$value}%'";
                    break;
            }

            // Costruisco il where - se non metto l'accettazione dei valori null
            // allora mi è impossibile prendere i valori nulli se viene
            // attivato questo where
            $this->db->where("({$field} IS NULL OR {$field} {$sql_op} {$value})");
        }


        if ($limit !== NULL) {
            $this->db->limit($limit);
        }
        if ($offset > 0) {
            $this->db->offset($offset);
        }
        if ($order_by !== NULL && !$count) {
            $this->db->order_by($order_by);
        }

        if ($count) {
            $dati['data'] = $this->db->count_all_results();
        } else {
            $dati['data'] = $this->db->get()->result_array();

            // Qui devo emulare un join, quindi so che per ogni query avrò un solo
            // risultato, perché sennò avrei usato le relazioni
            // (foreach successivo)
            if($dati['data']) {
                foreach($to_join_later as $main_field => $sub_entity_name) {
                    $sub_entity = $this->get_entity_by_name($sub_entity_name);
                    $main_field_values = implode(',', array_filter(array_map(function ($record) use($main_field) { return (int) $record[$main_field]; }, $dati['data'])));
                    if(!$main_field_values) {
                        continue;
                    }
                    
                    // Ritrova i dati - jData sono i dati grezzi, mentre mergeable
                    // sono i dati pronti ad essere uniti ai dati principali
                    $jData = $this->get_data_entity($sub_entity['entity_id'], 1, "{$sub_entity_name}_id IN ({$main_field_values})");
                    $mergeable = array();
                    
                    foreach($jData['data'] as $record) {
                        // Rimappo ogni valore in modo da avere il main field
                        // anteposto al vero field. Quindi se ho
                        // messaggi con campo messaggi_utente che ha ref a
                        // utenti, ogni campo recuperato da questa join sarà
                        // rinominato in messaggi_utente_utenti_*
                        $mergeable[$record[$sub_entity_name.'_id']] = array_combine(array_map(function($key) use($main_field) { return $main_field.'_'.$key; }, array_keys($record)), array_values($record));
                    }
                    
                    foreach($dati['data'] as $k => $record) {
                        $id = $record[$main_field];
                        $dati['data'][$k] = array_merge($record, isset($mergeable[$id])? $mergeable[$id]: array());
                    }
                    
                }
            }


            foreach ($post_process_data_entity as $key => $entity) {
                $dati['visible_fields'][$key]['data'] = $this->get_data_entity($entity['entity_id']);
            }

        }
        
        return $dati;
    }
    
    
    
    
    public function get_visible_fields($entity_id = NULL) {

        if (!$entity_id) {
            return array();
        }
        
        $where = (is_numeric($entity_id)? "entity_id = '{$entity_id}'": "entity_name = '{$entity_id}'");
        return $this->db->query("
                        SELECT *
                        FROM fields
                            JOIN entity ON entity.entity_id = fields.fields_entity_id
                            LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                        WHERE {$where} AND (fields_visible = 't')
                    ")->result_array();
    }

    public function get_field($field_id) {
        if(is_numeric($field_id)) {
            return $this->db->query("SELECT * FROM fields WHERE fields_id = '{$field_id}'")->row_array();
        } else {
            return $this->get_field_by_name($field_id);
        }
    }

    public function get_field_by_name($field_name) {
        $slashed = addslashes($field_name);
        return $this->db->query("SELECT * FROM fields WHERE fields_name = '{$slashed}'")->row_array();
    }

    /**
     * Forms
     */
    public function get_default_fields_value($fields) {

        $value = null;
        switch ($fields['forms_fields_default_type']) {
            case 'session':
                // Mi aspetto una sintassi di questo tipo: {arr campo} oppure {campo}
                $str = str_replace(array("{", "}"), "", $fields['forms_fields_default_value']);
                $exp = explode(' ', $str);
                if (count($exp) > 1) {
                    $sess_arr = $this->session->userdata($exp[0]);

                    $value = $sess_arr[$exp[1]];
                } else {
                    $value = $this->session->userdata($exp[0]);
                }
                break;
            case 'static_value':
                $value = $fields['forms_fields_default_value'];
                break;
            case 'function':
                // Esplodo xkè potrebbero esserci dei valori
                $exp = explode(',', $fields['forms_fields_default_value']);
                $func = $exp[0];
                $var1 = (isset($exp[1])) ? $exp[1] : null;
                $var2 = (isset($exp[2])) ? $exp[2] : null;

                switch ($func) {
                    case '{now_date}':
                        $value = date("d/m/Y");
                        break;
                    case '{different_date}':
                        $timestamp = strtotime($var1 . ((trim($var1)==='+1')? " day": " days"));
                        $value = date('d/m/Y', $timestamp);
                        break;
                    case '{different_date_time}':
                        // Se l'argomento è della forma +10 allora appendi days alla fine
                        if (preg_match('/\A\+[0-9]+\z/', $var1)) {
                            $var1.=" days";
                        }
                        $value = date("d/m/Y H:m", strtotime($var1));
                        break;
                    case '{now_date_time}':
                        $value = date("d/m/Y") . " " . date('H:m');
                        break;
                    default:
                        debug("NON GESTITA DEFAULT TYPE FUNCTION");
                        break;
                }
                break;
            case 'variable':
                //TODO
                debug("NON GESTITA DEFAULT TYPE VARIABLE");
                break;
        }

        return $value;
    }

    public function get_default_form($entity_id) {
        if (!$entity_id)
            die('ERRORE: Entity ID mancante');


        $form_id = $this->db->query("SELECT * FROM forms WHERE forms_entity_id = '$entity_id' AND forms_default = 't' LIMIT 1")->row()->forms_id;
        return $form_id;
    }

    public function get_forms_from_entity($entity_id) {
        if (!$form_id)
            die('ERRORE: Entity ID mancante');

        $dati = array();
        $dati['forms'] = $this->db->query("SELECT * FROM forms WHERE entity_id = '$entity_id'")->result_array();
        return $dati;
    }

    public function get_form($form_id) {
        if (!$form_id)
            die('ERRORE: Form ID mancante');

        $dati = array();
        $dati['forms'] = $this->db->query("SELECT * FROM forms LEFT JOIN entity ON forms.forms_entity_id = entity.entity_id WHERE forms_id = '{$form_id}'")->row_array();

        $dati['forms_fields'] = $this->db->query("SELECT * FROM forms_fields LEFT JOIN forms ON forms_fields.forms_fields_forms_id = forms.forms_id
                                                                             LEFT JOIN fields ON fields.fields_id = forms_fields.forms_fields_fields_id 
                                                                             LEFT JOIN fields_draw ON forms_fields.forms_fields_fields_id = fields_draw.fields_draw_fields_id
                                                                             WHERE forms_id = '$form_id' AND fields_visible = 't' ORDER BY forms_fields_order ASC")->result_array();

        // Il ref è il nome della tabella/entità di supporto/da joinare quindi estraggo i valori da proporre
        $operators = unserialize(OPERATORS);
        foreach ($dati['forms_fields'] as $key => $field) {
            if (!$field['fields_ref']) {
                continue;
            }

            $entity = $this->get_entity_by_name($field['fields_ref']);
            if (!isset($entity['entity_name'])) {
                debug($field['fields_ref']);
                debug($entity);
                echo "Campo legato ad una relazione inesistente (" . $field['fields_ref'] . ") ";
                continue;
            }

            // Verifico se il ref si riferisce ad una eventuale relations oppure ad una tabella di supporto, in modo da gestirlo diversamente
            // Chiaramente x funzionare non ci devono essere 2 relazioni con lo stesso nome
            $relations = $this->db->query("SELECT * FROM relations WHERE relations_name = '{$entity['entity_name']}'")->row_array();
            if (count($relations) > 0) {
                $support_relation_table = $relations['relations_table_2'];
                $entity = $this->get_entity_by_name($relations['relations_table_2']);
                $field_support_id = $relations['relations_field_2'];
            } else {
                $support_relation_table = $field['fields_ref'];
                $field_support_id = $entity['entity_name'] . "_id";
            }

            // Estraggo i campi che si possono visualizzare per la tabella o entità di supporto....
            $visible_fields_supports = $this->db->query("SELECT * FROM fields LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                                                            LEFT JOIN entity ON entity.entity_id = fields.fields_entity_id
                                                            WHERE fields_entity_id = '{$entity['entity_id']}' AND fields_preview = 't'")->result_array();
            $support_fields = $this->fields_implode($visible_fields_supports);
            $select = $field_support_id . ($support_fields? ',' . $support_fields: '');

            // Applico limiti permessi
            $user_id = (int) $this->auth->get(LOGIN_ENTITY . '_id');
            $data_entity = $this->get_entity_by_name($support_relation_table);
            $field_limit = $this->db->query("SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = '{$user_id}' AND fields_entity_id = {$data_entity['entity_id']}")->row_array();
            $wheres = array();
            if (!empty($field_limit)) {
                $fieldLimitName = $field_limit['fields_name'];
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

                    $wheres[] = "{$fieldLimitName} {$sql_op} {$value}";
                }
            }

            $fieldWhere = trim($field['fields_select_where']);
            if ($fieldWhere) {
                $wheres[] = $this->replace_superglobal_data($fieldWhere);
            }

            $where = (empty($wheres)? '': 'WHERE ' . implode(' AND ', $wheres));
            $support_data = (($field['fields_draw_html_type'] == 'select_ajax')? array(): $this->db->query("SELECT {$select} FROM {$support_relation_table} {$where}")->result_array());
            $dati['forms_fields'][$key]['support_fields'] = $visible_fields_supports;
            $dati['forms_fields'][$key]['support_data'] = $support_data;

            // Fix x dichiarare quale sarà il campo id da utilizzare nel form
            $dati['forms_fields'][$key]['field_support_id'] = $field_support_id;
        }
        // Se il campo One record è impostato a TRUE mi prendo già i dati di quel form per popolarlo e renderlo quindi editable
        if ($dati['forms']['forms_one_record'] == 't') {
            $dati['forms']['edit_data'] = $this->get_data_entity($dati['forms']['forms_entity_id']);
        }

        // 
        return $dati;
    }

    public function get_edit_form($form_id, $record_id) {
        if (!$form_id)
            die('ERRORE: Form ID mancante');

        $form = $this->get_form($form_id);
    }

    /**
     * Grids
     */
    public function get_default_grid($entity_id) {
        if (!$entity_id)
            die('ERRORE: Entity ID mancante');

        $grid_id = $this->db->query("SELECT grids_id FROM grids WHERE grids_entity_id = '$entity_id' AND grids_default = 't'")->row()->grids_id;
        return $grid_id;
    }

    public function get_grids_from_entity($entity_id) {
        if (!$grid_id)
            die('ERRORE: Entity ID mancante');

        $dati = array();
        $dati['grids'] = $this->db->query("SELECT * FROM grids WHERE entity_id = '$entity_id'")->result_array();
        return $dati;
    }

    public function get_grid_data($grid, $value_id = null, $where = array(), $limit = NULL, $offset = 0, $order_by = NULL, $count = FALSE) {
        
        
        if(is_array($value_id)) {
            $additional_data = isset($value_id['additional_data'])? $value_id['additional_data']: array();
            $value_id = isset($value_id['value_id'])? $value_id['value_id']: null;
        } else {
            $additional_data = array();
        }
        
        


        $where = $this->generate_where("grids", $grid['grids']['grids_id'], $value_id, $where, $additional_data);


        /** Valuta order_by * */
        if (is_null($order_by) && !empty($grid['grids']['grids_order_by']) && !$count) {
            $order_by = $grid['grids']['grids_order_by'];
        }

        $data = $this->datab->get_data_entity($grid['grids']['grids_entity_id'], 0, $where, $limit, $offset, $order_by, $count);
        

        if (!empty($data['relations']) && !empty($data['data'])) {
            foreach ($data['relations'] as $relation) {
                if (isset($relation['relations_name'])) {

                    // Prendi i dati della relazione
                    $rel = $this->db->get_where('relations', array('relations_name' => $relation['relations_name']))->row_array();

                    // Se ho trovato dei dati allora posso provare a cercare le relazioni
                    if (isset($data['data'][0]) && is_array($data['data'][0]) && array_key_exists($rel['relations_field_1'], $data['data'][0])) {
                        $field = $rel['relations_field_1'];
                        $other = $rel['relations_field_2'];
                        $other_table = $rel['relations_table_2'];
                    } elseif (isset($data['data'][0]) && is_array($data['data'][0]) && array_key_exists($rel['relations_field_2'], $data['data'][0])) {
                        $field = $rel['relations_field_2'];
                        $other = $rel['relations_field_1'];
                        $other_table = $rel['relations_table_1'];
                    } else {
                        continue;
                    }

                    /**
                     * Il risultato dell'operazione sarà un array di valori - questo array sarà il valore del campo dell'entità che va a relazionarsi con l'altra tabella
                     * Ad esempio se una camera può avere più servizi voglio che tutti i servizi finiscano sul campo camere_servizi
                     * $field_name_for_relation_values avrà in questo caso il valore di camere_servizi
                     */
                    $field_name_for_relation_values = NULL;
                    foreach ($data['visible_fields'] as $visible_field) {
                        
                        if (in_array($visible_field['fields_ref'], array($rel['relations_table_1'] . '_' . $rel['relations_table_2'], $rel['relations_table_2'] . '_' . $rel['relations_table_1']))) {
                            // Modalità nuova - faccio un field ref sulla relazione
                            $field_name_for_relation_values = $visible_field['fields_name'];
                            break;
                        }
                        
                        if ($visible_field['fields_ref'] === $other_table) {
                            $field_name_for_relation_values = $visible_field['fields_name'];
                        }
                    }

                    
                    if (!is_null($field_name_for_relation_values)) {

                        // Prendo il gruppo di id della tabella e cerco tutti i valori nella relazione per quegli id. Poi con un foreach smisto il valore corretto per ogni dato
                        $ids = array_map(function($dato) use($field) {
                            return $dato[$field];
                        }, $data['data']);

                        // Le tuple della tabella pivot della relazione - sono già filtrate per gli id dell'entità della grid
                        $relation_data = $this->db->where_in($field, $ids)->get($relation['relations_name'])->result_array();

                        // Cicla i dati della tabella pivot e metti in $relation_data_by_ids i record suddivisi per id dell'entità della grid (per accederci dopo con meno foreach),
                        // mentre in $related_data metti tutti gli id dell'altra tabella nella relazione (nell'esempio di camere_servizi, metti gli id dei servizi).
                        $relation_data_by_ids = array();
                        $related_data = array();
                        foreach ($relation_data as $relation_dato) {
                            if (empty($relation_data_by_ids[$relation_dato[$field]])) {
                                $relation_data_by_ids[$relation_dato[$field]] = array();
                            }

                            $related_data[] = (int) $relation_dato[$other];
                            $relation_data_by_ids[$relation_dato[$field]][] = $relation_dato[$other];
                        }


                        // Prendo le preview dei record relazionati
                        if (!empty($related_data)) {
                            $related_data_preview = $this->get_entity_preview_by_name($other_table, "{$other} IN (" . implode(',', $related_data) . ")");

                            foreach ($data['data'] as $key => $dato) {
                                $data['data'][$key][$field_name_for_relation_values] = array();
                                if (isset($relation_data_by_ids[$dato[$field]])) {
                                    foreach ($relation_data_by_ids[$dato[$field]] as $related_value) {
                                        if (array_key_exists($related_value, $related_data_preview)) {
                                            $data['data'][$key][$field_name_for_relation_values][] = $related_data_preview[$related_value];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function get_grid($grid_id) {
        if (!$grid_id)
            die('ERRORE: grid ID mancante');

        $dati = array();
        $dati['grids'] = $this->db->query("SELECT * FROM grids LEFT JOIN entity ON entity.entity_id = grids.grids_entity_id WHERE grids_id = '$grid_id'")->row_array();

        // FIX: aggiorna tutte le grid con layout "table" a "simple_table"
        if ($dati['grids']['grids_layout'] === 'table') {
            $dati['grids']['grids_layout'] = 'simple_table';
            $this->db->update('grids', array('grids_layout' => 'simple_table'), array('grids_id' => $grid_id));
        }



        $dati['grids_fields'] = $this->db->query("
                    SELECT *
                    FROM grids_fields
                        LEFT JOIN grids ON grids.grids_id = grids_fields.grids_fields_grids_id
                        LEFT JOIN fields ON fields.fields_id = grids_fields.grids_fields_fields_id 
                        LEFT JOIN fields_draw ON grids_fields.grids_fields_fields_id = fields_draw.fields_draw_fields_id
                    WHERE grids_id = '$grid_id' AND fields_draw_display_none = 'f'
                    ORDER BY grids_fields_order ASC
                ")->result_array();
        // Ciclo ed estraggo eventuali campi di tabelle joinate FUNZIONA SOLO CON ENTITA PER ORA
        $dati['grids_support_fields'] = array();
        foreach ($dati['grids_fields'] as $key => $field) {
            if ($field['fields_ref']) {
                $entity = $this->get_entity_by_name($field['fields_ref']);
                $support_fields = $this->db->query("SELECT * FROM fields LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                                                WHERE fields_entity_id = '{$entity['entity_id']}' AND fields_preview = 't'")->result_array();
                $dati['grids_fields'][$key]['support_fields'] = $support_fields;
            }
        }

        $dati['grids']['links'] = array(
            'view' => ($dati['grids']['grids_view_layout'] ? base_url("main/layout/{$dati['grids']['grids_view_layout']}") : str_replace('{base_url}', base_url(), $dati['grids']['grids_view_link'])),
            'edit' => ($dati['grids']['grids_edit_layout'] ? base_url("main/layout/{$dati['grids']['grids_edit_layout']}") : str_replace('{base_url}', base_url(), $dati['grids']['grids_edit_link'])),
            'delete' => ($dati['grids']['grids_delete_link'] ? str_replace('{base_url}', base_url(), $dati['grids']['grids_delete_link']): base_url("db_ajax/generic_delete/{$dati['grids']['entity_name']}"))
        );

        if (!filter_var($dati['grids']['links']['delete'], FILTER_VALIDATE_URL)) {
            unset($dati['grids']['links']['delete']);
        }


        $can_write = $this->datab->can_write_entity($dati['grids']['entity_id']);
        if (!$can_write) {
            unset($dati['grids']['links']['edit'], $dati['grids']['links']['delete']);
        }

        // Infine aggiungo le custom actions - attenzione! non posso valutare i permessi sulle custom actions
        $dati['grids']['links']['custom'] = $this->db->order_by('grids_actions_order', 'ASC')->get_where('grids_actions', array('grids_actions_grids_id' => $grid_id))->result_array();
        foreach ($dati['grids']['links']['custom'] as $key => $custom_link) {
            $html = $custom_link['grids_actions_html'];
            $html = str_replace('{base_url}', base_url(), $html);
            $dati['grids']['links']['custom'][$key]['grids_actions_html'] = $html;

            $dati['grids']['links']['custom'][$key]['grids_actions_name'] = addslashes($custom_link['grids_actions_name']);
        }


        // Mi assicuro che ogni link esistente termini con '/' e valuto se è da aprire con modale
        foreach ($dati['grids']['links'] as $type => $link) {
            if ($link && is_string($link)) {
                $dati['grids']['links'][$type] = rtrim($link, '/') . '/';
                $dati['grids']['links'][$type . '_modal'] = (strpos($link, base_url('get_ajax/layout_modal')) === 0);
            }
        }
        
        // Carico il replace
        $dati['replaces'] = array();
        foreach($dati['grids_fields'] as $gridField) {
            if ($gridField['grids_fields_replace']) {
                $dati['replaces'][$gridField['grids_fields_replace']] = $gridField;
            }
        }

        //debug($dati['grids']['links'],1);
        return $dati;
    }

    
    
    /**
     * CHARTS 
     */
    public function get_charts_elements($charts_id) {
        $elements = $this->db->where('charts_elements_charts_id', $charts_id)->get('charts_elements')->result_array();
        return $elements;
    }

    public function get_entity_fields($entity_id) {
        return $this->db->where('fields_entity_id', $entity_id)->get('fields')->result_array();
    }

    public function get_chart_data($chart) {

        $all_data = array();
        $data = array();

        // Ciclo gli elementi qualora ne abbia + di uno
        foreach ($chart['elements'] as $element) {
            //debug($element);
            $entity = $this->get_entity($element['charts_elements_entity_id']);

            $group_by = $element['charts_elements_groupby'];


            // Gli costruisco il Where con il mega-metodo generico
            //$where = trim($element['charts_elements_where']);
            $where = $this->generate_where("charts_elements", $element['charts_elements_id']);

            if ($where) {
                $where = "WHERE {$where}";
            }

            // Mi costruisco eventuali join
            $join = ""; $alreadyJoined = array($entity['entity_name']);
            foreach ($this->get_entity_fields($element['charts_elements_entity_id']) as $_field) {
                if ($_field['fields_ref'] && !in_array($_field['fields_ref'], $alreadyJoined)) {
                    $entity_ref = $this->get_entity_by_name($_field['fields_ref']);
                    $join .= "LEFT JOIN {$_field['fields_ref']} ON ({$_field['fields_ref']}.{$_field['fields_ref']}_id = {$entity['entity_name']}.{$_field['fields_name']}) ";
                    $alreadyJoined[] = $_field['fields_ref'];
                }
            }


            $field = $this->get_field($element['charts_elements_fields_id']);
            // Se ha un ref joino

            /* if ($field['fields_ref']) {
              //$entity_ref = $this->get_entity_by_name($field['fields_ref']);
              //$join .= "LEFT JOIN {$field['fields_ref']} ON ({$field['fields_ref']}.{$field['fields_ref']}_id = {$entity['entity_name']}.{$field['fields_name']}) ";
              $as_x = "{$field['fields_ref']}.{$field['fields_ref']}_id AS x";
              //$group_by = $field['fields_ref'].".".$field['fields_ref']."_id,".$group_by;
              } else {
              //$join = '';
              $as_x = "{$entity['entity_name']}.{$field['fields_name']} AS x";
              //$group_by = "{$entity['entity_name']}.{$field['fields_name']},".$group_by;
              } */

            if ($group_by) {
                $query_group_by = str_replace('#', ',', $group_by);
                $gr_by = "GROUP BY " . $query_group_by;
            } else {
                $query_group_by = "";
                $gr_by = "";
            }

            $order = ($element['charts_elements_order']) ? "ORDER BY " . $element['charts_elements_order'] : '';
            $data = array();

            $field_function_parameter = ($element['charts_elements_function_parameter']) ? $element['charts_elements_function_parameter'] : $field['fields_name'];

            switch ($element['charts_elements_function']):
                case 'COUNT':
                    $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}(*) AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $gr_by $order")->result_array();
                    break;
                case null:
                    $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}{$field_function_parameter} AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $order")->result_array();
                    break;
                default:
                    $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}({$field_function_parameter}) AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $gr_by $order")->result_array();
                    break;
            endswitch;
//            if ($element['charts_elements_function']) {
//                if ($element['charts_elements_function'] == "COUNT") {
//                    $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}(*) AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $query_group_by $order")->result_array();
//                } else {
//                    $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}({$field['fields_name']}) AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $query_group_by $order")->result_array();
//                }
//            
//                }
            // Precalcolo tutte le x, perché ogni serie deve avere lo stesso numero di valori
            // e questo deve coincidere col numero di x
            $data['x'] = array_unique(array_map(function($row) {return $row['x'];}, $data['data']));
            
            if (!empty($data['x'])) {
                // Trova chi è il campo messo come x (il campo x è l'ultimo dopo la virgola-cancelletto nella stringa group by)
                //$group_by ="asdds.test, asds.ket, agente, asd.tk"
                $arr_group_by = explode('#', $group_by);
                $x_field_name = trim(array_pop($arr_group_by));

                $field_exploso = explode('.', $x_field_name);
                $field_name_exploso = trim(array_pop($field_exploso));
                
                
                // Ha senso valutare sta cosa se è una stringa alfanumerica
                $xfield = null;
                if(preg_match("/^[a-z0-9_\-]+$/i", $field_name_exploso)) {
                    if ($field_name_exploso === $field['fields_name']) {
                        $xfield = $field;
                    } else {
                        $xfield = $this->get_field_by_name($field_name_exploso);
                    }
                }


                // Se ho un ref devo ricalcolare tutte le etichette perché vorrebbe dire che il campo
                // contiene solo una lista di inutili id
                if (!empty($xfield['fields_ref'])) {
                    $preview = $this->get_entity_preview_by_name($xfield['fields_ref'], $xfield['fields_ref'] . "_id IN ('" . implode("','", array_filter($data['x'])) . "')");

                    // Sostituigli l'id nella x con la stringa di preview - nelle x
                    foreach ($data['x'] as $key => $xval) {
                        if (isset($preview[$xval])) {
                            $data['x'][$key] = $preview[$xval];
                        }
                    }

                    // e anche in data
                    foreach ($data['data'] as $key => $val) {
                        if (isset($preview[$val['x']])) {
                            $data['data'][$key]['x'] = $preview[$val['x']];
                        }
                    }
                }
            }


            // Monto l'array delle serie - di norma dovrei avere solo x e y, ma se voglio più serie
            // devo mettere altri campi nel group by.
            foreach ($data['data'] as $row) {
                $x = $row['x'];
                $y = $row['y'];
                unset($row['x'], $row['y']);    // Rimuovo x e y per vedere se ho altri dati

                if (empty($row)) {
                    // Non ho altri dati nella riga oltre a una x e una y => una colonna nella tabella,
                    // il nome può anche essere vuoto
                    $name = $element['charts_elements_label'];
                } else {
                    // Ho altri dati - l'implosione dei campi restanti mi rappresenterà la label delle varie colonnine
                    $name = implode(' ', $row);
                }

                // Inizializzo l'array di dati con tutti i possibili valori di x

                if (!isset($data['series'][$name])) {
                    foreach ($data['x'] as $xval) {
                        $data['series'][$name][$xval] = 0;
                    }
                }

                $data['series'][$name][$x] = $y;
            }



            $data['element'] = $element;
            $all_data[] = $data;
        }


        return $all_data;
    }

    /**
     * Calendars
     */
    public function get_calendar($calendar_id) {
        if (!$calendar_id)
            die('ERRORE: calendar ID mancante');

        $dati = array();
        $dati['calendars'] = $this->db->query("SELECT * FROM calendars LEFT JOIN entity ON entity.entity_id = calendars.calendars_entity_id WHERE calendars_id = '$calendar_id'")->row_array();

        $dati['calendars_fields'] = $this->db->query("SELECT * FROM calendars_fields 
                                                      LEFT JOIN fields ON fields.fields_id = calendars_fields.calendars_fields_fields_id 
                                                      LEFT JOIN calendars ON calendars.calendars_id = calendars_fields.calendars_fields_calendars_id
                                                      WHERE calendars_id = '$calendar_id'")->result_array();


        return $dati;
    }

    /**
     * Maps
     */
    public function get_map($map_id) {
        if (!$map_id)
            die('ERRORE: map ID mancante');

        $dati = array();
        $dati['maps'] = $this->db->query("SELECT * FROM maps LEFT JOIN entity ON entity.entity_id = maps.maps_entity_id WHERE maps_id = '$map_id'")->row_array();

        $dati['maps_fields'] = $this->db->query("SELECT * FROM maps_fields 
                                                    LEFT JOIN fields ON fields.fields_id = maps_fields.maps_fields_fields_id 
                                                    LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id 
                                                    LEFT JOIN maps ON maps.maps_id = maps_fields.maps_fields_maps_id
                                                 WHERE maps_id = '$map_id'")->result_array();


        return $dati;
    }

    /**
     * Utility methods
     */
    public function get_entity_preview_by_name($entity_name, $where = NULL, $limit = NULL, $offset = 0) {
        $entity = $this->datab->get_entity_by_name($entity_name);
        if (empty($entity)) {
            debug("Entity {$entity_name} does not exists.", 1);
        }

        /* Get the fields */
        $entity_id = $entity['entity_id'];
        $entity_data = $this->datab->get_data_entity($entity_id, 0, $where, $limit, $offset);
        $all_fields = $entity_data['visible_fields'];

        $entity_preview = array_filter($all_fields, function($field) use($entity_id, $all_fields) {
            if (!$field['fields_ref'] && $field['fields_entity_id'] == $entity_id) {
                // Sto guardando un campo semplice dell'entità, tipo il nome
                return $field['fields_preview'] == 't';
            } elseif (!$field['fields_ref'] && $field['fields_entity_id'] != $entity_id) {
                // Sto prendendo un campo semplice (non chiave) di un'entità joinata - lo voglio solo se il campo che punta a questa entità è preview e lui è preview
                foreach ($all_fields as $field1) {
                    if ($field1['fields_ref'] == $field['entity_name'] && $field['fields_preview'] == 't' && $field1['fields_preview'] == 't') {
                        return TRUE;
                    }
                }
                return FALSE;
            } else {
                // Negli altri casi non voglio prendere il campo
                return FALSE;
            }
        });
        $records = $entity_data['data'];

        /* Build preview */
        $result = array();
        foreach ($records as $record) {
            $preview = "";

            // 06/03/2015
            // ho dovuto aggiungere un array_unique() perché quando faccio la
            // preview di campi che si referenziano ricorsivamente rischio di
            // trovarmi campi duplicati
            $used = array();
            foreach ($entity_preview as $field) {
                if (!in_array($field['fields_name'], $used) && isset($record[$field['fields_name']])) {
                    $preview .= $record[$field['fields_name']] . " ";
                    $used[] = $field['fields_name'];
                }
            }

            $preview = trim($preview);
            if (!$preview) {
                $preview = "ID #{$record[$entity_name . '_id']}";
            }

            $result[$record[$entity_name . '_id']] = $preview;
        }

        return $result;
    }

    public function get_support_data($fields_ref = NULL) {

        if (!$fields_ref) {
            return array();
        } else {
            $entity = $this->get_entity_by_name($fields_ref);

            // Verifico se il ref si riferisce ad una eventuale relations oppure ad una tabella di supporto, in modo da gestirlo diversamente
            $relations = $this->db->get_where('relations', array('relations_name' => $entity['entity_name']))->row_array();
            if (count($relations) > 0) {
                $support_relation_table = $relations['relations_table_2'];
                $entity = $this->get_entity_by_name($relations['relations_table_2']);
                $field_support_id = $relations['relations_field_2'];
            } else {
                $support_relation_table = $fields_ref;
                $field_support_id = $entity['entity_name'] . "_id";
            }

            // Estraggo i campi che si possono visualizzare per la tabella o entità di supporto....
            $visible_fields_supports = $this->db->query("SELECT * FROM fields LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                                                         WHERE fields_entity_id = '{$entity['entity_id']}' AND fields_preview = 't'")->result_array();
            $support_fields = $this->fields_implode($visible_fields_supports);
            $select = $field_support_id . ($support_fields ? ',' . $support_fields : '');
            return $this->db->query("SELECT {$select} FROM {$support_relation_table}")->result_array();
        }
    }

    /**
     * Costruisce il where di un oggetto GRID, MAPS, CHARTS o altro
     */
    public function generate_where($element_type, $element_id, $value_id = NULL, $other_where = null, $additional_data = array()) {

        $arr = array();

        $element = $this->db->get_where($element_type, array($element_type . "_id" => $element_id))->row_array();
        $entity = $this->get_entity($element[$element_type . '_entity_id']);

        // Verifico se questo oggetto ha un where di suo
        if ($other_where) {
            $arr[] = "(" . (is_array($other_where)? implode(' AND ', $other_where): $other_where) . ")";
        }

        if ($element[$element_type . "_where"]) {
            // Aggiungo il suo where all'inizio del where che andrò a ritornare
            $arr[] = "(" . $element[$element_type . "_where"] . ")";
        }

        //Ora verifico se c'è una filter session key assegnata e se esiste in sessione
        if (array_key_exists($element_type . "_filter_session_key", $element) && $element[$element_type . "_filter_session_key"]) {

            $sess_where_data = $this->session->userdata(SESS_WHERE_DATA);
            $operators = unserialize(OPERATORS);

            if (isset($sess_where_data[$element[$element_type . "_filter_session_key"]])) {


                /** =======================
                 * Prendo le relazioni che mi serviranno dopo.
                 * Alla fine verranno disposte in un array indicizzato con il
                 * nome della relazione
                 * ========================
                 * NB - Ho preso le relazioni assumendo che nella
                 * RELATION TABLE 1 ci sia l'entità principale (quella sulla
                 * quale eseguo la query)
                 */
                $__relationships = $this->db->get_where('relations', array('relations_table_1' => $entity['entity_name']))->result_array();
                $relationships = array_combine(array_map(function($rel) { return $rel['relations_name']; }, $__relationships), $__relationships);

                
                foreach ($sess_where_data[$element[$element_type . "_filter_session_key"]] as $condition) {

                    $query_field = $this->db->join('fields_draw', 'fields_draw_fields_id = fields_id', 'left')->get_where('fields', array('fields_id' => (int) $condition['field_id']));
                    if ($query_field->num_rows() && $query_field->row()->fields_name) {
                        $field = $query_field->row();

                        // Se il campo è di un'entità diversa da quella del form devo fare un where in
                        // ovviamente l'entità a cui appartiene il campo deve avere almeno un campo che punta all'entità del form
                        $is_another_entity = ($entity['entity_id'] != $field->fields_entity_id);

                        if ($is_another_entity) {
                            // Sto cercando in un'entità diversa
                            $other_entity = $this->get_entity($field->fields_entity_id);
                            
                            $other_field_select = $this->db->get_where('fields', array('fields_entity_id' => $field->fields_entity_id, 'fields_ref' => $entity['entity_name']))->row();
                            if(isset($other_field_select->fields_name)) {
                                // Caso 1: è l'altra entità che ha il ref nell'entità in cui eseguo la ricerca
                                $where_prefix = "{$entity['entity_name']}_id IN (SELECT {$other_field_select->fields_name} FROM {$other_entity['entity_name']} WHERE ";
                            } else {
                                // Caso 2: è questa entità che sta ha il ref nell'altra entità
                                // devo trovare codesto field
                                $field_referencing = $this->db->get_where('fields', array('fields_entity_id' => $entity['entity_id'], 'fields_ref' => $other_entity['entity_name']))->row();
                                if(empty($field_referencing)) {
                                    // Non so come gestirlo, per ora piazzo un continue e tolgo debug
                                    //debug("Campo errato nella ricerca: {$field->fields_name}");
                                    continue;
                                }
                                
                                $where_prefix = "{$field_referencing->fields_name} IN (SELECT {$other_entity['entity_name']}_id FROM {$other_entity['entity_name']} WHERE ";
                            }
                            

                            $where_suffix = ")";
                        } elseif (array_key_exists($field->fields_ref, $relationships)) {
                            // Sto filtrando in una relazione, quindi il mio field ref punta ad una relazione
                            // prendo il campo della TABELLA 2 perché è la cossiddetta tabella correlata
                            // mi sto fondendo il cervello con queste relazioni, quindi se non si capisce una fava
                            // perdonatemi :)
                            $main_field = $relationships[$field->fields_ref]['relations_field_1'];
                            $related_field = $relationships[$field->fields_ref]['relations_field_2'];

                            $where_prefix = "{$entity['entity_name']}.{$entity['entity_name']}_id IN (SELECT {$main_field} FROM {$field->fields_ref} WHERE ";
                            $where_suffix = ")";

                            // A questo punto devo cambiare il $field perché $field è il campo dell'entità principale
                            // e il where finale dovrà essere fatto sull'entità di relazione e più precisamente sul campo della tabella 2
                            // il concetto è questo: campo_entita_principale IN ( SELECT campo_1 FROM nome_relazione WHERE campo_2 OP VAL )
                            // Il problema è che il campo_2 è cmq un ID quindi non sarà dentro la tabella fields - per risolvermi questa rogna
                            // modifico la variabile field già esistente - Questo penso sia il più brutto accrocchio della storia...
                            $field->fields_type = 'INT';
                            $field->fields_name = $related_field;
                            $field->fields_draw_html_type = NULL;
                        } else {
                            // Sto filtrando in un campo dell'entità principale
                            $where_prefix = '';
                            $where_suffix = '';
                        }

                        
                        
                        
                        // Metto in pratica i filtri e li aggiungo all'array
                        // delle condizioni del where
                        if (in_array($field->fields_draw_html_type, array('date', 'date_time'))) {
                            $values = explode(' - ', $condition['value']);
                            if (count($values) === 2) {
                                $start = preg_replace('/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $values[0]);
                                $end = preg_replace('/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $values[1]);
                                $arr[] = "({$where_prefix}{$field->fields_name}::TIMESTAMP >= '{$start}' AND {$field->fields_name}::TIMESTAMP <= '{$end}'{$where_suffix})";
                            }
                        } else {
                            $condition['value'] = str_replace("'", "''", $condition['value']);
                            switch ($condition['operator']) {
                                case 'in' :
                                    $values = "'" . implode("','", explode(',', $condition['value'])) . "'";
                                    $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} ({$values}){$where_suffix})";
                                    break;

                                case 'like' :
                                    if (in_array($field->fields_type, array('VARCHAR', 'TEXT'))) {
                                        $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} '%{$condition['value']}%'{$where_suffix})";
                                    }
                                    break;

                                default :
                                    $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} '{$condition['value']}'{$where_suffix})";
                            }
                        }
                    }
                }
            }
        }

        // Se ho un id in ingresso gli inserisco il where di default... farei deprecare questa cosa.
        // Pure io, anche perché crea solo casini - inoltre questa cosa andava fatta solo nel caso in cui non ci fosse nessun where aggiuntivo
        /*if (isset($value_id) && $value_id && !$element[$element_type . "_where"]) {
            $arr[] = "{$entity['entity_name']}_id = '$value_id'";
        }*/

        // Genero il where in stringa
        $where = implode(" AND ", $arr);
        //empty($arr) OR debug($arr);
        //
        // Rimpiazzo eventuali var value id
        $where = str_replace('{value_id}', $value_id, $where);
        if(is_array($additional_data) && $additional_data) {
            foreach($additional_data as $k=>$v) {
                $where = str_replace("{{$k}}", $v, $where);
            }
        }
        

        // Rimpiazzo eventuali variabili dalla sessione
        $where = $this->replace_superglobal_data($where);

        return $where;
    }

    /**
     * DEPRECATO - Metodo forse non + usato dovrebbe passare tutto sul generate where ora.
     */
    public function get_grid_where($grid_id, $value_id = NULL) {

        $grid = $this->db->get_where('grids', array('grids_id' => $grid_id))->row_array();
        $entity = $this->get_entity($grid['grids_entity_id']);
        $arr = array();

        // Valuto se ho un id ingresso ed un where
        if (isset($value_id) && $value_id) {
            if ($grid['grids_where']) {
                $arr[] = str_replace('{value_id}', $value_id, $grid['grids_where']);
            } else {
                $arr[] = "{$entity['entity_name']}_id = '$value_id'";
            }
        } else if ($grid['grids_where']) {
            // Per la grid è definito un where -> è plausibile che bisogni fare  il replace di variabili
            $arr[] = $this->replace_superglobal_data($grid['grids_where']);
        }


        // Applica il filtro => joinalo all'arr
        $sess_grid_data = $this->session->userdata(SESS_GRIDS_DATA);
        $operators = unserialize(OPERATORS);

        if (isset($sess_grid_data[$grid_id])) {
            foreach ($sess_grid_data[$grid_id] as $condition) {
                $query_field = $this->db->get_where('fields', array('fields_id' => (int) $condition['field_id']));
                if ($query_field->num_rows() && $query_field->row()->fields_name) {
                    $field = $query_field->row();
                    switch ($condition['operator']) {
                        case 'in' :
                            $values = "'" . implode("','", explode(',', $condition['value'])) . "'";
                            $arr[] = "{$field->fields_name} {$operators[$condition['operator']]['sql']} ({$values})";
                            break;

                        case 'like' :
                            if (in_array($field->fields_type, array('VARCHAR', 'TEXT'))) {
                                $arr[] = "{$field->fields_name} {$operators[$condition['operator']]['sql']} '%{$condition['value']}%'";
                            }
                            break;

                        default :
                            $arr[] = "{$field->fields_name} {$operators[$condition['operator']]['sql']} '{$condition['value']}'";
                    }
                }
            }
        }

        //Filtra tramite post (da studiare bene perchè genera problemi nel caso in cui ci siano degli altri post)
        if ($grid['grids_layout'] == 'datatable_action_filter') {
            $filter_post = $this->input->post('grid_filter');
            if (!empty($filter_post[$grid_id])) {
                foreach ($filter_post[$grid_id] as $key => $value) {
                    if ($value !== '') {
                        $arr[] = "{$key} = '{$value}'";
                    }
                }
            }
        }

        return $arr;
    }

    public function replace_superglobal_data($string) {
        
        $matches = array();
        if (preg_match_all('/\{.[^\{\}]+\}/', $string, $matches) && count($matches) > 0) {
            
            foreach ($matches[0] as $pattern) {

                // [!!!] I matches di preg_match_all sono degli array bidimensionali
                $pattern = $pattern;
                $exp = explode(' ', ltrim(rtrim($pattern, '}'), '{'));
                
                switch (strtoupper($exp[0])) {
                    
                    case 'POST':
                        // Post
                        $data = $this->input->post();
                        break;
                    
                    case 'GET':
                        // Get
                        $data = $this->input->get();
                        break;
                    
                    default :
                        // Session
                        $data = $this->session->userdata($exp[0]);
                }

                if (isset($exp[1]) && array_key_exists($exp[1], $data)) {
                    $value = $data[$exp[1]];
                } else {
                    $value = $data;
                }

                $string = str_replace($pattern, $value, $string);
            }
        }

        return $string;
    }

    public function fields_implode($fields) {
        $myarray = array();
        foreach ($fields as $field) {
            $myarray[] = (array_key_exists('alias', $field) ? "{$field['alias']}." : NULL) . $field['fields_name'];
        }
        $fields_imploded = implode(',', $myarray);
        return $fields_imploded;
    }

    /**
     * Notifiche
     */
    public function get_notifications($limit = null, $offset = 0) {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        
        if(is_numeric($limit) && $limit > 0) {
            $this->db->limit($limit);
        }
        
        if(is_numeric($offset) && $offset > 0) {
            $this->db->offset($offset);
        }
        
        $notifications = $this->db->order_by('notifications_date_creation', 'desc')->get_where('notifications', array('notifications_user_id' => $user_id))->result_array();
        return $notifications;
    }
    
    public function readAllNotifications() {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        $this->db->update('notifications', array('notifications_read' => 't'), array('notifications_user_id' => $user_id));
    }
    
    public function readNotification($notificationId) {
        $this->db->update('notifications', array('notifications_read' => 't'), array(
            'notifications_user_id' => $this->auth->get('id'),
            'notifications_id' => $notificationId
        ));
    }

    /**
     * Post process
     */
    public function run_post_process($entity_id, $when, $data = array()) {
        
        if( ! is_numeric($entity_id) && is_string($entity_id)) {
            $entity = $this->get_entity_by_name($entity_id);
            $entity_id = $entity['entity_id'];
        }

        $post_process = $this->db->get_where('post_process', array(
            'post_process_entity_id' => $entity_id,
            'post_process_when' => $when,
            'post_process_crm' => 't'
        ));
        
        if ($post_process->num_rows() > 0) {
            foreach ($post_process->result_array() as $function) {
                eval($function['post_process_what']);
            }
        }

        return $data;
    }

    /**
     * Costruzione link
     */
    public function get_detail_layout_link($entity_id, $value_id = null) {
        if (!array_key_exists($entity_id, $this->_entity_detail_layouts)) {
            $this->_entity_detail_layouts[$entity_id] = $this->db->get_where('layouts', array('layouts_entity_id' => $entity_id, 'layouts_is_entity_detail' => 't'))->row_array();
        }

        $lay = $this->_entity_detail_layouts[$entity_id];
        if (empty($lay)) {
            return FALSE;
        } else {
            return base_url('main/layout/' . $lay['layouts_id'] . '/' . $value_id);
        }
    }

    public function generate_menu_link($menu, $value_id = NULL, $data = NULL) {
        $link = '';
        if ($menu['menu_layout']) {
            $controller_method = (($menu['menu_modal'] == 't') ? 'get_ajax/layout_modal' : 'main/layout');
            $link = base_url("{$controller_method}/{$menu['menu_layout']}") . $menu['menu_link'];
        } elseif ($menu['menu_link']) {
            $link = str_replace('{base_url}', base_url(), $menu['menu_link']);
        }

        // Valuto se ho dati su cui fare il replace
        if (!is_null($value_id)) {
            $link = str_replace('{value_id}', $value_id, $link);
        }

        if ($data !== NULL && is_array($data)) {
            $replace_data = array();
            foreach ($data as $key => $value) {
                if (!is_numeric($key) && !is_array($value)) {
                    $replace_data['{' . $key . '}'] = $value;
                }
            }

            $link = str_replace(array_keys($replace_data), array_values($replace_data), $link);
        }

        return $this->replace_superglobal_data($link);
    }

    /**
     * Controllo permessi
     */
    public function is_admin($user_id = NULL) {
        if ($user_id === NULL || $user_id == $this->auth->get(LOGIN_ENTITY . "_id")) {
            // Sto controllando me stesso
            return $this->auth->is_admin();
        } else {
            $query = $this->db->where('permissions_user_id', $user_id)->get('permissions');
            return ($query->num_rows() > 0 ? $query->row()->permissions_admin == 't' : FALSE);
        }
    }

    public function get_menu($position = 'sidebar') {
        
        /* Un utente può accedere ad un layout se
         * - il campo layouts_entity_id è settato e l'utente ha i permessi di lettura almeno
         * - la coppia (utente,layout) non è contenuta nella tabella `unallowed_layouts`
         * - il campo layouts_entity_id non è settato */
        $user_id = (int) $this->auth->get(LOGIN_ENTITY . "_id");
        
        if (!$this->is_admin()) {
            // Se eseguo questa query son sicuro che l'utente non è un amministratore
            $this->db->where("
                (
                    layouts_entity_id IS NULL OR
                    NOT EXISTS ( SELECT 1 FROM permissions LEFT JOIN permissions_entities ON permissions_entities_permissions_id = permissions_id WHERE permissions_user_id = '{$user_id}' ) OR
                    layouts_entity_id IN (
                        SELECT permissions_entities_entity_id
                        FROM permissions LEFT JOIN permissions_entities ON permissions_entities_permissions_id = permissions_id
                        WHERE permissions_user_id = '{$user_id}' AND permissions_entities_value <> '" . PERMISSION_NONE . "'
                    )
                )", null, false);
        }

        // Controlla anche la tabella `unallowed_layouts`
        $this->db->where("(
                    menu.menu_layout IS NULL OR
                    menu.menu_layout NOT IN (SELECT unallowed_layouts_layout FROM unallowed_layouts WHERE unallowed_layouts_user = '{$user_id}')
                )");
        
        // Prendi tutti i menu, con i sottomenu e poi ciclandoli costruisci un array multidimensionale
        $menu = $this->db->from('menu')->join('layouts', 'layouts.layouts_id = menu.menu_layout', 'left')
                ->where('menu_position', $position)->order_by('menu_order')->get()->result_array();
        
        $return = array();
        $subs = array();
        
        foreach($menu as $item) {
            if($item['menu_parent']) {
                // Se c'è un parent è un sottomenu
                isset($subs[$item['menu_parent']]) OR $subs[$item['menu_parent']] = array();
                $item['pages_names'] = array("layout_{$item['menu_layout']}");
                $subs[$item['menu_parent']][] = $item;
            } else {
                // Altrimenti ha un sottomenu
                $item['submenu'] = array();
                $item['pages_names'] = array("layout_{$item['menu_layout']}");
                $return[$item['menu_id']] = $item;
            }
        }
        
        // Inserisci il sottomenu per ogni menu padre
        foreach($subs as $parent=>$items) {
            if(isset($return[$parent]['submenu'])) {
                $return[$parent]['submenu'] = $items;
                foreach ($items as $item) {
                    $return[$parent]['pages_names'][] = "layout_{$item['menu_layout']}";
                }
            }
        }
        
        return $return;
    }
    
    
    
    public function can_write_entity($entity_id) {

        if ($this->is_admin()) {
            return TRUE;
        } else {
            $user_id = (int) $this->auth->get(LOGIN_ENTITY . "_id");
            $permissions = $this->db->from('permissions')
                            ->join('permissions_entities', 'permissions_entities_permissions_id = permissions_id', 'left')
                            ->where(array('permissions_user_id' => $user_id, 'permissions_entities_entity_id' => $entity_id))
                            ->get()->row();
            return empty($permissions) || ($permissions->permissions_entities_value == PERMISSION_WRITE);
        }
    }

    public function can_read_entity($entity_id) {

        if ($this->is_admin()) {
            return TRUE;
        } else {
            $user_id = (int) $this->auth->get(LOGIN_ENTITY . "_id");
            $permissions = $this->db->from('permissions')
                            ->join('permissions_entities', 'permissions_entities_permissions_id = permissions_id', 'left')
                            ->where(array('permissions_user_id' => $user_id, 'permissions_entities_entity_id' => $entity_id))
                            ->get()->row();
            return empty($permissions) || ($permissions->permissions_entities_value != PERMISSION_NONE);
        }
    }

    public function can_access_layout($layout_id) {
        
        if(!$layout_id OR !is_numeric($layout_id)) {
            return false;
        }

        
        $user_id = (int) $this->auth->get(LOGIN_ENTITY . "_id");
        
        if ($this->is_admin()) {
            // Se l'utente è amministratore il layout è accessibile SSE
            // non è contenuto nella tabella dei layout non permessi
            $this->db->where('unallowed_layouts_user', $user_id)->where('unallowed_layouts_layout', $layout_id);
//            debug($this->db->get('unallowed_layouts')->result(),1);
            return $this->db->count_all_results('unallowed_layouts') == 0;
        } else {
            // Se eseguo questa query son sicuro chel'utente non è un
            // amministratore - comunque come prima non devo avere il record in
            // unallowed_layouts AND l'entità dev'essere accessibile
            return $this->db
                    ->where('layouts_id', $layout_id)
                    ->where("NOT EXISTS (SELECT 1 FROM unallowed_layouts WHERE unallowed_layouts_user = '{$user_id}' AND unallowed_layouts_layout = '{$layout_id}')", null, false)
                    ->where("(
                                layouts_entity_id IS NULL OR
                                (
                                    layouts_entity_id IN (SELECT entity_id FROM entity) AND
                                    (
                                        NOT EXISTS ( SELECT 1 FROM permissions LEFT JOIN permissions_entities ON permissions_entities_permissions_id = permissions_id WHERE permissions_user_id = '{$user_id}' ) OR
                                        layouts_entity_id IN (
                                            SELECT permissions_entities_entity_id
                                            FROM permissions LEFT JOIN permissions_entities ON permissions_entities_permissions_id = permissions_id
                                            WHERE permissions_user_id = '{$user_id}' AND permissions_entities_value <> '" . PERMISSION_NONE . "'
                                        )
                                    )
                                )
                            )")
                    ->count_all_results('layouts') > 0;
        }
    }
    
    
    public function addUserGroup($userId, $groupName) {
        
        if (!is_numeric($userId) OR !is_string($groupName) OR $userId < 1 OR !$groupName) {
            throw new InvalidArgumentException('Impossibile aggiungere lo user al gruppo: $userId deve contenere un id valido e il nome deve essere una stringa');
        }
        
        if (!$this->db->where(LOGIN_ENTITY . '_id', $userId)->count_all_results(LOGIN_ENTITY)) {
            throw new Exception("L'utente '{$userId}' non esiste");
        }
        
        // Recupera permessi del gruppo
        $permissions = $this->db->get_where('permissions', array('permissions_group' => $groupName))->row_array();
        if (empty($permissions['permissions_id'])) {
            throw new Exception("Il gruppo '{$groupName}' non esiste");
        }
        
        $permissionsEntities = $this->db->get_where('permissions_entities', array('permissions_entities_permissions_id' => $permissions['permissions_id']))->result_array();
        $permissionsModules = $this->db->get_where('permissions_modules', array('permissions_modules_permissions_id' => $permissions['permissions_id']))->result_array();
        
        $this->db->trans_start();
        
        // Cancella i permessi vecchi
        $this->db->delete('permissions', array('permissions_user_id' => $userId));
        $this->db->where('permissions_user_id NOT IN (SELECT '.LOGIN_ENTITY.'_id FROM '.LOGIN_ENTITY.')')->delete('permissions');
        $this->db->where('permissions_entities_permissions_id NOT IN (SELECT permissions_id FROM permissions)')->delete('permissions_entities');
        $this->db->where('permissions_modules_permissions_id NOT IN (SELECT permissions_id FROM permissions)')->delete('permissions_modules');
        
        unset($permissions['permissions_id']);
        $permissions['permissions_user_id'] = $userId;
        $this->db->insert('permissions', $permissions);
        $permissionId = $this->db->insert_id();
        
        if ($permissionsEntities) {
            $this->db->insert_batch('permissions_entities', array_map(function($item) use ($permissionId) {
                $item['permissions_entities_permissions_id'] = $permissionId;
                return $item;
            }, $permissionsEntities));
        }
        
        if ($permissionsModules) {
            $this->db->insert_batch('permissions_modules', array_map(function($item) use ($permissionId) {
                $item['permissions_modules_permissions_id'] = $permissionId;
                return $item;
            }, $permissionsModules));
        }
        
        $this->db->trans_complete();
        return $permissionId;
    }
    
    

    public function get_modules() {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        if ($this->is_admin($user_id)) {
            $modules = $this->db->get_where('modules', array('modules_installed' => 't',));
        } else {
            $modules = $this->db->
                            select('modules.*')->
                            from('modules')->
                            join('permissions_modules', 'permissions_modules_module_name = modules_name', 'left')->
                            join('permissions', 'permissions_modules_permissions_id = permissions_id', 'left')->
                            where(array(
                                'permissions_modules_value' => PERMISSION_WRITE,
                                'modules_installed' => 't',
                                'permissions_user_id' => $user_id,
                            ))->get();
        }

        return $modules->result_array();
    }

    public function module_installed($name) {
        $query = $this->db->from('modules')->where('modules_installed', 't')->where('modules_name', $name)->get();
        return $query->num_rows() > 0;
    }

    public function module_access($name) {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        $query = $this->db->from('permissions')->join('permissions_modules', 'permissions_modules_permissions_id = permissions_id', 'left')
                        ->where('permissions_modules_value', PERMISSION_WRITE)
                        ->where('permissions_modules_module_name', $name)
                        ->where('permissions_user_id', $user_id)->get();
        return $query->num_rows() > 0 || $this->is_admin($user_id);
    }

    /**
     * Search
     */
    public function get_search_results($search) {
        //Ottengo le entità cercabili
        $entities = $this->db->get_where('entity', array('entity_searchable' => 't'))->result_array();
        $e_ids = array_map(function($entity) {
            return $entity['entity_id'];
        }, $entities);

        $results = array();
        if (!empty($e_ids)) {
            $_all_fields = $this->db->where_in('fields_entity_id', $e_ids)->get('fields')->result_array();

            $all_fields = array();
            foreach ($_all_fields as $field) {
                $all_fields[$field['fields_entity_id']][] = $field;
            }

            foreach ($entities as $entity) {
                $fields = $all_fields[$entity['entity_id']];
                $where = $this->search_like($search, $fields);

                //Calcola risultato e consideralo sse ha dati effettivi
                $result = $this->get_data_entity($entity['entity_id'], 0, $where);
                if (!empty($result['data'])) {
                    $results[] = $result;
                }
            }
        }


        return $results;
    }

    public function search_like($search = '', $fields = array()) {
        $outer_where = array();

        if ($search) {

            /** FIX: Cerco gli eventuali support fields di un singolo field e li metto in un array al più bidimensionale * */
            $_fields = array();
            foreach ($fields as $field) {
                if (empty($field['support_fields'])) {
                    if (isset($field['fields_type']) && isset($field['fields_name'])) {
                        $_fields[] = $field;
                    }
                } else {
                    foreach ($field['support_fields'] as $sfield) {
                        if (isset($sfield['fields_type']) && isset($sfield['fields_name']) && isset($sfield['fields_preview']) && $sfield['fields_preview'] === 't') {
                            $_fields[] = $sfield;
                        }
                    }
                }
            }

            $fields = $_fields;


            /*
             * Facendo così penalizzo i risultati contenenti la stringa intera
             * cercata
             * 
            // Spezzo la stringa da cercare sugli spazi
            $search_chunks = explode(' ', $search);
             * 
             * Quindi faccio una cosa più intelligente:
             *  - cerco la stringa così com'è
             *  - la spezzo in parole e mantengo solo quelle di almeno 3
             *    caratteri
             */
            $search_chunks = array_unique(array_filter(explode(' ', $search), function($chunk) {
                return $chunk && strlen($chunk) > 2;
            }));
            
            

            // Sono interessato ai record che contengono TUTTI i chunk in uno o più campi
            foreach ($search_chunks as $_chunk) {
                $chunk = str_replace("'", "''", $_chunk);
                $inner_where = array();
                foreach ($fields as $field) {
                    switch (strtoupper($field['fields_type'])) {
                        case 'VARCHAR': case 'TEXT':
                            $inner_where[] = "({$field['fields_name']} ILIKE '%{$chunk}%')";
                            break;
                        case 'INT': case 'FLOAT':
                            //Uguaglianza semplice
                            if (is_numeric($chunk)) {
                                $inner_where[] = "({$field['fields_name']} = '{$chunk}')";
                            }
                    }
                }
                
                if ($inner_where) {
                    $outer_where[] = '(' . implode(' OR ', $inner_where) . ')';
                }
            }
        }

        return implode(' AND ', $outer_where);
    }

    /**
     * Layout builder
     */
    public function build_layout($layout_id, $value_id, $layout_data_detail = null) {
        
        if(!is_numeric($layout_id) OR ($value_id && !is_numeric($value_id))) {
            return null;
        }

        $dati['layout_container'] = $this->db->get_where('layouts', array('layouts_id' => $layout_id))->row_array();
        if (empty($dati['layout_container'])) {
            show_404();
        }

        if ($value_id && $dati['layout_container']['layouts_entity_id'] > 0) {
            $entity = $this->get_entity($dati['layout_container']['layouts_entity_id']);
            if (isset($entity['entity_name'])) {
                $data_entity = $this->get_data_entity($entity['entity_id'], 0, "{$entity['entity_name']}_id = {$value_id}", 1);
                if (isset($data_entity['data'][0]) && !empty($data_entity['data'][0]) && is_array($data_entity['data'][0])) {
                    $layout_data_detail = $data_entity['data'][0];
                } else {
                    // FIX: siamo nella condizione in cui ho una vista di dettaglio,
                    // ma i dati sono assenti o perché mancano o per permessi mancanti
                    return NULL;
                }
            }
        }
        
        if(is_null($layout_data_detail) && $dati['layout_container']['layouts_is_entity_detail'] === 't') {
            return null;
        }
        
        

        
        $layouts = $this->db->query("SELECT * FROM layouts_boxes LEFT JOIN layouts ON layouts.layouts_id = layouts_boxes.layouts_boxes_layout WHERE layouts_id = '$layout_id' ORDER BY layouts_boxes_row, layouts_boxes_position, layouts_boxes_cols")->result_array();
        $dati['pre-layout'] = $this->getHookContent('pre-layout', $layout_id, $value_id);
        $dati['post-layout'] = $this->getHookContent('post-layout', $layout_id, $value_id);
        $dati['layout'] = array();
        
        
        // Ricavo il content se necessario
        foreach ($layouts as $key => $layout) {

            switch ($layout['layouts_boxes_content_type']):

                case "layout":
                    $subLayout = $this->build_layout($layout['layouts_boxes_content_ref'], $value_id, $layout_data_detail);
                    $subLayout['current_page'] = "layout_{$layout_id}";
                    $subLayout['show_title'] = false;
                    $layout['content'] = $this->load->view("pages/layout", array('dati' => $subLayout,'value_id' => $value_id), true);
                    break;
                    
                case "chart":
                    $chart = $this->get_layout('charts', $layout['layouts_boxes_content_ref']);
                    if ($chart['charts_layout']) {
                        $chart_layout = $chart['charts_layout'];
                    } else {
                        $chart_layout = DEFAULT_LAYOUT_CHART;
                    }

                    $chart['elements'] = $this->get_charts_elements($chart['charts_id']);

                    // Controllo i permessi per questa grid
                    /* if( ! $this->can_read_entity($chart['charts_entity_id'])) {
                      $layout['content'] = 'Non disponi dei permessi sufficienti per leggere i dati.';
                      break;
                      } */

                    // prendo i dati
                    $chart_data = $this->get_chart_data($chart);
                    
                    if(empty($chart_data[0]['series']) || !is_array($chart_data[0]['series'])) {
                        // Ci ficco un content vuoto
                        $layout['content'] = '';
                    } else {
                        $layout['content'] = $this->load->view('pages/layouts/charts/' . $chart_layout, array('chart' => $chart, 'chart_data' => $chart_data, 'value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    }
                    break;

                case "grid":
                    $grid = $this->get_layout('grids', $layout['layouts_boxes_content_ref']);
                    if ($grid['grids_layout']) {
                        $grid_layout = $grid['grids_layout'];
                    } else {
                        $grid_layout = DEFAULT_LAYOUT_GRID;
                    }

                    // Controllo i permessi per questa grid
                    if (!$this->can_read_entity($grid['grids_entity_id'])) {
                        $layout['content'] = 'Non disponi dei permessi sufficienti per leggere i dati.';
                        break;
                    }


                    // Prendo la struttura della grid
                    $grid = $this->get_grid($layout['layouts_boxes_content_ref']);

                    // Ci sono problemi se inizializzo una datatable senza colonne!!
                    if (empty($grid['grids_fields']))
                        continue;

                    // Prendo i dati della grid: è inutile prendere i dati in una grid ajax
                    if (in_array($grid['grids']['grids_layout'], array('datatable_ajax', 'datatable_ajax_inline'))) {
                        $grid_data = array();
                    } else {
                        
                        if(empty($layout_data_detail)) {
                            // Passo solo il value_id
                            $grid_input = $value_id;
                        } else {
                            // Passo data e value_id
                            $grid_input = array('value_id' => $value_id, 'additional_data' => $layout_data_detail);
                        }
                        
                        $grid_data = $this->get_grid_data($grid, $grid_input);
                    }

                    /***********************************************************
                     * Se c'è una subentity aggiungo l'eventuale data aggiuntiva
                     */
                    $sub_grid = null;
                    if ($grid['grids']['grids_sub_grid_id']) {
                        $sub_grid = $this->get_grid($grid['grids']['grids_sub_grid_id']);
                        $relation_field = $this->db->select('fields_name')->from('fields')->where('fields_entity_id', $sub_grid['grids']['grids_entity_id'])->where('fields_ref', $grid['grids']['entity_name'])->get();
                        if ($relation_field->num_rows() < 1) {
                            debug("L'entità {$grid['grids']['entity_name']} non è referenziata dall'entità {$sub_grid['grids']['entity_name']}");
                        } else {
                            $sub_grid['grid_relation_field'] = $relation_field->row()->fields_name;
                            if( ! empty($grid_data['data'])) {
                                $entName = $grid_data['entity']['entity_name'];
                                $arr_parent_ids = array_map(function($parentRecord) use($entName) { return $parentRecord["{$entName}_id"]; }, $grid_data['data']);
                                $parent_ids = implode("','", $arr_parent_ids);
                                $where = "{$sub_grid['grid_relation_field']} IN ('{$parent_ids}')";
                                $grid_data['sub_grid_data'] = $this->get_grid_data($sub_grid, null, $where);
                            }
                        }
                    }

                    $layout['content'] = $this->load->view('pages/layouts/grids/' . $grid_layout, array('grid' => $grid, 'sub_grid' => $sub_grid, 'grid_data' => $grid_data, 'value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    break;
                    
                case "form":
                    $form_id = $layout['layouts_boxes_content_ref'];
                    $form = $this->get_form($form_id);
                    $form['forms']['action_url'] = base_url("db_ajax/save_form/{$form['forms']['forms_id']}");

                    // Se ho un id in ingresso tento di estrarre i dati per un possibile edit del form
                    if ($value_id) {
                        $form['forms']['edit_data'] = $this->get_data_entity($form['forms']['entity_id'], 1, "{$form['forms']['entity_name']}_id = '$value_id'");
                        $form['forms']['action_url'] = base_url("db_ajax/save_form/{$form['forms']['forms_id']}/true/$value_id");

                        $data = $form['forms']['edit_data'];

                        // Devo prendere per ogni relazione i valori selezionati
                        if (!empty($data['relations']) && !empty($data['data'])) {
                            foreach ($data['relations'] as $relation) {
                                if (isset($relation['relations_name'])) {

                                    // Prendi i dati della relazione
                                    $rel = $this->db->get_where('relations', array('relations_name' => $relation['relations_name']))->row_array();

                                    // Se ho trovato dei dati allora posso provare a cercare le relazioni
                                    if (isset($data['data'][0]) && is_array($data['data'][0]) && array_key_exists($rel['relations_field_1'], $data['data'][0])) {
                                        $field = $rel['relations_field_1'];
                                        $other = $rel['relations_field_2'];
                                        $other_table = $rel['relations_table_2'];
                                    } elseif (isset($data['data'][0]) && is_array($data['data'][0]) && array_key_exists($rel['relations_field_2'], $data['data'][0])) {
                                        $field = $rel['relations_field_2'];
                                        $other = $rel['relations_field_1'];
                                        $other_table = $rel['relations_table_1'];
                                    } else {
                                        continue;
                                    }

                                    /**
                                     * Il risultato dell'operazione sarà un array di valori - questo array sarà il valore del campo dell'entità che va a relazionarsi con l'altra tabella
                                     * Ad esempio se una camera può avere più servizi voglio che tutti i servizi finiscano sul campo camere_servizi
                                     * $field_name_for_relation_values avrà in questo caso il valore di camere_servizi
                                     */
                                    $field_name_for_relation_values = NULL;
                                    foreach ($data['visible_fields'] as $visible_field) {
                                        if (in_array($visible_field['fields_ref'], array($rel['relations_table_1'] . '_' . $rel['relations_table_2'], $rel['relations_table_2'] . '_' . $rel['relations_table_1']))) {
                                            // Modalità nuova - faccio un field ref sulla relazione
                                            $field_name_for_relation_values = $visible_field['fields_name'];
                                            break;
                                        }

                                        if ($visible_field['fields_ref'] === $other_table) {
                                            $field_name_for_relation_values = $visible_field['fields_name'];
                                        }
                                    }


                                    if (!is_null($field_name_for_relation_values)) {

                                        // Prendo il gruppo di id della tabella e cerco tutti i valori nella relazione per quegli id. Poi con un foreach smisto il valore corretto per ogni dato
                                        $ids = array_map(function($dato) use($field) {
                                            return $dato[$field];
                                        }, $data['data']);
                                        $relation_data = $this->db->where_in($field, $ids)->get($relation['relations_name'])->result_array();

                                        // Ciclando tutti i dati della relazione li raggruppo in un array che contiene id_entità_padre => array( tutti gli id a lui correlati )
                                        $relation_data_by_ids = array();
                                        foreach ($relation_data as $relation_dato) {
                                            if (empty($relation_data_by_ids[$relation_dato[$field]])) {
                                                $relation_data_by_ids[$relation_dato[$field]] = array();
                                            }

                                            $relation_data_by_ids[$relation_dato[$field]][] = $relation_dato[$other];
                                        }

                                        // Prendo le preview dei record relazionati
                                        foreach ($form['forms']['edit_data']['data'] as $key => $dato) {
                                            $form['forms']['edit_data']['data'][$key][$field_name_for_relation_values] = (isset($relation_data_by_ids[$dato[$field]]) ? $relation_data_by_ids[$dato[$field]] : array());
                                        }
                                    }
                                }
                            }
                        }
                    }


                    // Controllo i permessi per questa grid
                    if (!$this->can_write_entity($form['forms']['forms_entity_id'])) {
                        $layout['content'] = str_repeat('&nbsp;', 3) . 'Non disponi dei permessi sufficienti per modificare i dati.';
                        break;
                    }

                    $layout['content'] = $this->load->view("pages/layouts/forms/form_{$form['forms']['forms_layout']}", array('form' => $form, 'ref_id' => $layout['layouts_boxes_content_ref'], 'value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    break;

                case "calendar":
                    $data = $this->get_calendar($layout['layouts_boxes_content_ref']);
                    $data['cal_layout'] = $this->get_layout('calendars', $layout['layouts_boxes_content_ref']);
                    $cal_layout = ($data['cal_layout']['calendars_layout']) ? $data['cal_layout']['calendars_layout'] : DEFAULT_LAYOUT_CALENDAR;

                    $layout['content'] = $this->load->view('pages/layouts/calendars/' . $cal_layout, array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    break;
                
                case "map":
                    $data = $this->get_map($layout['layouts_boxes_content_ref']);
                    $data['map_layout'] = $this->get_layout('maps', $layout['layouts_boxes_content_ref']);
                    $map_layout = ($data['map_layout']['maps_layout']) ? $data['map_layout']['maps_layout'] : DEFAULT_LAYOUT_MAP;

                    $layout['content'] = $this->load->view('pages/layouts/maps/' . $map_layout, array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    break;

                case "menu_group": case "menu_button_stripe": case "menu_big_button":
                    $data = $this->get_menu($layout['layouts_boxes_content_ref']);
                    $layout['content'] = $this->load->view("pages/layouts/menu/{$layout['layouts_boxes_content_type']}", array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    break;
                
                case "view":
                    $layout['content'] = $this->load->view("pages/layouts/custom_views/{$layout['layouts_boxes_content_ref']}", array('value_id' => $value_id, 'layout_data_detail' => $layout_data_detail), true);
                    break;

                default:
                    if (empty($layout['layouts_boxes_content_type']) && $layout['layouts_boxes_content']) {
                        // Contenuto definito
                        ob_start();
                        eval(' ?>' . $layout['layouts_boxes_content'] . '<?php ');
                        $layout['content'] = ob_get_contents();
                        ob_end_clean();
                    } else {
                        debug("TYPE: " . $layout['layouts_boxes_content_type'] . " ANCORA NON GESTITO");
                    }
                    break;
                    
            endswitch;


            // Assicuriamoci che il content esista
            if (empty($layout['content'])) {
                $layout['content'] = '';
            }
            
            
            // Fa il wrap degli hook pre e post che devono esistere per ogni
            // componente ad eccezione di custom views e custom php code
            // ---
            // Gli hook per il layout non vengono definiti da qua ma vengono
            // presi globali all'inizio del build layout
            $hookSuffix = $layout['layouts_boxes_content_type'];
            $hookRef = $layout['layouts_boxes_content_ref'];
            
            if ($hookSuffix && is_numeric($hookRef) && $hookSuffix !== 'layout') {
                $layout['content'] =
                        $this->getHookContent('pre-'.$hookSuffix, $hookRef, $value_id) .
                        $layout['content'] .
                        $this->getHookContent('post-'.$hookSuffix, $hookRef, $value_id);
            }
            
            $dati['layout'][$layout['layouts_boxes_row']][] = $layout;
        }

        // I dati del record di dettaglio
        if (!empty($layout_data_detail)) {

            $fields = array_map(function($key) {
                return "{{$key}}";
            }, array_keys($layout_data_detail));
            $values = array_values($layout_data_detail);

            $fields[] = '{value_id}';
            $values[] = $value_id;

            $dati['layout_container']['layouts_title'] = str_replace($fields, $values, $dati['layout_container']['layouts_title']);
            $dati['layout_container']['layouts_subtitle'] = str_replace($fields, $values, $dati['layout_container']['layouts_subtitle']);
        }
        $dati['layout_data_detail'] = $layout_data_detail;
        return $dati;
    }

    /**
     * Non è proprio un get layout.... zio billy x non stare a cambiare lascio così
     */
    public function get_layout($entity_name, $value) {
        $entity = $this->db->query("SELECT * FROM $entity_name WHERE " . $entity_name . "_id = '$value' LIMIT 1 ")->row_array();
        return $entity;
    }
    
    public function getHookContent($hookType, $hookRef, $valueId = null) {
        $hooks = $this->db
                ->order_by('hooks_order')->where("(hooks_ref IS NULL OR hooks_ref = {$hookRef})")
                ->get_where('hooks', array('hooks_type' => $hookType))->result_array();
        
        $plainHookContent = trim(implode(PHP_EOL, array_key_map($hooks, 'hooks_content', '')));
        
        if (!$plainHookContent) {
            return '';
        }
        
        ob_start();
        $value_id = $valueId;   // per comodità e uniformità...
        eval(' ?> ' . $plainHookContent . ' <?php ');
        return ob_get_clean();
    }
    
    
    /**
     * Print a grid cell
     */
    public function build_grid_cell($field, $dato) {
        
        /**
         * Fetch the value
         */
        $value = array_key_exists($field['fields_name'], $dato) ? $dato[$field['fields_name']] : '';

        if ($value !== '' && (!$field['fields_ref'] OR $value)) {
            if ($field['fields_ref'] && $field['fields_type'] === 'INT') {
                if (is_array($value)) {
                    // Ho una relazione molti a molti - non mi serve alcuna informazione sui field ref, poiché ho già la preview stampata
                    return implode('<br/>', $value);
                } elseif (!empty($field['support_fields'])) {
                    // Ho un field ref semplice - per stamparlo ho bisogno dei support fields (che sono i campi preview dell'entità referenziata)
                    $link = $value ? $this->datab->get_detail_layout_link($field['support_fields'][0]['fields_entity_id']) : false;

                    if (empty($field['support_fields'])) {
                        // Non ho nessun campo di preview, quindi la preview sarà vuota - stampo solo l'ID del record
                        $text = $value;
                    } else {
                        $hasAllFields = true;
                        $_text = array();
                        foreach ($field['support_fields'] as $support_field) {
                            if (array_key_exists($field['fields_name'] . '_' . $support_field['fields_name'], $dato)) {
                                $_text[] = $dato[$field['fields_name'] . '_' . $support_field['fields_name']];
                            } elseif (array_key_exists($support_field['fields_name'], $dato)) {
                                // Appendo il nuovo campo preview all'array della preview $_text
                                $_text[] = $dato[$support_field['fields_name']];
                            } else {
                                // Non posso continuare a stampare la preview perché ci sono campi non presenti
                                $hasAllFields = false;
                                break;
                            }
                        }

                        if ($hasAllFields) {
                            // La preview completa sta nell'arrat $_text
                            $text = implode(' ', $_text);
                        } else {
                            // Non ho tutti i campi preview disponibili (ad es. nelle relazioni NxM), quindi faccio una chiamata alla get entity preview
                            $value_id = (int) $value;
                            $preview = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id = '{$value_id}'", 1);
                            $text = isset($preview[$value]) ? $preview[$value] : $value;
                        }
                    }

                    // C'è un link? stampo un <a></a> altrimenti stampo il testo puro e semplice
                    return $link ? anchor(rtrim($link, '/') . '/' . $value, $text) : $text;
                }
            } else {
                // Posso stampare il campo in base al tipo
                switch ($field['fields_draw_html_type']) {
                    case 'upload':
                        if ($value) {
                            return anchor(base_url_template("uploads/$value"), 'Scarica file', array('target' => '_blank'));
                        }
                        break;

                    case 'upload_image':

                        if ($value) {
                            return anchor(base_url_template("uploads/{$value}"), "<img src='" . base_url_template("imgn/1/50/50/uploads/{$value}") . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px'));
                        } else {
                            $path = base_url_template('images/no-image-50x50.gif');
                            return "<img src='{$path}' style='width: 50px;' />";
                        }

                    case 'textarea':
                        $style = 'white-space: pre-line';
                    case 'wysiwyg':
                        if (empty($style)) {
                            $style = '';
                        }

                        $stripped = strip_tags($value);
                        $value = preg_replace(array('#<script(.*?)>(.*?)</script>#is', '/<img[^>]+\>/i'), '', $value);
                        //$value = $this->security->xss_clean($value);

                        if (strlen($stripped) > 150) {
                            
                            $textContainerID = md5($value);
                            $javascript = "$(this).parent().hide(); $('.text_{$textContainerID}').show();";

                            return '<div><div onclick="' . $javascript . '" style="cursor:pointer;">' . nl2br(character_limiter($stripped, 130)) . '</div>' .
                                    '<a onclick="' . $javascript . '" href="#">Vedi tutto</a></div>' .
                                    '<div class="text_' . $textContainerID . '" style="display:none;' . $style . '">' . (($field['fields_draw_html_type'] == 'textarea') ? nl2br($stripped) : $value) . '</div>';
                        } else {
                            return (($field['fields_draw_html_type'] == 'textarea') ? nl2br($stripped) : $value);
                        }

                    case 'date':
                        return "<span class='hide'>{$value}</span>" . dateFormat($value);

                    case 'date_time':
                        return "<span class='hide'>{$value}</span>" . dateTimeFormat($value);

                    case 'stars':
                        $out = "<span class='hide'>{$value}</span>";
                        for ($i = 1; $i <= 5; $i++) {
                            $class = $i > $value ? 'icon-star-empty' : 'icon-star';
                            $out .= "<i class='{$class}'></i>";
                        }
                        return $out;

                    case 'radio':
                    case 'checkbox':
                        return (($field['fields_type'] == 'BOOL') ? (($value == 't') ? 'Si' : 'No') : $value);

                    default:

                        if ($field['fields_type'] === 'DATERANGE') {
                            // Formato daterange 
                            $dates = dateRange_to_dates($value);
                            switch (count($dates)) {
                                case 2:
                                    return 'Dal ' . dateFormat($dates[0]) . ' al ' . dateFormat($dates[1]);
                                case 0:
                                    return '';
                                default:
                                    return '<small>[Formato daterange errato]</small>';
                            }
                            
                        } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            return mailto($value);
                        } elseif (filter_var($value, FILTER_VALIDATE_URL) || (preg_match("/\A^www.( [^\s]* ).[a-zA-Z]$\z/ix", $value) && filter_var('http://' . $value, FILTER_VALIDATE_URL) !== false )) {

                            if (stripos($value, 'http://') === false) {
                                $value = 'http://' . $value;
                            }

                            return anchor($value, str_replace(array('http://', 'https://'), '', $value), array('target' => '_blank'));
                        } else {
                            return $value;
                        }
                }
            }
        } elseif ($field['fields_draw_placeholder']) {
            return "<small class='text-muted'>{$field['fields_draw_placeholder']}</small>";
        }
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php eba2ed2ea9c8d823fddc0ee25dc799c3 */