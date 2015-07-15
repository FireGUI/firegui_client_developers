<?php

/*
 * Install constants
 */
define('MODULE_NAME',       'messages');






define('MESSAGES_TABLE',            'messaggi');
define('MESSAGES_TABLE_JOIN_FIELD', 'messaggi_agenti_id_to');
define('MESSAGES_TABLE_FROM_FIELD', 'messaggi_agenti_id_from');
define('MESSAGES_TABLE_DATE_FIELD', 'messaggi_date_creation');
define('MESSAGES_TABLE_TEXT_FIELD', 'messaggi_text');



define('MESSAGES_USER_TABLE',       'agenti');
define('MESSAGES_USER_JOIN_FIELD',  'agenti_id');
define('MESSAGES_USER_NAME',        'agenti_nome');
define('MESSAGES_USER_LASTNAME',        'agenti_cognome');
define('MESSAGES_USER_THUMB',        'agenti_thumbnail');

?>
