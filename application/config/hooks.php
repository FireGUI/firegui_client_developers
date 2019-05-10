<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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

//$hook['post_system'][] = array(
//    'class' => 'LogQueryHook',
//    'function' => 'log_queries',
//    'filename' => 'LogQueryHook.php',
//    'filepath' => 'hooks'
//);

/* End of file hooks.php */
/* Location: ./application/config/hooks.php */