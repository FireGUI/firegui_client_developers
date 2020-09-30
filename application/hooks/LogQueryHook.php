<?php
class LogQueryHook
{

    function log_queries()
    {
        $CI = &get_instance();
        $times = $CI->db->query_times;
        $dbs    = array();
        $output = NULL;
        $queries = $CI->db->queries;

        $CI->load->helper('general_helper');

        if (count($queries) > 0) {
            foreach ($queries as $key => $query) {
                if (strstr($query, "SELECT ") === FALSE) {
                    $output .= str_replace("\n", ' ', $query) . ";\n";
                }
            }
            $took = round(doubleval($times[$key]), 3);
        }

        $CI->load->helper('file');
        if (!write_file(APPPATH  . "/logs/queries.log." . $CI->db->database . ".txt", $output, 'a+')) {
            log_message('debug', 'Unable to write query the file');
        }
    }
}
