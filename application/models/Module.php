<?php


class Module extends CI_Model
{

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
        $result = $this->selected_db->where('modules_identifier', $identifier)->get('modules');
        return $result->num_rows() != 0;
    }
}
