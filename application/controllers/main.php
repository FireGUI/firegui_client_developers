<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Main extends CI_Controller {

    var $template = array();
    var $settings = NULL;
    

    function __construct() {
        parent :: __construct();
        
        // Controllo anche la current uri
        if($this->auth->guest()) {
            
            // FIX: siamo nel controller main, quindi l'uri dovrebbe cominciare con main
            $uri = explode('/', uri_string());
            
            foreach ($uri as $k=>$chunk) {
                if($chunk === 'main') {
                    // Se il chunk è main allora sono 'apposto'
                    break;
                } else {
                    // Altrimenti è un prefisso che è già contato nel base_url, quindi lo unsetto
                    unset($uri[$k]);
                }
            }
            
            $redirection_url = base_url(implode('/', $uri));
            $this->auth->store_intended_url($redirection_url);
            redirect('access');
        }
        
        $this->settings = $this->db->get('settings')->row_array();
        $this->output->enable_profiler($_SERVER['SERVER_ADDR']=='192.168.0.201');
    }

    public function index() {
        /** Carica la dashboard - prendi il primo layout `dashboardable` accessibile dal cliente **/
        $layouts = $this->db->order_by('layouts_id')->get_where('layouts', array('layouts_dashboardable' => 't'))->result_array();
        foreach($layouts as $layout) {
            if($this->datab->can_access_layout($layout['layouts_id'])) {
                break;
            }
        }
        
        if(isset($layout['layouts_id'])) {
            $this->layout($layout['layouts_id']);
        } else {
            show_error('Nessun layout Dashboard trovato.');
        }
    }

    public function page($page) {
        $dati['current_page'] = $page;
        $pagina = $this->load->view("pages/$page", array('dati' => $dati), true);
        $this->stampa($pagina);
    }
    
    public function entity($entity_id, $edit_record=false) {
        if (!$entity_id) {
            redirect();
        }
        $dati = array();
        $dati['current_page'] = "entity_{$entity_id}";
        $dati['entity_id'] = $entity_id;
        $dati['entity'] = $this->datab->get_entity($entity_id);
        if (!$dati['entity']) {
            redirect();
        }
        
        if ($dati['entity']['entity_view']) {
            $pagina = $this->load->view("pages/{$dati['entity']['entity_view']}", array('dati' => $dati), true);
        } else {
            $pagina = $this->load->view("pages/entity_view_1", array('dati' => $dati), true);
        }
        
        $this->stampa($pagina);
    }
    
    public function layout($layout_id = null, $value_id=null) {
        
        if(!$layout_id) {
            redirect();
        }
        
        
        /** $value_id ha senso sse è un numero */
        if(!is_numeric($value_id) && !is_null($value_id)) {
            $value_id = null;
        }
        
        
        if(! $this->datab->can_access_layout($layout_id)) {
            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
            $this->stampa($pagina);
            return;
        }
        
        $dati = $this->datab->build_layout($layout_id, $value_id);
        if(is_null($dati)) {
            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
            $this->stampa($pagina);        
        } elseif(isset($dati['layout_container']['layouts_pdf']) && $dati['layout_container']['layouts_pdf'] == 't') {
            $content = $this->load->view("layout/pdf", array('dati' => $dati,'value_id' => $value_id), true);
            
            // Load and render the pdf
            require_once('./class/html2pdf/html2pdf.class.php');
            $html2pdf = new HTML2PDF('P', 'A4', 'it');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->WriteHTML($content);

            $name = url_title($dati['layout_container']['layouts_title'], '-', TRUE).'.pdf';
            $html2pdf->Output($name, 'I'); // stampa il pdf nel browser
        } else {
            $dati['current_page'] = "layout_{$layout_id}";
            $dati['show_title'] = TRUE;
            $pagina = $this->load->view("pages/layout", array('dati' => $dati,'value_id' => $value_id), true);
            $this->stampa($pagina);        
        }
        
    }
    
    public function edit_form($entity_id, $record_id, $form_id=null) {
        if (!$entity_id) {
            redirect();
        }
        $dati = array();
        $dati['current_page'] = "entity_{$entity_id}";
        $dati['entity_id'] = $entity_id;
        $dati['entity'] = $this->datab->get_entity($entity_id);
        if (!$dati['entity']) {
            redirect();
        }
        
        // Ricavo il form id di default se non specificato uno diverso
        if (!$form_id) {
            $form_id = $this->datab->get_default_form($entity_id);
        }
        // Recuperto i dati per quel form
        $dati['edit_data'] = $this->datab->get_data_entity($entity_id, 1, array($dati['entity']['entity_name'].'_id' => $record_id));
        
        debug($dati);
        
        if ($dati['entity']['entity_view']) {
            $pagina = $this->load->view("pages/{$dati['entity']['entity_view']}", array('dati' => $dati), true);
        } else {
            $pagina = $this->load->view("pages/entity_view_1", array('dati' => $dati), true);
        }
        
        $this->stampa($pagina);
    }
    
    
    
    /* =============================
     * Permissions
     * ============================= */
    public function permissions() {
        
        if( ! $this->datab->is_admin()) {
            $pagina = '<h1 style="color: #cc0000;">Permission denied</h1>';
            $this->stampa($pagina);
            return;
        }
        
        $dati['current_page'] = 'permissions';

        // ===========
        // Sezione permessi
        $dati['groups'] = array_key_map($this->db->where('permissions_group IS NOT NULL AND permissions_user_id IS NULL')->get('permissions')->result_array(), 'permissions_group');

        $where = array();
        if(defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            // Se c'è un login field mi aspetto che sia un booleano e
            // dev'essere true
            $where[LOGIN_ACTIVE_FIELD] = 't';
        }
        $dati['users'] = $this->datab->get_entity_preview_by_name(LOGIN_ENTITY, $where);
        asort($dati['users']);


        if (LOGIN_NAME_FIELD) {
            $this->db->order_by(LOGIN_NAME_FIELD);
        }

        if (LOGIN_SURNAME_FIELD) {
            $this->db->order_by(LOGIN_SURNAME_FIELD);
        }

        // ===========
        // Sezione layouts
        if(defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            $users = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ACTIVE_FIELD => 't'))->result_array();
        } else {
            $users = $this->db->get(LOGIN_ENTITY)->result_array();
        }

        $usersLayouts = array_combine(array_key_map($users, LOGIN_ENTITY . '_id'), array_map(function($user) {
            $n = isset($user[LOGIN_NAME_FIELD])? $user[LOGIN_NAME_FIELD]: '';
            $s = isset($user[LOGIN_SURNAME_FIELD])? $user[LOGIN_SURNAME_FIELD]: '';
            return ($n && $s)? ucwords($n[0] . '. ' . $s): $n . ' ' . $s;
        }, $users));

        $layouts = $this->db->order_by('layouts_title')->get('layouts')->result_array();
        $dati['layouts'] = array_combine(array_map(function($layout) { return $layout['layouts_id']; }, $layouts), array_map(function($layout) { return ucfirst(str_replace('_', ' ', $layout['layouts_title'])); }, $layouts));

        $unalloweds = $this->db->get('unallowed_layouts')->result_array();
        $dati['unallowed'] = array();

        $dati['userGroupsStatus'] = $userGroupsStatus = $this->datab->getUserGroups();  // Un array dove per ogni utente ho il gruppo corrispondente
        $dati['users_layout'] = [];
        foreach ($usersLayouts as $userId => $userPreview) {
            if (isset($userGroupsStatus[$userId])) {
                $dati['users_layout'][$userGroupsStatus[$userId]] = ucwords($userGroupsStatus[$userId]);
            } else {
                $dati['users_layout'][$userId] = $userPreview;
            }
        }

        foreach($unalloweds as $unallowedLayout) {
            $layout = $unallowedLayout['unallowed_layouts_layout'];
            $user = $unallowedLayout['unallowed_layouts_user'];

            if (isset($userGroupsStatus[$user]) && $userGroupsStatus[$user]) {
                $dati['unallowed'][$userGroupsStatus[$user]][] = $layout;
            } else {
                $dati['unallowed'][$user][] = $layout;
            }
        }

        $pagina = $this->load->view("pages/permissions", array('dati' => $dati), true);
        $this->stampa($pagina);
    }
    
    
    
    
    
    
    
    
    /* =============================
     * Ricerca
     * ============================= */
    public function search() {
        $dati['current_page'] = 'search';
        $dati['search_string'] = $this->input->post('search');
        
        if(strlen($dati['search_string']) > 2) {
            $dati['count_total'] = 0;
            $dati['results'] = $this->datab->get_search_results($dati['search_string']);
            foreach ($dati['results'] as $res) {
                $dati['count_total'] += count($res['data']);
            }
        } else {
            $dati['count_total'] = -1;
        }
        
        
        if($dati['count_total'] === 1) {
            
            $results = array_values($dati['results']);
            $entity_result = $results[0];
            $link = $this->datab->get_detail_layout_link($entity_result['entity']['entity_id']);
            
            if($link) {
                $data_results = array_values($entity_result['data']);
                $data = $data_results[0];
                redirect($link.'/'.$data[$entity_result['entity']['entity_name'] . '_id']);
            }
            
        }
        
        

        $pagina = $this->load->view("pages/search_results", array('dati' => $dati), true);
        $this->stampa($pagina);
    }
    
    
    
    public function cache_control($action = null) {
        
        switch ($action) {
            case 'on':
                $this->apilib->toggleCachingSystem(true);
                break;
            
            case 'off':
                $this->apilib->toggleCachingSystem(false);
                break;
            
            case 'clear':
                $this->apilib->clearCache();
                break;
            
            default :
                show_error('Action non definita');
        }
        
        $redirection = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_VALIDATE_URL);
        redirect($redirection?:  base_url());
    }
    
    
    /* =============================
     * Metodi utili
     * ============================= */
    public function clear_session($logout=0) {
        $user_id = $this->auth->get(LOGIN_ENTITY.'_id');
        $this->session->sess_destroy();
        
        if( ! $logout) {
            $this->auth->login_force($user_id);
        }
        
        redirect(base_url());
    }
    
    public function test_email() {
        
        $email = $this->input->get_post('email');
        $key = $this->input->get_post('key');
        $lang = $this->input->get_post('lang')?:'it';
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            die('E-mail non valida. Passa un get/post con chiave `email`');
        }
        
        if (!$key) {
            die('Key e-mail non valida. Passa un get/post con chiave `key`');
        }
        
        $sent = $this->mail_model->send($email, $key, $lang);
        echo $sent? 'E-mail inviata correttamente': 'E-mail non inviata';
    }
    
    
    
    
    public function stampa($pagina) {
        $this->template['head'] = $this->load->view('layout/head', array(), true);
        $this->template['header'] = $this->load->view('layout/header', array(), true);
        $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        $this->template['page'] = $pagina;
        $this->template['footer'] = $this->load->view('layout/footer', null, true);
        
        echo $this->load->view('layout/main', $this->template, true);
    }

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */