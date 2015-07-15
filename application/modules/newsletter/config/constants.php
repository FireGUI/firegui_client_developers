<?php

/*
 * Install constants
 */
define('MODULE_NAME',       'newsletter');




/*
 * Other settings
 */
if(!defined('MAILING_LISTS')) {
    /** nome_entità.nome_campo **/
    define('MAILING_LISTS', serialize(array(
        'Users' => 'agenti.agenti_email',
        'Customers' => 'aziende.aziende_email',
    )));
}

if(!defined('ENTITY_SETTINGS')) {
    define('ENTITY_SETTINGS',               'settings');
}

if(!defined('MAIL_QUEUE')) {
    define('MAIL_QUEUE',                    'mail_queue');
}


?>
