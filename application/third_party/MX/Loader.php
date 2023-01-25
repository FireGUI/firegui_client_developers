<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/**
 * Modular Extensions - HMVC
 *
 * Adapted from the CodeIgniter Core Classes
 * @link    http://codeigniter.com
 *
 * Description:
 * This library extends the CodeIgniter CI_Loader class
 * and adds features allowing use of modules and the HMVC design pattern.
 *
 * Install this file as application/third_party/MX/Loader.php
 *
 * @copyright    Copyright (c) 2015 Wiredesignz
 * @version     5.5
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
 **/
class MX_Loader extends CI_Loader
{
    protected $_module;

    public $_ci_plugins = array();
    public $_ci_cached_vars = array();

    /** Initialize the loader variables **/
    public function initialize($controller = null)
    {
        /* set the module name */
        $this->_module = CI::$APP->router->fetch_module();

        if ($controller instanceof MX_Controller) {
            /* reference to the module controller */
            $this->controller = $controller;

            /* references to ci loader variables */
            foreach (get_class_vars('CI_Loader') as $var => $val) {
                if ($var != '_ci_ob_level') {
                    $this->$var = &CI::$APP->load->$var;
                }
            }
        } else {
            parent::initialize();

            /* autoload module items */
            $this->_autoloader(array());
        }

        /* add this module path to the loader variables */
        $this->_add_module_paths($this->_module);
    }

    /** Add a module path loader variables **/
    public function _add_module_paths($module = '')
    {
        if (empty($module)) {
            return;
        }

        foreach (Modules::$locations as $location => $offset) {
            /* only add a module path if it exists */
            if (is_dir($module_path = $location . $module . '/') && !in_array($module_path, $this->_ci_model_paths)) {
                array_unshift($this->_ci_model_paths, $module_path);
            }
        }
    }

    /** Load a module config file **/
    public function config($file, $use_sections = false, $fail_gracefully = false)
    {
        return CI::$APP->config->load($file, $use_sections, $fail_gracefully, $this->_module);
    }

    /** Load the database drivers **/
    public function database($params = '', $return = false, $query_builder = null)
    {
        if (
            $return === false && $query_builder === null &&
            isset(CI::$APP->db) && is_object(CI::$APP->db) && !empty(CI::$APP->db->conn_id)
        ) {
            return false;
        }

        require_once BASEPATH . 'database/DB' . EXT;

        if ($return === true) {
            return DB($params, $query_builder);
        }

        CI::$APP->db = DB($params, $query_builder);

        return $this;
    }

    /** Load a module helper **/
    public function helper($helper = array())
    {
        if (is_array($helper)) {
            return $this->helpers($helper);
        }

        if (isset($this->_ci_helpers[$helper])) {
            return;
        }

        list($path, $_helper) = Modules::find($helper . '_helper', $this->_module, 'helpers/');

        if ($path === false) {
            //201910170932 - Check if helper exists eventually in a folder called "custom"
            if (file_exists(APPPATH . "helpers/custom/{$helper}_helper.php")) {
                $helper = "custom/$helper";
            }
            //echo ($helper);

            return parent::helper($helper);
        } else {
            Modules::load_file($_helper, $path);
            $this->_ci_helpers[$_helper] = true;
            return $this;
        }
    }

    /** Load an array of helpers **/
    public function helpers($helpers = array())
    {
        foreach ($helpers as $_helper) {
            $this->helper($_helper);
        }

        return $this;
    }

    /** Load a module language file **/
    public function language($langfile, $idiom = '', $return = false, $add_suffix = true, $alt_path = '')
    {
        CI::$APP->lang->load($langfile, $idiom, $return, $add_suffix, $alt_path, $this->_module);

        //Now add modules translations files
        if (is_dir(APPPATH . 'modules/')) {
            $modules = scandir(APPPATH . 'modules/');
            foreach ($modules as $module) {
                if (is_dir(APPPATH . 'modules/' . $module)) {
                    if (file_exists(APPPATH . 'modules/' . $module . '/language/' . $langfile . '/' . "{$idiom}_lang.php")) {
                        include APPPATH . 'modules/' . $module . '/language/' . $langfile . '/' . "{$idiom}_lang.php";
                        $module_lang = $lang;
                        CI::$APP->lang->language = array_merge(CI::$APP->lang->language, $module_lang);
                        $lang = [];
                    }
                }
            }
        }

        return $this;
    }

