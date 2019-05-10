<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?php echo $severity; ?></p>
<p>Message:  <?php echo $message; ?></p>
<p>Filename: <?php echo $filepath; ?></p>
<p>Line Number: <?php echo $line; ?></p>

<?php if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE === TRUE): ?>

	<p>Backtrace:</p>
	<?php foreach (debug_backtrace() as $error): ?>

		<?php if (isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0): ?>

			<p style="margin-left:10px">
			File: <?php echo $error['file'] ?><br />
			Line: <?php echo $error['line'] ?><br />
			Function: <?php echo $error['function'] ?>
			</p>

		<?php endif ?>

	<?php endforeach ?>

<?php endif ?>

</div>



<?php


$html = ob_get_contents();
@ob_end_clean();

if (ENVIRONMENT == 'development') {
    echo $html;

} else {

    @session_start();
    $md5 = md5($message);
    $minExp = 10;
    $time = time();

    $sentRecently = (isset($_SESSION['dberr'][$md5]) && $time - $_SESSION['dberr'][$md5] <= $minExp * 60);


    $header = 'From: ' . DEFAULT_EMAIL_SENDER . ' <' . DEFAULT_EMAIL_SYSTEM . '>' . PHP_EOL;
    $header .= "MIME-Version: 1.0" . PHP_EOL;
    $header .= "Content-Type: text/html; charset=\"iso-8859-1\"" . PHP_EOL;
    $header .= "Content-Transfer-Encoding: 7bit" . PHP_EOL;

    $fullMessage = array($html);
    $fullMessage[] = 'URL ' . current_url();

    if ($_POST) {
        $fullMessage[] = '<br/>$_POST <pre>' . print_r($_POST, true) . '</pre>';
    }

    if ($_GET) {
        $fullMessage[] = '<br/>$_GET <pre>' . print_r($_GET, true) . '</pre>';
    }

    if (isset($this) && isset($this->session)) {
        $fullMessage[] = '<br/>Sessione <pre>' . print_r($this->session->all_userdata(), true) . '</pre>';
    }

    $fullMessage[] = '<br/>$_SERVER <pre>' . print_r($_SERVER, true) . '</pre>';

    log_error_slack(implode('<br/>', $fullMessage));
    if (!$sentRecently && $_SERVER['REMOTE_ADDR'] !== 'XXXXXXXXXX' && gethostname() !== 'idra') {
        if (mail('debug@h2web.it', 'Errore PHP CRM', implode('<br/>', $fullMessage), $header)) {
            $_SESSION['dberr'][$md5] = $time;
        }
    }
}

?>
