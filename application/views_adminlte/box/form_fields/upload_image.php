<?php echo $label; ?>
<br />
<div class="<?php echo $class ?> fileinput <?php echo $value ? 'fileinput-exists' : 'fileinput-new' ?>" data-provides="fileinput">

    <input type="hidden" class="default" name="<?php echo $field['fields_name']; ?>" value="<?php echo $value; ?>" />

    <div class="fileinput-new thumbnail" style="width: 156px; height: 100px;">
        <img src="<?php echo base_url(); ?>images/upload_icon.png" alt="" style="max-height:80px;" />
    </div>
    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px;">
        <img src="<?php echo $value ? base_url_uploads("uploads/{$value}") : ''; ?>" alt="" style="max-height: 140px;">
    </div>
    <div>
        <span class="btn btn-default btn-sm btn-file">
            <span class="fileinput-new"><?php e('Select image'); ?></span>
            <span class="fileinput-exists"><?php e('Change'); ?></span>
            <input type="file" name="<?php echo $field['fields_name']; ?>">
        </span>
        <a href="javascript:;" class="fileinput-exists " data-dismiss="fileinput"><i class="fas fa-times"></i> <?php e('Remove'); ?></a>
    </div>
</div>
<?php echo $help; ?>