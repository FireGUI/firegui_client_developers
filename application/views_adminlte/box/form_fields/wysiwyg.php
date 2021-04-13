<?php $ckeditor_id = "js_ckeditor_{$field['fields_id']}" . ($lang ? "_{$lang}" : ''); ?>
<?php echo $label; ?>
<textarea <?php echo "id='{$ckeditor_id}'"; ?> name="<?php echo $field['fields_name']; ?>" class="form-control js_ckeditor <?php echo $class ?>"><?php echo str_replace('{base_url}', base_url(), $value); ?></textarea>
<?php echo $help; ?>