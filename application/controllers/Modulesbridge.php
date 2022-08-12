<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Modulesbridge extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) { //???
            //$this->output->cache(0);
        }
    }

    public function loadAssetFile($module_identifier)
    {
        $file = $this->input->get('file');
        if (stripos($file, '..')) {
            die("Nope!");
        }
        $explode = explode('.', $file);
        $ext = end($explode);
        $modules_path = APPPATH . 'modules';
        $assets_folder = "{$modules_path}/{$module_identifier}/assets";
        $asset_file = "$assets_folder/$file";
        if (file_exists($asset_file)) {
            $fp = fopen($asset_file, 'rb');
            header("Content-Type: text/{$ext}");
            header("Content-Length: " . filesize($asset_file));
            fpassthru($fp);
        } else {
            echo '';
        }
    }
}
