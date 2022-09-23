<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Main extends MY_Controller
{

    /**
     * Controller constructor
     */
    public function __construct()
    {

        parent::__construct();

        // Controllo anche la current uri
        if ($this->auth->guest()) {

            // FIX: siamo nel controller main, quindi l'uri dovrebbe cominciare con main
            $uri = explode('/', uri_string());

            foreach ($uri as $k => $chunk) {
                if ($chunk === 'main') {
                    // Se il chunk è main allora sono 'apposto'
                    break;
                } else {
                    // Altrimenti è un prefisso che è già contato nel base_url, quindi lo unsetto
                    unset($uri[$k]);
                }
            }
            if ($this->input->get('source')) {
                $append = '?source=' . $this->input->get('source');
            } else {
                $append = '';
            }
            $redirection_url = base_url(implode('/', $uri));
            $this->auth->store_intended_url($redirection_url);
            redirect('access' . $append);
        }

        // Imposta il log di accesso giornaliero
        $this->apilib->logSystemAction(Apilib::LOG_ACCESS);

    }

    public function dashboard()
    {
        $this->index();
    }

    /**
     * Dashboard/main page
     */
    public function index()
    {
        if ($layout_dashboard = $this->auth->get('default_dashboard')) {
            $this->layout($layout_dashboard);
            return;

        } else {
            // Carica la dashboard - prendi il primo layout `dashboardable` accessibile dall'utente
            $layouts = $this->db->order_by('layouts_id')->get_where('layouts', array('layouts_dashboardable' => DB_BOOL_TRUE))->result_array();
            foreach ($layouts as $layout) {
                if ($this->datab->can_access_layout($layout['layouts_id'])) {
                    $this->layout($layout['layouts_id']);
                    return;
                }
            }
        }
        show_error('Nessun layout Dashboard trovato.');
    }

    /**
     * Get Ajax Layout Content
     */
    public function get_layout_content($layout_id, $value_id = null)
    {
        if (empty($layout_id)) {
            die(json_encode(array('status' => 0, 'msg' => 'Layout id needed')));
        }

        //Se non è un numero, vuol dire che sto passando un url-key
        if (!is_numeric($layout_id)) {
            $result = $this->db->where('layouts_identifier', $layout_id)->get('layouts');

            if ($result->num_rows() == 0) {
                die(json_encode(array('status' => 0, 'msg' => 'Layout not found')));
            } else {
                $layout_id = $result->row()->layouts_id;
            }
        }
        // $value_id must be a number
        if (!is_numeric($value_id) && !is_null($value_id)) {
            $value_id = null;
        }

        // Check permission
        if (!$this->datab->can_access_layout($layout_id, $value_id)) {
            die(json_encode(array('status' => 0, 'msg' => 'Permission denied')));
        }

        //If layout is module dependent, preload translations
        $layout = $this->layout->getLayout($layout_id);
        if ($layout['layouts_module']) {
            $this->lang->language = array_merge($this->lang->language, $this->module->loadTranslations($layout['layouts_module'], @array_values($this->lang->is_loaded)[0]));
            $this->layout->setLayoutModule($layout['layouts_module']);
        }

        // Build layout, if null then layout is not accessible due to user permissions
        $dati = $this->datab->build_layout($layout_id, $value_id);

        if (is_null($dati)) {

            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
            $this->layout->setLayoutModule();
            $dati = [
                'status' => 1, 'type' => 'html', 'content' => $pagina, 'value_id' => $value_id,
            ];
            //$dati['title_prefix'] = ucfirst(t(trim(implode(', ', array_filter([$dati['layout_container']['layouts_title'], $dati['layout_container']['layouts_subtitle']])))));

            $this->load->view('layout/json_return', ['json' => json_encode($dati)]);
        } else {
            // I have 2 type of layouts: PDF or standard. If PDF return type pdf and open in target blank by client
            if ($dati['layout_container']['layouts_pdf'] == DB_BOOL_TRUE) {
                echo json_encode(array('status' => 1, 'type' => 'pdf'));
            } else {
                $dati['title_prefix'] = ucfirst(t(trim(implode(', ', array_filter([$dati['layout_container']['layouts_title'], $dati['layout_container']['layouts_subtitle']])))));
                $dati['current_page'] = "layout_{$layout_id}";
                $dati['show_title'] = true;
                $dati['related_entities'] = $this->layout->getRelatedEntities();
                //debug($dati['related_entities']);
                $dati['layout_id'] = $layout_id;
                $pagina = $this->load->view("pages/layout", compact('dati', 'value_id'), true);

                $this->layout->setLayoutModule();
                $this->load->view('layout/json_return', ['json' => json_encode(array('status' => 1, 'type' => 'html', 'content' => $pagina, 'value_id' => $value_id, 'dati' => $dati))]);

            }
        }
    }

    /**
     * Render a layout item
     * @param int $layout_id
     * @param int $value_id
     */
    public function layout($layout_id = null, $value_id = null)
    {

        // Se non ho un layout_id non posso fare il render di questo controllo,
        // allora redirigo il flusso verso il controller dashboard che calcolerà
        // automaticamente il layout principale da usare come dashboard
        if (!$layout_id) {
            redirect();
        }

        //Se non è un numero, vuol dire che sto passando un url-key
        if (!is_numeric($layout_id)) {
            $layout_id = $this->layout->getLayoutByIdentifier($layout_id);

            if (!$layout_id) {
                show_error("Layout non trovato!");
            }
        }

        // $value_id ha senso sse è un numero
        if (!is_numeric($value_id) && !is_null($value_id)) {
            $value_id = null;
        }

        // Se non posso accedere a questo layout, allora mostro la pagina con il
        // messaggio: "La pagina da te cercata non esiste, oppure non hai i
        // permessi per accedervi"
        if (!$this->datab->can_access_layout($layout_id, $value_id)) {

            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
            $this->stampa($pagina, $value_id);
        } else {

            //If layout is module dependent, preload translations
            $layout = $this->layout->getLayout($layout_id);
            if ($layout['layouts_module']) {
                $this->lang->language = array_merge($this->lang->language, $this->module->loadTranslations($layout['layouts_module'], @array_values($this->lang->is_loaded)[0]));
                $this->layout->setLayoutModule($layout['layouts_module']);
            }

            // Build layout, if null then layout is not accessible due to user permissions
            $dati = $this->datab->build_layout($layout_id, $value_id);
            if (is_null($dati)) {

                $pagina = $this->load->view("pages/layout_unaccessible", null, true);
                $this->layout->setLayoutModule();
                $this->stampa($pagina, $value_id);
            } else {

                // I have 2 type of layouts: PDF or standard
                if ($dati['layout_container']['layouts_pdf'] == DB_BOOL_TRUE) {
                    if (file_exists(FCPATH . "application/views/custom/layout/pdf.php")) {
                        $view_content = $this->load->view("custom/layout/pdf", array('dati' => $dati, 'value_id' => $value_id), true);
                    } else {
                        $view_content = $this->load->view("layout/pdf", array('dati' => $dati, 'value_id' => $value_id), true);
                    }

                    $orientation = $this->input->get('orientation') ? $this->input->get('orientation') : 'portrait';
                    $pdfFile = $this->layout->generate_pdf($view_content, $orientation, "", [], false, true);

                    $contents = file_get_contents($pdfFile, true);
                    $pdf_b64 = base64_encode($contents);

                    $file_name = url_title($dati['layout_container']['layouts_title'], '-', true) . '_';

                    header('Content-Type: application/pdf');
                    header('Content-disposition: inline; filename="' . $file_name . time() . '.pdf"');
                    $this->layout->setLayoutModule();
                    echo base64_decode($pdf_b64);
                } else {
                    $dati['title_prefix'] = trim(implode(', ', array_filter([$dati['layout_container']['layouts_title'], $dati['layout_container']['layouts_subtitle']])));
                    $dati['current_page'] = "layout_{$layout_id}";
                    $dati['related_entities'] = $this->layout->getRelatedEntities();
                    $dati['show_title'] = true;
                    $dati['layout_id'] = $layout_id;
                    $pagina = $this->load->view("pages/layout", compact('dati', 'value_id'), true);

                    $this->layout->setLayoutModule();
                    $this->stampa($pagina, $value_id);
                }
            }
        }
    }

    /**
     * Custom page
     * ---
     * @param string $page
     */
    public function custom($page)
    {
        // Only admin can use this method
        if (!$this->datab->is_admin()) {
            die("Only Admin can use this method!");
        }
        // Load custom view if exists
        if (file_exists(FCPATH . "application/views_adminlte/custom/$page.php")) {
            $pagina = $this->load->view("custom/$page", null, true);
            $this->stampa($pagina);
        } else {
            die("Oh no! Your custom view does not exists in custom directory.");
        }
    }

    /**
     * Template renderer
     * ---
     * Accept a string in html format
     *
     * @param string $pagina
     */
    protected function stampa($pagina, $value_id = null)
    {

        $this->output->setTags($this->layout->getRelatedEntities());

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/head.php")) {
            $this->template['head'] = $this->load->view('custom/layout/head', array(), true);
        } else {
            $this->template['head'] = $this->load->view('layout/head', array(), true);
        }

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/header.php")) {
            $this->template['header'] = $this->load->view('custom/layout/header', array(), true);
        } else {
            $this->template['header'] = $this->load->view('layout/header', array(), true);
        }

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/sidebar.php")) {
            $this->template['sidebar'] = $this->load->view('custom/layout/sidebar', array(), true);
        } else {
            $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        }

        $this->template['page'] = $pagina;

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/footer.php")) {
            $this->template['footer'] = $this->load->view('custom/layout/footer', null, true);
        } else {
            $this->template['footer'] = $this->load->view('layout/footer', null, true);
        }

        foreach ($this->template as $key => $html) {
            $this->template[$key] = $this->layout->replaceTemplateHooks($html, $value_id);
        }

        $this->load->view('layout/main', $this->template);
    }
    public function print_barcode($type)
    {
        $val = $this->input->get('val');
        $w = $this->input->get('w');
        $h = $this->input->get('h');
        $left = $this->input->get('left');
        $top = $this->input->get('top');
        $val = base64_decode($val);
        $this->load->view('box/grid/barcode_print', compact('type', 'val', 'w', 'h', 'left', 'top'));
    }
    /**
     * Form rendering
     */
    public function form($form_id, $value_id = null)
    {
        $form = $this->datab->get_form($form_id, $value_id);
        if (!$form) {
            $this->stampa($this->load->view("box/errors/missing_form", ['form_id' => $form_id], true));
            return;
        }

        if ($this->datab->can_write_entity($form['forms']['forms_entity_id'])) {
            // Get form view
            $formHtml = $this->load->view("pages/layouts/forms/form_{$form['forms']['forms_layout']}", [
                'form' => $form,
                'ref_id' => 'test',
                'value_id' => $value_id,
                'layout_data_detail' => null,
            ], true);

            // Get hooks
            $preFormHtml = $this->datab->getHookContent('pre-form', $form_id, $value_id);
            $postFormHtml = $this->datab->getHookContent('post-form', $form_id, $value_id);

            // ...
            $content = $preFormHtml . $formHtml . $postFormHtml;
        } else {
            // Cannot write to entity
            $content = str_repeat('&nbsp;', 3) . t('You are not allowed to do this.');
        }

        if ($this->input->get('_raw')) {
            echo $content;
        } else {
            $this->stampa($content, $value_id);
        }
    }

    /**
     * Log CRM page
     */
    public function system_log()
    {
        if (!$this->datab->is_admin()) {
            $pagina = '<h1 style="color: #cc0000;">Permission denied</h1>';
            $this->stampa($pagina);
            return;
        }

        $dati['current_page'] = 'system_log';

        $pagina = $this->load->view("pages/system_log", array('dati' => $dati), true);
        $this->stampa($pagina);
    }
    public function support_tables()
    {
        if (!$this->datab->is_admin()) {
            $pagina = '<h1 style="color: #cc0000;">Permission denied</h1>';
            $this->stampa($pagina);
            return;
        }

        $dati['current_page'] = 'support_tables';
        //Get all grids related to support tables
        $grids = $this->db
            ->where('entity_type', ENTITY_TYPE_SUPPORT_TABLE)
            ->join('entity', 'entity_id = grids_entity_id', 'LEFT')
            ->order_by('entity_name')
            ->get('grids')
            ->result_array();
        $grids_html = [];
        foreach ($grids as $griddb) {
            $fooBox = [
                'layouts_boxes_content_type' => 'grid',
                'layouts_boxes_content_ref' => $griddb['grids_id'],
            ];
            $html = $this->datab->getBoxContent($fooBox);
            $grids_html[$griddb['grids_id']] = $html;
        }
        $dati['grids'] = $grids;
        $dati['grids_html'] = $grids_html;
        $pagina = $this->load->view("pages/support_tables", array('dati' => $dati), true);
        $this->stampa($pagina);
    }

    /*
     * General settings
     */
    public function settings()
    {
        $dati['current_page'] = 'settings';

        //Get all settings layout
        $layouts = $this->db
            ->where('layouts_settings', DB_BOOL_TRUE)
            ->join('modules', 'layouts_module = modules_identifier', 'LEFT')
            ->order_by('layouts_title')
            ->get('layouts')
            ->result_array();

        foreach ($layouts as $layout) {
            $dati['settings_layout'][$layout['modules_name']][] = $layout;
        }

        $pagina = $this->load->view("pages/settings", array('dati' => $dati), true);
        $this->stampa($pagina);
    }
    /**
     * Translations page
     */
    // Configure your module
    public $LogPath = "../../../logs";
    public function setPath($path)
    {
        $this->LogPath = $path;
    }
    private function getPath()
    {
        if (is_dir($this->LogPath)) {
            return $this->LogPath;
        } else {
            die("Log directory: " . $this->LogPath . " is not a valid dir");
        }
    }
    public function getFiles()
    {
        $path = $this->getPath();
        $files = scandir($path);
        $files = array_reverse($files);
        return array_values($files);
    }
    public function getLastLogFile()
    {
        $files = $this->getFiles();
        $path = $this->getPath();
        $last_file = $path . "/" . $files[0];
        if (is_file($last_file)) {
            return $path . "/" . $files[0];
        } else {
            return false;
        }
    }
    public function getLastLogs()
    {
        // Get files and open the lastest
        $logFile = $this->getLastLogFile();
        if ($logFile) {
            $lines = file($logFile);
            return $lines;
        } else {
            return false;
        }
    }
    public function translations()
    {
        $dati['current_page'] = 'translations';
        $data['settings'] = $this->db->query("SELECT * FROM settings LEFT JOIN languages ON settings_default_language = languages_id")->row_array();
        $data['languages'] = $this->db->query("SELECT * FROM languages")->result_array();
        /* Extract logs */
        $path = FCPATH . "application/logs";
        $files = scandir($path);
        $files = array_reverse($files);
        $log_files = array_values($files);
        $last_file = $path . "/" . $log_files[0];
        if (is_file($last_file)) {
            $logFile = $path . "/" . $files[0];
            $data['log_lines'] = file($logFile);
        } else {
            $data['log_file_error'] = "Log file not found";
        }
        $page = $this->load->view("pages/translations", array('data' => $data), true);
        $this->stampa($page);
    }

    /**
     * Permissions page
     */
    public function permissions()
    {
        if (!$this->datab->is_admin()) {
            $pagina = '<h1 style="color: #cc0000;">Permission denied</h1>';
            $this->stampa($pagina);
            return;
        }

        $dati['current_page'] = 'permissions';

        // ===========
        // Sezione permessi
        $dati['groups'] = array_key_map($this->db->where('permissions_group IS NOT NULL AND permissions_user_id IS NULL')->get('permissions')->result_array(), 'permissions_group');

        $where = array();
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            // Se c'è un login field mi aspetto che sia un booleano e
            // dev'essere true
            $where[LOGIN_ACTIVE_FIELD] = DB_BOOL_TRUE;
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
        if (defined('LOGIN_ACTIVE_FIELD') && LOGIN_ACTIVE_FIELD) {
            if (defined('LOGIN_DELETED_FIELD') && LOGIN_DELETED_FIELD) {
                $this->db->where(LOGIN_DELETED_FIELD . " <> '" . DB_BOOL_TRUE . "'", null, false);
            }
            $users = $this->db->get_where(LOGIN_ENTITY, array(LOGIN_ACTIVE_FIELD => DB_BOOL_TRUE))->result_array();
        } else {
            $users = $this->db->get(LOGIN_ENTITY)->result_array();
        }

        // Crea un array di mappatura layout_id => ucwords(n. cognome)
        $userIds = array_key_map($users, LOGIN_ENTITY . '_id');
        $ucwordsUserNames = array_map(function ($user) {
            $n = isset($user[LOGIN_NAME_FIELD]) ? $user[LOGIN_NAME_FIELD] : '';
            $s = isset($user[LOGIN_SURNAME_FIELD]) ? $user[LOGIN_SURNAME_FIELD] : '';
            return ($n && $s) ? ucwords($n[0] . '. ' . $s) : $n . ' ' . $s;
        }, $users);
        $usersLayouts = array_combine($userIds, $ucwordsUserNames);

        // Crea un array di mappatura layout_id => ucfirst(layout_title)
        $dati['layouts'] = $this->db->order_by('layouts_module, layouts_title')->get('layouts')->result_array();

        //Fix per non prendere tutti gli utenti ma solo quelli che possono fare login
        if (defined('LOGIN_ACTIVE_FIELD') && !empty(LOGIN_ACTIVE_FIELD)) {
            $this->db->where("unallowed_layouts_user IN (SELECT " . LOGIN_ENTITY . "_id FROM " . LOGIN_ENTITY . " WHERE " . LOGIN_ACTIVE_FIELD . " = '" . DB_BOOL_TRUE . "')", null, false);
        }

        $unalloweds = $this->db->get('unallowed_layouts')->result_array();
        $dati['unallowed'] = array();

        $dati['userGroupsStatus'] = $userGroupsStatus = $this->datab->getUserGroups(); // Un array dove per ogni utente ho il gruppo corrispondente
        $dati['users_layout'] = [];
        foreach ($usersLayouts as $userId => $userPreview) {
            if (isset($userGroupsStatus[$userId])) {
                $dati['users_layout'][$userGroupsStatus[$userId]] = ucwords($userGroupsStatus[$userId]);
            } else {
                $dati['users_layout'][$userId] = $userPreview;
            }
        }

        foreach ($unalloweds as $unallowedLayout) {
            $layout = $unallowedLayout['unallowed_layouts_layout'];
            $user = $unallowedLayout['unallowed_layouts_user'];

            if (isset($userGroupsStatus[$user]) && $userGroupsStatus[$user]) {
                $dati['unallowed'][$userGroupsStatus[$user]][] = $layout;
            } else {
                $dati['unallowed'][$user][] = $layout;
            }
        }

        uksort($dati['users_layout'], function ($k1, $k2) {
            $isGroupK1 = !is_numeric($k1);
            $isGroupK2 = !is_numeric($k2);

            if ($isGroupK1 == $isGroupK2) {
                return ($k1 < $k2) ? -1 : 1;
            } elseif ($isGroupK1) {
                return -1;
            } else {
                return 1;
            }
        });

        $pagina = $this->load->view("pages/permissions", array('dati' => $dati), true);
        $this->stampa($pagina);
    }

    /**
     * Permissions page
     */
    public function cache_manager()
    {
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) {
            $this->output->cache(0);
        }
        if (!$this->datab->is_admin()) {
            $pagina = '<h1 style="color: #cc0000;">Permission denied</h1>';
            $this->stampa($pagina);
            return;
        }

        $dati['current_page'] = 'cache_manager';
        $current_config = $this->mycache->getCurrentConfig();
        $disk_space = $this->mycache->getDiskSpace();
        $modified_dates = $this->mycache->getModifiedDate();
        $dati['caches'] = [
            'database_schema' => [
                'status' => 1,
                'label' => 'Database schema',
                'last_update' => $modified_dates['database_schema'],
                'space' => $disk_space['database_schema'],
                'active' => (!empty($current_config['database_schema']['active'])),
                'driver' => 'File',
            ],
            'apilib' => [
                'status' => 1,
                'label' => 'Apilib',
                'last_update' => $modified_dates['apilib'],
                'space' => $disk_space['apilib'],
                'active' => (!empty($current_config['apilib']['active'])),
                'driver' => 'File',
            ],
            'raw_queries' => [
                'status' => 1,
                'label' => 'Database raw queries',
                'last_update' => $modified_dates['raw_queries'],
                'space' => $disk_space['raw_queries'],
                'active' => (!empty($current_config['raw_queries']['active'])),
                'driver' => 'File',
            ],
            'full_page' => [
                'status' => 1,
                'label' => 'Full pages',
                'last_update' => $modified_dates['full_page'],
                'space' => $disk_space['full_page'],
                'active' => (!empty($current_config['full_page']['active'])),
                'driver' => 'File',
            ],
            'template_assets' => [
                'status' => 1,
                'label' => 'Template assets',
                'last_update' => $modified_dates['template_assets'],
                'space' => $disk_space['template_assets'],
                'active' => (!empty($current_config['template_assets']['active'])),
                'driver' => 'File',
            ],
        ];

        $pagina = $this->load->view("pages/cache_manager", array('dati' => $dati), true);
        $this->stampa($pagina);
    }

    public function cache_switch_active($key, $val)
    {
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) {
            $this->output->cache(0);
        }
        $this->mycache->switchActive($key, $val);
    }
    /**
     * Generic search page
     */
    public function search()
    {
        $dati['current_page'] = 'search';
        $dati['search_string'] = $this->input->get_post('search');

        if (strlen($dati['search_string']) >= (defined('MIN_SEARCH_CHARS')) ? MIN_SEARCH_CHARS : 3) {
            $dati['count_total'] = 0;
            $dati['results'] = $this->datab->get_search_results($dati['search_string']);
            foreach ($dati['results'] as $res) {
                $dati['count_total'] += count($res['data']);
            }
        } else {
            $dati['count_total'] = -1;
        }

        if ($this->input->is_ajax_request()) {
            echo json_encode($dati);
            return;
        }

        if ($dati['count_total'] === 1) {
            $results = array_values($dati['results']);
            $entity_result = $results[0];
            $link = $this->datab->get_detail_layout_link($entity_result['entity']['entity_id']);

            if ($link) {
                $data_results = array_values($entity_result['data']);
                $data = $data_results[0];
                redirect($link . '/' . $data[$entity_result['entity']['entity_name'] . '_id']);
            }
        }

        $pagina = $this->load->view("pages/search_results", array('dati' => $dati), true);
        $this->stampa($pagina);
    }

    /**
     * Trash results
     */
    public function trash()
    {
        if ($this->auth->is_admin()) {
            $dati['current_page'] = 'trash';
            $dati['count_total'] = "1";
            $dati['results'] = $this->datab->get_trash_results();

            $pagina = $this->load->view("pages/trash", array('dati' => $dati), true);
            $this->stampa($pagina);
        } else {
            set_status_header(403);
            die('Nope...not allowed');
        }
    }

    /**
     * Cache control
     * @param string $action Valid values for $action:
     *      - on    Enable cache (set a persistent cache driver)
     *      - off   Disable cache (set a dummy cache driver)
     *      - clear Clear all data from cache
     */
    public function cache_control($action = null, $key = null)
    {
        $redirection = false;
        $cache_controller_file = APPPATH . 'cache/cache-controller';
        switch ($action) {
            case 'on':
                $this->mycache->toggleCachingSystem(true);
                $redirection = base_url('main/cache_manager');
                break;

            case 'off':
                $fp = fopen($cache_controller_file, "r+");

                if (flock($fp, LOCK_EX)) { // acquire an exclusive lock
                    $cache_controller = file_get_contents($cache_controller_file);
                    $this->mycache->clearCache(true);
                    ftruncate($fp, 0); // truncate file
                    fwrite($fp, $cache_controller);
                    fflush($fp); // flush output before releasing the lock
                    flock($fp, LOCK_UN); // release the lock

                } else {
                    $this->mycache->clearCache(true);
                }
                @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);
                $this->mycache->toggleCachingSystem(false);
                fclose($fp);

                break;

            case 'clear':

                $fp = fopen($cache_controller_file, "r+");

                if (flock($fp, LOCK_EX)) { // acquire an exclusive lock
                    $cache_controller = file_get_contents($cache_controller_file);
                    $this->mycache->clearCache(true);
                    ftruncate($fp, 0); // truncate file
                    fwrite($fp, $cache_controller);
                    fflush($fp); // flush output before releasing the lock
                    flock($fp, LOCK_UN); // release the lock

                } else {
                    $this->mycache->clearCache(true);
                }
                @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);
                fclose($fp);

                break;

            default:
                show_error('Action non definita');
        }
        if (!$redirection) {
            $redirection = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_VALIDATE_URL);

        }
        redirect($redirection ?: base_url());
    }

    /**
     * Show a phpinfo
     */
    public function phpinfo()
    {
        if ($this->auth->is_admin()) {
            phpinfo();
        } else {
            set_status_header(403);
            die('Nope...not allowed');
        }
    }

    /**
     * Download language files
     */
    public function dwlangfiles()
    {
        if (!$this->auth->is_admin()) {
            set_status_header(403);
            die('Nope...not allowed');
        }

        $langs = $this->datab->getAllLanguages();
        $paths = [];

        foreach ($langs as $lang) {
            $path = sprintf('%slanguage/%s/%s_lang.php', APPPATH, $lang['file'], $lang['file']);
            if (file_exists($path)) {
                $paths[$lang['file']] = $path;
            }
        }

        if (!$paths) {
            die('Nessun file di lingua da scaricare');
        }

        $zippath = FCPATH . 'uploads/_tmp_langs.zip';
        file_put_contents($zippath, ''); // Mi assicuro che il file esista, perché se non esiste per qualche strano motivo ZipArchive non riesce a crearmelo.. mah
        $zip = new ZipArchive();

        if (($code = $zip->open($zippath, ZipArchive::OVERWRITE)) !== true) {
            show_error("Impossibile creare il file delle lingue (err. #{$code})");
        }

        foreach ($paths as $lang => $path) {
            $zip->addFile($path, "{$lang}_lang.php");
        }

        $zip->close();

        $zipcontent = file_get_contents($zippath);
        $zipname = 'language_files.zip';

        @unlink($zippath);

        if (!$zipcontent) {
            die(htmlentities('Non è stato possibile generare il file di lingue'));
        }

        $this->load->helper('download');
        if (!force_download($zipname, $zipcontent)) {
            die(htmlentities('Non è stato possibile scaricare il file di lingue '));
        }
    }

    /**
     * Session clearer
     */
    public function clear_session($logout = 0)
    {
        $user_id = $this->auth->get(LOGIN_ENTITY . '_id');
        $this->session->sess_destroy();

        if (!$logout) {
            $this->auth->login_force($user_id);
        }

        redirect(base_url());
    }

    public function custom_view_to_pdf($view, $orientation = "landscape", $html = false)
    {
        if ($html) {
            die($content);
        } else {
            $relative_path = ($this->input->get('relative_path')) ?: '';

            $pdfFile = $this->layout->generate_pdf($view, $orientation, $relative_path);

            // Send the file
            $fp = fopen($pdfFile, 'rb');
            header("Content-Type: application/pdf");
            header("Content-Length: " . filesize($pdfFile));
            fpassthru($fp);
        }
    }
    public function initialize_permissions()
    {
        $tipologia = $this->db->query("SELECT DISTINCT users_type FROM users")->result_array();
        $dashobard = $this->apilib->searchFirst('layouts', ['layouts_dashboardable' => 1]);
        foreach ($tipologia as $tipologia_user) {
            if ($tipologia_user) {
                $user_type = $tipologia_user['users_type'];
                if ($user_type) {
                    $tipologia = $this->apilib->view('users_type', $user_type);
                    $permissions = $this->db->get_where('permissions', array('permissions_group' => $tipologia['users_type_value']));
                    if ($permissions->num_rows() == 0) {
                        //non c'è in permission, quindi vado a creare la riga
                        $dati_db['permissions_admin'] = 0;
                        $dati_db['permissions_group'] = $tipologia['users_type_value'];
                        //prima il campo users_id vuoto così da inizializzarlo
                        $this->db->insert('permissions', $dati_db);
                        //per ogni utente che ha quel permesso, lo vado ad impostare
                        $users = $this->db->query("SELECT users_id FROM users WHERE users_type = '$user_type'")->result_array();
                        foreach ($users as $user) {
                            if ($user['users_id']) {
                                $dati_db['permissions_user_id'] = $user['users_id'];
                                $this->db->insert('permissions', $dati_db);
                                //ora imposto i permessi su 0 a tutto, a parte la dashboard iniziale
                                $dati_permissions['unallowed_layouts_user'] = $user['users_id'];
                                $layouts = $this->db->query("SELECT layouts_id FROM layouts")->result_array();
                                foreach ($layouts as $layout) {
                                    //escludo il primo dashboard layout
                                    if ($layout['layouts_id'] != $dashobard['layouts_id']) {
                                        $dati_permissions['unallowed_layouts_layout'] = $layout['layouts_id'];
                                        $this->db->insert('unallowed_layouts', $dati_permissions);
                                    }
                                }
                            }
                        }
                    }
                    echo json_encode(array('status' => 2, 'txt' => "Permessi inizializzati correttamente."));
                }
            }
        }
    }
}
