<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron extends CI_Controller {

    var $template = array();
    var $settings = NULL;

    function __construct() {
        parent :: __construct();
       
    }

    public function index() {
    }
    
    /*
     *  IL NOME E AMBIGUO MA CRONS SONO QUELLI GESTITI DINAMICAMENTE MENTRE I METODI run_ VENGONO CHIAMATI ESCLUSIVAMENTE DA CONSOLE
     */
    function run_mail_queue() {
        $this->emails->run_queue();
    }

    
    function testa() {
        mail('manuel.aiello@gmail.com', 'Ciao', 'Prova');
    }
    /*
     * 
     * CRONS 
     * 
     */
    // Cron effettivo
    public function check() {
        $crons = $this->db->query("SELECT * FROM crons WHERE crons_last_execution IS NULL OR crons_last_execution < now() - interval '1 minute' * crons_frequency");
        $crons_type = unserialize(CRON_TYPES);
        if ($crons->num_rows() > 0) {
            
            foreach ($crons->result_array() as $cron) {
                
                // Essendo un update quello dell'attivazione del cron, non forzo
                // il sistema ad usarlo
                if (empty($cron['crons_active']) OR $cron['crons_active'] === 'f') {
                    continue;
                }
                
                switch ($cron['crons_type']) {
                    case 'mail':
                        $this->cron_email($cron);
                    break;
                    case 'curl':
                        $this->cron_curl($cron);
                    break;
                    case 'php_file':
                        $this->cron_php_file($cron);
                    break;
                    case 'php_code':
                        $this->cron_php_code($cron);
                    break;
                    default:
                       echo "Type: ".$cron['crons_type']." Non gestito";
                    break;
                }
                $this->db->where('crons_id', $cron['crons_id']);
                $this->db->update('crons', array('crons_last_execution' => 'NOW()'));
            }
            
        }
    }
    
    
    public function cron_php_code($cron) {
        eval($cron['crons_text']);
    }
    
    public function cron_php_file($cron) {
        $file = realpath(dirname(__FILE__).'/../../'.$cron['crons_file']);
        if($file === FALSE) {
            die("Il file '{$cron['crons_file']}' non esiste.");
        } else {
            include $file;
        }
    }
    
    public function cron_curl($cron) {
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $cron['crons_file']); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $output = curl_exec($ch); 
        curl_close($ch);
    }
    
    public function cron_email($cron) {
        //$this->output->enable_profiler(TRUE);
        $crons_fields = $this->db->query("SELECT * FROM crons_fields LEFT JOIN fields ON crons_fields.crons_fields_fields_id = fields.fields_id WHERE crons_fields_crons_id = '{$cron['crons_id']}'")->result_array();
        $data_entity = $this->datab->get_data_entity($cron['crons_entity_id'], 0, $cron['crons_where']);
        
        $events = array();
         foreach ($data_entity['data'] as $dato) {
            $ev = array();
            foreach ($crons_fields as $field) {
                $ev[$field['crons_fields_type']] = $dato[$field['fields_name']];
            }
            $events[] = $ev;
         }
         
         foreach ($events as $evento) {
            if (!$ev['mailto']) {
                continue;
            }
            // Genero il messaggio con i tpl che mi fa i replace automatici
            $msg = $this->emails->generate_email_body('crons/mail', $evento);
            // Aggiungo alla coda
            $this->emails->add_queue($ev['mailto'], $cron['crons_title'], $msg['body']);
         }
         // Devo rifare la query con i campi convertiti, così se nel where c'è scritto {date} mi va a prendere il campo corrispondente
    }
    
    
    
    
    public function reset_cron_history() {
        $this->db->update('crons', array('crons_last_execution' => NULL));
    }
    
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */