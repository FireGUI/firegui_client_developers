<?php

/*
 * Install constants
 */
define('MODULE_NAME',       'google_calendar');


/*
 * Other settings
 */
define('ENTITY_SETTINGS',           'settings');

define('GOOGLEAPP_CLIENTID', '826169456490-lt2c0cao2fulm2l6ubne4lanchf400um.apps.googleusercontent.com');
define('GOOGLEAPP_SECRET', '1E0yFf5brdAJxWkcNljG4mfX');

//Configurare nel crm_configurations
if (!defined('GC_ENTITY')) {
    define('GC_ENTITY',                 'events');
    define('GC_FIELD_UTENTE',           'events_utente');
    define('GC_FIELD_TITOLO',           'events_title');
    define('GC_FIELD_DESCRIZIONE',      'events_description');
    define('GC_FIELD_DATA_DA',          'events_date_start');
    define('GC_FIELD_GIORNATA_INTERA',  'events_full_day');
    define('GC_FIELD_DATA_A',           'events_date_end');
    define('GC_FIELD_DATA_MODIFICA',    'events_date_creation');
    define('GC_FIELD_LUOGO',            'events_place'); //Se non impostato il luogo, non lo importo
    define('GC_FIELD_CANCELLATO',       'events_deleted'); //Se non impostato, nessun appuntamento verrà cancellato! Questo campo è obbligatorio per questioni di sicurezza ed evitare di perdere appuntamenti...
    define('GC_FIELD_SORGENTE',         'events_source');
    define('GC_FIELD_CODICE_ESTERNO',   'events_external_code');
}
?>
