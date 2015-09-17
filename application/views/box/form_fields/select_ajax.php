<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <input type="hidden" name="<?php echo $field['fields_name']; ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" class="form-control js_select_ajax <?php echo $class ?>" value="<?php echo $value; ?>" data-val="<?php echo $value; ?>" />
    <?php echo $help; ?>
</div>