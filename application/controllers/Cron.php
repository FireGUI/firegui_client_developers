<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Cron extends MY_Controller
{
    const ENABLE_TRACKING = false;

    /**
     * Testa un cron per id
     * @param int $id
     * @param int $rollback
     */
    public function test_now($id, $rollback = 0)
    {
        if ($this->mycache->isCacheEnabled() && $this->mycache->isActive('full_page')) {
            $this->output->cache(0);
        }
        if (!$this->auth->is_admin()) {
            e('You must be logged in as admin to run cronjobs.');
        }

        $cron = $this->db->get_where('crons', ['crons_id' => $id])->row_array();
        $cron or show_404();    // Il cron deve esistere

        if ($rollback) {
            $this->db->trans_start(true);
        }

        $this->run($cron);

        if ($rollback) {
            $this->db->trans_rollback();
        }
    }


    /**
     * Esegui il cron
     */
    public function check()
    {

          // Save last execution on settings
        $check_col = $this->db->query("SHOW COLUMNS FROM settings LIKE 'settings_last_cron_check';");
        if ($check_col->num_rows() > 0) {
            $settings = $this->apilib->searchFirst('settings');
            $this->db->query("UPDATE settings SET settings_last_cron_check = NOW() WHERE settings_id = '{$settings['settings_id']}'");
        }


        $cronKey = uniqid();

        if (self::ENABLE_TRACKING) {
            mail(DEFAULT_EMAIL_SYSTEM, "Cron $cronKey start" . DEFAULT_EMAIL_SENDER, 'Data inizio: ' . date('Y-m-d H:i:s'));
        }

        // Precarico il file di cache per vedere IN QUESTO PUNTO quali cron sono
        // attivi in altri thread in modo da skipparli dopo
        $inExecution = $this->getInExecution();

        if ($this->db->dbdriver != 'postgre') {
            $crons = $this->db->query("SELECT * FROM crons WHERE crons_last_execution IS NULL OR DATE_FORMAT(crons_last_execution, '%Y-%m-%d %H:%i') <= DATE_FORMAT(DATE_SUB(NOW(), INTERVAL (1 * crons_frequency) MINUTE), '%Y-%m-%d %H:%i')");
        } else {
            $crons = $this->db->query("SELECT * FROM crons WHERE crons_last_execution IS NULL OR crons_last_execution < now() - interval '1 minute' * crons_frequency");
        }


        $skipped = $executed = [];

        foreach ($crons->result_array() as $cron) {

            // Essendo un update quello dell'attivazione del cron, non forzo
            // il sistema ad usarlo
            //
            // Controllo anche se il cron_id era tra quelli in esecuzione
            // all'avvio del cron runner
            if ((isset($cron['crons_active']) && $cron['crons_active'] === DB_BOOL_FALSE) or in_array($cron['crons_id'], $inExecution)) {
                $skipped[] = $cron['crons_id'];
                //Remove to avoid infinite pending cron
                $this->noMoreInExecution($cron['crons_id']);
                continue;
            }

            // Marco l'inizio del cron impostando il last execution field e
            // ricordandolo come in esecuzione in cache
            $this->db->set('crons_last_execution', 'NOW()', false);
            $this->db->where('crons_id', $cron['crons_id']);
            $this->db->update('crons');

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
        print_r(array_map(function ($c) use ($idxCrons) {
            return sprintf('(%s) %s', $c, $idxCrons[$c]);
        }, array_keys($idxCrons)));

        echo PHP_EOL, PHP_EOL, 'In esecuzione alla partenza', PHP_EOL;
        print_r(array_map(function ($c) use ($idxCrons) {
            return sprintf('(%s) %s', $c, $idxCrons[$c]);
        }, $inExecution));

        echo PHP_EOL, PHP_EOL, 'Eseguiti', PHP_EOL;
        print_r(array_map(function ($c) use ($idxCrons) {
            return sprintf('(%s) %s', $c, $idxCrons[$c]);
        }, $executed));

        echo PHP_EOL, PHP_EOL, 'Skippati', PHP_EOL;
        print_r(array_map(function ($c) use ($idxCrons) {
            return sprintf('(%s) %s', $c, $idxCrons[$c]);
        }, $skipped));
        echo '</pre>';

        // =============== OUTPUT ===============
        //
        // ============= Start MAIL_QUEUE =============
        $model = $this->config->item('crm_name') . '_mail_model';
        if (file_exists(APPPATH . "models/$model.php")) {
            try {
                $this->load->model($model, 'my_model');
                $this->my_model->flushEmails();
                // Model Exists
            } catch (Exception $e) {
                debug($e, true);
                // Model does NOT Exist
                //Uso il mail model di default
                $this->mail_model->flushEmails();
            }
        } else {
            $this->mail_model->flushEmails();
        }
        // ============= End MAIL_QUEUE =============

        // ============= Start Delete logs =============
        //Execute only on time a day
        if (date('H') == 11) {
            if ($this->db->dbdriver != 'postgre') {
                $this->db->where("log_api_date < now() - interval 280 day", null, false)->delete('log_api');
                $this->db->where("log_crm_time < now() - interval 280 day", null, false)->delete('log_crm');
                $this->db->where("DATE_FORMAT(FROM_UNIXTIME(timestamp), '%Y-%m-%d') < CURDATE() - INTERVAL 7 DAY", null, false)->delete('ci_sessions');
            } else {
                $this->db
                    ->where("log_api_date < NOW() - INTERVAL '6 MONTH'", null, false)
                    ->delete('log_api');
                $this->db
                    ->where("log_crm_time < NOW() - INTERVAL '6 MONTH'", null, false)
                    ->delete('log_crm');
                $this->db
                    ->where("to_timestamp(timestamp) < NOW() - INTERVAL '1 MONTH'", null, false)
                    ->delete('ci_sessions');
            }
        }
        // ============= End Delete logs =============

        $out = ob_get_clean();

        // Send output mail
        if (self::ENABLE_TRACKING) {
            mail(DEFAULT_EMAIL_SYSTEM, "Cron $cronKey end " . DEFAULT_EMAIL_SENDER, strip_tags($out));
        }

        //Clear logs folder
        $files = scandir(APPPATH . 'logs/');
        foreach ($files as $file) {
            if (in_array($file, ['.', '..', 'index.html']) || count(explode('-', $file)) != 4) {
                continue;
            } else {
                $file_no_ext = explode('.', $file)[0];
                $file_expl = explode('-', $file_no_ext);
                $data_txt = "{$file_expl[1]}-{$file_expl[2]}-{$file_expl[3]}";
                $date = DateTime::createFromFormat('Y-m-d', $data_txt);

                if ($date->diff(new DateTime())->format('%a') > 7) {
                    //debug("Cancello il file " . APPPATH . 'logs/' . $file);
                    unlink(APPPATH . 'logs/' . $file);
                    log_message("info", "Cancello il file " . APPPATH . 'logs/' . $file);
                } else {
                    //debug("Mantengo il file " . APPPATH . 'logs/' . $file);
                }
            }
        }

        //Send output to the browser if running cron check from client (not cli) and user is logged in as admin
        if ($this->auth->is_admin()) {
            echo $out;
        }
    }

    private function run(array $cron)
    {
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
            case 'custom_code':
                $this->cron_php_code($cron);
                break;
            default:
                echo "Type: " . $cron['crons_type'] . " unknown";
                break;
        }
    }

    //TODO: should be private?
    public function cron_php_code($cron)
    {
        eval($cron['crons_text']);
    }

    public function cron_php_file($cron)
    {
        $file = realpath(dirname(__FILE__) . '/../../' . $cron['crons_file']);
        if ($file === false) {
            die("Il file '{$cron['crons_file']}' non esiste.");
        } else {
            include $file;
        }
    }

    public function cron_curl($cron)
    {
        $ch = curl_init();
        $url = str_ireplace('{base_url}', base_url(), $cron['crons_file']);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $output = curl_exec($ch);
        curl_close($ch);
        echo $output;
    }

    public function cron_email($cron)
    {
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




    public function reset_cron_history()
    {
        $this->db->update('crons', array('crons_last_execution' => null));
        $this->noMoreInExecution();
    }


    // ============================
    //  Sezione cache esecuzione
    //  cron
    // ============================

    private function getInExecution()
    {
        // Usiamo la cache per ricordare quali cron sono in esecuzione
        $this->load->driver('cache');
        $inExecution = $this->cache->file->get($this->getKey());

        return is_array($inExecution) ? $inExecution : [];
    }

    private function saveInExecution($cronId)
    {
        $inExecution = $this->getInExecution();
        $inExecution[] = $cronId;
        $this->cache->file->save($this->getKey(), $inExecution, 240);

        return $inExecution;
    }

    private function noMoreInExecution($cronId = null)
    {
        if (is_numeric($cronId) && $cronId > 0) {
            $inExecution = array_filter($this->getInExecution(), function ($execCronId) use ($cronId) {
                return $execCronId != $cronId;
            });
            $this->cache->file->save($this->getKey(), $inExecution, 240);
            return $inExecution;
        } else {
            $this->cache->file->delete($this->getKey());
            return [];
        }
    }

    private function getKey()
    {
        return 'crons' . substr(sha1(__FILE__), 0, 6);
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
