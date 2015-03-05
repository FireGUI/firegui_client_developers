<?php

function get_invoice_number($number, $creation_date) {
    $date_arr = explode('-', $creation_date);
    return str_pad($number."-".$date_arr[0], 9, 0, STR_PAD_LEFT);
    
}

/*
LEFT JOIN (
  SELECT offers_products_accessories_offer_id AS offer_id, SUM(offers_products_accessories_price) AS price
  FROM offers_products_accessories
  GROUP BY offers_products_accessories_offer_id
) AS acces ON (prods.offer_id = acces.offer_id)
 */