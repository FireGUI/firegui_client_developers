<?php
log_message('debug', 'Started migration 2.2.9...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'settings'])->row()->entity_id;

log_message('debug', 'Inserting settings_company_email_update_notifications field');

/*
['fields_name' => 'topbar_color', 'fields_type' => 'VARCHAR', 'fields_draw_html_type' => 'color_palette',],
*/

//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'settings_company_email_update_notifications',
    'fields_type' => 'varchar',
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);
$field_id = $this->db->insert_id();

log_message('debug', 'Inserting settings_company_email_update_notifications field draw');

$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Email update notification',
    'fields_draw_html_type' => 'input_text',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',

]);
log_message('debug', 'Alter table settings');

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
