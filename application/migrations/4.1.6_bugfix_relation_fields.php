<?php
echo_log('debug', 'Started migration 4.1.6_bugfix_relation_fields...<br/>');

$relations = $this->db
    ->join('entity', 'entity.entity_name = relations.relations_name', 'LEFT')
    ->get('relations')->result_array();

/** TABELLA relations
 * Column    Type
 * relations_id    bigint(20) unsigned Auto Increment
 * relations_name    varchar(250) NULL
 * relations_table_1    varchar(250) NULL
 * relations_table_2    varchar(250) NULL
 * relations_field_1    varchar(250) NULL
 * relations_field_2    varchar(250) NULL
 * relations_type    varchar(250) NULL
 * relations_module    varchar(250) NULL
 */

/** TABELLA fields
 * Column    Type
 * fields_id    bigint(20) unsigned Auto Increment
 * fields_entity_id    bigint(20) unsigned
 * fields_default    varchar(250) NULL
 * fields_name    varchar(250) NULL
 * fields_type    varchar(250) NULL
 * fields_size    varchar(12) NULL
 * fields_required    int(11) NULL [0]
 * fields_preview    tinyint(1) NULL [0]
 * fields_visible    tinyint(1) NULL [1]
 * fields_ref    varchar(100) NULL
 * fields_ref_auto_left_join    tinyint(1) NULL [1]
 * fields_ref_auto_right_join    tinyint(1) NULL [1]
 * fields_source    varchar(100) NULL
 * fields_select_where    text NULL
 * fields_multilingual    tinyint(1) NULL [0]
 * fields_searchable    tinyint(1) NULL [1]
 * fields_xssclean    tinyint(1) [1]
 * fields_preview_base    varchar(250) NULL
 * fields_preview_custom    varchar(250) NULL
 * fields_additional_data    text NULL
 */

/** TABELLA fields_draw
 * Column    Type
 * fields_draw_id    bigint(20) unsigned Auto Increment
 * fields_draw_fields_id    bigint(10) unsigned NULL
 * fields_draw_label    varchar(250) NULL
 * fields_draw_help_text    text NULL
 * fields_draw_onclick    text NULL
 * fields_draw_html_type    varchar(250) NULL
 * fields_draw_placeholder    varchar(250) NULL
 * fields_draw_css_extra    varchar(250) NULL
 * fields_draw_display_none    tinyint(1) NULL [0]
 * fields_draw_enabled    tinyint(1) NULL [1]
 * fields_draw_attr    varchar(250) NULL
 */

foreach ($relations as $relation) {
    if (empty($relation['entity_id'])) {
        echo_log('debug', 'Entity not found for relation ' . $relation['relations_name'] . '<br/>');
        continue;
    }
    
    $field1 = $relation['relations_field_1'];
    $field2 = $relation['relations_field_2'];
    
    if ($this->db->table_exists($relation['relations_name'])) {
        if ($this->db->field_exists($field1, $relation['relations_name']) && $this->db->field_exists($field2, $relation['relations_name'])) {
            $field1Exists = $this->db->get_where('fields', ['fields_name' => $field1, 'fields_entity_id' => $relation['entity_id']])->row_array();
            $field2Exists = $this->db->get_where('fields', ['fields_name' => $field2, 'fields_entity_id' => $relation['entity_id']])->row_array();
            
            // Process field1
            if (!empty($field1Exists)) {
                $this->db->delete('fields', ['fields_id' => $field1Exists['fields_id']]);
                echo_log('debug', 'Deleted existing field ' . $field1 . ' for relation ' . $relation['relations_name'] . '<br/>');
            }
            
            // Process field2
            if (!empty($field2Exists)) {
                $this->db->delete('fields', ['fields_id' => $field2Exists['fields_id']]);
                echo_log('debug', 'Deleted existing field ' . $field2 . ' for relation ' . $relation['relations_name'] . '<br/>');
            }
            
            echo_log('debug', 'Processed relation ' . $relation['relations_name'] . '<br/><br/>');
        } else {
            echo_log('debug', 'Fields not found for relation ' . $relation['relations_name'] . '<br/>');
        }
    } else {
        echo_log('debug', 'Table not found for relation ' . $relation['relations_name'] . '<br/>');
    }
}

echo_log('debug', 'Ended migration 4.1.6_bugfix_relation_fields...');
