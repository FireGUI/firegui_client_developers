<?php

if (!function_exists('command_exists')) {

    function command_exists($cmd)
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
        return !empty($return);
    }
}

if (!function_exists('dd')) {
    function dd($var)
    {
        if (!is_development() && !is_maintenance()) {
            return;
        }
        echo '</select>';
        echo '</script>';

        $stack = '';
        $i = 1;
        $trace = debug_backtrace();
        array_shift($trace);

        foreach ($trace as $node) {
            if (isset($node['file']) && ($node['line'])) {
                $stack .= "#$i " . $node['file'] . "(" . $node['line'] . "): ";
            }
            if (isset($node['class'])) {
                $stack .= $node['class'] . "->";
            }
            $stack .= $node['function'] . "()" . PHP_EOL;
            $i++;
        }

        $out[] = '<pre style="background-color:#CCCCCC">';

        $calledFrom = debug_backtrace();

        $out[] = '<strong>' . substr(str_replace(dirname(__FILE__), '', $calledFrom[0]['file']), 1) . '</strong>:' . $calledFrom[0]['line'];

        if (is_object($var)) {
            $out[] = '-------- Class methods --------';
            $out[] = print_r(get_class_methods(get_class($var)), true);
        }

        if ($trace) {
            $out[] = '-------- Backtrace --------';
            $out[] = $stack;
        }

        $out[] = '</pre>';

        echo implode(PHP_EOL, $out);

        array_map(function ($x) {
            dump($x);
        }, func_get_args());

        die;
    }
}

if (!function_exists('d')) {
    function d($var)
    {
        if (!is_development() && !is_maintenance()) {
            return;
        }
        echo '</select>';
        echo '</script>';

        $stack = '';
        $i = 1;
        $trace = debug_backtrace();
        array_shift($trace);

        foreach ($trace as $node) {
            if (isset($node['file']) && ($node['line'])) {
                $stack .= "#$i " . $node['file'] . "(" . $node['line'] . "): ";
            }
            if (isset($node['class'])) {
                $stack .= $node['class'] . "->";
            }
            $stack .= $node['function'] . "()" . PHP_EOL;
            $i++;
        }

        $out[] = '<pre style="background-color:#CCCCCC">';

        $calledFrom = debug_backtrace();

        $out[] = '<strong>' . substr(str_replace(dirname(__FILE__), '', $calledFrom[0]['file']), 1) . '</strong>:' . $calledFrom[0]['line'];

        if (is_object($var)) {
            $out[] = '-------- Class methods --------';
            $out[] = print_r(get_class_methods(get_class($var)), true);
        }

        if ($trace) {
            $out[] = '-------- Backtrace --------';
            $out[] = $stack;
        }

        $out[] = '</pre>';

        echo implode(PHP_EOL, $out);

        array_map(function ($x) {
            dump($x);
        }, func_get_args());
    }
}

if (!function_exists('is_maintenance')) {
    function is_maintenance()
    {
        $CI = get_instance();

        return $CI->db->query("SELECT settings_maintenance_mode FROM settings")->row()->settings_maintenance_mode == DB_BOOL_TRUE;
    }
}
if (!function_exists('debug')) {

    function debug($var, $die = false, $trace = true, $show_from = true)
    {
        if (!is_development() && !is_maintenance()) {
            return;
        }
        echo '</select>';
        echo '</script>';
        switch (DEBUG_LEVEL) {
            case "DEVELOP":

                // BackTrace
                $stack = '';
                $i = 1;
                $trace = debug_backtrace();
                array_shift($trace);

                foreach ($trace as $node) {
                    if (isset($node['file']) && ($node['line'])) {
                        $stack .= "#$i " . $node['file'] . "(" . $node['line'] . "): ";
                    }
                    if (isset($node['class'])) {
                        $stack .= $node['class'] . "->";
                    }
                    $stack .= $node['function'] . "()" . PHP_EOL;
                    $i++;
                }

                $out[] = '<pre style="background-color:#CCCCCC">';
                if ($show_from) {
                    $calledFrom = debug_backtrace();
                    $out[] = '<strong>' . substr(str_replace(dirname(__FILE__), '', $calledFrom[0]['file']), 1) . '</strong>';
                    $out[] = ' (line <strong>' . $calledFrom[0]['line'] . '</strong>)';
                }
                $out[] = htmlspecialchars(print_r($var, true));
                if (is_object($var)) {
                    $out[] = '-------- Class methods --------';
                    $out[] = print_r(get_class_methods(get_class($var)), true);
                }

                if ($trace) {
                    $out[] = '-------- Backtrace --------';
                    $out[] = $stack;
                }

                $out[] = '</pre>';
                echo implode(PHP_EOL, $out);
                if ($die) {
                    die();
                }
                break;

            case "PRODUCTION":
            default:
                break;
        }
    }
}


