<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH . "third_party/MX/Loader.php";

class MY_Loader extends MX_Loader
{


    /** HMVC Fix and template view manager **/
	public function view($view, $vars = array(), $return = FALSE)
	{
		list($path, $_view) = Modules::find($view, $this->_module, 'views/');

		if ($path != FALSE) {
			$this->_ci_view_paths = array($path => TRUE) + $this->_ci_view_paths;
			$view = $_view;
		}

        // Define template from settings or set base template
        if (isset($this->controller) && isset($this->controller->settings['settings_template_folder'])) {
            $template = (!empty($this->controller->settings['settings_template_folder'])) ? $this->controller->settings['settings_template_folder'] : 'base';
        } else {
            $template = 'base';
        }
        
        // Check extension
        $_ci_ext = pathinfo($view, PATHINFO_EXTENSION);
        $view_file = ($_ci_ext === '') ? $view.'.php' : $view;

        // Check custom file
        if (file_exists(FCPATH . "application/views/custom/".$view_file)) {
            $view = 'custom/'.$view;
        } else if (file_exists(FCPATH . "application/views/".$template."/".$view_file)) {
            $view = $template.'/'.$view;
        } else {
            $view = 'base/'.$view;
            if ($template != ' base') {
                log_message('error', 'Template file not found: '. $view_file.' Loaded base file');
            }
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


    function module_view($folder, $view, $vars = array(), $return = FALSE)
    {
        $CI = get_instance();

        $CI->lang->language = array_merge($CI->lang->language, $CI->module->loadTranslations($folder, @array_values($CI->lang->is_loaded)[0]));

        $this->_ci_view_paths = array_merge($this->_ci_view_paths, array(APPPATH . 'modules/' . $folder . '/' => TRUE));
        $vars = (is_object($vars)) ? get_object_vars($vars) : $vars;
        $_ci_ext = pathinfo($view, PATHINFO_EXTENSION);
        $_ci_file = ($_ci_ext === '') ? $view . '.php' : $view;
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
            return $this->_ci_load(array(
                '_ci_view' => $view,
                '_ci_vars' => $vars,
                '_ci_return' => $return
            ));
        }
    }
}
