<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author    EllisLab Dev Team
 * @copyright    Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright    Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license    http://opensource.org/licenses/MIT    MIT License
 * @link    https://codeigniter.com
 * @since    Version 2.0.0
 * @filesource
 */
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * CodeIgniter Session Class
 *
 * @package        CodeIgniter
 * @subpackage    Libraries
 * @category    Sessions
 * @author        Andrey Andreev
 * @link        https://codeigniter.com/user_guide/libraries/sessions.html
 */
class MY_Session extends CI_Session
{

    public function __construct(array $params = array())
    {
        if ($this->ignore_sessions()) {
            return;
        }

        parent::__construct();
    }

    private function ignore_sessions()
    {
        $uri = str_replace("//", "/", $_SERVER['REQUEST_URI']);
        if (strpos($uri, '/rest/') === 0) {
            return true;
        }

        // if ($current_controller == 'Public_Controller') {
        //     return true;
        // }

        return false;
    }
    // ------------------------------------------------------------------------

    /**
     * Configuration
     *
     * Handle input parameters and configuration defaults
     *
     * @param    array    &$params    Input parameters
     * @return    void
     */
    protected function _configure(&$params)
    {

        $expiration = config_item('sess_expiration');

        if (isset($params['cookie_lifetime'])) {
            $params['cookie_lifetime'] = (int) $params['cookie_lifetime'];
        } else {
            $params['cookie_lifetime'] = (!isset($expiration) && config_item('sess_expire_on_close'))
            ? 0 : (int) $expiration;
        }

        isset($params['cookie_name']) or $params['cookie_name'] = config_item('sess_cookie_name');
        if (empty($params['cookie_name'])) {
            $params['cookie_name'] = ini_get('session.name');
        } else {

            ini_set('session.name', $params['cookie_name']);

        }

        isset($params['cookie_path']) or $params['cookie_path'] = config_item('cookie_path');
        isset($params['cookie_domain']) or $params['cookie_domain'] = config_item('cookie_domain');
        isset($params['cookie_secure']) or $params['cookie_secure'] = (bool) config_item('cookie_secure');

        $secure_cookie = (bool) config_item('cookie_secure');
        $cookie_samesite = config_item('cookie_samesite');

        session_set_cookie_params(
            [
                'samesite' => $cookie_samesite,
                'secure' => $secure_cookie,
                'path' => config_item('cookie_path'),
                'domain' => config_item('cookie_domain'),
                'httponly' => config_item('cookie_httponly'),
            ]
        );

        if (empty($expiration)) {
            $params['expiration'] = (int) ini_get('session.gc_maxlifetime');
        } else {
            $params['expiration'] = (int) $expiration;
            ini_set('session.gc_maxlifetime', $expiration);
        }

        $params['match_ip'] = (bool) (isset($params['match_ip']) ? $params['match_ip'] : config_item('sess_match_ip'));

        isset($params['save_path']) or $params['save_path'] = config_item('sess_save_path');

        $this->_config = $params;

        // Security is king
        ini_set('session.use_trans_sid', 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        $this->_configure_sid_length();
    }
}
