<?php


class Init_hook {
    
    private $ci = null;
    
    
    public function __construct() {
        $this->ci =& get_instance();
    }
    
    
    public function initialize() {
        
        $language = $this->ci->session->userdata('language');
        if($language === FALSE) {
            $language = $this->ci->config->item('language');
            $this->ci->session->set_userdata('language', $language);
        }
        
        $this->ci->load->language($language, $language);
    }
    
}

?>