    public function languages($languages)
    {
        foreach ($languages as $_language) {
            $this->language($_language);
        }

        return $this;
    }

    /** Load a module library **/
    public function library($library, $params = null, $object_name = null)
    {
        if (is_array($library)) {
            return $this->libraries($library);
        }

        $class = strtolower(basename($library));

        if (isset($this->_ci_classes[$class]) && $_alias = $this->_ci_classes[$class]) {
            return $this;
        }

        ($_alias = strtolower($object_name)) or $_alias = $class;

        list($path, $_library) = Modules::find($library, $this->_module, 'libraries/');

        /* load library config file as params */
        if ($params == null) {
            list($path2, $file) = Modules::find($_alias, $this->_module, 'config/');
            ($path2) && $params = Modules::load_file($file, $path2, 'config');
        }

        if ($path === false) {
            $uc_library = ucfirst($library);
            //201910170932 - Check if library exists eventually in a folder called "custom"
            if (file_exists(APPPATH . "libraries/custom/{$uc_library}.php")) {
                $library = "custom/$library";
            }
            $this->_ci_load_library($library, $params, $object_name);
        } else {
            Modules::load_file($_library, $path);

            $library = ucfirst($_library);
            CI::$APP->$_alias = new $library($params);

            $this->_ci_classes[$class] = $_alias;
        }
        return $this;
    }

    /** Load an array of libraries **/
    public function libraries($libraries)
    {
        foreach ($libraries as $library => $alias) {
            (is_int($library)) ? $this->library($alias) : $this->library($library, null, $alias);
        }
        return $this;
    }

    /** Load a module model **/
    public function model($model, $object_name = null, $connect = false)
    {
        if (is_array($model)) {
            return $this->models($model);
        }

        ($_alias = $object_name) or $_alias = basename($model);

        if (in_array($_alias, $this->_ci_models, true)) {
            return $this;
        }

        /* check module */

        list($path, $_model) = Modules::find(strtolower($model), $this->_module, 'models/');
        
        if ($path == false) {
            if (strpos($model, '/')) {

                //Check if exists module in folder, else is missing
                try {
                    parent::model($model, $object_name, $connect);
                } catch (RuntimeException $e) {
                    log_message('error', "Missing model '{$model}'");

                    $this->$_model = false;
                    CI::$APP->$_model = false;
                    $this->_ci_models[] = $_model;
                    echo $this->load->view("box/errors/missing_model", ['model' => $model], true);
                }
            } else {
                $uc_model = ucfirst($model);
                //201910170932 - Check if library exists eventually in a folder called "custom"
                if (file_exists(APPPATH . "models/custom/{$uc_model}.php")) {
                    $model = "custom/$model";
                }
                //debug($model);
                /* check application & packages */
                parent::model($model, $object_name, $connect);
            }
        } else {
            class_exists('CI_Model', false) or load_class('Model', 'core');

            if ($connect !== false && !class_exists('CI_DB', false)) {
                if ($connect === true) {
                    $connect = '';
                }

                $this->database($connect, false, true);
            }

            Modules::load_file($_model, $path);

            $model = ucfirst($_model);
            CI::$APP->$_alias = new $model();

            $this->_ci_models[] = $_alias;
        }
        return $this;
    }

