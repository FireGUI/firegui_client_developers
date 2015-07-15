<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);


/********************************** FINE CONFIGURAZIONE ****************************************/



define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');

define('DEBUG_LEVEL', 'DEVELOP');


/*********** Layout di default *********/
define('DEFAULT_LAYOUT_CALENDAR',   'calendar_simple');
define('DEFAULT_LAYOUT_CHART',   'simple_pie');
define('DEFAULT_LAYOUT_GRID',       'default');
define('DEFAULT_LAYOUT_MAP',        'map_standard');



/*********** Session fields *********/
define('SESS_LOGIN',        'master_crm_login');
define('SESS_GRIDS_DATA',   'master_crm_grids');
define('SESS_WHERE_DATA',   'master_crm_where_data');


/*********** Operatori filtri *********/
define('OPERATORS', serialize(array(
    'eq'    => array( 'html' => '=',       'sql' => '=' ),
    'lt'    => array( 'html' => '<',       'sql' => '<' ),
    'gt'    => array( 'html' => '>',       'sql' => '>' ),
    'le'    => array( 'html' => '&le;',    'sql' => '<=' ),
    'ge'    => array( 'html' => '&ge;',    'sql' => '>=' ),
    'in'    => array( 'html' => 'IN',      'sql' => 'IN' ),
    'like'  => array( 'html' => 'LIKE',    'sql' => 'ILIKE' ),
)));





define('CRON_TYPES', serialize(array(
    'mail'  =>  'Mail',
    'php_file'  =>  'PHP FILE',
    'curl'  =>  'CURL'
)));





/* ============
 * Permessi
 * ============ */
define('PERMISSION_NONE',   '00');
define('PERMISSION_READ',   '10');
define('PERMISSION_WRITE',  '11');




/* ========================
 * Tipi entità
 * ======================== */
define('ENTITY_TYPE_SYSTEM',        0);
define('ENTITY_TYPE_DEFAULT',       1);
define('ENTITY_TYPE_SUPPORT_TABLE', 2);
define('ENTITY_TYPE_RELATION',      3);
define('ENTITY_TYPE_MODULE',        4);


/* ========================
 * Tipi di notifica
 * ======================== */
define('NOTIFICATION_TYPE_ERROR',   0);
define('NOTIFICATION_TYPE_INFO',    1);
define('NOTIFICATION_TYPE_WARNING', 2);
define('NOTIFICATION_TYPE_MESSAGE', 3);








/* End of file constants.php */
/* Location: ./application/config/constants.php */
