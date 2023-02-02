<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
$hook['post_controller_constructor'] = array(
    'class' => 'Init_hook',
    'function' => 'initialize',
    'filename' => 'crm_initialization.php',
    'filepath' => 'hooks',
    'params' => array()
);

$hook['post_controller'] = [
    'class' => 'Init_hook',
    'function' => 'pre_destruct',
    'filename' => 'crm_initialization.php',
    'filepath' => 'hooks',
    'params' => array()
];

if (file_exists(APPPATH . 'config/hooks_custom.php')) {
    include_once APPPATH . 'config/hooks_custom.php';
}