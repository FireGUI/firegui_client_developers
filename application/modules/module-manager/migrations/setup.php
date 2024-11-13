<?php
// Set baseurl default
$this->db->query("UPDATE modules_manager_settings SET modules_manager_settings_modules_repository = 'https://admin.openbuilder.net/'");


//auto update ultima patch stable
$recursive_to_last = true;
$last_version = $this->core->updatePatches(null, 4, $recursive_to_last);

$this->load->model('core');
$this->core->update();
