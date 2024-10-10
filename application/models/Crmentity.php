<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

include_once __DIR__ . '/../helpers/general_helper.php';

class Crmentity extends CI_Model
{
    const CACHE_TIME = 300;
    const SCHEMA_CACHE_KEY = 'crm.schema';

    /** Cache */
    private $_fields_ref_by = [];
    private $_visible_fields = [];
    private $_schemaCache = null;


    private $_default_grids = [];
    private $_default_forms = [];

    /**
     * Class constructor
     * @param string $entity_name
     * @param array $languageId
     */
    public function __construct($entity_name = '', array $languageId = [])
    {
        parent::__construct();

        //$this->load->driver('cache');
        $this->load->driver('Cache/drivers/MY_Cache_file', null, 'mycache');

        $this->buildSchemaCacheIfNotValid();

        $this->setLanguages($languageId);

        // if ($entity_name) {
        //     $this->entity_name = $entity_name;
        //     $this->table = $entity_name;
        //     $entity = $this->getEntity($entity_name);
        //     $this->entity_id = $entity['entity_id'];
        // }
    }

    public function isRelation($entity)
    {
        if (is_numeric($entity)) {
            $entity_name = $this->getEntity($entity)['entity_name'];
        } elseif (is_array($entity)) {
            $entity_name = $entity['entity_name'];
        } else {
            $entity_name = $entity;
        }

        return array_key_exists($entity_name, $this->_schemaCache['relations']['by_name']);
    }

    public function getCrmSchemaCacheKey()
    {
        return self::SCHEMA_CACHE_KEY;
    }

    /**
     * Imposta le lingue usate per le traduzioni
     * @param array $languagesId
     */
    public function setLanguages(array $languagesId)
    {
        $this->languages = array_filter($languagesId, function ($item) {
            return $item && is_numeric($item);
        });
    }

    /**
     * Controlla se la $key è definita nella cache, altrimenti esegue $callback
     * e salva il valore ritornato in cache.
     *
     * @param string $key       Chiave della cache
     * @param Closure $callback Funzione da eseguire in caso di cache-miss
     * @return mixed            Il valore contenuto in cache
     */
    private function getFromCache($key, Closure $callback, $tags)
    {
        $result = $this->mycache->get($key);

        if ($result === false) {
            $result = $callback();
            if ($this->apilib->isCacheEnabled()) {
                $this->mycache->save($key, $result, self::CACHE_TIME, $tags);
            }
        }

        return $result;
    }

    /**
     * Estrai singolo record per id. Solo il record associato alla Crmentity
     * corrente viene estratto
     *
     * @param int $id
     * @param int $maxDepthLevel
     * @return array
     */
    public function get_data_full($entity, $id, $maxDepthLevel = 2)
    {

        $input = $this->apilib->runDataProcessing($entity, 'pre-search', ["{$entity}.{$entity}_id = '{$id}'"]);
        $arr = $this->get_data_full_list($entity, null, $input, 1, 0, null, false, $maxDepthLevel);
        
        return array_get($arr, 0, []);
    }

