<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');



// From 2.7.0 Just for retro-compatibility -> forward all methods to NativeNotification model in CORE Notification model if exists

class Notifications extends CI_Model
{

    public function __call($name, $arguments)
    {
        if ($this->datab->module_installed('core-notifications')) {

            $this->load->model('core-notifications/nativenotifications');

            if (method_exists($this->nativenotifications, $name)) {
                return call_user_func_array(array($this->nativenotifications, $name), $arguments);
            }
        }
    }
}