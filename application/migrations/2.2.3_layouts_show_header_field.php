<?php
log_message('debug', 'Started migration 2.2.3...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'layouts'])->row()->entity_id;

log_message('debug', 'Inserting layouts add show header field');

//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'layouts_show_header',
    'fields_type' => 'bool',
    'fields_required' => '1',
    'fields_default' => DB_BOOL_TRUE,
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);

$field_id = $this->db->insert_id();
log_message('debug', 'Inserting layouts_show_header field draw');
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Show header',
    'fields_draw_html_type' => 'checkbox',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',
]);

log_message('debug', 'Alter table layouts');
$this->db->query("ALTER TABLE layouts ADD COLUMN layouts_show_header BOOLEAN NOT NULL DEFAULT '" . DB_BOOL_TRUE . "';");

log_message('debug', '...ended migration 2.2.3');
