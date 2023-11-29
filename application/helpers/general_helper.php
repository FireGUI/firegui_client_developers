<?php

if (!function_exists('is_even')) {
    function is_even($number)
    {
        return $number % 2 == 0;
    }
}
if (!function_exists('is_odd')) {
    function is_odd($number)
    {
        return !is_even($number);
    }
}

if (!function_exists('command_exists')) {
    function command_exists($cmd)
    {
        $return = shell_exec(sprintf("which %s", escapeshellarg($cmd)));
        return !empty($return);
    }
}
/**
 * 
 */
if (!function_exists('json_validate')) {
    function json_validate($string, $return_decoded = false)
    {

        if (empty($string)) {
            log_message('error', 'Validate json failed: string is empty');
            return false;
        }

        // decode the JSON data
        $result = json_decode($string, true);

        // switch and check possible JSON errors
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = '';
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_RECURSION:
                $error = 'One or more recursive references in the value to be encoded.';
                break;
            // PHP >= 5.5.0
            case JSON_ERROR_INF_OR_NAN:
                $error = 'One or more NAN or INF values in the value to be encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }

        if ($error !== '') {
            log_message('error', 'Validate json failed: ' . $error);
            return false;
        } else {

            if ($return_decoded == true) {
                return $result;
            } else {
                return true;
            }
        }
    }
}


