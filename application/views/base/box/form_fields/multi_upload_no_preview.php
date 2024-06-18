<?php
$unique = $field['fields_id'];
$form_id = $field['forms_fields_forms_id'];
if (is_string($value)) {
    $value = htmlspecialchars_decode($value);
}
?>

<?php echo $label; ?>
<br />
<div class="col-md-12 dropzone_div <?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
    <input type="hidden" class="default" data-name="<?php echo $field['fields_name']; ?>" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>"  />
    <?php if (is_array($value)) : ?>

        <?php foreach ($value as $file_id => $file) : ?>
            <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>[]" value="<?php echo $file_id; ?>" />
        <?php endforeach; ?>
    <?php elseif (!empty($value)) : ?>
        <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
    <?php endif; ?>

    <div class="row js_dropzone dropzone upload-drop-zone" data-preview="0" data-fieldid="<?php echo $field['forms_fields_fields_id']; ?>" data-formid="<?php echo $form_id; ?>" data-unique="<?php echo $unique; ?>" data-fieldname="<?php echo $field['fields_name']; ?>" data-maxuploadsize="<?php echo (int) ((defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 10000) / 1000); ?>" data-fieldtype="<?php echo $field['fields_type']; ?>" data-value="<?php echo base64_encode(json_encode($value)); ?>" data-url="<?php echo base_url("db_ajax/multi_upload_async/{$field['fields_id']}"); ?>">

    </div>
    <p class="help-block dz-helptext" style="display: none"><i class="fas fa-info fa-fw"></i><?php e('Files has been uploaded successfully. Now you can save'); ?></p>
    <p class="help-block dz-helptext-uploading" style="display: none"><i class="fas fa-info fa-fw"></i><?php e('You cannot save because one or more files are being uploaded.<br/>Please wait for the upload to complete or press ‘Cancel upload’ to cancel it.'); ?></p>
</div>
<?php echo $help; ?>