if (!function_exists('json_message')) {

    /**
     * JSON per submitajax: messaggio
     *
     * @param string $message   Il messaggio da mostrare
     * @param bool $is_success  Indica se dev'essere mostrato come success o
     *                          error
     */
    function json_message($message, $is_success = false)
    {
        echo json_encode(['status' => $is_success ? 5 : 0, 'txt' => $message]);
    }
}


if (!function_exists('json_redirect')) {

    /**
     * JSON per submitajax: redirect
     *
     * @param string $url
     * @throws UnexpectedValueException Se il parametro non è un URL valido
     */
    function json_redirect($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new UnexpectedValueException(sprintf("Impossibile tornare un URL: %s", $url));
        }

        echo json_encode(['status' => 1, 'txt' => $url]);
    }
}

if (!function_exists('json_refresh')) {

    /**
     * JSON per submitajax: refresh
     */
    function json_refresh()
    {
        echo json_encode(['status' => 2]);
    }
}

if (!function_exists('json_alert')) {

    /**
     * JSON per submitajax: apri alert popup ed opzionalmente fai refresh.
     *
     * @param string $message           Messaggio di alert
     * @param bool $refresh             Booleano che indica se effettuare reload
     * @param null|int $refresh_timeout Se refresh = true, è il tempo in ms che
     *                                  intercorre tra la chiusura dell'alert e
     *                                  il reload
     */
    function json_alert($message, $refresh = false, $refresh_timeout = null)
    {
        $json['status'] = $refresh ? 4 : 3;
        $json['txt'] = $message;

        if ($refresh && $refresh_timeout > 0) {
            $json['timeout'] = (int) $refresh_timeout;
        }

        echo json_encode($json);
    }
}


if (!function_exists('var_swap')) {

    function var_swap(&$var1, &$var2)
    {
        $tmp = $var1;
        $var1 = $var2;
        $var2 = $tmp;
    }
}


if (!function_exists('debug_caller')) {

    function debug_caller()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        debug(array_pop($trace), false, false);
    }
}


if (!function_exists('isValidDateRange')) {

    function isValidDateRange($dateRange)
    {
        return preg_match("/^[\[\(][0-9]{4}-[0-9]{2}-[0-9]{2},[0-9]{4}-[0-9]{2}-[0-9]{2}[\)\]]$/", $dateRange);
    }
}

if (!function_exists('dateRange_to_dates')) {

    function dateRange_to_dates($date_range)
    {
        $dates = explode(',', trim($date_range, "[)(]"));
        if (count($dates) == 2) {
            $dates[1] = date('Y-m-d', strtotime('-1 day', strtotime($dates[1])));
        }
        return $dates;
    }
}

if (!function_exists('dateFormat')) {

    function dateFormat($date, $format = 'd/m/Y')
    {
        return ($timestamp = strtotime($date)) ? date($format, $timestamp) : $date;
    }
}

if (!function_exists('dateTimeFormat')) {

    function dateTimeFormat($date, $format = 'd/m/Y H:i:s')
    {
        return dateFormat($date, $format);
    }
}

