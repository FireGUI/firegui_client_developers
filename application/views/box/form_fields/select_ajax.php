<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">

    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    
    
    <input type="hidden" name="<?php echo $field['fields_name']; ?>" data-source-field="<?php echo $field['fields_source'] ?>" data-ref="<?php echo $field['fields_ref'] ?>" class="form-control js_select_ajax field_<?php echo $field['fields_id']; ?>" value="<?php echo $value; ?>" data-val="<?php echo $value; ?>" />
    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>