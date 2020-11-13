<?php
if (!defined('THEME_CSS_PATH')) { //Avoid double include


    /*
 * ============================================================
 * Base configurations
 * ============================================================
 
 * THEME_CSS_PATH       Path to custom theme css
 * CUSTOM_CSS_PATH     Path to custom css
 * UPLOAD_DEPTH_LEVEL   Annidated folders to create for uploads directory
 */
    define('THEME_CSS_PATH', null);
    define('CUSTOM_CSS_PATH', null);
    define('UPLOAD_DEPTH_LEVEL', 3);

    define('CUSTOM_FAVICON', null);

    define('DEFAULT_DATE_FORMAT', 'Y-m-d');
    define('DEFAULT_DATETIME_FORMAT', 'Y-m-d H:i:s');

    define('MAX_UPLOAD_SIZE', 30000);
    /*
 * ============================================================
 * E-mail system
 * ============================================================
 * DEFAULT_EMAIL_SYSTEM     System email address (for debugging, error log, ecc....)
 * DEFAULT_EMAIL_SENDER     Default email sender
 * DEFAULT_EMAIL_FROM       "From" default header
 * DEFAULT_EMAIL_REPLY      "Reply To" default header
 */
    define('DEFAULT_EMAIL_SYSTEM', 'no-reply@yourdomain.com');
    define('DEFAULT_EMAIL_SENDER', 'FireGUI');
    define('DEFAULT_EMAIL_FROM', 'Example Inc. <no-reply@yourdomain.com>');
    define('DEFAULT_EMAIL_REPLY', 'Example Inc. <no-reply@yourdomain.com>');

    /*
 

    /*
 * ============================================================
 * Base entities
 * ============================================================
 * LOGIN_ENTITY             Entity to use for the login
 * LOGIN_USERNAME_FIELD     Username/email field for login
 * LOGIN_PASSWORD_FIELD     Password field for login
 * LOGIN_ACTIVE_FIELD       Active field for login
 * LOGIN_NAME_FIELD         Name field for login
 * LOGIN_SURNAME_FIELD      Surname field for login
 * LOGIN_IMG_FIELD          Avatar field for login
 *
 * LANG_ENTITY              Language entity
 * LANG_CODE_FIELD          Language code field
 * LANG_NAME_FIELD          Language name/identifier field
 * LANG_DEFAULT_FIELD       Language "is default" field
 */
    define('LOGIN_ENTITY',          'users');
    define('LOGIN_USERNAME_FIELD',  'users_email');
    define('LOGIN_PASSWORD_FIELD',  'users_password');
    define('LOGIN_ACTIVE_FIELD',    'users_active');
    define('LOGIN_NAME_FIELD',      'users_first_name');
    define('LOGIN_SURNAME_FIELD',   'users_last_name');
    define('LOGIN_IMG_FIELD',       'users_avatar');

    define('LANG_ENTITY',       'languages');
    define('LANG_CODE_FIELD',   'languages_code');
    define('LANG_NAME_FIELD',   'languages_name');
    //define('LANG_DEFAULT_FIELD', 'languages_default');

    define('MIN_SEARCH_CHARS', 3);
    //define('STRICT_SEARCH', false);
    define('EXPLODE_SPACES', true);
    define('DEFAULT_GRID_LIMIT', 10);

    define('API_MANAGER_PRIVATE_KEY', '*******');
    define('API_MANAGER_CRM_PASSPARTOUT', '*******');

    //MD5 Passepartout for login password (example: if you want to login with every user email using the string 'your-secret-password', you should copy&paste the md5 of 'your-secret-password' here.)
    define('PASSEPARTOUT', '*********************************');

    //If true, when no user is admin, every user is considered as admin. If one user is admin, this parameter will have no effetc.
    define('PROMOTE_ADMIN', false);

    /* ============
 * Custom colors
 * ============ */
    define('TOPBAR_COLOR', '#222d32');
    define('TOPBAR_HOVER', '#222d32');
    define('LOGIN_COLOR', '#b22222');
    define('LOGIN_TITLE_COLOR', '#FFFFFF');

    /*
 * ============================================================
 * Base URL ADMIN 
 * ============================================================
 *
 */
}
if (!function_exists('base_url_admin')) {
    function base_url_admin($uri = '')
    {
        if (function_exists('base_url')) {
            return base_url($uri);
        } else {
            return $uri;
        }
    }

    /*
 * ============================================================
 * Base URL Template , Base URL Script, Base URL Uploads
 * ============================================================
 * 
 */
    function base_url_template($uri = '')
    {
        return base_url_admin($uri);
    }
    function base_url_scripts($uri = '')
    {
        return base_url_admin($uri);
    }
    function base_url_uploads($uri = '')
    {
        return base_url_admin($uri);
    }
}

/* 
 * ============================================================
 * Additional and customizable configurations
 * ============================================================
 * 
 */

$config = array(
    'version' => 1,
    'email_deferred' => false,
    'email_headers' => array(
        'From' => DEFAULT_EMAIL_FROM,
        'Reply-To' => DEFAULT_EMAIL_REPLY,
        'X-Mailer' => "PHP/" . phpversion(),
        //'X-Priority' => 3,
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=utf-8',
        'Content-Transfer-Encoding' => '8bit',
    )
);
