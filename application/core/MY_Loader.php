<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH . "third_party/MX/Loader.php";

class MY_Loader extends MX_Loader
{
    public function _ci_load($_ci_data)
    {
        extract($_ci_data);







        // Define template from settings or set base template
        if (isset($this->controller) && isset($this->controller->settings['settings_template_folder'])) {
            $template = (!empty($this->controller->settings['settings_template_folder'])) ? $this->controller->settings['settings_template_folder'] : 'base';
        } else {
            $template = 'base';
        }

        // Check extension
        $_ci_ext = pathinfo($_ci_view, PATHINFO_EXTENSION);
        $view_file = ($_ci_ext === '') ? $_ci_view . '.php' : $_ci_view;






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

            // Check custom file

            if (file_exists(FCPATH . "application/views/custom/" . $view_file)) {
                $_ci_path = FCPATH . "application/views/custom/" . $view_file;
            } elseif (file_exists(FCPATH . "application/views/" . $template . "/" . $view_file)) {
                $_ci_path = FCPATH . "application/views/" . $template . "/" . $view_file;
            } else {
                $_ci_path = FCPATH . "application/views/base/" . $view_file;

                if ($template != 'base') {
                    log_message('info', 'Template file not found: ' . $view_file . ' Loaded base file');
                }
            }
            if (!file_exists($_ci_path)) {
                //show_error('Unable to load the requested file: ' . $_ci_file);
                log_message('error', 'Unable to load the requested file: ' . $_ci_file);
                //$error =			$this->load->view("box/errors/missing_layout", ['layout' => $_ci_file], true);
                if (is_maintenance()) {
                    $error = "Missing layout: {$_ci_path}!";
                } else {
                    $error = "Missing layout!";
                }
            }
        }

        if (isset($_ci_vars)) {
            $this->_ci_cached_vars = array_merge($this->_ci_cached_vars, (array) $_ci_vars);
        }

        extract($this->_ci_cached_vars);

        ob_start();

        if (empty($error)) {
            if ((bool) @ini_get('short_open_tag') === false && CI::$APP->config->item('rewrite_short_tags') == true) {
                echo eval ('?>' . preg_replace("/;*\s*\?>/", "; ?>", str_replace('<?=', '<?php echo ', file_get_contents($_ci_path))));
            } else {
                include ($_ci_path);
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

    public function capture_profiler_output()
    {

        // Cattura l'output del profiler
        $this->load->library('profiler');

        $profiler_output = $this->profiler->run();


        return $profiler_output;
    }

    /** HMVC Fix and template view manager **/
    public function view($view, $vars = array(), $return = false)
    {
        if ($this->input->is_ajax_request()) {
            $this->output->enable_profiler(false);
        }
        list($path, $_view) = Modules::find($view, $this->_module, 'views/');

        if ($path != false) {
            $this->_ci_view_paths = array($path => true) + $this->_ci_view_paths;
            $view = $_view;
        }


        // Fixed by stackoverflow  https://stackoverflow.com/questions/41557760/codeigniter-hmvc-object-to-array-error
        // Original
        //return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
        // Fixed

        if (method_exists($this, '_ci_object_to_array')) {
            return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_object_to_array($vars), '_ci_return' => $return));
        } else {
            return $this->_ci_load(array('_ci_view' => $view, '_ci_vars' => $this->_ci_prepare_view_vars($vars), '_ci_return' => $return));
        }
    }


    public function module_view($folder, $view, $vars = array(), $return = false)
    {
        $CI = get_instance();

        $CI->lang->language = array_merge($CI->lang->language, $CI->module->loadTranslations($folder, @array_values($CI->lang->is_loaded)[0]));

        $this->_ci_view_paths = array_merge($this->_ci_view_paths, array(APPPATH . 'modules/' . $folder . '/' => true));

        $vars = (is_object($vars)) ? get_object_vars($vars) : $vars;
        $_ci_ext = pathinfo($view, PATHINFO_EXTENSION);
        $_ci_file = ($_ci_ext === '') ? $view . '.php' : $view;

        //debug($folder);

        $file = APPPATH . 'modules/' . $folder . '/' . $_ci_file;

        if (!file_exists($file)) {
            if (is_maintenance()) {
                $content = '<div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                View ' . $file . ' doesn\'t exist
              </div>';
            } else {
                $content = '';
            }
            if ($return) {
                return $content;
            } else {
                echo $content;
            }
        } else {

            //debug($view);

            return $this->_ci_load(
                array(
                    '_ci_view' => $view,
                    '_ci_vars' => $vars,
                    '_ci_return' => $return
                )
            );
        }
    }

    public function database($params = '', $return = false, $query_builder = null)
    {
        $ci =& get_instance();

        if ($return === false && $query_builder === null && isset($ci->db) && is_object($ci->db) && !empty($ci->db->conn_id)) {
            return false;
        }

        require_once (BASEPATH . 'database/DB.php');

        $db =& DB($params, $query_builder);

        $driver = config_item('subclass_prefix') . 'DB_' . $db->dbdriver . '_driver';
        $file = APPPATH . 'libraries/' . $driver . '.php';

        if (file_exists($file) === true && is_file($file) === true) {
            require_once ($file);

            $dbo = new $driver(get_object_vars($db));
            $db = &$dbo;
        }

        if ($return === true) {
            return $db;
        }

        $ci->db = '';
        $ci->db = $db;

        return $this;
    }
}
