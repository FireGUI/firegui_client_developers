<?php

//Variables
$quality = 100;

$params_array_map = [
    0 => 'width',
    1 => 'height',
    2 => 'config',

];

//Constants
define('CROP', 1);
define('NO_CROP', 2);
define('CACHE', true);
define('ROOT_PATH', '../../');
define('WRITABLE_DIR', './writable/');
define('ACCEPTED_EXTENSIONS', [
    1 => 'gif',
    2 => 'jpeg',
    3 => 'png'
]);
define('UPLOAD_DEPTH_LEVEL', 3);
define('DIR_WRITE_MODE', 0755);
define('CACHE_TIME', 60 * 60 * 2); //Cache time in seconds (ex.: 60*60*2 = 2 hours)

define('CONFIGS', [
    1 => [
        'mode' => CROP,
        'cache' => true
    ],
    2 => [
        'mode' => NO_CROP,
        'cache' => true
    ],
    3 => [
        'mode' => CROP,
        'filters' => [
            'blur' => 50,
        ],
        'cache' => true
    ],
    4 => [
        'mode' => CROP,
        'watermark' => [
            'src' => 'watermark.jpg',
            'repeat' => true,

        ],
        'cache' => true
    ],
    5 => [
        'mode' => CROP,
        'library' => 'gd2',
        'cache' => true
    ],
    6 => [
        'mode' => NO_CROP,
        'library' => 'gd2',
        'cache' => true
    ],
    7 => [
        'mode' => CROP,
        'library' => 'imagemagick',
    ],
    8 => [
        'mode' => NO_CROP,
        'library' => 'imagemagick',
    ],
]);
