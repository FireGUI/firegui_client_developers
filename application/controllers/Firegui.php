<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Firegui extends MY_Controller
{

    function __construct()
    {
        parent:: __construct();

        // TODO: INTEGRARE SISTEMA DI PROTEZIONE, SOLO FIREGUI DEVE POTER ESEGUIRE QUESTI METODI SE QUALCUNO SCOPRE
    }

    public function createModule($identifier)
    {

        //Creo le cartelle necessarie
        $folders = [
            'controllers', 'models', 'views', 'assets'
        ];

        $prefix_folder = FCPATH . 'application/modules/' . $identifier . '/';

        foreach ($folders as $folder) {
            if (!is_dir($prefix_folder . $folder)) //create the folder if it's not already exists
            {
                mkdir($prefix_folder . $folder, DIR_WRITE_MODE, true);
            }
        }

    }

    public function uninstallModule($identifier)
    {
        // TODO: Pericoloso esporre un metodo del genere
        unlink(FCPATH . "application/modules/$identifier/");
    }

    function updateFromGit($command = null, $output = true)
    {

        if ($command == null) {
            $command = "git pull https://github.com/FireGUI/firegui_client_developers.git master";
        }

        $result = array();
        exec($command, $result);

        if ($output == true) {
            foreach ($result as $line) {
                print($line . "\n");
            }
        }
    }

    public function downloadModuleFolder($identifier)
    {
        $module = $this->db->where('modules_identifier', $identifier)->get('modules')->row_array();

        if (!$module) {
            die('Unauthorized');
        } else {

            $folder = FCPATH . 'application/modules/' . $identifier . '/';

            $destination_file = FCPATH . 'uploads/' . $identifier . '.zip';

            if (!file_exists($folder)) {
                die('Can not create module, folder not found: ' . $destination_file);
            }


            $success = zip_folder($folder, $destination_file);

            if ($success === TRUE) { //NON TORNA FALSE!!!!!!
                if (file_exists($destination_file)) {
                    header('Content-Type: application/zip');
                    header('Content-Disposition: attachment; filename="' . $identifier . '.zip"');
                    header('Content-Length: ' . filesize($destination_file));
                    //die(filesize($destination_file));
                    //ob_end_clean();
                    readfile($destination_file);
                    //unlink($destination_file);
                } else {
                    die('Can not create  ' . $destination_file);
                }

            } else {
                die($success);
            }
        }
    }


    public function uploadModule($identifier)
    {
        $zip = new ZipArchive;

        if ($zip->open($_FILES['module_file']['tmp_name']) === TRUE) {
            $zip->extractTo(FCPATH . "/application/modules/{$identifier}");
            $zip->close();
        } else {
            die('Wrong zip archive!');
        }

        debug($_FILES);

        if (file_exists($_FILES['module_file']['tmp_name'])) {
            echo('file esistente');
        } else {

        }

        $content = file_get_contents($_FILES['module_file']['tmp_name']);
        echo('Inizio contenuto...');
        echo(strlen($content));
        //echo($content);
        die('test');

    }

    public function updateClient() {
        /*$versionDataJson = $this->input->post('client');
        $version_data = @json_decode($versionDataJson);*///DISMESSO ORA PRENDO IL FILE DIRETTAMENTE
        //var_dump($version_data);

        if (!class_exists('ZipArchive')) {
            die("Missing ZipArchive class in client");
        }

            $file_link = FIREGUI_BUILDER_BASEURL."public/client/getLastClientVersion/".VERSION;
            //die($file_link); 
            $newfile = './tmp_file.zip';
            if (!copy($file_link, $newfile)) {
                throw new Exception("Error while copying zip file.");
            } else{

                $zip = new ZipArchive();

                if ($zip->open($newfile) !== TRUE) {
                   throw new Exception("Cannot open <$newfile>");
                } else {
                    $temp_folder = FCPATH;
                    @mkdir($temp_folder);
                    $zip->extractTo($temp_folder);
                    $zip->close();
                    echo 'ok';
                }
            }

        


    }

    public function get_client_version() {
        echo VERSION;
    }
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */