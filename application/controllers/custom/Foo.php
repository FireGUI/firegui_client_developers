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

    //Call this method by {your_url}/custom/foo/foo
    public function foo()
    {
        //Load custom model foo (you can call both with custom/ prefix or without it)
        //$this->load->model('custom/foomodel');
        $this->load->model('foomodel');
        debug($this->foomodel->foo());

        // //Load custom library foo (you can call both with custom/ prefix or without it)
        //$this->load->library('custom/foolibrary');
        $this->load->library('foolibrary');
        $foo = new Foolibrary();
        debug($foo->foo());

        //Load custom helper foo (you can call both with custom/ prefix or without it)
        //$this->load->helper('custom/foo_helper');
        $this->load->helper('foo_helper');
        debug(foo());
    }
}
