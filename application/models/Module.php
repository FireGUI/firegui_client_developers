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

    public function moduleExists($identifier, $only_installed = false)
    {
        if (!array_key_exists($identifier, $this->_modules_installed)) {
            if ($only_installed) {
                $this->db->where('modules_installed', 1);
            }
            $result = $this->db->where('modules_identifier', $identifier)->get('modules');

            if ($result->num_rows() && is_dir(APPPATH . 'modules/' . $identifier)) {
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