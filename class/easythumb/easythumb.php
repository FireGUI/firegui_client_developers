<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once './includes/config.php';
include_once './includes/ThumbException.php';
include_once './includes/GeneralHelper.php';
include_once './includes/ImageHelper.php';

$param_string = urldecode($_SERVER['QUERY_STRING']);

$ext = strtolower(pathinfo($param_string, PATHINFO_EXTENSION));
$ext = str_ireplace('jpg', 'jpeg', $ext);

if (!in_array($ext, ACCEPTED_EXTENSIONS)) {
    throw new ThumbException("Extension '$ext' not supported.");
}

$cache = md5($param_string) . '.' . $ext;
$folder = createFoldersFromFilename($cache);
$cachedfile = WRITABLE_DIR . $folder . $cache;

$invalid_cache = (!file_exists($cachedfile) || time() - filectime($cachedfile) > CACHE_TIME);

if (CACHE == false || $invalid_cache) {

    $params = explode('/', $param_string);

    //Set all parameters (path, width, height and mode)
    foreach ($params_array_map as $key => $param_name) {
        if (array_key_exists($key, $params)) {
            $$param_name = $params[$key];
            unset($params[$key]);
        }
    }

    //Remaining elements represent the full image path
    $path = ROOT_PATH . implode('/', $params);
    $image_data = resize_image($path, $width, $height, $config);
    $type = $image_data['type'];
    $image = $image_data['image'];

    $image_function_output = "image$type";
    if (CACHE) {
        $image_function_output($image, $cachedfile);
    }

    //$image_function_output($image, $cachedfile);
    //exifExtract($cachedfile);
    //exit;

    header("Content-Type: image/$type");
    $image_function_output($image);

    imagedestroy($image);
} else {
    $type = getImageType($cachedfile);
    //die($type);
    header("Content-Type: image/$type");
    //header("Content-Disposition: attachment; filename=$cache");
    fpassthru(fopen($cachedfile, 'r'));
    //echo file_get_contents($cachedfile);
}
