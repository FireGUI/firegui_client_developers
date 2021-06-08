<?php
$login_entity = LOGIN_ENTITY;
$entity_id = $this->db->get_where('entity', ['entity_name' => $login_entity])->row()->entity_id;

//Add webauthn_data field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => $login_entity . '_webauthn_data',
    'fields_type' => 'LONGTEXT',
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilangual' => '0',
]);
$field_id = $this->db->insert_id();

$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Webauthn data',
    'fields_draw_html_type' => 'textarea',
    'fields_draw_display_none' => '0',
    'fields_draw_enable' => '1',

]);

$this->db->query("ALTER TABLE $login_entity ADD COLUMN {$login_entity}_webauthn_data LONGTEXT;");
