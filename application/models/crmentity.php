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

class Crmentity extends CI_Model {

    const CACHE_TIME = 300;

    /** Cache */
    private $_entities = array();
    private $_entity_names = array();
    private $_entities_processed = array();
    private $_relations = array();
    private $_fields_ref_by = array();
    private $_visible_fields = array();
    //private $cache = array();

    /** Dati della entity */
    private $entity_name = '';
    private $entity_id = null;
    private $table = '';

    function __construct($entity_name = '') {

        parent :: __construct();
        
        $this->load->driver('cache');
        if ($entity_name) {
            $this->entity_name = $entity_name;
            $this->table = $entity_name;

            $entity = $this->get_entity_by_name($entity_name);
            if (empty($entity)) {
                show_error("Entit&agrave; inesistente: {$entity_name}");
            }

            $this->entity_id = $entity['entity_id'];
        }
    }

    private function saveCache($key, $val) {
        $this->cache->save($key, $val, self::CACHE_TIME);
    }

    private function getCache($key) {
        return $this->cache->get($key);
    }

    public function getList($limit = 0) {
        $key = md5(__METHOD__ . serialize(func_get_args()) . $this->table);
        $result = $this->getCache($key);
        if ($result === false) {
            $result = $this->db->limit($limit)->get($this->table)->result_array();
            $this->saveCache($key, $result);
        }
        
        return $result;
    }

    public function get_data_simple($id) {
        $key = md5(__METHOD__ . serialize(func_get_args()) . $this->entity_name);
        $arr = $this->getCache($key);
        if ($arr === false) {
            $arr = $this->get_data_simple_list(null, "{$this->entity_name}_id = '$id'");
            $this->saveCache($key, $arr);
        }

        if (isset($arr['data'][0])) {
            return $arr[0];
        } else {
            return false;
        }
    }

    public function get_data_full($id, $maxDepthLevel = 2) {
        
        $key = md5(__METHOD__ . serialize(func_get_args()).serialize($this->_entities_processed) . $this->entity_name . '_' . $this->entity_id . '_' . $id . $maxDepthLevel);
        $arr = $this->getCache($key);
        
        if ($arr === false) {
            $this->_entities_processed = array();
            $arr = $this->get_data_full_list($this->entity_id, $this->entity_name, "{$this->entity_name}.{$this->entity_name}_id = '$id'", 1, 0, null, false, $maxDepthLevel);
            $this->saveCache($key, $arr);
        }

        return isset($arr['data'][0])? $arr['data'][0]: array();
    }