if (!function_exists('date_toDbFormat')) {

    function date_toDbFormat($date)
    {
        $normalized_date = normalize_date($date);
        if (is_null($normalized_date)) {
            // Data non normalizzabile
            return null;
        }

        // Data normalizzata === date-time in formato PostgreSQL
        return DateTime::createFromFormat('Y-m-d H:i:s', $normalized_date)->format('Y-m-d');
    }
}

if (!function_exists('dateTime_toDbFormat')) {

    function dateTime_toDbFormat($date)
    {
        $normalized_date = normalize_date($date);
        if (is_null($normalized_date)) {
            // Data non normalizzabile
            return null;
        }

        // Data normalizzata === date-time in formato PostgreSQL
        return $normalized_date;
    }
}

if (!function_exists('normalize_date')) {

    function normalize_date($date)
    {
        // Scansiona i formati di data accettati e ritorna una stringa
        // rappresentante una data in formato US
        $validFormats = array(
            'd/m/Y H:i:s', // (IT) Datetime
            'd/m/Y H:i', // (IT) Datetime (no secondi)
            'd/m/Y', // (IT) Date
            'Y-m-d H:i:s.u', // (--) PostgreSQL datetime
            'Y-m-d H:i:s', // (US) Datetime
            'Y-m-d H:i', // (US) Datetime (no secondi)
            'Y-m-d', // (US) Date
        );
        foreach ($validFormats as $format) {
            $dateObject = DateTime::createFromFormat($format, $date);
            if ($dateObject instanceof DateTime && $dateObject->format($format) == $date) {
                return $dateObject->format('Y-m-d H:i:s');
            }
        }

        // Ultimo controllo disperato sulla data - strtotime
        if (($timestamp = strtotime($date)) >= 0) {
            return date('Y-m-d H:i:s', $timestamp);
        }

        return null;
    }
}


if (!function_exists('extract_intrange_data')) {

    function extract_intrange_data($value)
    {
        if (!$value) {
            return null;
        } else {
            if (is_array($value) && isset($value['range'], $value['from'], $value['to'])) {
                return $value;
            } elseif (is_string($value) && !is_numeric($value)) {
                $minmax = explode(',', trim($value, '()[]'));
                switch ($value[0]) {
                    case '[':
                        $min = $minmax[0];
                        break;
                    case '(':
                        $min = is_numeric($minmax[0]) ? $minmax[0] + 1 : null;
                        break;
                    default:

                        break;
                }
                switch ($value[strlen($value) - 1]) {
                    case ']':
                        $max = $minmax[1];
                        break;
                    case ')':
                        $max = is_numeric($minmax[1]) ? $minmax[1] - 1 : null;
                        break;
                    default:
                        break;
                }
            } elseif (is_int($value)) {
                $min = $max = $value;
            } else {
                return null;
            }
        }
        return [
            'range' => '[' . $min . ',' . $max . ']',
            'from' => $min,
            'to' => $max,
        ];
    }
}


if (!function_exists('array_key_map')) {

    function array_key_map(array $array, $key, $default = null)
    {
        return array_map(function ($item) use ($key, $default) {
            return ((is_array($item) && array_key_exists($key, $item)) ? $item[$key] : $default);
        }, $array);
    }
}


if (!function_exists('array_get')) {

    function array_get(array $array, $key, $default = null)
    {
        if (is_array($key)) {
            // $key contiene una lista di chiavi
            $tmp = $array;
            foreach ($key as $_key) {
                if (!is_array($tmp) or !array_key_exists($_key, $tmp)) {
                    return $default;
                }

                $tmp = $tmp[$_key];
            }
            return $tmp;
        } else {
            // $key contiene è una chiave singola quindi mi limito a prendere la
            // singola chiave richiesta
            return array_key_exists($key, $array) ? $array[$key] : $default;
        }
    }
}


if (!function_exists('array_key_value_map')) {

    function array_key_value_map(array $array, $key, $value)
    {
        return array_combine(array_key_map($array, $key), array_key_map($array, $value));
    }
}

