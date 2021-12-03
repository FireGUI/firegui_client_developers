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
        foreach ($chart['elements'] as $element) {
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
                $data['data'] = $this->db->query($this->datab->replace_superglobal_data(str_replace('{value_id}', $value_id, $element['charts_elements_full_query'])))->result_array();
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
                unset($row['x'], $row['y']);    // Rimuovo x e y per vedere se ho altri dati

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

    public function process_data($chart, $data)
    {
        switch ($chart['charts_x_datatype']) {
            case 'dates':

                break;
            default:
                throw new Exception("Charts x datatype '{$chart['charts_x_datatype']}' not recognized!");
                break;
        }
        debug($chart);
        debug($data, true);
    }
}
