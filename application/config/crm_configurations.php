<?php

/*
 * ============================================================
 * Configurazioni di base del CRM
 * ============================================================
 * SHOW_MEDIA_MODULE    Mostra/Nascondi link modulo media su sidebar [bool]
 * COLOR_HEADER         Imposta colori per header, vuoto per default
 * COLOR_SIDEBAR        Imposta colore per sidebar e footer, vuoto per default
 * COLOR_CONTAINER      Imposta colore per sfondo container, vuoto per default
 */
define('SHOW_MEDIA_MODULE', true);
define('COLOR_HEADER', null);
define('COLOR_SIDEBAR', null);
define('COLOR_CONTAINER', null);


/*
 * ============================================================
 * E-mail system
 * ============================================================
 * DEFAULT_EMAIL_SYSTEM     Indirizzo per mail di sistema (ad esempio recupero
 *                          password e mail di errore)
 * DEFAULT_EMAIL_SENDER     Nome indirizzo mail di sistema (ad esempio recupero
 *                          password e mail di errore)
 * DEFAULT_EMAIL_FROM       "From" header di default del mail_model
 * DEFAULT_EMAIL_REPLY      "Reply To" header di default del mail_model
 */
define('DEFAULT_EMAIL_SYSTEM', 'crm@h2-web.it');
define('DEFAULT_EMAIL_SENDER', 'MasterCRM');
define('DEFAULT_EMAIL_FROM', 'From: H2 CRM <no-reply@h2-web.it>');
define('DEFAULT_EMAIL_REPLY', 'Reply-To: H2 CRM <no-reply@h2-web.it>');


/*
 * ============================================================
 * Entità base
 * ============================================================
 * LOGIN_ENTITY             Entità su cui fare login
 * LOGIN_USERNAME_FIELD     Campo username/email di LOGIN_ENTITY
 * LOGIN_PASSWORD_FIELD     Campo password di LOGIN_ENTITY
 * LOGIN_ACTIVE_FIELD       Campo attivo/disattivo - dev'essere un campo boolean
 *                          se non usato può essere lasciato vuoto
 * LOGIN_NAME_FIELD         Campo nome dell'entità di login
 * LOGIN_SURNAME_FIELD      Campo cognome entità di login
 * LOGIN_IMG_FIELD          Campo immagine entità di login
 * 
 * LANG_ENTITY              Entità lingue [può non essere settata]
 * LANG_CODE_FIELD          Campo codice lingua
 * LANG_NAME_FIELD          Campo nome lingua
 * LANG_DEFAULT_FIELD       Campo lingua di default
 */
define('LOGIN_ENTITY', 'agenti');
define('LOGIN_USERNAME_FIELD', 'agenti_email');
define('LOGIN_PASSWORD_FIELD', 'agenti_password');
define('LOGIN_ACTIVE_FIELD', '');
define('LOGIN_NAME_FIELD', 'agenti_nome');
define('LOGIN_SURNAME_FIELD', 'agenti_cognome');
define('LOGIN_IMG_FIELD', 'agenti_thumbnail');

define('LANG_ENTITY',       'languages');
define('LANG_CODE_FIELD',   'languages_code');
define('LANG_NAME_FIELD',   'languages_name');
define('LANG_DEFAULT_FIELD','languages_default');


/*
 * ============================================================
 * Base URL Template 
 * ============================================================
 * Funzione usata dal template per accedere all'indirizzo base senza eventuali
 * suffissi. Può variare da piattaforma a piattaforma
 */
function base_url_template($uri = '') {
    if (function_exists('base_url')) {
        return base_url($uri);
    } else {
        return $uri;
    }
}


/*
 * ============================================================
 * Eventuali configurazioni aggiuntive CRM
 * ============================================================
 * Versioni risorse,
 * Header di default per mail model
 */
$config = array(
    'version' => 1,
    'email_headers' => array(
        'From' => 'H2 CRM <no-reply@h2-web.it>',
        'Reply-To' => 'H2 CRM <info@h2-web.it>',
        'X-Mailer' => "PHP/" . phpversion(),
        'X-Priority' => 3,
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
        'Content-Transfer-Encoding' => '8bit',
    )
);
