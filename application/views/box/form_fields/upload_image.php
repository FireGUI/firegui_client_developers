<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if ($field['fields_required'] == 't'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>

    <div class="fileupload fileupload-new field_<?php echo $field['fields_id']; ?>" data-provides="fileupload" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"' ?>>
        <input type="hidden">
        <div class="fileupload-preview fileupload-exists thumbnail"></div>
        <?php if($value): ?>
            <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
            <div class="fileupload-new thumbnail">
                <img src="<?php echo base_url_template("uploads/{$value}"); ?>" alt="" />
            </div>
        <?php else: ?>
            <div class="fileupload-new thumbnail">
                <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" alt="">
            </div>
        <?php endif; ?>
        <div>
            <span class="btn default btn-file btn-sm">
                <span class="fileupload-new"><i class="icon-paper-clip"></i> Select image</span>
                <span class="fileupload-exists"><i class="icon-undo"></i> Change</span>
                <input type="file" class="default" name="<?php echo $field['fields_name']; ?>" />
            </span>
            <a href="#" class="btn btn-sm red fileupload-exists" data-dismiss="fileupload"><i class="icon-trash"></i> Remove</a>
            <?php if($value): ?>
                <a href="#" class="btn btn-sm red" onclick="var c = $(this).parent().parent();$('input[type=hidden][name=<?php echo $field['fields_name']; ?>][value]', c).remove();$('img', c).attr('src', 'http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image')"><i class="icon-remove"></i></a>
            <?php endif; ?>
        </div>
    </div>

    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>