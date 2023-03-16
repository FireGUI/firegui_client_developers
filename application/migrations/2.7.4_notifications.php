<?php

log_message('debug', 'Started migration 2.7.4...');

$this->load->model('core');

if ($this->datab->module_installed('core-notifications')) {
    //nothing...
} else {
    // Drop native notifications table
    $this->db->query("DROP TABLE IF EXISTS notifications;");

    $this->core->installModule('core-notifications');
}


log_message('debug', 'Finished migration 2.7.4...');