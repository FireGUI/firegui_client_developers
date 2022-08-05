<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
if ($CI === null) {
    new MX_Controller();
    $CI = &get_instance();
}



// Invio l'errore al metodo centralizzato
if (empty($CI->db->conn_id)) {
    $error['type'] = 'database_connection';
} else {

    if (in_array($severity, ["Notice", E_DEPRECATED, "Warning", "Runtime Notice", "Parsing Error"])) {
        $error['type'] = 'php';
    } else {
        $error['type'] = 'exception';
    }
}
$error['error_title'] = 'A PHP Error was encountered';
$error['error_type'] = $severity;
$error['error_message'] = "<p>Severity: $severity </p>
    <p>Message: $message</p>
    <p>Filename: $filepath</p>
    <p>Line Number: $line</p>";
$error['error_filename'] = $filepath;
$error['error_linenumber'] = $line;
$error['error_extra_data'] = json_encode(debug_backtrace());

log_message('error', "*** WARNING *** PHP Error: " . $error['error_message']);



?>
</select>
<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

    <h4>A PHP Error was encountered</h4>

    <!-- <p>Severity: <?php echo $severity; ?></p>
    <p>Message: <?php echo $message; ?></p>
    <p>Filename: <?php echo $filepath; ?></p>
    <p>Line Number: <?php echo $line; ?></p> -->
    <?php echo $error['error_message']; ?>

    <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) : ?>

        <p>Backtrace:</p>
        <?php foreach (debug_backtrace() as $error) : ?>

            <?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>

                <p style="margin-left:10px">
                    File: <?php echo $error['file'] ?><br />
                    Line: <?php echo $error['line'] ?><br />
                    Function: <?php echo $error['function'] ?>
                </p>

            <?php endif ?>

        <?php endforeach ?>

    <?php endif ?>

</div>