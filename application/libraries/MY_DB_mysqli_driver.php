<?php

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver
{
    protected $query_logs = [];
    public function query($sql, $binds = FALSE, $return_object = null)
    {
        $ci =& get_instance();
        if ($ci->config->item('log_threshold') == 5) {
            log_message('debug', $sql); // Log the query
        }

        if ($this->save_queries === true) {
            $time_start = microtime(true);
            $result = parent::query($sql, $binds, $return_object);
            $time_end = microtime(true);

            $query_info = [
                'query' => $this->compile_binds($sql, $binds),
                'time' => $time_end - $time_start,
                'file' => '',
                // Inizializza con il nome del file corrente
                'line' => '',
                // Inizializza con il numero della riga corrente
            ];

            // Ottieni il backtrace per identificare il file e la riga
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            if (isset($backtrace[1]['file'])) {
                $query_info['file'] = $backtrace[1]['file'];
                $query_info['line'] = $backtrace[1]['line'];
            }

            $this->query_logs[] = $query_info;
        } else {
            $result = parent::query($sql, $binds, $return_object);
        }

        return $result;
    }
    public function get_query_logs()
    {
        return $this->query_logs;
    }
}