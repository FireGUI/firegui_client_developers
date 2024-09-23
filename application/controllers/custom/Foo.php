<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Foo extends MY_Controller
{
    function __construct()
    {
        parent::__construct();

        //Check logged in
        if ($this->auth->guest()) { //Guest
            //Do your stuff...
        } elseif ($this->auth->check()) { //Logged in
            //Do your stuff...
        } else {
            //Do your stuff...
            throw new AssertionError("Undetected authorization type");
        }
    }

    // Call this method by {your_url}/custom/foo/bar
    public function bar()
    {
        //Do your stuff...
    }

}