    /**
     * Esegue una query sull'entità e ritorna una lista di record - Vengono
     * calcolate eventuali relazioni da aggiungere ad ogni record, risolte le
     * geography, tradotti i campi multilingua (se ci sono lingue impostate),
     * ecc.
     *
     * !! Il risultato viene salvato in cache !!
     *
     * @param int|null $entity_id
     * @param null $unused_entity_name      DA DEPRECARE, PROBABILMENTE ADOTTEREI STRATEGIA DI ARRAY OPTIONS vedi `get_data_simple_list()`
     * @param array|string $where
     * @param int|null $limit
     * @param int $offset
     * @param string|null $order_by
     * @param bool $count
     * @param int $depth
     *
     * @return array
     *
     * @throws Exception
     */
    public function get_data_full_list($entity_id = null, $unused_entity_name = null, $where = [], $limit = null, $offset = 0, $order_by = null, $count = false, $depth = 2, $eval_cachable_fields = [], $additional_parameters = [])
    {

        if (!$entity_id) {

            throw new Exception("Impossibile eseguire la query: entità non specificata.");

        }

        $group_by = array_get($additional_parameters, 'group_by', null);
        // Entity name è da deprecare...

        $entity_name = $this->getEntity($entity_id)['entity_name'];
        if ($entity_name == 'timesheet') {
            //debug($depth);
        }

        $tags = $this->mycache->buildTagsFromEntity($entity_name);

        $_cache_key = 'apilib/' . __METHOD__ . ':' . $entity_name . md5(serialize(array_merge([$entity_id], array_slice(func_get_args(), 2))));

        if ($depth <= 0) { // Se è <= 0, ritorno array vuoto, altrimenti decrementa depth
            return [];
        }

        return $this->getFromCache($_cache_key, function () use ($entity_id, $entity_name, $where, $limit, $offset, $order_by, $group_by, $count, $depth, $eval_cachable_fields) {
            $extra_data = true;

            $data = $this->get_data_simple_list($entity_id, $where, compact('limit', 'offset', 'order_by', 'group_by', 'count', 'extra_data', 'depth', 'eval_cachable_fields'));

            //debug($entity_id);
            // Se è count ho finito qua, ma anche se non ho nessun risultato
            if ($count or !$data['data']) {
                return $data['data'];
            }

            // ---------------------------------------------------------
            // CON CALMA E TEMPO TUTTO STO PROCESSO E' DA OTTIMIZZARE!!!
            // ---------------------------------------------------------
            // Cercare se possibile di ciclare $data['data'] e
            // $data['visible_fields'] al più UNA volta ciascuno
            // Un array contenente tutti gli id dei risultati della query
            $result_ids = array_key_map($data['data'], $entity_name . '_id');
            $fieldsGeography = $fieldsWysiwyg = $fieldsMultilingual = $fieldsRanges = $fieldsFloat = [];

            foreach ($data['visible_fields'] as $field) {
                $fieldOfMainEntity = $field['entity_name'] === $entity_name;

                // Detect GEOGRAPHY
                // -----
                // Estraggo i geography per ricavarne latitudine e
                // longitudine - prendo solo quelli della mia stessa entità
                // altrimenti ho un db error
                if ($field['fields_type'] === 'GEOGRAPHY' && $fieldOfMainEntity) {
                    $geographyValues = [];
                    $geographyField = $field['fields_name'];

                    if ($this->db->dbdriver == 'postgre') {
                        $casted_field = "{$geographyField}::geometry";
                    } else {
                        $casted_field = "{$geographyField}";
                    }

                    // Indicizzo i risultati per id
                    if ($this->db->dbdriver == 'postgre') {
                        $this->db->select("{$entity_name}_id as id, ST_Y($casted_field) AS lat, ST_X($casted_field) AS lng")->where_in($entity_name . '_id', $result_ids);
                    } else {
                        $this->db->select("{$entity_name}_id as id, ST_X($casted_field) AS lat, ST_Y($casted_field) AS lng")->where_in($entity_name . '_id', $result_ids);
                    }

                    foreach ($this->db->get($entity_name)->result_array() as $result) {
                        $geographyValues[$result['id']] = ['lat' => $result['lat'], 'lng' => $result['lng']];
                    }

                    $fieldsGeography[$geographyField] = $geographyValues;
                }

                // Detect WYSIWYG
                // -----
                // Estraggo i campi html (fields_draw_html_type === wysiwyg)
                if ($field['fields_draw_html_type'] === 'wysiwyg') {
                    $fieldsWysiwyg[] = $field;
                }

                // Detect MULTILINGUAL
                // -----
                // Estraggo i campi multilingua sse ho una lista di priorità
                // lingue. Nel caso self::languages sia pieno, prendo per
                // ogni valore, il primo non vuoto ('' or null - 0 è ok).
                // Se non ho nessuna lingua, allora il valore è un array
                // contenente tutti i valori disponibili
                if ($this->languages && $field['fields_multilingual'] === DB_BOOL_TRUE) {
                    $fieldsMultilingual[$field['fields_id']] = $field;
                }

                // Attenzione se un domani vogliamo aggiungere il floatrange, temo succeda casino con l'estremo superiore (vd function rangeHumanFriendly)
                if ($field['fields_type'] === 'INT8RANGE' || $field['fields_type'] === 'INT4RANGE') {
                    $fieldsRanges[$field['fields_id']] = $field;
                }

                // Aggiunto double in qunato i field mysql
                if (strtolower($field['fields_type']) === 'float' || strtolower($field['fields_type']) === 'double') {
                    $fieldsFloat[$field['fields_id']] = $field;
                }
            }

            $baseUrl = function_exists('base_url_admin') ? base_url_admin() : base_url();
            foreach ($data['data'] as $key => $_data) {
                $id = $_data[$entity_name . '_id'];

                foreach ($fieldsGeography as $fieldName => $values) {
                    $geodata = array_get($values, $id, ['lat' => null, 'lon' => null]);
                    $geodata['geo'] = $_data[$geographyField];
                    $_data[$fieldName] = $geodata;
                }

                // Rimpiazzo il placeholder {base_url} dentro ai campi
                // contenenti un HTML
                foreach ($fieldsWysiwyg as $field) {
                    $name = $field['fields_name'];
                    if (!empty($_data[$name])) {
                        $_data[$name] = str_replace('{base_url}', $baseUrl, $_data[$name]);
                    }
                }

                // Decodifica json del campo
                foreach ($fieldsMultilingual as $field) {
                    $_data[$field['fields_name']] = $this->translateValue($_data[$field['fields_name']]);
                }

                foreach ($fieldsRanges as $field) {
                    $_data[$field['fields_name']] = extract_intrange_data($_data[$field['fields_name']]);
                }

                //Per i campi float li formato number_format 2, altrimenti mysql fa le bizze e mostra cose tipo 123.359999999999996
                if ($this->db->dbdriver != 'postgre') {
                    foreach ($fieldsFloat as $field) {
                        if (!empty($_data[$field['fields_name']]) && $_data[$field['fields_name']] !== null) {
                            $_data[$field['fields_name']] = number_format($_data[$field['fields_name']], 3, '.', '');
                            if (substr($_data[$field['fields_name']], -1) === '0') {
                                $_data[$field['fields_name']] = substr($_data[$field['fields_name']], 0, -1);
                            }
                        }
                    }
                }

                // Sovrascrivo il vecchio valore di data
                $data['data'][$key] = $_data;
            }

            // Cerco i campi che puntano a questa entità e ne ottengo i dati
            // sono sicuro che $result_ids non è vuoto
            $referersKeys = [];
            $referersRecords = array_fill_keys($result_ids, []);
            //debug($data, true);

            foreach ($data['fields_ref_by'] ?: [] as $entity) {
                $refererEntity = $entity['entity_name'];
                $refererField = $entity['fields_name'];

                // Se il campo che fa riferimento alla mia entità, ha lo
                // stesso nome dell'id di questa entità allora lo skippo,
                // perché vorrebbe dire che questa è una relazione
                if ($refererField == $entity_name . '_id') {
                    continue;
                }

                if (in_array($entity['fields_type'], ['VARCHAR', 'TEXT'])) {
                    // Sono in presenza di relazioni dove i miei id sono
                    // contenuti separati da virgola...
                    $refererWhere = sprintf("EXISTS(SELECT UNNEST(regexp_split_to_array(%s.%s, ',')) INTERSECT SELECT UNNEST(array[%s]))", $refererEntity, $refererField, "'" . implode("','", $result_ids) . "'");
                } else {
                    // Caso normale
                    $refererWhere = sprintf('%s.%s IN (%s)', $refererEntity, $refererField, implode(',', $result_ids));
                }

                $referersKeys[$refererEntity] = [];

                $referingData = $this->get_data_full_list($entity['entity_id'], $refererEntity, $refererWhere, null, 0, null, false, $depth);

                if (!empty($referingData)) {
                    foreach ($referingData as $record) {
                        // Se il campo è NON VISIBILE la query NON FALLISCE,
                        // ma non viene incluso nel risultato... quindi si
                        // rende necessario controllare se nel risultato è
                        // settata la chiave
                        if (array_key_exists($refererField, $record) && !is_array($record[$refererField]) && !is_object($record[$refererField])) {
                            $referersRecords[$record[$refererField]][$refererEntity][] = $record;
                        }
                    }
                }
            }

            foreach ($data['data'] as &$_data) {
                $_data = array_merge($_data, $referersKeys, $referersRecords[$_data[$entity_name . '_id']]);
            }

            // Estraggo le eventuali relazioni
            foreach ($data['relations'] as $relation) {

                // Prendi i dati della relazione
                $rel = $this->getRelationByName($relation);

                // Se ho trovato dei dati allora posso provare a cercare le relazioni
                if (array_key_exists($rel['relations_field_1'], $data['data'][0])) {
                    $field = $rel['relations_field_1'];
                    $other = $rel['relations_field_2'];
                    $other_table = $rel['relations_table_2'];
                } elseif (array_key_exists($rel['relations_field_2'], $data['data'][0])) {
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
                $field_name_for_relation_values = null;
                foreach ($data['visible_fields'] as $visible_field) {
                    if ($visible_field['fields_ref'] == $relation) {
                        $field_name_for_relation_values = $visible_field['fields_name'];
                        break;
                    }
                }

                if (!$field_name_for_relation_values) {
                    continue;
                }

                // Prendo il gruppo di id della tabella e cerco tutti i valori nella relazione per quegli id. Poi con un foreach smisto il valore corretto per ogni dato
                $ids = array_key_map($data['data'], $field);

                // Le tuple della tabella pivot della relazione - sono già filtrate per gli id dell'entità della grid
                //debug('test');
                $relation_data = $this->db->where_in($field, $ids)->get($relation)->result_array();
                //debug('test2');
                // Cicla i dati della tabella pivot e metti in $relation_data_by_ids i record suddivisi per id dell'entità della grid (per accederci dopo con meno foreach),
                // mentre in $related_data metti tutti gli id dell'altra tabella nella relazione (nell'esempio di camere_servizi, metti gli id dei servizi).
                $relation_data_by_ids = [];
                $related_data = [];
                foreach ($relation_data as $relation_dato) {
                    if (empty($relation_data_by_ids[$relation_dato[$field]])) {
                        $relation_data_by_ids[$relation_dato[$field]] = [];
                    }

                    $related_data[] = $relation_dato[$other];
                    $relation_data_by_ids[$relation_dato[$field]][] = $relation_dato[$other];
                }

                // Prendo le preview dei record relazionati
                if (!empty($related_data)) {
                    //if entity is soft deletable, remove filter here to access deleted records in previews
                    $other_entity_table = $this->getEntity($other_table);
                    $entityCustomActions = empty($other_entity_table['entity_action_fields']) ? [] : json_decode($other_entity_table['entity_action_fields'], true);
                    $where_related_data = ["{$other_table}.{$other} IN (" . implode(',', $related_data) . ")"];
                    if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
                        $where_related_data[] = "({$entityCustomActions['soft_delete_flag']} = 1 OR 1=1)";

                    }
                    $where_related_data_str = implode(' AND ', $where_related_data);
                    $related_data_preview = $this->getEntityPreview($other_table, $where_related_data_str);
                    //debug($related_data_preview);
                    foreach ($data['data'] as $key => $dato) {
                        if (isset($relation_data_by_ids[$dato[$field]])) {
                            foreach ($relation_data_by_ids[$dato[$field]] as $related_value) {

                                // Se il campo non è un array per il momento non ho soluzioni migliori se non farlo diventare un array vuoto
                                // perché in effetti non dovrebbe mai essere pieno
                                if (array_key_exists($related_value, $related_data_preview)) {
                                    if (!is_array($data['data'][$key][$field_name_for_relation_values])) {
                                        $data['data'][$key][$field_name_for_relation_values] = [];
                                    }

                                    $data['data'][$key][$field_name_for_relation_values][$related_value] = $related_data_preview[$related_value];
                                }
                            }
                        }
                    }
                }
            }

            // Recupero le eventuali fake relations, cioè tutti i fields con
            // fields_ref che hanno fields_type o VARCHAR o TEXT
            $fake_relations_fields = array_filter($data['visible_fields'], function ($field) {
                return $field['fields_ref'] && in_array($field['fields_type'], array('VARCHAR', 'TEXT'));
            });

            $names = [];
            foreach ($fake_relations_fields as $field) {
                $name = $field['fields_name'];
                $related = $field['fields_ref'];

                if (in_array($name, $names)) {
                    continue;
                }

                $names[] = $name;
                $fake_relation_ids = [];
                foreach ($data['data'] as &$_data) {
                    $_data[$name] = $_data[$name] ? explode(',', $_data[$name]) : [];
                    if ($_data[$name]) {
                        $fake_relation_ids = array_merge($fake_relation_ids, $_data[$name]);
                    }
                }

                $fullData = [];
                if (!empty($fake_relation_ids)) {
                    $imploded_fake_relation_ids = implode(',', $fake_relation_ids);
                    $frEntity = $this->getEntity($related);
                    //debug('test');
                    $qFullData = $this->get_data_simple_list($frEntity['entity_id'], "{$related}_id IN ({$imploded_fake_relation_ids})", ['depth' => $depth - 1]);
                    //debug('test2');
                    $fullData = array_combine(array_key_map($qFullData, "{$related}_id"), $qFullData);
                }

                // E li inserisco al loro posto
                foreach ($data['data'] as &$_data) {
                    $_data[$name] = array_intersect_key($fullData, array_flip($_data[$name]));
                }
            }

            return $data['data'];
        }, $tags);
    }

