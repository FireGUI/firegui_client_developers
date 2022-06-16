<?php echo $label; ?>
<br />
<div class="<?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
    <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" />
    <span class="btn btn-default btn-sm btn-file ">
        <span class="fileinput-new"><?php e('Select file'); ?></span>
        <span class="fileinput-exists"><?php e('Change'); ?></span>
        <input type="file" name="<?php echo $field['fields_name']; ?>">
    </span>

    <span class="fileinput-filename "><?php echo $value ?></span>
    &nbsp;
    <a href="javascript:;" class="fileinput-exists" data-dismiss="fileinput"><i class="fas fa-times"></i> <?php e('Remove'); ?></a>
</div>
<?php echo $help; ?>