if (!function_exists('e')) {

    function e($string, $ucfirst = true, $params = array())
    {
        echo t($string, $ucfirst, $params);
    }
}

if (!function_exists('t')) {

    function t($string, $ucfirst = false, $params = array())
    {

        //$CI = get_instance();
        //debug($CI->config->item('language'));
        $translation = lang($string);


        //debug($translation);
        if ($translation === false) {
            $translation = $string;
        }
        /*if ($translation === false) {



            $CI = get_instance();
            $lang_array = $CI->datab->getLanguage();
            $language = $lang_array ? $lang_array['file'] : $CI->config->item('language');



            $path = sprintf('%slanguage/%s/%s_lang.php', APPPATH, $language, $language);

            $val = addslashes($string);
            $add = '$lang[\'' . $val . '\'] = \'' . $val . '\';' . PHP_EOL;

            if (is_writable($path) && $string) {
                //sleep(1);
                include $path;
                if (!isset($lang) or !array_key_exists($string, $lang)) {
                    
                    file_put_contents($path, $add, FILE_APPEND | LOCK_EX);
                }
            }

            // Ricarica file traduzioni
            if ($lang_array) {
                $CI->datab->changeLanguage($lang_array['id']);
            }

            // Siccome la traduzione è vuota mantieni l'originale
            $translation = $string;
        }*/

        // Rimpiazza parametri
        if (is_array($params) && !empty($params)) {
            foreach ($params as $v) {
                $translation = preg_replace("/%s/", $v, $translation, 1);
            }
        }

        $modifiers = [1 => 'ucfirst', 2 => 'strtoupper', 3 => 'ucwords'];
        if (isset($modifiers[$ucfirst])) {
            call_user_func($modifiers[$ucfirst], $translation);
        }

        return $translation;
    }
}

