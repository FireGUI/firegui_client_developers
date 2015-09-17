<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Cron extends MY_Controller {

    const ENABLE_TRACKING = false;
    
    /*
     *  IL NOME E AMBIGUO MA CRONS SONO QUELLI GESTITI DINAMICAMENTE MENTRE I METODI run_ VENGONO CHIAMATI ESCLUSIVAMENTE DA CONSOLE
     */
    function run_mail_queue() {
        $this->emails->run_queue();
    }
    
    public function test_now($id, $fake = 1) {
        
        $this->db->trans_start();
        $cron = $this->db->get_where('crons', ['crons_id' => $id])->row_array();
        if (!$cron) {
            show_404();
        }
        
        $this->run($cron);
        
        if (!$fake) {
            $this->db->trans_complete();
        }
    }
    
    
    /*
     * 
     * CRONS 
     * 
     */
    // Cron effettivo
    public function check() {
        
        $cronKey = uniqid();
        
        if (self::ENABLE_TRACKING) {
            mail('alberto@h2-web.it', "Cron $cronKey start" . DEFAULT_EMAIL_SENDER, 'Data inizio: ' . date('Y-m-d H:i:s'));
        }
        
        // Precarico il file di cache per vedere IN QUESTO PUNTO quali cron sono
        // attivi in altri thread in modo da skipparli dopo
        $inExecution = $this->getInExecution();
        $crons = $this->db->query("SELECT * FROM crons WHERE crons_last_execution IS NULL OR crons_last_execution < now() - interval '1 minute' * crons_frequency");
        $skipped = $executed = [];

        foreach ($crons->result_array() as $cron) {

            // Essendo un update quello dell'attivazione del cron, non forzo
            // il sistema ad usarlo
            // 
            // Controllo anche se il cron_id era tra quelli in esecuzione
            // all'avvio del cron runner
            if ((isset($cron['crons_active']) && $cron['crons_active'] === 'f') OR in_array($cron['crons_id'], $inExecution)) {
                $skipped[] = $cron['crons_id'];
                continue;
            }
            
            // Marco l'inizio del cron impostando il last execution field e
            // ricordandolo come in esecuzione in cache
            $this->db->update('crons', array('crons_last_execution' => 'NOW()'), ['crons_id' => $cron['crons_id']]);
            $this->saveInExecution($cron['crons_id']);
            $this->run($cron);

            // Marco la fine del cron
            $executed[] = $cron['crons_id'];
            $this->noMoreInExecution($cron['crons_id']);
        }
        
        $allCrons = $this->db->get('crons')->result_array();
        $idxCrons = array_combine(array_key_map($allCrons, 'crons_id'), array_key_map($allCrons, 'crons_title'));
        
        ob_start();
        
        // =============== OUTPUT ===============
        echo '<pre>';
        echo 'Data fine: ', date('Y-m-d H:i:s');
        echo PHP_EOL, PHP_EOL, 'Attivi alla partenza', PHP_EOL;
        print_r(array_map(function($c) use($idxCrons) { return sprintf('(%s) %s', $c, $idxCrons[$c]); }, $inExecution));
        
        echo PHP_EOL, PHP_EOL, 'Eseguiti', PHP_EOL;
        print_r(array_map(function($c) use($idxCrons) { return sprintf('(%s) %s', $c, $idxCrons[$c]); }, $executed));
        
        echo PHP_EOL, PHP_EOL, 'Skippati', PHP_EOL;
        print_r(array_map(function($c) use($idxCrons) { return sprintf('(%s) %s', $c, $idxCrons[$c]); }, $skipped));
        echo '</pre>';
        // =============== OUTPUT ===============
        
        $out = ob_get_clean();
        echo $out;
        
        // Report via mail
        if (self::ENABLE_TRACKING) {
            mail('alberto@h2-web.it', "Cron $cronKey end " . DEFAULT_EMAIL_SENDER, strip_tags($out));
        }
    }
    
    
    private function run(array $cron) {
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
        $this->noMoreInExecution();
    }
    
    
    // ============================
    //  Sezione cache esecuzione
    //  cron
    // ============================
    
    private function getInExecution() {
        // Usiamo la cache per ricordare quali cron sono in esecuzione
        $this->load->driver('cache');
        $inExecution = $this->cache->file->get($this->getKey());
        return is_array($inExecution)? $inExecution: [];
    }
    
    private function saveInExecution($cronId) {
        $inExecution = $this->getInExecution();
        $inExecution[] = $cronId;
        $this->cache->file->save($this->getKey(), $inExecution, 240);
        return $inExecution;
    }
    
    private function noMoreInExecution($cronId = null) {
        if (is_numeric($cronId) && $cronId > 0) {
            $inExecution = array_filter($this->getInExecution(), function($execCronId) use($cronId) {
                return $execCronId != $cronId;
            });
            $this->cache->file->save($this->getKey(), $inExecution, 240);
            return $inExecution;
        } else {
            $this->cache->file->delete($this->getKey());
            return [];
        }
    }
    
    private function getKey() {
        return 'crons' . substr(sha1(__FILE__), 0, 6);
    }
    
    
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */