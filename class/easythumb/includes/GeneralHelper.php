<?php

function d($var, $die = false, $trace = true, $show_from = true)
{

    echo '</select>';
    echo '</script>';


    // BackTrace
    $stack = '';
    $i = 1;
    $trace = debug_backtrace();
    array_shift($trace);

    foreach ($trace as $node) {
        if (isset($node['file']) && ($node['line'])) {
            $stack .= "#$i " . $node['file'] . "(" . $node['line'] . "): ";
        }
        if (isset($node['class'])) {
            $stack .= $node['class'] . "->";
        }
        $stack .= $node['function'] . "()" . PHP_EOL;
        $i++;
    }

    $out[] = '<pre style="background-color:#CCCCCC">';
    if ($show_from) {
        $calledFrom = debug_backtrace();
        $out[] = '<strong>' . substr(str_replace(dirname(__FILE__), '', $calledFrom[0]['file']), 1) . '</strong>';
        $out[] = ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
    }
    $out[] = htmlspecialchars(print_r($var, true));
    if (is_object($var)) {
        $out[] = '-------- Class methods --------';
        $out[] = print_r(get_class_methods(get_class($var)), true);
    }

    if ($trace) {
        $out[] = '-------- Backtrace --------';
        $out[] = $stack;
    }

    $out[] = '</pre>';
    echo implode(PHP_EOL, $out);
    if ($die) {
        die();
    }
}

function createFoldersFromFilename($filename)
{
    $localFolder = '';
    $uploadDepthLevel = defined('UPLOAD_DEPTH_LEVEL') ? (int) UPLOAD_DEPTH_LEVEL : 0;
    for ($i = 0; $i < $uploadDepthLevel; $i++) {
        // Assumo che le lettere siano tutte alfanumeriche,
        // alla fine le immagini sono tutte delle hash md5
        $localFolder .= strtolower(isset($filename[$i]) ? $filename[$i] . DIRECTORY_SEPARATOR : '');
    }

    if (!is_dir(WRITABLE_DIR . $localFolder)) {
        mkdir(WRITABLE_DIR . $localFolder, DIR_WRITE_MODE, true);
    }

    return $localFolder . '/';
}
