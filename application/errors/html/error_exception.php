<?php



defined('BASEPATH') or exit('No direct script access allowed');

// Invio l'errore al metodo centralizzato
$error['type'] = 'exception';
$error['error_type'] = get_class($exception);
$severity = get_class($exception);
$filepath = $exception->getFile();
$line = $exception->getLine();
$error['error_message'] = "<p>Type: $severity </p>
    <p>Message: $message</p>
    <p>Filename: $filepath</p>
    <p>Line Number: $line</p>";
$error['error_filename'] = $exception->getFile();
$error['error_linenumber'] = $exception->getLine();
$error['error_extra_data'] = json_encode($exception->getTrace());
$error['error_title'] = 'An uncaught Exception was encountered';

log_message('error', "*** WARNING *** PHP Error: " . $error['error_message']);

?>
<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

    <h4>An uncaught Exception was encountered</h4>

    <!-- <p>Type: <?php echo get_class($exception); ?></p>
    <p>Message: <?php echo $message; ?></p>
    <p>Filename: <?php echo $exception->getFile(); ?></p>
    <p>Line Number: <?php echo $exception->getLine(); ?></p> -->
    <?php echo $error['error_message']; ?>

    <?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE) : ?>

        <p>Backtrace:</p>
        <?php foreach ($exception->getTrace() as $error) : ?>

            <?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0) : ?>

                <p style="margin-left:10px">
                    File: <?php echo $error['file']; ?><br />
                    Line: <?php echo $error['line']; ?><br />
                    Function: <?php echo $error['function']; ?>
                </p>
            <?php endif ?>

        <?php endforeach ?>

    <?php endif ?>

</div>