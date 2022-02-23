<?php

log_message('debug', 'Started migration 2.3.0...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'settings'])->row()->entity_id;

log_message('debug', 'Inserting settings_login_background field');

//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'settings_login_background',
    'fields_type' => 'varchar',
    'fields_required' => DB_BOOL_FALSE,
    'fields_preview' => DB_BOOL_FALSE,
    'fields_visible' => DB_BOOL_TRUE,
    'fields_multilingual' => DB_BOOL_FALSE,
]);
$field_id = $this->db->insert_id();

log_message('debug', 'Inserting settings_login_background field draw');

$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Background login page',
    'fields_draw_html_type' => 'upload_image',
    'fields_draw_display_none' => DB_BOOL_FALSE,
    'fields_draw_enabled' => DB_BOOL_TRUE,

]);
log_message('debug', 'Alter table settings');

$this->db->query("ALTER TABLE settings ADD COLUMN settings_login_background VARCHAR(250);");

log_message('debug', 'Search settings form');

$this->db->query("UPDATE forms SET forms_identifier = 'company_settings' WHERE forms_name = 'Settings' AND forms_entity_id = '$entity_id'");

$form_id = $this->db->get_where('forms', ['forms_identifier' => 'company_settings'])->row()->forms_id;

log_message('debug', 'Add field form id: ' . $form_id);

$dati = array(
    'forms_fields_forms_id' => $form_id,
    'forms_fields_fields_id' => $field_id,
    'forms_fields_override_colsize' => 6,
    'forms_fields_order' => 10,
);
$this->db->insert('forms_fields', $dati);

// Update General settings layout boxes to set container box
log_message('debug', 'Update layout box General settings');
$layout_box_id = $this->db->query("SELECT * FROM layouts_boxes WHERE layouts_boxes_title = 'General settings' ORDER BY layouts_boxes_id ASC LIMIT 1")->row()->layouts_boxes_id;
if ($layout_box_id) {
    $this->db->query("UPDATE layouts_boxes SET layouts_boxes_show_container = " . DB_BOOL_TRUE . " WHERE layouts_boxes_id = $layout_box_id");
}
