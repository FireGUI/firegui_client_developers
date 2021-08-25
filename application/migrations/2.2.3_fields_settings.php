<?php
log_message('debug', 'Started migration 2.2.3...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'settings'])->row()->entity_id;

log_message('debug', 'Inserting settings_topbar_color field');

/*
['fields_name' => 'topbar_color', 'fields_type' => 'VARCHAR', 'fields_draw_html_type' => 'color_palette',],
*/

//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'settings_topbar_color',
    'fields_type' => 'varchar',
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);
$field_id = $this->db->insert_id();
log_message('debug', 'Inserting settings_topbar_color field draw');
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Topbar color',
    'fields_draw_html_type' => 'color_palette',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',

]);
log_message('debug', 'Alter table settings');
$this->db->query("ALTER TABLE settings ADD COLUMN settings_topbar_color VARCHAR(255);");





log_message('debug', 'Inserting topbar_logo field');

/*

['fields_name' => 'topbar_logo', 'fields_draw_html_type' => 'upload_image'],
*/

//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'settings_topbar_logo',
    'fields_type' => 'varchar',
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);
$field_id = $this->db->insert_id();
log_message('debug', 'Inserting topbar_logo field draw');
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Topbar logo',
    'fields_draw_html_type' => 'upload_image',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',

]);
log_message('debug', 'Alter table settings');
$this->db->query("ALTER TABLE settings ADD COLUMN settings_topbar_logo VARCHAR(255);");







log_message('debug', 'Inserting topbar_logo field');

/*

['fields_name' => 'topbar_logo_small', 'fields_draw_html_type' => 'upload_image'],
*/

//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'settings_topbar_logo_small',
    'fields_type' => 'varchar',
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);
$field_id = $this->db->insert_id();
log_message('debug', 'Inserting topbar_logo_small field draw');
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Topbar logo small',
    'fields_draw_html_type' => 'upload_image',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',

]);
log_message('debug', 'Alter table settings');
$this->db->query("ALTER TABLE settings ADD COLUMN settings_topbar_logo_small VARCHAR(255);");



log_message('debug', '...ended migration 2.2.3');
