<?php echo $label; ?>
<?php if (in_array($field['fields_type'], array(DB_INTEGER_IDENTIFIER, 'INT', 'FLOAT'))): ?>
    <div class="star-container">
        <input class="<?php echo $class ?>" type="hidden" value="<?php echo $value; ?>" name="<?php echo $field['fields_name']; ?>" />

        <?php for ($i = 1; $i <= 5; $i++): ?>
            <span class="star" data-val="<?php echo $i; ?>" style="cursor:pointer;" onclick="changeStarsStatus(this)">
                <i class="<?php echo (($i > $value) ? 'fa fa-star-o' : 'fa fa-star'); ?>"></i>
            </span>
        <?php endfor; ?>

        <a href="javascript:void(0);" onclick="$('input', $(this).parent()).val('');
                        $('.star i', $(this).parent()).removeClass('fa fa-star').addClass('fa fa-star-o');">
            &times;
        </a>
    </div>
<?php else: ?>
    Impossibile mostrare il selettore stelline per il tipo di dato selezionato
<?php endif; ?>
<?php echo $help; ?>
