<?php


class Mail_model extends CI_Model {
    
    /** @var string */
    private $subject;


    /**
     * Invia e-mail prendendola dai template su database
     * 
     * @param string $to
     * @param string $key
     * @param string $lang
     * @param array $data
     * @param array $additional_headers
     * @return bool
     */
    public function send($to = '', $key = '', $lang = '', array $data = [], array $additional_headers = []) {
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
        
        $replace_from = empty($filteredData)? []: array_map(function($key) { return '{'.$key.'}'; }, array_keys($filteredData));
        $replace_to = empty($filteredData)? []: array_values($filteredData);
        $subject = str_replace($replace_from, $replace_to, $email['emails_subject']);
        $message = str_replace($replace_from, $replace_to, $email['emails_template']);
        return $this->sendEmail($to, $headers, $subject, $message);
    }
    
    /**
     * Invia una mail prendendo il messaggio dalle view. L'oggetto può essere
     * impostato in 3 modi:
     *   1) Passandolo come argomento
     *   2) Usando $this->mail_model->setSubject('...');
     *   3) Automaticamente, a partire dal nome della view
     * 
     * @param string $to
     * @param string $path
     * @param array $data
     * @param array $additional_headers
     * @param string $subject
     * @return bool
     */
    public function sendFromView($to, $path, array $data = [], array $additional_headers = [], $subject = null) {
        
        $message = $this->load->view($path, ['data' => $data], true);
        
        if (!$subject) {
            if (empty($this->subject) OR !is_string($this->subject)) {
                $subject = str_replace(array('_', '-'), ' ', pathinfo($path, PATHINFO_FILENAME));
            } else {
                $subject = $this->subject;
                $this->subject = null;
            }
        }
        
        return $this->sendEmail($to, $additional_headers, $subject, $message);
    }
    
    /**
     * Imposta l'oggetto della mail per le e-mail da vista. Si può usare anche
     * da dentro il view file per motivi di traduzione
     * 
     * @param type $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }
    
    /**
     * Metodo per l'invio di messaggi via e-mail
     * 
     * @param type $to
     * @param type $subject
     * @param type $message
     * @param type $isHtml
     * @param array $additionalHeaders
     * @return bool
     */
    public function sendMessage($to, $subject, $message, $isHtml = false, array $additionalHeaders = []) {
        return $this->sendEmail($to, $additionalHeaders, $subject, $message, $isHtml);
    }
    
    
    
    
    /**
     * Metodo interno per invio mail
     * 
     * @param string $to
     * @param array $headers
     * @param string $subject
     * @param string $message
     * @param bool $isHtml
     * @return type
     */
    private function sendEmail($to, array $headers, $subject, $message, $isHtml = true) {
        
        // Ensure the email library is loaded
        $this->load->library('email');
        
        // HTML mail setup
        if ($isHtml) {
            
            $this->email->set_mailtype('html');
            
            if (function_exists('mb_convert_encoding')) {
                $message = mb_convert_encoding(str_replace('&nbsp;', ' ', $message), 'HTML-ENTITIES', 'UTF-8');
            }
            $message = '<html><body>' . $message . '</body></html>';
        }
        
        // Addinfo to the email
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);
        
        // Prepend the default headers
        $defaultHeaders = $this->config->item('email_headers');
        $headers = array_merge($defaultHeaders?:[], $headers);

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
        
        // Send and return the result
        return $this->email->send();
    }
    
    
    /**
     * Metodo interno per parsare gli indirizzi nel formato
     * 
     *          Nome <email@addr.ess>
     * 
     * Viene ritornato un array con chiavi
     *  - mail
     *  - name
     * 
     * @param string $address
     * @return array
     */
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

