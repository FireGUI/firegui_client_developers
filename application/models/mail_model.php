<?php


class Mail_model extends CI_Model {
    
    /** @var string */
    private $subject;


    public function send($to = '', $key = '', $lang = '', $data = array(), $additional_headers = array()) {
        $email = $this->db->get_where('emails', array('emails_key' => trim($key), 'emails_language' => $lang))->row_array();
        if(empty($email)) {
            return false;
        }
        
        $headers = array_merge(
            array_filter(unserialize($email['emails_headers'])),
            array_filter($additional_headers)
        );
        
        $replace_from = empty($data)? array(): array_map(function($key) { return '{'.$key.'}'; }, array_keys($data));
        $replace_to = empty($data)? array(): array_values($data);
        $subject = str_replace($replace_from, $replace_to, $email['emails_subject']);
        $message = str_replace($replace_from, $replace_to, $email['emails_template']);
        return $this->sendEmail($to, $headers, $subject, $message);
    }
    
    public function sendFromView($to, $path, array $data = array(), $additional_headers = array()) {
        $message = $this->load->view($path, array('data' => $data), true);
        
        if (empty($this->subject) OR !is_string($this->subject)) {
            $subject = str_replace(array('_', '-'), ' ', pathinfo($path, PATHINFO_FILENAME));
        } else {
            $subject = $this->subject;
            $this->subject = null;
        }
        
        return $this->sendEmail($to, $additional_headers, $subject, $message);
    }
    
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    
    private function sendEmail($to, array $headers, $subject, $message) {
        $this->load->library('email');
        // -----------
        $this->email->set_mailtype('html');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message('<html>'.$message.'</html>');
        
        // Prepend the default headers
        $defaultHeaders = $this->config->item('email_headers');
        $headers = array_merge($defaultHeaders?:array(), $headers);

        // Setup standard headers
        if(isset($headers['From'])) {
            $this->email->from($headers['From']);
            unset($headers['From']);
        }
        
        if(isset($headers['Reply-To'])) {
            $this->email->reply_to($headers['Reply-To']);
            unset($headers['Reply-To']);
        }
        
        if(isset($headers['Cc'])) {
            $this->email->cc($headers['Cc']);
            unset($headers['Cc']);
        }
        
        if(isset($headers['Bcc'])) {
            $this->email->bcc($headers['Bcc']);
            unset($headers['Bcc']);
        }
        
        return $this->email->send();
    }
    
}

