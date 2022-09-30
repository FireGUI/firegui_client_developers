<?php

class Guest extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
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
        if ($value_id || (!is_numeric($value_id) && !is_null($value_id))) {
            $value_id = null;
        }
        
        // Se non posso accedere a questo layout, allora mostro la pagina con il
        // messaggio: "La pagina da te cercata non esiste, oppure non hai i
        // permessi per accedervi"
        if (!$this->datab->can_access_layout($layout_id, $value_id)) {
            redirect();
        } else {
            
            //If layout is module dependent, preload translations
    
            if (!$this->db->table_exists('users_manager_configurations')) {
                redirect();
            }
    
            $salt = $this->db->get('users_manager_configurations')->row();
    
            $layout = $this->layout->getLayout($layout_id);
    
            $realHash = substr(md5($layout_id.$salt->users_manager_configurations_salt), 0, 6);
            if ($layout['layouts_is_public'] == DB_BOOL_FALSE && empty($salt->users_manager_configurations_salt) && (empty($this->input->get('token')) || $this->input->get('token') !== $realHash)) {
                redirect();
            }
    
            if ($layout['layouts_module']) {
                $this->lang->language = array_merge($this->lang->language, $this->module->loadTranslations($layout['layouts_module'], @array_values($this->lang->is_loaded)[0]));
                $this->layout->setLayoutModule($layout['layouts_module']);
            }
            
            // Build layout, if null then layout is not accessible due to user permissions
            $dati = $this->datab->build_layout($layout_id, $value_id);
            if (is_null($dati)) {
                redirect();
                // $pagina = $this->load->view("pages/layout_unaccessible", null, true);
                // $this->layout->setLayoutModule();
                // $this->stampa($pagina, $value_id);
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
        
        // if (file_exists(FCPATH . "application/views_adminlte/custom/layout/header.php")) {
        //     $this->template['header'] = $this->load->view('custom/layout/header', array(), true);
        // } else {
        //     $this->template['header'] = $this->load->view('layout/header', array(), true);
        // }
        
        // if (file_exists(FCPATH . "application/views_adminlte/custom/layout/sidebar.php")) {
        //     $this->template['sidebar'] = $this->load->view('custom/layout/sidebar', array(), true);
        // } else {
        //     $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        // }
        
        $this->template['page'] = $pagina;
        
        // if (file_exists(FCPATH . "application/views_adminlte/custom/layout/footer.php")) {
        //     $this->template['footer'] = $this->load->view('custom/layout/footer', null, true);
        // } else {
        //     $this->template['footer'] = $this->load->view('layout/footer', null, true);
        // }
    
        if (file_exists(FCPATH . "application/views_adminlte/custom/layout/foot.php")) {
            $this->template['foot'] = $this->load->view('custom/layout/foot', null, true);
        } else {
            $this->template['foot'] = $this->load->view('layout/foot', null, true);
        }
        
        foreach ($this->template as $key => $html) {
            $this->template[$key] = $this->layout->replaceTemplateHooks($html, $value_id);
        }
        
        $this->load->view('layout/main_light', $this->template);
    }
}