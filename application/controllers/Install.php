<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Install extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

    }


    /**
     * From 2.3.10 Method to invoke update database from external curl
     * @return never
     */
    public function update($indexes_update = false)
    {

        if (!$this->datab->is_admin() || (!is_cli() && !is_development())) {
            echo_log("error", "Cannot access without admin (in maintenance) or cli...");
            return false;
        }
        echo_log("debug", "Start update database...");
        $this->load->model('core');
        $this->core->update($indexes_update);
        echo_log("debug", "Finish update database...");
    }

    /**
     * From 2.4.3 Update client patches
     */
    public function UpdatePatches($recursive_to_last = false)
    {

        // Security check
        if (!$this->datab->is_admin() && !is_cli()) {
            echo_log("error", "Cannot access without admin or cli...");
            return false;
        }
        echo_log('info', 'Start update without backup...');

        $this->load->model('core');
        $last_version = $this->core->updatePatches(null, 4, $recursive_to_last);
        echo_log("debug", "Updated to: " . $last_version);
        echo_log("debug", "Update client finish...");
    }

    /**
     * From 2.3.10 Update client. Invoked manually
     */
    public function UpdateClient($update_patches = false)
    {

        // Security check
        if (!$this->datab->is_admin() && !is_cli()) {
            echo_log("error", "Cannot access without admin or cli...");
            return false;
        }
        echo_log('info', 'Start update without backup...');

        $this->load->model('core');
        $last_version = $this->core->updateClient(null, 0, 4, $update_patches);
        echo_log("debug", "Updated to: " . $last_version);
        echo_log("debug", "Update client finish...");
    }

    /**
     * Summary of import_query
     * @param mixed $filename
     * @return void
     */

    public function import_query($filename)
    {

        // Security check
        if (!$this->datab->is_admin() || !is_cli()) {
            echo_log("error", "Cannot access without admin or cli...");
            return false;
        }

        $file = './application/logs/' . $filename . '.txt';

        if (file_exists($file)) {
            $queries = file($file);

            foreach ($queries as $query) {
                $this->db->query($query);
            }

            $this->load->helper('file');
            write_file($file, '');
        }
    }


}