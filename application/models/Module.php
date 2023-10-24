<?php
//20230123 - MP - Questo model sarebbe da deprecare e spostare i metodi nel model core/modules.php

class Module extends CI_Model
{
    private $_modules_installed = [];
    public function __construct()
    {
        parent::__construct();
    }

    public function loadTranslations($name, $language = null)
    {
        if ($language === null) {

            $language = $this->session->userdata('language');
        }
        $module_name = explode('/', $name)[0];
        $language_file = APPPATH . 'modules/' . $module_name . '/language/' . $language . '/' . $language . '_lang.php';
        if (file_exists($language_file)) {
            include($language_file);
        } else {
            $lang = [];
        }

        return $lang;
    }

    public function moduleExists($identifier)
    {
        if (!array_key_exists($identifier, $this->_modules_installed)) {
            $result = $this->db->where('modules_identifier', $identifier)->get('modules');

            if ($result->num_rows()) {
                $this->_modules_installed[$identifier] = true;
            } else {
                $this->_modules_installed[$identifier] = false;
            }
        }


        return $this->_modules_installed[$identifier];
    }
    public function generate_key($identifier, $type, $id)
    {
        return "{$identifier}-{$type}-{$id}";
    }
}