<?php (defined('BASEPATH')) or exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH . "third_party/MX/Loader.php";

class MY_Loader extends MX_Loader
{
    function module_view($folder, $view, $vars = array(), $return = FALSE)
    {
        //debug($vars);
        $this->_ci_view_paths = array_merge($this->_ci_view_paths, array(APPPATH . 'modules/' . $folder . '/' => TRUE));
        $vars = (is_object($vars)) ? get_object_vars($vars) : $vars;
        $_ci_ext = pathinfo($view, PATHINFO_EXTENSION);
        $_ci_file = ($_ci_ext === '') ? $view . '.php' : $view;
        $file = APPPATH . 'modules/' . $folder . '/' . $_ci_file;

        if (!file_exists($file)) {
            $content = '<div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                View ' . $file . ' doesn\'t exist
              </div>';

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