if (!function_exists('normalize_path')) {

    function normalize_path($path)
    {
        $parts = array(); // Array to build a new path from the good parts
        $path = str_replace('\\', '/', $path); // Replace backslashes with forwardslashes
        $path = preg_replace('/\/+/', '/', $path); // Combine multiple slashes into a single slash
        $segments = explode('/', $path); // Collect path segments
        $test = ''; // Initialize testing variable
        foreach ($segments as $segment) {
            if ($segment != '.') {
                $test = array_pop($parts);
                if (is_null($test))
                    $parts[] = $segment;
                else if ($segment == '..') {
                    if ($test == '..')
                        $parts[] = $test;

                    if ($test == '..' || $test == '')
                        $parts[] = $segment;
                } else {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }
        return implode('/', $parts);
    }
}

if (!function_exists('echo_flush')) {

    function echo_flush($str)
    {
        echo str_pad($str, 2048, ' ');
        flush();
        ob_flush();
    }
}

if (!function_exists('generateRandomPassword')) {

    function generateRandomPassword($length = 8, $random_case = false, $user_friendly = false, $letters_array = false)
    {

        $result = '';
        if (!$letters_array) {
            $consonanti = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'); //manca la i e la l per non creare confusione!
            $vocali = array('a', 'e', 'o', 'u');
            $numeri = range(0, 9);

            $letters = array_merge($consonanti, $vocali, $numeri);
        } else {
            $letters = $letters_array;
        }
        for ($i = 0; $i < $length; $i++) {
            if ($user_friendly) {
                $letters = ($i % 2) ? $consonanti : $vocali;
            }

            $letter = $letters[rand(0, sizeof($letters) - 1)];
            if ($random_case && !is_integer($letter)) {
                $case = rand(0, 1);
                if ($case) {
                    $letter = strtoupper($letter);
                }
            }

            $result .= $letter;
        }

        return $result;
    }
}



if (!function_exists('str_replace_placeholders')) {

    function str_replace_placeholders($string, array $replaces, $caseinsensitive = true, $clearunmatched = false)
    {

        // Passa da multidimensionale a unidimensionale
        $smoothreplaces = array_smooth($replaces, ' ');
        if ($clearunmatched) {
            $all = array_map(function ($placeholder) {
                return substr($placeholder, 1, strlen($placeholder) - 2);
            }, str_get_placeholders($string));

            $smoothreplaces = array_merge(array_fill_keys($all, null), $smoothreplaces);
        }

        $keys = array_map(function ($k) {
            return "{{$k}}";
        }, array_keys($smoothreplaces));
        $vals = array_values($smoothreplaces);


        $replaced = $caseinsensitive ? str_ireplace($keys, $vals, $string) : str_replace($keys, $vals, $string);

        return $replaced;
    }
}

if (!function_exists('str_get_placeholders')) {

    function str_get_placeholders($string)
    {
        $matches = array();
        if (preg_match_all('/\{.[^\{\}]+\}/', $string, $matches) && count($matches) > 0) {
            return array_shift($matches);
        } else {
            return [];
        }
    }
}

if (!function_exists('array_smooth')) {

    function array_smooth(array $array, $separator, $depth = null)
    {
        $output = [];
        foreach ($array as $key => $val) {
            if (is_array($val) && (is_null($depth) or $depth > 0)) {
                $smoothed = array_smooth($val, $separator, is_null($depth) ? null : $depth - 1);
                foreach ($smoothed as $subkey => $subval) {
                    $output[$key . $separator . $subkey] = $subval;
                }
            } else {
                $output[$key] = $val;
            }
        }

        return $output;
    }
}

if (!function_exists('is_development')) {

    function is_development()
    {
        if (($ci = get_instance())) {
            $ipAddr = $ci->input->ip_address();
        } else {
            $ipAddr = filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?: @$_SERVER['REMOTE_ADDR'];
        }
        //die($_SERVER['REMOTE_ADDR']);
        //return in_array($ipAddr, ['151.95.143.14']) OR ( gethostname() === 'sfera');
        return ((!empty($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])));
    }
}

if (!function_exists('benchmark')) {

    function benchmark(callable $function)
    {
        // Misura 1
        $t1 = microtime(true);

        // Esegui
        $function();

        // Misura 2
        $t2 = microtime(true);

        // TODO: Eseguire n volte la funzione, quindi voglio dividere il tempo per n
        return ($t2 - $t1);  // ritorna il tempo trascorso in secondi approx. al microsecondo
    }
}


if (!function_exists('crm_exception_handler')) {

    function crm_exception_handler($ex, $print = true, $sendQueryListing = false)
    {
        // $CI = &get_instance();
        // //$CI->load->model('Exception');
        // //$CI->show_exception($ex);
        // Exceptions::show_exception($ex);
        // exit;
        // messaggio per utente
        $out = '<div style="padding:10px;background:#efefef;border-radius:7px;">';
        $out .= 'Si &egrave; verificato un errore imprevisto. Il problema &egrave; gi&agrave; stato inoltrato al nostro staff tecnico<br/>';

        if (is_development()) {
            // In ambiente di sviluppo mostro più dettagli
            $out .= 'Eccezione:';
            $out .= '<div style="padding:10px;background:#fff;border-radius:7px;margin-top:10px;">' . $ex->getMessage() . " at line {$ex->getLine()} in file {$ex->getFile()}";

            //debug(get_class_methods($ex),true);

            $out .= '<hr><pre>' . $ex->getTraceAsString() . '</pre>';
            $out .= '</div>';
        } else {
            // In produzione invece mando una mail
            $h = [
                'From: Crm Exception Handler <' . (defined('DEFAULT_EMAIL_SYSTEM') ? DEFAULT_EMAIL_SYSTEM : 'no-reply@yourdomain.com') . '>',
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=\"iso-8859-1\"',
                'Content-Transfer-Encoding: 7bit',
            ];

            $m = [
                'Message: ' . $ex->getMessage(),
                'URL: ' . current_url(),
                'Trace: ' . $ex->getTraceAsString()
            ];

            // Carico l'istanza di CI se presente:
            $ci = function_exists('get_instance') ? get_instance() : null;

            // Non stampo la sessione per nulla...
            if ($ci && isset($ci->session) && $ci->session->all_userdata()) {
                $m[] = 'Session <pre>' . print_r($ci->session->all_userdata(), true) . '</pre>';
            }

            // Go superglobal, go
            foreach (['_POST', '_GET', '_SERVER'] as $varname) {
                if (!empty($$varname)) {
                    $m[] = null;
                    $m[] = sprintf('$%s <pre>%s</pre>', $varname, print_r($$varname, true));
                }
            }

            // Render query listing
            if ($sendQueryListing && $ci) {
                $m[] = null;
                $m[] = 'Listing query database:';
                foreach ($ci->db->queries as $q) {
                    $m[] = '<pre style="background:#e4e4e4;#1f1f1f">' . $q . '</pre>';
                }
            }

            // Vai di mail
            mail(DEFAULT_EMAIL_SYSTEM, 'Eccezione non catturata su host ' . $_SERVER['HTTP_HOST'], implode('<hr>', $m), implode(PHP_EOL, $h));
            log_error_slack(implode('<hr>', $m));
        }

        $out .= '</div>';

        if ($print) {
            echo $out;
        }
    }
}

if (!function_exists('make_tiny')) {

    function make_tiny($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, 'http://tinyurl.com/api-create.php?url=' . $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

if (!function_exists('base64_to_jpeg')) {

    function base64_to_jpeg($base64_string, $output_file)
    {
        // open the output file for writing
        //debug($output_file);
        $ifp = fopen($output_file, 'wb');

        //debug($ifp);
        // split the string on commas
        // $data[ 0 ] == "data:image/png;base64"
        // $data[ 1 ] == <actual base64 string>
        $data = explode(',', $base64_string);
        if (empty($data[1])) {
            fwrite($ifp, base64_decode($data[0]));
        } else {
            fwrite($ifp, base64_decode($data[1]));
        }
        // we could add validation here with ensuring count( $data ) > 1
        // clean up the file resource
        fclose($ifp);

        return $output_file;
    }
}

if (!function_exists('array_diff_assoc_recursive')) {

    function array_diff_assoc_recursive($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                    if ($new_diff != FALSE) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!isset($array2[$key]) || $array2[$key] != $value) {
                $difference[$key] = $value;
            }
        }
        return !isset($difference) ? 0 : $difference;
    }
}


if (!function_exists('send_telegram_message')) {

    function send_telegram_message($botid, $chatid, $text)
    {
        $ch = curl_init();
        //Bot id must start with "bot".
        if (strpos($botid, 'bot') !== 0) {
            $botid = 'bot' . $botid;
        }
        $params = ['chat_id' => $chatid, 'text' => $text, 'parse_mode' => 'HTML'];
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/$botid/sendmessage");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

if (!function_exists('log_error_slack')) {

    function log_error_slack($message, $channel = '#log_crm')
    {
        return false;
    }
}

if (!function_exists('mese_testuale')) {

    function mese_testuale($data_o_mese)
    {
        if (is_numeric($data_o_mese)) {
            $month = $data_o_mese;
        } else {
            $month = date("n", strtotime($data_o_mese));
        }
        $month = (int) $month;
        $months = [
            1 => 'Gennaio',
            2 => 'Febbraio',
            3 => 'Marzo',
            4 => 'Aprile',
            5 => 'Maggio',
            6 => 'Giugno',
            7 => 'Luglio',
            8 => 'Agosto',
            9 => 'Settembre',
            10 => 'Ottobre',
            11 => 'Novembre',
            12 => 'Dicembre',
        ];
        // Call the format method on the DateInterval-object
        return $months[$month];
    }
}

if (!function_exists('zip_folder')) {

    function zip_folder($source, $destination, $exclude_dirs = [])
    {

        if (!extension_loaded('zip')) {
            die('Extension ZIP not found!');
            return false;
        }

        if (!file_exists($source)) {
            die("'$source' does not exists!");
            return false;
        }

        //Folder tree creation before open destination
        $dirs = explode('/', $destination);
        array_pop($dirs);
        foreach ($dirs as $dir) {
            if (!is_dir(FCPATH . $dir)) {
                mkdir(FCPATH . $dir, DIR_WRITE_MODE, true);
            }
        }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            //return $zip->getStatusString();
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    //Ignore folders excluded
                    if (in_array(str_replace($source . '/', '', $file), $exclude_dirs)) {
                        continue;
                    }
                    if (!$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'))) {
                        //die('Error adding folder '.$file);
                    }
                } else if (is_file($file) === true) {
                    if ($exclude_dirs) {
                        $dir_container = explode('/', str_replace($source . '/', '', $file));
                        array_pop($dir_container);
                        $dir_container = implode('/', $dir_container);

                        if (in_array($dir_container, $exclude_dirs)) {
                            //die(str_replace($source . '/', '', $file));
                            continue;
                        }
                    }
                    if (!$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file))) {
                        //die('Error adding file '.$file);
                    }
                }
            }
        } else if (is_file($source) === true) {
            if (!$zip->addFromString(basename($source), file_get_contents($source))) {
                //die('Error adding file '.$source);
            }
        }

        //die($destination);

        return $zip->close();
    }
}