    /**
     * Esegui una query specificando l'entità oppure utilizzando l'eventuale
     * entity bindata alla crmentity
     *
     * @param int|string $entity_id
     * @param string|array $where
     * @param array $options        Le opzioni della query. Opzioni valide:
     *      - select [array]
     *      - count [bool]
     *      - extra_data [bool]
     *      - depth [int]
     *      - limit
     *      - offset
     *      - order_by
     * @return array|int
     *
     * @throws Exception
     */
    public function get_data_simple_list($entity_id = null, $where = null, array $options = [])
    {
        //TODO: cache
        $key = 'apilib/' . __METHOD__ . ':' . md5(serialize(func_get_args()));
        $tags = $this->mycache->buildTagsFromEntity($entity_id);


        if (!$entity_id) {

            throw new Exception("Impossibile eseguire la query: entità non specificata.");



        }

        return $this->getFromCache($key, function () use ($entity_id, $where, $options) {


            // Params extraction
            $count = array_get($options, 'count', false);
            $extra_data = array_get($options, 'extra_data', false);
            $depth = array_get($options, 'depth', 2);
            $eval_cachable_fields = array_get($options, 'eval_cachable_fields', []);



            // =================
            $dati = $this->getEntityFullData($entity_id);
            $this->buildSelect($dati, $options);




            $this->buildWhere($where);
            $this->buildLimitOffsetOrder($options);
            $this->buildGroupBy($options);

            // Save list of joined entity, to avoid double joins...
            $this->db->from($dati['entity']['entity_name']);
            $joined = array($dati['entity']['entity_name']);
            $to_join_later = [];



            $permission_entities = [$entity_id]; // Lista delle entità su cui devo applicare i limiti

            //Join entities
            foreach ($dati['visible_fields'] as $key => $campo) {
                if (count($joined) >= 60) {
                    break;
                }
                //$leftJoinable = (empty($campo['fields_ref_auto_left_join']) or $campo['fields_ref_auto_left_join'] == DB_BOOL_TRUE);
                $leftJoinable = ($campo['fields_ref_auto_left_join'] == DB_BOOL_TRUE && $this->entityExists($campo['fields_ref']));

                // I campi che hanno un ref li join solo se non sono in realtà legati a delle relazioni Se invece sono delle relazioni faccio select dei dati
                if ($campo['fields_ref'] && $leftJoinable && !in_array($campo['fields_ref'], $dati['relations'])) {
                    if (in_array($campo['fields_ref'], $joined)) {
                        // Metto nella lista dei join later
                        $to_join_later[$campo['fields_name']] = $campo['fields_ref'];
                    } else {
                        if ($campo['fields_ref'] == 'rel_impianto_immagine') {
                            // debug($dati['entity']['entity_name']);
                            // debug($dati['relations']);
                        }

                        $this->db->join($campo['fields_ref'], "{$campo['fields_ref']}.{$campo["fields_ref"]}_id = {$campo['entity_name']}.{$campo['fields_name']}", "left");
                        array_push($joined, $campo['fields_ref']);

                        // Devo fare il controllo dei limiti sui field ref
                        $ent = $this->getEntity($campo['fields_ref'], false);

                        if ($ent && !in_array($ent['entity_id'], $permission_entities)) {
                            $permission_entities[] = $ent['entity_id'];
                        }
                    }
                }
            }

            // =====================================================================
            // QUERY OUT - COUNT
            // ---
            // Se ho un count allora non procedo, ritorno direttamente il risultato
            // richiesto (in base al fatto che voglia gli extra_data o meno)
            // =====================================================================
            if ($count) {
                $dati['data'] = $this->db->count_all_results();
                return $extra_data ? $dati : $dati['data'];
            }

            // =====================================================================
            // QUERY OUT - RESULTS
            // ---
            // Qui invece devo ritornare dei risultati, quindi mi assicuro che la
            // query sia andata a buon fine
            // =====================================================================
            //debug('test');
            $qResult = $this->db->get();

            if (!$qResult instanceof CI_DB_result) {
                // Errore, la query
                throw new Exception('Si è verificato un errore estraendo i dati' . PHP_EOL . 'Ultima query:' . PHP_EOL . $this->db->last_query());
            }

            $dati['data'] = $qResult->result_array();

            $qResult->free_result();

            // =====================================================================
            // SUPPORTO MULTI-JOIN
            // ---
            // Un'entità (A) può essere joinata più di una volta ad un'altra entità
            // (B) mediante altrettanti campi (ab1 e ab2). Non posso fare tutto in
            // una query perché dovrei usare degli alias e, per retrocompatibilità,
            // questo non è possibile (tutte le query inserite scoppierebbero).
            // Quindi nella query appena fatta ho joinato 1 solo campo, mentre gli
            // altri campi li "joino" ora. Non si tratta di un vero e proprio join,
            // in quanto devo estrarre manualmente i dati.
            // =====================================================================
            if ($dati['data'] && $depth > 0) {
                foreach ($to_join_later as $main_field => $sub_entity_name) {
                    $sub_entity = $this->getEntity($sub_entity_name);

                    $main_field_values = $this->buildWhereInList(array_filter(array_key_map($dati['data'], $main_field)));

                    if (!$main_field_values) {
                        continue;
                    }

                    // Ritrova i dati - jData sono i dati grezzi, mentre mergeable
                    // sono i dati pronti ad essere uniti ai dati principali
                    // debug($main_field);
                    // debug("{$sub_entity_name}.{$sub_entity_name}_id IN ({$main_field_values})");
                    $jData = $this->get_data_simple_list($sub_entity['entity_id'], "{$sub_entity_name}.{$sub_entity_name}_id IN ({$main_field_values})", ['depth' => $depth - 1]);

                    $mergeable = [];

                    foreach ($jData as $record) {
                        // Rimappo ogni valore in modo da avere il main field
                        // anteposto al vero field. Quindi se ho
                        // messaggi con campo messaggi_utente che ha ref a
                        // utenti, ogni campo recuperato da questa join sarà
                        // rinominato in messaggi_utente_utenti_*
                        $mergeable[$record[$sub_entity_name . '_id']] = array_combine(array_map(function ($key) use ($main_field) {
                            return $main_field . '_' . $key;
                        }, array_keys($record)), array_values($record));
                    }

                    foreach ($dati['data'] as $k => $record) {
                        $id = $record[$main_field];
                        $dati['data'][$k] = array_merge($record, array_get($mergeable, $id, []));
                    }
                }
            }

            return $extra_data ? $dati : $dati['data'];
        }, $tags);
    }

