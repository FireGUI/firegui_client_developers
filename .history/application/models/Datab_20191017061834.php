<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * @property-read Crmentity $crmentity
 */
class Datab extends CI_Model
{

    const LANG_SESSION_KEY = 'master_crm_language';
    const CACHE_TIME = 3600;
    private $_accessibleLayouts = [];
    private $_forwardedLayouts = [];
    private $_accessibleEntityLayouts = [];
    private $_hooks = null;

    /* Multilingual system */
    private $_currentLanguage;
    private $_defaultLanguage;
    private $_languages = [];

    function __construct()
    {
        parent::__construct();
        $this->load->model('crmentity');
        $this->preloadLanguages();
        $this->prefetchMyAccessibleLayouts();
    }

    protected function prefetchMyAccessibleLayouts()
    {
        $userId = (int) $this->auth->get('id');

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
        $this->_accessibleLayouts = array_combine(array_key_map($accessibleLayouts, 'layouts_id'), $accessibleLayouts);

        foreach ($this->_accessibleLayouts as $id => $linfo) {
            if ($linfo['layouts_is_entity_detail'] === DB_BOOL_TRUE && !isset($this->_accessibleEntityLayouts[$linfo['layouts_entity_id']])) {
                $this->_accessibleEntityLayouts[$linfo['layouts_entity_id']] = $id;
            }
        }


        if ($this->_accessibleLayouts) {
            $allEntitiesDetails = $this->db->join('entity', 'layouts_entity_id = entity_id')
                ->where_not_in('layouts_id', array_keys($this->_accessibleLayouts))
                ->get_where('layouts', ['layouts_is_entity_detail' => DB_BOOL_TRUE])
                ->result_array();

            foreach ($allEntitiesDetails as $layout) {
                if (isset($this->_accessibleEntityLayouts[$layout['layouts_entity_id']])) {
                    $this->_forwardedLayouts[$layout['layouts_id']] = $this->_accessibleEntityLayouts[$layout['layouts_entity_id']];
                }
            }
        }
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
    public function getDataEntity($entity_id, $where = NULL, $limit = NULL, $offset = 0, $order_by = NULL, $depth = 2, $count = FALSE, $eval_cachable_fields = [])
    {

        // Questo è un wrapper di apilib che va a calcolare i permessi per ogni
        // entità
        $visibleFields = $this->crmentity->getFields($entity_id);

        // Estraggo i campi visibili anche di eventuali tabelle da joinare per
        // calcolarne i permessi
        $permissionEntities = [$entity_id];   // Lista delle entità su cui devo applicare i limiti dei permessi

        foreach ($visibleFields as $k => $campo) {
            if ($campo['fields_ref']) {
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
            return $this->apilib->count($entity['entity_name'], $where);
        } else {

            return $this->apilib->search($entity['entity_name'], $where, $limit, $offset, $order_by, null, $depth, $eval_cachable_fields);
        }
    }

    public function get_visible_fields($entity_id = NULL)
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

    public function get_field($field_id)
    {
        if (is_numeric($field_id)) {
            return $this->db->query("SELECT * FROM fields LEFT JOIN entity ON (fields_entity_id = entity_id) WHERE fields_id = '{$field_id}'")->row_array();
        } else {
            return $this->get_field_by_name($field_id);
        }
    }

    public function get_field_by_name($field_name)
    {
        $slashed = addslashes($field_name);
        return $this->db->query("SELECT * FROM fields WHERE fields_name = '{$slashed}'")->row_array();
    }

    /**
     * Forms
     */
    public function get_default_fields_value($fields)
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
                $var1 = (isset($exp[1])) ? $exp[1] : null;
                $var2 = (isset($exp[2])) ? $exp[2] : null;

                switch ($func) {
                    case '{now_date}':
                        if (!empty($var1)) {
                            $value = date($var1);
                        } else {
                            $value = date("d/m/Y");
                        }

                        break;
                    case '{different_date}':
                        $timestamp = strtotime($var1 . ((trim($var1) === '+1') ? " day" : " days"));
                        $value = date('d/m/Y', $timestamp);
                        break;
                    case '{different_date_time}':
                        // Se l'argomento è della forma +10 allora appendi days alla fine
                        if (preg_match('/\A\+[0-9]+\z/', $var1)) {
                            $var1 .= " days";
                        }
                        $value = date("d/m/Y H:i", strtotime($var1));
                        break;
                    case '{now_date_time}':
                        $value = date("d/m/Y H:i");
                        break;

                    default:

                        debug("NON GESTITA DEFAULT TYPE FUNCTION");
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
                } else {
                    debug("NON GESTITA DEFAULT TYPE VARIABLE");
                }

                break;
        }

        return $value;
    }

    public function get_form($form_id, $value_id = null)
    {

        $form_id or die('ERRORE: Form ID mancante');
        $form = $this->db->join('entity', 'forms_entity_id = entity_id')->get_where('forms', ['forms_id' => $form_id])->row_array();
        if (!$form) {
            die(sprintf('Form %s non esistente', $form));
        }

        $fields = $this->db
            ->join('fields', 'fields_id = forms_fields_fields_id')
            ->join('fields_draw', 'forms_fields_fields_id = fields_draw_fields_id')
            ->order_by('forms_fields_order')
            ->get_where('forms_fields', ['forms_fields_forms_id' => $form_id, 'fields_visible' => DB_BOOL_TRUE])->result_array();
        if (is_array($value_id)) {
            $form['action_url'] = base_url("db_ajax/save_form/{$form_id}/true");
        } else {
            $form['action_url'] = base_url("db_ajax/save_form/{$form_id}" . ($value_id ? "/true/{$value_id}" : ''));
        }

        /*
         * Per far funzionare correttamente i form non posso recuperare i valori
         * già tradotti, quindi devo resettare il sistema lingue dell'apilib,
         * fare la chiamata e poi ripristinarlo
         */
        $clanguage = $this->apilib->getLanguage();          // Current Language
        $flanguage = $this->apilib->getFallbackLanguage();  // Fallback Language

        $this->apilib->setLanguage();
        if ($form['forms_one_record'] == DB_BOOL_TRUE) {
            $formData = $this->apilib->searchFirst($form['entity_name']);
        } else {
            $formData = ($value_id && !is_array($value_id)) ? $this->apilib->view($form['entity_name'], $value_id, 1) : [];
        }
        $this->apilib->setLanguage($clanguage, $flanguage);

        $operators = unserialize(OPERATORS);
        foreach ($fields as &$field) {

            // Il ref è il nome della tabella/entità di supporto/da joinare
            // quindi estraggo i valori da proporre
            if (!$field['fields_ref']) {
                continue;
            }

            if (!($entity = $this->get_entity_by_name($field['fields_ref']))) {
                echo "Campo legato ad una relazione inesistente (" . $field['fields_ref'] . ") ";
                continue;
            }

            // Verifico se il ref si riferisce ad una eventuale relations oppure ad una tabella di supporto, in modo da gestirlo diversamente
            // Chiaramente x funzionare non ci devono essere 2 relazioni con lo stesso nome
            //$relations = $this->db->query("SELECT * FROM relations WHERE relations_name = ?", [$entity['entity_name']])->row_array();
            $relations = $this->crmentity->getRelationByName($entity['entity_name']);

            if (count($relations) > 0) {

                // Se ho relazione A_B e il form inserisce A, allora voglio prendere la tabella B...
                $nField = ($relations['relations_table_2'] == $form['entity_name']) ? 1 : 2;

                $entity = $this->get_entity_by_name($relations["relations_table_{$nField}"]);
                $support_relation_table = $relations["relations_table_{$nField}"];
                $field['field_support_id'] = $relations["relations_field_{$nField}"];   // Dichiara il campo id da utilizzare nel form
            } else {
                $support_relation_table = $field['fields_ref'];
                $field['field_support_id'] = $entity['entity_name'] . "_id";    // Dichiara il campo id da utilizzare nel form
            }

            // A questo punto se il campo è ajax non pesco i dati, ma demando
            // l'onere alla chiamata ajax
            if ($field['fields_draw_html_type'] == 'select_ajax' or $field['fields_source']) {
                continue;
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
                $wheres[] = $this->replace_superglobal_data($fieldWhere);
            }

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
            // TODO Calcoare l'order by

            $order_by = NULL;

            $field['support_data'] = $this->crmentity->getEntityPreview($support_relation_table, $where, $order_by);

            //debug($field['support_data'] , true);
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


        /* $hidden = array_values(array_filter($fields, function($field) {
          $type = !empty($field['forms_fields_override_type']) ? $field['forms_fields_override_type']: $field['fields_draw_html_type'];
          return $type === 'input_hidden';
          })); */

        foreach ($hidden as $k => $field) {



            $hidden[$k] = $this->build_form_input($field, isset($formData[$field['fields_name']]) ? $formData[$field['fields_name']] : null);
        }
        /* $shown = array_values(array_filter($fields, function($field) {
          $type = !empty($field['forms_fields_override_type']) ? $field['forms_fields_override_type']: $field['fields_draw_html_type'];
          return $type !== 'input_hidden';
          })); */

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
            //debug($formData);
            $shown[$k] = [
                'id' => $field['fields_id'],
                'name' => $field['fields_name'],
                'label' => $field['forms_fields_override_label'] ?: $field['fields_draw_label'],
                'size' => $colsize,
                'min' => $field['forms_fields_min'],
                'max' => $field['forms_fields_max'],
                'type' => $type,
                'datatype' => $field['fields_type'],
                'filterref' => empty($field['support_fields'][0]['entity_name']) ? $field['fields_ref'] : $field['support_fields'][0]['entity_name'], // Computo il ref field da usare nel caso di form
                'html' => $this->build_form_input($field, isset($formData[$field['fields_name']]) ? $formData[$field['fields_name']] : null)
            ];
        }


        return ['forms' => $form, 'forms_hidden' => $hidden, 'forms_fields' => $shown];
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
        if (!$grid_id)
            die('ERRORE: Entity ID mancante');

        $dati = array();
        $dati['grids'] = $this->db->query("SELECT * FROM grids WHERE entity_id = '$entity_id'")->result_array();
        return $dati;
    }

