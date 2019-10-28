<?php ob_start(); ?>
<div style="border:1px solid #990000;padding-left:20px;margin:0 0 10px 0;">

    <h4>A PHP Error was encountered</h4>

    <p>Severity: <?php echo $severity; ?></p>
    <p>Message: <?php echo $message; ?></p>
    <p>Filename: <?php echo $filepath; ?></p>
    <p>Line Number: <?php echo $line; ?></p>

</div>
<?php
$html = ob_get_contents();
ob_end_clean();
echo $html;
/*
if($_SERVER['REMOTE_ADDR'] !== 'XXXXXXXXX' && gethostname() !== 'sfera') {
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
    
    //mail(DEFAULT_EMAIL_SYSTEM, 'Errore php', implode('<br/>', $fullMessage), $header);

    $data = array(
            //'channel'     => $channel,
            //'username'    => $bot_name,
            'text'        => $fullMessage,
            //'icon_emoji'  => $icon,
            //'attachments' => $attachments
        );
        $data_string = json_encode($data);
        $ch = curl_init("https://hooks.slack.com/services/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");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );
        //Execute CURL
        $result = curl_exec($ch);
        return $result;

}
*/
