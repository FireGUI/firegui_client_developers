<?php $ckeditor_id =  "js_ckeditor_{$field['fields_id']}" . ($lang? "_{$lang}": ''); ?>
<div class="form-group" <?php echo $containerAttributes; ?>>
    <?php echo $label; ?>
    <textarea <?php echo "id='{$ckeditor_id}'"; ?> name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" ><?php echo str_replace('{base_url}', base_url_template(), $value); ?></textarea>
    <?php echo $help; ?>
</div>

<script type="text/javascript" charset="utf-8">
    $(document).ready(function() {
        CKEDITOR.replace('<?php echo $ckeditor_id; ?>', {
            filebrowserUploadUrl : '<?php echo base_url('db_ajax/ck_uploader') ?>'
        });
    });
</script>