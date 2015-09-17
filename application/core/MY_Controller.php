<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Controller base
 * 
 * @author Alberto
 * @property-read Apilib $apilib
 * @property-read Datab $datab
 * @property-read Mail_model $mail_model
 * @property-read CI_Input $input
 */
class MY_Controller extends CI_Controller {

    public $template = [];
    public $settings = [];
    
    public function __construct() {
        parent::__construct();
        
        // Profiler sse non è ajax
        $this->output->enable_profiler(gethostname() === 'sfera' && !$this->input->is_ajax_request());
        
        // Abilita errori/profiler in ambiente di sviluppo
        if (gethostname() === 'sfera' OR $this->auth->is_admin()) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            $this->apilib->setDebug(false);
        }
        
        // Recupera settings
        $this->settings = $this->db->get('settings')->row_array();
        
        // Imposto lingue apilib
        $currentLang = $this->datab->getLanguage();
        if ($currentLang) {
            $fallbackLang = $this->datab->getDefaultLanguage();
            $this->apilib->setLanguage($currentLang['id'], $fallbackLang['id']);
        }
    }
    
    
    
}
