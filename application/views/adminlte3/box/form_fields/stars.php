<?php echo $label; ?>
<?php if (in_array($field['fields_type'], array(DB_INTEGER_IDENTIFIER, 'INT', 'FLOAT'))) : ?>
    <div class="star-container">
        <input class="<?php echo $class ?>" type="hidden" value="<?php echo $value; ?>" name="<?php echo $field['fields_name']; ?>" />

        <?php for ($i = 1; $i <= 5; $i++) : ?>
            <span class="star cursor-pointer" data-val="<?php echo $i; ?>" onclick="changeStarsStatus(this)">
                <i class="<?php echo (($i > $value) ? 'far fa-star' : 'fas fa-star'); ?>"></i>
            </span>
        <?php endfor; ?>

        <a href="javascript:void(0);" onclick="$('input', $(this).parent()).val('');
                        $('.star i', $(this).parent()).removeClass('fas fa-star').addClass('far fa-star');">
            &times;
        </a>
    </div>
<?php else : ?>
    <?php e('The star selector cannot be shown for the selected data type'); ?>
<?php endif; ?>
<?php echo $help; ?>