<?php $tinymce_id = "js_tinymce_{$field['fields_id']}" . ($lang ? "_{$lang}" : ''); ?>
<?php echo $label; ?>
<textarea <?php echo "id='{$tinymce_id}'"; ?> name="<?php echo $field['fields_name']; ?>" class="form-control js_tinymce <?php echo $class ?>" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" ><?php echo str_replace('{base_url}', base_url(), $value); ?></textarea>
<?php echo $help; ?>