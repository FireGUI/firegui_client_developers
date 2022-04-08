<?php


class Init_hook
{

    private $ci = null;


    public function __construct()
    {
        $this->ci = &get_instance();
    }

    /**
     * @deprecated 
     */
    public function initialize()
    {
        // @deprecated: si occupa il datab di effettuare questo passaggio
        $language = $this->ci->session->userdata('language');

        if (!$language) {
            $default_language_id = $this->ci->db->get_where('settings')->row()->settings_default_language;
            $default_language = $this->ci->db->get_where('languages', ['languages_id' => $default_language_id])->row_array();
            if (!empty($default_language)) {
                $language = strtolower($default_language['languages_name']);
            } else {
                $language = 'english';
            }

            $this->ci->session->set_userdata('language', $language);
        }

        $language = str_ireplace(['ñ', 'ç'], ['n', 'c'], $language);

        $this->ci->load->language($language, $language);
    }

    public function pre_destruct()
    {
        //debug('test', true);
        if (is_maintenance()) {
            //write long query
            if (!$slow_queries = $this->ci->session->userdata('slow_queries')) {
                $slow_queries = [];
            }

            foreach ($this->ci->db->queries as $key => $query) {
                $time = @$this->ci->db->query_times[$key];
                if (!$time) {
                    continue;
                }
                if (array_key_exists($query, $slow_queries)) {
                    if ($time > $slow_queries[$query]) {
                        $slow_queries[$query] = $time;
                    }
                } else {
                    $slow_queries[$query] = $time;
                }
            }

            arsort($slow_queries);
            $slow_queries = array_slice($slow_queries, 0, 10);
            //debug($slow_queries, true);
            $this->ci->session->set_userdata('slow_queries', $slow_queries);
            //debug($this->session->userdata('slow_queries'), true);
        } else {
            $this->ci->session->set_userdata('slow_queries', []);
        }
        //$_SESSION['slow_queries'] = $this->ci->session->userdata('slow_queries');
        //parent::__destruct();
    }
}
