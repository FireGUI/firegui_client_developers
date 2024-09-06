<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * @property-read Crmentity $crmentity
 */
class Datab extends CI_Model
{
    public const LANG_SESSION_KEY = 'master_crm_language';
    public const CACHE_TIME = 3600;
    private $_accessibleLayouts = [];
    private $_forwardedLayouts = [];
    private $_accessibleEntityLayouts = [];
    private $_hooks = null;

    /* Multilingual system */
    private $_currentLanguage;
    private $_defaultLanguage;
    private $_languages = [];

    private $_default_language_id;
    private $_default_language;

    private $_grids_data = [];

    private $_layout_boxes_benchmark = [];

    public $executed_hooks = [];

    private $_permissions = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('crmentity');

        $this->preloadLanguages();
        $this->prefetchMyAccessibleLayouts();
    }

    protected function prefetchMyAccessibleLayouts()
    {
        $userId = (int) $this->auth->get('id');

        $cache_key = "database_schema/datab.build_layout.accessible.{$userId}." . md5(serialize($_GET) . serialize($_POST) . serialize($this->session->all_userdata()));
        if (!($dati = $this->mycache->get($cache_key))) {
            $dati['accessibleLayouts'] = $dati['accessibleEntityLayouts'] = $dati['forwardedLayouts'] = [];
            $accessibleLayouts = $this->db->query("
                SELECT layouts_id, layouts_is_entity_detail, layouts_entity_id
                FROM layouts
                WHERE (
                    NOT EXISTS (SELECT 1 FROM unallowed_layouts WHERE layouts_id = unallowed_layouts_layout AND unallowed_layouts_user = ?) AND
                    (
                        layouts_entity_id IS NULL OR
                        EXISTS (SELECT 1 FROM permissions WHERE permissions_admin AND permissions_user_id = ?) OR
                        NOT EXISTS (
                            SELECT 1
                            FROM permissions_entities
                            JOIN permissions ON permissions_entities_permissions_id = permissions_id
                            WHERE layouts_entity_id = permissions_entities_entity_id AND permissions_user_id = ?
                        ) OR
                        EXISTS (
                            SELECT permissions_entities_entity_id
                            FROM permissions_entities JOIN permissions ON permissions_entities_permissions_id = permissions_id
                            WHERE (
                                permissions_user_id = ? AND
                                layouts_entity_id = permissions_entities_entity_id AND
                                permissions_entities_value <> ?
                            )
                        )
                    )
                )
                ORDER BY layouts_id
            ", [$userId, $userId, $userId, $userId, PERMISSION_NONE])->result_array();
            $dati['accessibleLayouts'] = array_combine(array_key_map($accessibleLayouts, 'layouts_id'), $accessibleLayouts);

            foreach ($dati['accessibleLayouts'] as $id => $linfo) {
                if ($linfo['layouts_is_entity_detail'] === DB_BOOL_TRUE && !isset($dati['accessibleEntityLayouts'][$linfo['layouts_entity_id']])) {
                    $dati['accessibleEntityLayouts'][$linfo['layouts_entity_id']] = $id;
                }
            }

            if ($dati['accessibleLayouts']) {
                $allEntitiesDetails = $this->db->join('entity', 'layouts_entity_id = entity_id')
                    ->where_not_in('layouts_id', array_keys($dati['accessibleLayouts']))
                    ->get_where('layouts', ['layouts_is_entity_detail' => DB_BOOL_TRUE])
                    ->result_array();

                foreach ($allEntitiesDetails as $layout) {
                    if (isset($dati['accessibleEntityLayouts'][$layout['layouts_entity_id']])) {
                        $dati['forwardedLayouts'][$layout['layouts_id']] = $dati['accessibleEntityLayouts'][$layout['layouts_entity_id']];
                    }
                }
            }
            if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('database_schema')) {
                $this->mycache->save($cache_key, $dati, self::CACHE_TIME);
            }
        }
        $this->_accessibleLayouts = $dati['accessibleLayouts'];
        $this->_accessibleEntityLayouts = $dati['accessibleEntityLayouts'];
        $this->_forwardedLayouts = $dati['forwardedLayouts'];
    }

    /**
     * Metodi per entità e campi
     */
    public function get_entity($entity_id)
    {
        $entity = $this->crmentity->getEntity($entity_id);
        $entity['fields'] = $this->crmentity->getFields($entity_id);
        return $entity;
    }

    public function get_entity_by_name($entity_name)
    {
        $entity = $this->crmentity->getEntity($entity_name);
        $entity['fields'] = $this->crmentity->getFields($entity_name);
        return $entity;
    }
    public function getDataEntityByQuery($entity_id, $query, $input = null, $limit = null, $offset = 0, $orderBy = null, $count = false, $eval_cachable_fields = [], $additional_parameters = [])
    {
        $unique = md5($query);
        $cache_key = "apilib/datab.getDataEntity.{$unique}." . md5(serialize(func_get_args()) . serialize($_GET) . serialize($_POST) . serialize($this->session->all_userdata()));
        if (!($dati = $this->mycache->get($cache_key))) {
            $group_by = array_get($additional_parameters, 'group_by', null);
            //debug($input);
            $where = [];
            if (isset($input['where'])) {
                $where[] = $input['where'];
                unset($input['where']);
            }
            // Preparo il where: accetto sia stringa che array, però dopo questo
            // punto dovrà essere per forza un array di condizioni in AND
            if ($input && !is_array($input)) {
                $input = [$input];
            }
            if (empty($input)) {
                $input = [];
            }
            foreach ($input as $key => $value) {
                if (is_array($value) && is_string($key)) {
                    // La chiave se è stringa indica il nome del campo,
                    // mentre il value se è array (fattibile solo da POST o PROCESS)
                    // fa un WHERE IN
                    $values = "'" . implode("','", $value) . "'";
                    $where[] = "{$key} IN ({$values})";
                } elseif (is_string($key)) {
                    $where[$key] = $value;
                } else {
                    // Ho una chiave numerica che potrebbe essere stata inserita
                    // da pre-process...
                    $where[] = $value;
                }
            }

            $func = function (&$value, $key) {
                if (is_numeric($key)) {
                } elseif (is_string($key)) {
                    $return = "({$key} = {$value})";
                    foreach (['<', '<=', '>', '>=', '=', '<>'] as $operator) {
                        if (stripos($key, $operator) === false) {
                            $return = "({$key} {$value})";
                            break;
                        }
                    }
                    $value = $return;
                }
            };

            array_walk($where, $func);
            $where = $this->apilib->runDataProcessing($entity_id, 'pre-search', $where);

            $query = str_ireplace('{where}', $this->buildWhereString($where, $query), $query);

            if ($count) {
                $query = $this->removeLimitOffsetGroupBy($query);
                $query = str_ireplace('{order_by}', '', $query);
                $query = "SELECT count(1) as c FROM ($query) as foo";
                //$query = $this->replaceSelectWithCount($query);

                $res = $this->db->query($query);
                if ($res->num_rows() == 1) {
                    $out = $res->row()->c;
                } else {
                    $out = 0;
                }
            } else {
                if ($orderBy) {
                    $query = str_ireplace('{order_by_clear}', ",$orderBy", $query);

                    $query = str_ireplace('{order_by}', "ORDER BY $orderBy", $query);
                } else {
                    $query = str_ireplace('{order_by}', '', $query);
                }

                if ($limit) {
                    $query = str_ireplace(['{limit}', '{offset}'], [$limit, $offset], $query);
                } else {
                    $limit = 9999999;
                    $query = str_ireplace(['{limit}', '{offset}'], [$limit, $offset], $query);
                }

                $out = $this->db->query($query)->result_array();
            }
            if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('apilib')) {
                $this->mycache->save($cache_key, $out);
            }
            $dati = $out;
        }
        return $dati;
    }

    /**
     * Summary of replaceSelectWithCount
     * @param mixed $query
     * @throws Exception
     * @return array|string
     */
    public function replaceSelectWithCount($query)
    {
        if (strpos($query, 'SELECT') !== false) {
            $select_identifier = 'SELECT';
        } elseif (strpos($query, 'select') !== false) {
            $select_identifier = 'select';
        } else {
            throw new Exception("String 'SELECT' not found in query '$query';");
        }
        if (strpos($query, 'FROM') !== false) {
            $from_identifier = 'FROM';
        } elseif (strpos($query, 'from') !== false) {
            $from_identifier = 'from';
        } else {
            throw new Exception("String 'FROM' not found in query '$query';");
        }
        $after_select = explode($select_identifier, $query)[1];
        $select_part = explode($from_identifier, $after_select)[0];

        $query_count = str_ireplace($select_part, " COUNT(1) as c ", $query);

        return $query_count;
    }

    /**
     * Summary of removeLimitOffsetGroupBy
     * @param mixed $query
     * @return string
     */

    public function removeLimitOffsetGroupBy($query)
    {
        return explode('LIMIT', $query)[0];
    }

    /**
     * Builds a WHERE clause for a SQL query using the provided conditions.
     *
     * @param array $where An array of conditions for the WHERE clause. Each element in the array should be a string containing a condition, such as 'id = 1' or 'name = "John"'.
     * @param string $query A string containing a SQL query.
     *
     * @return string The modified $query string with the WHERE clause added or appended to it. If the $where array is empty, an empty string is returned.
     */
    public function buildWhereString($where, $query)
    {
        if ($where) {
            if (!$where_pos = stripos($query, 'where ')) {
                $return = " WHERE " . implode(' AND ', $where);
            } else {
                $right_str = explode('{where}', $query)[0];
                $where_str = substr($right_str, $where_pos + 6);
                if (trim($where_str)) {
                    $return = " AND " . implode(' AND ', $where) . " ";
                } else {
                    $return = " " . implode(' AND ', $where) . " ";
                }
            }
        } else {
            $return = '';
        }

        return $return;
    }
    /**
     * Cerca una lista di dati. Wrapper del search/count apilib, ma che tiene
     * conto della sessione del crm e dei permessi
     *
     * @param int|string $entity_id
     * @param string|array $where
     * @param int|null $limit
     * @param int|null $offset
     * @param string $order_by
     * @param int $depth
     * @param bool $count
     * @param array $eval_cachable_fields Eventuale array degli eval fields della grid cachabili. Servono per buildare correttamente il where e l'order by
     * @return array
     */
    public function getDataEntity($entity_id, $where = null, $limit = null, $offset = 0, $order_by = null, $depth = 2, $count = false, $eval_cachable_fields = [], $additional_parameters = [])
    {
        $cache_key = "apilib/datab.getDataEntity.{$entity_id}." . md5(serialize(func_get_args()) . serialize($_GET) . serialize($_POST) . serialize($this->session->all_userdata()));
        if (!($dati = $this->mycache->get($cache_key))) {
            $group_by = array_get($additional_parameters, 'group_by', null);
            // Questo è un wrapper di apilib che va a calcolare i permessi per ogni
            // entità
            $visibleFields = $this->crmentity->getFields($entity_id);

            // Estraggo i campi visibili anche di eventuali tabelle da joinare per
            // calcolarne i permessi
            $permissionEntities = [$entity_id]; // Lista delle entità su cui devo applicare i limiti dei permessi

            foreach ($visibleFields as $k => $campo) {
                if ($campo['fields_ref'] && $this->crmentity->entityExists($campo['fields_ref'])) {
                    $joinEnt = $this->crmentity->getEntity($campo['fields_ref']);
                    $visibleFields = array_merge($visibleFields, $this->crmentity->getFields($joinEnt['entity_id']));
                    in_array($joinEnt['entity_id'], $permissionEntities) or array_push($permissionEntities, $joinEnt['entity_id']);
                }
            }

            // Preparo il where: accetto sia stringa che array, però dopo questo
            // punto dovrà essere per forza un array di condizioni in AND
            if ($where && !is_array($where)) {
                $where = [$where];
            }

            // Applico limiti permessi sul where appena preparato
            $userId = (int) $this->auth->get(LOGIN_ENTITY . '_id');
            $operators = unserialize(OPERATORS);
            $field_limits = $this->db->join('fields', 'limits_fields_id = fields_id')
                ->where_in('fields_entity_id', $permissionEntities)
                ->where_in('limits_operator', array_keys($operators))
                ->get_where('limits', ['limits_user_id' => $userId])
                ->result_array();

            foreach ($field_limits as $flimit) {
                $field = $flimit['fields_name'];
                $op = $flimit['limits_operator'];
                $value = trim($flimit['limits_value']);
                $sql_op = $operators[$op]['sql'];

                // Modifico i value in alcuni casi particolari
                switch ($op) {
                    case 'in':
                        $value = "('" . implode("','", explode(',', $value)) . "')";
                        break;

                    case 'like':
                        $value = "'%{$value}%'";
                        break;
                }

                // Costruisco il where - se non metto l'accettazione dei valori null
                // allora mi è impossibile prendere i valori nulli se viene
                // attivato questo where
                $where[] = "({$field} IS NULL OR {$field} {$sql_op} {$value})";
            }

            // Ok, where pronto, mi resta solo da fare il dispatch ad apilib
            $entity = $this->crmentity->getEntity($entity_id);

            if ($count) {
                $dati = $this->apilib->count($entity['entity_name'], $where, ['group_by' => $group_by]);
            } else {
                $dati = $this->apilib->search($entity['entity_name'], $where, $limit, $offset, $order_by, null, $depth, $eval_cachable_fields, ['group_by' => $group_by]);


            }
            if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('apilib')) {
                $this->mycache->save($cache_key, $dati, self::CACHE_TIME, $this->mycache->buildTagsFromEntity($entity_id));
            }
        }
        //debug($dati, true);
        return $dati;
    }

    public function get_visible_fields($entity_id = null)
    {
        if (!$entity_id) {
            return array();
        }

        $where = (is_numeric($entity_id) ? "entity_id = '{$entity_id}'" : "entity_name = '{$entity_id}'");
        return $this->db->query("
                        SELECT *
                        FROM fields
                            JOIN entity ON entity.entity_id = fields.fields_entity_id
                            LEFT JOIN fields_draw ON fields.fields_id = fields_draw.fields_draw_fields_id
                        WHERE {$where} AND (fields_visible = '" . DB_BOOL_TRUE . "')
                    ")->result_array();
    }

    public function get_field($field_id, $full_data = false)
    {
        if (is_numeric($field_id)) {
            if ($full_data) {
                return $this->db->join('entity', 'fields_entity_id = entity_id', 'LEFT')->join('fields_draw', 'fields_draw_fields_id = fields_id', 'LEFT')->get_where('fields', ['fields_id' => $field_id])->row_array();
            } else {
                return $this->db->query("SELECT * FROM fields LEFT JOIN entity ON (fields_entity_id = entity_id) WHERE fields_id = '{$field_id}'")->row_array();
            }
        } else {
            return $this->get_field_by_name($field_id, $full_data);
        }
    }

    public function get_field_by_name($field_name, $full_data = false)
    {
        if ($full_data) {
            return $this->db->join('entity', 'fields_entity_id = entity_id', 'LEFT')->join('fields_draw', 'fields_draw_fields_id = fields_id', 'LEFT')->get_where('fields', ['fields_name' => $field_name])->row_array();
        } else {
            $slashed = addslashes($field_name);
            return $this->db->query("SELECT * FROM fields LEFT JOIN entity ON (fields_entity_id = entity_id) WHERE fields_name = '{$slashed}'")->row_array();
        }
    }

    /**
     * Forms
     */
    public function get_default_fields_value($fields, $value_id = null)
    {
        $value = $this->input->get_post($fields['fields_name']);
        if ($value === false) {
            $value = null;
        }

        switch ($fields['forms_fields_default_type']) {
            case 'session':
                // Mi aspetto una sintassi di questo tipo: {arr campo} oppure {campo}
                $str = str_replace(array("{", "}"), "", $fields['forms_fields_default_value']);
                $exp = explode(' ', $str);

                if (count($exp) > 1) {
                    // Fix compatibility with old version
                    if ($exp[0] == 'master_crm_login') {
                        $exp[0] = SESS_LOGIN;
                    }
                    $sess_arr = $this->session->userdata($exp[0]);

                    $value = (isset($sess_arr[$exp[1]])) ? $sess_arr[$exp[1]] : '';
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
                $var1 = (isset($exp[1])) ? trim($exp[1]) : null;
                $var2 = (isset($exp[2])) ? trim($exp[2]) : null;
                $var3 = (isset($exp[3])) ? trim($exp[3]) : null;
                $var4 = (isset($exp[4])) ? trim($exp[4]) : null;

                switch ($func) {
                    case '{now_date}':
                        if (!empty($var1)) {
                            $value = date($var1);
                        } else {
                            $value = date("d/m/Y");
                        }

                        break;
                    case '{now_date_time}':
                        $value = date("d/m/Y H:i");
                        break;
                    case '{now_time}':
                        if (!empty($var1)) {
                            $value = date($var1);
                        } else {
                            $value = date("H:i");
                        }
                        break;
                    case '{different_date}':
                        $timeobj = new DateTime();

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var1)) {
                            if (substr($var1, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('P' . str_ireplace('+', '', $var1) . 'D'));
                            } elseif (substr($var1, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('P' . str_ireplace('-', '', $var1) . 'D'));
                            }
                        }

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var2)) {
                            if (substr($var2, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('P' . str_ireplace('+', '', $var2) . 'M'));
                            } elseif (substr($var2, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('P' . str_ireplace('-', '', $var2) . 'M'));
                            }
                        }

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var3)) {
                            if (substr($var3, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('P' . str_ireplace('+', '', $var3) . 'Y'));
                            } elseif (substr($var2, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('P' . str_ireplace('-', '', $var3) . 'Y'));
                            }
                        }

                        $value = $timeobj->format('d/m/Y');
                        break;
                    case '{different_time}':
                        $timeobj = new DateTime();

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var1)) {
                            if (substr($var1, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('PT' . str_ireplace('+', '', $var1) . 'H'));
                            } elseif (substr($var1, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('PT' . str_ireplace('-', '', $var1) . 'H'));
                            }
                        }

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var2)) {
                            if (substr($var2, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('PT' . str_ireplace('+', '', $var2) . 'M'));
                            } elseif (substr($var2, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('PT' . str_ireplace('-', '', $var2) . 'M'));
                            }
                        }

                        $value = $timeobj->format('H:i');
                        break;
                    case '{different_date_time}':
                        $timeobj = new DateTime();

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var1)) {
                            if (substr($var1, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('P' . str_ireplace('+', '', $var1) . 'D'));
                            } elseif (substr($var1, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('P' . str_ireplace('-', '', $var1) . 'D'));
                            }
                        }

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var2)) {
                            if (substr($var2, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('P' . str_ireplace('+', '', $var2) . 'M'));
                            } elseif (substr($var2, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('P' . str_ireplace('-', '', $var2) . 'M'));
                            }
                        }

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var3)) {
                            if (substr($var3, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('PT' . str_ireplace('+', '', $var3) . 'H'));
                            } elseif (substr($var3, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('PT' . str_ireplace('-', '', $var3) . 'H'));
                            }
                        }

                        if (preg_match('/\A[-+]?[0-9]+\z/', $var4)) {
                            if (substr($var4, 0, 1) == '+') {
                                $timeobj->add(new DateInterval('PT' . str_ireplace('+', '', $var4) . 'M'));
                            } elseif (substr($var4, 0, 1) == '-') {
                                $timeobj->sub(new DateInterval('PT' . str_ireplace('-', '', $var4) . 'M'));
                            }
                        }

                        $value = $timeobj->format('d/m/Y H:i');
                        break;
                    case '{last_month}':
                        $timeobj = new DateTime();
                        $last_month_day = $timeobj->format('t/m/Y');
                        $first_month_day = $timeobj->format('01/m/Y');
                        $value = "{$first_month_day} - {$last_month_day}";
                        //debug($value);
                        break;
                    case '{last_year}':
                        $timeobj = new DateTime();
                        $last_year_day = $timeobj->format('31/12/Y');
                        $first_year_day = $timeobj->format('01/01/Y');
                        $value = "{$first_year_day} - {$last_year_day}";
                        //debug($value);
                        break;
                    case '{last_day}':
                        $timeobj = new DateTime();
                        $last_year_day = $timeobj->format('m/d/Y');
                        $first_year_day = $last_year_day;
                        $value = "{$first_year_day} - {$last_year_day}";
                        //debug($value);
                        break;
                    default:

                        debug("NON GESTITA DEFAULT TYPE FUNCTION '{$func}'");
                        break;
                }
                break;
            case 'variable':
                // Mi aspetto una sintassi di questo tipo: {arr campo} oppure {campo}
                $str = str_replace(array("{", "}"), "", $fields['forms_fields_default_value']);
                $exp = explode(' ', $str);

                if (count($exp) > 1) {
                    // Fix compatibility with old version
                    if ($exp[0] == 'get') {
                        $value = $this->input->get($exp[1]);
                    } else {
                        debug("NON GESTITA DEFAULT TYPE VARIABLE");
                    }
                } elseif ($str == 'value_id') {
                    $value = $value_id;
                }

                break;
        }

        return $value;
    }

    public function get_form_id_by_identifier($form_identifier)
    {
        $form = $this->db->where('forms_identifier', $form_identifier)->get('forms')->row();
        if ($form) {
            return $form->forms_id;
        } else {
            return false;
        }
    }
    public function get_grids_id_by_identifier($grid_identifier)
    {
        return $this->get_grid_id_by_identifier($grid_identifier);
    }
    public function get_grid_id_by_identifier($grid_identifier)
    {
        $grid = $this->db->where('grids_identifier', $grid_identifier)->get('grids')->row();
        if ($grid) {
            return $grid->grids_id;
        } else {
            return false;
        }
    }

    public function get_form($form_id, $edit_id = null, $value_id = null)
    {
        $cache_key = "database_schema/datab.get_form.{$form_id}." . md5(serialize(func_get_args()) . serialize($_GET) . serialize($_POST) . serialize($this->session->all_userdata()));
        if (!($dati = $this->mycache->get($cache_key))) {
            if (!$form_id) {
                log_message('error', "Form id '$form_id' not found");
                echo $this->load->view("box/errors/missing_form", ['form_id' => $form_id], true);
                return false;
            }
            $form = $this->db->join('entity', 'forms_entity_id = entity_id')->get_where('forms', ['forms_id' => $form_id])->row_array();
            if (!$form) {
                $dati = false;
                if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('database_schema')) {
                    $this->mycache->save($cache_key, $dati, self::CACHE_TIME);
                }
                return $dati;
            }

            $fields = $this->db
                ->join('fields', 'fields_id = forms_fields_fields_id')
                ->join('fields_draw', 'forms_fields_fields_id = fields_draw_fields_id')
                ->order_by('forms_fields_order')
                ->get_where('forms_fields', ['forms_fields_forms_id' => $form_id, 'fields_visible' => DB_BOOL_TRUE])->result_array();

            if (!empty($form['forms_action'])) {
                $form['action_url'] = str_ireplace(['{base_url}', '{value_id}'], [base_url(), ($edit_id ?? null)], $form['forms_action']);
            } else {
                if (is_array($edit_id)) {
                    $form['action_url'] = base_url("db_ajax/save_form/{$form_id}/true");
                } else {
                    $form['action_url'] = base_url("db_ajax/save_form/{$form_id}" . ($edit_id ? "/true/{$edit_id}" : ''));
                }
            }

            foreach ($fields as $key => $field) {
                if ($field['fields_ref'] && !$this->crmentity->entityExists($field['fields_ref'])) {
                    unset($fields[$key]);
                }
            }

            /*
             * Per far funzionare correttamente i form non posso recuperare i valori
             * già tradotti, quindi devo resettare il sistema lingue dell'apilib,
             * fare la chiamata e poi ripristinarlo
             */
            $clanguage = $this->apilib->getLanguage(); // Current Language
            $flanguage = $this->apilib->getFallbackLanguage(); // Fallback Language

            $this->apilib->setLanguage();
            if ($form['forms_one_record'] == DB_BOOL_TRUE) {
                $formData = $this->apilib->searchFirst($form['entity_name']);
            } else {
                $formData = ($edit_id && !is_array($edit_id)) ? $this->apilib->view($form['entity_name'], $edit_id, 1) : [];
            }

            foreach ($fields as $key => $field) {
                if (!$this->conditions->accessible('forms_fields', "{$field['forms_fields_forms_id']},{$field['forms_fields_fields_id']}", $value_id, $formData, $edit_id)) {
                    unset($fields[$key]);
                    continue;
                }
                if ($field['fields_multilingual'] == DB_BOOL_TRUE && $edit_id) { //If multilanguage, override value with original json
                    $entity = $this->crmentity->getEntity($field['fields_entity_id']);

                    $value_json = $this->db
                        ->select($field['fields_name'])
                        ->get_where($entity['entity_name'], [$entity['entity_name'] . '_id' => $edit_id])
                        ->row_array()[$field['fields_name']];
                    $formData[$field['fields_name']] = $value_json;
                }

            }

            $this->apilib->setLanguage($clanguage, $flanguage);

            $operators = unserialize(OPERATORS);
            foreach ($fields as $key => $field) {

                $fields[$key] = $this->processFieldMapping($field, $form, $edit_id);



            }
            unset($field);

            /*
             * Combino i form data col get per fare il render dei fields
             */

            $formData = array_merge($formData, $this->input->get() ?: [], array_filter($formData, function ($val) {
                return is_null($val) or $val === '';
            }));

            /*
             * Splitto i fields in due categorie:
             *  - form_fields: che funzionano come tutti quelli già inseriti
             *  - hidden_fields: che vengono inseriti all'inizio del form
             * ----
             * NB: Faccio qua il pre-render dei fields in modo da poter unsettare i
             *     dati e liberare memoria
             */
            $hidden = $shown = [];
            foreach ($fields as $field) {

                $type = !empty($field['forms_fields_override_type']) ? $field['forms_fields_override_type'] : $field['fields_draw_html_type'];
                if ($type === 'input_hidden') {
                    $hidden[] = $field;
                } else {
                    $shown[] = $field;
                }
            }

            foreach ($hidden as $k => $field) {

                $hidden[$k] = $this->build_form_input($field, isset($formData[$field['fields_name']]) ? $formData[$field['fields_name']] : null, $value_id);
            }

            foreach ($shown as $k => $field) {

                // Dimensione del field:
                //  - cerca prima un valore valido in `forms_fields_override_colsize`
                //  - altrimenti controlla se è un wysiwyg e impostala a 12
                //      - per controllare se è un wysiwyg prima controllo nel campo
                //        `forms_fields_override_type`
                //      - se questo è VUOTO allora prendo il `fields_draw_html_type`
                //  - altrimenti metti null
                $colsize = empty($field['forms_fields_override_colsize']) ? null : $field['forms_fields_override_colsize'];
                $type = $field['forms_fields_override_type'] ?: $field['fields_draw_html_type'];
                if (!$colsize && $type === 'wysiwyg') {
                    $colsize = 12;
                }

                $shown[$k] = [
                    'id' => $field['fields_id'],
                    'name' => $field['fields_name'],
                    'label' => $field['forms_fields_override_label'] ?: $field['fields_draw_label'],
                    'size' => $colsize,
                    'min' => $field['forms_fields_min'],
                    'max' => $field['forms_fields_max'],
                    'type' => $type,
                    'datatype' => $field['fields_type'],
                    'filterref' => empty($field['support_fields'][0]['entity_name']) ? $field['fields_ref'] : $field['support_fields'][0]['entity_name'],
                    // Computo il ref field da usare nel caso di form
                    'fields_source' => $field['fields_source'],
                    // Computo il ref field da usare nel caso di form
                    'html' => $this->build_form_input($field, isset($formData[$field['fields_name']]) ? $formData[$field['fields_name']] : null, $value_id),
                    'fieldset' => $field['forms_fields_fieldset'],
                    'required' => $field['forms_fields_show_required'] ? $field['forms_fields_show_required'] == DB_BOOL_TRUE : ($field['fields_required'] != FIELD_NOT_REQUIRED && !trim($field['fields_default'])),
                    'onclick' => $field['fields_draw_onclick'] ? sprintf('onclick="%s"', $field['fields_draw_onclick']) : '',
                    'original_field' => $field,
                ];

                $dati = ['forms' => $form, 'forms_hidden' => $hidden, 'forms_fields' => $shown];
                if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('database_schema')) {
                    $this->mycache->save($cache_key, $dati, $this->mycache->CACHE_TIME, $this->mycache->buildTagsFromEntity($form['forms_entity_id']));
                }
            }
        }
        //debug($dati['forms_fields']);
        return $dati;
    }
    public function processFieldMapping($field, $form, $value_id = null)
    {
        //debug($field,true);
        // Il ref è il nome della tabella/entità di supporto/da joinare
        // quindi estraggo i valori da proporre
        if (!$field['fields_ref']) {
            //Potrebbe essere comunque una select con dati da pescare da fields_additional_data (valori separati da virgola)
            if (!empty($field['fields_additional_data'])) {
                //verifico se ho i dati separati da virgola o una query da eseguire (se è una query sarà scritto {query: select......})
                if (strpos($field['fields_additional_data'], '{query:') !== false) {
                    $query = str_replace(['{query:', '}'], '', $field['fields_additional_data']);
                    $support_data = $this->db->query($query)->result_array();
                    $field['support_data'] = array_combine(array_column($support_data, 'id'), array_column($support_data, 'value'));
                } else {
                    $support_data = explode(',', $field['fields_additional_data']);
                    $field['support_data'] = array_combine($support_data, $support_data);
                    //debug($field['support_data'], true);
                }


            }


            return $field;
        }

        if (!($entity = $this->get_entity_by_name($field['fields_ref']))) {
            log_message('error', "Relation field does not exist");
            echo "Relation field does not exists (" . $field['fields_ref'] . ") ";
            return $field;
        }

        // Verifico se il ref si riferisce ad una eventuale relations oppure ad una tabella di supporto, in modo da gestirlo diversamente
        // Chiaramente x funzionare non ci devono essere 2 relazioni con lo stesso nome
        $relations = $this->crmentity->getRelationByName($entity['entity_name']);

        if (count($relations) > 0) {
            // Se ho relazione A_B e il form inserisce A, allora voglio prendere la tabella B...
            $nField = ($relations['relations_table_2'] == $form['entity_name']) ? 1 : 2;

            $entity = $this->get_entity_by_name($relations["relations_table_{$nField}"]);
            $support_relation_table = $relations["relations_table_{$nField}"];
            $field['field_support_id'] = $relations["relations_field_{$nField}"]; // Dichiara il campo id da utilizzare nel form
        } else {
            $support_relation_table = $field['fields_ref'];
            $field['field_support_id'] = $entity['entity_name'] . "_id"; // Dichiara il campo id da utilizzare nel form
        }

        // A questo punto se il campo è ajax non pesco i dati, ma demando
        // l'onere alla chiamata ajax
        $type = $field['forms_fields_override_type'] ?: $field['fields_draw_html_type'];
        if ($type == 'select_ajax' or $field['fields_source']) {
            return $field;
        }

        // Applico limiti permessi
        $field_limits = $this->getUserLimits($support_relation_table);
        $wheres = [];

        foreach ($field_limits as $field_limit) {
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

        // Prendo la field select where
        if (($fieldWhere = trim($field['fields_select_where']))) {
            $replaces = ['value_id' => $value_id];
            $wheres[] = $this->replace_superglobal_data(str_replace_placeholders($fieldWhere, $replaces));
        }

        //If any pre-search are present, run it before extract data
        $wheres = $this->apilib->runDataProcessing($support_relation_table, 'pre-search', $wheres);

        $where = $wheres ? '(' . implode(' AND ', $wheres) . ')' : '';

        // Se attualmente ci sono dei filtri E se il campo ha una
        // corrispondenza nei dati del form (in modifica), allora voglio
        // assicurarmi che il valore/i valori vengano preselezionati, a
        // prescindere dai filtri
        if ($where && isset($formData[$field['fields_name']])) {
            $lvalue = $field['field_support_id'];
            $oper = '=';
            $rvalue = $formData[$field['fields_name']];

            // è una relazione, quindi nelle chiavi ci sono gli id
            if (is_array($rvalue)) {
                $oper = 'IN';
                $rvalue = '(' . implode(',', array_keys($rvalue)) . ')';
            }

            $where .= (($where ? ' OR ' : '') . '(' . $lvalue . ' ' . $oper . ' ' . $rvalue . ')');
        }
        // TODO Calculate order by

        $limit = null;
        $offset = 0;

        $options['depth'] = 1;

        $field['support_data'] = $this->crmentity->getEntityPreview($support_relation_table, $where, $limit, $offset, $options);

        if ($field['forms_field_full_data']) {
            $support_data_full = $this->apilib->search($support_relation_table, $where, $limit, $offset);
            $field['support_data_full'] = array_key_map_data($support_data_full, $support_relation_table . '_id');
        }

        return $field;
    }

    /**
     * Grids
     */
    public function get_default_grid($entity_id)
    {
        if (!$entity_id) {
            die('ERRORE: Entity ID mancante');
        }

        $grid_id = $this->db->query("SELECT grids_id FROM grids WHERE grids_entity_id = '$entity_id' AND grids_default = '" . DB_BOOL_TRUE . "'")->row()->grids_id;
        return $grid_id;
    }

    public function get_grids_from_entity($entity_id)
    {
        debug("TODO: Assert error! Deprecated function.", true);
        if (!$entity_id) {
            die('ERRORE: Entity ID mancante');
        }

        $dati = array();
        $dati['grids'] = $this->db->query("SELECT * FROM grids WHERE entity_id = '$entity_id'")->result_array();
        return $dati;
    }

    public function get_grid_data($grid, $value_id = null, $where = array(), $limit = null, $offset = 0, $order_by = null, $count = false, $additional_parameters = [])
    {
        $cache_key = "apilib/datab.get_grid_data." . md5(serialize($grid) . serialize(func_get_args()) . serialize($_GET) . serialize($_POST) . serialize($this->session->all_userdata()));
        if (!($dati = $this->mycache->get($cache_key))) {
            $group_by = array_get($additional_parameters, 'group_by', null);
            $search = array_get($additional_parameters, 'search', null);
            $preview_fields = array_get($additional_parameters, 'preview_fields', []);

            //@TODO: Intervenire su questa funzione per estrarre eventuali eval cachable
            $eval_cachable_fields = array_filter($grid['grids_fields'], function ($field) {
                return ($field['grids_fields_replace_type'] == 'eval' && $field['grids_fields_eval_cache_type'] && $field['grids_fields_eval_cache_type'] != 'no_cache');
            });

            if (is_array($value_id)) {
                $additional_data = isset($value_id['additional_data']) ? $value_id['additional_data'] : array();
                $value_id = isset($value_id['value_id']) ? $value_id['value_id'] : null;
            } else {
                $additional_data = array();
            }

            /** Grid order_by * */
            if (is_null($order_by) && !empty($grid['grids']['grids_order_by']) && !$count) {
                $replaces['value_id'] = $value_id;
                $order_by = $this->replace_superglobal_data(str_replace_placeholders($grid['grids']['grids_order_by'], $replaces));
            }

            /** Grid group_by * */
            if (is_null($group_by) && !empty($grid['grids']['grids_group_by'])) {
                $group_by = $grid['grids']['grids_group_by'];
            }

            /** GRID LIMIT (Michael, 2021-12-09) */
            if ((!empty($grid['grids']['grids_limit']) && $grid['grids']['grids_limit'] > 0) && !$limit) {
                $limit = $grid['grids']['grids_limit'];
            }

            /** Grid depth * */

            $depth = ($grid['grids']['grids_depth'] > 0) ? $grid['grids']['grids_depth'] : 2;

            //debug($grid['grids_fields']);
            //If $search is present, order by best match before ordering the rest of the data
            if ($search && defined('USE_INSTR_ORDERBY') && USE_INSTR_ORDERBY === true) {
                $order_by_prepend = $this->search_like_orderby($search, array_merge($grid['grids_fields'], $preview_fields));
                if ($order_by) {
                    $order_by = $order_by_prepend . ',' . $order_by;
                } else {
                    $order_by = $order_by_prepend;
                }
            }
            // Se è ancora null, vuol dire che non ho cliccato su nessuna colonna e che non c'è nemmeno un order by default. Di conseguenza ordino per id desc (che è la cosa più logica)
            if (is_null($order_by) && !$count) {
                $order_by = $grid['grids']['entity_name'] . '.' . $grid['grids']['entity_name'] . '_id DESC';
            }

            $has_bulk = !empty($grid['grids_bulk_mode']);
            $where = $this->generate_where("grids", $grid['grids']['grids_id'], $value_id, is_array($where) ? implode(' AND ', $where) : $where, $additional_data);
            
            // Verifico che non sia impostato un campo order by di default nell'entità, qualora non specificato un order by specifico della grid

            if (empty($order_by)) {
                // Recupero i dati dell'entità
                try {
                    //$this->load->model('crmentity');
                    $entity_data = $this->crmentity->getEntity($grid['grids']['grids_entity_id']);
                } catch (Exception $ex) {
                    $this->error = self::ERR_VALIDATION_FAILED;
                    $this->errorMessage = $ex->getMessage();
                    $dati = false;
                    if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('apilib')) {
                        $this->mycache->save($cache_key, $dati, self::CACHE_TIME, $this->mycache->buildTagsFromEntity($grid['grids']['entity_name']));
                    }
                    return $dati;
                }

                $entityCustomActions = empty($entity_data['entity_action_fields']) ? [] : json_decode($entity_data['entity_action_fields'], true);

                if (isset($entityCustomActions['order_by_asc'])) {
                    $order_by = $entityCustomActions['order_by_asc'] . ' ASC';
                } elseif (isset($entityCustomActions['order_by_desc'])) {
                    $order_by = $entityCustomActions['order_by_desc'] . ' DESC';
                }
            }

            // Disabilita temporaneamente sistema di traduzioni in modo da pescare
            // i dati completi
            $clanguage = $this->apilib->getLanguage(); // Current Language
            $flanguage = $this->apilib->getFallbackLanguage(); // Fallback Language

            $this->apilib->setLanguage();

            if (!empty($grid['grids']['grids_custom_query'])) {
                //Rimpiazzo eventuali placeholder
                $replaces['value_id'] = $value_id;
                if (is_array($additional_data) && $additional_data) {
                    $replaces = array_merge($replaces, $additional_data);
                }



                $grid['grids']['grids_custom_query'] = $this->replace_superglobal_data(str_replace_placeholders($grid['grids']['grids_custom_query'], $replaces), true, false);

                $data = $this->getDataEntityByQuery($grid['grids']['grids_entity_id'], $grid['grids']['grids_custom_query'], $where, $limit, $offset, $order_by, $count, $eval_cachable_fields, ['group_by' => $group_by]);
            } else {
                $data = $this->getDataEntity($grid['grids']['grids_entity_id'], $where, $limit, $offset, $order_by, $depth, $count, $eval_cachable_fields, ['group_by' => $group_by]);
            }
            //debug($data,true);
            // Riabilita sistema traduzioni
            $this->apilib->setLanguage($clanguage, $flanguage);

            $dati = $data;
            if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('apilib')) {
                $this->mycache->save($cache_key, $dati, self::CACHE_TIME, $this->mycache->buildTagsFromEntity($grid['grids']['entity_name'], $value_id));
            }
        }
        return $dati;
    }

    public function get_grid($grid_id)
    {
        if (array_key_exists($grid_id, $this->_grids_data)) {
            return $this->_grids_data[$grid_id];
        } else {
            if (!$grid_id) {
                die('ERRORE: grid ID mancante');
            }

            $dati['grids'] = $this->db->query("SELECT * FROM grids LEFT JOIN entity ON entity.entity_id = grids.grids_entity_id WHERE grids_id = ?", [$grid_id])->row_array();

            $dati['grids_fields'] = $this->db->query("
                    SELECT *
                    FROM grids_fields
                        LEFT JOIN grids ON grids.grids_id = grids_fields.grids_fields_grids_id
                        LEFT JOIN fields ON fields.fields_id = grids_fields.grids_fields_fields_id
                        LEFT JOIN entity ON fields.fields_entity_id = entity.entity_id
                        LEFT JOIN fields_draw ON grids_fields.grids_fields_fields_id = fields_draw.fields_draw_fields_id
                    WHERE grids_id = ? AND (fields_id IS NULL OR NOT fields_draw_display_none)
                    ORDER BY grids_fields_order ASC
                ", [$grid_id])->result_array();

            // Ciclo ed estraggo eventuali campi di tabelle joinate FUNZIONA SOLO
            // CON ENTITA PER ORA
            foreach ($dati['grids_fields'] as $key => $field) {
                if ($field['fields_ref'] && !$this->crmentity->entityExists($field['fields_ref']) || !$this->conditions->accessible('grids_fields', $field['grids_fields_id'], null)) {
                    unset($dati['grids_fields'][$key]);
                    continue;
                }
                // Preparo il nome colonna
                $colname = isset($field['grids_fields_column_name']) ? $field['grids_fields_column_name'] : $field['fields_draw_label'];
                $dati['grids_fields'][$key]['grids_fields_column_name'] = trim($colname) ?: $field['fields_draw_label'];

                if ($field['fields_ref'] && $field['fields_ref_auto_left_join'] == DB_BOOL_TRUE && $this->crmentity->entityExists($field['fields_ref'])) {
                    $dati['grids_fields'][$key]['support_fields'] = array_values(
                        array_filter(
                            $this->crmentity->getFields($field['fields_ref']),
                            function ($field) {
                                return $field['fields_preview'] == DB_BOOL_TRUE;
                            }
                        )
                    );
                }
            }

            //TODO: will be deprecated as soon as new actions features will become stable
            $dati['grids']['links'] = array(
                'view' => ($dati['grids']['grids_view_layout'] ? base_url("main/layout/{$dati['grids']['grids_view_layout']}") : str_replace('{base_url}', base_url(), $dati['grids']['grids_view_link'])),
                'edit' => ($dati['grids']['grids_edit_layout'] ? base_url("main/layout/{$dati['grids']['grids_edit_layout']}") : str_replace('{base_url}', base_url(), $dati['grids']['grids_edit_link'])),
                'delete' => ($dati['grids']['grids_delete_link'] ? str_replace('{base_url}', base_url(), $dati['grids']['grids_delete_link']) : base_url("db_ajax/generic_delete/{$dati['grids']['entity_name']}")),
            );

            if (!filter_var($dati['grids']['links']['delete'], FILTER_VALIDATE_URL)) {
                unset($dati['grids']['links']['delete']);
            }
            //TODO: check other actions
            // if (empty($dati['grids']['entity_id'])) {
            //     debug($dati, true);
            // }
            $can_write = $this->can_write_entity($dati['grids']['entity_id']);
            if (!$can_write) {
                unset($dati['grids']['links']['edit'], $dati['grids']['links']['delete']);
            }

            // Infine aggiungo le custom actions - attenzione! non posso valutare i permessi sulle custom actions
            $dati['grids']['links']['custom'] = $this->db->order_by('grids_actions_order', 'ASC')->get_where('grids_actions', array('grids_actions_grids_id' => $grid_id))->result_array();

            foreach ($dati['grids']['links']['custom'] as &$custom_link) {
                // Mantengo questa funzionalità solo se è impostato il custom html
                if (!empty($custom_link['grids_actions_html'])) {
                    $html = str_replace('{base_url}', base_url(), $custom_link['grids_actions_html']);
                    $custom_link['grids_actions_html'] = $html;
                    $custom_link['grids_actions_name'] = addslashes($custom_link['grids_actions_name']);
                }
            }

            // Mi assicuro che ogni link esistente termini con '/' e valuto se è da aprire con modale
            foreach ($dati['grids']['links'] as $type => $link) {
                if ($link && is_string($link)) {
                    $dati['grids']['links'][$type] = rtrim($link, '/') . '/';
                    $dati['grids']['links'][$type . '_modal'] = (strpos($link, 'modal') !== false);
                }
            }

            $dati['replaces'] = [];
            foreach ($dati['grids_fields'] as $gridField) {
                $isValidType = (empty($dati['grids_fields_replace_type']) or $dati['grids_fields_replace_type'] === 'field');
                if ($isValidType && $gridField['grids_fields_replace']) {
                    $dati['replaces'][$gridField['grids_fields_replace']] = $gridField;
                }
            }
            $this->_grids_data[$grid_id] = $dati;

            return $dati;
        }
    }

    /**
     * CHARTS
     */

    public function get_entity_fields($entity_id)
    {
        return $this->crmentity->getFields($entity_id);
    }

    /**
     * Calendars
     */
    public function get_calendar($calendar_id)
    {
        if (!$calendar_id) {
            die('ERRORE: calendar ID mancante');
        }

        $dati['calendars'] = $this->db->query("SELECT * FROM calendars LEFT JOIN entity ON entity.entity_id = calendars.calendars_entity_id WHERE calendars_id = '$calendar_id'")->row_array();

        $dati['calendars_fields'] = $this->db->query("SELECT * FROM calendars_fields
                                                      LEFT JOIN fields ON fields.fields_id = calendars_fields.calendars_fields_fields_id
                                                      LEFT JOIN calendars ON calendars.calendars_id = calendars_fields.calendars_fields_calendars_id
                                                      WHERE calendars_id = '$calendar_id'")->result_array();
        //debug($dati['calendars_fields'], true);

        $defaultForm = null; // Faccio la query solamente se è realmente necessario
        $allowCreate = $dati['calendars']['calendars_allow_create'] == DB_BOOL_TRUE;
        $allowUpdate = $dati['calendars']['calendars_allow_edit'] == DB_BOOL_TRUE;
        $formCreate = $dati['calendars']['calendars_form_create'];
        $formUpdate = $dati['calendars']['calendars_form_edit'];

        if (($allowCreate && !$formCreate) or ($allowUpdate && !$formUpdate)) {
            $defaultForm = $this->db->query('SELECT forms_id FROM forms WHERE forms_default AND forms_entity_id = ? LIMIT 1', [$dati['calendars']['calendars_entity_id']])->row()->forms_id;
        }

        $dati['create_form'] = $allowCreate ? ($formCreate ?: $defaultForm) : null;
        $dati['update_form'] = $allowUpdate ? ($formUpdate ?: $defaultForm) : null;

        return $dati;
    }

    /**
     * Maps
     */
    public function get_map($map_id)
    {
        $map_id or die('ERRORE: map ID mancante');

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
    public function get_entity_preview_by_name($entity_name, $where = null, $limit = null, $offset = 0)
    {
        return $this->crmentity->getEntityPreview($entity_name, $where, $limit, $offset);
    }

    public function get_support_data($fields_ref = null)
    {
        if (!$fields_ref) {
            return array();
        } else {
            $entity = $this->get_entity_by_name($fields_ref);

            // Verifico se il ref si riferisce ad una eventuale relations oppure ad una tabella di supporto, in modo da gestirlo diversamente
            $relations = $this->crmentity->getRelationByName($entity['entity_name']);

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
                                                         WHERE fields_entity_id = '{$entity['entity_id']}' AND fields_preview = '" . DB_BOOL_TRUE . "'")->result_array();
            $support_fields = $this->fields_implode($visible_fields_supports);

            $select = $field_support_id . ($support_fields ? ',' . $support_fields : '');

            //TODO: don't use query, but apilib search....
            return $this->db->query("SELECT {$select} FROM {$support_relation_table}")->result_array();
        }
    }

    /**
     * Costruisce il where di un oggetto GRID, MAPS, CHARTS o altro
     */
    public function generate_where($element_type, $element_id, $value_id = null, $other_where = null, $additional_data = array())
    {
        $arr = array();
        if (!is_numeric($element_id)) {
            $func = "get_{$element_type}_id_by_identifier";
            $element_id = $this->$func($element_id);
        }
        $element = $this->db->get_where($element_type, array($element_type . "_id" => $element_id))->row_array();
        if (!empty($element[$element_type . '_entity_id'])) {
            $entity = $this->get_entity($element[$element_type . '_entity_id']);
        }


        // Verifico se questo oggetto ha un where di suo
        if ($other_where) {
            $arr[] = "(" . (is_array($other_where) ? implode(' AND ', $other_where) : $other_where) . ")";
        }

        if ($element[$element_type . "_where"]) {
            // Aggiungo il suo where all'inizio del where che andrò a ritornare
            $arr[] = "(" . $element[$element_type . "_where"] . ")";
        }

        if (!empty($element[$element_type . "_builder_where"])) {
            // Aggiungo il suo where all'inizio del where che andrò a ritornare
            $arr[] = "(" . $element[$element_type . "_builder_where"] . ")";
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
                if (!empty($entity)) {
                    $__relationships = $this->db->get_where('relations', array('relations_table_1' => $entity['entity_name']))->result_array();
                    $relationships = array_combine(array_map(function ($rel) {
                        return $rel['relations_name'];
                    }, $__relationships), $__relationships);
                } else {
                    $relationships = [];
                }
                foreach ($sess_where_data[$element[$element_type . "_filter_session_key"]] as $condition) {
                    if (!array_key_exists('value', $condition) || $condition['value'] === '' || $condition['value'] === []) {
                        continue;
                    }
                    $query_field = $this->db->join('fields_draw', 'fields_draw_fields_id = fields_id', 'left')->get_where('fields', array('fields_id' => (int) $condition['field_id']));
                    if ($query_field->num_rows() && $query_field->row()->fields_name) {
                        $field = $query_field->row();
                        // Se il campo è di un'entità diversa da quella del form devo fare un where in
                        // ovviamente l'entità a cui appartiene il campo deve avere almeno un campo che punta all'entità del form
                        $is_another_entity = !empty($entity) && ($entity['entity_id'] != $field->fields_entity_id);

                        if ($is_another_entity) {
                            // Sto cercando in un'entità diversa
                            $other_entity = $this->get_entity($field->fields_entity_id);

                            $other_field_select = $this->db->get_where(
                                'fields',
                                array(
                                    'fields_entity_id' => $field->fields_entity_id,
                                    'fields_ref' => $entity['entity_name']
                                )
                            )->row();
                            if (isset($other_field_select->fields_name)) {
                                // Caso 1: è l'altra entità che ha il ref nell'entità in cui eseguo la ricerca
                                $where_prefix = "{$entity['entity_name']}.{$entity['entity_name']}_id IN (SELECT {$other_field_select->fields_name} FROM {$other_entity['entity_name']} WHERE ";
                            } else {
                                // Caso 2: è questa entità che sta ha il ref nell'altra entità
                                // devo trovare codesto field
                                $field_referencing = $this->db->get_where('fields', array('fields_entity_id' => $entity['entity_id'], 'fields_ref' => $other_entity['entity_name']))->row();
                                if (empty($field_referencing)) {
                                    // Non so come gestirlo, per ora piazzo un continue e tolgo debug
                                    //continue;

                                    //Non so come gestirlo ma ci provo mettendo il filtro così com'è (es.: customers.customers_group = 2, sperando che customers sia joinata in qualche modo...)
                                    $field_referencing = $field;
                                    $where_prefix = "({$other_entity['entity_name']}.";
                                }

                                $where_prefix = "{$entity['entity_name']}.{$field_referencing->fields_name} IN (SELECT {$other_entity['entity_name']}_id FROM {$other_entity['entity_name']} WHERE ";
                            }

                            $where_suffix = ")";
                        } elseif (array_key_exists($field->fields_ref, $relationships)) {
                            // Sto filtrando in una relazione, quindi il mio field ref punta ad una relazione
                            // prendo il campo della TABELLA 2 perché è la cossiddetta tabella correlata
                            $main_field = $relationships[$field->fields_ref]['relations_field_1'];
                            $related_field = $relationships[$field->fields_ref]['relations_field_2'];

                            $where_prefix = "{$entity['entity_name']}.{$entity['entity_name']}_id IN (SELECT {$main_field} FROM {$field->fields_ref} WHERE ";
                            $where_suffix = ")";

                            // A questo punto devo cambiare il $field perché $field è il campo dell'entità principale
                            // e il where finale dovrà essere fatto sull'entità di relazione e più precisamente sul campo della tabella 2
                            // il concetto è questo: campo_entita_principale IN ( SELECT campo_1 FROM nome_relazione WHERE campo_2 OP VAL )
                            // Il problema è che il campo_2 è cmq un ID quindi non sarà dentro la tabella fields - per risolvermi questo problema
                            // modifico la variabile field già esistente
                            $field->fields_type = DB_INTEGER_IDENTIFIER;
                            $field->fields_name = $related_field;
                            $field->fields_draw_html_type = null;
                        } else {
                            // Sto filtrando in un campo dell'entità principale
                            // Metto comunque il nome della tabella come prefisso per evitare il classico errore che il campo compare in più tabelle...
                            $where_prefix = (!empty($entity)) ? "{$entity['entity_name']}." : '';
                            $where_suffix = '';
                        }

                        if (!empty($condition['reverse']) && $condition['reverse'] == DB_BOOL_TRUE) {
                            $not = 'NOT ';
                        } else {
                            $not = '';
                        }

                        // Metto in pratica i filtri e li aggiungo all'array
                        // delle condizioni del where
                        if (in_array($field->fields_draw_html_type, array('date', 'date_time')) || (is_string($condition['value']) && strpos($condition['value'], ' - ') !== false)) {
                            if ($condition['value'] == '-2') {
                                //Special condition: means "field empty"...
                                $arr[] = "$not({$where_prefix}{$field->fields_name} IS NULL OR ({$where_prefix}{$field->fields_name} = ''))";

                                continue;
                            }
                            $values = explode(' - ', $condition['value']);
                            if (count($values) === 2) {
                                $start = preg_replace('/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $values[0]);
                                $end = preg_replace('/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $values[1]);

                                if ($this->db->dbdriver != 'postgre') {
                                    if ($is_another_entity) { //Sono costretto a ricontrollare se questo campo fa riferimento a un'altra tabella
                                        $other_entity = $this->get_entity($field->fields_entity_id);
                                        $other_field_select = $this->db->get_where('fields', array('fields_entity_id' => $field->fields_entity_id, 'fields_ref' => $entity['entity_name']))->row();
                                        if (isset($other_field_select->fields_name)) {
                                            // Caso 1: è l'altra entità che ha il ref nell'entità in cui eseguo la ricerca
                                            $arr[] = "{$entity['entity_name']}.{$entity['entity_name']}_id $not IN (SELECT {$other_field_select->fields_name} FROM {$other_entity['entity_name']} WHERE (CAST({$field->fields_name} AS DATE) BETWEEN '{$start}' AND '{$end}'))";
                                        } else {
                                            // Caso 2: è questa entità che sta ha il ref nell'altra entità
                                            // devo trovare codesto field
                                            $field_referencing = $this->db->get_where('fields', array('fields_entity_id' => $entity['entity_id'], 'fields_ref' => $other_entity['entity_name']))->row();
                                            if (empty($field_referencing)) {
                                                // Non so come gestirlo, per ora piazzo un continue e tolgo debug
                                                continue;
                                            }
                                            $arr[] = "{$entity['entity_name']}.{$field_referencing->fields_name} $not IN (SELECT {$other_entity['entity_name']}_id FROM {$other_entity['entity_name']} WHERE (CAST({$field->fields_name} AS DATE) BETWEEN '{$start}' AND '{$end}'))";
                                        }
                                    } else {
                                        $arr[] = "(CAST({$where_prefix}{$field->fields_name} AS DATE) $not BETWEEN '{$start}' AND '{$end}'{$where_suffix})";
                                    }
                                } else {
                                    $arr[] = "$not ({$where_prefix}{$field->fields_name}::DATE >= '{$start}'::DATE AND {$field->fields_name}::DATE <= '{$end}'::DATE{$where_suffix})";
                                }
                            }
                        } else {
                            
                            $condition['value'] = str_replace("'", "''", $condition['value']);
                            if ($condition['value'] == '-1') {
                                continue;
                            }

                            if ($condition['value'] == '-2') {
                                //Special condition: means "field empty"...
                                $arr[] = "$not({$where_prefix}{$field->fields_name} IS NULL OR ({$where_prefix}{$field->fields_name} = '' AND {$where_prefix}{$field->fields_name} <> 0))";
                                //debug($arr);
                                continue;
                            }

                            switch ($condition['operator']) {
                                case 'in':
                                case 'notin':
                                    if (!is_array($condition['value'])) {
                                        $condition['value'] = explode(',', $condition['value']);
                                    }
                                    $values = "'" . implode("','", $condition['value']) . "'";
                                    if (in_array(-2, $condition['value'])) {
                                        

                                        



                                        $foo_prefix = "{$where_prefix}{$field->fields_name}";
                                        $foo_prefix = str_ireplace(' IN ', ' NOT IN ', $foo_prefix);
                                        $foo_prefix = str_ireplace('WHERE', '', $foo_prefix);

                                        
                                            $arr[] = "
                                                $not (
                                                        (
                                                                {$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} ({$values}){$where_suffix}
                                                                OR
                                                                (
                                                                    {$foo_prefix}{$where_suffix} IS NULL
                                                                )
                                                        )
                                                    )";
                                        

                                        
                                                
                                    } else {
                                        $arr[] = "({$where_prefix}{$field->fields_name} $not {$operators[$condition['operator']]['sql']} ({$values}){$where_suffix})";
                                    }
                                    break;

                                case 'like':
                                case 'notlike':
                                    if (in_array(strtoupper($field->fields_type), array('VARCHAR', 'TEXT'))) {
                                        $arr[] = "({$where_prefix}{$field->fields_name} $not{$operators[$condition['operator']]['sql']} '%{$condition['value']}%'{$where_suffix})";
                                    }
                                    break;
                                case 'rangein':

                                    $arr[] = "$not ({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} int4range({$condition['value']}))";
                                    break;
                                default:

                                    if (in_array($field->fields_type, array('INT4RANGE', 'INT8RANGE'))) {
                                        // Se sto filtrando nei range prendo i limiti superiori/inferiori in base all'occorrenza
                                        switch ($condition['operator']) {
                                            // Se filtro per {range} le/lt {value} vuol dire che sto cercando
                                            // tutti i range il cui estremo superiore è le/lt del valore inserito
                                            // nel form di ricerca. Per ge/gt vale l'opposto
                                            case 'lt':
                                            case 'le':
                                                $field->fields_name = "UPPER({$field->fields_name})-1"; // nei range ho l'estremo sup. escluso e l'estremo inf. incluso
                                                $where_prefix = '';
                                                break;

                                            case 'ge':
                                            case 'gt':
                                                $field->fields_name = "LOWER({$field->fields_name})";
                                                $where_prefix = '';
                                                break;
                                        }
                                    }

                                    $arr[] = "$not({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} '{$condition['value']}'{$where_suffix})";
                            }
                        }
                    }
                }
            }
        }

        // Genero il where in stringa
        $where = implode(" AND ", $arr);

        $replaces['value_id'] = $value_id;
        if (is_array($additional_data) && $additional_data) {
            $replaces = array_merge($replaces, $additional_data);
        }

        // Rimpiazzo eventuali variabili dalla sessione e dagli additional data

        return $this->replace_superglobal_data(str_replace_placeholders($where, $replaces));
    }

    /**
     * DEPRECATO - Metodo forse non + usato dovrebbe passare tutto sul generate where ora.
     */
    public function get_grid_where($grid_id, $value_id = null)
    {
        $grid = $this->db->get_where('grids', array('grids_id' => $grid_id))->row_array();
        $entity = $this->get_entity($grid['grids_entity_id']);
        $arr = array();

        // Valuto se ho un id ingresso ed un where
        if (isset($value_id) && $value_id) {
            if ($grid['grids_where']) {
                $arr[] = str_replace('{value_id}', $value_id, $grid['grids_where']);
            } else {
                $arr[] = "{$entity['entity_name']}.{$entity['entity_name']}_id = '$value_id'";
            }
        } elseif ($grid['grids_where']) {
            // Per la grid è definito un where -> è plausibile che bisogni fare  il replace di variabili
            $arr[] = $this->replace_superglobal_data($grid['grids_where']);
        }

        // If builder_where specified, proceed by adding these conditions to $arr
        if (isset($value_id) && $value_id) {
            if (!empty($grid['grids_builder_where'])) {
                $arr[] = str_replace('{value_id}', $value_id, $grid['grids_builder_where']);
            }
        } elseif (!empty($grid['grids_builder_where'])) {
            // Per la grid è definito un where -> è plausibile che bisogni fare  il replace di variabili
            $arr[] = $this->replace_superglobal_data($grid['grids_builder_where']);
        }

        //debug($arr, true);
        // Applica il filtro => joinalo all'arr
        $sess_grid_data = $this->session->userdata(SESS_GRIDS_DATA);
        $operators = unserialize(OPERATORS);

        if (isset($sess_grid_data[$grid_id])) {
            foreach ($sess_grid_data[$grid_id] as $condition) {
                $query_field = $this->db->get_where('fields', array('fields_id' => (int) $condition['field_id']));
                if ($query_field->num_rows() && $query_field->row()->fields_name) {
                    $field = $query_field->row();
                    switch ($condition['operator']) {
                        case 'in':
                            $values = "'" . implode("','", explode(',', $condition['value'])) . "'";
                            $arr[] = "{$field->fields_name} {$operators[$condition['operator']]['sql']} ({$values})";
                            break;

                        case 'like':
                            if (in_array($field->fields_type, array('VARCHAR', 'TEXT'))) {
                                $arr[] = "{$field->fields_name} {$operators[$condition['operator']]['sql']} '%{$condition['value']}%'";
                            }
                            break;

                        default:
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

    public function replace_superglobal_data($string, $caseinsensitive = true, $clearunmatched = true)
    {
        // Fix per mantenere vecchia compatibilità con replace di sessioni login
        if (strpos($string, 'master_crm_login') !== false) {
            $string = str_replace('master_crm_login', SESS_LOGIN, $string);
        }

        $replaces = array_merge(
            ['post' => $this->input->post(), 'get' => $this->input->get()],
            $this->session->all_userdata()
        );

        return str_replace_placeholders($string, $replaces, $caseinsensitive, $clearunmatched);
    }

    public function fields_implode($fields)
    {
        $myarray = array();
        foreach ($fields as $field) {
            $myarray[] = (array_key_exists('alias', $field) ? "{$field['alias']}." : null) . $field['fields_name'];
        }
        $fields_imploded = implode(',', $myarray);
        return $fields_imploded;
    }

    /**
     * Post process
     */
    public function run_post_process($entity_id, $when, $data = array())
    {
        if (!is_numeric($entity_id) && is_string($entity_id)) {
            $entity = $this->get_entity_by_name($entity_id);
            $entity_id = $entity['entity_id'];
        }

        $post_process = $this->db
            ->join('fi_events', 'fi_events_post_process_id = post_process_id', 'LEFT')
            ->get_where(
                'post_process',
                array(
                    'post_process_entity_id' => $entity_id,
                    'post_process_when' => $when,
                    'post_process_crm' => DB_BOOL_TRUE,
                )
            );

        if ($post_process->num_rows() > 0) {
            foreach ($post_process->result_array() as $function) {
                // Se arrivo qua, potrei avere anche dei fi_events con action non gestita.
                //Es.: i fi_events di tipo custom code, creano anche il relativo pp che continuerà a funzionare senza problemi (è retro compatibile).
                //Le nuove action però (quindi non i custom code, che continueranno a funzionare con gli eval), devono avere una gestione ad hoc.

                switch ($function['fi_events_action']) {
                    case '':
                        eval ($function['post_process_what']);
                        break;
                    case 'curl':
                        $ch = curl_init();
                        $url = json_decode($function['fi_events_actiondata'], true)['url'];

                        // set URL and other appropriate options
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                        // grab URL and pass it to the browser
                        $result = curl_exec($ch);

                        // close cURL resource, and free up system resources
                        curl_close($ch);
                        break;
                    default:
                        debug($function, true);
                        break;
                }
            }
        }

        return $data;
    }

    /**
     * Costruzione link di dettaglio
     *
     * @param int|string $entityIdentifier
     * @param int $value_id
     * @param bool|string $modal
     * @return string
     */
    public function get_detail_layout_link($entityIdentifier, $value_id = null, $modal = false)
    {
        $cache_key = "apilib/datab.get_detail_layout_link.{$value_id}." . $this->auth->get(LOGIN_ENTITY . "_id") . '.' . $entityIdentifier;
        if (!($dati = $this->mycache->get($cache_key))) {
            // Che sia name o id a getEntity non importa...
            $entity_id = $this->crmentity->getEntity($entityIdentifier)['entity_id'];
            $baseRoute = 'main/layout';
            $suffix = '';
            $getSuffix = [];
            if ($modal) {
                $baseRoute = 'get_ajax/layout_modal';
                if (is_string($modal)) {
                    $getSuffix['_size'] = $modal;
                }
            }
            if ($getSuffix) {
                $suffix .= '?';
            }
            foreach ($getSuffix as $k => $v) {
                $suffix .= $k . '=' . $v;
            }
            $dati = isset($this->_accessibleEntityLayouts[$entity_id]) ? base_url("{$baseRoute}/{$this->_accessibleEntityLayouts[$entity_id]}/{$value_id}{$suffix}") : false;
            if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('apilib')) {
                $this->mycache->save($cache_key, $dati, self::CACHE_TIME, $this->layout->getRelatedEntities());
            }
        }


        return $dati;
    }

    public function generate_menu_link($menu, $value_id = null, $data = null)
    {
        $link = '';
        if ($menu['menu_layout'] && $menu['menu_layout'] != '-2') {
            $controller_method = (($menu['menu_modal'] == DB_BOOL_TRUE) ? 'get_ajax/layout_modal' : 'main/layout');
            $layou_id_or_identifier = (!empty($menu['layouts_identifier']) ? $menu['layouts_identifier'] : $menu['menu_layout']);
            $link = base_url("{$controller_method}/{$layou_id_or_identifier}") . $menu['menu_link'];
        } elseif ($menu['menu_form']) {
            $link = base_url("get_ajax/modal_form/{$menu['menu_form']}") . $menu['menu_link'];
        } elseif ($menu['menu_link']) {
            $link = str_replace('{base_url}', base_url(), $menu['menu_link']);
        }

        // Valuto se ho dati su cui fare il replace
        if (!is_null($value_id)) {
            $link = str_replace('{value_id}', $value_id, $link);
        }

        if ($data !== null && is_array($data)) {
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
    public function is_admin($user_id = null)
    {
        if ($user_id === null || $user_id == $this->auth->get(LOGIN_ENTITY . "_id")) {
            // Sto controllando me stesso

            return $this->auth->is_admin();
        } else {
            $query = $this->db->where('permissions_user_id', $user_id)->get('permissions');
            return ($query->num_rows() > 0 ? $query->row()->permissions_admin == DB_BOOL_TRUE : false);
        }
    }

    public function get_menu($position = 'sidebar')
    {
        // Prendi tutti i menu, con i sottomenu e poi ciclandoli costruisci un array multidimensionale
        $menu = $this->db->from('menu')->join('layouts', 'layouts.layouts_id = menu.menu_layout', 'left')
            ->where('menu_position', $position)->order_by('menu_order')->get()->result_array();

        $return = $subs = [];
        foreach ($menu as $key => $item) {
            if (!$this->conditions->accessible('menu', $item['menu_id'])) {
                unset($menu[$key]);
                continue;
            }

            if ($item['menu_parent']) {
                // Se c'è un parent è un sottomenu
                isset($subs[$item['menu_parent']]) or $subs[$item['menu_parent']] = array();
                $item['pages_names'] = array("layout_{$item['menu_layout']}");
                $subs[$item['menu_parent']][] = $item;
            } else {
                // Altrimenti potrebbe avere un sottomenu: imposto have_submenu
                // a false e predispongo l'array dei sottomenu
                $item['have_submenu'] = false;
                $item['submenu'] = array();
                $item['pages_names'] = array("layout_{$item['menu_layout']}");
                $return[$item['menu_id']] = $item;
            }
        }

        // Inserisci il sottomenu per ogni menu padre
        foreach ($subs as $parent => $items) {
            if (isset($return[$parent]['submenu'])) {
                // Dovrei avere realmente dei sottomenu.. se non ci sono vuol
                // dire che sono proibiti dai permessi...
                $return[$parent]['have_submenu'] = true;

                // Prendo i sottomenu che mi sono concessi...
                $return[$parent]['submenu'] = array_filter($items, function ($menu) {
                    return empty($menu['menu_layout']) or array_key_exists($menu['menu_layout'], $this->_accessibleLayouts);
                });

                foreach ($items as $item) {
                    $return[$parent]['pages_names'][] = "layout_{$item['menu_layout']}";
                }
            }
        }

        return array_filter($return, function ($menu) {
            // Il layout è accessibile per i permessi? (il link del menu è
            // considerato sempre accessibile se non punta ad un layout)
            if (!empty($menu['menu_layout']) && $menu['menu_layout'] != '-2' && !array_key_exists($menu['menu_layout'], $this->_accessibleLayouts)) {
                return false;
            }

            // Dato che il layout è accessibile, verifico se dovrebbe essere un
            // container di sottomenu e se effettivamente ha i sottomenu, perché
            // se `have_submenu` === true, allora `submenu` dev'essere pieno,
            // ---
            // se non lo fosse, allora tutte le voci del sottomenu sono bloccate
            // da permessi e quindi non voglio mostrare nemmeno il parent vuoto.
            $shouldHasSubmenu = $menu['have_submenu'];
            $hasReallySubmenu = count($menu['submenu']) > 0;

            return (!$shouldHasSubmenu or $hasReallySubmenu);
        });
    }

    public function getUserLimits($entity, $user = null)
    {
        if (!$user) {
            $user = $this->auth->get('id');
        }

        if (is_numeric($entity)) {
            $entityId = $entity;
        } else {
            if (!is_array($entity)) {
                $entity = $this->get_entity_by_name($entity);
            }
            $entityId = $entity['entity_id'];
        }

        $query = "SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = ? AND fields_entity_id = ?";
        return $this->db->query($query, [(int) $user, (int) $entityId])->result_array();
    }

    public function can_write_entity($entity_id)
    {
        if ($this->is_admin()) {
            return true;
        } else {
            // Resolve the entity id
            $entity_id = is_numeric($entity_id) ? $entity_id : $this->get_entity_by_name($entity_id)['entity_id'];

            $user_id = (int) $this->auth->get('id');
            $permissions = $this->db->from('permissions')
                ->join('permissions_entities', 'permissions_entities_permissions_id = permissions_id', 'left')
                ->where(array('permissions_user_id' => $user_id, 'permissions_entities_entity_id' => $entity_id))
                ->get()->row();

            return empty($permissions) || ($permissions->permissions_entities_value == PERMISSION_WRITE);
        }
    }

    public function can_read_entity($entity_id)
    {
        if ($this->is_admin()) {
            return true;
        } else {
            $user_id = (int) $this->auth->get('id');
            $permissions = $this->db->from('permissions')
                ->join('permissions_entities', 'permissions_entities_permissions_id = permissions_id', 'left')
                ->where(array('permissions_user_id' => $user_id, 'permissions_entities_entity_id' => $entity_id))
                ->get()->row();
            return empty($permissions) || ($permissions->permissions_entities_value != PERMISSION_NONE);
        }
    }

    public function can_access_layout($layout_id, $value_id = null)
    {
        if (!$layout_id) {
            return false;
        }

        if (!is_numeric($layout_id)) {
            $layout_id = $this->layout->getLayoutByIdentifier($layout_id);

            if (!$layout_id) {
                return false;
            }
        }

        if (isset($this->_accessibleLayouts[$layout_id]) or isset($this->_forwardedLayouts[$layout_id])) {
            return $this->conditions->accessible('layouts', $layout_id, $value_id);
        } else {
            return false;
        }
    }

    public function setPermissions($userOrGroup, $isAdmin, array $entitiesPermissions, array $modulesPermissions)
    {
        if (!$userOrGroup) {
            throw new Exception("Il nome gruppo o utente non può essere vuoto");
        }

        if ($isAdmin !== DB_BOOL_TRUE && $isAdmin !== DB_BOOL_FALSE) {
            $isAdmin = (is_bool($isAdmin) ? ($isAdmin ? DB_BOOL_TRUE : DB_BOOL_FALSE) : DB_BOOL_FALSE);
        }

        try {
            $perm = $this->getPermission($userOrGroup);

            $permId = $perm['permissions_id'];

            $update = [];
            if (is_numeric($userOrGroup)) {
                // Se faccio setPermissions(idUtente, ... ) automaticamente tolgo
                // l'utente dal gruppo
                $update['permissions_group'] = null;
            }

            if ($isAdmin !== $perm['permissions_admin']) {
                // Se è cambiato lo stato di amministratore per il vecchio
                // permesso, devo notificare la modifica
                $update['permissions_admin'] = $isAdmin;
            }

            if (count($update) > 0) {
                $this->db->update('permissions', $update, ['permissions_id' => $permId]);
            }
        } catch (Exception $ex) {
            $this->db->insert('permissions', [
                'permissions_user_id' => is_numeric($userOrGroup) ? $userOrGroup : null,
                'permissions_group' => is_numeric($userOrGroup) ? null : $userOrGroup,
                'permissions_admin' => $isAdmin,
            ]);
            $permId = $this->db->insert_id();
        }

        // $entitiesPermissions e $modulesPermissions devono essere array nella
        // forma $entityId => $permissionValue e $moduleName => $permissionValue
        $this->insertEntitiesPermissions($permId, $entitiesPermissions);
        $this->insertModulesPermissions($permId, $modulesPermissions);
        $this->fixPermissions();
    }

    public function getPermission($userOrGroup, $throwException = true)
    {
        if (!array_key_exists($userOrGroup, $this->_permissions)) {
            if (is_numeric($userOrGroup)) {
                // Is User
                $perm = $this->db->get_where('permissions', array('permissions_user_id' => $userOrGroup))->row_array();
            } else {
                // Is Group
                $perm = $this->db->where('permissions_user_id IS NULL')
                    ->get_where('permissions', array('permissions_group' => $userOrGroup))->row_array();
            }
            $this->_permissions[$userOrGroup] = $perm;
        }

        $perm = $this->_permissions[$userOrGroup];
        if (empty($perm)) {
            if ($throwException) {
                throw new Exception(sprintf('Nessun utente o gruppo trovato per %s', $userOrGroup));
            } else {
                $perm = false;
            }
        }

        return $perm;
    }

    public function removePermissionById($id)
    {
        $this->db->delete('permissions', ['permissions_id' => $id]);
        $this->fixPermissions();
    }

    public function fixPermissions()
    {
        // Cancella i permessi che non hanno più senso di esistere
        $this->db->where('permissions_user_id IS NOT NULL AND permissions_user_id NOT IN (SELECT ' . LOGIN_ENTITY . '_id FROM ' . LOGIN_ENTITY . ')')->delete('permissions');
        $this->db->where('permissions_entities_permissions_id NOT IN (SELECT permissions_id FROM permissions)')->delete('permissions_entities');
        $this->db->where('permissions_modules_permissions_id NOT IN (SELECT permissions_id FROM permissions)')->delete('permissions_modules');

        // Togli l'eventuale gruppo agli utenti se non esiste
        if ($this->db->dbdriver == 'postgre') {
            $this->db->query("
                    UPDATE permissions AS p1
                    SET permissions_group = NULL
                    WHERE (
                        p1.permissions_user_id IS NOT NULL AND
                        p1.permissions_group IS NOT NULL AND
                        NOT EXISTS (
                            SELECT *
                            FROM permissions AS p2
                            WHERE (
                                p2.permissions_user_id IS NULL AND
                                p1.permissions_group = p2.permissions_group
                            )
                        )
                    )
                ");
        } else {
            $this->db->query("
                    UPDATE permissions AS p1
                    SET permissions_group = NULL
                    WHERE (
                        p1.permissions_user_id IS NOT NULL AND
                        p1.permissions_group IS NOT NULL AND
                        NOT EXISTS (
                            SELECT *
                            FROM (SELECT * FROM permissions) AS p2
                            WHERE (
                                p2.permissions_user_id IS NULL AND
                                p1.permissions_group = p2.permissions_group
                            )
                        )
                    )
                ");
        }
    }

    public function addUserGroup($userId, $groupName)
    {
        if (!is_numeric($userId) or !is_string($groupName) or $userId < 1 or !$groupName) {
            throw new InvalidArgumentException('Impossibile aggiungere lo user al gruppo: $userId deve contenere un id valido e il nome deve essere una stringa');
        }

        if (!$this->db->where(LOGIN_ENTITY . '_id', $userId)->count_all_results(LOGIN_ENTITY)) {
            throw new Exception("L'utente '{$userId}' non esiste");
        }

        // Recupera permessi del gruppo
        $permissions = $this->getPermission($groupName);
        $permissionsEntities = $this->db->where('permissions_entities_entity_id IN (SELECT entity_id FROM entity)', null, false)->get_where(
            'permissions_entities',
            array(
                'permissions_entities_permissions_id' => $permissions['permissions_id'],

            )
        )->result_array();
        $permissionsModules = $this->db->get_where('permissions_modules', array('permissions_modules_permissions_id' => $permissions['permissions_id']))->result_array();

        $this->db->trans_start();

        // Cancella i permessi vecchi dell'utente
        $this->db->delete('permissions', array('permissions_user_id' => $userId));
        $this->fixPermissions();

        // Rimuovi il campo id dai permessi del gruppo ottenuto, in modo da
        // poterlo clonare e aggiungi l'id dell'utente
        unset($permissions['permissions_id']);
        $permissions['permissions_user_id'] = $userId;
        $this->db->insert('permissions', $permissions);
        $permissionId = $this->db->insert_id();

        // Rimappa i permessi entità/moduli in idEntità => permesso e
        // nomeModulo => permesso
        $this->insertEntitiesPermissions($permissionId, array_combine(array_key_map($permissionsEntities, 'permissions_entities_entity_id'), array_key_map($permissionsEntities, 'permissions_entities_value')));
        $this->insertModulesPermissions($permissionId, array_combine(array_key_map($permissionsModules, 'permissions_modules_module_name'), array_key_map($permissionsModules, 'permissions_modules_value')));

        // Assegnando un utente ad un gruppo devo anche assegnargli i layout che
        // può o non può vedere
        $this->assignUnallowedLayoutAsGroup($userId, $groupName);

        $this->db->trans_complete();
        return $permissionId;
    }

    public function insertEntitiesPermissions($permId, array $entitiesPermissions)
    {
        $this->db->delete('permissions_entities', ['permissions_entities_permissions_id' => $permId]);
        $entitiesPermissionsData = [];
        foreach ($entitiesPermissions as $entityId => $permissionValue) {
            $entitiesPermissionsData[] = ['permissions_entities_permissions_id' => $permId, 'permissions_entities_entity_id' => $entityId, 'permissions_entities_value' => $permissionValue];
        }

        if ($entitiesPermissionsData) {
            $this->db->insert_batch('permissions_entities', $entitiesPermissionsData);
        }
    }

    public function insertModulesPermissions($permId, array $modulesPermissions)
    {
        $this->db->delete('permissions_modules', ['permissions_modules_permissions_id' => $permId]);
        $modulesPermissionsData = [];
        foreach ($modulesPermissions as $moduleName => $permissionValue) {
            if ($moduleName === 0) { // Sembra che la nuova gestione moduli passi qualcosa di sbagliato e quindi qui arriva un modulo con nome '0'... ovviamente inesistente nel db. Skippo
                continue;
            }
            $modulesPermissionsData[] = ['permissions_modules_permissions_id' => $permId, 'permissions_modules_module_name' => $moduleName, 'permissions_modules_value' => $permissionValue];
        }

        if ($modulesPermissionsData) {
            $this->db->insert_batch('permissions_modules', $modulesPermissionsData);
        }
    }

    public function assignUnallowedLayoutAsGroup($userId, $groupName)
    {
        if (is_numeric($groupName)) {
            throw new Exception("Il nome gruppo non può essere numerico");
        }

        $old_unallowedLayouts = $this->db->get_where('unallowed_layouts', ['unallowed_layouts_user' => $userId]);

        // Elimino impostazioni accessi layout correnti per l'utente passato
        $this->db->delete('unallowed_layouts', ['unallowed_layouts_user' => $userId]);

        // Recupero viste permessi per l'utente corrente
        // Se non ne trovo ho finito, in quanto ho già eliminato le vecchie
        // impostazioni e non ci sono altri utenti da cui copiare i layout non
        // accessibili
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            $permissionWithGroup = $this->db
                ->where('permissions_user_id IS NOT NULL')
                ->where('permissions_user_id IN (SELECT ' . LOGIN_ENTITY . '_id FROM ' . LOGIN_ENTITY . ' WHERE ' . LOGIN_ACTIVE_FIELD . ' = \'' . DB_BOOL_TRUE . '\')')
                ->get_where('permissions', [
                    'permissions_group' => $groupName,
                    'permissions_user_id <>' => $userId,
                ]);
        } else {
            $permissionWithGroup = $this->db
                ->where('permissions_user_id IS NOT NULL')
                ->get_where('permissions', [
                    'permissions_group' => $groupName,
                    'permissions_user_id <>' => $userId,
                ]);
        }

        if (!$permissionWithGroup->num_rows()) {
            $permissionWithGroup = $this->db
                ->get_where('permissions', [
                    'permissions_user_id <>' => $userId,
                ]);
            //Riprendo i suoi vecchi unallowed...
            $unallowedLayouts = $old_unallowedLayouts;
        } else {
            // Anche qua recupero l'utente e i suoi accessi al layout, se non trovo
            // nulla significa che l'utente, e quindi il gruppo, può accedere a
            // qualunque layout
            $anotherUser = $permissionWithGroup->row()->permissions_user_id;
            $unallowedLayouts = $this->db->get_where('unallowed_layouts', ['unallowed_layouts_user' => $anotherUser]);
        }

        if (!$unallowedLayouts->num_rows()) {
            return;
        }

        // Rimappo ogni record in modo da cambiare lo user id e inserisco in
        // batch il tutto
        $newData = array_map(function ($row) use ($userId) {
            $row['unallowed_layouts_user'] = $userId;
            return $row;
        }, $unallowedLayouts->result_array());

        $this->db->insert_batch('unallowed_layouts', $newData);
    }

    public function getUserGroups()
    {
        $idField = LOGIN_ENTITY . '_id';

        //Fix per non prendere tutti gli utenti ma solo quelli che possono fare login
        if (defined('LOGIN_ACTIVE_FIELD') && !empty(LOGIN_ACTIVE_FIELD)) {
            $this->db->where("permissions_user_id IN (SELECT " . LOGIN_ENTITY . "_id FROM " . LOGIN_ENTITY . " WHERE " . LOGIN_ACTIVE_FIELD . " = '" . DB_BOOL_TRUE . "')", null, false);
        }

        $users = $this->db
            ->join('permissions', "{$idField} = permissions_user_id", 'left')
            ->where('permissions_user_id IS NOT NULL')->get(LOGIN_ENTITY)->result_array();
        $out = [];
        foreach ($users as $user) {
            $out[$user[LOGIN_ENTITY . '_id']] = $user['permissions_group'] ?: null;
        }

        return $out;
    }

    public function get_modules()
    {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        if ($this->is_admin($user_id)) {
            $modules = $this->db->get_where('modules', array('modules_installed' => DB_BOOL_TRUE));
        } else {
            $modules = $this->db->select('modules.*')->from('modules')->join('permissions_modules', 'permissions_modules_module_name = modules_name', 'left')->join('permissions', 'permissions_modules_permissions_id = permissions_id', 'left')->where(
                array(
                    'permissions_modules_value' => PERMISSION_WRITE,
                    'modules_installed' => DB_BOOL_TRUE,
                    'permissions_user_id' => $user_id,
                )
            )->get();
        }

        return $modules->result_array();
    }

    public function module_installed($name)
    {

        // $query = $this->db->from('modules')->where('modules_installed', DB_BOOL_TRUE)->where("(modules_name = '{$name}' OR modules_identifier = '{$name}')", null, false)->get();
        // return $query->num_rows() > 0;

        return $this->module->moduleExists($name, true);
    }

    public function module_access($name)
    {
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
    public function get_search_results($search)
    {
        //Ottengo le entità cercabili
        $entities = $this->db->get_where('entity', array('entity_searchable' => DB_BOOL_TRUE))->result_array();
        $e_ids = array_map(function ($entity) {
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
                $result = $this->getDataEntity($entity['entity_id'], $where, null, 0, null, 1, false, [], ['group_by' => null]);
                if ($result) {
                    $results[] = [
                        'entity' => $entity,
                        'visible_fields' => $this->crmentity->getVisibleFields($entity['entity_id']),
                        'data' => $result,
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Trash results
     */
    public function get_trash_results()
    {
        // Get entity with soft delete
        $entities = $this->db->query("SELECT *, JSON_UNQUOTE(JSON_EXTRACT(entity_action_fields,'$.soft_delete_flag')) AS entity_soft_delete_field FROM entity WHERE JSON_EXTRACT(entity_action_fields,'$.soft_delete_flag') IS NOT NULL")->result_array();

        $e_ids = array_map(function ($entity) {
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
                $where = $entity['entity_soft_delete_field'] . " = " . DB_BOOL_TRUE;

                //Calcola risultato e consideralo sse ha dati effettivi
                $result = $this->getDataEntity($entity['entity_id'], $where, null, 0, null, 1, false, [], ['group_by' => null]);
                if ($result) {
                    $results[] = [
                        'entity' => $entity,
                        'visible_fields' => $this->crmentity->getVisibleFields($entity['entity_id']),
                        'data' => $result,
                    ];
                }
            }
        }

        return $results;
    }

    public function search_like($search = '', $fields = array())
    {
        $outer_where = array();

        //Pulisco eventuali field doppi
        $fields_ids = [];

        if ($search) {
            $maxint4 = 2147483647; // Max per int4

            /** FIX: Cerco gli eventuali support fields di un singolo field e li metto in un array al più bidimensionale * */
            $_fields = array();

            foreach ($fields as $field) {
                if (in_array($field['fields_id'], $fields_ids) && $field['fields_id'] != '') {
                    continue;
                }
                if (empty($field['support_fields'])) {
                    if (isset($field['fields_type']) && isset($field['fields_name'])) {
                        $_fields[] = $field;
                    } else {
                        //Se entro qui potrebbe essere un eval cachable...
                        $_fields[] = $field;
                    }
                } else {
                    foreach ($field['support_fields'] as $sfield) {
                        if (isset($sfield['fields_type']) && isset($sfield['fields_name']) && isset($sfield['fields_preview']) && $sfield['fields_preview'] === DB_BOOL_TRUE) {
                            $_fields[] = $sfield;
                        }
                    }
                }
                $fields_ids[] = $field['fields_id'];
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
            if (!defined('EXPLODE_SPACES') || EXPLODE_SPACES === true) {
                $search_chunks = array_unique(array_filter(explode(' ', $search), function ($chunk) {
                    return $chunk && strlen($chunk) > (defined('MIN_SEARCH_CHARS') ? (MIN_SEARCH_CHARS - 1) : 2);
                }));
            } else {
                $search_chunks = array_unique(array_filter([$search], function ($chunk) {
                    return $chunk && strlen($chunk) > (defined('MIN_SEARCH_CHARS') ? (MIN_SEARCH_CHARS - 1) : 2);
                }));
            }
            if (empty($search_chunks)) {
                $search_chunks[] = trim($search);
            }

            // Sono interessato ai record che contengono TUTTI i chunk in uno o più campi
            foreach ($search_chunks as $_chunk) {
                $chunk = str_replace("'", "''", $_chunk);
                $inner_where = [];
                foreach ($fields as $field) {
                    if (!empty($field['fields_type'])) {
                        switch (($type = strtoupper($field['fields_type']))) {
                            case 'VARCHAR':
                            case 'TEXT':
                            case 'LONGTEXT':
                                if ($this->db->dbdriver != 'postgre') {
                                    $chunk = strtolower($chunk);
                                    $inner_where[] = "LOWER({$field['fields_name']}) LIKE '%{$chunk}%'";
                                } else {
                                    $inner_where[] = "({$field['fields_name']}::TEXT ILIKE '%{$chunk}%')";
                                }

                                break;

                            case 'INT':
                            case 'INTEGER':
                                if (is_numeric($chunk) && $chunk <= $maxint4) {
                                    $i_chunk = (int) $chunk;
                                    $inner_where[] = "({$field['fields_name']} = '{$i_chunk}')";
                                }
                                break;

                            case 'FLOAT':
                                if (is_numeric($chunk)) {
                                    $f_chunk = (float) $chunk;
                                    $inner_where[] = "({$field['fields_name']} = '{$f_chunk}')";
                                }
                                break;
                        }
                    } else {
                        if (!empty($field['grids_fields_eval_cache_data'])) {
                            if ($this->db->dbdriver != 'postgre') {
                                $chunk = strtolower($chunk);
                                $inner_where[] = "LOWER({$field['grids_fields_eval_cache_data']}) LIKE '%{$chunk}%'";
                            } else {
                                $inner_where[] = "({$field['grids_fields_eval_cache_data']}::TEXT ILIKE '%{$chunk}%')";
                            }
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
    public function search_like_orderby($search = '', $fields = array())
    {
        $order_by = [];

        $search = $this->db->escape_str($search);
        foreach ($fields as $field) {
            if (!empty($field['fields_name'])) {
                $order_by[] = "COALESCE(INSTR({$field['entity_name']}.{$field['fields_name']}, '$search'), 0)";
            } elseif (!empty($field['grids_fields_eval_cache_data'])) {
                $order_by[] = "COALESCE(INSTR({$field['grids_fields_eval_cache_data']}, '$search'), 0)";
            } else {
                continue;
            }
        }

        return implode(',', $order_by);
    }
    private function is_layout_cachable($layout_id)
    {
        return $this->db->get_where('layouts', array('layouts_id' => $layout_id, 'layouts_cachable' => DB_BOOL_TRUE))->num_rows() == 1;
    }

    /**
     * Layout builder
     */
    public function build_layout($layout_id, $value_id, $layout_data_detail = null)
    {

        $cache_key = "apilib/datab.build_layout.{$layout_id}.{$value_id}." . md5(serialize($_GET) . serialize($_POST) . serialize($layout_data_detail) . serialize($this->session->all_userdata()));
        if (!($dati = $this->mycache->get($cache_key))) {
            if (!is_numeric($layout_id) or ($value_id && !is_numeric($value_id))) {
                return null;
            }

            if (isset($this->_forwardedLayouts[$layout_id])) {
                $entity_id = $this->_forwardedLayouts[$layout_id];
            }

            // ========================================
            // Start Build Layout
            // ========================================
            $this->layout->addLayout($layout_id);

            $dati['layout_container'] = $this->layout->getLayout($layout_id);

            if (empty($dati['layout_container'])) {
                return [
                    'pre-layout' => '',
                    'post-layout' => '',
                    'layout' => [],
                ];
                //show_404();
            }

            if ($value_id && $dati['layout_container']['layouts_entity_id'] > 0 && $layout_data_detail == null) {
                $entity = $this->crmentity->getEntity($dati['layout_container']['layouts_entity_id']);
                if (isset($entity['entity_name'])) {
                    $this->layout->addRelatedEntity($entity['entity_name'], $value_id);
                    //debug($entity);
                    $data_entity = $this->getDataEntity($entity['entity_id'], ["{$entity['entity_name']}.{$entity['entity_name']}_id" => $value_id], 1);

                    $layout_data_detail = array_shift($data_entity);
                }
            }

            if (is_null($layout_data_detail) && $dati['layout_container']['layouts_is_entity_detail'] === DB_BOOL_TRUE) {
                $this->layout->removeLastLayout($layout_id);
                //debug($dati['layout_container'], true);
                return null;
            }

            $layouts = $this->layout->getBoxes($layout_id, $value_id);

            if ($dati['layout_container']['layouts_pdf'] != DB_BOOL_TRUE) {
                $dati['pre-layout'] = $this->getHookContent('pre-layout', $layout_id, $value_id);
                $dati['post-layout'] = $this->getHookContent('post-layout', $layout_id, $value_id);
            } else {
                $dati['pre-layout'] = $this->getHookContent('pre-layout', $layout_id, $value_id, false);
                $dati['post-layout'] = $this->getHookContent('post-layout', $layout_id, $value_id, false);
            }

            $dati['layout'] = array();


            // Ricavo il content se necessario
            foreach ($layouts as $layout) {
                // Recupero del contenuto del layout
                // ---
                // Precedentemente questa operazione veniva effettuata in questo
                // punto, ma per motivi di dimensione e complessità della procedura
                // è stata spostata in un metodo a se `getBoxContent`
                $start = microtime(true);
                $layout['content'] = $this->getBoxContent($layout, $value_id, $layout_data_detail, $dati['layout_container']);
                if ($this->output->enable_profiler) {

                    $this->_layout_boxes_benchmark[$layout['layouts_boxes_title']] =
                        [
                            'container' => $dati['layout_container']['layouts_title'],
                            'layouts_id' => $dati['layout_container']['layouts_id'],
                            'time' => elapsed_time($start)
                        ]
                    ;
                    //debug("{$layout['layouts_boxes_title']}: " . elapsed_time($start));
                }

                // Fa il wrap degli hook pre e post che devono esistere per ogni
                // componente ad eccezione di custom views e custom php code
                // ---
                // Gli hook per il layout non vengono definiti da qua ma vengono
                // presi globali all'inizio del build layout
                $hookSuffix = $layout['layouts_boxes_content_type'];
                $hookRef = $layout['layouts_boxes_content_ref'];

                if ($hookSuffix && is_numeric($hookRef) && $hookSuffix !== 'layout') {
                    $layout['content'] = $this->getHookContent('pre-' . $hookSuffix, $hookRef, $value_id) .
                        $layout['content'] .
                        $this->getHookContent('post-' . $hookSuffix, $hookRef, $value_id);
                }

                $dati['layout'][$layout['layouts_boxes_row']][] = $layout;
            }

            // I dati del record di dettaglio
            if (!empty($layout_data_detail)) {
                $replaces = $layout_data_detail;
                $replaces['value_id'] = $value_id;

                $dati['layout_container']['layouts_title'] = str_replace_placeholders($dati['layout_container']['layouts_title'], $replaces);
                $dati['layout_container']['layouts_subtitle'] = str_replace_placeholders($dati['layout_container']['layouts_subtitle'], $replaces);
            }
            $dati['layout_data_detail'] = $layout_data_detail;
            //TODO: remove apilib and create another cache type for html_blocks!
            if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('template_assets') && $this->is_layout_cachable($layout_id)) {
                //debug($this->layout->getRelatedEntities(), true);
                $this->mycache->save($cache_key, $dati, self::CACHE_TIME, $this->layout->getRelatedEntities());
            }
            // ========================================
            // Fine Build Layout
            // ========================================
            $this->layout->removeLastLayout($layout_id);
        }

        return $dati;
    }

    /**
     * Carica una custom view
     *
     * @param string $viewName
     * @param array $data
     * @param bool $return
     */
    public function loadCustomView($viewName, $data = [], $return = false)
    {
        //if (file_exists(FCPATH . "application/views_adminlte/custom/{$viewName}.php") || file_exists(FCPATH . "application/views_adminlte/custom/{$viewName}")) {
        return $this->load->view("{$viewName}", $data, $return);
        //} else {
        //    return $this->load->view("pages/layouts/custom_views/{$viewName}", $data, $return);
        //}
    }

    /**
     * Renderizza contenuto di un layout
     *
     * @param string $hookType
     * @param int|string $hookRef
     * @param int|null $valueId
     * @return string
     */
    public function getHookContent($hookType, $hookRef, $valueId = null, $include_every_layouts = true)
    {
        $hooks_by_type = array_get($this->_precalcHooks(), $hookType, []);
        $hooks = array_filter($hooks_by_type, function ($hook) use ($hookRef, $include_every_layouts) {
            return ($hook['hooks_ref'] == $hookRef or (!$hook['hooks_ref'] && $include_every_layouts));
        });

        $plainHookContent = trim(implode(PHP_EOL, array_key_map($hooks, 'hooks_content', '')));

        if (!$plainHookContent) {
            return '';
        } else {
            $this->executed_hooks[] = array("type" => $hookType, "ref" => $hookRef, "value_id" => $valueId, 'hooks' => $hooks);
        }

        ob_start();
        $value_id = $valueId; // per comodità e uniformità...
        eval (' ?> ' . $plainHookContent . ' <?php ');
        return ob_get_clean();
    }

    private function _precalcHooks()
    {
        if (is_null($this->_hooks)) {
            $hooks = $this->db->order_by('hooks_order')->get('hooks')->result_array();

            $this->_hooks = [];

            // Raggruppo gli hook per tipo
            foreach ($hooks as $h) {
                $this->_hooks[$h['hooks_type']][] = $h;
            }
        }

        return $this->_hooks;
    }

    /**
     * Build della cella
     */
    public function build_grid_cell($field, &$dato, $escape_date = true, $crop = true, $download = false)
    {
        // Valuta eventuali grid fields eval e placeholder
        $type = isset($field['grids_fields_replace_type']) ? $field['grids_fields_replace_type'] : 'field';

        switch ($type) {
            // case 'placeholder':
            //     $return = $this->buildPlaceholderGridCell($field['grids_fields_replace'], $dato);
            //     break;

            case 'eval':
            case 'placeholder':
                //debug($dato['flussi_cassa_importo']);
                $field['grids_fields_replace'] = $this->buildEvalGridCell($field['grids_fields_replace'], $dato, $field);
                $return = $this->buildPlaceholderGridCell($field['grids_fields_replace'], $dato);
                //debug($return);

                break;
            case 'field':
            default:
                $return = $this->buildFieldGridCell($field, $dato, true, $escape_date, $crop, $download);
                break;
        }
        if ($download == false) {
            $inline_actions = $this->buildInlineActions($field, $dato);
            return $return . $inline_actions;
        } else {
            return $return;
        }

    }
    private function buildInlineActions($field, $dato)
    {
        //TODO: if field_ref not empty, grab default grid of that entity, then grab those actions...

        if (!empty($field['fields_ref']) && $this->crmentity->entityExists($field['fields_ref']) && $grid_db = $this->crmentity->getDefaultGrid($field['fields_ref'])) {
            $skip_delete = true;
            $id_record = $dato[$field['fields_name']];
            $grid_id = $grid_db['grids_id'];
            $grid = $this->get_grid($grid_id);
        } elseif (array_key_exists('grids_fields_grids_id', $field)) {
            $grid = $this->datab->get_grid($field['grids_fields_grids_id']);
            if (!empty($dato[$grid['grids']['entity_name'] . "_id"])) {
                $id_record = $dato[$grid['grids']['entity_name'] . "_id"];
                $skip_delete = false;
            } else {
                return '';
            }
        } else {
            return '';
        }

        $return = (!empty($field['grids_fields_with_actions']) && $field['grids_fields_with_actions'] == DB_BOOL_TRUE) ? $this->load->view('box/grid/inline_actions', [
            'skip_delete' => $skip_delete,
            'links' => $grid['grids']['links'],
            'id' => $id_record,
            'row_data' => $dato,
            'grid' => $grid['grids'],
        ], true) : '';

        return $return;
    }
    private function buildFieldGridCell($field, $dato, $processMultilingual, $escape_date = true, $crop = true, $download = false)
    {
        // =====================================================================
        // Controllo multilingua:
        // Se il field è multilingua allora ciclo tutte le lingue e le stampo
        // una dopo l'altra
        $multilingual = defined('LANG_ENTITY') && LANG_ENTITY && $field['fields_multilingual'] == DB_BOOL_TRUE;
        $value = array_key_exists($field['fields_name'], $dato) ? $dato[$field['fields_name']] : '';



        if ($processMultilingual && $multilingual) {
            if (!$value) {
                return '';
            }

            $out = [];
            $contents = json_decode($value, true);
            if (is_array($contents)) {
                foreach ($contents as $idLang => $valueLang) {
                    $dato[$field['fields_name']] = $valueLang;
                    $style = ($idLang != $this->_currentLanguage) ? 'style="display:none"' : '';
                    $out[] = "<div data-lang='{$idLang}' {$style}>" . $this->buildFieldGridCell($field, $dato, false) . '</div>';
                }
            } else { // 20231129 - michael - ho aggiunto questo else perchè da qualche parte si è modificato il sistema di traduzione dei campi di tipo multilingua, di conseguenza nelle grid apparivano le celle vuote in caso di multilang
                return $value;
            }

            return implode(PHP_EOL, $out);
        }

        // =====================================================================
        // Controllo se il campo è stampabile
        //
        // Controllo se il campo è vuoto: in tal caso, se ho un placeholder
        // stampo quello altrimenti non ritorno niente
        $isEmptyString = ($value === '');
        $isRefWithoutValue = ($field['fields_ref'] && !$value);

        if ($isEmptyString or $isRefWithoutValue) {
            // Il campo non è stampabile, quindi torno il placeholder se ce l'ho
            $placeholder = trim($field['fields_draw_placeholder']);
            return $placeholder ?
                sprintf('<small class="text-muted">%s</small>', $placeholder) : '';
        }

        // =====================================================================
        // Stampa del campo
        //



        if (($field['fields_ref'] || $field['fields_additional_data']) && in_array($field['fields_type'], [DB_INTEGER_IDENTIFIER, 'INT']) && $field['fields_draw_html_type'] != 'multi_upload') {


            if (is_array($value)) {
                // Ho una relazione molti a molti - non mi serve alcuna
                // informazione sui field ref, poiché ho già la preview stampata
                $referenced = $this->crmentity->getReferencedEntity($field);
                $lnk = $referenced ? $this->get_detail_layout_link($referenced['entity_id']) : false;
                if ($download) {

                    $result = array_values($value);
                    return implode(';', $result);

                } elseif ($lnk) {
                    foreach ($value as $id => $name) {
                        $value[$id] = anchor("{$lnk}/{$id}", $name ?: t('view'));
                    }
                }
                return implode('<br/>', $value);
            } elseif (!empty($field['support_fields'])) {
                // Ho un field ref semplice - per stamparlo ho bisogno dei
                // support fields (che sono i campi preview dell'entità
                // referenziata)



                $link = $value ? $this->get_detail_layout_link($field['support_fields'][0]['fields_entity_id']) : false;
                $idKey = $field['fields_ref'] . '_id';

                if (empty($field['support_fields'])) {
                    // Non ho nessun campo di preview, quindi la preview sarà vuota - stampo solo l'ID del record
                    $text = $value;
                } else {
                    $hasAllFields = true;
                    $_text = array();

                    //Check if entity has a special/custom preview rule
                    $field_ref_entity = $this->get_entity_by_name($field['fields_ref']);
                    $entity_preview = ($field_ref_entity['entity_preview_custom'] ?? false);
                    if (!$entity_preview) {
                        $entity_preview = ($field_ref_entity['entity_preview_base'] ?? false);
                    }

                    //Check if entity_preview_base or custom is set
                    if ($entity_preview) {
                        $text = str_replace_placeholders($entity_preview, $dato, true, true);
                        //debug($text,true);
                    } else {
                        foreach ($field['support_fields'] as $support_field) {
                            $prefixedKey = $field['fields_name'] . '_' . $support_field['fields_name'];
                            $simpleKey = $support_field['fields_name'];

                            if (array_key_exists($prefixedKey, $dato)) {
                                // Il caso migliore:    entitàReferenziata_entitàPrincipale_nomeBaseCampo
                                $previewSegment = '';
                                if ($support_field['fields_multilingual'] === DB_BOOL_TRUE) {
                                    $contents = json_decode($dato[$prefixedKey], true);
                                    foreach ($contents as $idLang => $valueLang) {
                                        $style = ($idLang != $this->_currentLanguage) ? 'style="display:none"' : '';
                                        $previewSegment .= "<div data-lang='{$idLang}' {$style}>" . $valueLang . '</div>';
                                    }
                                } else {
                                    $previewSegment = $dato[$prefixedKey];
                                }
                                $_text[] = $previewSegment;
                            } elseif (array_key_exists($simpleKey, $dato) && (!array_key_exists($idKey, $dato) or $dato[$idKey] == $value)) {
                                // Appendo il nuovo campo preview all'array della preview $_text
                                // Attenzione qua però, se l'id è settato ed è
                                // diverso dal mio value id allora non va bene
                                // prendere questo
                                $previewSegment = '';
                                if ($support_field['fields_multilingual'] === DB_BOOL_TRUE) {
                                    $contents = json_decode($dato[$simpleKey], true);
                                    if (is_array($contents)) {
                                        foreach ($contents as $idLang => $valueLang) {
                                            $style = ($idLang != $this->_currentLanguage) ? 'style="display:none"' : '';
                                            $previewSegment .= "<div data-lang='{$idLang}' {$style}>" . $valueLang . '</div>';
                                        }
                                    } else {
                                        $previewSegment = $dato[$prefixedKey];
                                    }
                                } else {
                                    $previewSegment = $dato[$simpleKey];
                                }
                                $_text[] = $previewSegment;
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
                            my_log('debug', "Query data select empty for key '{$idKey}'. Check grid '{$field['grids_fields_grids_id']}'. Running avoidable query...");
                            $preview = $this->get_entity_preview_by_name($field['fields_ref'], "{$idKey} = '{$value_id}'", 1);
                            $text = array_key_exists($value_id, $preview) ? $preview[$value_id] : $value_id;
                        }
                    }
                }

                // C'è un link? stampo un <a></a> altrimenti stampo il testo puro e semplice
                return $link ? anchor(rtrim($link, '/') . '/' . $value, $text) : $text;
            } elseif (strpos($field['fields_additional_data'], '{query:') === 0) {
                $query = str_replace(['{query:', '}'], '', $field['fields_additional_data']);
                $support_data = $this->db->query($query)->result_array();
                $support_data_remap = array_combine(array_column($support_data, 'id'), array_column($support_data, 'value'));
                $value = $support_data_remap[$value] ?? $value;
                return $value;
            }
        } elseif ($field['fields_preview'] == DB_BOOL_TRUE) {

            $link = $value ? $this->get_detail_layout_link($field['fields_entity_id']) : false;

            $entity = $this->get_entity($field['fields_entity_id']);
            $idKey = $entity['entity_name'] . '_id';
            $text = $value;

            if (array_key_exists($idKey, $dato)) {

                return $link ? anchor(rtrim($link, '/') . '/' . $dato[$idKey], $text) : $text;
            } else {
                return $text;
            }
        } elseif ($field['grids_fields_switch_inline'] == DB_BOOL_TRUE) {

            $entity = $this->get_entity($field['fields_entity_id']);
            $idKey = $entity['entity_name'] . '_id';
            return $this->load->view('box/grid/switch_bool', ['field' => $field, 'value' => $value, 'row_id' => $dato[$idKey]], true);
        } else {
            // Posso stampare il campo in base al tipo
            switch ($field['fields_draw_html_type']) {
                case 'upload':
                    if ($value && file_exists(FCPATH . "uploads/$value")) {
                        $file = mime_content_type(FCPATH . "uploads/$value");
                        $doc_ext = array('doc', 'docx', 'ods', 'odt', 'html', 'htm', 'xls', 'xlsx', 'ppt', 'pptx', 'txt');
                        $ext = pathinfo($value, PATHINFO_EXTENSION);
                        if (in_array($ext, $doc_ext)) {
                            return "<a href='" . base_url_uploads("uploads/$value") . "' class='fancybox'><img width='30px' height='30px' src='" . base_url("images/download.png") . "'>Download file</a>";
                        } elseif (strstr($file, "audio/")) {
                            // this code for audio
                            return "<a href='" . base_url("uploads/$value") . "' target='_blank'>Ascolta file</a>";
                        } elseif (strstr($file, "video/")) {
                            // this code for video
                            //return"<video controls><source src=".base_url_uploads("uploads/$value")." type='video/mp4'>Your browser does not support the video tag.</video>";
                            //return $this->load->view('layout/modal_link_file', ['file' => $file, 'value' => '_blank');
                            /*return "<a href=".base_url_uploads("uploads/$value")." class='js_open_modal'>ciao</a>";
                            $this->load->view("layout/modal_container", array(
                            'size' => $modalSize,
                            'title' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title'])),
                            'subtitle' => ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_subtitle'])),
                            'content' => $pagina,
                            'footer' => null
                            ));*/
                            return anchor(base_url_uploads("uploads/$value"), 'Download file', array('class' => 'js_open_modal_link_file'));
                        } elseif (strstr($file, "image/")) {
                            return "<a href='" . base_url_uploads("uploads/$value") . "' class='fancybox'><img src='" . base_url_uploads("thumb/50/50/1/uploads/$value") . "'></a>";
                        } else {
                            return anchor(base_url_uploads("uploads/$value"), 'Download file', array('class' => 'js_open_modal_link_file'));
                        }
                    } else {
                        return "";
                    }

                // no break
                case 'upload_image':
                    if ($value) {
                        if ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) {
                            $_url = base_url_uploads("uploads/{$value}");
                        } else {
                            $_url = base_url_admin("thumb/50/50/1/uploads/{$value}");
                        }

                        return anchor(base_url_uploads("uploads/{$value}"), "<img src='" . $_url . "' />", array('class' => 'fancybox', 'rel' => 'group'));
                    } else {
                        $path = base_url_admin('images/no-image-50x50.gif');
                        return "<img src='{$path}' style='width: 50px;' />";
                    }
                case 'single_upload':
                case 'signature':
                    if (!empty($value)) {
                        $item = json_decode($value, true);

                        $filename = is_array($item) ? $item['client_name'] : 'Download file';
                        $value = is_array($item) ? $item['path_local'] : $value;

                        if (file_exists(FCPATH . "uploads/$value")) {
                            $file = mime_content_type(FCPATH . "uploads/$value");
                            $doc_ext = array('doc', 'docx', 'ods', 'odt', 'html', 'htm', 'xls', 'xlsx', 'ppt', 'pptx', 'txt');
                            $ext = pathinfo($value, PATHINFO_EXTENSION);
                            if (in_array($ext, $doc_ext)) {
                                return "<a href='" . base_url_uploads("uploads/$value") . "' class='fancybox'><img width='30px' height='30px' src=" . base_url("images/download.png") . ">{$filename}</a>";
                            } elseif (strstr($file, "audio/")) {
                                // this code for audio
                                return "<a href='" . base_url("uploads/$value") . "' target='_blank'>Ascolta file</a>";
                            } elseif (strstr($file, "video/")) {
                                return anchor(base_url_uploads("uploads/$value"), 'Download ' . $filename, array('class' => 'js_open_modal_link_file'));
                            } elseif (strstr($file, "image/")) {
                                return "<a href='" . base_url_uploads("uploads/$value") . "' class='fancybox'><img src='" . base_url_uploads("thumb/50/50/1/uploads/$value") . "' alt='{$filename}'></a>";
                            } else {
                                return anchor(base_url_uploads("uploads/$value"), $filename, array('class' => 'js_open_modal_link_file'));
                            }
                        } else {
                            return "";
                        }
                    } else {
                        return "";
                    }
                // no break
                case 'multi_upload':
                case 'multi_upload_no_preview':
                    if (in_array($field['fields_type'], ['JSON', 'LONGTEXT', 'TEXT'])) {
                        $value = (array) json_decode($value, true);
                        $value = array_map(function ($item) {
                            if (in_array($item['file_type'], ['image/jpeg', 'image/png'])) {
                                if ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) {
                                    $_url = base_url_uploads("uploads/{$item['path_local']}");
                                } else {
                                    $_url = base_url_admin("thumb/50/50/1/uploads/{$item['path_local']}");
                                }
                                return anchor(base_url_uploads("uploads/{$item['path_local']}"), "<img src='" . $_url . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px', 'rel' => 'group'));
                            } else {
                                // if ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) {
                                //     $_url = base_url_uploads("uploads/{$item['path_local']}");
                                // } else {
                                //     $_url = base_url_admin("uploads/{$item['path_local']}");
                                // }
                                //TODO: find icons
                                switch ($item['file_type']) {
                                    case '___application/pdf':
                                        $img = 'document_pdf.png';
                                        break;
                                    default:
                                        $img = 'document.png';
                                        break;
                                }
                                $_url = base_url("get_ajax/preview_pdf/" . rtrim(base64_encode("uploads/" . $item['path_local']), '='));

                                //TODO: check mime type pdf, word, xls, ecc...
                                return anchor($_url, "<img src=\"" . base_url("images/$img") . "\" style='width: 50px;'/>", array('style' => 'width:50px', 'class' => 'js_open_modal'));
                            }
                        }, $value);
                    } else { //Se arrivo qua i file sono scritti su un altra tabella, quindi mi arriva già l'array bello pulito con i file...
                        $value = array_map(function ($item) {
                            if ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) {
                                $_url = base_url_uploads("uploads/{$item}");
                            } else {
                                $_url = base_url_admin("thumb/50/50/1/uploads/{$item}");
                            }
                            return anchor(base_url_uploads("uploads/{$item}"), "<img src='" . $_url . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px', 'rel' => 'group'));
                        }, $value);
                    }

                    $value = implode(' ', $value);

                    if ($value) {
                        return $value;
                    } else {
                        $path = base_url_admin('images/no-image-50x50.gif');
                        return "<img src='{$path}' style='width: 50px;' />";
                    }

                // no break
                case 'textarea':
                    $style = 'white-space: pre-line';
                // no break
                case 'wysiwyg':
                    if (empty($style)) {
                        $style = '';
                    }

                    $stripped = strip_tags($value);
                    $value = preg_replace(array('#<script(.*?)>(.*?)</script>#is', '/<img[^>]+\>/i'), '', $value);

                    if (strlen($stripped) > 150 && $crop) {
                        $textContainerID = md5($value);
                        $javascript = "event.preventDefault();$(this).parent().hide(); $('.text_{$textContainerID}').show();";

                        return '<div><div onclick="' . $javascript . '" style="cursor:pointer;">' . nl2br(character_limiter($stripped, 130)) . '</div>' .
                            '<a onclick="' . $javascript . '" href="#">Vedi tutto</a></div>' .
                            '<div class="text_' . $textContainerID . '" style="display:none;' . $style . '">' . (($field['fields_draw_html_type'] == 'textarea') ? nl2br($stripped) : $value) . '</div>';
                    } else {
                        return (($field['fields_draw_html_type'] == 'textarea') ? nl2br($stripped) : $value);
                    }

                // no break
                case 'date':

                    if ($escape_date && $value) {
                        $append = "<span class='hide'>{$value}</span>";
                    } else {
                        $append = '';
                    }
                    return $value ? $append . dateFormat($value) : null;

                case 'date_time':
                    if ($escape_date && $value) {
                        $append = "<span class='hide'>{$value}</span>";
                    } else {
                        $append = '';
                    }

                    return $value ? $append . dateTimeFormat($value) : null;

                case 'stars':
                    $out = "<span class='hide'>{$value}</span>";
                    for ($i = 1; $i <= 5; $i++) {
                        $class = $i > $value ? 'fa-star-o' : 'fa-star';
                        $out .= "<i class='fa {$class}'></i>";
                    }
                    return $out;

                case 'radio':
                case 'checkbox':

                    return (($field['fields_type'] == DB_BOOL_IDENTIFIER) ? (($value == DB_BOOL_TRUE) ? t('Yes') : t('No')) : $value);
                case 'color':
                case 'color_palette':

                    return "<div style=\"background-color:{$value};width:20px;height:20px;\"></div>";

                case 'multiple_values':
                    if (is_array(json_decode(html_entity_decode($value), true))) {
                        $values = json_decode(html_entity_decode($value), true);
                        $val_string = "";
                        foreach ($values as $val) {
                            $val_string .= "<li>" . $val . "</li>";
                        }
                        return '<ul class="ul_multiple_key_values">' . $val_string . '</ul>';
                    } else {
                        return $value;
                    }

                // no break
                case 'multiple_key_values':
                    if (is_array(json_decode(html_entity_decode($value), true))) {
                        $values = json_decode(html_entity_decode($value), true);
                        $val_string = "";

                        foreach ($values as $val) {
                            if ($val['key'] && $val['key']) {
                                $val_string .= "<li><strong>" . $val['key'] . "</strong>: " . $val['value'] . "</li>";
                            }
                        }
                        return '<ul class="ul_multiple_key_values">' . $val_string . '</ul>';
                    } else {
                        return $value;
                    }

                // no break
                case 'todo':
                    if (is_array(json_decode(html_entity_decode($value), true))) {
                        $values = json_decode(html_entity_decode($value), true);

                        return '<i>' . count($values) . ' ToDo items</i>';
                    } else {
                        return $value;
                    }

                // no break
                default:
                    if ($field['fields_type'] === 'DATERANGE') {
                        // Formato daterange
                        $dates = dateRange_to_dates($value);
                        switch (count($dates)) {
                            case 2:
                                return t('From ') . dateFormat($dates[0]) . t(' to ') . dateFormat($dates[1]);
                            case 0:
                                return '';
                            default:
                                return '<small>[Wrong daterange format]</small>';
                        }
                    } elseif ($field['fields_type'] === 'GEOGRAPHY') {
                        return $value['geo'] ? sprintf('<small>Lat:</small>%s, <small>Lon:</small>%s', $value['lat'], $value['lng']) : '';
                    } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        return mailto($value);
                    } elseif (filter_var($value, FILTER_VALIDATE_URL) || (is_string($value) && preg_match("/\A^www.( [^\s]* ).[a-zA-Z]$\z/ix", $value) && filter_var('http://' . $value, FILTER_VALIDATE_URL) !== false)) {
                        if ((stripos($value, 'http://') === false) && (stripos($value, 'https://') === false)) {
                            $value = 'http://' . $value;
                        }
                        return anchor($value, str_replace(array('http://', 'https://'), '', $value), array('target' => '_blank'));
                    } elseif ($field['fields_type'] === 'INT4RANGE' || $field['fields_type'] === 'INT8RANGE') {
                        return $value['from'] . ' - ' . $value['to'];
                    } else {
                        return $value;
                    }
            }
        }
    }

    private function buildPlaceholderGridCell($placeholderedString, &$record)
    {
        return $this->replace_superglobal_data(str_replace_placeholders($placeholderedString, $record));
    }

    private function buildEvalGridCell($evalString, &$data, $field)
    {
        extract($data);
        ob_start();
        eval ('?> ' . $evalString . '<?php ');
        return ob_get_clean();
    }

    /**
     * Build del form input
     */
    public function build_form_input(array $field, $value = null, $value_id = null)
    {


        if (!$value && $field['forms_fields_default_value'] <> '') {

            $value = $this->get_default_fields_value($field, $value_id);


        }

        $output = '';
        $isMultilingual = defined('LANG_ENTITY') && LANG_ENTITY && $field['fields_multilingual'] == DB_BOOL_TRUE;

        $languages = $isMultilingual ? $this->_languages : [null];

        /*
         * Mi assicuro che le seguenti chiavi esistano nel field perché posso
         * anche usare questo metodo quando non sono in ambiente form e i campi
         * non sono definiti. Se non ci sono li metto a null
         */
        if (!isset($field['forms_fields_override_label'])) {
            $keys = ['forms_fields_override_label', 'forms_fields_override_type', 'forms_fields_override_placeholder', 'forms_fields_show_required', 'forms_fields_show_label', 'forms_fields_subform_id'];
            $field = array_merge(array_fill_keys($keys, null), $field);
        }

        /*
         * Parametri di base dell'input
         * ---
         * Questi sono i parametri presi dal form_field (o, se non presenti, dal
         * field)
         * -- Messa una chiocciola
         */
        $baseLabel = $field['forms_fields_override_label'] ?: $field['fields_draw_label'];
        $baseType = $field['forms_fields_override_type'] ?: $field['fields_draw_html_type'];
        $basePlaceholder = $field['forms_fields_override_placeholder'] ?: $field['fields_draw_placeholder'];
        $baseHelpText = $field['fields_draw_help_text'] ? '<span class="help-block">' . t($field['fields_draw_help_text']) . '</span>' : '';
        $baseShow = $field['fields_draw_display_none'] == DB_BOOL_FALSE;
        //$baseShowRequired = $field['forms_fields_show_required'] ? $field['forms_fields_show_required'] == DB_BOOL_TRUE : ($field['fields_required'] != FIELD_NOT_REQUIRED && !trim($field['fields_default']));
        $baseShowRequired = $field['forms_fields_show_required'] ? $field['forms_fields_show_required'] == DB_BOOL_TRUE : ($field['fields_required'] != FIELD_NOT_REQUIRED);
        $baseShowLabel = $field['forms_fields_show_label'] ? $field['forms_fields_show_label'] == DB_BOOL_TRUE : true; // Se è vuoto mostro sempre la label di default, altrimenti valuto il campo
        $baseOnclick = $field['fields_draw_onclick'] ? sprintf('onclick="%s"', $field['fields_draw_onclick']) : '';

        $attr = (!empty($field['fields_draw_attr'])) ? $field['fields_draw_attr'] : '';

        $subform = $field['forms_fields_subform_id'] ?: null;

        $class = $field['fields_draw_css_extra'] . ' ' . ($field['forms_fields_extra_class'] ?? null) . ' field_' . $field['fields_id'];
        $name = $field['fields_name'];
        if ($isMultilingual) {
            $value = json_decode($value, true);
        }

        /*
         * Valori di default per monolingua
         * ---
         * Il caso monolingua è gestito come un multilingua con lingua === null
         */
        $langId = null;
        $langShow = $baseShow;
        $langValue = $value;
        $langAttribute = null;
        $langLabel = $baseLabel;

        foreach ($languages as $id => $lang) {
            /*
             * Faccio l'override delle variabili nel caso di multilingua e
             * quindi una lingua valida ($lang non vuoto) e sia attivo e
             * configurato nel crm il sistema multilingua. Se così non fosse,
             * avrei i valori già settati precedentemente
             */
            if ($lang && $isMultilingual) {
                // Override dei valori per multilingua
                $field['fields_name'] = $name . "[{$id}]";
                $langLabel = sprintf('<img src="%s" alt="%s" class="lang-flag" /> ', $lang['flag'], $lang['name']) . $baseLabel;

                $langId = $id;
                $langShow = $this->_currentLanguage == $id && $baseShow;

                $langValue = isset($value[$id]) ? $value[$id] : null;
                $langAttribute = "data-lang='{$id}'";
            }

            $style = $langShow ? '' : 'style="display:none"';
            // Verifica se il testo di aiuto è lungo più di 20 caratteri e prepara l'icona di aiuto
            if (strlen($baseHelpText) > 20) {
                // Se il testo di aiuto è più lungo di 20 caratteri, usa un'icona con tooltip
                $helpIcon = '<i class="far fa-question-circle" aria-hidden="true" data-toggle="tooltip" title="' . htmlspecialchars(strip_tags($baseHelpText)) . '"></i>';
            } else {
                // Se il testo di aiuto è breve o vuoto, non mostrare l'icona del tooltip
                $helpIcon = '';
            }

            // Costruisce il testo della label, includendo l'icona del tooltip direttamente all'interno del tag label se necessario
            $label = $baseShowLabel ? '<label class="control-label">' . t($langLabel) . ($baseShowRequired ? ' <small class="text-danger fas fa-asterisk firegui_fontsize85"></small>' : '') . ' ' . $helpIcon . '</label>' : '';

            // Prepara il resto dei dati come prima
            $data = [
                'lang' => $langId,
                'field' => $field,
                'value' => is_string($langValue) ? htmlspecialchars($langValue) : $langValue,
                'label' => $label,
                'placeholder' => $basePlaceholder,
                'help' => strlen($baseHelpText) > 20 ? '' : $baseHelpText, // Non includere il testo di aiuto qui se è lungo
                'class' => $class,
                'attr' => $attr,
                'onclick' => $baseOnclick,
                'subform' => $subform,
            ];


            // Aggiungo la preview del value, da usare nelle nuove select_ajax
            if (!empty($data['value']) && !is_array($data['value']) && !empty($field['fields_ref']) && $field['forms_fields_override_type'] != 'input_hidden') {
                $preview = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id = '{$data['value']}'", 1);
                $data['value_preview'] = array_pop($preview);
            }

            if ($baseType == 'multi_upload') {
            }

            if ($baseType == 'single_upload') {
                $baseType = 'upload';
            }

            $view = $this->load->view("box/form_fields/{$baseType}", $data, true);

            if ($baseType !== 'input_hidden') {
                $wrapAttributes = implode(' ', array_filter([$style, $langAttribute]));
                $view = sprintf('<div class="form-group" %s>%s</div>', $wrapAttributes, $view);
            }

            $output .= $view;
        }

        return $output;
    }

    /**
     * Retrieve the box contents from the layout box definition
     *
     * @param array $layoutBoxData      Layout box def
     * @param int|null $value_id        Value ID
     * @param array $layoutEntityData   Data related
     *
     * @return string
     */
    public function getBoxContent($layoutBoxData, $value_id = null, $layoutEntityData = [], $layout_container = null)
    {

        if (is_numeric($layoutBoxData)) {
            $layoutBoxData = $this->layout->getLayoutBox($layoutBoxData);
        }

        $contentType = $layoutBoxData['layouts_boxes_content_type'];
        $contentRef = $layoutBoxData['layouts_boxes_content_ref'];

        $parent_layout = $this->layout->getLayout($layoutBoxData['layouts_boxes_layout']);

        if ($value_id && $parent_layout['layouts_entity_id'] > 0 && $layoutEntityData == null) {
            $entity = $this->crmentity->getEntity($parent_layout['layouts_entity_id']);
            if (isset($entity['entity_name'])) {
                $this->layout->addRelatedEntity($entity['entity_name'], $value_id);
                //debug($entity);
                $data_entity = $this->getDataEntity($entity['entity_id'], ["{$entity['entity_name']}.{$entity['entity_name']}_id" => $value_id], 1);

                $layoutEntityData = array_shift($data_entity);
            }
        }

        switch ($contentType) {
            case "layout":

                if (!$this->datab->can_access_layout($contentRef, $value_id)) {
                    //Layout permission unallowed
                    return false;
                } else {
                    $subLayout = $this->build_layout($contentRef, $value_id, $layoutEntityData);
                    $subLayout['current_page'] = sprintf("layout_%s", $layoutBoxData['layouts_boxes_layout']);
                    $subLayout['show_title'] = false;

                    if ($subLayout['layout'] != []) {
                        return $this->load->view("pages/layout", array('dati' => $subLayout, 'value_id' => $value_id), true);
                    } else {
                        //Layout content is empty
                        return false;
                    }
                }
            // no break
            case 'tabs':
                $tabs = [];
                $tabId = 'tabs_' . $layoutBoxData['layouts_boxes_id'];

                $subboxes = (isset($layoutBoxData['subboxes']) && is_array($layoutBoxData['subboxes'])) ? $layoutBoxData['subboxes'] : [];
                foreach ($subboxes as $key => $subbox) {
                    // Nelle tab non venivano scatenati i pre-grid, post-grid, ecc.... ora si!

                    $content = $this->getBoxContent($subbox, $value_id, $layoutEntityData);
                    if ($content) {
                        if (is_numeric($subbox)) {
                            $subbox = $this->layout->getLayoutBox($subbox);
                        }
                        $hookSuffix = $subbox['layouts_boxes_content_type'];
                        $hookRef = $subbox['layouts_boxes_content_ref'];

                        if ($hookSuffix && is_numeric($hookRef) && $hookSuffix !== 'layout') {
                            $content = $this->getHookContent('pre-' . $hookSuffix, $hookRef, $value_id) .
                                $content .
                                $this->getHookContent('post-' . $hookSuffix, $hookRef, $value_id);
                        }
                        //debug($content);
                        $tabs[$key] = [
                            'title' => $subbox['layouts_boxes_title'],
                            'content' => $content,
                        ];
                    }
                }
                return $this->load->view("pages/layouts/tabbed/{$contentType}", array('tabs' => $tabs, 'tabs_id' => $tabId, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            case "chart":
                $this->load->model('charts');
                $chart = $this->db->get_where('charts', ['charts_id' => $contentRef])->row_array();

                $chart['elements'] = $this->charts->get_charts_elements($chart['charts_id']);
                $chart_data = $this->charts->get_chart_data($chart, $value_id);
                $processed_chart_data = $this->charts->process_data($chart, $chart_data);

                $chart_layout = $chart['charts_layout'] ?: DEFAULT_LAYOUT_CHART;
                return $this->load->view("pages/layouts/charts/{$chart_layout}", array(
                    'chart' => $chart,
                    'chart_data' => $chart_data,
                    'value_id' => $value_id,
                    'layout_data_detail' => $layoutEntityData,
                    'processed_data' => $processed_chart_data,
                ), true);

            case "grid":
                // Prendo la struttura della grid
                $grid = $this->get_grid($contentRef);

                $this->layout->addRelatedEntity($grid['grids']['entity_name']);

                // Ci sono problemi se inizializzo una datatable senza colonne!!
                if (empty($grid['grids_fields'])) {
                    return sprintf(t('*** Grid `%s` without fields ***'), $contentRef);
                }

                // Controllo i permessi per questa grid
                if (!$this->can_read_entity($grid['grids']['grids_entity_id'])) {
                    return t('You are not allowed to do read data from this entity.');
                }

                // Prendo i dati della grid: è inutile prendere i dati in una grid ajax
                $grid_data = ['data' => [], 'sub_grid_data' => []];

                if ($grid['grids']['grids_ajax'] == DB_BOOL_FALSE && !in_array($grid['grids']['grids_layout'], ['datatable_ajax', 'datatable_ajax_inline', 'datatable_ajax_slim'])) {
                    $grid_data['data'] = $this->get_grid_data($grid, empty($layoutEntityData) ? $value_id : ['value_id' => $value_id, 'additional_data' => $layoutEntityData], [], null, 0, null, false, ['depth' => $grid['grids']['grids_depth']]);
                }

                /*                 * *********************************************************
                 * If there's a subentity, load subentity data also
                 */
                $sub_grid = null;
                if ($grid['grids']['grids_sub_grid_id']) {
                    $sub_grid = $this->get_grid($grid['grids']['grids_sub_grid_id']);
                    $relation_field = $this->db->select('fields_name')->from('fields')->where('fields_entity_id', $sub_grid['grids']['grids_entity_id'])->where('fields_ref', $grid['grids']['entity_name'])->get();
                    if ($relation_field->num_rows() < 1) {
                        debug("L'entità {$grid['grids']['entity_name']} non è referenziata dall'entità {$sub_grid['grids']['entity_name']}");
                    } else {
                        $sub_grid['grid_relation_field'] = $relation_field->row()->fields_name;
                        if ($grid_data['data']) {
                            $entName = $grid['grids']['entity_name'];
                            $arr_parent_ids = array_map(function ($parentRecord) use ($entName) {
                                return $parentRecord["{$entName}_id"];
                            }, $grid_data['data']);
                            $parent_ids = implode("','", $arr_parent_ids);
                            $where = "{$sub_grid['grid_relation_field']} IN ('{$parent_ids}')";
                            $grid_data['sub_grid_data'] = $this->get_grid_data($sub_grid, null, $where);
                        }
                    }
                }

                $grid_layout = $grid['grids']['grids_layout'] ?: DEFAULT_LAYOUT_GRID;

                return $this->load->view("pages/layouts/grids/{$grid_layout}", array(
                    'grid' => $grid,
                    'sub_grid' => $sub_grid,
                    'grid_data' => $grid_data,
                    'value_id' => $value_id,
                    'layout_data_detail' => $layoutEntityData,
                    'layout' => $layout_container,
                    'where' => false,
                ), true);

            case "form":
                $form_id = $contentRef;
                $form = $this->get_form($form_id, $value_id);
                if ($form) {
                    // Check permissions for this form
                    if (!in_array($form['forms']['forms_layout'], ['filter_select']) && !$this->can_write_entity($form['forms']['forms_entity_id'])) {
                        return str_repeat('&nbsp;', 3) . t('You are not allowed to do this.');
                    }
                    return $this->load->view("pages/layouts/forms/form_{$form['forms']['forms_layout']}", array(
                        'form' => $form,
                        'ref_id' => $contentRef,
                        'value_id' => $value_id,
                        'layout_data_detail' => $layoutEntityData,
                    ), true);
                } else {
                    return $this->load->view("box/errors/missing_form", ['form_id' => $form_id], true);
                }
            // no break
            case "calendar":
                $data = $this->get_calendar($contentRef);
                $data['cal_layout'] = $this->db->get_where('calendars', ['calendars_id' => $contentRef])->row_array();

                // Rimpiazzo i placeholders sul campo "link / extra parameters"
                if ($layoutEntityData !== null && is_array($layoutEntityData) && !empty($data['cal_layout']['calendars_link'])) {
                    $layoutEntityData['value_id'] = $value_id;
                    $replace_data = array();
                    foreach ($layoutEntityData as $key => $value) {
                        if (!is_numeric($key) && !is_array($value)) {
                            $replace_data['{' . $key . '}'] = $value;
                        }
                    }

                    $data['cal_layout']['calendars_link'] = str_replace(array_keys($replace_data), array_values($replace_data), $data['cal_layout']['calendars_link']);
                    $data['cal_layout']['calendars_link'] = $this->replace_superglobal_data($data['cal_layout']['calendars_link']);
                }

                $cal_layout = $data['cal_layout']['calendars_layout'] ?: DEFAULT_LAYOUT_CALENDAR;
                return $this->load->view("pages/layouts/calendars/{$cal_layout}", array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            case "map":
                $data = $this->get_map($contentRef);
                $data['map_layout'] = $this->db->get_where('maps', ['maps_id' => $contentRef])->row_array();
                $map_layout = ($data['map_layout']['maps_layout']) ? $data['map_layout']['maps_layout'] : DEFAULT_LAYOUT_MAP;
                return $this->load->view('pages/layouts/maps/' . $map_layout, array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);
            case "menu_group":
            case "menu_button_stripe":
            case "menu_big_button":
                $data = $this->get_menu($contentRef);
                return $this->load->view("pages/layouts/menu/{$contentType}", array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);
            case "view":
                //TODO: verificare prima se esiste un custom per questo modulo nelle view native custom
                $module_view = $this->getModuleViewData($contentRef);
                // debug($module_view);
                // debug($contentRef, true);

                if (!empty($module_view) && (file_exists(FCPATH . "application/views/custom/{$module_view['module_name']}/{$module_view['module_view']}") || file_exists(FCPATH . "application/views/custom/{$module_view['module_name']}/{$module_view['module_view']}.php"))) {

                    $html = $this->load->view("custom/{$module_view['module_name']}/{$module_view['module_view']}", ['value_id' => $value_id, 'layout_data_detail' => $layoutEntityData], true);
                    //die($html);
                    return $html;

                } else {
                    //Verifico se questa custom view fa parte di un modulo. In tal caso, carico la view direttamente dal modulo
                    if ($module_view) {
                        return $this->load->module_view($module_view['module_name'] . '/views', $module_view['module_view'], ['value_id' => $value_id, 'layout_data_detail' => $layoutEntityData], true);
                    } else {
                        return $this->loadCustomView($contentRef, ['value_id' => $value_id, 'layout_data_detail' => $layoutEntityData], true);
                    }

                }

            // no break
            default:
                if (empty($contentType) && $layoutBoxData['layouts_boxes_content']) {
                    // Contenuto definito
                    ob_start();
                    $layout_data_detail = $layoutEntityData; // Ci assicuriamo che questa variabile esista dentro all'eval
                    eval (' ?>' . $layoutBoxData['layouts_boxes_content'] . '<?php ');
                    $return = ob_get_clean();

                    $return = str_replace_placeholders($return, (array) $layoutEntityData, true, false);

                    return $return;
                }
                return sprintf('<strong style="color:red">TYPE: %s No content</strong>', $contentType);
        }
    }

    //Funzione che verifica se la view custom fa parte di un nmodulo o meno...
    protected function getModuleViewData($contentRef)
    {
        if (stripos($contentRef, '{module ') !== false) {
            $split = explode('/', $contentRef);
            $module_name = str_ireplace('{module ', '', $split[0]);
            $module_name = rtrim($module_name, '}');
            unset($split[0]);
            $view = implode('/', $split);
            return [
                'module_name' => $module_name,
                'module_view' => $view,
            ];
        } else {
            return false;
        }
    }
    /* =========================
     * Multilingua
     * ========================= */

    protected function preloadLanguages()
    {
        if (!defined('LANG_ENTITY') or !LANG_ENTITY) {
            return;
        }

        $this->load->helper('text');
        $this->_currentLanguage = $this->session->userdata(self::LANG_SESSION_KEY);
        $this->_languages = [];
        $this->_default_language_id = $this->db->get_where('settings')->row()->settings_default_language;
        $this->_default_language = $this->db->get_where('languages', ['languages_id' => $this->_default_language_id])->row_array();

        $languages = $this->db->get(LANG_ENTITY)->result_array();
        foreach ($languages as $language) {
            $nlang = $this->normalizeLanguageArray($language);
            $this->_languages[$nlang['id']] = $nlang;

            if (strtolower($language['languages_name']) == strtolower($this->_default_language['languages_name']) or is_null($this->_defaultLanguage)) {
                $this->_defaultLanguage = $nlang['id'];
            }
        }

        if (!$this->_currentLanguage or empty($this->_languages[$this->_currentLanguage])) {
            $this->_currentLanguage = $this->_defaultLanguage;
        }

        // Forza impostazione della lingua
        $this->changeLanguage($this->_currentLanguage);
    }

    protected function normalizeLanguageArray(array $language)
    {
        $code = $language[LANG_CODE_FIELD];
        if (preg_match('/^[a-z]{2}(-|_)[a-z]{2}$/i', $code)) {
            $img = $code[3] . $code[4];
        } elseif (strlen($code) > 1) {
            $img = substr($code, 0, 2);
        } else {
            $img = null;
        }

        if ($img) {
            $flag = base_url_template('template/common/img/flags/' . strtolower($img) . '.png');
        }

        return [
            'id' => $language[LANG_ENTITY . '_id'],
            'name' => $language[LANG_NAME_FIELD],
            'file' => convert_accented_characters(strtolower(str_replace(' ', '_', $language[LANG_NAME_FIELD]))),
            'code' => $code,
            'flag' => $flag,
        ];
    }

    /**
     * Prendi la lingua selezionata
     *
     * @return array|null
     */
    public function getLanguage()
    {
        return $this->findLanguage($this->_currentLanguage);
    }

    /**
     * Prendi la lingua di default
     *
     * @return array|null
     */
    public function getDefaultLanguage()
    {
        foreach ($this->_languages as $lang) {
            if (strtolower($lang['name']) == strtolower($this->_default_language['languages_name'])) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Cerca un array di lingua per id o per codice
     *
     * @param int|string $key
     * @return array|null
     */
    public function findLanguage($key)
    {
        if (is_numeric($key)) {
            return isset($this->_languages[$key]) ? $this->_languages[$key] : null;
        }

        $iCode = strtolower(str_replace(['-', '_', ' '], '', $key));
        $out = array_filter($this->_languages, function ($lang) use ($iCode) {
            return strtolower(str_replace(['-', '_', ' '], '', $lang['code'])) === $iCode;
        });

        // Ritorna il primo o null
        return array_shift($out);
    }

    public function getAllLanguages()
    {
        return $this->_languages;
    }

    public function changeLanguage($key)
    {
        if (!is_numeric($key)) {
            $compatible = array_filter($this->_languages, function ($lang) use ($key) {
                return $lang['code'] == $key;
            });

            if (!($lang = array_shift($compatible))) {
                return false;
            }

            $key = $lang['id'];
        }

        // Language ID
        if (empty($this->_languages[$key])) {
            return false;
        }

        $this->_currentLanguage = $key;
        $this->session->set_userdata(self::LANG_SESSION_KEY, $this->_currentLanguage);
        $this->crmentity->setLanguages([$this->_currentLanguage, $this->_defaultLanguage]);
        $this->loadCiTranslations($this->_languages[$key]['file']);
        return true;
    }

    private function loadCiTranslations($language)
    {
        $langpath = APPPATH . "language/{$language}";
        $langfile = "{$langpath}/{$language}_lang.php";

        if (!is_dir($langpath)) {
            mkdir($langpath, 0755);

            $this->load->helper('file');

            $defpath = APPPATH . "language/_default_";
            foreach (get_filenames($defpath) as $file) {
                $deffile = "{$defpath}/{$file}";
                $newfile = "{$langpath}/{$file}";
                copy($deffile, $newfile);
            }
        }

        if (!file_exists($langfile)) {
            file_put_contents($langfile, "<?php \n\n");
        }


        //dd($this->lang);
        // Rimuovo le traduzioni caricate in modo da poter ricaricare tutto
        $this->lang->language = [];
        $this->lang->is_loaded = [];

        $this->load->language($language, $language);
    }

    /*
    Functions to prepare data for grid export
    */
    public function prepareData($grid_id = null, $value_id = null, $params = [])
    {
        //prendo tutti i dati della grid (filtri compresi) e li metto in un array associativo, pronto per essere esportato
        $grid = $this->datab->get_grid($grid_id);

        $preview_fields = $this->db->join('entity', 'fields_entity_id = entity_id')->get_where(
            'fields',
            array('fields_entity_id' => $grid['grids']['grids_entity_id'], 'fields_preview' => DB_BOOL_TRUE)
        )
            ->result_array();
        if (!empty($params['search'])) {
            $where = $this->search_like($params['search'], array_merge($grid['grids_fields'], $preview_fields));
        } else {
            $where = null;
        }
        if (!empty($params['order_by'])) {
            $order_by = $params['order_by'];
        } else {
            $order_by = null;
        }


        $grid_data = $this->datab->get_grid_data($grid, $value_id, $where, null, 0, $order_by);
        $out_array = [];
        foreach ($grid_data as $dato) {
            $dato['value_id'] = $value_id;
            
            $tr = [];

            foreach ($grid['grids_fields'] as $field) {
                $tr[] = trim(strip_tags($this->build_grid_cell($field, $dato, false, true, true)));

            }

            $out_array[] = $tr;
        }

        $columns_names = [];

        //Rimpiazzo i nomi delle colonne
        foreach ($grid['grids_fields'] as $key => $field) {
            $columns_names[$key . $field['fields_name']] = $field['grids_fields_column_name'];
        }

        array_walk($out_array, function ($value, $key) use ($columns_names, &$out_array) {
            $out_array[$key] = array_combine($columns_names, $value);
        });

        return $out_array;
    }
    public function arrayToCsv(array $data, $delim = ',', $enclosure = '"')
    {
        if (!$data) {
            return '';
        }

        // Apri un nuovo file, mi serve per avere un handler per usare
        // nativamente fputcsv che fa gli escape corretti
        $tmp = tmpfile() or show_error('Impossibile creare file temporaneo');

        $keys = array_keys(array_values($data)[0]);
        fputcsv($tmp, $keys, $delim, $enclosure);


        foreach ($data as $row) {
            if (fputcsv($tmp, $row, $delim, $enclosure) === false) {
                show_error('Impossibile scrivere sul file temporaneo');
            }
        }

        // Chiudendo il file qua, lo eliminerei completamente, quindi lo leggo
        // per intero e lo muovo in filedata. fseek mi serve perché in questo
        // momento il puntatore si trova alla fine del file e devo resettarlo
        $filedata = '';
        fseek($tmp, 0);

        do {
            $buffer = fread($tmp, 8192);
            if ($buffer === false) {
                show_error('Non è stato possibile leggere');
            }
            $filedata .= $buffer;
        } while (strlen($buffer) > 0);

        fclose($tmp); // Rilascia risorsa
        return $filedata;
    }

    public function getLayoutBoxesBenchmark()
    {
        return $this->_layout_boxes_benchmark;
    }
    /**
     * @param $element
     * @param $id
     * @param $return_data
     */
    public function clone_element($element, $id, $return_id = true)
    {
        if (!$this->auth->is_admin()) {
            return t('Unauthorized');
        }

        switch ($element) {
            case 'grid':
                return $this->clone_grid($id, $return_id);
            case 'layout':
            case 'layout_box':
            case 'form':
            // @todo 20231206 - michael - clonare gli altri elementi in base al bisogno.
            default:
                return t('Element unknown or unmanaged');
        }
    }

    private function clone_grid($id, $return_id = false)
    {
        if (!is_numeric($id)) {
            $this->db->where('grids_identifier', $id);
        } else {
            $this->db->where('grids_id', $id);
        }

        $grid = $this->db->get('grids')->row_array();

        if (empty($grid)) {
            return t('Grid not found');
        }

        $fields = $this->db->get_where('grids_fields', array('grids_fields_grids_id' => $grid['grids_id']))->result_array();
        $actions = $this->db->get_where('grids_actions', array('grids_actions_grids_id' => $grid['grids_id']))->result_array();

        // Inserisci una nuova grid - togli l'id
        unset($grid['grids_id']);

        $grid['grids_name'] .= " - duplicated";
        $grid['grids_default'] = DB_BOOL_FALSE;

        unset($grid['grids_module_key']);

        $this->db->insert('grids', $grid);
        $new_id = $this->db->insert_id();

        if (!empty($grid['grids_module_key'])) {
            $old_key_expl = explode('-', $grid['grids_module_key']);
            $this->db->where('grids_id', $new_id)->update('grids', ['grids_module_key' => "{$old_key_expl[0]}-grid-{$new_id}"]);
        }

        // Aggiungi tutti i fields
        foreach ($fields as $field) {
            $field['grids_fields_grids_id'] = $new_id;

            unset($field['grids_fields_id']);

            $this->db->insert('grids_fields', $field);
        }

        // Aggiungi tutte le custom actions
        foreach ($actions as $action) {
            unset($action['grids_actions_id']);

            $action['grids_actions_grids_id'] = $new_id;

            $this->db->insert('grids_actions', $action);
        }

        if ($return_id) {
            return $new_id;
        } else {
            return $this->get_grid($new_id);
        }
    }

    /**
     * @param $type
     * @param $id
     * @description Function to lock an element and avoid accidental remove or overwrite (often when updating modules...)
     * @return string|true
     */
    public function lock_element($type, $id)
    {
        if (!$this->auth->is_admin()) {
            return t('Unauthorized');
        }

        try {
            //Controllo se presente, se si unlocko, se no locko
            $exists = $this->db->get_where('locked_elements', ['locked_elements_type' => $type, 'locked_elements_ref_id' => $id]);
            if ($exists->num_rows() >= 1) {
                $this->db->where(['locked_elements_type' => $type, 'locked_elements_ref_id' => $id])->delete('locked_elements');
            } else {
                $this->db->insert('locked_elements', ['locked_elements_type' => $type, 'locked_elements_ref_id' => $id]);
            }

            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
