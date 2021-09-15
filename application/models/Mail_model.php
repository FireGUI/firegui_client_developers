<?php

class Mail_model extends CI_Model
{

    /** @var string */
    private $subject;

    /** @var bool */
    private $deferred;

    /**
     * Class Constructor
     * Automatically fetch the deferred flag from configs
     */
    public function __construct()
    {
        parent::__construct();
        $this->resetDeferred();
    }

    /**
     * Ripristina l'opzione deferred come da config
     */
    public function resetDeferred()
    {
        $this->setDeferred($this->config->item('email_deferred'));
    }

    /**
     * Imposta l'invio come deferred, cioè inserisce nella coda le mail anziché
     * inviarle direttamente
     * 
     * @param bool $is_deferred
     */
    public function setDeferred($is_deferred)
    {
        $this->deferred = (bool) $is_deferred;
    }

    /**
     * L'invio è in differita?
     * @return bool
     */
    public function isDeferred()
    {
        return (true === $this->deferred);
    }

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
    public function send($to = '', $key = '', $lang = '', array $data = [], array $additional_headers = [], array $attachments = [])
    {

        if (is_development()) {
            $old_to = $to;
            $to = '*******@gmail.com';
            $headers_json = json_encode($additional_headers);
        }

        $email = $this->db->get_where('emails', array('emails_key' => trim($key), 'emails_language' => $lang))->row_array();

        if (empty($email)) {
            $email = $this->db->get_where('emails', array('emails_key' => trim($key)))->row_array();

            if (empty($email)) {
                return false;
            }
        }

        $headers = array_merge(
            array_filter(unserialize($email['emails_headers'])),
            array_filter($additional_headers)
        );

        // Usa come replacement i parametri che non sono array, object e risorse
        $filteredData = array_filter($data, 'is_scalar');
        $subject = str_replace_placeholders($email['emails_subject'],  $filteredData, true, true);
        $message = str_replace_placeholders($email['emails_template'], $filteredData, true, true);

        if (is_development()) { //Meglio questo dell'is_development
            $message = "(Messaggio da inviare a: {$old_to}) (headers: {$headers_json}) $message";
        }

        if ($this->isDeferred()) {

            return $this->queueEmail($to, $headers, $subject, $message); // @todo da mettere gli attachments
        } else {

            return $this->sendEmail($to, $headers, $subject, $message, true, [], $attachments);
        }
    }

