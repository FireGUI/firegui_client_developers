<?php
define("NET_ERROR", "Errore+di+rete+impossibile+spedire+il+messaggio");
define("SENDER_ERROR", "Puoi+specificare+solo+un+tipo+di+mittente%2C+numerico+o+alfanumerico");

define("SMS_TYPE_CLASSIC", "classic");
define("SMS_TYPE_CLASSIC_PLUS", "classic_plus");
define("SMS_TYPE_BASIC", "basic");
define("SMS_TYPE_TEST_CLASSIC", "test_classic");
define("SMS_TYPE_TEST_CLASSIC_PLUS", "test_classic_plus");
define("SMS_TYPE_TEST_BASIC", "test_basic");


function do_post_request($url, $data, $optional_headers = null)
{
    if (!function_exists('curl_init')) {
        $params = array(
            'http' => array(
                'method' => 'POST',
                'content' => $data
            )
        );
        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }
        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            return 'status=failed&message=' . NET_ERROR;
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            return 'status=failed&message=' . NET_ERROR;
        }
        return $response;
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Generic Client');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);

        if ($optional_headers !== null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optional_headers);
        }

        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            return 'status=failed&message=' . NET_ERROR;
        }
        return $response;
    }
}

function skebbyGatewaySendSMS($username, $password, $recipients, $text, $sms_type = SMS_TYPE_CLASSIC, $sender_number = '', $sender_string = '', $user_reference = '', $charset = '', $optional_headers = null)
{
    $url = 'https://gateway.skebby.it/api/send/smseasy/advanced/http.php';

    switch ($sms_type) {
        case SMS_TYPE_CLASSIC:
        default:
            $method = 'send_sms_classic';
            break;
        case SMS_TYPE_CLASSIC_PLUS:
            $method = 'send_sms_classic_report';
            break;
        case SMS_TYPE_BASIC:
            $method = 'send_sms_basic';
            break;
        case SMS_TYPE_TEST_CLASSIC:
            $method = 'test_send_sms_classic';
            break;
        case SMS_TYPE_TEST_CLASSIC_PLUS:
            $method = 'test_send_sms_classic_report';
            break;
        case SMS_TYPE_TEST_BASIC:
            $method = 'test_send_sms_basic';
            break;
    }

    $parameters = 'method='
        . urlencode($method) . '&'
        . 'username='
        . urlencode($username) . '&'
        . 'password='
        . urlencode($password) . '&'
        . 'text='
        . urlencode($text) . '&'
        . 'recipients[]=' . implode('&recipients[]=', $recipients);

    if ($sender_number != '' && $sender_string != '') {
        parse_str('status=failed&message=' . SENDER_ERROR, $result);
        return $result;
    }
    $parameters .= $sender_number != '' ? '&sender_number=' . urlencode($sender_number) : '';
    $parameters .= $sender_string != '' ? '&sender_string=' . urlencode($sender_string) : '';

    $parameters .= $user_reference != '' ? '&user_reference=' . urlencode($user_reference) : '';


    switch ($charset) {
        case 'UTF-8':
            $parameters .= '&charset=' . urlencode('UTF-8');
            break;
        case '':
        case 'ISO-8859-1':
        default:
    }

    parse_str(do_post_request($url, $parameters, $optional_headers), $result);

    return $result;
}

function skebbyGatewayGetCredit($username, $password, $charset = '')
{
    $url = "https://gateway.skebby.it/api/send/smseasy/advanced/http.php";
    $method = "get_credit";

    $parameters = 'method='
        . urlencode($method) . '&'
        . 'username='
        . urlencode($username) . '&'
        . 'password='
        . urlencode($password);

    switch ($charset) {
        case 'UTF-8':
            $parameters .= '&charset=' . urlencode('UTF-8');
            break;
        default:
    }

    parse_str(do_post_request($url, $parameters), $result);
    return $result;
}

// Invio singolo
//$recipients = array('*******');

// Per invio multiplo
// $recipients = array('*******','******');


// ------------ Invio SMS Classic --------------

// Invio SMS CLASSIC con mittente personalizzato di tipo alfanumerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC,'','John');

// Invio SMS CLASSIC con mittente personalizzato di tipo numerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC,'393471234567');


// ------------- Invio SMS Basic ----------------
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you? By John', SMS_TYPE_BASIC);


// ------------ Invio SMS Classic Plus -----------

// Invio SMS CLASSIC PLUS(con notifica) con mittente personalizzato di tipo alfanumerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC_PLUS,'','John');

// Invio SMS CLASSIC PLUS(con notifica) con mittente personalizzato di tipo numerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC_PLUS,'393471234567');

// Invio SMS CLASSIC PLUS(con notifica) con mittente personalizzato di tipo numerico e stringa di riferimento personalizzabile
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_CLASSIC_PLUS,'393471234567','','riferimento');




// ------------------------------------------------------------------
// ATTENZIONE I TIPI DI SMS SMS_TYPE_TEST* NON FANNO PARTIRE ALCUN SMS 
// SERVONO SOLO PER VERIFICARE LA POSSIBILITA' DI RAGGIUNGERE IL SERVER DI SKEBBY 
// ------------------------------------------------------------------

// ------------- Testing invio SMS Classic---------
// TEST di invio SMS CLASSIC con mittente personalizzato di tipo alfanumerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC,'','John');

// TEST di invio SMS CLASSIC con mittente personalizzato di tipo numerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC,'393471234567');

// ------------- Testing invio SMS Classic Plus---------

// TEST di invio SMS CLASSIC PLUS(con notifica) con mittente personalizzato di tipo alfanumerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC_PLUS,'','John');

// TEST di invio SMS CLASSIC PLUS(con notifica) con mittente personalizzato di tipo numerico
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you?', SMS_TYPE_TEST_CLASSIC_PLUS,'393471234567');

// ------------- Testing invio SMS Basic---------------
// $result = skebbyGatewaySendSMS('username','password',$recipients,'Hi Mike, how are you? By John', SMS_TYPE_TEST_BASIC);

// ------------------------------------------------------------------
// ATTENZIONE I TIPI DI SMS SMS_TYPE_TEST* NON FANNO PARTIRE ALCUN SMS 
// SERVONO SOLO PER VERIFICARE LA POSSIBILITA' DI RAGGIUNGERE IL SERVER DI SKEBBY 
// ------------------------------------------------------------------
/*



if($result['status']=='success') {
	echo '<b style="color:#8dc63f;">SMS inviato con successo</b><br/>';
	if (isset($result['remaining_sms'])){
		echo '<b>SMS rimanenti:</b>'.$result['remaining_sms'];
	}
	if (isset($result['id'])){
		echo '<b>ID:</b>'.$result['id'];
	}
}

if($result['status']=='failed')	{
	echo '<b style="color:#ed1c24;">Invio fallito</b><br/>';
	if(isset($result['code'])) {
		echo '<b>Codice:</b>'.$result['code'].'<br/>';
	}
		echo '<b>Motivo:</b>'.urldecode($result['message']);
}
*/

// ------------ Controllo del CREDITO RESIDUO -------------
// $credit_result = skebbyGatewayGetCredit('username', 'password');


// if($credit_result['status']=='success') {
  // echo 'Credito residuo: ' .$credit_result['credit_left']."\n";
  // echo 'SMS Classic rimanenti: ' .$credit_result['classic_sms']."\n";
  // echo 'SMS Basic rimanenti: ' .$credit_result['basic_sms']."\n";
// }

// if($credit_result['status']=='failed') {
  // echo 'Invio richiesta fallito';
// }
