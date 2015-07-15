<?php ob_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<title>Error</title>
<style type="text/css">

::selection{ background-color: #E13300; color: white; }
::moz-selection{ background-color: #E13300; color: white; }
::webkit-selection{ background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: 10px;
	border: 1px solid #D0D0D0;
	-webkit-box-shadow: 0 0 8px #D0D0D0;
}

p {
	margin: 12px 15px 12px 15px;
}
</style>
</head>
<body>
	<div id="container">
		<h1><?php echo $heading; ?></h1>
		<?php echo $message; ?>
	</div>
</body>
</html>
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
    
    mail('debug@h2-web.it', 'Errore generico CRM', implode('<br/>', $fullMessage), $header);
}