if (!function_exists('doGeocoding')) {

    function doGeocoding($address)
    {


        usleep(800000);
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address) . "&countrycodes=it&format=json";



        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HEADER => 0,
            /* CURLOPT_FOLLOWLOCATION => 1, */
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:56.0) Gecko/20100101 Firefox/56.0"
        ));
        $result = curl_exec($ch);
        $data = json_decode($result, true);

        //debug($result,true);

        curl_close($ch);

        $place = is_array($data) ? array_shift($data) : null;



        return $place;
    }
}
if (!function_exists('calculateDistance')) {

    function calculateDistance($startPlace, $endPlace)
    {
        $url = "http://www.yournavigation.org/api/1.0/gosmore.php?flat={$startPlace['lat']}&flon={$startPlace['lon']}&tlat={$endPlace['lat']}&tlon={$endPlace['lon']}&v=motorcar&fast=0&layer=mapnik&format=geojson";

        //die($url);

        $ch = curl_init();
        curl_setopt_array($ch, array(CURLOPT_URL => $url, CURLOPT_HEADER => 0, /* CURLOPT_FOLLOWLOCATION => 1, */ CURLOPT_RETURNTRANSFER => 1));
        $result = curl_exec($ch);
        $data = json_decode($result, true);
        curl_close($ch);

        // Non potendo fare più di 10 richieste al secondo metto un leggero tempo di attesa dopo ogni richiesta
        usleep(250000); // 250 ms è un valore sperimentale che mi permette di poter eseguire tutte le richieste una dopo l'altra

        if (empty($data['properties']['distance'])) {
            return 0;
        } else {
            return ceil($data['properties']['distance']);
        }
    }
}

