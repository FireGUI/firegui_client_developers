<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');


class Install extends CI_Controller {

    var $template = array();

    function __construct() {
        parent :: __construct();
    }

    public function index() {
        
    }
    
    public function import_query($filename) {
        
        $file = './application/logs/'.$filename.'.txt';
        
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