<?php


class Fi_activity extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
    }

    public function getDescriptionCreate($username)
    {
        return "User $username has created this record";
    }
    public function getDescriptionEditSmall($username)
    {
        return "User $username has edited this record";
    }

    public function getDescriptionEditMedium($username, $count_fields)
    {
        return "User $username has edited $count_fields fields on this record";
    }

    public function getDescriptionEditFull($username, $data)
    {
        $fields_li = '';
        $old = $data['old'];

        foreach ($data['diff'] as $field => $val) {
            if (is_array($val) || stripos($field, 'modified_date') || ($val == $old[$field])) {
                continue;
            }
            $oldval = $old[$field];

            $fields_li .= "<li>$field from '$oldval' to '$val'</li>";
        }

        return "User $username has edited this record.<ul>$fields_li</ul>";
    }
}
