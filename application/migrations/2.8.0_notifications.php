<?php

log_message('debug', 'Started migration 2.8.0...');

$this->load->model('core');

if ($this->datab->module_installed('core-notifications')) {
    //nothing...
} else {
    // Drop native notifications table
    $this->db->query("DROP TABLE IF EXISTS notifications;");
    $this->mycache->clearCache();
    $this->core->installModule('core-notifications');
}


log_message('debug', 'Finished migration 2.8.0...');