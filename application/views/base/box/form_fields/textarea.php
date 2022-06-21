<?php echo $label; ?>
<textarea name="<?php echo $field['fields_name']; ?>" class="form-control <?php echo $class ?>" placeholder="<?php echo $placeholder; ?>" rows="3" <?php echo $onclick; ?> data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>" ><?php echo $value; ?></textarea>
<?php echo $help; ?>
