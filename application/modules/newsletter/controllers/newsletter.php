<?php


class Newsletter extends MX_Controller {
    

    var $template = array();
    var $settings = NULL;
    var $headers = array("MIME-Version: 1.0", "Content-type: text/html; charset=iso-8859-1");
    
    
    public function __construct() {
        parent::__construct();
        if($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Module not installed');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Access forbidden');
        }
        
        $this->load->model('docs');
        $this->settings = $this->db->get(ENTITY_SETTINGS)->row_array();
    }
    
    
    public function index() {
        $dati['current_page'] = 'module_'.MODULE_NAME;
        $dati['templates'] = $this->db->get('newsletter_email_templates')->result_array();
        $dati['newsletters'] = $this->db->order_by('newsletter_date DESC')->get('newsletters')->result_array();
        $this->stampa('index', $dati);
    }
    
    public function write_mail($newsletter_source = null) {
        
        
        if(is_numeric($newsletter_source)) {
            $template_id = $newsletter_source;
            $newsletter = null;
        } elseif(is_array($newsletter_source)) {
            $template_id = null;
            $newsletter = $newsletter_source;
        } else {
            $template_id = null;
            $newsletter = null;
        }
        
        
        $dati['current_page'] = 'module_'.MODULE_NAME;
        
        $dati['mailing_lists'] = unserialize(MAILING_LISTS);
        $dati['templates'] = $this->db->get('newsletter_email_templates')->result_array();
        
        $tables = array();
        $dati['email_list'] = array();
        foreach ($dati['mailing_lists'] as $list) {
            $arr = explode('.', $list);
            $table = $arr[0];
            $field = $arr[1];
            $mails = $this->db->select($field)->distinct()->get($table)->result_array();
            $tables[] = $table;
            foreach ($mails as $mail) {
                $dati['email_list'][] = "&quot;{$mail[$field]}&quot;";
            }
        }
        
        $dati['email_list'] = implode(',', $dati['email_list']);
        
        if($newsletter) {
            
            $dati['newsletter'] = $newsletter;
            
            // Elimino gli headers
            $dati['newsletter']['newsletter_headers'] = trim(str_replace($this->headers, '', $newsletter['newsletter_headers']));
            
        } elseif($template_id) {
            $dati['template'] = $this->db->get_where('newsletter_email_templates', array('email_templates_id'=>$template_id))->row_array();
        }
        
        // Prendo le entità ignorando quelle già considerate nelle liste
        /*if(!empty($tables)) {
            $this->db->where_not_in('entity_name', $tables);
        }*/
        
        $dati['entities'] = $this->db->where("entity_id IN (SELECT fields_entity_id FROM fields WHERE fields_type = 'VARCHAR')")->get_where('entity', array('entity_type' => ENTITY_TYPE_DEFAULT, 'entity_visible' => 't'))->result_array();
        
        $this->stampa('compose_mail', $dati);
    }

    
    
    
    
    public function riproponi($newsletterID) {
        $qNewsletter = $this->db->get_where('newsletters', array('newsletter_id' => $newsletterID));
        
        if($qNewsletter->num_rows() > 0) {
            $this->write_mail($qNewsletter->row_array());
        } else {
            show_404();
        }
    }
    
    
    
    public function create_template($id=NULL) {
        $this->load->helper('text');
        
        
        $dati['current_page'] = 'module_'.MODULE_NAME;
        $dati['templates'] = $this->db->get('newsletter_email_templates')->result_array();
        
        if($id !== NULL) {
            $dati['template'] = $this->db->get_where('newsletter_email_templates', array('email_templates_id'=>$id))->row_array();
        }
        
        $this->stampa('create_template', $dati);
    }
    
    
    
    
    
    
    
    public function add_to_queue() {
        
        $this->load->library('form_validation');
        $valid = $this->form_validation
                ->set_rules('to', t('destinatario'), 'valid_emails')
                ->set_rules('subject', t('oggetto'), 'required')
                ->set_rules('mail', t('body'), 'required')->run();
        
        if( ! $valid ) {
            echo json_encode(array('status'=>0, 'txt'=>  validation_errors()));
        } else {
            $data = $this->input->post();
            
            if(empty($data['to'])) {
                $data['to'] = '';
            }
            
            if(empty($data['mailing_lists'])) {
                $data['mailing_lists'] = array();
            }
            
            // Il campo 'to' è corretto perché già validato (alla peggio è vuoto). Il resto lo prendi dal db...
            $emails_to = array();
            if($data['to']) {
                $emails_to = array_map(function($mail) {
                    return strtolower(trim($mail));
                }, explode(',', $data['to']));
            }
            
            foreach ($data['mailing_lists'] as $list) {
                $arr = explode('.', $list);
                $table = $arr[0];
                $field = $arr[1];
                $mails = $this->db->select($field)->distinct()->get($table)->result_array();
                foreach ($mails as $mail) {
                    if(filter_var($mail[$field], FILTER_VALIDATE_EMAIL) !== false) {
                        $emails_to[] = $mail[$field];
                    }
                }
            }
            
            $entity_mails = $this->input->post('entity_mails');
            if($entity_mails['entity'] && !empty($entity_mails['field'])) {
                if($entity_mails['filter']) {
                    $mails = $this->get_emails_in_field($entity_mails['field'], $entity_mails['filter_field'], $entity_mails['op_field'], $entity_mails['val_field'], $entity_mails['manual_where']);
                } else {
                    $mails = $this->get_emails_in_field($entity_mails['field']);
                }
                
                $emails_to = array_merge($emails_to, $mails);
            }
            
            
            $emails_to = array_unique(array_filter($emails_to));
            if(empty($emails_to)) {
                echo json_encode(array('status'=>0, 'txt'=>  t('inserisci degli indirizzi email oppure seleziona una mailing list')));
                die();
            }
            
            $headers = trim($data['headers']);
            
            $this->db->insert('newsletters', array(
                'newsletter_block_size' => empty($data['block_size'])? null: $data['block_size'],
                'newsletter_block_time' => empty($data['block_time'])? null: $data['block_time'],
                'newsletter_subject' => $data['subject'],
                'newsletter_content' => $data['mail'],
                'newsletter_headers' => ($headers? $headers.PHP_EOL: '') . implode(PHP_EOL, $this->headers)
            ));
            
            $newsletter_id = $this->db->insert_id();
            
            foreach ($emails_to as $mail) {
                if(filter_var($mail, FILTER_VALIDATE_EMAIL) !== false) {
                    $this->db->insert('newsletter_mail_queue', array(
                        'mail_newsletter' => $newsletter_id,
                        'mail_to' => $mail,
                    ));
                }
            }
            
            
            echo json_encode(array('status'=>1, 'txt'=> base_url('newsletter')));
        }
        //debug($data,1);
        //redirect(base_url('newsletter'));
    }
    
    
    public function save_template() {
        $data = $this->input->post();
        
        if(isset($data['email_templates_id'])) {
            $this->db->where('email_templates_id', $data['email_templates_id'])->update('newsletter_email_templates', $data);
        } else {
            $this->db->insert('newsletter_email_templates', $data);
        }
        
        redirect(base_url('newsletter/create_template'));
    }
    
    
    public function remove_template($id) {
        $this->db->delete('newsletter_email_templates', array('email_templates_id' => $id));
        redirect(base_url('newsletter/create_template'));
    }
    
    
    
    
    public function get_mailable_fields($entity_id) {
        
        $out = array();
        $entity = $this->db->get_where('entity', array('entity_id' => $entity_id))->row();
        
        if(isset($entity->entity_name)) {
            
            $length = strlen($entity->entity_name);
            $fields = $this->db->get_where('fields', array('fields_entity_id' => $entity_id, 'fields_type' => 'VARCHAR', 'fields_visible' => 't'))->result_array();

            foreach($fields as $field) {
                // Length + 1 così cava pure l'underscore
                $name = substr($field['fields_name'], $length+1);
                $out[] = array(
                    'id' => $field['fields_id'],
                    'name' => ucfirst(str_replace('_', ' ', $name)),
                );
            }
        }
        
        echo json_encode($out);
    }
    
    
    public function get_fields($entity_id) {
        
        $out = array();
        $entity = $this->db->get_where('entity', array('entity_id' => $entity_id))->row();
        
        if(isset($entity->entity_name)) {
            
            $length = strlen($entity->entity_name);
            $fields = $this->db->get_where('fields', array('fields_entity_id' => $entity_id, 'fields_visible' => 't'))->result_array();

            foreach($fields as $field) {
                // Length + 1 così cava pure l'underscore
                $name = substr($field['fields_name'], $length+1);
                $out[] = array(
                    'id' => $field['fields_id'],
                    'name' => ucfirst(str_replace('_', ' ', $name)),
                );
            }
        }
        
        echo json_encode($out);
    }
    
    
    
    public function count_emails($field_id) {
        echo count($this->get_emails_in_field($field_id, $this->input->post('filter_field'), $this->input->post('filter_op'), $this->input->post('filter_val'), $this->input->post('manual_where')));
    }
    
    
    
    
    private function get_emails_in_field($field_id, $whereField=null, $whereOp=null, $whereVal=null, $manualWhere = null) {
        
        $field = $this->db
                ->join('entity', 'entity_id = fields_entity_id')
                ->get_where('fields', array('fields_id' => $field_id))->row();
        
        
        if($whereField && $whereOp) {
            $whereField = $this->db->get_where('fields', array('fields_id' => $whereField))->row()->fields_name;
            

            switch($whereOp) {
                case 'ILIKE':
                    $this->db->where("{$whereField} {$whereOp} '%{$whereVal}%'");
                    break;
                
                case 'IN': case 'NOT IN':
                    $whereVal = trim($whereVal);
                    if($whereVal[0] == '(' && $whereVal[strlen($whereVal)-1] == ')') {
                        $whereVal = substr($whereVal, 1, count($whereVal)-2);
                    }
                    
                    $this->db->where("{$whereField} {$whereOp} ({$whereVal})");
                    break;
                
                default:
                    $this->db->where($whereField, $whereVal);
            }
        }
        
        
        if(!empty($manualWhere) && is_string($manualWhere)) {
            $this->db->where($manualWhere);
        }
        
        
        $data = $this->db->get($field->entity_name)->result_array();
        
        
        $return = array();
        foreach($data as $row) {
            $mail = $row[$field->fields_name];
            if ($mail && filter_var($mail, FILTER_VALIDATE_EMAIL) !== false && !in_array($mail, $return)) {
                $return[] = $mail;
            }
        }
        return $return;
    }
    
    
    
    
    
    
    
    

    private function stampa($view_file=NULL, $data=NULL) {
        $this->template['page'] = $this->load->view($view_file, array('dati'=>$data), true);
        
        $this->template['head'] = $this->load->view('layout/head', array(), true);
        $this->template['header'] = $this->load->view('layout/header', array(), true);
        $this->template['sidebar'] = $this->load->view('layout/sidebar', array(), true);
        $this->template['footer'] = $this->load->view('layout/footer', null, true);
        
        /*
         * Module-related assets extensions
         */
        $this->template['head'] .= $this->load->view('layout/module_css', array(), true);
        $this->template['footer'] .= $this->load->view('layout/module_js', null, true);
        
        //Build template
        $this->load->view('layout/main', $this->template);
    }
    
    
}