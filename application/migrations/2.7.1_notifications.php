<?php

log_message('debug', 'Started migration 2.7.1...');


$this->load->model('core');

$core_modules = [
    'core-notifications'
];

foreach ($core_modules as $module) {
    if ($this->datab->module_installed($module)) {
        //nothing...
    } else {

        // Drop native notifications table
        $this->db->query("DROP TABLE IF EXISTS notifications;");

        $this->core->installModule($module);
    }
}

log_message('debug', 'Finished migration 2.7.1...');