// Native function to use our api system from/to other openbuilder projects
if (!function_exists('my_api')) {
    function my_api($url, $public_key, $post_data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.2 Safari/537.36");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Authorization: Bearer ' . $public_key));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_XOAUTH2_BEARER, $public_key);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $jsonData = curl_exec($ch);
        curl_close($ch);

        // Check output if valid json
        if ($data = json_validate($jsonData, true)) {
            return $data;
        } else {
            log_message('error', 'My API request failed. Excpected json output. ');
            log_message('error', 'URL: ' . $url);
            log_message('error', 'Response: ' . $jsonData);
            return false;
        }
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
    /**
     * Debug function
     * @param mixed $var
     * @param array $
     * @return void
     */
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
    /**
     * Cehck maintenance mode
     * @return bool
     */
    function is_maintenance()
    {
        $CI = get_instance();

        return $CI->db->query("SELECT settings_maintenance_mode FROM settings")->row()->settings_maintenance_mode == DB_BOOL_TRUE;
    }
}

if (!function_exists('is_update_in_progress')) {
    /**
     * Cehck maintenance mode
     * @return bool
     */
    function is_update_in_progress()
    {
        $CI = get_instance();

        return $CI->db->query("SELECT settings_update_in_progress FROM settings")->row()->settings_update_in_progress == DB_BOOL_TRUE;
    }
}

if (!function_exists('debug')) {
    /**
     * Debug function
     * @param mixed $var
     * @param mixed $die
     * @param mixed $trace
     * @param mixed $show_from
     * @return void
     */
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
                $print_r = print_r($var, true);
                if (htmlspecialchars($print_r)) {
                    $out[] = htmlspecialchars($print_r);
                } else {
                    $out[] = $print_r;
                }

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

if (!function_exists('is_valid_json')) {
    function is_valid_json($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
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
    function dateFormat($date, $format = null)
    {
        if ($format == null && defined('DEFAULT_DATE_FORMAT')) {
            $format = DEFAULT_DATE_FORMAT;
        } elseif ($format == null) {
            $format = 'd/m/Y';
        }
        return ($timestamp = strtotime($date)) ? date($format, $timestamp) : $date;
    }
}

if (!function_exists('dateTimeFormat')) {
    function dateTimeFormat($date, $format = null)
    {
        if ($format == null && defined('DEFAULT_DATETIME_FORMAT')) {
            $format = DEFAULT_DATETIME_FORMAT;
        } elseif ($format == null) {
            $format = 'd/m/Y H:i:s';
        }

        return dateFormat($date, $format);
    }
}

if (!function_exists('date_toDbFormat')) {
    function date_toDbFormat($date)
    {
        $normalized_date = normalize_date($date);
        if (is_null($normalized_date)) {
            //Null date
            return null;
        }

        // Postgres date format
        return DateTime::createFromFormat('Y-m-d H:i:s', $normalized_date)->format('Y-m-d');
    }
}

if (!function_exists('dateTime_toDbFormat')) {
    function dateTime_toDbFormat($date)
    {
        $normalized_date = normalize_date($date);
        if (is_null($normalized_date)) {
            //Null date
            return null;
        }

        // Postgres datetime format
        return $normalized_date;
    }
}

if (!function_exists('normalize_date')) {
    function normalize_date($date)
    {
        // Scan for date time format known
        $validFormats = array(
            'Y-m-d H:i:s',
            // (US) Datetime
            'Y-m-d H:i:s.u',
            // (--) PostgreSQL datetime
            'Y-m-d H:i',
            // (US) Datetime (no secondi)
            'Y-m-d',
            // (US) Date
            'd/m/Y H:i:s',
            // (IT) Datetime
            'd/m/Y H:i',
            // (IT) Datetime (no secondi)
            'd/m/Y', // (IT) Date
        );
        foreach ($validFormats as $format) {
            $dateObject = DateTime::createFromFormat($format, $date);
            if ($dateObject instanceof DateTime && $dateObject->format($format) == $date) {
                return $dateObject->format('Y-m-d H:i:s');
            }
        }

        // Nothing before works... :( Try with strtotime
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

if (!function_exists('array_key_map_data')) {
    function array_key_map_data(array $array, $key, $default = null)
    {
        $new_array = [];
        foreach ($array as $k => $v) {
            $new_array[$v[$key]] = $v;
        }
        return $new_array;
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
        $translation = lang($string);

        if ($translation === false) {
            $CI = get_instance();
            $lang_array = $CI->datab->getLanguage();
            $language = $lang_array ? $lang_array['file'] : $CI->config->item('language');

            $traces = debug_backtrace();
            array_shift($traces);

            $module_name = array_get($params, 'module_name', $CI->layout->getLayoutModule());

            foreach ($traces as $trace) {
                if (!empty($trace['file']) && stripos($trace['file'], 'application/modules')) {
                    $module_name = explode('application/modules/', $trace['file'])[1];
                    $module_name = explode('/', $module_name)[0];
                    break;
                }
            }

            if ($module_name) {
                //debug('test 1');
                $path = sprintf('%smodules/%s/language/%s/%s_lang.php', APPPATH, $module_name, $language, $language);
            } else {
                $path = sprintf('%slanguage/%s/%s_lang.php', APPPATH, $language, $language);
            }

            $val = addslashes($string);
            $add = '$lang[\'' . $val . '\'] = \'' . $val . '\';' . PHP_EOL;

            if (file_exists($path)) {
                include $path;
            }
            $custom_path = sprintf('%slanguage/%s/%s_lang_custom.php', APPPATH, $language, $language);
            if (file_exists($custom_path)) {
                include $custom_path;


            }

            if (!isset($lang) or !array_key_exists($string, $lang) && is_development()) {
                if (is_writable($path) && $string) {
                    $fp = fopen($path, "a+");

                    if (flock($fp, LOCK_EX)) { // acquire an exclusive lock
                        fwrite($fp, $add);
                        fflush($fp); // flush output before releasing the lock
                        flock($fp, LOCK_UN);

                        $CI->lang->language = array_merge($CI->lang->language, [$val => $val]);
                    } else {
                    }
                }
            }

            // Siccome la traduzione è vuota mantieni l'originale
            $translation = lang($string);


            if ($translation === false) {
                $translation = $string;
            }

        }

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
        //debug($translation);
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
                if (is_null($test)) {
                    $parts[] = $segment;
                } elseif ($segment == '..') {
                    if ($test == '..') {
                        $parts[] = $test;
                    }

                    if ($test == '..' || $test == '') {
                        $parts[] = $segment;
                    }
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
    function echo_flush($str, $new_line = '')
    {

        $CI = get_instance();
        if ($CI->input->is_cli_request()) {
            // echo str_pad($str, 2048, ' ');
            echo $str . PHP_EOL;
            // flush();
            // ob_flush();
        } else {
            $newlineTags = array(
                '<br>',
                '<br/>',
                '<br />',
            );
            $str = str_ireplace($newlineTags, PHP_EOL, $str);
            echo $str;
            if ($new_line === 1) {
                echo '<br />';
            } else {
                echo $new_line;
            }
            flush();
            ob_flush();
        }
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

if (!function_exists('remove_objects_from_array_recursive')) {
    function remove_objects_from_array_recursive($arr)
    {
        foreach ($arr as $key => $foo) {
            if (is_object($foo)) {
                unset($arr[$key]);
            } elseif (is_array($foo)) {
                $arr[$key] = remove_objects_from_array_recursive($foo);
            }
        }
        return $arr;
    }
}

if (!function_exists('str_replace_placeholders')) {
    function str_replace_placeholders($string, array $replaces, $caseinsensitive = true, $clearunmatched = false)
    {
        $replaces = remove_objects_from_array_recursive($replaces);
        // Passa da multidimensionale a unidimensionale
        $smoothreplaces = array_smooth($replaces, ' ');
        if ($clearunmatched) {
            $all = array_map(function ($placeholder) {
                return substr($placeholder, 1, strlen($placeholder) - 2);
            }, str_get_placeholders($string));

            $smoothreplaces = array_merge(array_fill_keys($all, null), $smoothreplaces);
        }

        $keys = array_map(function ($k) {
            return '{' . $k . '}';
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
        return ((!empty($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) || @(array_key_exists('SERVER_NAME', $_SERVER) && $_SERVER['SERVER_NAME'] == 'localhost'));
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
        return ($t2 - $t1); // ritorna il tempo trascorso in secondi approx. al microsecondo
    }
}

if (!function_exists('crm_exception_handler')) {
    function crm_exception_handler($ex, $print = true, $sendQueryListing = false)
    {
        // messaggio per utente
        $out = '<div style="padding:10px;background:#efefef;border-radius:7px;">';
        $out .= 'Si &egrave; verificato un errore imprevisto. Il problema &egrave; gi&agrave; stato inoltrato al nostro staff tecnico<br/>';

        if (is_development()) {
            // In ambiente di sviluppo mostro più dettagli
            $out .= 'Eccezione:';
            $out .= '<div style="padding:10px;background:#fff;border-radius:7px;margin-top:10px;">' . $ex->getMessage() . " at line {$ex->getLine()} in file {$ex->getFile()}";

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
                'Trace: ' . $ex->getTraceAsString(),
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}

if (!function_exists('base64_to_jpeg')) {
    function base64_to_jpeg($base64_string, $output_file)
    {
        // open the output file for writing
        $ifp = fopen($output_file, 'wb');

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
                    if ($new_diff != false) {
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
if (!function_exists('delete_older_files')) {
    /**
     * Generate dump
     * @param mixed $destination
     * @param mixed $filename
     * @return bool
     */
    function delete_older_files($folder, $days, $name_contain = "")
    {
        // Converti $days in secondi
        $seconds = $days * 24 * 60 * 60;

        // Verifica se la folder esiste
        if (!is_dir($folder)) {
            my_log('error', "Dump failed... File does note exists or too small. Check");
            return false;
        }

        // Loop attraverso i file nella folder
        $dir = opendir($folder);
        while (($file = readdir($dir)) !== false) {
            // Salta se il file è una folder o una directory padre
            if ($file == '.' || $file == '..' || is_dir("$folder/$file")) {
                continue;
            }

            // Check string contain
            if ($name_contain && strpos($file, $name_contain) === false) {
                continue;
            }

            // Ottieni la data di ultima modifica del file
            $lastModified = filemtime("$folder/$file");

            // Calcola da quanti secondi è stato modificato il file
            $diff = time() - $lastModified;

            // Cancella il file se è più vecchio di $days giorni
            if ($diff > $seconds) {
                unlink("$folder/$file");
            }
        }

        // Chiudi la directory
        closedir($dir);

        return true;
    }
}

if (!function_exists('generate_dump')) {
    /**
     * Generate dump
     * @param mixed $destination
     * @param mixed $filename
     * @return bool
     */
    function generate_dump($destination, $filename = "")
    {
        $CI = &get_instance();
        $DBUSER = $CI->db->username;
        $DBPASSWD = $CI->db->password;
        $DATABASE = $CI->db->database;
        $DBHOST = $CI->db->hostname;

        if (empty($filename)) {
            $filename = "backup-db-" . date("d-m-Y") . ".sql.gz";
        }
        $filepath = $destination . "/" . $filename;

        $cmd = "mysqldump -h $DBHOST -u $DBUSER --password='$DBPASSWD' $DATABASE | gzip --best > $filepath";
        shell_exec($cmd);
        // Check exists zip file and size if already 0.1mb
        if (!file_exists($filepath) || filesize($filepath) < 100000) {
            echo_log('error', "Dump failed... File does note exists or too small. Check");
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('ci_zip_folder')) {
    /**
     * Create zip folder with ZIP Codeigniter library, recommended
     * @param mixed $source
     * @param mixed $destination
     * @param mixed $exclude_dirs
     * @return mixed
     */
    function ci_zip_folder($source, $destination, $exclude_dirs = [])
    {
        $CI = &get_instance();

        $CI->load->library('zip');


        // Check zip extension
        if (!extension_loaded('zip')) {
            echo_log('error', "ZIP Extension not found... zip failed.");
            return false;
        }

        // Read the contents of the directory
        $items = scandir($source);
        // Iterate over the items in the directory
        foreach ($items as $item) {
            // Check if the item is a directory
            if (is_dir($source . '/' . $item)) {
                if ($item != '.' && $item != '..' && substr($item, 0, 1) != '.' && !in_array($item, $exclude_dirs)) {
                    // Item is a directory
                    echo "Add dir: " . $item . "\n";
                    $CI->zip->read_dir($source . $item, false);
                }
            } else {
                echo "Add file: " . $item . "\n";
                $CI->zip->read_file($source . $item);
            }
        }

        return $CI->zip->archive($destination);
    }
}




if (!function_exists('zip_folder')) {
    /**
     * Create zip folder with ZipArchive, Pay attention, exclude_dirs does not work.
     * @param mixed $source
     * @param mixed $destination
     * @param mixed $exclude_dirs
     * @return bool
     */
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

        if (file_exists($destination)) {
            unlink($destination); // Unlink before create because otherwise it add files to existing zip file
        }

        // Folder tree creation before open destination
        // $dirs = explode('/', $destination);
        // array_pop($dirs);
        // foreach ($dirs as $dir) {
        //     if (!is_dir(FCPATH . $dir)) {
        //         mkdir(FCPATH . $dir, DIR_WRITE_MODE, true);
        //     }
        // }

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);


            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) {
                    continue;
                }
                $file = realpath($file);
                // Check if file or dir
                if (is_dir($file) === true) {
                    //Ignore folders excluded
                    if (in_array(str_replace($source . '/', '', $file), $exclude_dirs)) {
                        continue;
                    }
                    if (!$zip->addEmptyDir(str_replace($source . '/', '', $file . '/'))) {
                    }
                } elseif (is_file($file) === true) {
                    if ($exclude_dirs) {
                        $dir_container = explode('/', str_replace($source . '/', '', $file));
                        array_pop($dir_container);
                        $dir_container = implode('/', $dir_container);

                        if (in_array($dir_container, $exclude_dirs)) {
                            continue;
                        }
                    }
                    if (!$zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file))) {
                    }
                }
            }
        } elseif (is_file($source) === true) {
            if (!$zip->addFromString(basename($source), file_get_contents($source))) {
            }
        }

        return $zip->close();
    }
}

if (!function_exists('doGeocoding')) {
    function doGeocoding($address)
    {
        usleep(800000);
        $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($address) . "&countrycodes=it&format=json";

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => 0,
                /* CURLOPT_FOLLOWLOCATION => 1, */
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_USERAGENT => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:56.0) Gecko/20100101 Firefox/56.0",
            )
        );
        $result = curl_exec($ch);
        $data = json_decode($result, true);

        curl_close($ch);

        $place = is_array($data) ? array_shift($data) : null;

        return $place;
    }
}
if (!function_exists('calculateDistance')) {
    function calculateDistance($startPlace, $endPlace)
    {
        //$url = "http://www.yournavigation.org/api/1.0/gosmore.php?flat={$startPlace['lat']}&flon={$startPlace['lon']}&tlat={$endPlace['lat']}&tlon={$endPlace['lon']}&v=motorcar&fast=0&layer=mapnik&format=geojson";
        $url = "https://router.project-osrm.org/route/v1/driving/{$startPlace['lon']},{$startPlace['lat']};{$endPlace['lon']},{$endPlace['lat']}?geometries=geojson&alternatives=false&steps=false&generate_hints=false";

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $url,
                CURLOPT_HEADER => 0,
                /* CURLOPT_FOLLOWLOCATION => 1, */
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 2,
            )
        );
        $result = curl_exec($ch);

        $data = json_decode($result, true);
        curl_close($ch);
        //debug($data, true);
        // Non potendo fare più di 10 richieste al secondo metto un leggero tempo di attesa dopo ogni richiesta
        usleep(250000); // 250 ms è un valore sperimentale che mi permette di poter eseguire tutte le richieste una dopo l'altra
        $km = 0;
        if (empty($data['routes'][0]['legs'][0]['distance'])) {
            return 0;
        } else {
            // foreach ($data['routes'] as $point) {
            //     $km += $point['distance'];
            // }
            $km = $data['routes'][0]['legs'][0]['distance'];
            return $km / 1000;
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
            // debug($v1);
            // debug($v2);
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
        }
        ;
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
            if ($filename[0] === '.') {
                continue;
            }
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
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('.', '..'));
            foreach ($files as $file) {
                (is_dir("$dir/$file")) ? deleteDirRecursive("$dir/$file") : @unlink("$dir/$file");
            }
            return @rmdir($dir);
        } else {
            return true;
        }
    }

    function recurse_copy($src, $dst)
    {
        $src = preg_replace('#/{2,}#', '/', $src);
        $dst = preg_replace('#/{2,}#', '/', $dst);

        $dir = opendir($src);

        if ($dir) {
            @mkdir($dst);

            while (false !== ($file = readdir($dir))) {

                if ($file && ($file != '.') && ($file != '..')) {

                    if (is_dir($src . '/' . $file)) {

                        recurse_copy($src . '/' . $file, $dst . '/' . $file);

                    } else {
                        if (file_exists($src . '/' . $file)) {
                            copy($src . '/' . $file, $dst . '/' . $file);
                        }


                    }

                }

            }
        }

        closedir($dir);

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

if (!function_exists('copy_file')) {
    function copy_file($s1, $s2)
    {
        $path = pathinfo($s2);
        if (!file_exists($path['dirname'])) {
            mkdir($path['dirname'], 0777, true);
        }
        return copy($s1, $s2);
    }
}

if (!function_exists('checkClientVersion')) {
    /**
     * Summary of checkClientVersion
     * @param mixed $update_channel
     * @return bool|string
     */
    function checkClientVersion($update_channel = 4)
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

            $new_version = file_get_contents(OPENBUILDER_BUILDER_BASEURL . "public/client/getLastClientVersionNumber/" . VERSION . "/0/" . $update_channel);

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
                if (stripos($value, '.') === 0) {
                    continue;
                }
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
            'hash' => $CI->security->get_csrf_hash(),
        );
        return $csrf;
    }
}
if (!function_exists('e_json')) {
    function json_recursive_cast($arr)
    {
        foreach ($arr as $key => $el) {
            if (is_array($el)) {
                $arr[$key] = json_recursive_cast($el);
            } else {
                $arr[$key] = (string) $el;
            }
        }
        return $arr;
    }
    function e_json($data, $force_string_cast = false)
    {
        if ($force_string_cast) {
            $data = json_recursive_cast($data);
        }
        echo json_encode($data);
    }
}

if (!function_exists('elapsed_time')) {
    function elapsed_time($start_time)
    {
        // get the current time in seconds
        $end_time = microtime(true);

        // calculate the elapsed time in seconds
        $elapsed_time = $end_time - $start_time;

        // return the elapsed time in seconds
        return $elapsed_time;
    }
}

if (!function_exists('time_elapsed')) {
    function time_elapsed($datetime, $full = false)
    {
        $now = new DateTime();
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . t($v . ($diff->$k > 1 ? 's' : ''));
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) {
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . t(' ago') : t('just now');
    }
}

function getReverseGeocoding($address)
{
    $ch = curl_init();
    
    $get = http_build_query([
        'format' => 'json',
        'addressdetails' => 1,
        'limit' => 1,
        'polygon_svg' => 1,
        'q' => $address,
    ]);
    
    $url = "https://nominatim.openstreetmap.org/search.php?{$get}";

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type' => 'application/json']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 900);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if ($response === false) {
        $response = curl_error($ch);
    } else {
        $response = json_decode($response, true);
    }

    curl_close($ch);

    return $response;
}

if (!function_exists('curlRequest')) {
    function curlRequest($url, $data = [], $isPost = false, $jsonPayload = false, $headers = ['Content-Type: application/json'], $method = 'GET')
    {
        $ch = curl_init();

        $params = null;
        if (!$isPost && !empty($data)) {
            $params = '?' . (is_array($data) || is_object($data) ? http_build_query($data) : $data);
        } elseif ($isPost && !empty($data)) {
            if ($jsonPayload) {
                $data = json_encode($data);
            }

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_URL, $url . $params);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.110 Safari/537.36");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);

        $data = curl_exec($ch);

        if ($data === false) {
            die(curl_error($ch));
        }

        curl_close($ch);

        return $data;
    }
}

if (!function_exists('e_money')) {
    function e_money($number, $format = '{number}')
    {
        $return = str_ireplace('{number}', number_format($number, 2, ',', '.'), $format);
        echo $return;
    }
}


if (!function_exists('echo_log')) {
    function echo_log($type, $message)
    {
        echo $message . "\r\n";
        my_log($type, $message);
    }
}

if (!function_exists('set_log_scope')) {
    function set_log_scope($scope)
    {
        static $_log;

        if ($_log === NULL) {
            // references cannot be directly assigned to static variables, so we use an array
            $_log[0] =& load_class('Log', 'core');
        }

        $_log[0]->setScope($scope);

    }
}

if (!function_exists('my_log')) {
    function my_log($level, $message, $scope = false)
    {

        static $_log;

        if ($_log === NULL) {
            // references cannot be directly assigned to static variables, so we use an array
            $_log[0] =& load_class('Log', 'core');
        }

        $_log[0]->write_log($level, $message, $scope);
    }
}

if (!function_exists('progress')) {
    function progress($current, $total, $selector_id = 'js_progress')
    {
        if (empty($total) || $total == 0) {
            return 0;
        }

        $perc = number_format(100 * $current / $total, 2);

        if ($selector_id != 'js_progress') {
            $prepend = "$selector_id: ";
        } else {
            $prepend = '';
        }

        if (!is_cli()) {
            echo_flush(
                '
        <script>
            var progress_div = document.getElementById("' . $selector_id . '");
            if (!progress_div) {
                var progress_div = document.createElement("div");
                progress_div.setAttribute("id", "' . $selector_id . '");
                document.write(progress_div.outerHTML);
            }
            progress_div.innerText = "' . $prepend . $perc . ' of 100%";
        </script>
        '
            );
        } else {
            echo "\r{$perc} of 100%";
        }

        return $perc;
    }
}
if (!function_exists('createFolderRecursive')) {
    function createFolderRecursive($path, $remove_last_chunk = false)
    {
        // Remove FCPATH 
        $path = str_replace(FCPATH, "", $path);

        $exploded = explode('/', $path);
        $parents = '';
        foreach ($exploded as $key => $folder) {
            if (!($remove_last_chunk && ($key + 1 == count($exploded)))) {
                if (!is_dir($parents . $folder)) {
                    @mkdir($parents . $folder, 0755, true);
                }
                $parents .= $folder . '/';
            }
        }
    }
}

if (!function_exists('imagecreatefromany')) {
    function imagecreatefromany($src)
    {
        // switch (strtolower(substr($src, -4))) {
        //     case '.jpg':
        //     case 'jpeg':
        //         $img = imagecreatefromjpeg($src);
        //         break;
        //     case '.png':
        //         $img = imagecreatefrompng($src);
        //         break;
        //     case '.gif':
        //         $img = imagecreatefromgif($src);
        //         break;
        //     default:
        //         debug("Formato immagine '$src' non riconosciuto!", true);
        //         break;
        // }
        $img = imagecreatefromstring(file_get_contents($src));
        return $img;
    }
}

if (!function_exists('mime2ext')) {
    function mime2ext($mime)
    {
        $mime_map = [
            'video/3gpp2' => '3g2',
            'video/3gp' => '3gp',
            'video/3gpp' => '3gp',
            'application/x-compressed' => '7zip',
            'audio/x-acc' => 'aac',
            'audio/ac3' => 'ac3',
            'application/postscript' => 'ai',
            'audio/x-aiff' => 'aif',
            'audio/aiff' => 'aif',
            'audio/x-au' => 'au',
            'video/x-msvideo' => 'avi',
            'video/msvideo' => 'avi',
            'video/avi' => 'avi',
            'application/x-troff-msvideo' => 'avi',
            'application/macbinary' => 'bin',
            'application/mac-binary' => 'bin',
            'application/x-binary' => 'bin',
            'application/x-macbinary' => 'bin',
            'image/bmp' => 'bmp',
            'image/x-bmp' => 'bmp',
            'image/x-bitmap' => 'bmp',
            'image/x-xbitmap' => 'bmp',
            'image/x-win-bitmap' => 'bmp',
            'image/x-windows-bmp' => 'bmp',
            'image/ms-bmp' => 'bmp',
            'image/x-ms-bmp' => 'bmp',
            'application/bmp' => 'bmp',
            'application/x-bmp' => 'bmp',
            'application/x-win-bitmap' => 'bmp',
            'application/cdr' => 'cdr',
            'application/coreldraw' => 'cdr',
            'application/x-cdr' => 'cdr',
            'application/x-coreldraw' => 'cdr',
            'image/cdr' => 'cdr',
            'image/x-cdr' => 'cdr',
            'zz-application/zz-winassoc-cdr' => 'cdr',
            'application/mac-compactpro' => 'cpt',
            'application/pkix-crl' => 'crl',
            'application/pkcs-crl' => 'crl',
            'application/x-x509-ca-cert' => 'crt',
            'application/pkix-cert' => 'crt',
            'text/css' => 'css',
            'text/x-comma-separated-values' => 'csv',
            'text/comma-separated-values' => 'csv',
            'application/vnd.msexcel' => 'csv',
            'application/x-director' => 'dcr',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/x-dvi' => 'dvi',
            'message/rfc822' => 'eml',
            'application/x-msdownload' => 'exe',
            'video/x-f4v' => 'f4v',
            'audio/x-flac' => 'flac',
            'video/x-flv' => 'flv',
            'image/gif' => 'gif',
            'application/gpg-keys' => 'gpg',
            'application/x-gtar' => 'gtar',
            'application/x-gzip' => 'gzip',
            'application/mac-binhex40' => 'hqx',
            'application/mac-binhex' => 'hqx',
            'application/x-binhex40' => 'hqx',
            'application/x-mac-binhex40' => 'hqx',
            'text/html' => 'html',
            'image/x-icon' => 'ico',
            'image/x-ico' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
            'text/calendar' => 'ics',
            'application/java-archive' => 'jar',
            'application/x-java-application' => 'jar',
            'application/x-jar' => 'jar',
            'image/jp2' => 'jp2',
            'video/mj2' => 'jp2',
            'image/jpx' => 'jp2',
            'image/jpm' => 'jp2',
            'image/jpeg' => 'jpeg',
            'image/pjpeg' => 'jpeg',
            'application/x-javascript' => 'js',
            'application/json' => 'json',
            'text/json' => 'json',
            'application/vnd.google-earth.kml+xml' => 'kml',
            'application/vnd.google-earth.kmz' => 'kmz',
            'text/x-log' => 'log',
            'audio/x-m4a' => 'm4a',
            'application/vnd.mpegurl' => 'm4u',
            'audio/midi' => 'mid',
            'application/vnd.mif' => 'mif',
            'video/quicktime' => 'mov',
            'video/x-sgi-movie' => 'movie',
            'audio/mpeg' => 'mp3',
            'audio/mpg' => 'mp3',
            'audio/mpeg3' => 'mp3',
            'audio/mp3' => 'mp3',
            'video/mp4' => 'mp4',
            'video/mpeg' => 'mpeg',
            'application/oda' => 'oda',
            'audio/ogg' => 'ogg',
            'video/ogg' => 'ogg',
            'application/ogg' => 'ogg',
            'application/x-pkcs10' => 'p10',
            'application/pkcs10' => 'p10',
            'application/x-pkcs12' => 'p12',
            'application/x-pkcs7-signature' => 'p7a',
            'application/pkcs7-mime' => 'p7c',
            'application/x-pkcs7-mime' => 'p7c',
            'application/x-pkcs7-certreqresp' => 'p7r',
            'application/pkcs7-signature' => 'p7s',
            'application/pdf' => 'pdf',
            'application/octet-stream' => 'pdf',
            'application/x-x509-user-cert' => 'pem',
            'application/x-pem-file' => 'pem',
            'application/pgp' => 'pgp',
            'application/x-httpd-php' => 'php',
            'application/php' => 'php',
            'application/x-php' => 'php',
            'text/php' => 'php',
            'text/x-php' => 'php',
            'application/x-httpd-php-source' => 'php',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'application/powerpoint' => 'ppt',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.ms-office' => 'ppt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'application/x-photoshop' => 'psd',
            'image/vnd.adobe.photoshop' => 'psd',
            'audio/x-realaudio' => 'ra',
            'audio/x-pn-realaudio' => 'ram',
            'application/x-rar' => 'rar',
            'application/rar' => 'rar',
            'application/x-rar-compressed' => 'rar',
            'audio/x-pn-realaudio-plugin' => 'rpm',
            'application/x-pkcs7' => 'rsa',
            'text/rtf' => 'rtf',
            'text/richtext' => 'rtx',
            'video/vnd.rn-realvideo' => 'rv',
            'application/x-stuffit' => 'sit',
            'application/smil' => 'smil',
            'text/srt' => 'srt',
            'image/svg+xml' => 'svg',
            'application/x-shockwave-flash' => 'swf',
            'application/x-tar' => 'tar',
            'application/x-gzip-compressed' => 'tgz',
            'image/tiff' => 'tiff',
            'text/plain' => 'txt',
            'text/x-vcard' => 'vcf',
            'application/videolan' => 'vlc',
            'text/vtt' => 'vtt',
            'audio/x-wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/wav' => 'wav',
            'application/wbxml' => 'wbxml',
            'video/webm' => 'webm',
            'audio/x-ms-wma' => 'wma',
            'application/wmlc' => 'wmlc',
            'video/x-ms-wmv' => 'wmv',
            'video/x-ms-asf' => 'wmv',
            'application/xhtml+xml' => 'xhtml',
            'application/excel' => 'xl',
            'application/msexcel' => 'xls',
            'application/x-msexcel' => 'xls',
            'application/x-ms-excel' => 'xls',
            'application/x-excel' => 'xls',
            'application/x-dos_ms_excel' => 'xls',
            'application/xls' => 'xls',
            'application/x-xls' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xlsx',
            'application/xml' => 'xml',
            'text/xml' => 'xml',
            'text/xsl' => 'xsl',
            'application/xspf+xml' => 'xspf',
            'application/x-compress' => 'z',
            'application/x-zip' => 'zip',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'application/s-compressed' => 'zip',
            'multipart/x-zip' => 'zip',
            'text/x-scriptzsh' => 'zsh',
        ];

        return isset($mime_map[$mime]) === true ? $mime_map[$mime] : false;
    }
}

if (!function_exists('is_base64')) {
    function is_base64($s)
    {
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s)) {
            return false;
        }

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($s, true);
        if (false === $decoded) {
            return false;
        }

        // Encode the string again
        if (base64_encode($decoded) != $s) {
            return false;
        }

        return true;
    }
}

if (!function_exists('slugify')) {
    function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

if (!function_exists('GetDirectorySize')) {
    function GetDirectorySize($path)
    {
        $bytestotal = 0;
        $path = realpath($path);
        if ($path !== false && $path != '' && file_exists($path)) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
                $bytestotal += $object->getSize();
            }
        }
        return $bytestotal;
    }
}

if (!function_exists('human_filesize')) {
    function human_filesize($bytes, $dec = 2)
    {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$dec}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }
}

