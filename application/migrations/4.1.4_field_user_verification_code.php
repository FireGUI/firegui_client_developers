<?php
log_message('debug', 'Started migration 4.1.4...');
$login_entity = LOGIN_ENTITY;
$entity_id = $this->db->get_where('entity', ['entity_name' => $login_entity])->row()->entity_id;

log_message('debug', 'Inserting verification_code field');
//Add webauthn_data field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => $login_entity . '_verification_code',
    'fields_type' => 'INT',
    'fields_size' => '6',
    'fields_required' => DB_BOOL_FALSE,
    'fields_preview' => DB_BOOL_FALSE,
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);
$field_id = $this->db->insert_id();
log_message('debug', 'Inserting verification_code field draw');
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Verification Code',
    'fields_draw_html_type' => 'input_text',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',

]);
log_message('debug', 'Alter table ' . $login_entity);
$this->db->query("ALTER TABLE $login_entity ADD COLUMN {$login_entity}_verification_code VARCHAR(250) NULL;");
log_message('debug', 'Ended migration 4.1.4...');
