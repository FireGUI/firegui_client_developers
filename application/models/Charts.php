<?php

class Charts extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
    }

    public function get_charts_elements($charts_id)
    {
        $elements = $this->db->where('charts_elements_charts_id', $charts_id)->get('charts_elements')->result_array();
        return $elements;
    }

    public function get_chart_data($chart, $value_id = null)
    {

        $all_data = [];

        // Ciclo gli elementi qualora ne abbia + di uno
        foreach ($chart['elements'] as $key_element => $element) {
            $data = [];

            if (empty($element['charts_elements_mode']) or $element['charts_elements_mode'] == 1) {
                $entity = $this->datab->get_entity($element['charts_elements_entity_id']);
                $group_by = $element['charts_elements_groupby'];

                // Gli costruisco il Where con il mega-metodo generico
                $where = $this->datab->generate_where("charts_elements", $element['charts_elements_id'], $value_id);

                if ($where) {
                    $where = "WHERE {$where}";
                }

                // Mi costruisco eventuali join
                $join = "";
                $alreadyJoined = array($entity['entity_name']);
                foreach ($this->datab->get_entity_fields($element['charts_elements_entity_id']) as $_field) {
                    if ($_field['fields_ref'] && !in_array($_field['fields_ref'], $alreadyJoined)) {
                        $entity_ref = $this->datab->get_entity_by_name($_field['fields_ref']);
                        $join .= "LEFT JOIN {$_field['fields_ref']} ON ({$_field['fields_ref']}.{$_field['fields_ref']}_id = {$entity['entity_name']}.{$_field['fields_name']}) ";
                        $alreadyJoined[] = $_field['fields_ref'];
                    }
                }

                $field = $this->datab->get_field($element['charts_elements_fields_id']);

                if (!$field) {
                    log_message('error', 'Field not found: ' . $element['charts_elements_fields_id'] . ' in chart ' . $chart['charts_id']);
                    unset($chart['elements'][$key_element]);
                    continue;
                }
                if ($group_by) {
                    $query_group_by = str_replace('#', ',', $group_by);
                    $gr_by = "GROUP BY " . $query_group_by;
                } else {
                    $query_group_by = "";
                    $gr_by = "";
                }

                //TODO: joinare tutte le tabelle fino al 3° livello

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
                $where = $this->datab->generate_where("charts_elements", $element['charts_elements_id'], $value_id);
                $query = str_replace('{value_id}', $value_id, $element['charts_elements_full_query']);
                if (stripos($query, ' where ')) {
                    $query = str_ireplace('{where}', ' AND ' . $where, $query);
                } else {
                    $query = str_ireplace('{where}', ' WHERE ' . $where, $query);
                }
                $query = $this->datab->replace_superglobal_data($query);

                //debug($query);
                $data['data'] = $this->db->query($query)->result_array();
            }

            // Precalcolo tutte le x, perché ogni serie deve avere lo stesso numero di valori
            // e questo deve coincidere col numero di x
            $data['x'] = array_unique(array_map(function ($row) {
                return $row['x'];
            }, $data['data']));

            if (!empty($data['x']) && isset($group_by)) {
                // Trova chi è il campo messo come x (il campo x è l'ultimo dopo la virgola-cancelletto nella stringa group by)
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
                        $xfield = $this->datab->get_field_by_name($field_name_exploso);
                    }
                }

                // Se ho un ref devo ricalcolare tutte le etichette perché vorrebbe dire che il campo
                // contiene solo una lista di inutili id
                if (!empty($xfield['fields_ref'])) {
                    $preview = $this->datab->get_entity_preview_by_name($xfield['fields_ref'], $xfield['fields_ref'] . "_id IN ('" . implode("','", array_filter($data['x'])) . "')");

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
                unset($row['x'], $row['y']); // Rimuovo x e y per vedere se ho altri dati

                if (empty($row)) {
                    // Non ho altri dati nella riga oltre a una x e una y => una colonna nella tabella,
                    // il nome può anche essere vuoto
                    $name = $element['charts_elements_label'];
                } else {
                    // Ho altri dati - l'implosione dei campi restanti mi rappresenterà la label delle varie colonnine
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
    public function splitBars($data)
    {
        $splitted = [];

        foreach ($data as $key => $element) {
            foreach ($element['data'] as $key2 => $xy) {
                if (array_key_exists('x2', $xy)) {
                    if (!array_key_exists($xy['x2'], $splitted)) {
                        $_element = $element;
                        unset($_element['data']);
                        $_element['element']['charts_elements_label'] = $xy['x2'];
                        //$_element['element']['charts_elements_label2'] = $xy['x2'];

                        $splitted[$xy['x2']] = $_element;
                        $splitted[$xy['x2']]['data'] = [$xy];
                    } else {
                        $splitted[$xy['x2']]['data'][] = $xy;
                        //break;
                    }
                } else {
                    return $data;
                }
            }

        }
        // debug($splitted);
        // debug($data);
        return $splitted;
    }
    public function process_data($chart, $data)
    {
        $data = $this->splitBars($data);

        switch ($chart['charts_x_datatype']) {
            case 'dates':
                $data = $this->processDates($chart, $data);
                break;
            case 'month':
                //debug($data,true);
                $data = $this->processMonths($chart, $data);
                //debug($data,true);
                $data = $this->addMonthsCategories($data);
                $data = $this->addMonthsSeries($data);

                break;
            default:
                //throw new Exception("Charts x datatype '{$chart['charts_x_datatype']}' not recognized!");
                break;
        }
        foreach ($data as $key => $subdata) {
            uksort($data[$key]['data'], array('Charts', 'sortDataByKey'));

        }
        $data = $this->processX($data);
        $data = $this->processSeries($data);
        //debug($data);
        return $data;
    }
    private function addMonthsCategories($data)
    {
        //debug($data, true);
        $categories = [];
        foreach ($data as $key => $element) {
            foreach ($element['data'] as $Ym => $xy) {
                $fisrt_day_of_month = new DateTime($Ym . '-01');
                if (!in_array($fisrt_day_of_month->format('M Y'), $categories)) {
                    $categories[] = $fisrt_day_of_month->format('M Y');
                }
            }

        }
        foreach ($data as $key => $element) {
            $data[$key]['categories'] = $categories;

        }

        return $data;
    }
    private function addMonthsSeries($data)
    {
        //debug($data, true);
        $categories = [];
        foreach ($data as $key => $element) {
            foreach ($element['data'] as $Ym => $xy) {
                if (!in_array($Ym, $categories)) {
                    $categories[] = $Ym;
                }
            }

        }
        foreach ($data as $key => $element) {

            foreach ($categories as $cat) {
                if (!array_key_exists($cat, $data[$key]['data'])) {
                    //debug($element, true);
                    $data[$key]['data'][$cat] = [
                        'x' => $cat,
                        'y' => 0,
                    ];
                }
            }
        }

        return $data;
    }
    private function processX($data)
    {
        //debug($data);
        foreach ($data as $key => $element) {

            $element['x'] = array_keys($element['data']);
            $data[$key] = $element;
        }
        return $data;
    }
    private function processSeries($data)
    {

        foreach ($data as $key => $element) {
            $element['series'] = [];

            foreach ($element['data'] as $key2 => $xy) {

                $element['series'][$xy['x']] = $xy['y'];
            }
            uksort($element['series'], array('Charts', 'sortDataByKey'));
            //debug($element);
            $data[$key] = $element;
            //$data['series'][] = $element['series'];
        }

        return $data;
    }
    private static function sortByDate($a, $b)
    {

        return ($a['x'] < $b['x']) ? -1 : 1;
    }
    private static function sortDataByKey($a, $b)
    {
        $a = str_replace('-', '', $a);
        $b = str_replace('-', '', $b);

        return ($a < $b) ? -1 : 1;
    }
    private function fillDateColumns($data)
    {

        $dates = $dataFilled = [];
        foreach ($data as $key => $xy) {
            $dates[$xy['x']] = $xy;
        }
        if ($dates) {
            $min_date = new DateTime(min(array_keys($dates)));
            $max_date = new DateTime(max(array_keys($dates)));
            $startDate = $min_date;
            while ($startDate <= $max_date) {
                $formattedStart = $startDate->format('Y-m-d');
                $dataFilled[$formattedStart] = (empty($dates[$formattedStart])) ? ['x' => $formattedStart, 'y' => 0] : ['x' => $formattedStart, 'y' => $dates[$formattedStart]['y']];
                $startDate->modify('+1 day');
            }
        }
        //debug($dataFilled, true);
        return $dataFilled;
    }
    private function fillMonthColumns($data)
    {

        $dates = $dataFilled = [];

        foreach ($data as $key => $xy) {

            $dates[$xy['x']] = $xy;
        }
        if ($dates) {

            $min_date = new DateTime(min(array_keys($dates)));
            $max_date = new DateTime(max(array_keys($dates)));

            $startDate = $min_date;
            while ($startDate <= $max_date) {
                $formattedStart = $startDate->format('Y-m');
                $dataFilled[$formattedStart] = (empty($dates[$formattedStart])) ? ['x' => $formattedStart, 'y' => 0] : ['x' => $formattedStart, 'y' => $dates[$formattedStart]['y']];
                $startDate->modify('+1 month');
            }
        }
        //debug($dataFilled, true);
        return $dataFilled;
        //$min_date =
    }
    public function processDates($chart, $data)
    {
        //debug($chart, true);
        foreach ($data as $key => $element) {
            //debug($element);
            foreach ($element['data'] as $key2 => $xy) {
                //debug($xy, true);
                if (empty($xy['x'])) {
                    unset($element['data'][$key2]);
                } else {
                    $element['data'][$key2]['x'] = dateFormat($xy['x'], 'Y-m-d');
                }
            }

            usort($element['data'], array('Charts', 'sortByDate'));

            if ($chart['charts_fill_columns'] == DB_BOOL_TRUE) {
                $element['data'] = $this->fillDateColumns($element['data']);
            }

            $data[$key] = $element;
        }
        return $data;
    }
    public function processMonths($chart, $data)
    {

        foreach ($data as $key => $element) {
            //debug($element);
            foreach ($element['data'] as $key2 => $xy) {
                //debug($xy, true);
                if (empty($xy['x'])) {
                    unset($element['data'][$key2]);
                } else {
                    $element['data'][$key2]['x'] = dateFormat($xy['x'], 'Y-m');
                }
            }

            usort($element['data'], array('Charts', 'sortByDate'));

            if ($chart['charts_fill_columns'] == DB_BOOL_TRUE) {
                $element['data'] = $this->fillMonthColumns($element['data']);
            }

            $data[$key] = $element;
        }
        //debug($data, true);
        return $data;
    }
}
