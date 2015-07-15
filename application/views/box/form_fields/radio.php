<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="col-xs-12">
        <div class="radio-list" style="margin-left: 6px;">
            <?php if(!empty($field['support_data'])): ?>
                <?php $selectedValue = $value; ?>
                <?php foreach ($field['support_data'] as $value): ?>
                    <label class="radio">
                        <input class="field_<?php echo $field['fields_id']; ?>" type="radio" value="<?php echo $value[$field['fields_ref']."_id"]; ?>" <?php if($value[$field['fields_ref']."_id"] == $selectedValue) echo 'checked'; ?> name="<?php echo $field['fields_name']; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
                        <?php foreach ($field['support_fields'] as $support_field): ?>
                            <?php echo $value[$support_field['fields_name']]; ?>
                        <?php endforeach; ?>
                    </label>
                <?php endforeach; ?>
            <?php else: ?>
                    <?php
                        $positive_val = $field['fields_type']=='BOOL'? 't': '1';
                        $negative_val = $field['fields_type']=='BOOL'? 'f': '0';
                    ?>

                    <label class="radio-inline">
                        <input class="field_<?php echo $field['fields_id']; ?>" type="radio" value="<?php echo $positive_val ?>" <?php if($value == $positive_val) echo 'checked'; ?> name="<?php echo $field['fields_name']; ?>"  <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
                        Si
                    </label>
                    <label class="radio-inline">
                        <input class="field_<?php echo $field['fields_id']; ?>" type="radio" value="<?php echo $negative_val ?>" <?php if($value == $negative_val) echo 'checked'; ?> name="<?php echo $field['fields_name']; ?>"  <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?> />
                        No
                    </label>
            <?php endif; ?>
        </div>
    </div>
    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>