<?php

if (!defined('DB_BOOL_TRUE')) {
    if ($db['default']['dbdriver'] == 'postgre') {
        define('DB_BOOL_TRUE', 't'); //Mettere 1 per mysql
        define('DB_BOOL_FALSE', 'f');//Mettere 0 per mysql

        define('DB_INTEGER_IDENTIFIER', 'INT');
        define('DB_BOOL_IDENTIFIER', 'BOOL');
    } else {
        define('DB_BOOL_TRUE', '1'); //Mettere 1 per mysql
        define('DB_BOOL_FALSE', '0');//Mettere 0 per mysql
        define('DB_INTEGER_IDENTIFIER', 'integer');
        define('DB_BOOL_IDENTIFIER', 'BOOLEAN');
    }
}