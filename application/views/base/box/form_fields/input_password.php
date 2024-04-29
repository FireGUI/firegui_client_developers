<?php echo $label; ?>
<div class="input-group">
    <input type="password" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" autocomplete="new-password"  />
    <span class="input-group-addon js_show_user_password">
        <i class="fas fa-eye password-icon"></i>
    </span>
</div>
<?php echo $help ? : e('<span class="help-block">Don\'t compile if you don\'t want to change the password</span>'); ?>