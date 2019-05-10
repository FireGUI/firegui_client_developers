<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/* load the MX_Loader class */
require APPPATH."third_party/MX/Loader.php";

class MY_Loader extends MX_Loader {
    function module_view($folder, $view, $vars = array(), $return = FALSE) {
        //debug($vars);
        $this->_ci_view_paths = array_merge($this->_ci_view_paths, array(APPPATH .'modules/'. $folder . '/' => TRUE));
        $vars = (is_object($vars)) ? get_object_vars($vars) : $vars;
        //debug($vars);
        return $this->_ci_load(array(
                    '_ci_view' => $view,
                    '_ci_vars' => $vars,
                    '_ci_return' => $return
                ));
      }
}