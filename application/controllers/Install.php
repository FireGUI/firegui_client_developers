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
     * From 2.3.9 Method to invoke update database from external curl
     * @return never
     */
    public function update()
    {

        if (!$this->datab->is_admin() || !is_cli()) {
            echo_log("error", "Cannot access without admin or cli...");
            return false;
        }
        echo_log("debug", "Start update database...");
        $this->load->model('core');
        $this->core->update();
        echo_log("debug", "Finish update database...");
    }


    /**
     * Summary of import_query
     * @param mixed $filename
     * @return void
     */

    public function import_query($filename)
    {
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