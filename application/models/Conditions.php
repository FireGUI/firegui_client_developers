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

        //Preload rules
        $this->_preloadRules();
    }
    public function accessible($what, $ref, $value_id)
    {
        $accessible = true;
        if (!empty($this->_rules[$what][$ref])) {
            $rules = $this->_rules[$what][$ref];

            $dati = $this->buildElementData($what,$ref,$value_id);

            
            foreach ($rules as $rule) {
                debug($rule);
                if (!$this->isApplicableRule($rule['_rule'], $dati)) {
                    $accessible = false;
                    break;
                }
            }
        }
        
        

        return $accessible;
    }
    private function buildElementData($what,$ref,$value_id) {
        $dati = [
            '_current_date' => date('Y-m-d'),
            '_current_time' => date('H:i:s'),
        ];
        foreach ($this->auth->getSessionUserdata() as $session_field => $val) {
            $dati["session_{$session_field}"] = $val;
        }

        return $dati;
    }
    private function _preloadRules()
    {
       if (empty($this->_rules)) {
        $cache_key = 'conditions_rules';
        if (!($this->_rule = $this->cache->get($cache_key))) {
            $rules = $this->db->where('conditions_json_rules IS NOT NULL', null, false)->get('_conditions')->result_array();
            foreach ($rules as $rule) {
                if (empty($this->_rules[$rule['conditions_what']][$rule['conditions_ref']])) {
                     $this->_rules[$rule['conditions_what']][$rule['conditions_ref']] = [];
                }
                $rule['_rule'] = json_decode($rule['conditions_json_rules'], true);
                 $this->_rules[$rule['conditions_what']][$rule['conditions_ref']][] = $rule;
            }
            if ($this->apilib->isCacheEnabled()) {
                $this->cache->save($cache_key, $this->_rule, self::CACHE_TIME);
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
    private function isApplicableRule(array $rule, array $dati)
    {
        //debug($rule, true);
        $contains_rules = (isset($rule['condition']) && isset($rule['rules']));
        $is_rule_definition = (isset($rule['id']) && isset($rule['type']) && isset($rule['value']) && isset($rule['operator']));

        if ($contains_rules) {
            /*
             * Abbiamo un contenitore di regole
             */
            $is_and = strtoupper($rule['condition']) === 'AND';
            foreach ($rule['rules'] as $sub_rule) {
                $is_applicable = $this->isApplicableRule($sub_rule, $dati);

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
                case 'almeno_un_bambino_di_eta':
                case 'almeno_2_bambini_di_eta':
                case 'almeno_3_bambini_di_eta':
                    return $this->doSpecialOperation($rule['id'], $rule['operator'], $rule['value'], $room);
                    break;
                default:
                    if (!array_key_exists($rule['id'], $this->rules_mapping)) {
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
            return false;   // throw exception [?]
        }
    }
    public function doSpecialOperation($id, $ruleOperator, $ruleValue, $room)
    {
        switch ($id) {
            case 'almeno_un_bambino_di_eta':
                //debug($room, true);
                foreach ($room['_ospiti']['bambini'] as $bambino) {
                    $eta = $bambino['eta'];
                    if ($this->doOperation($eta, $ruleOperator, $ruleValue)) {
                        return true;
                    }
                }
                return false;
                break;
            case 'almeno_2_bambini_di_eta':
                $count = 0;
                foreach ($room['_ospiti']['bambini'] as $bambino) {
                    $eta = $bambino['eta'];
                    if ($this->doOperation($eta, $ruleOperator, $ruleValue)) {
                        $count++;
                    }
                }
                if ($count >= 2) {
                    return true;
                } else {
                    return false;
                }
                
                break;
            case 'almeno_3_bambini_di_eta':
                $count = 0;
                foreach ($room['_ospiti']['bambini'] as $bambino) {
                    $eta = $bambino['eta'];
                    if ($this->doOperation($eta, $ruleOperator, $ruleValue)) {
                        $count++;
                    }
                }
                if ($count >= 3) {
                    return true;
                } else {
                    return false;
                }
                
                break;
            default:
                debug("Controllo '{$id}' non riconosciuto!");
                break;
        }
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
                        7 => 'domenica'
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
