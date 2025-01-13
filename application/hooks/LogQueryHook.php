<?php
class LogQueryHook
{
    public function log_queries()
    {
        $CI = &get_instance();

        $save_queries = $CI->db->save_queries;

        if (!$save_queries) {
            return;
        }

        $times = $CI->db->query_times;
        $queries = $CI->db->queries;

        $CI->load->helper('file');



        // Nome del file basato su data e ora
        $logFile = APPPATH . "/logs/queries_" . $CI->db->database . "_" . date('Y-m-d_H') . ".log";

        $output = "";

        if (count($queries) > 0) {
            foreach ($queries as $key => $query) {
                // Salva solo query non di tipo SELECT
                //if (strstr($query, "SELECT ") === FALSE) {
                $executionTime = isset($times[$key]) ? round(doubleval($times[$key]), 3) : 0;
                $output .= "[" . date('H:i:s') . "] " . str_replace("\n", ' ', $query) . " (Execution time: {$executionTime}s);\n";
                //}
            }
        }

        // Scrivi nel file
        if (!empty($output)) {
            if (!write_file($logFile, $output, 'a+')) {
                log_message('error', 'Unable to write to the query log file');
            }
        }
    }
}