    /**
     * Estrai a partire dai fulldata di un'entità e da un array di condizioni
     * select, l'eventuale select da usare nella query. $entityFullData è
     * passato per riferimento dato che dev'essere aggiornato con i campi delle
     * entità joinate
     *
     * @param array $entityFullData
     * @param array $options
     */
    private function buildSelect(array &$entityFullData, array $options = [])
    {
        // Inizializzo i fields: se ho specificato una select allora sono a
        // posto, altrimenti li autocalcolo in base ai fields entità
        $visible_fields = array_get($options, 'select', []);

        $depth = array_get($options, 'depth', 2);

        //debug($depth);

        $eval_cachable_fields = array_get($options, 'eval_cachable_fields', []);

        $entityName = $entityFullData['entity']['entity_name'];

        if (!$visible_fields) {
            foreach ($entityFullData['visible_fields'] as $campo) {
                // Aggiungo il campo alla select
                $visible_fields[] = sprintf('%s.%s', $campo['entity_name'], $campo['fields_name']);

                // Ciclo i campi uno ad uno e se:
                // -- sono campi semplici non faccio altro che aggiungere il nome
                // del field alla mia select ($visible_fields)
                // -- altrimenti se sono field ref devo estrarre a sua volta i
                // suoi campi (valutare chiamate ricorsive). ATTENZIONE CHE IL
                // CAMPO NON DEV'ESSERE RELAZIONE, perchè lo gestisco
                // diversamente

                $hasFieldRef = (bool) $campo['fields_ref'];
                $isRelation = $hasFieldRef && in_array($campo['fields_ref'], $entityFullData['relations']);
                $isJoinable = ($campo['fields_ref_auto_left_join'] == DB_BOOL_TRUE && $this->entityExists($campo['fields_ref']));

                if ($hasFieldRef && $isJoinable) {
                    $entity = $this->getEntity($campo['fields_ref']);
                    if ($isRelation) {
                        //debug($entity);
                    } else {
                        if ($entityName == 'subscriptions' && $entity['entity_name'] == 'currencies') {
                            //debug($depth,true);
                        }



                        foreach ($this->getVisibleFields($entity['entity_id'], $depth - 1) as $supfield) {

                            $visible_fields[] = sprintf('%s.%s', $supfield['entity_name'], $supfield['fields_name']);
                            $entityFullData['visible_fields'][] = $supfield;
                        }


                    }
                }
            }
        }

        // AGGIUNGO A PRESCINDERE I CAMPI DI UNA RELATION
        if ($entityFullData['entity']['entity_type'] == ENTITY_TYPE_RELATION) {
            $relation = $this->getRelationByName($entityFullData['entity']['entity_name']);
            // debug($relation, true);
            $visible_fields[] = $relation['relations_field_1'];
            $visible_fields[] = $relation['relations_field_2'];
        }

        // Mi assicuro che l'id sia contenuto ed eventualmente rimuovo i
        // duplicati
        array_unshift($visible_fields, sprintf($entityName . '.%s_id', $entityName));




        $this->db->select(array_unique($visible_fields));



        //Aggiungo eventuali eval cachable
        $eval_fields = [];
        foreach ($eval_cachable_fields as $eval_field) {
            if ($eval_field['grids_fields_eval_cache_type'] == 'query_equivalent') {
                $eval_fields[] = $eval_field['grids_fields_eval_cache_data'] . ' AS ' . url_title($eval_field['grids_fields_column_name'], '_', true);
            }
        }

        if (!empty($eval_fields)) {
            //Rimuovo la scritta "SELECT " davanti
            $select_str = $this->db->get_compiled_select();
            $select_str = str_ireplace("SELECT ", '', $select_str);

            $this->db->select($select_str . ',' . implode(',', $eval_fields), false); //Sugli eval cachable, presuppongo non ci sia bisogno di escape sql.
        }

        if ($entityFullData['entity']['entity_name'] == 'timesheet') {
            //debug($this->db->get_compiled_select());
            //debug($entityFullData,true);
        }
    }

