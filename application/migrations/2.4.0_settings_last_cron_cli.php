<?php

log_message('debug', 'Started migration 2.4.0...');

$entity_id = $this->db->get_where('entity', ['entity_name' => 'settings'])->row()->entity_id;


$this->load->model('entities');

$output = $this->entities->addFields([
    'entity_id' => $entity_id,
    'fields' => [
        ['fields_name' => 'last_cron_cli', 'fields_type' => 'DATETIME', 'fields_draw_html_type' => 'input_hidden'],
    ],
]);
