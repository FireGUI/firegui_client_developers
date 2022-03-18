<?php
log_message('debug', 'Started migration 2.3.1...');

$entity = $this->db->get_where('entity', ['entity_name' => LOGIN_ENTITY])->row();

$entity_id = $entity->entity_id;

log_message('debug', 'Inserting LOGIN_ENTITY_deleted field');

//Add LOGIN_ENTITY_deleted field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => LOGIN_ENTITY . '_last_password_change',
    'fields_type' => 'DATETIME',
    'fields_default' => 180,
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);

$field_id = $this->db->insert_id();

log_message('debug', 'Inserting LOGIN_ENTITY_deleted field draw');

$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => ucfirst(LOGIN_ENTITY) . ' last password change',
    'fields_draw_html_type' => 'datetime',
    'fields_draw_display_none' => '1',
    'fields_draw_enabled' => '1',
]);

log_message('debug', 'Alter table LOGIN_ENTITY');
