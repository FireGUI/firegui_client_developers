<?php echo $label; ?>
<div class="input-group">
    <input type="password" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $onclick; ?> />
    <span class="input-group-addon">
        <i class="fas fa-lock"></i>
    </span>
</div>
<?php echo $help ? : e('<span class="help-block">Don\'t compile if you don\'t want to change the password</span>'); ?>