    public function get_grid_data($grid, $value_id = null, $where = array(), $limit = NULL, $offset = 0, $order_by = NULL, $count = FALSE)
    {
        //TODO: 20190513 - MP - Intervenire su questa funzione per estrarre eventuali eval cachable
        $eval_cachable_fields = array_filter($grid['grids_fields'], function ($field) {
            return ($field['grids_fields_replace_type'] == 'eval' && $field['grids_fields_eval_cache_type'] && $field['grids_fields_eval_cache_type'] != 'no_cache');
        });

        //debug($eval_cachable_fields,true);

        if (is_array($value_id)) {
            $additional_data = isset($value_id['additional_data']) ? $value_id['additional_data'] : array();
            $value_id = isset($value_id['value_id']) ? $value_id['value_id'] : null;
        } else {
            $additional_data = array();
        }

        /** Valuta order_by * */
        if (is_null($order_by) && !empty($grid['grids']['grids_order_by']) && !$count) {
            $order_by = $grid['grids']['grids_order_by'];
        }

        //20190327 Se è ancora null, vuol dire che non ho cliccato su nessuna colonna e che non c'è nemmeno un order by default. Di conseguenza ordino per id desc (che è la cosa più logica)
        if (is_null($order_by) && !$count) {

            $order_by = $grid['grids']['entity_name'] . '.' . $grid['grids']['entity_name'] . '_id DESC';
        }

        $has_bulk = !empty($grid['grids_bulk_mode']);
        $where = $this->generate_where("grids", $grid['grids']['grids_id'], $value_id, is_array($where) ? implode(' AND ', $where) : $where, $additional_data);

        //20170530 - Verifico che non sia impostato un campo order by di default nell'entità, qualora non specificato un order by specifico della grid

        if (empty($order_by)) {
            // Recupero i dati dell'entità
            try {
                $this->load->model('crmentity');
                $entity_data = $this->crmentity->getEntity($grid['grids']['grids_entity_id']);
            } catch (Exception $ex) {
                $this->error = self::ERR_VALIDATION_FAILED;
                $this->errorMessage = $ex->getMessage();
                return false;
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
        $clanguage = $this->apilib->getLanguage();          // Current Language
        $flanguage = $this->apilib->getFallbackLanguage();  // Fallback Language

        $this->apilib->setLanguage();

        $data = $this->getDataEntity($grid['grids']['grids_entity_id'], $where, $limit, $offset, $order_by, 2, $count, $eval_cachable_fields);

        // Riabilita sistema traduzioni
        $this->apilib->setLanguage($clanguage, $flanguage);

        return $data;
    }

    public function get_grid($grid_id)
    {
        if (!$grid_id) {
            die('ERRORE: grid ID mancante');
        }

        $dati['grids'] = $this->db->query("SELECT * FROM grids LEFT JOIN entity ON entity.entity_id = grids.grids_entity_id WHERE grids_id = ?", [$grid_id])->row_array();
        $dati['grids_fields'] = $this->db->query("
                    SELECT *
                    FROM grids_fields
                        LEFT JOIN grids ON grids.grids_id = grids_fields.grids_fields_grids_id
                        LEFT JOIN fields ON fields.fields_id = grids_fields.grids_fields_fields_id 
                        LEFT JOIN fields_draw ON grids_fields.grids_fields_fields_id = fields_draw.fields_draw_fields_id
                    WHERE grids_id = ? AND (fields_id IS NULL OR NOT fields_draw_display_none)
                    ORDER BY grids_fields_order ASC
                ", [$grid_id])->result_array();

        // Ciclo ed estraggo eventuali campi di tabelle joinate FUNZIONA SOLO
        // CON ENTITA PER ORA
        foreach ($dati['grids_fields'] as $key => $field) {

            // Preparo il nome colonna
            $colname = isset($field['grids_fields_column_name']) ? $field['grids_fields_column_name'] : $field['fields_draw_label'];
            $dati['grids_fields'][$key]['grids_fields_column_name'] = trim($colname) ?: $field['fields_draw_label'];

            if ($field['fields_ref']) {
                $dati['grids_fields'][$key]['support_fields'] = array_values(array_filter(
                    $this->crmentity->getFields($field['fields_ref']),
                    function ($field) {
                        return $field['fields_preview'] == DB_BOOL_TRUE;
                    }
                ));
            }
        }

        $dati['grids']['links'] = array(
            'view' => ($dati['grids']['grids_view_layout'] ? base_url("main/layout/{$dati['grids']['grids_view_layout']}") : str_replace('{base_url}', base_url(), $dati['grids']['grids_view_link'])),
            'edit' => ($dati['grids']['grids_edit_layout'] ? base_url("main/layout/{$dati['grids']['grids_edit_layout']}") : str_replace('{base_url}', base_url(), $dati['grids']['grids_edit_link'])),
            'delete' => ($dati['grids']['grids_delete_link'] ? str_replace('{base_url}', base_url(), $dati['grids']['grids_delete_link']) : base_url("db_ajax/generic_delete/{$dati['grids']['entity_name']}"))
        );

        if (!filter_var($dati['grids']['links']['delete'], FILTER_VALIDATE_URL)) {
            unset($dati['grids']['links']['delete']);
        }

        $can_write = $this->can_write_entity($dati['grids']['entity_id']);
        if (!$can_write) {
            unset($dati['grids']['links']['edit'], $dati['grids']['links']['delete']);
        }

        // Infine aggiungo le custom actions - attenzione! non posso valutare i permessi sulle custom actions
        $dati['grids']['links']['custom'] = $this->db->order_by('grids_actions_order', 'ASC')->get_where('grids_actions', array('grids_actions_grids_id' => $grid_id))->result_array();
        foreach ($dati['grids']['links']['custom'] as &$custom_link) {
            //20170915 - Matteo Puppis - Mantengo questa funzionalità solo se è impostato il custom html
            if (!empty($custom_link['grids_actions_html'])) {
                $html = str_replace('{base_url}', base_url(), $custom_link['grids_actions_html']);
                $custom_link['grids_actions_html'] = $html;
                $custom_link['grids_actions_name'] = addslashes($custom_link['grids_actions_name']);
            } else {
                //debug($custom_link, true);
            }
        }

        // Mi assicuro che ogni link esistente termini con '/' e valuto se è da aprire con modale
        foreach ($dati['grids']['links'] as $type => $link) {
            if ($link && is_string($link)) {
                $dati['grids']['links'][$type] = rtrim($link, '/') . '/';
                //$dati['grids']['links'][$type . '_modal'] = (strpos($link, base_url('get_ajax/layout_modal')) === 0);
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

        return $dati;
    }

    /**
     * CHARTS 
     */
    public function get_charts_elements($charts_id)
    {
        $elements = $this->db->where('charts_elements_charts_id', $charts_id)->get('charts_elements')->result_array();
        return $elements;
    }

    public function get_entity_fields($entity_id)
    {
        return $this->crmentity->getFields($entity_id);
    }

    public function get_chart_data($chart, $value_id = null)
    {

        $all_data = [];

        // Ciclo gli elementi qualora ne abbia + di uno
        foreach ($chart['elements'] as $element) {
            $data = [];

            if (empty($element['charts_elements_mode']) or $element['charts_elements_mode'] == 1) {
                $entity = $this->get_entity($element['charts_elements_entity_id']);
                $group_by = $element['charts_elements_groupby'];

                // Gli costruisco il Where con il mega-metodo generico
                //$where = trim($element['charts_elements_where']);
                $where = $this->generate_where("charts_elements", $element['charts_elements_id'], $value_id);

                if ($where) {
                    $where = "WHERE {$where}";
                }

                // Mi costruisco eventuali join
                $join = "";
                $alreadyJoined = array($entity['entity_name']);
                foreach ($this->get_entity_fields($element['charts_elements_entity_id']) as $_field) {
                    if ($_field['fields_ref'] && !in_array($_field['fields_ref'], $alreadyJoined)) {
                        $entity_ref = $this->get_entity_by_name($_field['fields_ref']);
                        $join .= "LEFT JOIN {$_field['fields_ref']} ON ({$_field['fields_ref']}.{$_field['fields_ref']}_id = {$entity['entity_name']}.{$_field['fields_name']}) ";
                        $alreadyJoined[] = $_field['fields_ref'];
                    }
                }


                $field = $this->get_field($element['charts_elements_fields_id']);
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

                switch ($element['charts_elements_function']) {
                    case 'COUNT':
                        $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}(*) AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $gr_by $order")->result_array();
                        break;
                    case null:
                        $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}{$field_function_parameter} AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $order")->result_array();
                        break;
                    default:
                        $data['data'] = $this->db->query("SELECT {$element['charts_elements_function']}({$field_function_parameter}) AS y, $query_group_by AS x FROM {$entity['entity_name']} $join $where $gr_by $order")->result_array();
                        break;
                }
            } else {
                $data['data'] = $this->db->query($this->replace_superglobal_data(str_replace('{value_id}', $value_id, $element['charts_elements_full_query'])))->result_array();
            }

            // Precalcolo tutte le x, perché ogni serie deve avere lo stesso numero di valori
            // e questo deve coincidere col numero di x
            $data['x'] = array_unique(array_map(function ($row) {
                return $row['x'];
            }, $data['data']));

            if (!empty($data['x']) && isset($group_by)) {
                // Trova chi è il campo messo come x (il campo x è l'ultimo dopo la virgola-cancelletto nella stringa group by)
                //$group_by ="asdds.test, asds.ket, agente, asd.tk"
                $arr_group_by = explode('#', $group_by);
                $x_field_name = trim(array_pop($arr_group_by));

                $field_exploso = explode('.', $x_field_name);
                $field_name_exploso = trim(array_pop($field_exploso));


                // Ha senso valutare sta cosa se è una stringa alfanumerica
                $xfield = null;
                if (preg_match("/^[a-z0-9_\-]+$/i", $field_name_exploso)) {
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
            //debug($data['data']);
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
                    //debug($row);
                    if ($element['charts_elements_label']) {
                        $name = $element['charts_elements_label'];
                    } else {
                        $name = implode(' ', $row);
                    }
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
    public function get_calendar($calendar_id)
    {
        if (!$calendar_id)
            die('ERRORE: calendar ID mancante');

        $dati['calendars'] = $this->db->query("SELECT * FROM calendars LEFT JOIN entity ON entity.entity_id = calendars.calendars_entity_id WHERE calendars_id = '$calendar_id'")->row_array();

        $dati['calendars_fields'] = $this->db->query("SELECT * FROM calendars_fields 
                                                      LEFT JOIN fields ON fields.fields_id = calendars_fields.calendars_fields_fields_id 
                                                      LEFT JOIN calendars ON calendars.calendars_id = calendars_fields.calendars_fields_calendars_id
                                                      WHERE calendars_id = '$calendar_id'")->result_array();

        $defaultForm = null;    // Faccio la query solamente se è realmente necessario
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
    public function get_entity_preview_by_name($entity_name, $where = NULL, $limit = NULL, $offset = 0)
    {

        return $this->crmentity->getEntityPreview($entity_name, $where, $limit, $offset);
    }

    public function get_support_data($fields_ref = NULL)
    {

        if (!$fields_ref) {
            return array();
        } else {
            $entity = $this->get_entity_by_name($fields_ref);

            // Verifico se il ref si riferisce ad una eventuale relations oppure ad una tabella di supporto, in modo da gestirlo diversamente
            //$relations = $this->db->get_where('relations', array('relations_name' => $entity['entity_name']))->row_array();
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
            return $this->db->query("SELECT {$select} FROM {$support_relation_table}")->result_array();
        }
    }

    /**
     * Costruisce il where di un oggetto GRID, MAPS, CHARTS o altro
     */
    public function generate_where($element_type, $element_id, $value_id = NULL, $other_where = null, $additional_data = array())
    {

        $arr = array();

        $element = $this->db->get_where($element_type, array($element_type . "_id" => $element_id))->row_array();
        $entity = $this->get_entity($element[$element_type . '_entity_id']);

        // Verifico se questo oggetto ha un where di suo
        if ($other_where) {
            $arr[] = "(" . (is_array($other_where) ? implode(' AND ', $other_where) : $other_where) . ")";
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
                $relationships = array_combine(array_map(function ($rel) {
                    return $rel['relations_name'];
                }, $__relationships), $__relationships);


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
                            if (isset($other_field_select->fields_name)) {
                                // Caso 1: è l'altra entità che ha il ref nell'entità in cui eseguo la ricerca
                                $where_prefix = "{$entity['entity_name']}_id IN (SELECT {$other_field_select->fields_name} FROM {$other_entity['entity_name']} WHERE ";
                            } else {
                                // Caso 2: è questa entità che sta ha il ref nell'altra entità
                                // devo trovare codesto field
                                $field_referencing = $this->db->get_where('fields', array('fields_entity_id' => $entity['entity_id'], 'fields_ref' => $other_entity['entity_name']))->row();
                                if (empty($field_referencing)) {
                                    // Non so come gestirlo, per ora piazzo un continue e tolgo debug
                                    //debug("Campo errato nella ricerca: {$field->fields_name}");
                                    continue;
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
                            $field->fields_draw_html_type = NULL;
                        } else {
                            // Sto filtrando in un campo dell'entità principale
                            //Matteo - 201703227 - Metto comunque il nome della tabella come prefisso per evitare il classico errore che il campo compare in più tabelle...
                            $where_prefix = "{$entity['entity_name']}.";
                            $where_suffix = '';
                        }




                        // Metto in pratica i filtri e li aggiungo all'array
                        // delle condizioni del where
                        if (in_array($field->fields_draw_html_type, array('date', 'date_time'))) {
                            $values = explode(' - ', $condition['value']);
                            if (count($values) === 2) {
                                $start = preg_replace('/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $values[0]);
                                $end = preg_replace('/([0-9]+)\/([0-9]+)\/([0-9]+)/', '$3-$2-$1', $values[1]);

                                if ($this->db->dbdriver != 'postgre') {
                                    //die('test');
                                    if ($is_another_entity) { //Sono costretto a ricontrollare se questo campo fa riferimento a un'altra tabella
                                        $other_entity = $this->get_entity($field->fields_entity_id);
                                        $other_field_select = $this->db->get_where('fields', array('fields_entity_id' => $field->fields_entity_id, 'fields_ref' => $entity['entity_name']))->row();
                                        if (isset($other_field_select->fields_name)) {
                                            // Caso 1: è l'altra entità che ha il ref nell'entità in cui eseguo la ricerca
                                            $arr[] = "{$entity['entity_name']}._id IN (SELECT {$other_field_select->fields_name} FROM {$other_entity['entity_name']} WHERE (CAST({$field->fields_name} AS DATE) BETWEEN '{$start}' AND '{$end}'))";
                                        } else {
                                            // Caso 2: è questa entità che sta ha il ref nell'altra entità
                                            // devo trovare codesto field
                                            $field_referencing = $this->db->get_where('fields', array('fields_entity_id' => $entity['entity_id'], 'fields_ref' => $other_entity['entity_name']))->row();
                                            if (empty($field_referencing)) {
                                                // Non so come gestirlo, per ora piazzo un continue e tolgo debug
                                                //debug("Campo errato nella ricerca: {$field->fields_name}");
                                                continue;
                                            }
                                            $arr[] = "{$entity['entity_name']}.{$field_referencing->fields_name} IN (SELECT {$other_entity['entity_name']}_id FROM {$other_entity['entity_name']} WHERE (CAST({$field->fields_name} AS DATE) BETWEEN '{$start}' AND '{$end}'))";
                                            //$where_prefix = "{$entity['entity_name']}.{$field_referencing->fields_name} IN (SELECT {$other_entity['entity_name']}_id FROM {$other_entity['entity_name']} WHERE ";
                                        }
                                    } else {
                                        $arr[] = "(CAST({$where_prefix}{$field->fields_name} AS DATE) BETWEEN '{$start}' AND '{$end}'{$where_suffix})";
                                    }

                                    //debug($where_prefix,true);
                                } else {
                                    $arr[] = "({$where_prefix}{$field->fields_name}::DATE >= '{$start}'::DATE AND {$field->fields_name}::DATE <= '{$end}'::DATE{$where_suffix})";
                                }
                            }
                        } else {
                            $condition['value'] = str_replace("'", "''", $condition['value']);

                            //debug($condition,true);

                            switch ($condition['operator']) {
                                case 'in':
                                    $values = "'" . implode("','", is_array($condition['value']) ? $condition['value'] : explode(',', $condition['value'])) . "'";
                                    $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} ({$values}){$where_suffix})";
                                    break;

                                case 'like':
                                    if (in_array($field->fields_type, array('VARCHAR', 'TEXT'))) {
                                        $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} '%{$condition['value']}%'{$where_suffix})";
                                    }
                                    break;
                                case 'rangein':

                                    $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} int4range({$condition['value']}))";
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

                                    $arr[] = "({$where_prefix}{$field->fields_name} {$operators[$condition['operator']]['sql']} '{$condition['value']}'{$where_suffix})";
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
    public function get_grid_where($grid_id, $value_id = NULL)
    {

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

    public function replace_superglobal_data($string)
    {

        // Fix per mantenere vecchia compatibilità con replace di sessioni login
        if (strpos($string, 'master_crm_login') !== false) {
            $string = str_replace('master_crm_login', SESS_LOGIN, $string);
        }

        $replaces = array_merge(
            ['post' => $this->input->post(), 'get' => $this->input->get()],
            $this->session->all_userdata()
        );

        return str_replace_placeholders($string, $replaces, true, true);
    }

    public function fields_implode($fields)
    {
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
    public function get_notifications($limit = null, $offset = 0)
    {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");

        if (is_numeric($limit) && $limit > 0) {
            $this->db->limit($limit);
        }

        if (is_numeric($offset) && $offset > 0) {
            $this->db->offset($offset);
        }

        $notifications = $this->db->order_by('notifications_read')->order_by('notifications_date_creation', 'desc')->get_where('notifications', array('notifications_user_id' => $user_id))->result_array();

        return array_map(function ($notification) {
            switch (true) {
                case filter_var($notification['notifications_link'], FILTER_VALIDATE_URL):
                    // Il link è un URL intero, quindi inseriscilo così senza toccarlo
                    $href = $notification['notifications_link'];
                    break;

                case is_numeric($notification['notifications_link']):
                    // Il link è numerico, quindi assumo che sia l'id del layout che devo linkare
                    $href = base_url("main/layout/{$notification['notifications_link']}");
                    break;

                case $notification['notifications_link']:
                    // Il link non è né un URL, né un numero, ma non è vuoto, quindi assumo che sia un URI e lo wrappo con base_url();
                    $href = base_url($notification['notifications_link']);
                    break;

                default:
                    // Non è stato inserito nessun link quindi metti un'azione vuota nell'href
                    $href = 'javascript:void(0);';
            }


            switch ($notification['notifications_type']) {
                case NOTIFICATION_TYPE_ERROR:
                    $label = ['class' => 'bg-red-thunderbird', 'icon' => 'fas fa-exclamation'];
                    break;

                case NOTIFICATION_TYPE_INFO:
                    $label = ['class' => 'bg-blue-steel', 'icon' => 'fas fa-bullhorn'];
                    break;

                case NOTIFICATION_TYPE_MESSAGE:
                    $label = ['class' => 'bg-green-jungle', 'icon' => 'fas fa-comment'];
                    break;

                case NOTIFICATION_TYPE_WARNING:
                default:
                    $label = ['class' => 'bg-yellow-gold', 'icon' => 'fas fa-bell'];
                    break;
            }

            $nDate = new DateTime($notification['notifications_date_creation']);
            $diff = $nDate->diff(new DateTime);

            switch (true) {
                case $diff->d < 1:
                    $datespan = $nDate->format('H:i');
                    break;
                case $diff->days == 1:
                    $datespan = 'yesterday';
                    break;
                default:
                    $datespan = $nDate->format('d M');
            }

            $notification['href'] = $href;
            $notification['label'] = $label;
            $notification['datespan'] = $datespan;
            return $notification;
        }, $notifications);
    }

    public function readAllNotifications()
    {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        $this->db->update('notifications', array('notifications_read' => DB_BOOL_TRUE), array('notifications_user_id' => $user_id));
    }

    public function readNotification($notificationId)
    {
        $this->db->update('notifications', array('notifications_read' => DB_BOOL_TRUE), array(
            'notifications_user_id' => $this->auth->get('id'),
            'notifications_id' => $notificationId
        ));
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
            ->get_where('post_process', array(
                'post_process_entity_id' => $entity_id,
                'post_process_when' => $when,
                'post_process_crm' => DB_BOOL_TRUE
            ));

        if ($post_process->num_rows() > 0) {
            foreach ($post_process->result_array() as $function) {
                //20191001 - Matteo Puppis - Se arrivo qua, potrei avere anche dei fi_events con action non gestita.
                //Es.: i fi_events di tipo custom code, creano anche il relativo pp che continuerà a funzionare senza problemi (è retro compatibile).
                //Le nuove action però (quindi non i custom code, che continueranno a funzionare con gli eval), devono avere una gestione ad hoc.

                switch ($function['fi_events_action']) {
                    case '':
                        eval($function['post_process_what']);
                        break;
                    case 'curl':
                        $ch = curl_init();
                        $url = json_decode($function['fi_events_actiondata'], true)['url'];

                        //die('test');
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

        return isset($this->_accessibleEntityLayouts[$entity_id]) ? base_url("{$baseRoute}/{$this->_accessibleEntityLayouts[$entity_id]}/{$value_id}{$suffix}") : false;
    }

    public function generate_menu_link($menu, $value_id = NULL, $data = NULL)
    {
        $link = '';
        if ($menu['menu_layout'] && $menu['menu_layout'] != '-2') {

            $controller_method = (($menu['menu_modal'] == DB_BOOL_TRUE) ? 'get_ajax/layout_modal' : 'main/layout');
            $link = base_url("{$controller_method}/{$menu['menu_layout']}") . $menu['menu_link'];
        } elseif ($menu['menu_link']) {
            link = str_replace('{base_url}', base_url(), $menu['menu_link']);

          lseif  $menu['menu_form'])  

            link = base_url("get_ajax/modal_form/{$menu['menu_form']}") . $menu['menu_link'];

        

        / Valuto se ho dati su cui fare il replace

        f  !is_null($value_id))  

            link = str_replace('{value_id}', $value_id, $link);

        

        f  $data !== NULL && is_array($data))  

            replace_data = array();

            oreach  $data as $key => $value)  

                f  !is_numeric($key) && !is_array($value))  

                    replace_data['{' . $key . '}'] = $value;

                

            

            link = str_replace(array_keys($replace_data), array_values($replace_data), $link);

        

        eturn $this->replace_superglobal_data($link);

    

    **
     * Controllo permessi
     */
    public function is_admin($user_id = NULL)

    

        f  $user_id === NULL || $user_id == $this->auth->get(LOGIN_ENTITY . "_id"))  
            // Sto controllando me stesso

            eturn $this->auth->is_admin();

          lse  

            query = $this->db->where('permissions_user_id', $user_id)->get('permissions');

            eturn  $query->num_rows() > 0 ? $query->row()->permissions_admin == DB_BOOL_TRUE :  ALSE);

        

    

    ublic function get_menu($position = 'sidebar')

    

        / Prendi tutti i menu, con i sottomenu e poi ciclandoli costruisci un array multidimensionale

        menu = $this->db->from('menu')->join('layouts', 'layouts.layouts_id = menu.menu_layout', 'left')
>where('menu_position', $position)->order_by('menu_order')->get()->result_array();

        return = $subs = [];

        oreach  $menu as $item)  

            f  $item['menu_parent'])  
                // Se c'è un parent è un sottomenu

                sset($subs[$item['menu_parent']]) or $subs[$item['menu_parent']] = array();

                item['pages_names'] = array("layout_{$item['menu_layout']}");

                subs[$item['menu_parent']][] = $item;

              lse  
                // Altrimenti potrebbe avere un sottomenu: imposto have_submenu

                / a false e predispongo l'array dei sottomenu

                item['have_submenu'] = false;

                item['submenu'] = array();

                item['pages_names'] = array("layout_{$item['menu_layout']}");

                return[$item['menu_id']] = $item;

            

        

        / Inserisci il sottomenu per ogni menu padre

        oreach  $subs as $parent => $items)  

            f  isset($return[$parent]['submenu']))  
                // Dovrei avere realmente dei sottomenu.. se non ci sono vuol

                / dire che sono proibiti dai permessi...

                return[$parent]['have_submenu'] = true;

                / Prendo i sottomenu che mi sono concessi...

                return[$parent]['submenu'] = array_filter($items, function  $menu)  

                    eturn empty($menu['menu_layout']) or array_key_exists($menu['menu_layout'], $this->_accessibleLayouts);

                );

                oreach  $items as $item)  

                    return[$parent]['pages_names'][] = "layout_{$item['menu_layout']}";

                

            

        

        eturn array_filter($return, function  $menu)  
            // Il layout è accessibile per i permessi? (il link del menu è

            / considerato sempre accessibile se non punta ad un layout)

            f  !empty($menu['menu_layout']) && $menu['menu_layout'] != '-2' && !array_key_exists($menu['menu_layout'], $this->_accessibleLayouts))  

                eturn false;

            

            / Dato che il layout è accessibile, verifico se dovrebbe essere un

            / container di sottomenu e se effettivamente ha i sottomenu, perché

            / se `have_submenu` === true, allora `submenu` dev'essere pieno,

            / ---

            / se non lo fosse, allora tutte le voci del sottomenu sono bloccate

            / da permessi e quindi non voglio mostrare nemmeno il parent vuoto.

            shouldHasSubmenu = $menu['have_submenu'];

            hasReallySubmenu = count($menu['submenu']) > 0;

            eturn  !$shouldHasSubmenu or $hasReallySubmenu);

        );

    

    ublic function getUserLimits($entity, $user = null)

    

        f  !$user)  

            user = $this->auth->get('id');

        

        f  is_numeric($entity))  

            entityId = $entity;

          lse  

            f  !is_array($entity))  

                entity = $this->get_entity_by_name($entity);

            

            entityId = $entity['entity_id'];

        

        query = "SELECT * FROM limits JOIN fields ON (limits_fields_id = fields_id) WHERE limits_user_id = ? AND fields_entity_id = ?";

        eturn $this->db->query($query, [((int)$user, ((int)$entityId])->result_array();

    

    ublic function can_write_entity($entity_id)

    

        f  $this->is_admin())  

            eturn TRUE;

          lse  
            // Resolve the entity id

            entity_id = is_numeric($entity_id) ? $entity_id :  this->get_entity_by_name($entity_id)['entity_id'];

            user_id = ((int)$this->auth->get('id');

            permissions = $this->db->from('permissions')
>join('permissions_entities', 'permissions_entities_permissions_id = permissions_id', 'left')
>where(array('permissions_user_id' => $user_id, 'permissions_entities_entity_id' => $entity_id))
>get()->row();

            eturn empty($permissions) ||  $permissions->permissions_entities_value == PERMISSION_WRITE);

        

    

    ublic function can_read_entity($entity_id)

    

        f  $this->is_admin())  

            eturn TRUE;

          lse  

            user_id = ((int)$this->auth->get('id');

            permissions = $this->db->from('permissions')
>join('permissions_entities', 'permissions_entities_permissions_id = permissions_id', 'left')
>where(array('permissions_user_id' => $user_id, 'permissions_entities_entity_id' => $entity_id))
>get()->row();

            eturn empty($permissions) ||  $permissions->permissions_entities_value != PERMISSION_NONE);

        

    

    ublic function can_access_layout($layout_id)

    

        f  !$layout_id or !is_numeric($layout_id))  

            eturn false;

        

        eturn isset($this->_accessibleLayouts[$layout_id]) or isset($this->_forwardedLayouts[$layout_id]);

    

    ublic function setPermissions($userOrGroup, $isAdmin, array $entitiesPermissions, array $modulesPermissions)

    

        f  !$userOrGroup)  

            hrow new Exception("Il nome gruppo o utente non può essere vuoto");

        

        f  $isAdmin !== DB_BOOL_TRUE && $isAdmin !== DB_BOOL_FALSE)  

            isAdmin =  is_bool($isAdmin) ?  $isAdmin ? DB_BOOL_TRUE :  B_BOOL_FALSE) :  B_BOOL_FALSE);

        

        ry  

            perm = $this->getPermission($userOrGroup);

            permId = $perm['permissions_id'];

            update = [];

            f  is_numeric($userOrGroup))  
                // Se faccio setPermissions(idUtente, ... ) automaticamente tolgo

                / l'utente dal gruppo

                update['permissions_group'] = null;

            

            f  $isAdmin !== $perm['permissions_admin'])  
                // Se è cambiato lo stato di amministratore per il vecchio

                / permesso, devo notificare la modifica

                update['permissions_admin'] = $isAdmin;

            
            //print_r($update);

            f  count($update) > 0)  



                this->db->update('permissions', $update, ['permissions_id' => $permId]);

            

          atch  Exception $ex)  

            this->db->insert('permissions', [
permissions_user_id' => is_numeric($userOrGroup) ? $userOrGroup :  ull,
                'permissions_group' => is_numeric($userOrGroup) ? null :  userOrGroup,
                'permissions_admin' => $isAdmin,
);

            permId = $this->db->insert_id();

        

        / $entitiesPermissions e $modulesPermissions devono essere array nella

        / forma $entityId => $permissionValue e $moduleName => $permissionValue

        this->insertEntitiesPermissions($permId, $entitiesPermissions);

        this->insertModulesPermissions($permId, $modulesPermissions);

        this->fixPermissions();

    

    ublic function getPermission($userOrGroup)

    

        f  is_numeric($userOrGroup))  
            // Is User

            perm = $this->db->get_where('permissions', array('permissions_user_id' => $userOrGroup))->row_array();

          lse  
            // Is Group

            perm = $this->db->where('permissions_user_id IS NULL')
