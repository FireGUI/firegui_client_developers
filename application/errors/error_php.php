<?php ob_start(); ?>
<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

<h4>A PHP Error was encountered</h4>

<p>Severity: <?php echo $severity; ?></p>
<p>Message:  <?php echo $message; ?></p>
<p>Filename: <?php echo $filepath; ?></p>
<p>Line Number: <?php echo $line; ?></p>

</div>
<?php
$html = ob_get_contents();
ob_end_clean();
echo $html;

if($_SERVER['REMOTE_ADDR'] !== '88.86.183.74' && gethostname() !== 'sfera') {
    $header = 'From: '.DEFAULT_EMAIL_SENDER.' <'.DEFAULT_EMAIL_SYSTEM.'>'.PHP_EOL;
    $header .= "MIME-Version: 1.0".PHP_EOL;
    $header .= "Content-Type: text/html; charset=\"iso-8859-1\"".PHP_EOL;
    $header .= "Content-Transfer-Encoding: 7bit".PHP_EOL;
    
    $fullMessage = array($html);
    $fullMessage[] = 'URL ' . current_url();
    
    if($_POST) {
        $fullMessage[] = '<br/>$_POST <pre>' . print_r($_POST, true) . '</pre>';
    }
    
    if($_GET) {
        $fullMessage[] = '<br/>$_GET <pre>' . print_r($_GET, true) . '</pre>';
    }
    
    if(isset($this) && isset($this->session)) {
        $fullMessage[] = '<br/>Sessione <pre>' . print_r($this->session->all_userdata(), true) . '</pre>';
    }
    
    $fullMessage[] = '<br/>$_SERVER <pre>' . print_r($_SERVER, true) . '</pre>';
    
    mail('debug@h2-web.it', 'Errore php CRM', implode('<br/>', $fullMessage), $header);
}
