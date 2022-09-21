<?php

class MY_Output extends CI_Output
{
    public $path = null;
    public $tags = [];
    /**
     * Write Cache
     *
     * @param   string  $output Output data to cache
     * @return  void
     */
    public function _write_cache($output)
    {
        //die('test');
        $CI = &get_instance();

        if ($CI->mycache->isCacheEnabled()) {
//-XXX CUSTOM------------------------------------
            $cache_path = $this->cachePath();

            if ($cache_path == false) {
                return;
            }

// echo '<pre>';
// print_r($_SESSION);
// die();

//-----------------------------------------------
            if (!is_dir($cache_path)) {
                mkdir($cache_path, DIR_WRITE_MODE, true);
            }

            if (!is_dir($cache_path) or !is_really_writable($cache_path)) {
                log_message('error', 'Unable to write cache file: ' . $cache_path);
                return;
            }

            $uri = $CI->config->item('base_url')
            . $CI->config->item('index_page')
            . $CI->uri->uri_string();

            if (($cache_query_string = $CI->config->item('cache_query_string')) && !empty($_SERVER['QUERY_STRING'])) {
                if (is_array($cache_query_string)) {
                    $uri .= '?' . http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
                } else {
                    $uri .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            $cache_path .= md5($uri);

//die($uri.serialize($_SESSION[SESS_WHERE_DATA]));

            if (!$fp = @fopen($cache_path, 'w+b')) {
                log_message('error', 'Unable to write cache file: ' . $cache_path);
                return;
            }

            if (!flock($fp, LOCK_EX)) {
                log_message('error', 'Unable to secure a file lock for file at: ' . $cache_path);
                fclose($fp);
                return;
            }

// If output compression is enabled, compress the cache
// itself, so that we don't have to do that each time
// we're serving it
            if ($this->_compress_output === true) {
                $output = gzencode($output);

                if ($this->get_header('content-type') === null) {
                    $this->set_content_type($this->mime_type);
                }
            }

            $expire = time() + ($this->cache_expiration * 60);

// Put together our serialized info.
            $cache_info = serialize(array(
                'expire' => $expire,
                'headers' => $this->headers,
            ));

            $output = $cache_info . 'ENDCI--->' . $output;
            //debug($cache_path, true);
            for ($written = 0, $length = self::strlen($output); $written < $length; $written += $result) {
                if (($result = fwrite($fp, self::substr($output, $written))) === false) {
                    break;
                }
            }

            flock($fp, LOCK_UN);
            fclose($fp);

            if (!is_int($result)) {
                @unlink($cache_path);
                log_message('error', 'Unable to write the complete cache content at: ' . $cache_path);
                return;
            }

            chmod($cache_path, 0640);

//Last step: save this cacheid in tags to easily remove later (when db invalidate)
            $relative_cache_path = implode('/', array_slice(explode('/', $cache_path), -3, 3, true));

            $this->saveTagsMapping($relative_cache_path);

            log_message('debug', 'Cache file written: ' . $cache_path);

// Send HTTP cache-control headers to browser to match file cache settings.
            $this->set_cache_header($_SERVER['REQUEST_TIME'], $expire);
        }

    }

    // --------------------------------------------------------------------

    /**
     * Update/serve cached output
     *
     * @uses    CI_Config
     * @uses    CI_URI
     *
     * @param   object  &$CFG   CI_Config class instance
     * @param   object  &$URI   CI_URI class instance
     * @return  bool    TRUE on success or FALSE on failure
     */
    public function _display_cache(&$CFG, &$URI)
    {

        //-XXX CUSTOM------------------------------------
        $cache_path = $this->cachePath($CFG);
        //$cache_path = ($CFG->item('cache_path') === '') ? APPPATH.'cache/' : $CFG->item('cache_path');
        //-----------------------------------------------

        // Build the file path. The file name is an MD5 hash of the full URI
        $uri = $CFG->item('base_url') . $CFG->item('index_page') . $URI->uri_string;

        if (($cache_query_string = $CFG->item('cache_query_string')) && !empty($_SERVER['QUERY_STRING'])) {
            if (is_array($cache_query_string)) {
                $uri .= '?' . http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
            } else {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
            }
        }

        $filepath = $cache_path . md5($uri);

        if (!file_exists($filepath) or !$fp = @fopen($filepath, 'rb')) {
            //die($uri.serialize($_SESSION[SESS_WHERE_DATA]));
            return false;
        }

        flock($fp, LOCK_SH);

        $cache = (filesize($filepath) > 0) ? fread($fp, filesize($filepath)) : '';

        flock($fp, LOCK_UN);
        fclose($fp);

        // Look for embedded serialized file info.
        if (!preg_match('/^(.*)ENDCI--->/', $cache, $match)) {
            return false;
        }

        $cache_info = unserialize($match[1]);
        $expire = $cache_info['expire'];

        $last_modified = filemtime($filepath);

        // Has the file expired?
        if ($_SERVER['REQUEST_TIME'] >= $expire && is_really_writable($cache_path)) {
            // If so we'll delete it.
            @unlink($filepath);
            log_message('debug', 'Cache file has expired. File deleted.');
            return false;
        }

        // Send the HTTP cache control headers
        $this->set_cache_header($last_modified, $expire);

        // Add headers from cache file.
        foreach ($cache_info['headers'] as $header) {
            $this->set_header($header[0], $header[1]);
        }

        //-XXX CUSTOM------------------------------------
        $exTime = $this->executionTime();
        setcookie('exe_time', "$exTime", time() + 120, '/');
        //-----------------------------------------------

        // Display the cache
        log_message('debug', "Load from cache: uri: '$uri', path: '$filepath'");
        $this->_display(self::substr($cache, self::strlen($match[0])));

        log_message('debug', 'Cache file is current. Sending it to browser.');
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Delete cache
     *
     * @param   string  $uri    URI string
     * @return  bool
     */
    public function delete_cache($uri = '')
    {
        die('test3');
        $CI = &get_instance();
        //-XXX CUSTOM------------------------------------
        $cache_path = $CI->config->item('cache_path');
        $cache_path = ($cache_path === '') ? APPPATH . 'cache/' : $cache_path;
        //-----------------------------------------------

        if (!is_dir($cache_path)) {
            log_message('error', 'Unable to find cache path: ' . $cache_path);
            return false;
        }

        if (empty($uri)) {
            $uri = $CI->uri->uri_string();

            if (($cache_query_string = $CI->config->item('cache_query_string')) && !empty($_SERVER['QUERY_STRING'])) {
                if (is_array($cache_query_string)) {
                    $uri .= '?' . http_build_query(array_intersect_key($_GET, array_flip($cache_query_string)));
                } else {
                    $uri .= '?' . $_SERVER['QUERY_STRING'];
                }
            }
        }

        //-XXX CUSTOM------------------------------------
        $passed = true;
        $path1 = $cache_path . 'fullpages/' . md5($CI->config->item('base_url') . $CI->config->item('index_page') . ltrim($uri, '/'));

        if (!@unlink($path1)) {
            log_message('error', 'Unable to delete cache file for ' . $uri);
            $passed = false;
        }

        //-----------------------------------------------

        return $passed;
    }

    private function cachePath(&$CFG = false)
    {
        try {
            $CI = &get_instance();

        } catch (Error $e) {
            return false;
        }
        $user_id = $CI->auth->get('users_id');
        if (!$this->path || !$user_id) {
            $hasSession = !empty($_COOKIE['ci_session']);

            if (empty($CFG)) {
                $CI = &get_instance();

                $CFG = $CI->config;
            }

            $this->path = $CFG->item('cache_path');
            $this->path = empty($path) ? APPPATH . 'cache/' : $path;

            $this->path .= 'fullpages/';

            if ($hasSession) {

                // ob_start();
                $CI = &get_instance();
                $user_id = $CI->auth->get('users_id');

                if (!empty($_SESSION[SESS_WHERE_DATA])) {
                    $filters_md5 = md5(serialize($_SESSION[SESS_WHERE_DATA]));
                } else {
                    $filters_md5 = '';
                }

                if (!empty($CI->input->post())) {
                    $post_md5 = md5(serialize($CI->input->post()));
                } else {
                    $post_md5 = '';
                }

                if (!empty($user_id)) {
                    log_message('debug', "utente connesso: {$user_id}");
                    $this->path .= $user_id . '/';
                    if ($filters_md5) {
                        $this->path .= $filters_md5 . '/';

                    }
                    if ($post_md5) {
                        $this->path .= $post_md5 . '/';

                    }
                } else {
                    log_message('debug', "utente non trovato in sessione!");
                    return false;
                }

                //ob_end_clean();

            } else {
                //debug('TODO: come fa a nn esserci una sessione?',true);
            }
        }

        return $this->path;
    }

    public function executionTime()
    {

        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $total_time = round(($time), 4); //second unit
        return $total_time;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }
    public function addTag($tag)
    {
        if (!in_array($tag, $this->tags)) {
            $this->tags[] = $tag;
        }

    }
    public function addTags($tags)
    {
        foreach ($tags as $tag) {
            $this->addTag($tag);
        }

    }

    public function saveTagsMapping($id)
    {
        $CI = &get_instance();
        $current_mapping = $CI->mycache->getTagsMapping();
        foreach ($this->tags as $tag) {

            if (!array_key_exists($tag, $current_mapping)) {
                $current_mapping[$tag] = [];
            }
            if (!in_array($id, $current_mapping[$tag])) {
                //Add the id to the tag mapping
                $current_mapping[$tag][] = $id;
            }
        }
        $CI->mycache->writeMappingFile($current_mapping);
    }

}
