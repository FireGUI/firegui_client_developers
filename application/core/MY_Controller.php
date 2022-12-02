<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Controller base
 *
 * @author Alberto
 * @property-read Apilib $apilib
 * @property-read Auth $auth
 * @property-read Datab $datab
 * @property-read Crmentity $crmentity
 * @property-read Mail_model $mail_model
 * @property-read CI_Input $input
 */
class MY_Controller extends MX_Controller
{

    /**
     * @var array
     */
    protected $template = [];

    /**
     * @var array
     */
    public $settings;

    /**
     * @var bool
     */
    public $isAdmin;

    /**
     * @var bool
     */
    public $isDev;

    /**
     * Class constructor
     * Initialize base options
     */
    public function __construct()
    {
        //debug($this, true);
        parent::__construct();
        $this->load->driver('Cache/drivers/MY_Cache_file', null, 'mycache');

        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('raw_queries')) {

            $this->db->cache_on();
        } else {
            $this->db->cache_off();
        }

        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) {
            $this->output->cache(240);
        }

        // Inizializza le variabili d'istanza del controller
        if ($this->db->table_exists('settings_template')) {
            $this->db
                ->join('settings_template', 'settings.settings_template = settings_template.settings_template_id', 'LEFT');

        }
        $this->settings = $this->db
            ->get('settings')->row_array();

        //$this->settings = $this->db->get('settings')->row_array();
        $this->isAdmin = $this->auth->is_admin();
        $this->isDev = is_development();
        //$this->output->set_header('Cache-Control: max-age=31536000');
        // Profiler se richiesto da amministratori (oppure se in modalità sviluppo)
        $this->output->enable_profiler($this->input->get('_profiler') && ($this->isAdmin or $this->isDev));

        // Abilita errori/profiler in ambiente di sviluppo
        if ($this->isAdmin or $this->isDev) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            $this->apilib->setDebug(false);
        }

        // Imposto lingue apilib
        $currentLang = $this->datab->getLanguage();
        if ($currentLang) {
            $fallbackLang = $this->datab->getDefaultLanguage();
            $this->apilib->setLanguage($currentLang['id'], $fallbackLang['id']);

            $this->config->set_item('language', $currentLang['file']);

            $loaded = $this->lang->is_loaded;

            $this->is_loaded = [];

            foreach ($loaded as $lang) {
                $this->lang->load($lang);
            }
        }
    }

    public function __destruct()
    {

        // if (is_maintenance()) {
        //     //write long query
        //     if (!$slow_queries = $this->session->userdata('slow_queries')) {
        //         $slow_queries = [];
        //     }

        //     foreach ($this->db->queries as $key => $query) {
        //         $time = @$this->db->query_times[$key];
        //         if (!$time) {
        //             continue;
        //         }
        //         if (array_key_exists($query, $slow_queries)) {
        //             if ($time > $slow_queries[$query]) {
        //                 $slow_queries[$query] = $time;
        //             }
        //         } else {
        //             $slow_queries[$query] = $time;
        //         }
        //     }

        //     arsort($slow_queries);
        //     $slow_queries = array_slice($slow_queries, 0, 10);
        //     //debug($slow_queries, true);
        //     $this->session->set_userdata('slow_queries', $slow_queries);
        //     //debug($this->session->userdata('slow_queries'), true);
        // } else {
        //     $this->session->set_userdata('slow_queries', []);
        // }

        // //parent::__destruct();
    }
}
