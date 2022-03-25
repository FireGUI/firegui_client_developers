<?php
log_message('debug', 'Started migration 2.3.1...');

$entity = $this->db->get_where('entity', ['entity_name' => LOGIN_ENTITY])->row();

$entity_id = $entity->entity_id;

log_message('debug', 'Inserting LOGIN_ENTITY_deleted field');

//Add LOGIN_ENTITY_deleted field
$this->db->insert('fields', [
    'fields_entity_id' => $entity_id,
    'fields_name' => LOGIN_ENTITY . '_deleted',
    'fields_type' => 'boolean',
    'fields_default' => DB_BOOL_FALSE,
    'fields_required' => '1',
    'fields_preview' => '0',
    'fields_visible' => '1',
    'fields_multilingual' => '0',
]);

$field_id = $this->db->insert_id();

log_message('debug', 'Inserting LOGIN_ENTITY_deleted field draw');

$this->db->insert('fields_draw', [
    'fields_draw_fields_id' => $field_id,
    'fields_draw_label' => ucfirst(LOGIN_ENTITY) . ' deleted',
    'fields_draw_html_type' => 'radio',
    'fields_draw_display_none' => '0',
    'fields_draw_enabled' => '1',
]);

log_message('debug', 'Alter table LOGIN_ENTITY');
log_message('debug', 'Adding soft_delete_flag to the entity'); // {"soft_delete_flag":"users_deleted"}

$action_fields = (!empty($entity->entity_action_fields)) ? json_decode($entity->entity_action_fields, true) : [];

if (empty($action_fields['soft_delete_flag'])) {
    $action_fields['soft_delete_flag'] = LOGIN_ENTITY . '_deleted';
}

log_message('debug', 'Updating entity_action_fields with updated action flags');
$this->db->where('entity_id', $entity_id)->update('entity', [
    'entity_action_fields' => json_encode($action_fields),
]);
