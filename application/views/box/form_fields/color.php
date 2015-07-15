<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="row">
        <div class="col-md-6">
            <div class="input-group color js_form_colorpicker field_<?php echo $field['fields_id']; ?>" data-color="<?php echo $value?:'#000000'; ?>" data-color-format="rgba">
                <input name="<?php echo $field['fields_name']; ?>" type="text" class="form-control" value="<?php echo $value?:'#000000'; ?>" readonly <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?>>
                <span class="input-group-btn">
                    <button class="btn default" type="button"><i></i>&nbsp;</button>
                </span>
            </div>
            <?php if($field['fields_draw_help_text']): ?>
                <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
            <?php endif; ?>
        </div>
    </div>
    <div class="clearfix"></div>
</div>