<?php


class Init_hook
{

    private $ci = null;


    public function __construct()
    {
        $this->ci = &get_instance();
    }


    public function initialize()
    {

        // Deprecato: si occupa il datab di effettuare questo passaggio
        $language = $this->ci->session->userdata('language');
        //debug($this->ci->lang, true);
        if (!$language) {
            //$language = $this->ci->config->item('language');
            $default_language_id = $this->ci->db->get_where('settings')->row()->settings_default_language;
            $default_language = $this->ci->db->get_where('languages', ['languages_id' => $default_language_id])->row_array();
            if (!empty($default_language)) {
                $language = strtolower($default_language['languages_name']);
                
                
            } else {
                $language = 'english';
            }

            $this->ci->session->set_userdata('language', $language);
        }

        $language = str_ireplace(['ñ','ç'],['n','c'], $language);

        $this->ci->load->language($language, $language);
    }
}
