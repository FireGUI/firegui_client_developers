<?php

function grid_has_action($grid)
{
    if (isset($grid['links'])) {
        $links = array_filter($grid['links'], function ($link) {
            return is_array($link) ? !empty($link) : (bool) trim($link);
        });

        return !empty($links);
    } else {
        return FALSE;
    }
}
