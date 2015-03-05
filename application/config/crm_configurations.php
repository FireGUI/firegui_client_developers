<?php

/**************************************
 * Configurazioni di base del CRM
 */
define('SHOW_MEDIA_MODULE', true);


/************ EMAILS *****************/
define('DEFAULT_EMAIL_SYSTEM', 'crm@h2-web.it');
define('DEFAULT_EMAIL_SENDER', 'MasterCRM');
define('DEFAULT_EMAIL_FROM', 'From: H2 CRM <no-reply@h2-web.it>');
define('DEFAULT_EMAIL_REPLY', 'Reply-To: H2 CRM <no-reply@h2-web.it>');


/*********** Entità base *********/
define('LOGIN_ENTITY',          'agenti');
define('LOGIN_USERNAME_FIELD',  'agenti_email');
define('LOGIN_PASSWORD_FIELD',  'agenti_password');
define('LOGIN_ACTIVE_FIELD',    '');
define('LOGIN_NAME_FIELD',      'agenti_nome');
define('LOGIN_SURNAME_FIELD',   'agenti_cognome');
define('LOGIN_IMG_FIELD',       'agenti_thumbnail');





/*********** Puo variare da CRM a CRM *****************/
function base_url_template($uri='') {
    if(function_exists('base_url')) {
        return base_url($uri);
    } else {
        return $uri;
    }
}







/**************************************
 * Eventuali configurazioni aggiuntive CRM
 */
$config=array(
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