if (!function_exists('send_telegram_log')) {

    function send_telegram_log($chatid, $text)
    {
        return false;
    }
}
if (!function_exists('my_version_compare')) {

    function my_version_compare($v1, $v2)
    {
        if (is_string($v1) && is_string($v2)) {
            return version_compare($v1, $v2);
        } else {
            debug($v1);
            debug($v2, true);
            return true;
        }
    }
}

if (!function_exists('stats_standard_deviation')) {
    /**
     * This user-land implementation follows the implementation quite strictly;
     * it does not attempt to improve the code or algorithm in any way. It will
     * raise a warning if you have fewer than 2 values in your array, just like
     * the extension does (although as an E_USER_WARNING, not E_WARNING).
     *
     * @param array $a
     * @param bool $sample [optional] Defaults to false
     * @return float|bool The standard deviation or false on error.
     */
    function stats_standard_deviation(array $a, $sample = false)
    {
        $n = count($a);
        if ($n === 0) {
            trigger_error("The array has zero elements", E_USER_WARNING);
            return false;
        }
        if ($sample && $n === 1) {
            trigger_error("The array has only 1 element", E_USER_WARNING);
            return false;
        }
        $mean = array_sum($a) / $n;
        $carry = 0.0;
        foreach ($a as $val) {
            $d = ((float) $val) - $mean;
            $carry += $d * $d;
        };
        if ($sample) {
            --$n;
        }
        return sqrt($carry / $n);
    }
}

