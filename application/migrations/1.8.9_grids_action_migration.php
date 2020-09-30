<?php

//Get all grids with action delete
$grids = $this->db->where('grids_delete_link <>', '#')->or_where('grids_delete_link IS NULL')->get('grids')->result_array();

//Foreach grid migrate to new grids_actions management
foreach ($grids as $grid) {

    $grid_id = $grid['grids_id'];

    //Create action delete
    $action_data = [
        'grids_actions_grids_id' => $grid_id,
        'grids_actions_order' => 999,
        'grids_actions_mode' => 'default',
        'grids_actions_type' => 'delete',
        'grids_actions_html' => '',
        'grids_actions_link' => '',
        'grids_actions_layout' =>  null,
        'grids_actions_form' =>  null,
        'grids_actions_icon' => 'fas fa-trash',
        'grids_actions_color' => '#dd4b39',
        'grids_actions_name' => 'Delete',

    ];

    $this->db->insert('grids_actions', $action_data);

    //remove old action delete
    $this->db->where('grids_id', $grid_id)->update('grids', ['grids_delete_link' => '#']);
}
