<?php

/*
 * Install constants
 */
define('MODULE_NAME',       'fatture');


/*
 * Other settings
 */
define('ENTITY_PRODUCTS',               'prodotti');
define('ENTITY_PRODUCTS_FIELD_NAME',    'prodotti_descrizione');
define('ENTITY_PRODUCTS_FIELD_CODE',    'prodotti_codice');
define('ENTITY_PRODUCTS_FIELD_PRICE',   'prodotti_prezzo');

define('ENTITY_USERS',                  'agenti');
define('USER_NAME',                     'agenti_nome');
define('USER_CELL',                     'agenti_cellulare');


define('ENTITY_SETTINGS',               'settings');


define('ENTITY_CUSTOMERS',              'clienti');




define('ENTITY_CITY',   'citta');
define('CITY_ID',   'citta_id');
define('CITY_NAME',   'citta_value');


define('ENTITY_PROV',   'province');
define('PROV_ID',   'province_id');
define('PROV_NAME',   'province_value');






/** Create a task: imposta la entity a FALSE per non creare un task, oppure imposta i field a FALSE per saltarli **/
define('ENTITY_TASK',           'tasks');
define('TASK_TEXT',             'tasks_text');
define('TASK_CUSTOMER',         'tasks_azienda');
define('TASK_USER',             'tasks_user');
define('TASK_DATE',             'tasks_date');





?>