if (!function_exists('scanAllDir')) {
    function scanAllDir($dir)
    {
        $result = [];
        foreach (scandir($dir) as $filename) {
            if ($filename[0] === '.') continue;
            $filePath = $dir . '/' . $filename;
            if (is_dir($filePath)) {
                foreach (scanAllDir($filePath) as $childFilename) {
                    $result[] = $filename . '/' . $childFilename;
                }
            } else {
                $result[] = $filename;
            }
        }
        return $result;
    }
    function deleteDirRecursive($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    function tofloat($num)
    {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9\-]/", "", $num));
        }

        return floatval(
            preg_replace("/[^0-9\-]/", "", substr($num, 0, $sep)) . '.' .
                preg_replace("/[^0-9\-]/", "", substr($num, $sep + 1, strlen($num)))
        );
    }
}

if (!function_exists('checkClientVersion')) {
    function checkClientVersion()
    {
        $CI = &get_instance();

        if (!$CI->auth->is_admin()) {
            return false;
        }

        //Check if client is updated
        $last_check = $CI->session->userdata('last_client_check');


        //Check every 10 minutes to avoid unwanted curls...
        if (!$last_check || $last_check < date('Y-m-d h:i:s', strtotime('-10 minutes'))) {
            $last_check = date('Y-m-d h:i:s');
            $CI->session->set_userdata('last_client_check', $last_check);

            //$file_link = FIREGUI_BUILDER_BASEURL . "public/client/getLastClientVersion/" . VERSION;
            $new_version = file_get_contents(FIREGUI_BUILDER_BASEURL . "public/client/getLastClientVersionNumber/" . VERSION);
            //$new_version_code = file_get_contents(FIREGUI_BUILDER_BASEURL . "public/client/getLastClientVersionCode/" . VERSION);
            $CI->session->set_userdata('last_checked_version', $new_version);
            if ($new_version != VERSION) {
                return $new_version;
            } else {
                //Client already updated to the last version
                return false;
            }
        } else {
            //Already checked client version in the last few minutes
            return false;
        }
        $last_checked_version = $CI->session->userdata('last_checked_version');

        if ($last_checked_version != VERSION) {
            return $last_checked_version;
        } else {
            return false;
        }
    }
}

if (!function_exists('file_put_contents_and_create_dir')) {
    function file_put_contents_and_create_dir($filename, $data, $flags = 0, $context = null)
    {
        $exploded = explode('/', $filename);
        $file = array_pop($exploded);
        $dir = implode('/', $exploded);

        if (!is_dir($dir)) {
            mkdir($dir, DIR_WRITE_MODE, true);
        } elseif (!is_writable($dir)) {
            chmod($dir, DIR_WRITE_MODE);
        }

        return file_put_contents($filename, $data, $flags, $context);
    }
}


if (!function_exists('dirToArray')) {
    function dirToArray($dir)
    {

        $result = array();

        $cdir = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}

if (!function_exists('add_csrf')) {
    function add_csrf()
    {
        $csrf = get_csrf();
        echo "<input type=\"hidden\" name=\"{$csrf['name']}\" value=\"{$csrf['hash']}\" />";
    }
}

if (!function_exists('get_csrf')) {
    function get_csrf()
    {
        $CI = get_instance();
        $csrf = array(
            'name' => $CI->security->get_csrf_token_name(),
            'hash' => $CI->security->get_csrf_hash()
        );
        return $csrf;
        //echo "<input type=\"hidden\" name=\"{$csrf['name']}\" value=\"{$csrf['hash']}\" />";
    }
}
