<?php

function grid_has_action($grid)
{

    if (isset($grid['links'])) {
        $links = array_filter($grid['links'], function ($link) {
            return is_array($link) ? !empty($link) : (bool) trim($link);
        });

        return (!empty($links) && $grid['grids_actions_column'] == DB_BOOL_TRUE) || ($grid['grids_inline_edit'] == DB_BOOL_TRUE && $grid['grids_inline_form']);
    } elseif ($grid['grids_inline_edit'] == DB_BOOL_TRUE && $grid['grids_inline_form']) {

        return true;
    } else {
        return FALSE;
    }
}
