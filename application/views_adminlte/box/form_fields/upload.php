<?php echo $label; ?>
<br/>
<div class="<?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">
    <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />
    <span class="btn btn-default btn-file ">
       
        <span class="fileinput-new"><?php e('Seleziona file'); ?></span>
        <input type="file" name="<?php echo $field['fields_name']; ?>">
    </span>

    <span class="fileinput-filename "><?php echo $value ?></span>
    &nbsp;
    <a href="javascript:;" class="close fileinput-exists" data-dismiss="fileinput"></a>
</div>
<?php echo $help; ?>
