<div class="form-group" style="<?php if ($field['fields_draw_display_none'] === 't') echo 'display: none;' ?>">
    <label>
        <?php echo $field['fields_draw_label']; ?>
        <?php if ($field['fields_required'] == 't'): ?><span class="text-danger icon-asterisk"></span><?php endif; ?>
    </label>
    
    <?php if (in_array($field['fields_type'], array('INT', 'FLOAT'))): ?>
        <div class="star-container">
            <input class="field_<?php echo $field['fields_id']; ?>" type="hidden" value="<?php echo $value; ?>" name="<?php echo $field['fields_name']; ?>" />
            
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <span class="star" data-val="<?php echo $i; ?>" style="cursor:pointer;" onclick="changeStarsStatus(this)">
                    <i class="<?php echo (($i > $value)? 'icon-star-empty': 'icon-star'); ?>"></i>
                </span>
            <?php endfor; ?>
            
            <a href="javascript:void(0);" onclick="$('input', $(this).parent()).val('');$('.star i', $(this).parent()).removeClass('icon-star').addClass('icon-star-empty');">
                &times;
            </a>
        </div>
    <?php else: ?>
        <?php debug("Attenzione!! Le stelline devono essere un tipo numerico"); ?>
    <?php endif; ?>
    
    
    
    
    <?php if ($field['fields_draw_help_text']): ?>
        <span class="help-block"><?php echo $field['fields_draw_help_text']; ?></span>
    <?php endif; ?>
</div>
