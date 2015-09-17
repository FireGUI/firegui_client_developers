<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <?php $_default_value = $value; ?>
    <select class="form-control select2me <?php echo $class ?>" name="<?php echo $field['fields_name']; ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" data-val="<?php echo $_default_value; ?>" >
        <?php if( ! $field['fields_source']): ?>
            <?php if($field['fields_required']=='f'): ?>
                <option></option>
            <?php endif; ?>
            <?php if(!empty($field['support_data'])): ?>
                <?php foreach ($field['support_data'] as $value): ?>
                    <option value="<?php echo $value[$field['fields_ref']."_id"]; ?>" <?php if($field['fields_draw_onclick']) { echo 'onclick="'.$field['fields_draw_onclick'].'"'; } ?> <?php if($_default_value == $value[$field['fields_ref']."_id"]) { echo 'selected'; } ?> >
                        <?php if(empty($field['support_fields'])): ?>
                            <?php echo "{$field['fields_ref']} {$value[$field['fields_ref']."_id"]}"; ?>
                        <?php else: ?>
                            <?php foreach ($field['support_fields'] as $support_field): ?>
                                <?php echo $value[$support_field['fields_name']]; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>
    </select>
    <?php echo $help; ?>
</div>