    public function get_data_full_list($entity_id = null, $entity_name = null, $where = array(), $limit = NULL, $offset = 0, $order_by = NULL, $count = FALSE, $depth = 2) {
        $key = md5(__METHOD__ . serialize(func_get_args()) . $this->entity_name . '_' . $this->entity_id . '_1');
        $data = $this->getCache($key);
        if ($data === false) {
            $entity_id = is_null($entity_id) ? $this->entity_id : $entity_id;
            $entity_name = is_null($entity_name) ? $this->entity_name : $entity_name;

            if ($depth <= 0) {
                return array();
            } else {
                $depth--;
            }
            // Metodo per elaborare anche l'eventuale where inserito nella grid:
            // -$where è un array => gli elementi di $arr equivalgono a quelli di $where
            $arr = is_array($where) ? $where : array();

            // -$where è una stringa => $where viene inserito in $arr come elemento
            if (is_string($where) AND $where !== '') {

                $fst = $where[0];
                $lst = $where[strlen($where) - 1];

                if ($fst === '(' && $lst === ')') {
                    $arr[] = $where;
                } else {
                    $arr[] = "({$where})";
                }
            }

            $data = $this->get_data_simple_list($entity_id, $arr, $limit, $offset, $order_by, $count, true, $depth);

            if ($count) {
                return $data['data'];
            }



            // Un array contenente tutti gli id dei risultati della query
            $result_ids = array_map(function($row) use ($entity_name) {
                return $row[$entity_name . '_id'];
            }, $data['data']);

            if (!empty($result_ids)) {
                
                // Estraggo e ciclo tutti i geography per ricavarne latitudine e
                // longitudine
                $fields_geography = array_filter($data['visible_fields'], function($field) use($entity_name) {
                    return $field['fields_type'] === 'GEOGRAPHY' && $field['entity_name'] === $entity_name;
                });
                foreach ($fields_geography as $field) {
                    $geography = array();
                    $geography_field = $field['fields_name'];

                    $this->db->select("{$entity_name}_id as id, ST_Y({$geography_field}::geometry) AS lat, ST_X({$geography_field}::geometry) AS lng")->where_in($entity_name . '_id', $result_ids);

                    // Indicizzo i risultati per id
                    foreach ($this->db->get($entity_name)->result_array() as $result) {
                        $geography[$result['id']] = array('lat' => $result['lat'], 'lng' => $result['lng']);
                    }

                    /**
                     * $geography è un array fatto così
                     * record id => ( [lat] => 46.010085296546, [lng] => 13.1390368938446)
                     */
                    // E li inserisco al posto della geometry
                    foreach ($data['data'] as $key => $_data) {

                        $id = $_data[$entity_name . '_id'];
                        $latlng_array = (isset($geography[$id]) ? $geography[$id] : array('lat' => NULL, 'lon' => NULL));

                        $data['data'][$key][$geography_field] = array_merge(array('geo' => $_data[$geography_field]), $latlng_array);
                    }
                }
                
                // Estraggo e ciclo tutti i campi html (fields_draw_html_type === wysiwyg)
                // e sostituisco la stringa {base_url} con base_url()
                $fields_html = array_filter($data['visible_fields'], function($field) use($entity_name) {
                    return $field['fields_draw_html_type'] === 'wysiwyg' && $field['entity_name'] === $entity_name;
                });
                
                foreach ($fields_html as $field) {
                    $name = $field['fields_name'];
                    foreach ($data['data'] as $key => $_data) {
                        
                        if (empty($_data[$name])) {
                            continue;
                        }
                        
                        $data['data'][$key][$name] = str_replace('{base_url}', function_exists('base_url_template')? base_url_template(): base_url(), $_data[$name]);
                    }
                }
                
                
            }
            


            // Cerco i campi che puntano a questa entità e ne ottengo i dati
            if (!empty($data['fields_ref_by']) && !empty($data['data'])) {
                foreach ($data['data'] as $key => $_data) {

                    foreach ($data['fields_ref_by'] as $entity) {
                        $entita = $this->get_data_full_list($entity['entity_id'], $entity['entity_name'], array("{$entity['entity_name']}.{$entity['fields_name']} = '{$_data[$entity_name . '_id']}'"), null, 0, null, false, $depth);
                        $data['data'][$key][$entity['entity_name']] = $entita;
                    }
                }
            }


            // Estraggo le eventuali relazioni
            if (!empty($data['relations']) && !empty($data['data'])) {
                //debug($data['relations']);
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

                            if ($visible_field['fields_ref'] == $relation['relations_name']) {
                                $field_name_for_relation_values = $visible_field['fields_name'];
                                break;
                            } else if ($visible_field['fields_ref'] == $other_table) {
                                // Questo metodo è la versione vecchia in cui il
                                // field_ref di relazione puntava all'entità
                                // relazionata piuttosto che alla tabella di
                                // relazione... tenuto per retrocompatibilità
                                $field_name_for_relation_values = $visible_field['fields_name'];
                                break;
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

                                $related_data[] = $relation_dato[$other];
                                $relation_data_by_ids[$relation_dato[$field]][] = $relation_dato[$other];
                            }

                            // Prendo le preview dei record relazionati
                            if (!empty($related_data)) {
                                $related_data_preview = $this->get_entity_preview_by_name($other_table, "{$other_table}.{$other} IN (" . implode(',', $related_data) . ")");

                                foreach ($data['data'] as $key => $dato) {
                                    if (isset($relation_data_by_ids[$dato[$field]])) {
                                        foreach ($relation_data_by_ids[$dato[$field]] as $related_value) {

                                            // Se il campo non è un array per il momento non ho soluzioni migliori se non farlo diventare un array vuoto
                                            // perché in effetti non dovrebbe mai essere pieno
                                            if (array_key_exists($related_value, $related_data_preview)) {

                                                if (!is_array($data['data'][$key][$field_name_for_relation_values])) {
                                                    $data['data'][$key][$field_name_for_relation_values] = array();
                                                }

                                                $data['data'][$key][$field_name_for_relation_values][$related_value] = $related_data_preview[$related_value];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }


            // Recupero le eventuali fake relations, cioè tutti i fields con
            // fields_ref che hanno fields_type o VARCHAR o TEXT
            if (!empty($result_ids)) {
                $fake_relations_fields = array_filter($data['visible_fields'], function($field) {
                    return $field['fields_ref'] && in_array($field['fields_type'], array('VARCHAR', 'TEXT'));
                });

                $names = array();
                foreach ($fake_relations_fields as $field) {
                    $name = $field['fields_name'];
                    $related = $field['fields_ref'];

                    if (in_array($name, $names)) {
                        continue;
                    }

                    $names[] = $name;
                    $fake_relation_ids = array();
                    foreach ($data['data'] as $_data) {
                        if ($_data[$name]) {
                            $fake_relation_ids = array_merge($fake_relation_ids, explode(',', $_data[$name]));
                        }
                    }

                    $fullData = array();
                    if (!empty($fake_relation_ids)) {
                        $imploded_fake_relation_ids = implode(',', $fake_relation_ids);
                        $frEntity = $this->get_entity_by_name($related);
                        $qFullData = $this->get_data_simple_list($frEntity['entity_id'], "{$related}_id IN ({$imploded_fake_relation_ids})", null, 0, null, false, false, $depth - 1);
                        $fullData = array_combine(array_map(function ($dato) use ($related) {
                                    return $dato["{$related}_id"];
                                }, $qFullData), $qFullData);
                    }

                    // E li inserisco al posto della geometry
                    foreach ($data['data'] as $key => $_data) {
                        if (!$_data[$name]) {
                            continue;
                        }

                        $fieldIds = array_merge($fake_relation_ids, explode(',', $_data[$name]));
                        $data['data'][$key][$name] = array();
                        foreach ($fieldIds as $id) {
                            if (array_key_exists($id, $fullData)) {
                                $data['data'][$key][$name][$id] = $fullData[$id];
                            }
                        }
                    }
                }
            }

            $this->saveCache($key, $data);
        }
        return $data;
    }

    public function get_data_simple_list($entity_id = null, $where = NULL, $limit = NULL, $offset = 0, $order_by = NULL, $count = FALSE, $extra_data = false, $depth = 2) {
        if ($entity_id === null) {
            $entity_id = $this->entity_id;
        }

        $dati = $this->getEntityFullData($entity_id);

        if (count($dati['visible_fields']) < 1) {
            return false;
        }

        $visible_fields = $this->fields_implode($dati['visible_fields']);

        // Estraggo i campi visibili anche di eventuali tabelle da joinare
        foreach ($dati['visible_fields'] as $k => $campo) {
            if ($campo['fields_ref'] && !in_array(array('relations_name' => $campo['fields_ref']), $dati['relations'])) {
                $entity = $this->get_entity_by_name($campo['fields_ref']);
                if (empty($entity)) {
                    // L'entità non esiste più quindi svuoto il fields_ref
                    $dati['visible_fields'][$k]['fields_ref'] = '';
                } else {
                    $visible_fields_supports = $this->getVisibleFields($entity['entity_id']);
                    $this->db->query("SELECT * FROM fields LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                                                    LEFT JOIN entity ON entity.entity_id = fields.fields_entity_id
                                                    WHERE fields_entity_id = '{$entity['entity_id']}' AND (fields_preview = 't')")->result_array();

                    $fields = $this->fields_implode($visible_fields_supports);

                    $dati['visible_fields'] = array_merge($dati['visible_fields'], $visible_fields_supports);
                    if ($fields) {
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
            $this->db->where(($where[0] === '(' && $where[strlen($where) - 1] === ')') ? "({$where})" : $where, null, false);   // null: il valore, false: NON FARE ESCAPE
        } elseif (is_array($where) && count($where) > 0) {
            // Attenzione!! Devo distinguere da where con chiave numerica a
            // quelli con chiave a stringa: dei primi ignoro la chiave, mentre
            // dei secondi faccio un where(key, value);
            array_walk($where, function($value, $key) {
                if (is_numeric($key)) {
                    $this->db->where($value, null, false); // non escapare nemmeno qui
                } elseif (is_string($key)) {
                    $this->db->where($key, $value);
                }
            });
        }


        // Mi salvo l'elenco delle entità joinate, in modo da evitare doppi join
        // in futuro non dovrò evitare, ma semplicemente assegnare un alias
        $this->db->from($dati['entity']['entity_name']);
        $joined = array($dati['entity']['entity_name']);
        $to_join_later = array();

        // Aggiungo in automatico i join SUPPONENDO che il campo da joinare, nella tabella sarà nometabella_id ********
        $permission_entities = array($entity_id);   // Lista delle entità su cui devo applicare i limiti
        $post_process_data_entity = array();
        foreach ($dati['visible_fields'] as $key => $campo) {
            // I campi che hanno un ref li join solo se non sono in realtà legati a delle relazioni Se invece sono delle relazioni faccio select dei dati
            if (($campo['fields_ref'] && !in_array(array('relations_name' => $campo['fields_ref']), $dati['relations']))) {

                if (in_array($campo['fields_ref'], $joined)) {
                    // Metto nella lista dei join later
                    $to_join_later[$campo['fields_name']] = $campo['fields_ref'];
                } else {
                    $this->db->join($campo['fields_ref'], "{$campo['fields_ref']}.{$campo["fields_ref"]}_id = {$campo['entity_name']}.{$campo['fields_name']}", "left");
                    array_push($joined, $campo['fields_ref']);

                    // Devo fare il controllo dei limiti sui field ref
                    $ent = $this->get_entity_by_name($campo['fields_ref']);
                    if (!in_array($ent['entity_id'], $permission_entities)) {
                        $permission_entities[] = $ent['entity_id'];
                    }
                }
            } elseif (($campo['fields_ref'] && in_array(array('relations_name' => $campo['fields_ref']), $dati['relations']))) {
                $relation_to = $this->db->query("SELECT relations_table_2 FROM relations WHERE relations_name = '{$campo['fields_ref']}'")->row()->relations_table_2;
                $ent = $this->get_entity_by_name($relation_to);
                $ent['relation_name'] = $campo['fields_ref'];
                $post_process_data_entity[$key] = $ent;

                // Devo fare il controllo dei limiti sulle relazioni
                if (!in_array($ent['entity_id'], $permission_entities)) {
                    $permission_entities[] = $ent['entity_id'];
                }
            }
        }



        /**
         * Applico limiti permessi
         */
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
            $queryExecuted = $this->db->get();

            if ($queryExecuted instanceof CI_DB_result) {
                $dati['data'] = $queryExecuted->result_array();
            } else {
                //debug(array_map(function($dbg) { unset($dbg['object']); return $dbg; }, debug_backtrace()),1);
                throw new Exception('Si è verificato un errore estraendo i dati' . PHP_EOL . 'Ultima query:' . PHP_EOL . $this->db->last_query());
            }



            // Qui devo emulare un join, quindi so che per ogni query avrò un solo
            // risultato, perché sennò avrei usato le relazioni
            // (foreach successivo)
            if ($dati['data'] && $depth > 0) {
                foreach ($to_join_later as $main_field => $sub_entity_name) {
                    $sub_entity = $this->get_entity_by_name($sub_entity_name);
                    $main_field_values = implode(',', array_filter(array_map(function ($record) use($main_field) {
                                        return $record[$main_field];
                                    }, $dati['data'])));
                    if (!$main_field_values) {
                        continue;
                    }

                    // Ritrova i dati - jData sono i dati grezzi, mentre mergeable
                    // sono i dati pronti ad essere uniti ai dati principali
                    $jData = $this->get_data_simple_list($sub_entity['entity_id'], "{$sub_entity_name}.{$sub_entity_name}_id IN ({$main_field_values})", null, 0, null, false, false, $depth - 1);
                    $mergeable = array();

                    foreach ($jData as $record) {
                        // Rimappo ogni valore in modo da avere il main field
                        // anteposto al vero field. Quindi se ho
                        // messaggi con campo messaggi_utente che ha ref a
                        // utenti, ogni campo recuperato da questa join sarà
                        // rinominato in messaggi_utente_utenti_*
                        $mergeable[$record[$sub_entity_name . '_id']] = array_combine(array_map(function($key) use($main_field) {
                                    return $main_field . '_' . $key;
                                }, array_keys($record)), array_values($record));
                    }

                    foreach ($dati['data'] as $k => $record) {
                        $id = $record[$main_field];
                        $dati['data'][$k] = array_merge($record, isset($mergeable[$id]) ? $mergeable[$id] : array());
                    }
                }
            }

            if ($dati['data'] && $depth > 0) {
                $mainEntityID = "{$dati['entity']['entity_name']}_id";
                $entityIDs = array_map(function($record) use($mainEntityID) {
                    return $record[$mainEntityID];
                }, $dati['data']);
                $implodedEntityIds = implode(',', $entityIDs);

                foreach ($post_process_data_entity as $key => $entity) {
                    $relEntityID = "{$entity['entity_name']}_id";
                    $relationName = $entity['relation_name'];
                    $dati['visible_fields'][$key]['data'] = $this->get_data_simple_list($entity['entity_id'], "{$entity['entity_name']}.{$relEntityID} IN (SELECT {$relEntityID} FROM {$relationName} WHERE {$relationName}.{$mainEntityID} IN ({$implodedEntityIds}))", null, 0, null, false, false, $depth - 1);
                }
            }
        }


        if ($extra_data) {
            return $dati;
        } else {
            return $dati['data'];
        }
    }

    public function fields_implode($fields) {

        $fields_imploded = "";
        $myarray = array();
        foreach ($fields as $field) {
            $myarray[] = $field['entity_name'] . '.' . $field['fields_name'];
        }

        return implode(',', $myarray);
    }

    /**
     * Get entity by ID
     * @param int $entity_id
     * @return array
     */
    public function get_entity($entity_id) {
        $entity = $this->extractEntityFromCache($entity_id);
        if (!$entity) {
            $entity = $this->db->query("SELECT * FROM entity WHERE entity_id = '{$entity_id}'")->row_array();
            if (empty($entity)) {
                show_error("L'entità {$entity_id} non esiste");
            }
            $this->cacheEntity($entity);
        }
        return $entity;
    }

    /**
     * Get entity by name
     * @param string $entity_name
     * @return array
     */
    public function get_entity_by_name($entity_name) {
        $entity = $this->extractEntityFromCache($entity_name);
        if (!$entity) {
            $qEntity = $this->db->query("SELECT * FROM entity WHERE entity_name = '{$entity_name}'");
            if (!$qEntity instanceof CI_DB_result) {
                show_error("L'entità {$entity_name} non esiste");
            }
            $entity = $qEntity->row_array();
            $this->cacheEntity($entity);
        }
        return $entity;
    }

    private function getEntityFullData($entity) {
        return array(
            'entity' => is_numeric($entity) ? $this->get_entity($entity) : $this->get_entity_by_name($entity),
            'relations' => $this->getEntityRelations($entity),
            'fields_ref_by' => $this->getFieldsRefBy($entity),
            'visible_fields' => $this->getVisibleFields($entity),
        );
    }

    private function getFieldsRefBy($entity) {

        if (is_numeric($entity)) {
            $entityData = $this->get_entity($entity);
            $entity = $entityData['entity_name'];
        }

        if (!array_key_exists($entity, $this->_fields_ref_by)) {
            $this->_fields_ref_by[$entity] = $this->db->select('entity_id, entity_name, fields_name')->join('entity', 'entity.entity_id = fields.fields_entity_id')->get_where('fields', array('fields_ref' => $entity))->result_array();
        }

        return $this->_fields_ref_by[$entity];
    }

    private function getVisibleFields($entity) {

        if (!is_numeric($entity)) {
            $entityData = $this->get_entity($entity);
            $entity = $entityData['entity_id'];
        }

        if (!array_key_exists($entity, $this->_visible_fields)) {
            $this->_visible_fields[$entity] = $this->db->join('entity', 'entity.entity_id = fields.fields_entity_id')->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id')->order_by('fields_name')->get_where('fields', array('fields_entity_id' => $entity, 'fields_draw_display_none' => 'f'))->result_array();
        }

        return $this->_visible_fields[$entity];
    }

    private function getEntityRelations($entity) {

        if (is_numeric($entity)) {
            $entityData = $this->get_entity($entity);
            $entity = $entityData['entity_name'];
        }

        if (!array_key_exists($entity, $this->_relations)) {
            $this->_relations[$entity] = $this->db->select('relations_name')->get_where('relations', array('relations_table_1' => $entity))->result_array();
        }

        return $this->_relations[$entity];
    }

    /**
     * Salva entità su cache
     * @param array $entity
     */
    private function cacheEntity(array $entity) {
        $this->_entities[$entity['entity_id']] = $entity;
        $this->_entity_names[$entity['entity_name']] = $entity['entity_id'];
    }

    /**
     * Ritrova entità da cache
     * @param mixed $id
     */
    private function extractEntityFromCache($id) {

        if (!is_numeric($id)) {
            if (array_key_exists($id, $this->_entity_names)) {
                $id = $this->_entity_names[$id];
            } else {
                return null;
            }
        }

        // ID è numerico ora
        return (array_key_exists($id, $this->_entities) ? $this->_entities[$id] : null);
    }

    /*     * ********** Utility methods ****************** */

    public function get_entity_preview_by_name($entity_name, $where = NULL, $limit = NULL, $offset = 0) {
        $entity = $this->get_entity_by_name($entity_name);
        if (empty($entity)) {
            debug("Entity {$entity_name} does not exists.", 1);
        }

        /* Get the fields */
        $entity_id = $entity['entity_id'];

        $entity_data = $this->get_data_simple_list($entity_id, $where, $limit, $offset, null, false, true);

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
            foreach ($entity_preview as $field) {
                if (isset($record[$field['fields_name']])) {
                    $preview .= $record[$field['fields_name']] . " ";
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

}
