<?php
log_message('debug', 'Started migration 2.1.9...');
$login_entity = LOGIN_ENTITY;
$entity_id = $this->db->get_where('entity', ['entity_name' => $login_entity])->row()->entity_id;

log_message('debug', 'Inserting webauthn_data field');
//Add webauthn_data field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => $login_entity . '_webauthn_data',
    'fields_type' => 'LONGTEXT',
    'fields_required' => DB_BOOL_FALSE,
    'fields_preview' => DB_BOOL_FALSE,
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);
$field_id = $this->db->insert_id();
log_message('debug', 'Inserting webauthn_data field draw');
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Webauthn data',
    'fields_draw_html_type' => 'textarea',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',

]);
log_message('debug', 'Alter table ' . $login_entity);
$this->db->query("ALTER TABLE $login_entity ADD COLUMN {$login_entity}_webauthn_data LONGTEXT;");
log_message('debug', 'Ended migration 2.1.9...');
