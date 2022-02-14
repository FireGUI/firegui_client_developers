<div class="material-switch">
    <span style="display:none"><?php echo ($value == DB_BOOL_TRUE) ? t('Yes') : t('No'); ?></span>

    <input class="js_switch_bool" data-field_name="<?php echo $field['fields_name']; ?>" data-row_id="<?php echo $row_id; ?>" value="<?php echo DB_BOOL_TRUE; ?>" id="switch_bool<?php echo $field['fields_name']; ?><?php echo $row_id; ?>" name="someSwitchOption001" type="checkbox" <?php if ($value == DB_BOOL_TRUE) : ?> checked="checked" <?php endif; ?> />
    <label for="switch_bool<?php echo $field['fields_name']; ?><?php echo $row_id; ?>" class="label-success"></label>
</div>