<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Conditions extends CI_Model
{
    private $_rules = [];
    const CACHE_TIME = 3600;

    private $rules_mapping = [
        '_date' => '_current_date',
        // 'numero_adulti' => '_numero_adulti',
        // 'numero_bambini' => '_numero_bambini',
        // 'numero_persone' => '_numero_persone',
        // 'numero_notti' => '_numero_notti',
        // 'data_checkin' => '_checkin',
        // 'data_checkout' => '_checkout',
        // 'data' => '_current_date',
    ];

    public function __construct()
    {

        parent::__construct();
        $this->load->driver('Cache/drivers/MY_Cache_file', null, 'mycache');

        //Preload rules
        $this->_preloadRules();
    }
    public function accessible($what, $ref, $value_id = null, $_dati = null)
    {
        $accessible = true;
        if (!empty($this->_rules[$what][$ref])) {
            $rules = $this->_rules[$what][$ref];

            $dati = $this->buildElementData($what, $ref, $value_id, $_dati);

            //debug($dati);
            foreach ($rules as $rule) {
                //debug($rule);
                $applicable = $this->isApplicableRule($rule['_rule'], $dati, $value_id);
                switch ($rule['conditions_action']) {
                    case 1: //Allow
                        $accessible = $applicable;
                        break;
                    case 2: //Deny
                        $accessible = !$applicable;
                        break;
                    case 3: //Allow
                        redirect();
                        break;
                    default:
                        break;
                }

                if (!$accessible) { //Mi fermo alla prima regola deny o non applicabile in caso di allow
                    break;
                }

            }
        }

        return $accessible;
    }
    private function buildElementData($what, $ref, $value_id, $_dati = null)
    {
        $dati = [
            '_current_date' => date('Y-m-d'),
            '_current_time' => date('H:i:s'),
            '_current_page_id' => $this->layout->getCurrentLayout(),
            '_current_page_identifier' => $this->layout->getCurrentLayoutIdentifier(),
            '_nested_layouts_id' => $this->layout->getLoadedLayoutsIds(), //TODO: array contentente tutti i layout/sublayout attuaklmente visualizzati
        ];
        if ($_dati !== null) {
            $dati = array_merge($dati, $_dati);
        }
        foreach ($this->auth->getSessionUserdata() as $session_field => $val) {
            $dati["session_{$session_field}"] = $val;
        }

        switch ($what) {
            case 'grids_actions':
                if ($_dati !== null) {
                    $entity = $this->db->join('grids', 'grids_id = grids_actions_grids_id', 'LEFT')->join('entity', 'entity_id = grids_entity_id', 'LEFT')->get_where('grids_actions', ['grids_actions_id' => $ref])->row()->entity_name;
                    $dati = array_merge($dati, $this->apilib->view($entity, $value_id));
                }

                break;
            case 'forms_fields':
                if ($_dati !== null && $value_id) {
                    debug("TODO: extract data from entity related to forms_fields with value_id");
                }

                break;
            case 'layouts':
            case 'layouts_boxes':
            case 'menu':
            case 'grids_fields':
                break;
            default:
                debug("Element '$what' not recognized for conditions");
                break;
        }

        return $dati;
    }
    private function _preloadRules()
    {
        if (empty($this->_rules)) {
            $cache_key = 'database_schema/conditions_rules';
            if (!($this->_rule = $this->mycache->get($cache_key))) {
                $rules = $this->db->where('conditions_json_rules IS NOT NULL', null, false)->get('_conditions')->result_array();
                foreach ($rules as $rule) {
                    if (empty($this->_rules[$rule['conditions_what']][$rule['conditions_ref']])) {
                        $this->_rules[$rule['conditions_what']][$rule['conditions_ref']] = [];
                    }
                    $rule['_rule'] = json_decode($rule['conditions_json_rules'], true);
                    $this->_rules[$rule['conditions_what']][$rule['conditions_ref']][] = $rule;
                }
                if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('database_schema')) {
                    $this->mycache->save($cache_key, $this->_rule, self::CACHE_TIME);
                }
            }

        }

        //debug($this->_rules,true);
    }

    /**
     * Controlla se questa regola è applicabile
     *
     * @param array $rule
     * @param array $prenoData
     * @return boolean
     */
    private function isApplicableRule(array $rule, array $dati, $value_id = null)
    {
        //debug($rule, true);
        $contains_rules = (isset($rule['condition']) && isset($rule['rules']));
//$is_rule_definition = (isset($rule['id']) && isset($rule['type']) && isset($rule['value']) && isset($rule['operator']));
        $is_rule_definition = (isset($rule['id']) && isset($rule['operator']) && isset($rule['type']) && (isset($rule['value']) || in_array($rule['operator'], ['is_null'])));

        if ($contains_rules) {
            /*
             * Abbiamo un contenitore di regole
             */
            $is_and = strtoupper($rule['condition']) === 'AND';
            foreach ($rule['rules'] as $sub_rule) {
                $is_applicable = $this->isApplicableRule($sub_rule, $dati, $value_id);

                // Se devono essere tutte vere e la mia regola corrente è falsa,
                // allora non proseguo e ritorno false
                // Al contrario, se ne basta una vera e la trovo ora, allora
                // posso interrompere l'algoritmo proseguo e ritornare true
                // ===
                // Le precedenti affermazioni si traducono in
                // if     (IS_AND && !IS_APPLICABLE) return false;
                // elseif (!IS_AND && IS_APPLICABLE) return true;
                // che ottimizzato diventa...
                if ($is_and != $is_applicable) {
                    return $is_applicable;
                }
            }

            // Se le ho ciclate tutte, allora significa che: se la condizione
            // era AND, allora sono tutte vere (quindi ritorno true), altrimenti
            // se la condizione era OR, allora sono tutte false (quindi ritorno
            // false)
            return $is_and;
        } elseif ($is_rule_definition) {
            /*
             * Abbiamo una definizione di regola
             */
            //Creo uno switch per le condizioni speciali, ovvero che non sono semplici operatori di confronto ma serve un codice ad hoc per questa verifica
            switch ($rule['id']) {
                case '_query':
                     return $this->doQueryOperation($rule['id'], $rule['operator'], $rule['value'], $value_id);
                    break;
                case 'special2':

                    return $this->doFooSpecialOperation($rule['id'], $rule['operator'], $rule['value']);
                    break;
                case '_module_installed':

                    return $this->doModuleInstalledOperation($rule['id'], $rule['operator'], $rule['value']);
                    break;
                case '_current_page_id':

                    return $this->doCurrentPageIdOperation($dati, $rule['id'], $rule['operator'], $rule['value']);
                    break;
                case '_current_page_identifier':
                    return $this->doCurrentPageIdentifierOperation($dati, $rule['id'], $rule['operator'], $rule['value']);
                    break;
                case '_current_page_id_included':

                    return $this->doCurrentPageIdIncludedOperation($dati, $rule['id'], $rule['operator'], $rule['value']);
                    break;
                case '_current_page_identifier_included':
                    return $this->doCurrentPageIdentifierIncludedOperation($dati, $rule['id'], $rule['operator'], $rule['value']);
                    break;
                case '_is_maintenance':

                    $return = $this->doOperation($rule['id'], $rule['operator'], is_maintenance());

                    return $return;
                    break;
                default:
                    
                    if (!array_key_exists($rule['id'], $this->rules_mapping)) {
                        // debug($rule);
                        // debug($dati);
                        return $this->doOperation($dati[$rule['id']], $rule['operator'], $rule['value']);
                    } else {
                        return $this->doOperation($dati[$this->rules_mapping[$rule['id']]], $rule['operator'], $rule['value']);
                    }

                    break;
            }
        } else {
            /*
             * Situazione anomala
             */
            return false; // throw exception [?]
        }
    }
    public function doFooSpecialOperation($id, $ruleOperator, $ruleValue, $room)
    {
        switch ($id) {
            case 'foo_special':
                //TODO
                return false;
                break;

            default:
                debug("Controllo '{$id}' non riconosciuto!");
                break;
        }
    }
    public function doQueryOperation($id, $ruleOperator, $query, $value_id = null)
    {
        $replaces['value_id'] = $value_id;
        $query = str_replace_placeholders($query, $replaces);
        $query = $this->datab->replace_superglobal_data($query);
        switch ($ruleOperator) {

            case 'num rows >= 1':
                return $this->db->query($query)->num_rows() >= 1;

            case 'num rows = 0':
                
                return $this->db->query($query)->num_rows() == 0;

            default:
                debug("Rule operator '$ruleOperator' not recognized!");
                break;
        }
        return false;

    }
    

    public function doModuleInstalledOperation($id, $ruleOperator, $ruleValue)
    {

        switch ($ruleOperator) {

            case 'equal':
                return $this->datab->module_installed($ruleValue);

            case 'not_equal':
                return !$this->datab->module_installed($ruleValue);

            default:
                debug("Rule operator '$ruleOperator' not recognized!");
                break;
        }
        return false;

    }

    public function doCurrentPageIdOperation($dati, $rule_id, $ruleOperator, $ruleValue)
    {

        //TODO: in realtà non va bene perchè il current layout della grid è corretto, ma quando parte l'ajax il current layout è null (essendo una get_ajax)... le ajax dovrebbero portarsi dirtro / passare al controller l'informazione del layout da cui sono state invocate
        //TODO: return true also if ruleVale match/doesn't match/... with _nested_layouts_id. Probabily needs another specific rule id in builder...
        $value_to_validate = $dati[$rule_id];
        return $this->doOperation($value_to_validate, $ruleOperator, $ruleValue);
    }
    public function doCurrentPageIdentifierOperation($dati, $rule_id, $ruleOperator, $ruleValue)
    {
        //TODO: return true also if ruleVale match/doesn't match/... with _nested_layouts_id. Probabily needs another specific rule id in builder...

        $value_to_validate = $dati[$rule_id];
        return $this->doOperation($value_to_validate, $ruleOperator, $ruleValue);
    }
    public function doCurrentPageIdIncludedOperation($dati, $rule_id, $ruleOperator, $ruleValue)
    {
        $layouts = $dati['_nested_layouts_id'];
        foreach ($layouts as $layout_id) {
            if ($this->doOperation($layout_id, $ruleOperator, $ruleValue)) {
                return true;
                break;
            }
        }
        return false;
    }
    public function doCurrentPageIdentifierIncludedOperation($dati, $rule_id, $ruleOperator, $ruleValue)
    {
        $rule_value_id = $this->layout->getLayoutByIdentifier($ruleValue);
        //debug($rule_value_id, true);
        $layouts = $dati['_nested_layouts_id'];
        foreach ($layouts as $layout_id) {
            if ($this->doOperation($layout_id, $ruleOperator, $rule_value_id)) {
                return true;
                break;
            }
        }
        return false;

    }

    /**
     * Esegue un'operazione booleana avendo i due operandi e il codice operatore
     *
     * @param mixed $bookingValue
     * @param string $ruleOperator
     * @param mixed $ruleValue
     * @return bool
     */
    private function doOperation($value_to_validate, $ruleOperator, $ruleValue)
    {

        // debug($value_to_validate);
        // debug($ruleOperator);
        // debug($ruleValue);

        switch ($ruleOperator) {
            case 'greater':
                return $value_to_validate > $ruleValue;

            case 'greater_or_equal':
                return $value_to_validate >= $ruleValue;

            case 'equal':
                return is_array($ruleValue) ? in_array($value_to_validate, $ruleValue) : ($value_to_validate == $ruleValue);

            case 'not_equal':
                return is_array($ruleValue) ? !in_array($value_to_validate, $ruleValue) : ($value_to_validate != $ruleValue);

            case 'less':
                return $value_to_validate < $ruleValue;

            case 'less_or_equal':
                return $value_to_validate <= $ruleValue;

            case 'between':
                return ($ruleValue[0] <= $value_to_validate && $value_to_validate <= $ruleValue[1]);

            case 'not_between':
                return !($ruleValue[0] <= $value_to_validate && $value_to_validate <= $ruleValue[1]);

            case 'in':
                return is_array($ruleValue) ? in_array($value_to_validate, $ruleValue) : ($value_to_validate == $ruleValue);
            case 'is_null':
                //debug($value_to_validate);
                return $value_to_validate == '';
            default:
                debug("Rule operator '$ruleOperator' not recognized!");
                break;
        }

        return false;
    }

    //Ritorna una stringa che descrive lo sconto
    public function rulesHumanReadableCondition($rule_db, $rules_json = null)
    {
        //debug($rule_db);
        if ($rules_json === null) {
            $rules_json = $rule_db['regole_regola_json'];
        }
        if (@json_decode($rules_json)) {
            $rules_json = json_decode($rules_json);
        }

        //debug($rules_json);

        $str = '';
        //Se ho regole da elaborare
        if (!empty($rules_json->rules) && !empty($rules_json->condition)) {
            $rules = [];
            foreach ($rules_json->rules as $rule) {
                $rules[] = $this->rulesHumanReadableCondition($rule_db, $rule);
            }
            return implode($rules_json->condition == 'AND' ? ' e ' : ' o ', $rules);
        } else {
            //$rules_json->value = implode(', ',(array)($rules_json->value));
            switch ($rules_json->field) {
                case 'numero_notti':
                    $label = 'il numero di notti';
                    break;
                case 'camera':
                    $label = 'la camera';
                    //Visto che nel value mi arrivano gli id immobili separati da virgola, recupero i codici immobili e li implodo
                    $camere = $this->db->where_in('camere_id', (array) ($rules_json->value))->get('camere')->result_array();
                    $camere_labels = [];
                    foreach ($camere as $camera) {
                        $nomi = json_decode($camera['camere_nome'], true);

                        $camere_labels[] = $nomi[$this->apilib->getLanguage()];
                    }
                    $rules_json->value = implode(',', $camere_labels);
                    break;
                case 'periodo':
                case 'periodo_end':
                    $label = 'il giorno ' . ($rules_json->field == 'periodo' ? 'd\'arrivo' : 'di partenza');
                    $rules_json->value = implode(' e ', $rules_json->value);
                    break;
                case 'giorno_arrivo':
                case 'giorno_partenza':
                    $label = 'il giorno settimanale ' . ($rules_json->field == 'giorno_arrivo' ? 'd\'arrivo' : 'di partenza');
                    $week_days = array(
                        1 => 'lunedì',
                        2 => 'martedì',
                        3 => 'mercoledì',
                        4 => 'giovedì',
                        5 => 'venerdì',
                        6 => 'sabato',
                        7 => 'domenica',
                    );
                    $rules_json->value = $week_days[$rules_json->value];
                    break;
                default:
                    $label = $rules_json->field;
                    break;
            }
            switch ($rules_json->operator) {
                case 'greater':
                    $operator = 'maggiore di';
                    break;
                case 'greater_or_equal':
                    $operator = 'maggiore o uguale a';
                    break;
                case 'less':
                    $operator = 'minore di';
                    break;
                case 'less_or_equal':
                    $operator = 'minore o uguale di';
                    break;
                case 'equal':
                    $operator = 'uguale a';
                    break;
                case 'not_equal':
                    $operator = 'diverso da';
                    break;
                case 'between':
                    $operator = 'compreso tra';
                    break;
                case 'not_between':
                    $operator = 'non compreso tra';
                    break;
                default:
                    $operator = $rules_json->operator;
                    break;
            }

            $val = (is_array($rules_json->value)) ? implode(' e ', $rules_json->value) : $rules_json->value;
            return "se $label è $operator $val";
        }
    }

    public function rulesHumanReadableAction($rule_db)
    {
        return "allora " . strtolower($rule_db['regole_azione_value']) . (($rule_db['regole_parametro_azione'] ? " di {$rule_db['regole_parametro_azione']}" : ""));
    }

    public function rulesHumanReadable($rule_db)
    {
        //$rule_db = $this->apilib->view('tur_scontistiche', 1);
        return $this->rulesHumanReadableCondition($rule_db) . ', ' . $this->rulesHumanReadableAction($rule_db);
    }
}
