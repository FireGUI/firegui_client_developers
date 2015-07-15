<?php

function date3339($timestamp = 0) {

    if (!$timestamp) {
        $timestamp = time();
    }
    $date = date('Y-m-d\TH:i:s.000', $timestamp);

    $matches = array();
    if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
        $date .= $matches[1]; //.$matches[2].':'.$matches[3];
    } else {
        $date .= 'Z';
    }
    return $date;
}
