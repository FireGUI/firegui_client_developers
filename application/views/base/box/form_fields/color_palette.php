<?php $color_palette_id = "js_color_palette_{$field['fields_id']}"; ?>
<?php echo $label; ?>
<div class="row">
    <div class="col-md-6">

        <select id="<?php echo $color_palette_id; ?>" class="<?php echo $class ?? null ?>" name="<?php echo $field['fields_name']; ?>" data-dependent_on="<?php echo $field['forms_fields_dependent_on']; ?>">
            <?php foreach (COLORS_PALETTE as $key => $val) : ?>
                <option value="<?php echo $val; ?>" data-color="<?php echo $val; ?>"><?php echo $key; ?></option>
            <?php endforeach; ?>
        </select>


        <?php echo $help; ?>
    </div>
</div>
<div class="clearfix"></div>


<script>
    $(function() {
        <?php if ($value) : ?>
            $('#<?php echo $color_palette_id; ?>').colorselector('setColor', '<?php echo $value; ?>');
        <?php else : ?>
            $('#<?php echo $color_palette_id; ?>').colorselector();
        <?php endif; ?>
    })
</script>