    protected function _ci_load_library($class, $params = null, $object_name = null)
    {
        // Get the class name, and while we're at it trim any slashes.
        // The directory path can be included as part of the class name,
        // but we don't want a leading slash
        $class = str_replace('.php', '', trim($class, '/'));

        // Was the path included with the class name?
        // We look for a slash to determine this
        if (($last_slash = strrpos($class, '/')) !== false) {
            // Extract the path
            $subdir = substr($class, 0, ++$last_slash);

            // Get the filename from the path
            $class = substr($class, $last_slash);
        } else {
            $subdir = '';
        }

        $class = ucfirst($class);

        // Is this a stock library? There are a few special conditions if so ...
        if (file_exists(BASEPATH . 'libraries/' . $subdir . $class . '.php')) {
            return $this->_ci_load_stock_library($class, $subdir, $params, $object_name);
        } else {
            //debug(BASEPATH . 'libraries/' . $subdir . $class . '.php');
        }

        // Safety: Was the class already loaded by a previous call?
        if (class_exists($class, false)) {
            $property = $object_name;
            if (empty($property)) {
                $property = strtolower($class);
                isset($this->_ci_varmap[$property]) && $property = $this->_ci_varmap[$property];
            }

            $CI = &get_instance();
            if (isset($CI->$property)) {
                //log_message('debug', $class . ' class already loaded. Second attempt ignored.');
                return;
            }

            return $this->_ci_init_library($class, '', $params, $object_name);
        }

        // Let's search for the requested library file and load it.
        foreach ($this->_ci_library_paths as $path) {
            // BASEPATH has already been checked for
            if ($path === BASEPATH) {
                continue;
            }

            $filepath = $path . 'libraries/' . $subdir . $class . '.php';
            // Does the file exist? No? Bummer...
            if (!file_exists($filepath)) {
                continue;
            }

            include_once $filepath;
            return $this->_ci_init_library($class, '', $params, $object_name);
        }

        // One last attempt. Maybe the library is in a subdirectory, but it wasn't specified?
        if ($subdir === '') {
            return $this->_ci_load_library($class . '/' . $class, $params, $object_name);
        }

        // If we got this far we were unable to find the requested class.
        log_message('error', 'Unable to load the requested class: ' . $class);
        show_error('Unable to load the requested class: ' . $class);
    }

    /** Load an array of models **/
    public function models($models)
    {

        foreach ($models as $model => $alias) {
            (is_int($model)) ? $this->model($alias) : $this->model($model, $alias);
        }
        return $this;
    }

    /** Load a module controller **/
    public function module($module, $params = null)
    {
        if (is_array($module)) {
            return $this->modules($module);
        }

        $_alias = strtolower(basename($module));
        CI::$APP->$_alias = Modules::load(array($module => $params));
        return $this;
    }

    /** Load an array of controllers **/
    public function modules($modules)
    {
        foreach ($modules as $_module) {
            $this->module($_module);
        }

        return $this;
    }

    /** Load a module plugin **/
    public function plugin($plugin)
    {
        if (is_array($plugin)) {
            return $this->plugins($plugin);
        }

        if (isset($this->_ci_plugins[$plugin])) {
            return $this;
        }

        list($path, $_plugin) = Modules::find($plugin . '_pi', $this->_module, 'plugins/');

        if ($path === false && !is_file($_plugin = APPPATH . 'plugins/' . $_plugin . EXT)) {
            show_error("Unable to locate the plugin file: {$_plugin}");
        }

        Modules::load_file($_plugin, $path);
        $this->_ci_plugins[$plugin] = true;
        return $this;
    }

    /** Load an array of plugins **/
    public function plugins($plugins)
    {
        foreach ($plugins as $_plugin) {
            $this->plugin($_plugin);
        }

        return $this;
    }

    /** Load a module view **/
    // public function view($view, $vars = array(), $return = FALSE)
    // {
    //     list($path, $_view) = Modules::find($view, $this->_module, 'views/');

    //     if ($path != FALSE) {
    //         $this->_ci_view_paths = array($path => TRUE) + $this->_ci_view_paths;
    //         $view = $_view;
    //     }

    //     // Fixed by stackoverflow  https://stackoverflow.com/questions/41557760/codeigniter-hmvc-object-to-array-error
    //     // Original
    //     //return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    //     // Fixed
    //     if (method_exists($this, '_ci_object_to_array')) {
    //         return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
    //     } else {
    //         return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_prepare_view_vars($vars), '_ci_return' => $return));
    //     }
    // }

    function &_ci_get_component($component) {
        return CI::$APP->$component;
    }

    public function __get($class)
    {
        return (isset($this->controller)) ? $this->controller->$class : CI::$APP->$class;
    }

