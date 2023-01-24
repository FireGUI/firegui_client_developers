<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Templatebridge extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) { //???
            //$this->output->cache(0);
        }
    }

    public function loadAssetFile($template_folder)
    {

        $file = $this->input->get('file');
        if (stripos($file, '..')) {
            die("Nope!");
        }

        $explode = explode('.', $file);

        $ext = end($explode);

        $templates_path = APPPATH . 'views/';

        $assets_folder = "{$templates_path}/{$template_folder}/assets";

        $asset_file = "$assets_folder/$file";

        if (file_exists($asset_file)) {
            $fp = fopen($asset_file, 'rb');
            header("Content-Type: text/{$ext}");
            header("Content-Length: " . filesize($asset_file));
            fpassthru($fp);
        }
    }
}
