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
        // Load custom model foo (you can call both with custom/ prefix or without it)

        // Uncomment this code to test it!
        // $this->load->model('custom/foomodel');

        $this->load->model('foomodel');

        d($this->foomodel->bar());

        // Load custom library foo (you can call both with custom/ prefix or without it)

        // Uncomment this code to test it!
        // $this->load->library('custom/foolibrary');
        $this->load->library('foolibrary');

        $foo = new Foolibrary();

        d($foo->bar());

        // Load custom helper foo (you can call both with custom/ prefix or without it)

        $this->load->helper('custom/foo_helper');
        d(bar());

        // A simple array
        $fooArray = array(
            "1" => "This is an array",
            "foo" => "bar",
            5 => [
                "case" => "Random Stuff: " . rand(100, 999),
                "PHP Version" => phpversion()
            ],
            52 => 89009,

        );

        d($fooArray);
    }
}
