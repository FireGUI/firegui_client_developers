<?php

/*
 * Install constants
 */
define('MODULE_NAME',       'fatture');


/** Entità **/
defined('ENTITY_SETTINGS') OR define('ENTITY_SETTINGS',               'settings');
defined('ENTITY_CUSTOMERS') OR define('ENTITY_CUSTOMERS',              'clienti');


/** Parametri **/
defined('FATTURAZIONE_SERIE_SUFFIX') OR define('FATTURAZIONE_SERIE_SUFFIX', serialize(array('/AH', '/AW', '/AG')));
defined('FATTURAZIONE_METODI_PAGAMENTO') OR define('FATTURAZIONE_METODI_PAGAMENTO', serialize(array('Bonifico', 'Paypal', 'Contanti', 'Sepa RID', 'RIBA')));

