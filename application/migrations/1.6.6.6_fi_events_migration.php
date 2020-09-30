<?php

$this->db->empty_table('fi_events');

$pps = $this->db->get('post_process')->result_array();
foreach ($pps as $pp) {
    $this->db->insert('fi_events', [
        'fi_events_title' => "PP {$pp['post_process_id']}",
        'fi_events_json_data' => json_encode($pp),
        'fi_events_type' => 'database',
        'fi_events_when' => $pp['post_process_when'],
        'fi_events_ref_id' => $pp['post_process_entity_id'],
        'fi_events_action' => 'custom_code',
        'fi_events_actiondata' => json_encode(['code' => $pp['post_process_what']]),
        'fi_events_active' => DB_BOOL_TRUE,
        'fi_events_apilib' => $pp['post_process_apilib'],
        'fi_events_api' => $pp['post_process_api'],
        'fi_events_crm' => $pp['post_process_crm'],
        'fi_events_module' => $pp['post_process_module'],
        'fi_events_cron_frequency' => null,
        'fi_events_post_process_id' => $pp['post_process_id']
    ]);
}

//Importo gli hooks
$hooks = $this->db->get('hooks')->result_array();

foreach ($hooks as $hook) {
    $expl_type = explode('-', $hook['hooks_type']);
    $type = $expl_type[1]; //layout, grid, ecc...
    $this->db->insert('fi_events', [
        'fi_events_title' => $hook['hooks_title'],
        'fi_events_json_data' => json_encode($hook),
        'fi_events_type' => 'hook',
        'fi_events_when' => $hook['hooks_type'],
        'fi_events_ref_id' => $hook['hooks_ref'],
        'fi_events_action' => 'custom_code',
        'fi_events_actiondata' => json_encode(['code' => $hook['hooks_content']]),
        'fi_events_active' => DB_BOOL_TRUE,
        'fi_events_apilib' => null,
        'fi_events_api' => null,
        'fi_events_crm' => null,
        'fi_events_module' => $hook['hooks_module'],
        'fi_events_cron_frequency' => null,
        'fi_events_hook_id' => $hook['hooks_id']

    ]);
}

//Importo i crons
$crons = $this->db->get('crons')->result_array();

foreach ($crons as $cron) {
    $action_data = json_encode(
        [
            'code' => $cron['crons_text'],
            'url' => $cron['crons_file'],
            'where' => $cron['crons_where'],
            'entity_id' => $cron['crons_entity_id'],
            'type' => $cron['crons_type'],
        ]
    );

    if ($cron['crons_type'] == 'php_code') {
        $action = 'custom_code';
    } else {
        $action = $cron['crons_type'];
    }

    $this->db->insert('fi_events', [
        'fi_events_title' => $cron['crons_title'],
        'fi_events_json_data' => json_encode($cron),
        'fi_events_type' => 'cron',
        'fi_events_when' => null,
        'fi_events_ref_id' => $cron['crons_entity_id'],
        'fi_events_action' => $action,
        'fi_events_actiondata' => $action_data,
        'fi_events_active' => $cron['crons_active'],
        'fi_events_apilib' => null,
        'fi_events_api' => null,
        'fi_events_crm' => null,
        'fi_events_module' => $cron['crons_module'],
        'fi_events_cron_frequency' => $cron['crons_frequency'],
        'fi_events_cron_id' => $cron['crons_id']
    ]);
}
