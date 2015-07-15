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
        
        // Usa come replacement i parametri che non sono array, object e risorse
        $filteredData = array_filter($data, function($item) { return is_scalar($item); });
        
        $replace_from = empty($filteredData)? array(): array_map(function($key) { return '{'.$key.'}'; }, array_keys($filteredData));
        $replace_to = empty($filteredData)? array(): array_values($filteredData);
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
            $from = $this->prepareAddress($headers['From']);
            $this->email->from($from['mail'], $from['name']);
        }
        
        if(isset($headers['Reply-To'])) {
            $replyto = $this->prepareAddress($headers['From']);
            $this->email->reply_to($replyto['mail'], $replyto['name']);
        }
        
        if(isset($headers['Cc'])) {
            $this->email->cc($headers['Cc']);
        }
        
        if(isset($headers['Bcc'])) {
            $this->email->bcc($headers['Bcc']);
        }
        
        return $this->email->send();
    }
    
    
    public function prepareAddress($address) {
        
        $name = '';
        if (!filter_var($address, FILTER_VALIDATE_EMAIL) && preg_match( '/\<([^\<\>]*)\>/', $address)) {
            $ltpos = strpos($address, '<');
            $gtpos = strrpos($address, '>');
            $name = trim(substr($address, 0, $ltpos));
            $address = trim(substr($address, $ltpos+1, $gtpos-$ltpos-1));
        }
        
        $mail = strtolower(trim($address));
        return ['mail' => $mail, 'name' => $name];
    }
    
}