>get_where('permissions', array('permissions_group' => $userOrGroup))->row_array();

        

        f  empty($perm))  

            hrow new Exception(sprintf('Nessun utente o gruppo trovato per %s', $userOrGroup));

        

        eturn $perm;

    

    ublic function removePermissionById($id)

    

        this->db->delete('permissions', ['permissions_id' => $id]);

        this->fixPermissions();

    

    ublic function fixPermissions()

    
        // Cancella i permessi che non hanno più senso di esistere

        this->db->where('permissions_user_id IS NOT NULL AND permissions_user_id NOT IN (SELECT ' . LOGIN_ENTITY . '_id FROM ' . LOGIN_ENTITY . ')')->delete('permissions');

        this->db->where('permissions_entities_permissions_id NOT IN (SELECT permissions_id FROM permissions)')->delete('permissions_entities');

        this->db->where('permissions_modules_permissions_id NOT IN (SELECT permissions_id FROM permissions)')->delete('permissions_modules');

        / Togli l'eventuale gruppo agli utenti se non esiste

        f  $this->db->dbdriver == 'postgre')  

            this->db->query("
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

          lse  

            this->db->query("
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

        

    

    ublic function addUserGroup($userId, $groupName)

    

        f  !is_numeric($userId) or !is_string($groupName) or $userId < 1 or !$groupName)  

            hrow new InvalidArgumentException('Impossibile aggiungere lo user al gruppo: $userId deve contenere un id valido e il nome deve essere una stringa');

        

        f  !$this->db->where(LOGIN_ENTITY . '_id', $userId)->count_all_results(LOGIN_ENTITY))  

            hrow new Exception("L'utente '{$userId}' non esiste");

        

        / Recupera permessi del gruppo

        permissions = $this->getPermission($groupName);

        permissionsEntities = $this->db->get_where('permissions_entities', array('permissions_entities_permissions_id' => $permissions['permissions_id']))->result_array();

        permissionsModules = $this->db->get_where('permissions_modules', array('permissions_modules_permissions_id' => $permissions['permissions_id']))->result_array();

        this->db->trans_start();

        / Cancella i permessi vecchi dell'utente

        this->db->delete('permissions', array('permissions_user_id' => $userId));

        this->fixPermissions();

        / Rimuovi il campo id dai permessi del gruppo ottenuto, in modo da

        / poterlo clonare e aggiungi l'id dell'utente

        nset($permissions['permissions_id']);

        permissions['permissions_user_id'] = $userId;

        this->db->insert('permissions', $permissions);

        permissionId = $this->db->insert_id();

        / Rimappa i permessi entità/moduli in idEntità => permesso e

        / nomeModulo => permesso

        this->insertEntitiesPermissions($permissionId, array_combine(array_key_map($permissionsEntities, 'permissions_entities_entity_id'), array_key_map($permissionsEntities, 'permissions_entities_value')));

        this->insertModulesPermissions($permissionId, array_combine(array_key_map($permissionsModules, 'permissions_modules_module_name'), array_key_map($permissionsModules, 'permissions_modules_value')));

        / Assegnando un utente ad un gruppo devo anche assegnargli i layout che

        / può o non può vedere

        this->assignUnallowedLayoutAsGroup($userId, $groupName);

        this->db->trans_complete();

        eturn $permissionId;

    

    ublic function insertEntitiesPermissions($permId, array $entitiesPermissions)

    

        this->db->delete('permissions_entities', ['permissions_entities_permissions_id' => $permId]);

        entitiesPermissionsData = [];

        oreach  $entitiesPermissions as $entityId => $permissionValue)  

            entitiesPermissionsData[] = ['permissions_entities_permissions_id' => $permId, 'permissions_entities_entity_id' => $entityId, 'permissions_entities_value' => $permissionValue];

        

        f  $entitiesPermissionsData)  

            this->db->insert_batch('permissions_entities', $entitiesPermissionsData);

        

    

    ublic function insertModulesPermissions($permId, array $modulesPermissions)

    

        this->db->delete('permissions_modules', ['permissions_modules_permissions_id' => $permId]);

        modulesPermissionsData = [];

        oreach  $modulesPermissions as $moduleName => $permissionValue)  

            f  $moduleName === 0)   //20190613 - MP - Sembra che la nuova gestione moduli passi qualcosa di sbagliato e quindi qui arriva un modulo con nome '0'... ovviamente inesistente nel db. Skippo

                /debug($modulesPermissions);

                ontinue;

            

            modulesPermissionsData[] = ['permissions_modules_permissions_id' => $permId, 'permissions_modules_module_name' => $moduleName, 'permissions_modules_value' => $permissionValue];

        

        f  $modulesPermissionsData)  

            this->db->insert_batch('permissions_modules', $modulesPermissionsData);

        

    

    ublic function assignUnallowedLayoutAsGroup($userId, $groupName)

    

        f  is_numeric($groupName))  

            hrow new Exception("Il nome gruppo non può essere numerico");

        

        old_unallowedLayouts = $this->db->get_where('unallowed_layouts', ['unallowed_layouts_user' => $userId]);

        / Elimino impostazioni accessi layout correnti per l'utente passato

        this->db->delete('unallowed_layouts', ['unallowed_layouts_user' => $userId]);

        / Recupero viste permessi per l'utente corrente

        / Se non ne trovo ho finito, in quanto ho già eliminato le vecchie

        / impostazioni e non ci sono altri utenti da cui copiare i layout non

        / accessibili

        f  defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD)  

            permissionWithGroup = $this->db
