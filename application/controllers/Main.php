<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Main extends MY_Controller
{

    /**
     * Controller constructor
     */
    function __construct()
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

        // Carica la dashboard - prendi il primo layout `dashboardable` accessibile dall'utente
        $layouts = $this->db->order_by('layouts_id')->get_where('layouts', array('layouts_dashboardable' => DB_BOOL_TRUE))->result_array();
        foreach ($layouts as $layout) {
            if ($this->datab->can_access_layout($layout['layouts_id'])) {
                $this->layout($layout['layouts_id']);
                return;
            }
        }

        show_error('Nessun layout Dashboard trovato.');
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
            $result = $this->db->where('layouts_identifier', $layout_id)->get('layouts');

            //debug($this->db->last_query(),true);

            if ($result->num_rows() == 0) {
                show_error("Layout '$layout_id' non trovato!");
            } else {
                $layout_id = $result->row()->layouts_id;
            }
        }

        // $value_id ha senso sse è un numero
        if (!is_numeric($value_id) && !is_null($value_id)) {
            $value_id = null;
        }

        // Se non posso accedere a questo layout, allora mostro la pagina con il
        // messaggio: "La pagina da te cercata non esiste, oppure non hai i
        // permessi per accedervi"
        if (!$this->datab->can_access_layout($layout_id)) {
            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
            $this->stampa($pagina);
            die();
        }

        // Costruisco il layout, e se ritorna null allora mostro layout non
        // accessibile
        $dati = $this->datab->build_layout($layout_id, $value_id);
        if (is_null($dati)) {
            $pagina = $this->load->view("pages/layout_unaccessible", null, true);
            $this->stampa($pagina);
            die();
        }

        // Altrimenti posso procedere con il rendering della pagina. Due casi:
        //  1) il layout è "pdffable"
        //  2) il layout è normale
        if ($dati['layout_container']['layouts_pdf'] == DB_BOOL_TRUE) {
            $content = $this->load->view("layout/pdf", array('dati' => $dati, 'value_id' => $value_id), true);

            // Load and render the pdf
            require_once('./class/html2pdf/html2pdf.class.php');
            $html2pdf = new HTML2PDF($this->input->get('orientation') ?: 'P', 'A4', 'it');
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->WriteHTML($content);

            $name = url_title($dati['layout_container']['layouts_title'], '-', true) . '.pdf';
            $html2pdf->Output($name, 'I'); // stampa il pdf nel browser
        } else {
            $dati['title_prefix'] = trim(implode(', ', array_filter([$dati['layout_container']['layouts_title'], $dati['layout_container']['layouts_subtitle']])));
            $dati['current_page'] = "layout_{$layout_id}";
            $dati['show_title'] = true;
            $pagina = $this->load->view("pages/layout", compact('dati', 'value_id'), true);
            $this->stampa($pagina);
        }
    }

    /**
     * Template renderer
     * ---
     * Accept a string in html format
     *
     * @param string $pagina
     */
    protected function stampa($pagina)
    {
        $this->template['head'] = $this->load->view('layout/head', array(), true);

        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/header.php")) {
            $this->template['header'] = $this->load->view('custom/layout/header', array(), true);
        } else {
            $this->template['header'] = $this->load->view('layout/header', array(), true);
        }


        $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        $this->template['page'] = $pagina;
        $this->template['footer'] = $this->load->view('layout/footer', null, true);

        echo $this->load->view('layout/main', $this->template, true);
    }

    /**
     * Esegue il render di un form
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
                'layout_data_detail' => null
            ], true);

            // Get hooks
            $preFormHtml = $this->datab->getHookContent('pre-form', $form_id, $value_id);
            $postFormHtml = $this->datab->getHookContent('post-form', $form_id, $value_id);

            // ...
            $content = $preFormHtml . $formHtml . $postFormHtml;
        } else {
            // Non posso scrivere sull'entità
            $content = str_repeat('&nbsp;', 3) . 'Non disponi dei permessi sufficienti per modificare i dati.';
        }

        if ($this->input->get('_raw')) {
            echo $content;
        } else {
            $this->stampa($content);
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
        $layouts = $this->db->order_by('layouts_title')->get('layouts')->result_array();
        $layoutIds = array_key_map($layouts, 'layouts_id');
        $ucfirsLayoutTitles = array_map(function ($layout) {
            return ucfirst(str_replace('_', ' ', $layout['layouts_title']));
        }, $layouts);

        $dati['layouts'] = array_combine($layoutIds, $ucfirsLayoutTitles);

        //Fix per non prendere tutti gli utenti ma solo quelli che possono fare login
        if (defined('LOGIN_ACTIVE_FIELD') && !empty(LOGIN_ACTIVE_FIELD)) {
            $this->db->where("unallowed_layouts_user IN (SELECT " . LOGIN_ENTITY . "_id FROM " . LOGIN_ENTITY . " WHERE " . LOGIN_ACTIVE_FIELD . " = '" . DB_BOOL_TRUE . "')", null, false);
        }

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
     * Cache control
     * @param string $action Valid values for $action:
     *      - on    Enable cache (set a persistent cache driver)
     *      - off   Disable cache (set a dummy cache driver)
     *      - clear Clear all data from cache
     */
    public function cache_control($action = null)
    {

        switch ($action) {
            case 'on':
                $this->apilib->toggleCachingSystem(true);
                break;

            case 'off':
                $cache_controller = file_get_contents(APPPATH . 'cache/cache-controller');
                $this->apilib->clearCache();
                file_put_contents(APPPATH . 'cache/cache-controller', $cache_controller);

                @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);

                
                $this->apilib->toggleCachingSystem(false);

                           
                break;

            case 'clear':
                // 20200310 - Michael E. - Fix che riscrive il file cache-controller resettato da $this->cache->clean() (funzione nativa di Codeigniter) in quanto se abilitata la cache (quindi scrive dei parametri sul file cache-controller) e si pulisce la cache, il file viene resettato e quindi la cache disattivata
                $cache_controller = file_get_contents(APPPATH . 'cache/cache-controller');
                $this->apilib->clearCache();
                file_put_contents(APPPATH . 'cache/cache-controller', $cache_controller);

                @unlink(APPPATH . 'cache/' . Crmentity::SCHEMA_CACHE_KEY);

                break;

            default:
                show_error('Action non definita');
        }

        $redirection = filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_VALIDATE_URL);
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
        file_put_contents($zippath, '');    // Mi assicuro che il file esista, perché se non esiste per qualche strano motivo ZipArchive non riesce a crearmelo.. mah
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

    public function custom_view_to_pdf($view, $orientation = "landscape", $html = FALSE)
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


    //    public function testLoop() {
    //        for ($i=0; $i < 1000; $i++) {
    //            $cliente = $this->apilib->searchFirst('clienti');
    //            $this->apilib->edit('clienti', $cliente['clienti_id'],['clienti_nome' => 'test']);
    //            echo_flush(' . ');
    //        }
    //    }

    //    public function test_load_view() {
    //        echo $this->load->module_view('documents/views', 'elfinder', [], true);
    //    }

}