    /**
     * Compila la clausola where della query
     * @param string|array $where
     */
    private function buildWhere($where)
    {
        // Mi assicuro che il where stringa contenga altre cose oltre che parentesi, spazi bianchi, ecc...
        if (is_string($where) && trim($where, " \t\n\r\0\x0B()")) {
            // Attenzione!! Se il primo e l'ultimo carattere sono parentesi tonde,
            // allora non serve wrappeggiare il where stringhiforme perché è già
            // wrappeggiato in codesta maniera
            $this->db->where(($where[0] === '(' && $where[strlen($where) - 1] === ')') ? "({$where})" : $where, null, false); // null: il valore, false: NON FARE ESCAPE
        } elseif (is_array($where) && count($where) > 0) {
            // Attenzione!! Devo distinguere da where con chiave numerica a
            // quelli con chiave a stringa: dei primi ignoro la chiave, mentre
            // dei secondi faccio un where(key, value);
            $func = function ($value, $key) {
                if (is_numeric($key)) {
                    $this->db->where($value, null, false); // non escapare nemmeno qui
                } elseif (is_string($key)) {
                    $this->db->where($key, $value);
                }
            };

            //array_filter does not work well. It removes also filters with value 0 or '0'. Ex.: WHERE field=0 will be removed.
            //so we used a custom function
            $func_empty = function ($value) {
                if (empty($value) && $value !== 0 && $value !== '0') {
                    return false;
                } else {
                    return true;
                }
            };

            $where = array_filter($where, $func_empty);
            array_walk($where, $func);
        }
    }
    private function buildGroupBy(array $options)
    {
        $group_by = array_get($options, 'group_by', null);

        if ($group_by !== null) {
            $this->db->_protect_identifiers = false;
            $this->db->group_by($group_by);
            $this->db->_protect_identifiers = true;
        }
    }
    /**
     * Compila la sezione LIMIT-OFFSET e ORDER BY della query
     * @param array $options
     */
    private function buildLimitOffsetOrder(array $options)
    {
        $limit = array_get($options, 'limit', null);
        $offset = array_get($options, 'offset', 0);
        $order_by = array_get($options, 'order_by', null);
        $count = array_get($options, 'count', false);

        if ($limit !== null) {
            $this->db->limit($limit);
        }
        if ($offset > 0) {
            $this->db->offset($offset);
        }

        if ($order_by !== null && !$count) {
            $this->db->_protect_identifiers = false;
            $this->db->order_by($order_by);
            $this->db->_protect_identifiers = true;
        }
    }

    /**
     * Converte una lista di valori in una lista valida per il where in.
     *  [1, 2, 3, 4] => '1','2','3','4'
     *
     *
     * @param array $values
     * @return string
     */
    private function buildWhereInList(array $values)
    {
        $out = implode("','", array_unique(array_map(function ($value) {
            return str_replace("'", "''", $value);
        }, array_filter($values))));
        return $out ? "'{$out}'" : '';
    }

