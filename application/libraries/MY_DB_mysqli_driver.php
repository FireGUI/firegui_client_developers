<?php

class MY_DB_mysqli_driver extends CI_DB_mysqli_driver {
    public function query($sql, $binds = FALSE, $return_object = null) {
        $ci =& get_instance( );
        if ($ci->config->item('log_threshold') == 5) {
            log_message('debug', $sql); // Log the query
        }
        
        return parent::query($sql, $binds, $return_object);
    }
}