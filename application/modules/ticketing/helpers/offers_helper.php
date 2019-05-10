<?php

function get_offer_number($number, $creation_date) {
    $date_arr = explode('-', $creation_date);
    return str_pad($number."-".$date_arr[0], 9, 0, STR_PAD_LEFT);
    
}