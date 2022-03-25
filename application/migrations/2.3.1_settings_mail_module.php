<?php
log_message('debug', 'Started migration 2.3.1...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'settings'])->row()->entity_id;




log_message('debug', 'Inserting settings_mail_module_identifier field');



//Add settings_topbar_color field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => 'settings_mail_module_identifier',
    'fields_type' => DB_INTEGER_IDENTIFIER,
    'fields_required' => '0',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
    'fields_ref_auto_left_join' => DB_BOOL_TRUE,
    'fields_ref' => 'settings_mail_module_identifier',
    'fields_default' => 1
]);
$field_id = $this->db->insert_id();


log_message('debug', 'Inserting settings_mail_module_identifier field draw');

$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => 'Mail module',
    'fields_draw_html_type' => 'select',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',
]);



log_message('debug', 'Creating table settings_mail_module_identifier');
$this->db->query("
CREATE TABLE `settings_mail_module_identifier` (
  `settings_mail_module_identifier_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `settings_mail_module_identifier_value` varchar(250) NOT NULL,
  PRIMARY KEY (`settings_mail_module_identifier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");


log_message('debug', 'Inserting settings_mail_module_identifier into entity table');
$this->db->query("
    INSERT INTO `entity` 
    (`entity_name`, `entity_visible`, `entity_searchable`, `entity_login_entity`, `entity_type`, `entity_action_fields`, `entity_module`) 
    VALUES 
    ('settings_mail_module_identifier',	1,	0,	0,	2,	NULL,	NULL)
");
$entity_settings_mail_module_identifier_id = $this->db->insert_id();

log_message('DEBUG', 'Adding fields and fields_draw for entity settings_mail_module_identifier');


$this->db->query("
    INSERT INTO `fields` (`fields_entity_id`, `fields_default`, `fields_name`, `fields_type`, `fields_size`, `fields_required`, `fields_preview`, `fields_visible`, `fields_ref`, `fields_ref_auto_left_join`, `fields_ref_auto_right_join`, `fields_source`, `fields_select_where`, `fields_multilingual`) VALUES  
    ($entity_settings_mail_module_identifier_id,	'',	'settings_mail_module_identifier_id',	'INT',	NULL,	0,	0,	1,	NULL,	1,	1,	NULL,	NULL,	0)
");

$this->db->insert('fields', [
    'fields_entity_id' => $entity_settings_mail_module_identifier_id,
    'fields_name' => 'settings_mail_module_identifier_value',
    'fields_type' => 'VARCHAR',
    'fields_required' => '0',
    'fields_preview' => '1',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
    'fields_ref_auto_left_join' => DB_BOOL_TRUE,
    'fields_ref' => '',
    'fields_default' => ''
]);
$settings_mail_module_identifier_value_id = $this->db->insert_id();
$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $settings_mail_module_identifier_value_id,
    'fields_draw_label' => 'Settings Mail Module Identifier',
    'fields_draw_html_type' => 'input_text',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',
]);



log_message('debug', 'Inserting default value in settings_mail_module_identifier');
$this->db->query("
INSERT INTO `settings_mail_module_identifier` (`settings_mail_module_identifier_id`, `settings_mail_module_identifier_value`) VALUES
(1,	'System (default)');
");

log_message('debug', 'Alter table settings');
$this->db->query("ALTER TABLE settings ADD COLUMN settings_mail_module_identifier integer NOT NULL DEFAULT 1;");

log_message('debug', 'Search settings form');

$form_id = $this->db->get_where('forms', ['forms_identifier' => 'company_settings'])->row()->forms_id;

log_message('debug', 'Add field form id: ' . $form_id);

$dati = array(
    'forms_fields_forms_id' => $form_id,
    'forms_fields_fields_id' => $field_id,
    'forms_fields_override_colsize' => 6,
    'forms_fields_order' => 10,
);

$this->db->insert('forms_fields', $dati);
