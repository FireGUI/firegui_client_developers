<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');


/*
*
* TODO: Move this controller to the new Builder module!
*
*/
class Builder extends MY_Controller
{

    function __construct()
    {
        parent::__construct();


        // Super admin protection
        if (!$this->auth->is_admin()) {
            die("Oh no! Only super-admin can use this module.");
        }
    }


    // Drag and Drop layout boxes
    public function update_layout_box_position($layout_id, $last_box_moved)
    {
        $rows = $this->input->post();
        if (!$rows) {
            return;
        }

        foreach ($rows as $row_id => $row_array) {
            if (is_array($row_array)) {
                // Security reset
                //$this->db->where('layouts_boxes_layout', $layout_id)->where('layouts_boxes_row', $row_id)->update("layouts_boxes", array("layouts_boxes_position" => 0));
                foreach ($row_array as $position => $layout_box_id) {

                    if ($layout_box_id == $last_box_moved) {
                        $this->db->where("layouts_boxes_id = '$layout_box_id'")->update("layouts_boxes", array("layouts_boxes_layout" => $layout_id, "layouts_boxes_position" => $position, "layouts_boxes_row" => $row_id));
                    } else {
                        $this->db->where("layouts_boxes_id = '$layout_box_id'")->update("layouts_boxes", array("layouts_boxes_position" => $position, "layouts_boxes_row" => $row_id));
                    }
                }
            }
        }
    }
    // Resize layout boxes
    public function update_layout_box_cols($layouts_boxes_id, $cols)
    {
        $this->db->where('layouts_boxes_id', $layouts_boxes_id)->update("layouts_boxes", array("layouts_boxes_cols" => $cols));
    }

    // Delete layout boxes
    public function delete_layout_box($layout_box_id)
    {
        $this->db->where("layouts_boxes_id = '$layout_box_id'")->delete("layouts_boxes");
    }
    // Delete layout boxes
    public function move_layout_box($layout_box_id, $new_layout_id)
    {
        $this->db->where('layouts_boxes_id', $layout_box_id)->update("layouts_boxes", array("layouts_boxes_layout" => $new_layout_id));
    }

    // Update layout title
    public function update_layout_box_title($layout_box_id)
    {
        $title = $this->input->get('title');
        if ($title) {
            $this->db->where('layouts_boxes_id', $layout_box_id)->update("layouts_boxes", array("layouts_boxes_title" => $title));
        }
    }
}
