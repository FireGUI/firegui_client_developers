<?php

if (!function_exists('debug')) {

    function debug($var, $die = false, $trace = true) {

        if (filter_input(INPUT_SERVER, 'REMOTE_ADDR') !== '88.86.183.74' && gethostname() !== 'sfera') {
            return;
        }

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

if (!function_exists('var_swap')) {

    function var_swap(&$var1, &$var2) {
        $tmp = $var1;
        $var1 = $var2;
        $var2 = $tmp;
    }

}


if (!function_exists('debug_caller')) {

    function debug_caller() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        debug(array_pop($trace), false, false);
    }

}


if (!function_exists('isValidDateRange')) {

    function isValidDateRange($dateRange) {
        return preg_match("/^[\[\(][0-9]{4}-[0-9]{2}-[0-9]{2},[0-9]{4}-[0-9]{2}-[0-9]{2}[\)\]]$/", $dateRange);
    }

}

if (!function_exists('dateRange_to_dates')) {

    function dateRange_to_dates($date_range) {
        $dates = explode(',', trim($date_range, "[)(]"));
        if (count($dates) == 2) {
            $dates[1] = date('Y-m-d', strtotime('-1 day', strtotime($dates[1])));
        }
        return $dates;
    }

}

if (!function_exists('dateFormat')) {

    function dateFormat($date, $format = 'd/m/Y') {
        return ($timestamp = strtotime($date)) ? date($format, $timestamp) : $date;
    }

}

if (!function_exists('dateTimeFormat')) {

    function dateTimeFormat($date, $format = 'd/m/Y H:i:s') {
        return dateFormat($date, $format);
    }

}

if (!function_exists('date_toDbFormat')) {

    function date_toDbFormat($date) {
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

    function dateTime_toDbFormat($date) {
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

    function normalize_date($date) {
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

    function extract_intrange_data($value) {
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
                        $min = is_numeric($minmax[0]) ? $minmax[0] + 1: null;
                        break;
                    default:

                        break;
                }
                switch ($value[strlen($value) - 1]) {
                    case ']':
                        $max = $minmax[1];
                        break;
                    case ')':
                        $max = is_numeric($minmax[1]) ? $minmax[1] - 1: null;
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

    function array_key_map(array $array, $key, $default = null) {
        return array_map(function ($item) use($key, $default) {
            return ((is_array($item) && array_key_exists($key, $item)) ? $item[$key] : $default);
        }, $array);
    }

}

if (!function_exists('e')) {

    function e($string, $ucfirst = true, $params = array()) {
        echo t($string, $ucfirst, $params);
    }

}

if (!function_exists('t')) {

    function t($string, $ucfirst = false, $params = array()) {
        $string_translated = lang($string);
        if ($string_translated !== false) {
            $string = $string_translated;
        } else {
            $CI = & get_instance();

            $language = $CI->session->userdata('language');
            if ($language === FALSE) {
                $language = $CI->config->item('language');
                $CI->session->set_userdata('language', $language);
            }

            $add = '$lang[\'' . addslashes($string) . '\'] = \'' . addslashes($string) . '\';' . PHP_EOL;
            if (is_writable(APPPATH . 'language/' . $language . '/' . $language . '_lang.php')) {
                file_put_contents(APPPATH . 'language/' . $language . '/' . $language . '_lang.php', $add, FILE_APPEND | LOCK_EX);
            }

            // Ricarica file di lingue
            $CI->lang->is_loaded = array();
            $CI->load->language($language, $language);
        }

        // Rimpiazza parametri
        if (is_array($params) && !empty($params)) {
            foreach ($params as $v) {
                $string = preg_replace("/%s/", $v, $string, 1);
            }
        }

        // Maiuscole/Minuscole
        switch ($ucfirst) {
            case 1:
                $string = ucfirst($string);
                break;
            case 2:
                $string = strtoupper($string);
                break;
            case 3:
                $string = ucwords($string);
                break;
            default:
                break;
        }
        return $string;
    }

}

if (!function_exists('normalize_path')) {

    function normalize_path($path) {
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
                }
                else {
                    $parts[] = $test;
                    $parts[] = $segment;
                }
            }
        }
        return implode('/', $parts);
    }

}

if (!function_exists('echo_flush')) {

    function echo_flush($str) {
        echo str_pad($str, 2048, ' ');
        flush();
        ob_flush();
    }

}

if (!function_exists('generateRandomPassword')) {

    function generateRandomPassword($length = 8, $random_case = false, $user_friendly = false) {

        $result = '';
        $consonanti = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'q', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z');
        $vocali = array('a', 'e', 'i', 'o', 'u');
        $numeri = range(0, 9);

        $letters = array_merge($consonanti, $vocali, $numeri);

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

    function str_replace_placeholders($string, array $replaces, $caseinsensitive = true, $clearunmatched = false) {

        // Passa da multidimensionale a unidimensionale
        $smoothreplaces = array_smooth($replaces, ' ');
        if ($clearunmatched) {
            $all = array_map(function($placeholder) {
                return substr($placeholder, 1, strlen($placeholder) - 2);
            }, str_get_placeholders($string));

            $smoothreplaces = array_merge(array_fill_keys($all, null), $smoothreplaces);
        }

        $keys = array_map(function($k) {
            return "{{$k}}";
        }, array_keys($smoothreplaces));
        $vals = array_values($smoothreplaces);


        $replaced = $caseinsensitive ? str_ireplace($keys, $vals, $string) : str_replace($keys, $vals, $string);

        return $replaced;
    }

}

if (!function_exists('str_get_placeholders')) {

    function str_get_placeholders($string) {
        $matches = array();
        if (preg_match_all('/\{.[^\{\}]+\}/', $string, $matches) && count($matches) > 0) {
            return array_shift($matches);
        } else {
            return [];
        }
    }

}

if (!function_exists('array_smooth')) {

    function array_smooth(array $array, $separator, $depth = null) {
        $output = [];
        foreach ($array as $key => $val) {
            if (is_array($val) && (is_null($depth) OR $depth > 0)) {
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

