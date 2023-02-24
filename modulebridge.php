<?php
$resource = $_SERVER['QUERY_STRING'];
$exploded = array_filter(explode('/', $resource));
//print_r($exploded);
$module_name = array_shift($exploded);
$asset_path = implode('/', $exploded);
$realfile = './application/modules/' . $module_name.'/assets/'.$asset_path;
$mimes = [
    'css' => 'css',
    'js' => 'javascript'
];

//die($realfile);
if (file_exists($realfile)) {
    $fileinfo = pathinfo($realfile);
    //print_r($fileinfo);
    $extension = $fileinfo['extension'];
    //die($extension);
    if (!in_array($extension, ['php'])) {
        // imposta il MIME type corretto nell'header della risposta
        if (!empty($mimes[strtolower($extension)])) {
            $mime = $mimes[strtolower($extension)];
        } else {
            $mime = $extension;
        }
        header("Content-Type: text/$mime");
        
        fpassthru(fopen($realfile, 'r'));
    }
}
?>
