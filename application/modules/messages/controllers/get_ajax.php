<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Get_ajax extends MX_Controller {
    
    
    public $settings;
    
    
    public function __construct() {
        parent::__construct();
        if ($this->auth->guest()) {
            redirect('access');
        }

        if (!$this->datab->module_installed(MODULE_NAME)) {
            die('Module not installed');
        }

        if (!$this->datab->module_access(MODULE_NAME)) {
            die('Access forbidden');
        }

        $this->settings = $this->db->get('settings')->row_array();
    }
    
    
    
    
    
    
    public function dropdown_message_list() {
        
        $messages = $this->db->
                from(MESSAGES_TABLE)->
                join(MESSAGES_USER_TABLE, MESSAGES_USER_JOIN_FIELD.' = '.MESSAGES_TABLE_FROM_FIELD, 'left')->
                where(MESSAGES_TABLE_JOIN_FIELD, $this->auth->get(MESSAGES_USER_JOIN_FIELD))->
                order_by(MESSAGES_TABLE_DATE_FIELD)->
                get()->result_array();
        
        
        echo json_encode(array(
            'view' => $this->load->view('box/dropdown_item', array('messages'=>$messages), true),
            'count' => count($messages)
        ));
    }
    
    
    
    
    
    public function inbox() {
        $dati['messages'] = $this->db->
                from(MESSAGES_TABLE)->
                join(MESSAGES_USER_TABLE, MESSAGES_USER_JOIN_FIELD.' = '.MESSAGES_TABLE_FROM_FIELD, 'left')->
                where(MESSAGES_TABLE_JOIN_FIELD, $this->auth->get(MESSAGES_USER_JOIN_FIELD))->
                order_by(MESSAGES_TABLE_DATE_FIELD)->
                get()->result_array();
        
        $this->load->view('box/inbox_messages', array('dati'=>$dati));
    }
    
    
    
    
    public function compose($message_id = NULL) {
        $data['users'] = $this->db->order_by(MESSAGES_USER_TABLE.'_id')->get(MESSAGES_USER_TABLE)->result_array();
        if($message_id) {
            $data['message'] = $this->db->
                    from(MESSAGES_TABLE)->
                    join(MESSAGES_USER_TABLE, MESSAGES_USER_JOIN_FIELD.' = '.MESSAGES_TABLE_FROM_FIELD, 'left')->
                    where(MESSAGES_TABLE_JOIN_FIELD, $this->auth->get(MESSAGES_USER_JOIN_FIELD))->
                    where(MESSAGES_TABLE.'_id', $message_id)->
                    order_by(MESSAGES_TABLE_DATE_FIELD)->
                    get()->row_array();
        }
        
        
        $this->load->view('box/inbox_compose', array('data'=>$data));
    }
    
    
    
    public function message($message_id=null) {
        
        if($message_id) {
            $dati['message'] = $this->db->
                    from(MESSAGES_TABLE)->
                    join(MESSAGES_USER_TABLE, MESSAGES_USER_JOIN_FIELD.' = '.MESSAGES_TABLE_FROM_FIELD, 'left')->
                    where(MESSAGES_TABLE_JOIN_FIELD, $this->auth->get(MESSAGES_USER_JOIN_FIELD))->
                    where(MESSAGES_TABLE.'_id', $message_id)->
                    order_by(MESSAGES_TABLE_DATE_FIELD)->
                    get()->row_array();
            
            if(!empty($dati['message'])) {
                $this->load->view('box/inbox_message', array('dati'=>$dati));
            } else {
                echo "Message {$message_id} does not exist!";
            }

        }
    }
    
    
    
    /*public function recipient() {
        $this->db->like()->get()
        echo json_encode();
    }*/
    
    
}