    /**
     * Invia e-mail prendendola da un template passato come secondo parametro (un array key=>value con chiavi predefinite)
     * 
     * @param string $to
     * @param array $template Contiene un array chiave=>valore. Obbligatorie chiavi subject e template.
     * @param string $lang
     * @param array $data
     * @param array $additional_headers
     * @return bool
     */
    public function sendFromData($to = '', $template, array $data = [], array $additional_headers = [])
    {

        if (empty($template['subject']) || empty($template['template'])) {
            return false;
        }
        if (!empty($template['headers'])) {
            $headers = array_merge(
                array_filter(unserialize($template['headers'])),
                array_filter($additional_headers)
            );
        } else {
            $headers =                 array_filter($additional_headers);
        }


        // Usa come replacement i parametri che non sono array, object e risorse
        $filteredData = array_filter($data, 'is_scalar');
        $subject = str_replace_placeholders($template['subject'],  $filteredData);
        $message = str_replace_placeholders($template['template'], $filteredData);

        if ($this->isDeferred()) {

            return $this->queueEmail($to, $headers, $subject, $message);
        } else {

            return $this->sendEmail($to, $headers, $subject, $message);
        }
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
    public function sendFromView($to, $path, array $data = [], array $additional_headers = [], $subject = null)
    {

        $message = $this->load->view($path, ['data' => $data], true);

        if (!$subject) {
            if (empty($this->subject) or !is_string($this->subject)) {
                $subject = str_replace(array('_', '-'), ' ', pathinfo($path, PATHINFO_FILENAME));
            } else {
                $subject = $this->subject;
                $this->subject = null;
            }
        }
        //Verifico se è impostato email_deferred
        if ($this->isDeferred()) {
            return $this->queueEmail($to, $additional_headers, $subject, $message);
        } else {
            return $this->sendEmail($to, $additional_headers, $subject, $message);
        }
    }

    /**
     * Imposta l'oggetto della mail per le e-mail da vista. Si può usare anche
     * da dentro il view file per motivi di traduzione
     * 
     * @param type $subject
     */
    public function setSubject($subject)
    {
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
    public function sendMessage($to, $subject, $message, $isHtml = false, array $additionalHeaders = [])
    {
        //Verifico se è impostato email_deferred
        if ($this->isDeferred()) {
            return $this->queueEmail($to, $additionalHeaders, $subject, $message, $isHtml);
        } else {
            return $this->sendEmail($to, $additionalHeaders, $subject, $message, $isHtml);
        }
    }

    /**
     * Metodo interno per inserire le mail nella mail_queue
     * 
     * @param string $to
     * @param array $headers
     * @param string $subject
     * @param string $message
     * @param bool $isHtml
     * @return type
     */
    private function queueEmail($to, array $headers, $subject, $message, $isHtml = true)
    {
        $email_queue_data = [
            'mail_subject' => $subject,
            'mail_body' => $message,
            'mail_to' => $to,
            'mail_headers' => json_encode($headers),
            'mail_is_html' => ($isHtml) ? DB_BOOL_TRUE : DB_BOOL_FALSE,
            'mail_user' => $this->auth->get(LOGIN_ENTITY . '_id') ? $this->auth->get(LOGIN_ENTITY . '_id') : null
        ];

        return $this->db->insert('mail_queue', $email_queue_data);
    }

    /**
     * Metodo interno per invio mail
     * 
     * @param string $to
     * @param array $headers
     * @param string $subject
     * @param string $message
     * @param bool $isHtml
     * @param array $extra_data
     * @return type
     */
    function sendEmail($to, array $headers, $subject, $message, $isHtml = true, $extra_data = [], $attachments = [])
    {
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

        $headers = array_merge($defaultHeaders ?: [], $headers);

        // Setup standard headers
        if (isset($headers['From'])) {
            $from = $this->prepareAddress($headers['From']);
            $this->email->from($from['mail'], $from['name']);
        }

        if (isset($headers['Reply-To'])) {
            $replyto = $this->prepareAddress($headers['Reply-To']);
            $this->email->reply_to($replyto['mail'], $replyto['name']);
        }

        if (isset($headers['Cc'])) {
            $this->email->cc($headers['Cc']);
        }

        if (isset($headers['Bcc'])) {
            $this->email->bcc($headers['Bcc']);
        }

        if (isset($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                if (is_array($attachment) && !empty($attachment['file_name']) && !empty($attachment['file'])) {
                    $this->email->attach($attachment['file'], 'attachment', $attachment['file_name']);
                } else {
                    $this->email->attach($attachment);
                }
            }
        }

        // Send and return the result
        $sent = $this->email->send();
        $debug = $this->email->print_debugger();

        $this->email->clear(true);

        if (!$sent) {
            return $debug;
        } else {
            return $sent;
        }
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
    public function prepareAddress($address)
    {

        $name = '';
        if (!filter_var($address, FILTER_VALIDATE_EMAIL) && preg_match('/\<([^\<\>]*)\>/', $address)) {
            $ltpos = strpos($address, '<');
            $gtpos = strrpos($address, '>');
            $name = trim(substr($address, 0, $ltpos));
            $address = trim(substr($address, $ltpos + 1, $gtpos - $ltpos - 1));
        }

        $mail = strtolower(trim($address));
        return compact('mail', 'name');
    }

    /**
     * Invia le eventuali e-mail presenti in coda
     */
    function flushEmails($size = 5)
    {
        // Fetch e-mails
        $this->db->limit($size)->order_by('mail_date', 'asc');
        $this->db->where('mail_date_sent IS NULL');
        $emails = $this->db->get('mail_queue')->result_array();

        $emailsIds = array_key_map($emails, 'mail_id');
        if (!$emailsIds) {
            return;
        }


        // Segno la data di invio della coda in modo tale da proteggermi da
        // invii lenti, se sto tanto ad inviare, al prox cron non verranno 
        // prese le stesse email che sto processando con tutta la mia calma
        $this->db->where_in('mail_id', $emailsIds);
        $this->db->update('mail_queue', ['mail_date_sent' => date('Y-m-d H:i:s')]);

        // Ora ciclo tutte le email inviandole una ad una e aggiornando l'email
        // con la vera data di invio
        foreach ($emails as $email) {

            $to      = $email['mail_to'];
            $headers = json_decode($email['mail_headers'], true);
            $subject = $email['mail_subject'];
            $body    = $email['mail_body'];
            $is_html = $email['mail_is_html'] == DB_BOOL_TRUE;

            $is_sent = $this->sendEmail($to, $headers, $subject, $body, $is_html);

            // Salvo sempre la data di tentato invio e il log solo se l'invio è fallito
            $this->db->where('mail_id', $email['mail_id'])->update('mail_queue', [
                'mail_date_sent' => date('Y-m-d H:i:s'),
                'mail_log' => $is_sent ? null : $this->email->print_debugger(),
            ]);
        }
    }
}
