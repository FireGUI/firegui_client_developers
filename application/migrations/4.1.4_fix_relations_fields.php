<?php
echo_log('debug', 'Started migration 4.1.4...<br/>');
echo_log('debug', 'Fix relations fields<br/>');

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

// I loop through the relations and check if the table exists in the database, ensuring that both field_1 and field_2 are present.
// Next, I check the "fields" table to see if a field with field_name already exists.
// If it does, I skip to the next iteration; otherwise, I insert the data into the "fields" table.
// I only insert the essential data: field_name, entity_id (which corresponds to the "entity" table, where entity_name represents the relation name), field_type, and field_size (in this case, BIGINT 20).
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

            if (empty($field1Exists)) {
                $this->db->insert('fields', [
                    'fields_entity_id' => $relation['entity_id'],
                    'fields_name' => $field1,
                    'fields_type' => 'BIGINT',
                    'fields_size' => '20',
                ]);
                
                echo_log('debug', 'Inserted field ' . $field1 . ' for relation ' . $relation['relations_name'] . '<br/>');
            } else {
                echo_log('debug', 'Field ' . $field1 . ' already exists for relation ' . $relation['relations_name'] . '<br/>');
            }

            if (empty($field2Exists)) {
                $this->db->insert('fields', [
                    'fields_entity_id' => $relation['entity_id'],
                    'fields_name' => $field2,
                    'fields_type' => 'BIGINT',
                    'fields_size' => '20',
                ]);
                
                echo_log('debug', 'Inserted field ' . $field2 . ' for relation ' . $relation['relations_name'] . '<br/>');
            } else {
                echo_log('debug', 'Field ' . $field2 . ' already exists for relation ' . $relation['relations_name'] . '<br/>');
            }
            
            echo_log('debug', 'Processed relation ' . $relation['relations_name'] . '<br/><br/>');
        } else {
            echo_log('debug', 'Fields not found for relation ' . $relation['relations_name'] . '<br/>');
        }
    } else {
        echo_log('debug', 'Table not found for relation ' . $relation['relations_name'] . '<br/>');
    }
}

echo_log('debug', 'Ended migration 4.1.4...');
