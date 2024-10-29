<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/
defined('SHOW_DEBUG_BACKTRACE') or define('SHOW_DEBUG_BACKTRACE', true);

// Client Version
defined('VERSION') OR define('VERSION', '4.1.7');
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
define('DIR_WRITE_MODE', 0755);

/********************************** FINE CONFIGURAZIONE ****************************************/

define('DEBUG_LEVEL', 'DEVELOP');

/*********** Layout di default *********/
define('DEFAULT_LAYOUT_CALENDAR', 'calendar_simple');
define('DEFAULT_LAYOUT_CHART', 'simple_pie');
define('DEFAULT_LAYOUT_GRID', 'default');
define('DEFAULT_LAYOUT_MAP', 'map_standard');

/*********** Session fields *********/
define('SESS_LOGIN', 'session_login');
define('SESS_GRIDS_DATA', 'master_crm_grids');
define('SESS_WHERE_DATA', 'master_crm_where_data');
define('SESS_WEBAUTHN', 'session_webauthn');

/*********** Operatori filtri *********/
define(
    'OPERATORS',
    serialize(
        array(
            'eq' => array('html' => '=', 'sql' => '=', 'label' => 'Equal'),
            'neq' => array('html' => '=', 'sql' => '=', 'label' => 'Not equal'),
            'lt' => array('html' => '<', 'sql' => '<', 'label' => 'Less than'),
            'gt' => array('html' => '>', 'sql' => '>', 'label' => 'Greater than'),
            'le' => array('html' => '&le;', 'sql' => '<=', 'label' => 'Less or equal'),
            'ge' => array('html' => '&ge;', 'sql' => '>=', 'label' => 'Greater or equal'),
            'in' => array('html' => 'IN', 'sql' => 'IN', 'label' => 'In'),
            'notin' => array('html' => 'IN', 'sql' => 'IN', 'label' => 'Not in'),
            'like' => array('html' => 'LIKE', 'sql' => 'LIKE', 'label' => 'Like'),
            'notlike' => array('html' => 'LIKE', 'sql' => 'LIKE', 'label' => 'Not like'),
            //'rangein'  => array( 'html' => 'Range in',    'sql' => '<@' ),
            'rangein' => array('html' => 'Range in', 'sql' => '&&', 'label' => 'Range in'),
        )
    )
);

define(
    'CRON_TYPES',
    serialize(
        array(
            'mail' => 'Mail',
            'php_file' => 'PHP FILE',
            'curl' => 'CURL',
        )
    )
);

/* ============
 * Colors palette field
 * ============ */
define(
    'COLORS_PALETTE',
    array(
        'Blue' => '#4b8ffc',
        'Blue default' => '#222d32',
        'Blue speech' => '#3c40c6',
        'Blue disco' => '#0fbcf9',
        'Blue devil' => '#227093',
        'Blue night' => '#192a56',
        'Blue naval' => '#40739e',
        'Blue light' => '#7efff5',
        'Yellow' => '#ffd32a',
        'Yellow desert' => '#ccae62',
        'Yellow spice' => '#ffda79',
        'Yellow ultra' => '#fffa65',
        'Green' => '#0be881',
        'Green light' => '#0be881',
        'Green water' => '#00d8d6',
        'Green progress' => '#4cd1370',
        'Green slack' => '#3aaf85',
        'Grey' => '#485460',
        'Grey light' => '#d2dae2',
        'Grey medium' => '#808e9b',
        'Grey steel' => '#4b4b4b',
        'Grey strong' => '#dcdde1',
        'light gray beige' => '#aca89f',
        'Violet' => '#575fcf',
        'Violet light' => '#8c7ae6',
        'Violet lavender' => '#82589F',
        'Violet medium' => '#C71585',
        'Brown Sienna' => '#A0522D',
        'Brown Indianred' => '#CD5C5C',
        'Red' => '#b33939',
        'Red Crimson' => '#DC143C',
        'Red light' => '#ff5252',
        'Red Flower' => '#e84118',
        'Red strong' => '#c23616',
        'Orange' => '#FF4500',
        'Orange dark' => '#FF8C00',
        'Orange radiant' => '#ff9f1a',
        'Pink' => '#FC427B',
        'White swan' => '#f7f1e3',
        'White light' => '#fafafa',
        'White lynx' => '#f5f6fa',
        'Black' => '#000000',
        'Black' => '#1e272e',
        'Black dark' => '#2C3A47',
    )
);

/* ============
 * Permessi
 * ============ */
define('PERMISSION_NONE', '00');
define('PERMISSION_READ', '10');
define('PERMISSION_WRITE', '11');

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/
defined('FOPEN_READ') or define('FOPEN_READ', 'rb');
defined('FOPEN_READ_WRITE') or define('FOPEN_READ_WRITE', 'r+b');
defined('FOPEN_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb'); // truncates existing file data, use with care
defined('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE') or define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b'); // truncates existing file data, use with care
defined('FOPEN_WRITE_CREATE') or define('FOPEN_WRITE_CREATE', 'ab');
defined('FOPEN_READ_WRITE_CREATE') or define('FOPEN_READ_WRITE_CREATE', 'a+b');
defined('FOPEN_WRITE_CREATE_STRICT') or define('FOPEN_WRITE_CREATE_STRICT', 'xb');
defined('FOPEN_READ_WRITE_CREATE_STRICT') or define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');

/* ========================
 * Tipi entità
 * ======================== */
define('ENTITY_TYPE_SYSTEM', 0);
define('ENTITY_TYPE_DEFAULT', 1);
define('ENTITY_TYPE_SUPPORT_TABLE', 2);
define('ENTITY_TYPE_RELATION', 3);
define('ENTITY_TYPE_MODULE', 4);


define('FIELDS_PERMISSIONS', serialize([
    0 => 'No permission',
    1 => 'Read-only',
    2 => 'Read/Write (update only)',
    3 => 'Read/Write (insert only)',
    4 => 'Read/Write (insert & update)',
]));

define('OPENBUILDER_BUILDER_BASEURL', 'https://my.openbuilder.net/');
define('OPENBUILDER_ADMIN_BASEURL', 'https://admin.openbuilder.net/');

define('CONDITIONS_ACTIONS_GRANT_ACCESS', 1);
define('CONDITIONS_ACTIONS_DENY_ACCESS', 2);
define('CONDITIONS_ACTIONS_REDIRECT', 3);

define('FIELD_NOT_REQUIRED', 0);
define('FIELD_SOFT_REQUIRED', 1);
define('FIELD_REQUIRED', 2);

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS') or define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR') or define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG') or define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE') or define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS') or define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') or define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT') or define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE') or define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN') or define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX') or define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/* End of file constants.php */
/* Location: ./application/config/constants.php */

if (file_exists(APPPATH . 'config/constants_custom.php')) {
    include_once APPPATH . 'config/constants_custom.php';
}
