<?php
// Convert the value to array
if (!is_array($value)) {
   $value = in_array($field['fields_type'], array('VARCHAR', 'TEXT'))? explode(',', $value): array();
}

$preselected_values = $value;
?>
<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <select multiple class="form-control js_multiselect field_<?php echo $field['fields_id']; ?>" <?php if(!$field['fields_source'] && count($field['support_data']) > 200) echo 'data-minimum-input-length="3"'; ?> name="<?php echo $field['fields_name']; ?>[]" data-source-field="<?php echo $field['fields_source']; ?>" data-ref="<?php echo $field['fields_ref']; ?>">
        <?php if( ! $field['fields_source']): ?>
            <?php foreach ($field['support_data'] as $value): ?>
                <option value="<?php echo $value[$field['field_support_id']]; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> <?php if(in_array($value[$field['field_support_id']], $preselected_values)) echo 'selected'; ?>>
                    <?php 
                    $preview = '';
                    if( ! empty($field['support_fields'])) {
                        foreach ($field['support_fields'] as $support_field) {
                            $preview.= $value[$support_field['fields_name']].' ';
                        }
                    }
                    $label = trim($preview);
                    echo $label? $label: "ID #{$value[$field['field_support_id']]}";
                    ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>