    /**
     * Ritrova i dati completi da un'entità
     *
     * @param int|string $entity
     * @return array
     */
    public function getEntityFullData($entity)
    {
        return array(
            'entity' => $this->getEntity($entity),
            'relations' => array_key_map($this->getEntityRelations($entity), 'relations_name'),
            'fields_ref_by' => $this->getFieldsRefBy($entity),
            'visible_fields' => $this->getVisibleFields($entity),
        );
    }

    /**
     *  Utility methods
     */
    public function getEntityPreview($entityIdentifier, $where = null, $limit = null, $offset = 0, $options = [])
    {

        $depth = array_get($options, 'depth', 2);

        $key = sprintf('apilib/previews-%s', md5(serialize(func_get_args())));
        $entity = $this->getEntity($entityIdentifier);

        $entity_preview = ($entity['entity_preview_custom'] ?? false);
        if (!$entity_preview) {
            $entity_preview = ($entity['entity_preview_base'] ?? false);
        }



        $entity_name = $entity['entity_name'];
        $tags = $this->mycache->buildTagsFromEntity($entity_name);
        return $this->getFromCache($key, function () use ($entityIdentifier, $where, $limit, $offset, $entity, $entity_name, $entity_preview, $depth) {

            $previewFields = $this->getEntityPreviewFields($entityIdentifier);

            //debug($previewFields);

            $entity_id = $entity['entity_id'];

            $select = array_key_map($previewFields, 'fields_name');

            //Aggiungo ordinamento qualora l'entità ne avesse configurato uno di default
            $entityCustomActions = empty($entity['entity_action_fields']) ? [] : json_decode($entity['entity_action_fields'], true);

            if (isset($entityCustomActions['order_by_asc'])) {
                $order_by = $entityCustomActions['order_by_asc'] . ' ASC';
            } elseif (isset($entityCustomActions['order_by_desc'])) {
                $order_by = $entityCustomActions['order_by_desc'] . ' DESC';
            } else {
                $order_by = null;
            }

            // Filtro per soft-delete se non viene specificato questo filtro nel where della grid
            if (array_key_exists('soft_delete_flag', $entityCustomActions) && !empty($entityCustomActions['soft_delete_flag'])) {
                //Se nel where c'è già un filtro specifico sul campo impostato come soft-delete, ignoro. Vuol dire che sto gestendo io il campo delete (es.: per mostrare un archivio o un history...)

                // Where can be an array, so it's not correct to check online the where string conditions, but consider it different...
                if (is_array($where)) {
                    if (!array_key_exists($entityCustomActions['soft_delete_flag'], $where)) {
                        if (empty($where)) {
                            $where = ["({$entityCustomActions['soft_delete_flag']} =  '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)"];
                        } else {
                            $where[] = "({$entityCustomActions['soft_delete_flag']} =  '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)";
                        }
                    }
                } else { // If where is passed as string i can use stripos to check if soft_delete field has been already passed trouhgt this function and has not to be forced
                    if (stripos($where, $entityCustomActions['soft_delete_flag']) === false) {
                        if (empty($where)) {
                            $where = "({$entityCustomActions['soft_delete_flag']} =  '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)";
                        } else {
                            $where .= " AND ({$entityCustomActions['soft_delete_flag']} =  '" . DB_BOOL_FALSE . "' OR {$entityCustomActions['soft_delete_flag']} IS NULL)";
                        }
                    }
                }
            }

            $records = $this->get_data_simple_list($entity_id, $where, compact('limit', 'offset', 'select', 'order_by', 'depth'));

            /* Build preview */
            $result = [];
            foreach ($records as $record) {
                $id = array_get($record, $entity_name . '_id');
                if ($entity_preview) {
                    $result[$id] = str_replace_placeholders($entity_preview, $record, true, true);
                } else {

                    $preview = "";

                    foreach ($previewFields as $field) {
                        $rawval = array_get($record, $field['fields_name']);

                        $val = ($field['fields_multilingual'] === DB_BOOL_TRUE) ? $this->translateValue($rawval) : $rawval;

                        // Se non abbiamo nessuna lingua impostata, translateValue
                        // mi ritorna un array, generando un warning array-to-string
                        // nell'append successivo - quindi per ora implode sulla
                        // virgola
                        if (is_array($val)) {
                            $val = implode(',', $val);
                        }

                        // Se vuoto (null o stringa vuota - voglio tenere eventuali 0),
                        // allora lo skippo
                        if (is_null($val) or $val === '') {
                            continue;
                        }

                        $preview .= "{$val} ";
                    }

                    $result[$id] = (trim($preview) || trim($preview) === '0') ? trim($preview) : "ID #{$id}";
                }
            }

            return $result;
        }, $tags);
    }

    /**
     * Traduci un valore secondo l'array delle lingue corrente
     *
     * @param string $jsonEncodedValue rappresentazione json del valore
     * @return mixed
     */
    public function translateValue($jsonEncodedValue)
    {
        $transVal = json_decode($jsonEncodedValue, true);

        // Se non ci sono lingue in lista, allora lascia il valore json decoded
        // cioè l'array con tutte le lingue (o eventualmente un null)
        if (!$this->languages) {
            return $transVal;
        }

        // Cicla tutte le lingue, quelle prima sono quelle che hanno maggiore
        // priorità, al primo valore trovato (non nullo e non stringa vuota - 0
        // va bene) ritornalo. Se nessun valore viene trovato allora torna null
        foreach ($this->languages as $langId) {
            if (isset($transVal[$langId]) && $transVal[$langId] !== '') {
                return $transVal[$langId];
            }
        }

        return null;
    }

    // =========================================================================
    // Schema Cache Control
    // =========================================================================
    public function reloadSchemaCache()
    {
        $this->_schemaCache = null;
        $this->mycache->delete(self::SCHEMA_CACHE_KEY);
        $this->buildSchemaCacheIfNotValid();
    }


    protected function buildSchemaCacheIfNotValid()
    {
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('database_schema')) {
            $this->_schemaCache = $this->mycache->get(self::SCHEMA_CACHE_KEY);
            if ($this->_schemaCache) {
                return;
            }
        }

        $entities = $this->createDataMap($this->db->get('entity')->result_array(), 'entity_id');
        $fields = $validations = [];

