<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <div class="fileupload fileupload-new" data-provides="fileupload">
        <div class="input-group <?php echo $class ?>" <?php if($field['fields_draw_onclick']) echo 'onclick="'.$field['fields_draw_onclick'].'"'; ?>>
            <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
            <span class="input-group-btn">
                <span class="uneditable-input">
                    <i class="icon-file fileupload-exists"></i>
                    <span class="fileupload-preview"><?php echo $value ?></span>
                </span>
            </span>
            <span class="btn default btn-file">
                <span class="fileupload-new"><i class="icon-paper-clip"></i></span>
                <span class="fileupload-exists"><i class="icon-undo"></i></span>
                <input type="file" class="default" name="<?php echo $field['fields_name']; ?>" />
            </span>
            <a href="#" class="btn red fileupload-exists" data-dismiss="fileupload">
                <i class="icon-trash"></i>
            </a>
        </div>
        <?php echo $help; ?>
    </div>
</div>