    public function _ci_load($_ci_data)
    {
        extract($_ci_data);

        if (isset($_ci_view)) {
            $_ci_path = '';

            /* add file extension if not provided */
            $_ci_file = (pathinfo($_ci_view, PATHINFO_EXTENSION)) ? $_ci_view : $_ci_view . EXT;

            foreach ($this->_ci_view_paths as $path => $cascade) {
                if (file_exists($view = $path . $_ci_file)) {
                    $_ci_path = $view;
                    break;
                }
                if (!$cascade) {
                    break;
                }

            }
        } elseif (isset($_ci_path)) {

            $_ci_file = basename($_ci_path);
            if (!file_exists($_ci_path)) {
                $_ci_path = '';
            }

        }

        if (empty($_ci_path)) {

            //show_error('Unable to load the requested file: ' . $_ci_file);
            log_message('error', 'Unable to load the requested file: ' . $_ci_file);
            //$error =            $this->load->view("box/errors/missing_layout", ['layout' => $_ci_file], true);
            $error = "Missing layout!";
        }

        if (isset($_ci_vars)) {
            $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, (array) $_ci_vars);
        }

        extract($this->_ci_cached_vars);

        ob_start();

        if (empty($error)) {

            if ((bool) @ini_get('short_open_tag') === false && CI::$APP->config->item('rewrite_short_tags') == true) {
                echo eval('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
            } else {
                include $_ci_path;
            }

            log_message('info', 'File loaded: ' . $_ci_path);
        } else {
            echo $error;
        }

        if ($_ci_return == true) {
            return ob_get_clean();
        }

        if (ob_get_level() > $this->_ci_ob_level + 1) {
            ob_end_flush();
        } else {
            CI::$APP->output->append_output(ob_get_clean());
        }
    }

    /** Autoload module items **/
    public function _autoloader($autoload)
    {

        $path = false;

        if ($this->_module) {
            list($path, $file) = Modules::find('constants', $this->_module, 'config/');

            /* module constants file */
            if ($path != false) {
                include_once $path . $file . EXT;
            }

            list($path, $file) = Modules::find('autoload', $this->_module, 'config/');

            /* module autoload file */
            if ($path != false) {
                $autoload = array_merge(Modules::load_file($file, $path, 'autoload'), $autoload);
            }
        }

        /* nothing to do */
        if (count($autoload) == 0) {
            return;
        }

        /* autoload package paths */
        if (isset($autoload['packages'])) {
            foreach ($autoload['packages'] as $package_path) {
                $this->add_package_path($package_path);
            }
        }

        /* autoload config */
        if (isset($autoload['config'])) {
            foreach ($autoload['config'] as $config) {
                $this->config($config);
            }
        }

        /* autoload helpers, plugins, languages */
        foreach (array('helper', 'plugin', 'language') as $type) {
            if (isset($autoload[$type])) {
                foreach ($autoload[$type] as $item) {
                    $this->$type($item);
                }
            }
        }

        // Autoload drivers
        if (isset($autoload['drivers'])) {
            foreach ($autoload['drivers'] as $item => $alias) {
                (is_int($item)) ? $this->driver($alias) : $this->driver($item, $alias);
            }
        }

        /* autoload database & libraries */
        if (isset($autoload['libraries'])) {
            if (in_array('database', $autoload['libraries'])) {
                /* autoload database */
                if (!$db = CI::$APP->config->item('database')) {
                    $this->database();
                    $autoload['libraries'] = array_diff($autoload['libraries'], array('database'));
                }
            }

            /* autoload libraries */
            foreach ($autoload['libraries'] as $library => $alias) {
                (is_int($library)) ? $this->library($alias) : $this->library($library, null, $alias);
            }
        }

        /* autoload models */
        if (isset($autoload['model'])) {
            foreach ($autoload['model'] as $model => $alias) {
                (is_int($model)) ? $this->model($alias) : $this->model($model, $alias);
            }
        }

        /* autoload module controllers */
        if (isset($autoload['modules'])) {
            foreach ($autoload['modules'] as $controller) {
                ($controller != $this->_module) && $this->module($controller);
            }
        }
    }
}

/** load the CI class for Modular Separation **/
(class_exists('CI', false)) or require dirname(__FILE__) . '/Ci.php';
