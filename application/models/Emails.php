<?php

class Emails extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function headers($from = null, $reply = null)
    {

        if ($from === null) {
            $from = DEFAULT_EMAIL_FROM;
        }
        if ($reply === null) {
            $reply = DEFAULT_EMAIL_REPLY;
        }
        $headers = $from . "\r\n";
        $headers .= $reply . "\r\n";
        $headers .= "Return-Path: " . DEFAULT_EMAIL_SYSTEM . "\r\n";
        $headers .= "Organization: Sender " . DEFAULT_EMAIL_SENDER . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0 \r\n";
        // Se si usano i boundary è da cambiare questo metodo
        $headers .= "Content-type: text/html; charset=utf-8 \r\n";
        $headers .= "Content-Transfer-Encoding: 8bit";


        return $headers;
    }

    public function generate_email_body($template, $content)
    {
        $file = 'application/views/tpl/' . $template . '.tpl';
        if (file_exists($file)) {
            $tpl_arr = file($file);
        } else {
            debug("NON TROVO IL TPL " . $file);
            return false;
        }

        //Estrapolo l'oggetto della mail
        $subject = $tpl_arr[1];

        //La prima riga è commento, la seconda è l'oggetto, la terza è commento ancora
        unset($tpl_arr[0], $tpl_arr[1], $tpl_arr[2]);

        //Estrapolo il contenuto
        $body = implode('<br />', $tpl_arr);

        //Rimpiazzo le variabili
        $new_data = array();
        foreach ($content as $key => $val) {
            $new_data['{' . $key . '}'] = $val;
        }
        $msg['subject'] = strtr($subject, array_merge(array('{BASE}' => base_url(), '{base_url}' => base_url()), $new_data));
        $msg['body'] = strtr($body, array_merge(array('{BASE}' => base_url(), '{base_url}' => base_url()), $new_data));

        return $msg;
    }

    public function add_queue($to, $subject, $body, $headers = null, $boundary = null)
    {
        if (!$headers) {
            $headers = $this->headers();
        }

        $mail = array(
            'mail_to' => $to,
            'mail_subject' => $subject,
            'mail_body' => $body,
            'mail_headers' => $headers,
            'mail_boundary' => $boundary
        );

        if ($this->db->insert('mail_queue', $mail))
            return TRUE;
        else
            return FALSE;
    }

    function mail($to, $subject, $message, $headers)
    {

        if (mail($to, $subject, $message, $headers)) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    /* 
     * INVIO LE MAIL IN CODA (verra chiamato da cron ogni tot tempo)
     */
    public function run_queue()
    {
        $mail_queue = $this->db->query("SELECT * FROM mail_queue WHERE mail_date_sent IS NULL ORDER BY mail_date ASC LIMIT 20");
        if ($mail_queue->num_rows() > 0) {
            $n = 0;
            foreach ($mail_queue->result_array() as $mail) {
                $return = $this->mail($mail['mail_to'], $mail['mail_subject'], $mail['mail_body'], $mail['mail_headers'], $mail['mail_boundary']);
                if ($return) {
                    $this->db->query("UPDATE mail_queue SET mail_date_sent = NOW() WHERE mail_id = '{$mail['mail_id']}'");
                    sleep(1);
                    $n++;
                } else {
                    $this->db->query("UPDATE mail_queue SET mail_log = 'Invio errato' WHERE mail_id = '{$mail['mail_id']}'");

                    // TODO SEGNALARE L ERRORE INVIO IN QUALCHE MODO
                }
            }
            debug('Processato invio per ' . $n++ . ' mail su un totale da inviare di ' . $mail_queue->num_rows());
        }
    }
}
