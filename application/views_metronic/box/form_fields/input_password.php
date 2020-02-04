<?php echo $label; ?>
<div class="input-group">
    <input type="password" name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $onclick; ?> />
    <span class="input-group-addon">
        <i class="fas fa-lock"></i>
    </span>
</div>
<?php echo $help ? : '<span class="help-block">Non compilare se non vuoi modificare la password</span>'; ?>