>where('permissions_user_id IS NOT NULL')
>where('permissions_user_id IN (SELECT ' . LOGIN_ENTITY . '_id FROM ' . LOGIN_ENTITY . ' WHERE ' . LOGIN_ACTIVE_FIELD . ' = \'' . DB_BOOL_TRUE . '\')')
>get_where('permissions', [
permissions_group' => $groupName,
                    'permissions_user_id <>' => $userId
);

          lse  

            permissionWithGroup = $this->db
>where('permissions_user_id IS NOT NULL')
                //->where('permissions_user_id IN (SELECT '.LOGIN_ENTITY.'_id FROM '.LOGIN_ENTITY.' WHERE '.LOGIN_ACTIVE_FIELD.' = \''.DB_BOOL_TRUE.'\')') 

            >get_where('permissions', [
permissions_group' => $groupName,
                    'permissions_user_id <>' => $userId
);

        


        /debug($permissionWithGroup,true);

        f  !$permissionWithGroup->num_rows())  

            permissionWithGroup = $this->db
>get_where('permissions', [
permissions_user_id <>' => $userId
);
            //Riprendo i suoi vecchi unallowed...

            unallowedLayouts = $old_unallowedLayouts;
            //return;

          lse  
            // Anche qua recupero l'utente e i suoi accessi al layout, se non trovo

            / nulla significa che l'utente, e quindi il gruppo, può accedere a

            / qualunque layout

            anotherUser = $permissionWithGroup->row()->permissions_user_id;

            unallowedLayouts = $this->db->get_where('unallowed_layouts', ['unallowed_layouts_user' => $anotherUser]);

        


        f  !$unallowedLayouts->num_rows())  

            eturn;

        

        / Rimappo ogni record in modo da cambiare lo user id e inserisco in

        / batch il tutto

        newData = array_map(function  $row) use  $userId)  

            row['unallowed_layouts_user'] = $userId;

            eturn $row;

        , $unallowedLayouts->result_array());

        this->db->insert_batch('unallowed_layouts', $newData);

    

    ublic function getUserGroups()

    

        idField = LOGIN_ENTITY . '_id';

        /Fix per non prendere tutti gli utenti ma solo quelli che possono fare login

        f  defined('LOGIN_ACTIVE_FIELD') && !empty(LOGIN_ACTIVE_FIELD))  

            this->db->where("permissions_user_id IN (SELECT " . LOGIN_ENTITY . "_id FROM " . LOGIN_ENTITY . " WHERE " . LOGIN_ACTIVE_FIELD . " = '" . DB_BOOL_TRUE . "')", null, false);

        

        users = $this->db
