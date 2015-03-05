<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if($field['fields_required']=='t'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <div class="fileupload fileupload-new" data-provides="fileupload">
        <div class="input-group field_<?php echo $field['fields_id']; ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"'; ?>>
            <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
            <span class="input-group-btn">
                <span class="uneditable-input">
                    <i class="icon-file fileupload-exists"></i>
                    <span class="fileupload-preview"><?php echo $value ?></span>
                </span>
            </span>
            <span class="btn default btn-file">
                <span class="fileupload-new"><i class="icon-paper-clip"></i> Select file</span>
                <span class="fileupload-exists"><i class="icon-undo"></i> Change</span>
                <input type="file" class="default" name="<?php echo $field['fields_name']; ?>" />
            </span>
            <a href="#" class="btn red fileupload-exists" data-dismiss="fileupload">
                <i class="icon-trash"></i> Remove
            </a>
        </div>
        <?php if($field['fields_draw_help_text']): ?>
            <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
        <?php endif; ?>
    </div>
</div>