<?php $ckeditor_id =  "js_ckeditor_{$field['fields_id']}"; ?>
<div class="form-group" style="<?php if($field['fields_draw_display_none']==='t') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if ($field['fields_required'] == 't'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    <textarea id="<?php echo $ckeditor_id; ?>" name="<?php echo $field['fields_name']; ?>" class="form-control field_<?php echo $field['fields_id']; ?>" ><?php echo str_replace('{base_url}', base_url_template(), $value); ?></textarea>
    <?php if($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>





<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        CKEDITOR.replace('<?php echo $ckeditor_id; ?>', {
            filebrowserUploadUrl : '<?php echo base_url('db_ajax/ck_uploader') ?>'
        });
    });
</script>