>join('permissions', "{$idField} = permissions_user_id", 'left')
>where('permissions_user_id IS NOT NULL')->get(LOGIN_ENTITY)->result_array();

        out = [];

        oreach  $users as $user)  

            out[$user[LOGIN_ENTITY . '_id']] = $user['permissions_group'] ?:  ull;

        

        eturn $out;

    

    ublic function get_modules()

    

        user_id = $this->auth->get(LOGIN_ENTITY . "_id");

        f  $this->is_admin($user_id))  

            modules = $this->db->get_where('modules', array('modules_installed' => DB_BOOL_TRUE,));

          lse  

            modules = $this->db->select('modules.*')->from('modules')->join('permissions_modules', 'permissions_modules_module_name = modules_name', 'left')->join('permissions', 'permissions_modules_permissions_id = permissions_id', 'left')->where(array(
permissions_modules_value' => PERMISSION_WRITE,
                'modules_installed' => DB_BOOL_TRUE,
                'permissions_user_id' => $user_id,
)->get();

        

        eturn $modules->result_array();

    

    ublic function module_installed($name)

    

        query = $this->db->from('modules')->where('modules_installed', DB_BOOL_TRUE)->where('modules_name', $name)->get();

        eturn $query->num_rows() > 0;

    

    ublic function module_access($name)

    

        user_id = $this->auth->get(LOGIN_ENTITY . "_id");

        query = $this->db->from('permissions')->join('permissions_modules', 'permissions_modules_permissions_id = permissions_id', 'left')
>where('permissions_modules_value', PERMISSION_WRITE)
>where('permissions_modules_module_name', $name)
>where('permissions_user_id', $user_id)->get();

        eturn $query->num_rows() > 0 || $this->is_admin($user_id);

    

    **
     * Search
     */
    public function get_search_results($search)

    
        //Ottengo le entità cercabili

        entities = $this->db->get_where('entity', array('entity_searchable' => DB_BOOL_TRUE))->result_array();

        e_ids = array_map(function  $entity)  

            eturn $entity['entity_id'];

        , $entities);

        results = array();

        f  !empty($e_ids))  

            _all_fields = $this->db->where_in('fields_entity_id', $e_ids)->get('fields')->result_array();

            all_fields = array();

            oreach  $_all_fields as $field)  

                all_fields[$field['fields_entity_id']][] = $field;

            

            oreach  $entities as $entity)  

                fields = $all_fields[$entity['entity_id']];

                where = $this->search_like($search, $fields);

                /Calcola risultato e consideralo sse ha dati effettivi

                result = $this->getDataEntity($entity['entity_id'], $where, null, null, null, 1);

                f  $result)  

                    results[] = [
entity' => $entity,
                        'visible_fields' => $this->crmentity->getVisibleFields($entity['entity_id']),
                        'data' => $result
;

                

            

        

        eturn $results;

    

    ublic function search_like($search = '', $fields = array())

    

        outer_where = array();

        /Pulisco eventuali field doppi

        fields_ids = [];



        f  $search)  

            maxint4 = 2147483647;  // Max per int4

            ** FIX: Cerco gli eventuali support fields di un singolo field e li metto in un array al più bidimensionale * */
            $_fields = array();

            oreach  $fields as $field)  

                f  in_array($field['fields_id'], $fields_ids) && $field['fields_id'] != '')
                    continue;

                f  empty($field['support_fields']))  

                    f  isset($field['fields_type']) && isset($field['fields_name']))  

                        _fields[] = $field;

                      lse  
                        //Se entro qui potrebbe essere un eval cachable...

                        _fields[] = $field;

                    

                  lse  

                    oreach  $field['support_fields'] as $sfield)  

                        f  isset($sfield['fields_type']) && isset($sfield['fields_name']) && isset($sfield['fields_preview']) && $sfield['fields_preview'] === DB_BOOL_TRUE)  

                            _fields[] = $sfield;

                        

                    

                

                fields_ids[] = $field['fields_id'];

            

            fields = $_fields;


            *
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

            f  !defined('EXPLODE_SPACES') || EXPLODE_SPACES === true)  

                search_chunks = array_unique(array_filter(explode(' ', $search), function  $chunk)  

                    eturn $chunk && strlen($chunk) >  defined('MIN_SEARCH_CHARS') ?  MIN_SEARCH_CHARS - 1) :  );

                ));

              lse  

                search_chunks = array_unique(array_filter([$search], function  $chunk)  

                    eturn $chunk && strlen($chunk) >  defined('MIN_SEARCH_CHARS') ?  MIN_SEARCH_CHARS - 1) :  );

                ));

            


            / Sono interessato ai record che contengono TUTTI i chunk in uno o più campi

            oreach  $search_chunks as $_chunk)  

                chunk = str_replace("'", "''", $_chunk);

                inner_where = [];

                oreach  $fields as $field)  

                    f  !empty($field['fields_type']))  

                        witch  ($type = strtoupper($field['fields_type'])))  

                            ase 'VARCHAR':

                            ase 'TEXT':

                                f  $this->db->dbdriver != 'postgre')  

                                    chunk = strtolower($chunk);

                                    inner_where[] = "LOWER({$field['fields_name']} LIKE '%{$chunk}%')";

                                  lse  

                                    inner_where[] = "({$field['fields_name']}::TEXT ILIKE '%{$chunk}%')";

                                

                                reak;

                            ase 'INT':

                                f  is_numeric($chunk) && $chunk <= $maxint4)  

                                    i_chunk = ((int)$chunk;

                                    inner_where[] = "({$field['fields_name']} = '{$i_chunk}')";

                                

                                reak;

                            ase 'FLOAT':

                                f  is_numeric($chunk))  

                                    f_chunk = ((float)$chunk;

                                    inner_where[] = "({$field['fields_name']} = '{$f_chunk}')";

                                

                                reak;

                        

                      lse  

                        f  !empty($field['grids_fields_eval_cache_data']))  

                            f  $this->db->dbdriver != 'postgre')  

                                chunk = strtolower($chunk);

                                inner_where[] = "LOWER({$field['grids_fields_eval_cache_data']} LIKE '%{$chunk}%')";

                              lse  

                                inner_where[] = "({$field['grids_fields_eval_cache_data']}::TEXT ILIKE '%{$chunk}%')";

                            

                        

                    

                
                //debug($inner_where,true);

                f  $inner_where)  

                    outer_where[] = '(' . implode(' OR ', $inner_where) . ')';

                

            

        

        eturn implode(' AND ', $outer_where);

    

    rivate function is_layout_cachable($layout_id)

    

        eturn $this->db->get_where('layouts', array('layouts_id' => $layout_id, 'layouts_cachable' => DB_BOOL_TRUE))->num_rows() == 1;

    

    **
     * Layout builder
     */
    public function build_layout($layout_id, $value_id, $layout_data_detail = null)

    

        cache_key = "datab.build_layout.{$layout_id}.{$value_id}." . md5(serialize($_GET)) . md5(serialize($_POST)) . md5(serialize($layout_data_detail) . serialize($this->session->all_userdata()));

        f  !($dati = $this->cache->get($cache_key)))  

            f  !is_numeric($layout_id) or  $value_id && !is_numeric($value_id)))  

                eturn null;

            

            f  isset($this->_forwardedLayouts[$layout_id]))  

                layout_id = $this->_forwardedLayouts[$layout_id];

            


            / ========================================

            / Inizio Build Layout

            / ========================================

            this->layout->addLayout($layout_id);

            dati['layout_container'] = $this->db->get_where('layouts', array('layouts_id' => $layout_id))->row_array();

            f  empty($dati['layout_container']))  

                how_404();

            

            f  $value_id && $dati['layout_container']['layouts_entity_id'] > 0)  

                entity = $this->crmentity->getEntity($dati['layout_container']['layouts_entity_id']);

                f  isset($entity['entity_name']))  

                    data_entity = $this->getDataEntity($entity['entity_id'], ["{$entity['entity_name']}_id" => $value_id], 1);

                    layout_data_detail = array_shift($data_entity);

                

            

            f  is_null($layout_data_detail) && $dati['layout_container']['layouts_is_entity_detail'] === DB_BOOL_TRUE)  

                this->layout->removeLastLayout($layout_id);

                eturn null;

            


            layouts = $this->layout->getBoxes($layout_id);

            dati['pre-layout'] = $this->getHookContent('pre-layout', $layout_id, $value_id);

            dati['post-layout'] = $this->getHookContent('post-layout', $layout_id, $value_id);

            dati['layout'] = array();

            /debug($layouts,true);

            / Ricavo il content se necessario

            oreach  $layouts as $layout)  

                / Recupero del contenuto del layout

                / ---

                / Precedentemente questa operazione veniva effettuata in questo

                / punto, ma per motivi di dimensione e complessità della procedura

                / è stata spostata in un metodo a se `getBoxContent`

                layout['content'] = $this->getBoxContent($layout, $value_id, $layout_data_detail);


                / Fa il wrap degli hook pre e post che devono esistere per ogni

                / componente ad eccezione di custom views e custom php code

                / ---

                / Gli hook per il layout non vengono definiti da qua ma vengono

                / presi globali all'inizio del build layout

                hookSuffix = $layout['layouts_boxes_content_type'];

                hookRef = $layout['layouts_boxes_content_ref'];

                f  $hookSuffix && is_numeric($hookRef) && $hookSuffix !== 'layout')  

                    layout['content'] = $this->getHookContent('pre-' . $hookSuffix, $hookRef, $value_id) .
                        $layout['content'] .
                        $this->getHookContent('post-' . $hookSuffix, $hookRef, $value_id);

                

                dati['layout'][$layout['layouts_boxes_row']][] = $layout;

            

            / I dati del record di dettaglio

            f  !empty($layout_data_detail))  

                replaces = $layout_data_detail;

                replaces['value_id'] = $value_id;

                dati['layout_container']['layouts_title'] = str_replace_placeholders($dati['layout_container']['layouts_title'], $replaces);

                dati['layout_container']['layouts_subtitle'] = str_replace_placeholders($dati['layout_container']['layouts_subtitle'], $replaces);

            

            dati['layout_data_detail'] = $layout_data_detail;

            f  $this->is_layout_cachable($layout_id))  

                this->cache->save($cache_key, $dati, self::CACHE_TIME);

            
            // ========================================

            / Fine Build Layout

            / ========================================

            this->layout->removeLastLayout($layout_id);

        

        eturn $dati;

    

    **
     * Carica una custom view
     * 
     * @param string $viewName
     * @param array $data
     * @param bool $return
     */
    public function loadCustomView($viewName, $data = [], $return = false)

    

        eturn $this->load->view("pages/layouts/custom_views/{$viewName}", $data, $return);

    

    **
     * Renderizza contenuto di un layout
     * 
     * @param string $hookType
     * @param int|string $hookRef
     * @param int|null $valueId
     * @return string
     */
    public function getHookContent($hookType, $hookRef, $valueId = null)

    

        hooks_by_type = array_get($this->_precalcHooks(), $hookType, []);

        hooks = array_filter($hooks_by_type, function  $hook) use  $hookRef)  

            eturn  $hook['hooks_ref'] == $hookRef or !$hook['hooks_ref']);

        );

        plainHookContent = trim(implode(PHP_EOL, array_key_map($hooks, 'hooks_content', '')));

        f  !$plainHookContent)  

            eturn '';

        

        b_start();

        value_id = $valueId;   // per comodità e uniformità...

        val(' ?> ' . $plainHookContent . ' <?php ');

        eturn ob_get_clean();

    

    rivate function _precalcHooks()

    

        f  is_null($this->_hooks))  

            hooks = $this->db->order_by('hooks_order')->get('hooks')->result_array();

            this->_hooks = [];

            / Raggruppo gli hook per tipo

            oreach  $hooks as $h)  

                this->_hooks[$h['hooks_type']][] = $h;

            

        

        eturn $this->_hooks;

    

    **
     * Build della cella
     */
    public function build_grid_cell($field, $dato, $escape_date = true)

    

        / Valuta eventuali grid fields eval e placeholder

        type = isset($field['grids_fields_replace_type']) ? $field['grids_fields_replace_type'] :  field';

        witch  $type)  

            ase 'placeholder':

                eturn $this->buildPlaceholderGridCell($field['grids_fields_replace'], $dato);

            ase 'eval':

                eturn $this->buildEvalGridCell($field['grids_fields_replace'], $dato, $field);

            ase 'field':

            efault:

                eturn $this->buildFieldGridCell($field, $dato, true, $escape_date);

        

    

    rivate function buildFieldGridCell($field, $dato, $processMultilingual, $escape_date = true)

    

        / =====================================================================

        / Controllo multilingua:

        / Se il field è multilingua allora ciclo tutte le lingue e le stampo 

        / una dopo l'altra

        multilingual = defined('LANG_ENTITY') && LANG_ENTITY && $field['fields_multilingual'] == DB_BOOL_TRUE;

        value = array_key_exists($field['fields_name'], $dato) ? $dato[$field['fields_name']] :  ';

        f  $processMultilingual && $multilingual)  

            f  !$value)  

                eturn '';

            

            out = [];

            contents = json_decode($value, true);

            f  is_array($contents))  

                oreach  $contents as $idLang => $valueLang)  

                    dato[$field['fields_name']] = $valueLang;

                    style =  $idLang != $this->_currentLanguage) ? 'style="display:none"' :  ';

                    out[] = "<div data-lang='{$idLang}' {$style}>" . $this->buildFieldGridCell($field, $dato, false) . '</div>';

                

            

            eturn implode(PHP_EOL, $out);

        

        / =====================================================================

        / Controllo se il campo è stampabile

        / 

        / Controllo se il campo è vuoto: in tal caso, se ho un placeholder 

        / stampo quello altrimenti non ritorno niente

        isEmptyString =  $value === '');

        isRefWithoutValue =  $field['fields_ref'] && !$value);

        f  $isEmptyString or $isRefWithoutValue)  
            // Il campo non è stampabile, quindi torno il placeholder se ce l'ho

            placeholder = trim($field['fields_draw_placeholder']);

            eturn $placeholder ?
                sprintf('<small class="text-muted">%s</small>', $placeholder) :  ';

        



        / =====================================================================

        / Stampa del campo

        /

        /        if ($field['grids_fields_fields_id'] == 320) {

        /            var_dump($field['fields_type']);    

        /            var_dump(DB_INTEGER_IDENTIFIER);    

        /            debug($field['fields_type'],true);

        /        }

        f  $field['fields_ref'] && in_array($field['fields_type'], [DB_INTEGER_IDENTIFIER, 'INT']) && $field['fields_draw_html_type'] != 'multi_upload')  

            f  is_array($value))  
                // Ho una relazione molti a molti - non mi serve alcuna 

                / informazione sui field ref, poiché ho già la preview stampata

                referenced = $this->crmentity->getReferencedEntity($field);

                lnk = $referenced ? $this->get_detail_layout_link($referenced['entity_id']) :  alse;

                f  $lnk)  

                    oreach  $value as $id => $name)  

                        value[$id] = anchor("{$lnk}/{$id}", $name);

                    

                

                eturn implode('<br/>', $value);

              lseif  !empty($field['support_fields']))  
                // Ho un field ref semplice - per stamparlo ho bisogno dei 

                / support fields (che sono i campi preview dell'entità 

                / referenziata)

                link = $value ? $this->get_detail_layout_link($field['support_fields'][0]['fields_entity_id']) :  alse;

                idKey = $field['fields_ref'] . '_id';

                f  empty($field['support_fields']))  
                    // Non ho nessun campo di preview, quindi la preview sarà vuota - stampo solo l'ID del record

                    text = $value;

                  lse  

                    hasAllFields = true;

                    _text = array();

                    oreach  $field['support_fields'] as $support_field)  

                        prefixedKey = $field['fields_name'] . '_' . $support_field['fields_name'];

                        simpleKey = $support_field['fields_name'];

                        f  array_key_exists($prefixedKey, $dato))  

                            / Il caso migliore:    entitàReferenziata_entitàPrincipale_nomeBaseCampo

                            previewSegment = '';

                            f  $support_field['fields_multilingual'] === DB_BOOL_TRUE)  

                                contents = json_decode($dato[$prefixedKey], true);

                                oreach  $contents as $idLang => $valueLang)  

                                    style =  $idLang != $this->_currentLanguage) ? 'style="display:none"' :  ';

                                    previewSegment .= "<div data-lang='{$idLang}' {$style}>" . $valueLang . '</div>';

                                

                              lse  

                                previewSegment = $dato[$prefixedKey];

                            

                            _text[] = $previewSegment;

                          lseif  array_key_exists($simpleKey, $dato) &&  !array_key_exists($idKey, $dato) or $dato[$idKey] == $value))  
                            // Appendo il nuovo campo preview all'array della preview $_text

                            / Attenzione qua però, se l'id è settato ed è

                            / diverso dal mio value id allora non va bene

                            / prendere questo

                            previewSegment = '';

                            f  $support_field['fields_multilingual'] === DB_BOOL_TRUE)  

                                contents = json_decode($dato[$simpleKey], true);

                                f  is_array($contents))  

                                    oreach  $contents as $idLang => $valueLang)  

                                        style =  $idLang != $this->_currentLanguage) ? 'style="display:none"' :  ';

                                        previewSegment .= "<div data-lang='{$idLang}' {$style}>" . $valueLang . '</div>';

                                    

                                

                              lse  

                                previewSegment = $dato[$simpleKey];

                            

                            _text[] = $previewSegment;

                          lse  
                            // Non posso continuare a stampare la preview perché ci sono campi non presenti

                            hasAllFields = false;

                            reak;

                        

                    

                    f  $hasAllFields)  
                        // La preview completa sta nell'arrat $_text

                        text = implode(' ', $_text);

                      lse  
                        // Non ho tutti i campi preview disponibili (ad es. nelle relazioni NxM), quindi faccio una chiamata alla get entity preview

                        value_id = ((int)$value;

                        preview = $this->get_entity_preview_by_name($field['fields_ref'], "{$idKey} = '{$value_id}'", 1);

                        text = array_key_exists($value_id, $preview) ? $preview[$value_id] :  value_id;

                    

                

                / C'è un link? stampo un <a></a> altrimenti stampo il testo puro e semplice

                eturn $link ? anchor(rtrim($link, '/') . '/' . $value, $text) :  text;

            

          lse  
            // Posso stampare il campo in base al tipo

            witch  $field['fields_draw_html_type'])  

                ase 'upload':

                    f  $value)  
                        //                        return anchor(base_url("uploads/$value"), 'Scarica file', array('target' => '_blank'));

                        eturn anchor(base_url_uploads("uploads/$value"), 'Scarica file', array('target' => '_blank'));

                    

                    reak;

                ase 'upload_image':

                    f  $value)  
                        //                        return anchor(base_url("uploads/{$value}"), "<img src='" . base_url("imgn/1/50/50/uploads/{$value}") . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px'));

                        f  $this->config->item('cdn') && $this->config->item('cdn')['enabled'])  

                            _url = base_url_uploads("uploads/{$value}");

                          lse  

                            _url = base_url_admin("imgn/1/50/50/uploads/{$value}");

                        

                        eturn anchor(base_url_uploads("uploads/{$value}"), "<img src='" . $_url . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px'));

                      lse  
                        //                        $path = base_url('images/no-image-50x50.gif');

                        path = base_url_admin('images/no-image-50x50.gif');

                        eturn "<img src='{$path}' style='width: 50px;' />";

                    

                ase 'multi_upload':

                    f  $field['fields_type'] == 'JSON')  

                        value = ((array)json_decode($value, true);

                        value = array_map(function  $item)  
                            //debug($item, true);

                            f  $this->config->item('cdn') && $this->config->item('cdn')['enabled'])  

                                _url = base_url_uploads("uploads/{$item['path_local']}");

                              lse  

                                _url = base_url_admin("imgn/1/50/50/uploads/{$item['path_local']}");

                            

                            eturn anchor(base_url_uploads("uploads/{$item['path_local']}"), "<img src='" . $_url . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px'));

                        , $value);

                      lse   //Se arrivo qua i file sono scritti su un altra tabella, quindi mi arriva già l'array bello pulito con i file...

                        value = array_map(function  $item)  
                            //debug($item, true);

                            f  $this->config->item('cdn') && $this->config->item('cdn')['enabled'])  

                                _url = base_url_uploads("uploads/{$item}");

                              lse  

                                _url = base_url_admin("imgn/1/50/50/uploads/{$item}");

                            

                            eturn anchor(base_url_uploads("uploads/{$item}"), "<img src='" . $_url . "' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px'));

                        , $value);

                    

                    value = implode(' ', $value);

                    f  $value)  

                        eturn $value;

                      lse  

                        path = base_url_admin('images/no-image-50x50.gif');

                        eturn "<img src='{$path}' style='width: 50px;' />";

                    

                ase 'textarea':

                    style = 'white-space: pre-line';

                ase 'wysiwyg':

                    f  empty($style))  

                        style = '';

                    

                    stripped = strip_tags($value);

                    value = preg_replace(array('#<script(.*?)>(.*?)</script>#is', '/<img[^>]+\>/i'), '', $value);
                    //$value = $this->security->xss_clean($value);

                    f  strlen($stripped) > 150)  

                        textContainerID = md5($value);

                        javascript = "event.preventDefault();$(this).parent().hide(); $('.text_{$textContainerID}').show();";

                        eturn '<div><div onclick="' . $javascript . '" style="cursor:pointer;">' . nl2br(character_limiter($stripped, 130)) . '</div>' .
                            '<a onclick="' . $javascript . '" href="#">Vedi tutto</a></div>' .
                            '<div class="text_' . $textContainerID . '" style="display:none;' . $style . '">' .  ($field['fields_draw_html_type'] == 'textarea') ? nl2br($stripped) :  value) . '</div>';

                      lse  

                        eturn  ($field['fields_draw_html_type'] == 'textarea') ? nl2br($stripped) :  value);

                    

                ase 'date':

                    f  $escape_date && $value)  

                        append = "<span class='hide'>{$value}</span>";

                      lse  

                        append = '';

                    

                    eturn $value ? $append . dateFormat($value) :  ull;

                ase 'date_time':
                    //                    var_dump($escape_date);

                    /                    die();

                    f  $escape_date && $value)  

                        append = "<span class='hide'>{$value}</span>";

                      lse  

                        append = '';

                    
                    //die($value ? $append . dateTimeFormat($value) : null);

                    eturn $value ? $append . dateTimeFormat($value) :  ull;


                ase 'stars':

                    out = "<span class='hide'>{$value}</span>";

                    or  $i = 1;  i <= 5;  i++)  

                        class = $i > $value ? 'fa-star-o' :  fa-star';

                        out .= "<i class='fa {$class}'></i>";

                    

                    eturn $out;

                ase 'radio':

                ase 'checkbox':

                    eturn  ($field['fields_type'] == DB_BOOL_IDENTIFIER) ?  ($value == DB_BOOL_TRUE) ? 'Si' :  No') :  value);

                efault:

                    f  $field['fields_type'] === 'DATERANGE')  
                        // Formato daterange 

                        dates = dateRange_to_dates($value);

                        witch  count($dates))  

                            ase 2:

                                eturn 'Dal ' . dateFormat($dates[0]) . ' al ' . dateFormat($dates[1]);

                            ase 0:

                                eturn '';

                            efault:

                                eturn '<small>[Formato daterange errato]</small>';

                        

                      lseif  $field['fields_type'] === 'GEOGRAPHY')  

                        eturn $value['geo'] ? sprintf('<small>Lat:</small>%s, <small>Lon:</small>%s', $value['lat'], $value['lng']) :  ';

                      lseif  filter_var($value, FILTER_VALIDATE_EMAIL))  

                        eturn mailto($value);

                      lseif  filter_var($value, FILTER_VALIDATE_URL) ||  is_string($value) && preg_match("/\A^www.( [^\s]* ).[a-zA-Z]$\z/ix", $value) && filter_var('http://' . $value, FILTER_VALIDATE_URL) !== false))  

                        f  stripos($value, 'http://') === false)  

                            value = 'http://' . $value;

                        

                        eturn anchor($value, str_replace(array('http://', 'https://'), '', $value), array('target' => '_blank'));

                      lseif  $field['fields_type'] === 'INT4RANGE' || $field['fields_type'] === 'INT8RANGE')  

                        eturn $value['from'] . ' - ' . $value['to'];

                      lse  

                        eturn $value;

                    

            

        

    

    rivate function buildPlaceholderGridCell($placeholderedString, $record)

    

        eturn $this->replace_superglobal_data(str_replace_placeholders($placeholderedString, $record));

    

    rivate function buildEvalGridCell($evalString, $data, $field)

    

        b_start();

        val('?> ' . $evalString . '<?php ');

        eturn ob_get_clean();

    

    **
     * Build del form input
     */
    public function build_form_input(array $field, $value = null)

    

        f  !$value && !empty($field['forms_fields_default_value']))  

            value = $this->get_default_fields_value($field);

        

        output = '';

        isMultilingual = defined('LANG_ENTITY') && LANG_ENTITY && $field['fields_multilingual'] == DB_BOOL_TRUE;

        languages = $isMultilingual ? $this->_languages :  null];

        *
         * Mi assicuro che le seguenti chiavi esistano nel field perché posso
         * anche usare questo metodo quando non sono in ambiente form e i campi
         * non sono definiti. Se non ci sono li metto a null
         */

        f  !isset($field['forms_fields_override_label']))  

            keys = ['forms_fields_override_label', 'forms_fields_override_type', 'forms_fields_override_placeholder', 'forms_fields_show_required', 'forms_fields_show_label', 'forms_fields_subform_id'];

            field = array_merge(array_fill_keys($keys, null), $field);

        

        *
         * Parametri di base dell'input
         * ---
         * Questi sono i parametri presi dal form_field (o, se non presenti, dal
         * field) 
         * -- Messa una chiocciola 
         */

        baseLabel = $field['forms_fields_override_label'] ?:  field['fields_draw_label'];

        baseType = $field['forms_fields_override_type'] ?:  field['fields_draw_html_type'];

        basePlaceholder = $field['forms_fields_override_placeholder'] ?:  field['fields_draw_placeholder'];

        baseHelpText = $field['fields_draw_help_text'] ? '<span class="help-block">' . $field['fields_draw_help_text'] . '</span>' :  ';

        baseShow = $field['fields_draw_display_none'] == DB_BOOL_FALSE;

        baseShowRequired = $field['forms_fields_show_required'] ? $field['forms_fields_show_required'] == DB_BOOL_TRUE :  $field['fields_required'] == DB_BOOL_TRUE && !trim($field['fields_default']));

        baseShowLabel = $field['forms_fields_show_label'] ? $field['forms_fields_show_label'] == DB_BOOL_TRUE :  rue;    // Se è vuoto mostro sempre la label di default, altrimenti valuto il campo

        baseOnclick = $field['fields_draw_onclick'] ? sprintf('onclick="%s"', $field['fields_draw_onclick']) :  ';

        subform = $field['forms_fields_subform_id'] ?:  ull;

        class = $field['fields_draw_css_extra'] . ' field_' . $field['fields_id'];

        name = $field['fields_name'];

        f  $isMultilingual)  

            value = json_decode($value, true);

        

        *
         * Valori di default per monolingua
         * ---
         * Il caso monolingua è gestito come un multilingua con lingua === null
         */

        langId = null;

        langShow = $baseShow;

        langValue = $value;

        langAttribute = null;

        langLabel = $baseLabel;

        oreach  $languages as $id => $lang)  
            /*
             * Faccio l'override delle variabili nel caso di multilingua e
             * quindi una lingua valida ($lang non vuoto) e sia attivo e
             * configurato nel crm il sistema multilingua. Se così non fosse,
             * avrei i valori già settati precedentemente
             */

            f  $lang && $isMultilingual)  
                // Override dei valori per multilingua

                field['fields_name'] = $name . "[{$id}]";

                langLabel = sprintf('<img src="%s" alt="%s" class="lang-flag" /> ', $lang['flag'], $lang['name']) . $baseLabel;

                langId = $id;

                langShow = $this->_currentLanguage == $id && $baseShow;

                langValue = isset($value[$id]) ? $value[$id] :  ull;

                langAttribute = "data-lang='{$id}'";

            


            style = $langShow ? '' :  style="display:none"';

            label = $baseShowLabel ? '<label class="control-label">' . $langLabel . '</label>' .  $baseShowRequired ? ' <small class="text-danger fas fa-asterisk" style="font-size: 85%"></small>' :  ') :  ';

            data = [
lang' => $langId,
                'field' => $field,
                'value' => is_string($langValue) ? htmlspecialchars($langValue) :  langValue,
                'label' => $label,
                'placeholder' => $basePlaceholder,
                'help' => $baseHelpText,
                'class' => $class,
                'onclick' => $baseOnclick,
                'subform' => $subform
;

            /20190610 - Matteo Puppis - if value is a comma separated string of values, explodes...

            /            if ($field['fields_type'] == 'INT' && count(explode(',',$data['value'])) > 1) {

            /                $data['value'] = explode(',', $data['value']);

            /            }

            /debug($field,true);

            /20190409 - Matteo Puppis - Aggiungo la preview del value, da usare nelle nuove select_ajax

            f  !empty($data['value']) && !is_array($data['value']) && !empty($field['fields_ref']) && $field['forms_fields_override_type'] != 'input_hidden')  

                preview = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id = '{$data['value']}'", 1);

                data['value_preview'] = array_pop($preview);

            

            view = $this->load->view("box/form_fields/{$baseType}", $data, true);

            f  $baseType !== 'input_hidden')  

                wrapAttributes = implode(' ', array_filter([$style, $langAttribute]));

                view = sprintf('<div class="form-group" %s>%s</div>', $wrapAttributes, $view);

            

            output .= $view;

        

        eturn $output;

    

    **
     * Retrieve the box contents from the layout box definition
     * 
     * @param array $layoutBoxData      Layout box def
     * @param int|null $value_id        Value ID
     * @param array $layoutEntityData   Data related
     * 
     * @return string
     */
    private function getBoxContent($layoutBoxData, $value_id = null, $layoutEntityData = [])

    

        contentType = $layoutBoxData['layouts_boxes_content_type'];

        contentRef = $layoutBoxData['layouts_boxes_content_ref'];

        witch  $contentType)  

            ase "layout":

                subLayout = $this->build_layout($contentRef, $value_id, $layoutEntityData);

                subLayout['current_page'] = sprintf("layout_%s", $layoutBoxData['layouts_boxes_layout']);

                subLayout['show_title'] = false;

                eturn $this->load->view("pages/layout", array('dati' => $subLayout, 'value_id' => $value_id), true);

            ase 'tabs':

                tabs = [];

                tabId = 'tabs_' . $layoutBoxData['layouts_boxes_id'];

                subboxes =  isset($layoutBoxData['subboxes']) && is_array($layoutBoxData['subboxes'])) ? $layoutBoxData['subboxes'] :  ];

                oreach  $subboxes as $key => $subbox)  
                    //20190606 - MP - Nelle tab non venivano scatenati i pre-grid, post-grid, ecc.... ora si!

                    content = $this->getBoxContent($subbox, $value_id, $layoutEntityData);

                    hookSuffix = $subbox['layouts_boxes_content_type'];

                    hookRef = $subbox['layouts_boxes_content_ref'];

                    f  $hookSuffix && is_numeric($hookRef) && $hookSuffix !== 'layout')  

                        content = $this->getHookContent('pre-' . $hookSuffix, $hookRef, $value_id) .
                            $content .
                            $this->getHookContent('post-' . $hookSuffix, $hookRef, $value_id);

                    

                    tabs[$key] = [
title' => $subbox['layouts_boxes_title'],
                        'content' => $content,
;

                

                eturn $this->load->view("pages/layouts/tabbed/{$contentType}", array('tabs' => $tabs, 'tabs_id' => $tabId, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "chart":

                chart = $this->db->get_where('charts', ['charts_id' => $contentRef])->row_array();

                chart['elements'] = $this->get_charts_elements($chart['charts_id']);

                / prendo i dati e se non ci sono allora ritorno un content

                / vuoto

                chart_data = $this->get_chart_data($chart, $value_id);

                f  empty($chart_data[0]['series']) || !is_array($chart_data[0]['series']))  

                    eturn '';

                

                chart_layout = $chart['charts_layout'] ?:  EFAULT_LAYOUT_CHART;

                eturn $this->load->view("pages/layouts/charts/{$chart_layout}", array('chart' => $chart, 'chart_data' => $chart_data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "grid":

                / Prendo la struttura della grid

                grid = $this->get_grid($contentRef);

                /debug($grid);

                / Ci sono problemi se inizializzo una datatable senza colonne!!

                f  empty($grid['grids_fields']))  
                    //debug($grid);

                    eturn sprintf('*** Grid `%s` senza campi ***', $contentRef);

                

                / Controllo i permessi per questa grid

                f  !$this->can_read_entity($grid['grids']['grids_entity_id']))  

                    eturn 'Non disponi dei permessi sufficienti per leggere i dati.';

                

                / Prendo i dati della grid: è inutile prendere i dati in una grid ajax

                grid_data = ['data' => [], 'sub_grid_data' => []];

                f  !in_array($grid['grids']['grids_layout'], ['datatable_ajax', 'datatable_ajax_inline']))  

                    grid_data['data'] = $this->get_grid_data($grid, empty($layoutEntityData) ? $value_id :  'value_id' => $value_id, 'additional_data' => $layoutEntityData]);

                

                *                 * *********************************************************
                 * Se c'è una subentity aggiungo l'eventuale data aggiuntiva
                 */

                sub_grid = null;

                f  $grid['grids']['grids_sub_grid_id'])  

                    sub_grid = $this->get_grid($grid['grids']['grids_sub_grid_id']);

                    relation_field = $this->db->select('fields_name')->from('fields')->where('fields_entity_id', $sub_grid['grids']['grids_entity_id'])->where('fields_ref', $grid['grids']['entity_name'])->get();

                    f  $relation_field->num_rows() < 1)  
                        //debug($grid);

                        ebug("L'entità {$grid['grids']['entity_name']} non è referenziata dall'entità {$sub_grid['grids']['entity_name']}");

                      lse  

                        sub_grid['grid_relation_field'] = $relation_field->row()->fields_name;

                        f  $grid_data['data'])  

                            entName = $grid['grids']['entity_name'];

                            arr_parent_ids = array_map(function  $parentRecord) use  $entName)  

                                eturn $parentRecord["{$entName}_id"];

                            , $grid_data['data']);

                            parent_ids = implode("','", $arr_parent_ids);

                            where = "{$sub_grid['grid_relation_field']} IN ('{$parent_ids}')";

                            grid_data['sub_grid_data'] = $this->get_grid_data($sub_grid, null, $where);

                        

                    

                

                grid_layout = $grid['grids']['grids_layout'] ?:  EFAULT_LAYOUT_GRID;

                eturn $this->load->view("pages/layouts/grids/{$grid_layout}", array('grid' => $grid, 'sub_grid' => $sub_grid, 'grid_data' => $grid_data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "form":

                form_id = $contentRef;

                form = $this->get_form($form_id, $value_id);

                / Controllo i permessi per questa grid

                f  !$this->can_write_entity($form['forms']['forms_entity_id']))  

                    eturn str_repeat('&nbsp;', 3) . 'Non disponi dei permessi sufficienti per modificare i dati.';

                

                eturn $this->load->view("pages/layouts/forms/form_{$form['forms']['forms_layout']}", array('form' => $form, 'ref_id' => $contentRef, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "calendar":

                data = $this->get_calendar($contentRef);

                data['cal_layout'] = $this->db->get_where('calendars', ['calendars_id' => $contentRef])->row_array();

                cal_layout = $data['cal_layout']['calendars_layout'] ?:  EFAULT_LAYOUT_CALENDAR;

                eturn $this->load->view("pages/layouts/calendars/{$cal_layout}", array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "map":

                data = $this->get_map($contentRef);

                data['map_layout'] = $this->db->get_where('maps', ['maps_id' => $contentRef])->row_array();

                map_layout =  $data['map_layout']['maps_layout']) ? $data['map_layout']['maps_layout'] :  EFAULT_LAYOUT_MAP;

                eturn $this->load->view('pages/layouts/maps/' . $map_layout, array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "menu_group":

            ase "menu_button_stripe":

            ase "menu_big_button":

                data = $this->get_menu($contentRef);

                eturn $this->load->view("pages/layouts/menu/{$contentType}", array('data' => $data, 'value_id' => $value_id, 'layout_data_detail' => $layoutEntityData), true);

            ase "view":
                //Verifico se questa custom view fa parte di un modulo. In tal caso, carico la view direttamente dal modulo

                f  $module_view = $this->getModuleViewData($contentRef))  

                    eturn $this->load->module_view($module_view['module_name'] . '/views', $module_view['module_view'], ['value_id' => $value_id, 'layout_data_detail' => $layoutEntityData], true);

                  lse  

                    eturn $this->loadCustomView($contentRef, ['value_id' => $value_id, 'layout_data_detail' => $layoutEntityData], true);

                

            efault:

                f  empty($contentType) && $layoutBoxData['layouts_boxes_content'])  
                    // Contenuto definito

                    b_start();

                    layout_data_detail = $layoutEntityData;    // Ci assicuriamo che questa variabile esista dentro all'eval

                    val(' ?>' . $layoutBoxData['layouts_boxes_content'] . '<?php ');

                    eturn ob_get_clean();

                

                eturn sprintf('<strong style="color:red">TYPE: %s ANCORA NON GESTITO</strong>', $contentType);

        

    

    /Funzione che verifica se la view custom fa parte di un nmodulo o meno...

    rotected function getModuleViewData($contentRef)

    

        f  stripos($contentRef, '{module ') !== false)  

            split = explode('/', $contentRef);

            module_name = str_ireplace('{module ', '', $split[0]);

            module_name = rtrim($module_name, '}');

            nset($split[0]);

            view = implode('/', $split);

            eturn [
module_name' => $module_name,
                'module_view' => $view,
;

          lse  

            eturn false;

        

    
    /* =========================
     * Multilingua
     * ========================= */

    rotected function preloadLanguages()

    

        f  !defined('LANG_ENTITY') or !LANG_ENTITY)  

            eturn;

        

        this->load->helper('text');

        this->_currentLanguage = $this->session->userdata(self::LANG_SESSION_KEY);

        this->_languages = [];

        languages = $this->db->get(LANG_ENTITY)->result_array();

        oreach  $languages as $language)  

            nlang = $this->normalizeLanguageArray($language);

            this->_languages[$nlang['id']] = $nlang;

            f  $nlang['default'] or is_null($this->_defaultLanguage))  

                this->_defaultLanguage = $nlang['id'];

            

        

        f  !$this->_currentLanguage or empty($this->_languages[$this->_currentLanguage]))  

            this->_currentLanguage = $this->_defaultLanguage;

        

        / Forza impostazione della lingua

        this->changeLanguage($this->_currentLanguage);

    

    rotected function normalizeLanguageArray(array $language)

    

        code = $language[LANG_CODE_FIELD];

        f  preg_match('/^[a-z]{2}(-|_)[a-z]{2}$/i', $code))  

            img = $code[3] . $code[4];

          lseif  strlen($code) > 1)  

            img = substr($code, 0, 2);

          lse  

            img = null;

        

        f  $img)  

            flag = base_url_template('template/crm-v2/assets/global/img/flags/' . strtolower($img) . '.png');

        

        eturn [
id' => $language[LANG_ENTITY . '_id'],
            'name' => $language[LANG_NAME_FIELD],
            'file' => convert_accented_characters(strtolower(str_replace(' ', '_', $language[LANG_NAME_FIELD]))),
            'code' => $code,
            'flag' => $flag,
            'default' =>  $language[LANG_DEFAULT_FIELD] === DB_BOOL_TRUE),