        // ==== Get entity sub-data
        $this->db->join('entity', 'entity.entity_id = fields.fields_entity_id')
            ->join('fields_draw', 'fields.fields_id = fields_draw.fields_draw_fields_id', 'left') // LEFT JOIN perché a quanto pare non tutti i field hanno un fields_draw.
            ->order_by('fields_name');
        $_fields = $this->createDataMap($this->db->get('fields')->result_array(), 'fields_id');

        $_validations = $this->createDataMap($this->db->get('fields_validation')->result_array(), 'fields_validation_fields_id');

        // Fields validation rules
        foreach ($_validations as $rule) {
            $validations[$rule['fields_validation_fields_id']][] = $rule;
        }

        // Fields
        foreach ($_fields as $field) {
            $fields[$field['fields_entity_id']][] = $field;
        }

        // Relations
        $relations = $this->db->get('relations')->result_array();
        $relbyname = $relbyent = [];

        foreach ($relations as $relation) {
            $relbyname[$relation['relations_name']] = $relation;
            $relbyent[$relation['relations_table_1']][] = $relation;

            //Also reversable relations are valid!

            $reverse_relation = [
                'relations_name' => $relation['relations_name'],
                'relations_table_1' => $relation['relations_table_2'],
                'relations_table_2' => $relation['relations_table_1'],
                'relations_field_1' => $relation['relations_field_2'],
                'relations_field_2' => $relation['relations_field_1'],
                'relations_type' => $relation['relations_type'],
                'relations_module' => $relation['relations_module']
            ];
            $relbyent[$reverse_relation['relations_table_1']][] = $reverse_relation;
        }