if (!function_exists('rgb_string_to_hex')) {
    function rgb_string_to_hex($rgba)
    {
        if (strpos($rgba, '#') === 0) {
            return substr($rgba, 1);
        }

        preg_match('/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i', $rgba, $by_color);

        return sprintf('%02x%02x%02x', $by_color[1], $by_color[2], $by_color[3]);
    }
}

if (!function_exists('br2nl')) {
    function br2nl($input)
    {
        return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n", "", str_replace("\r", "", htmlspecialchars_decode($input))));
    }
}

if (!function_exists('hours_to_human')) {
    function hours_to_human($decimal_hours, $return_formatted = true, $return_seconds = false)
    {
        if (!is_numeric($decimal_hours)) {
            return false;
        }

        // start by converting to seconds
        $seconds = ($decimal_hours * 3600);
        // we're given hours, so let's get those the easy way
        $hours = floor($decimal_hours);
        // since we've "calculated" hours, let's remove them from the seconds variable
        $seconds -= $hours * 3600;
        // calculate minutes left
        $minutes = floor($seconds / 60);
        // remove those from seconds as well
        $seconds -= $minutes * 60;

        $hours = str_pad($hours, 2, 0, STR_PAD_LEFT);
        $minutes = str_pad($minutes, 2, 0, STR_PAD_LEFT);
        $seconds = str_pad($seconds, 2, 0, STR_PAD_LEFT);

        $hours_label = ($hours === 1) ? t('hour') : t('hours');
        $minutes_label = ($minutes === 1) ? t('minute') : t('minutes');
        $seconds_label = ($seconds === 1) ? t('second') : t('seconds');

        $return = '';

        if ($hours > 0) {
            $return .= "{$hours} {$hours_label} ";
        }

        if ($minutes > 0) {
            $return .= "{$minutes} {$minutes_label} ";
        }

        if ($return_seconds && $seconds > 0) {
            $return .= "{$seconds} {$seconds_label} ";
        }

        return $return_formatted ? $return : ['hours' => $hours, 'minutes' => $minutes, 'seconds' => $seconds];
    }
}