;

    

    **
     * Prendi la lingua selezionata
     * 
     * @return array|null
     */
    public function getLanguage()

    

        eturn $this->findLanguage($this->_currentLanguage);

    

    **
     * Prendi la lingua di default
     * 
     * @return array|null
     */
    public function getDefaultLanguage()

    

        oreach  $this->_languages as $lang)  

            f  $lang['default'])  

                eturn $lang;

            

        

        eturn null;

    

    **
     * Cerca un array di lingua per id o per codice
     * 
     * @param int|string $key
     * @return array|null
     */
    public function findLanguage($key)

    

        f  is_numeric($key))  

            eturn isset($this->_languages[$key]) ? $this->_languages[$key] :  ull;

        

        iCode = strtolower(str_replace(['-', '_', ' '], '', $key));

        out = array_filter($this->_languages, function  $lang) use  $iCode)  

            eturn strtolower(str_replace(['-', '_', ' '], '', $lang['code'])) === $iCode;

        );

        / Ritorna il primo o null

        eturn array_shift($out);

    

    ublic function getAllLanguages()

    

        eturn $this->_languages;

    

    ublic function changeLanguage($key)

    

        f  !is_numeric($key))  

            compatible = array_filter($this->_languages, function  $lang) use  $key)  

                eturn $lang['code'] == $key;

            );

            f  !($lang = array_shift($compatible)))  

                eturn false;

            

            key = $lang['id'];

        

        / Language ID

        f  empty($this->_languages[$key]))  

            eturn false;

        

        this->_currentLanguage = $key;

        this->session->set_userdata(self::LANG_SESSION_KEY, $this->_currentLanguage);

        this->crmentity->setLanguages([$this->_currentLanguage, $this->_defaultLanguage]);

        this->loadCiTranslations($this->_languages[$key]['file']);

        eturn true;

    

    rivate function loadCiTranslations($language)

    

        langpath = APPPATH . "language/{$language}";

        langfile = "{$langpath}/{$language}_lang.php";

        f  !is_dir($langpath))  

            kdir($langpath, 0755);

            this->load->helper('file');

            defpath = APPPATH . "language/_default_";

            oreach  get_filenames($defpath) as $file)  

                deffile = "{$defpath}/{$file}";

                newfile = "{$langpath}/{$file}";

                opy($deffile, $newfile);

            

        

        f  !file_exists($langfile))  

            ile_put_contents($langfile, "<?php \n\n");

        

        / Rimuovo le traduzioni caricate in modo da poter ricaricare tutto

        this->lang->language = [];

        this->lang->is_loaded = [];

        this->load->language($language, $language);

    


