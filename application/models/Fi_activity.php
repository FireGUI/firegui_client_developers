<?php


class Fi_activity extends CI_Model
{
    const LOG_FIELD_TYPES = [
        'INT',
        'VARCHAR',
        'INT4',
        'INTEGER',
        'FLOAT',
        'DOUBLE',
        'BOOL',
        'BOOLEAN',

    ];
    const LOG_NUMBER_FORMAT_FIELD_TYPES = [

        'FLOAT',
        'DOUBLE',


    ];
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

    public function getDescriptionEditFull($username, $data, $extra)
    {
        $fields_li = '';
        $old = $data['old'];

        $visible_fields = array_key_map_data($extra['entity_full_data']['visible_fields'], 'fields_name');
        $fields_modified_without_old_and_new_value = [];
        foreach ($data['diff'] as $field => $val) {
            if (
                is_array($val)
                || stripos($field, 'modified_date')
                || (isset($old[$field]) && $val == $old[$field])
                || !isset($visible_fields[$field])
            ) {
                continue;
            }

            $oldval = $old[$field];
            $field_data = $visible_fields[$field];
            $field_type = strtoupper($field_data['fields_type']);

            if (!in_array($field_type, self::LOG_FIELD_TYPES)) {
                $fields_modified_without_old_and_new_value[] = $field;
                //continue;
            } else {
                //Procedo a loggare
                //Check for fields_ref table
                if ($field_data['fields_ref']) {
                    $idField = $field_data['fields_ref'] . '_id';
                    if ($val) {
                        // dump($val);
                        $_val = $this->crmentity->getEntityPreview($field_data['fields_ref'], sprintf('%s = %d', $idField, $val));
                        if (array_key_exists($val, $_val)) {
                            $val = $_val[$val];
                        } else {
                            continue;
                        }
                    }
                    if ($oldval) {
                        $oldval = $this->crmentity->getEntityPreview($field_data['fields_ref'], sprintf('%s = %d', $idField, $oldval))[$oldval];
                    }
                }

                if (in_array($field_type, self::LOG_NUMBER_FORMAT_FIELD_TYPES)) {
                    $val = number_format($val, 2);
                    $oldval = number_format($oldval, 2);
                }





                if ($oldval != $val) {
                    $fields_li .= "<li>$field from '$oldval' to '$val'</li>";
                }
            }
        }
        //debug($fields_modified_without_old_and_new_value, true);
        if (!empty($fields_modified_without_old_and_new_value)) {
            $fields_li .= "<li>" . implode(', ', $fields_modified_without_old_and_new_value) . "</li>";
        }
        if (!empty($fields_li)) {
            $details = " Fields changed: <ul>$fields_li</ul>";
        } else {
            $details = '';
        }



        return "User $username has edited this record.{$details}";
    }
}
