<?php
log_message('debug', 'Started migration 2.3.9...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'settings'])->row()->entity_id;


$this->load->model('entities');

$output = $this->entities->addFields([
    'entity_id' => $entity_id,
    'fields' => [
        ['fields_name' => 'update_in_progress', 'fields_type' => DB_BOOL_IDENTIFIER, 'fields_draw_html_type' => 'input_hidden', 'fields_default' => DB_BOOL_FALSE],
    ],
]);