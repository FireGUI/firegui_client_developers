<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Foomodel extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }

    public function foo()
    {
        return 'foo';
    }
}