        // Build the cached array
        $this->_schemaCache = [
            'entity_names' => $this->createDataMap($entities, 'entity_name', 'entity_id'),
            'entities' => $entities,
            'fields' => $fields,
            'validations' => $validations,
            'relations' => ['by_name' => $relbyname, 'by_entity' => $relbyent],
        ];
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('database_schema')) {
            // And persist it
            $this->mycache->save(self::SCHEMA_CACHE_KEY, $this->_schemaCache, 3600 * 24, [self::SCHEMA_CACHE_KEY]); // 1h * 24 <= Salva cache per un giorno
        }
    }

    /**
     * Crea un array di mappatura a partire dai dati. From e To sono le
     * sottochiavi sulle quali eseguire la mappatura (array risultante avrà i
     * valori dei from sulle chiavi e i valori di to sui rispettivi valori).
     * Se to è null, allora viene preso l'intero record
     *
     * @param array $data
     * @param string $from
     * @param string|null $to
     */
    private function createDataMap(array $data, $from, $to = null)
    {
        return array_combine(
            array_key_map($data, $from),
            $to ? array_key_map($data, $to) : $data
        );
    }
    public function entityExists($id)
    {
        return $this->getEntity($id, false);
    }
    /**
     * Ritrova entità
     * @param mixed $id
     */
    public function getEntity($id, $throw_exception = true)
    {
        if (is_array($id) && isset($id['entity_id'])) {
            $id = $id['entity_id'];
        }

        if (!is_numeric($id)) {
            if (empty($this->_schemaCache['entity_names'][$id])) {
                if ($throw_exception) {
                    throw new Exception(sprintf("Entity '%s' does not exist", $id));
                } else {
                    return false;
                }
            }

            $id = $this->_schemaCache['entity_names'][$id];
        }

        if (empty($this->_schemaCache['entities'][$id])) {
            if ($throw_exception) {
                throw new Exception(sprintf("Entity '%s' does not exist", $id));
            } else {
                return false;
            }
        }

        return $this->_schemaCache['entities'][$id];
    }

    /**
     * Ritrova TUTTI i fields di un'entità
     * @param int|string $entity
     */
    public function getFields($entity)
    {
        if (!is_numeric($entity)) {
            $entity = $this->getEntity($entity)['entity_id'];
        }

        if (empty($this->_schemaCache['fields'][$entity])) {
            // L'entità esiste, ma non ha campi
            return [];
        }

        return $this->_schemaCache['fields'][$entity];
    }

    /**
     * Ritrova tutte le regole di validazione per un dato field id
     *
     * @param int $fieldId
     */
    public function getValidations($fieldId)
    {
        // Ci sono regole di validazione per il campo? [Assumo esista]
        return empty($this->_schemaCache['validations'][$fieldId]) ? [] : $this->_schemaCache['validations'][$fieldId];
    }

    /**
     * Get all fields that are right joined to the entity passed
     *
     * @param string|int $entity
     * @return array
     */
    public function getFieldsRefBy($entity, $right_join = true)
    {
        if ($right_join) {
            $column_join = 'fields_ref_auto_right_join';
        } else {
            $column_join = 'fields_ref_auto_left_join';
        }
        if (!array_key_exists($column_join, $this->_fields_ref_by)) {
            // debug($column_join);
            // debug($this->_fields_ref_by);
            $this->_fields_ref_by[$column_join] = [];

            $_allFieldsRefBy = $this->db->query("
                SELECT entity_id, entity_name, fields_name, fields_type, fields_ref
                FROM fields JOIN entity ON entity_id = fields_entity_id
                WHERE fields_ref IS NOT NULL AND fields_ref != '' AND $column_join = '" . DB_BOOL_TRUE . "'
            ")->result_array();


            foreach ($_allFieldsRefBy as $field) {
                $this->_fields_ref_by[$column_join][$field['fields_ref']][] = $field;
            }
            //debug($this->_fields_ref_by);
        }

        $ename = $this->getEntity($entity)['entity_name'];
        return array_get($this->_fields_ref_by[$column_join], $ename, []);
    }

    /**
     * Ottieni i field visibili
     * @param type $entity
     * @return type
     */
    public function getVisibleFields($entity, $depth = 1)
    {
        if ($depth <= 0) {
            return [];
        }
        if (!array_key_exists($entity, $this->_visible_fields) || $depth > 1) {
            $this->_visible_fields[$entity] = array_filter($this->getFields($entity), function ($item) {
                return $item['fields_draw_display_none'] !== DB_BOOL_TRUE;
            });

            while ($depth > 1) {
                $depth--;
                foreach ($this->_visible_fields[$entity] as $field) {

                    $isJoinable = $field['fields_ref_auto_left_join'] == DB_BOOL_TRUE;
                    if (!empty($field['fields_ref']) && $isJoinable) {

                        $this->_visible_fields[$entity] = array_merge($this->_visible_fields[$entity], $this->getVisibleFields($field['fields_ref'], $depth));
                    }
                }
            }
        }
        if ($entity == 20) {

            //debug(count($this->_visible_fields[$entity]));
        }
        return $this->_visible_fields[$entity];
    }

    /**
     * Ritrova relazioni da entità
     *
     * @param string|int|array $entity
     * @return array
     */
    public function getEntityRelations($entity)
    {
        $e_name = $this->getEntity($entity)['entity_name'];

        return array_get($this->_schemaCache['relations']['by_entity'], $e_name, []);
    }

    /**
     * Ottieni una relazione a partire dal suo nome
     * @param string $name
     * @return array
     */
    public function getRelationByName($name)
    {
        return array_get($this->_schemaCache['relations']['by_name'], $name, []);
    }

    /**
     * Data un'entità ottieni i suoi campi di preview
     *
     * @param string|int $entity
     * @return array
     */
    public function getEntityPreviewFields($entity)
    {
        $e = $this->getEntity($entity);
        $eid = $e['entity_id'];
        $entity_name = $e['entity_name'];
        $entity_preview = ($e['entity_preview_custom'] ?? false);
        if (!$entity_preview) {
            $entity_preview = ($e['entity_preview_base'] ?? false);
        }

        //Check if entity_preview_base or custom is set
        if ($entity_preview) {
            //debug('test',true);
            return [];
        } else {
            //$tags = $this->mycache->buildTagsFromEntity($entity_name);
            $tags = []; //Preview fields does not depend on data changes in entity, so keep these always in cache, event if any data changes
            return $this->getFromCache("apilib/preview-fields-{$eid}", function () use ($eid) {
                $preview = [];
                $fields = $this->getVisibleFields($eid);

                foreach ($fields as $field) {

                    // Non interessato alle non preview
                    if ($field['fields_preview'] !== DB_BOOL_TRUE) {
                        continue;
                    }

                    // Sono preview...
                    // Field normale? Inseriscilo nei miei campi preview
                    if (!$field['fields_ref']) {
                        $preview[] = $field;
                        continue;
                    }

                    // Caso `complesso`: preview in un field ref - prendo tutti i campi
                    // preview del ref
                    $subfields = $this->getVisibleFields($field['fields_ref']);
                    foreach ($subfields as $subfield) {
                        if ($subfield['fields_preview'] == DB_BOOL_TRUE && !$subfield['fields_ref']) {
                            $preview[] = $subfield;
                        }
                    }
                }
                //Sort by field id
                $ids = array_column($preview, 'fields_id');
                array_multisort($ids, SORT_ASC, $preview);
                //debug($preview,true);
                return $preview;
            }, $tags);
        }




    }

    /**
     * Risolve un'entità referenziata dal field passato: se è un'entità semplice
     * allora questo metodo ritorna l'equivalente di
     *              Crmentity::getEntity($field[fields_ref])
     * mentre se è una relazione, allora viene risolta l'entità relazionata e
     * ritornata quella
     *
     * @param string|int|array $field
     * @return array
     */
    public function getReferencedEntity($field)
    {
        // Step 1: Risolvo il field - entità di appartenenza e referenziata
        if (is_numeric($field)) {
            $field = $this->db->query("SELECT * FROM fields WHERE fields_id = ?", [$field])->row_array();
        } elseif (is_string($field)) {
            $field = $this->db->query("SELECT * FROM fields WHERE fields_name = ?", [$field])->row_array();
        } elseif (!is_array($field) or !array_key_exists('fields_ref', $field) or !array_key_exists('fields_entity_id', $field)) {
            throw new InvalidArgumentException("Impossibile riconoscere il campo specificato");
        }

        // Step 2: Risolvo l'eventuale entità referenziata
        if (!$field['fields_ref']) {
            // nessun'entità referenziata
            return [];
        }

        $referencedEntity = $this->getEntity($field['fields_ref']);

        if ($referencedEntity['entity_type'] == ENTITY_TYPE_RELATION) {

            // Step 3a: il field punta ad una relazione, quindi devo estrarre la
            // relazione e prendere l'entità puntata
            $relation = $this->getRelationByName($referencedEntity['entity_name']);
            $refererEntity = $this->getEntity($field['fields_entity_id']);

            if ($relation['relations_table_1'] == $refererEntity['entity_name']) {
                // Il mio referer corrisponde all'entità 1, quindi devo
                // risolvere la tabella 2
                return $this->getEntity($relation['relations_table_2']);
            } elseif ($relation['relations_table_2'] == $refererEntity['entity_name']) {
                // Il mio referer corrisponde all'entità 2, quindi devo
                // risolvere la tabella 1
                return $this->getEntity($relation['relations_table_2']);
            }

            // Non ho risolto nulla...
            return [];
        } else {
            return $referencedEntity;
        }
    }

    // Get default grid id by entity
    public function getDefaultGrid($entity)
    {
        if (!array_key_exists($entity, $this->_default_grids)) {
            $entity_id = $this->getEntity($entity)['entity_id'];
            $query = $this->db->get_where(
                'grids',
                array(
                    'grids_entity_id' => $entity_id,
                    'grids_default' => DB_BOOL_TRUE,
                )
            );

            $this->_default_grids[$entity] = $query->row_array();
            return $this->_default_grids[$entity];
        } else {
            return $this->_default_grids[$entity];
        }
    }

    // Get default form id by entity
    public function getDefaultForm($entity)
    {
        if (!array_key_exists($entity, $this->_default_forms)) {
            $entity_id = $this->getEntity($entity)['entity_id'];
            $query = $this->db->get_where(
                'forms',
                array(
                    'forms_entity_id' => $entity_id,
                    'forms_default' => DB_BOOL_TRUE,
                )
            );

            $this->_default_forms[$entity] = $query->row_array();
            return $this->_default_forms[$entity];
        } else {
            return $this->_default_forms[$entity];
        }
    }

    // Return all entities
    public function getAllEntities()
    {
        $entities = $this->db->order_by('entity_name')->get('entity')->result_array();
        return $entities;
    }
    public function getEntities()
    {
        return $this->getAllEntities();
    }
}
