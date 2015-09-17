<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <div class="row">
        <div class="col-xs-12" >
            <?php if(!empty($field['support_data'])): ?>
                <?php $selectedValue = $value; ?>
                <?php foreach ($field['support_data'] as $value): ?>
                    <label class="checkbox-inline">
                        <?php $thisVal = $value[$field['field_support_id']]; ?>
                        <input type="checkbox" class="<?php echo $class ?>" value="<?php echo $thisVal; ?>" <?php if((is_array($selectedValue) && in_array($thisVal, $selectedValue)) OR (!is_array($selectedValue) && $thisVal==$selectedValue)) echo 'checked'; ?> name="<?php echo $field['fields_name']; ?>[]" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
                        <?php foreach ($field['support_fields'] as $support_field): ?>
                            <?php echo $value[$support_field['fields_name']]; ?>
                        <?php endforeach; ?>
                    </label>
                <?php endforeach; ?>
            <?php else: ?>
                <label class="checkbox-inline">
                    <input type="checkbox" class="<?php echo $class ?>" value="1" <?php if($value=='t') echo 'checked'; ?> name="<?php echo $field['fields_name']; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
                    <?php echo $field['fields_draw_label']; ?>
                </label>
            <?php endif; ?>
            
            <?php echo $help; ?>
        </div>
    </div>
</div>