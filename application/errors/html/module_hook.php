<?php

$CI = &get_instance();
if ($CI === null) {

    new MX_Controller();
    $CI = &get_instance();
}

$CI->load->helper('url');


$error['url'] = current_url();

//Questo file viene sovrascritto da eventuali moduli che vogliano gestire gli errori
$config_error_file = './config.json';

//Una volta definite le variabili che mi servono a gestire l'errore, verifico se ci sia o meno un modulo di gestione errori.
if (file_exists($config_error_file)) {
    //Se si, lo invoco
    $json_content = file_get_contents($config_error_file);
    if ($json_data = json_decode($json_content, true)) {
        if (array_key_exists('error_module_name', $json_data)) {
            $module_name = $json_data['error_module_name'];

            $return = Modules::run("$module_name/main/index", $error);
        }
    } else {
        //Lascio andare avanti codeigniter con le sue logiche di gestione errori
    }
} else {
    //Lascio andare avanti codeigniter con le sue logiche di gestione errori

}
