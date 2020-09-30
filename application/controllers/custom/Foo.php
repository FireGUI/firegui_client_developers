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
        $this->load->model('foomodel');

        // //Load custom library foo (you can call both with custom/ prefix or without it)
        $this->load->library('foolibrary');
        $foo = new Foolibrary();

        d($foo->foo());

        $array = array(
            "1" => "PHP code tester Sandbox Online",
            "foo" => "bar",
            5 => [
                "case" => "Random Stuff: " . rand(100, 999),
                "PHP Version" => phpversion()
            ],
            52 => 89009,

        );
        d($array);
        d($foo->foo());
    }
}
