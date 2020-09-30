<?php
class Hook extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    private function module_installed($name)
    {
        return $this->db->get_where('modules', array(
            'modules_name' => $name,
            'modules_installed' => DB_BOOL_TRUE
        ))->num_rows() > 0;
    }

    public function message_dropdown()
    {
        if ($this->module_installed('messages')) {
            $this->load->view('../modules/messages/views/box/dropdown_list');
        }
    }
}
