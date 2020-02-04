<?php echo $label; ?>

<?php if ($subform): ?>
    <button <?php echo sprintf('id="%s"', ($randId = uniqid('form-opener'))); ?> type="button" class="btn-xs btn-link js-form-opener">Crea nuovo</button>
    <script>
        $(function () {
            var params = <?php echo json_encode(compact('randId', 'field', 'subform')); ?>;
            // ==============
            var button = $('#'+params.randId);
            var thisField = button.parent().find('[name="' + params.field.fields_name +'"]');
            var subform = params.subform;
            
            button.on('click', function () {
                openCreationForm(subform, thisField.data('ref'), function (id, name) {
                    thisField.val(id).trigger("change");
                });
            });
        });
    </script>
<?php endif; ?>
    
<input type="hidden"
       name="<?php echo $field['fields_name']; ?>"
       class="form-control js_select_ajax <?php echo $class ?>" 
       value="<?php echo $value; ?>" 
       data-source-field="<?php echo $field['fields_source'] ?>"
       data-ref="<?php echo $field['fields_ref'] ?>" 
       data-val="<?php echo $value; ?>" />

<?php echo $help; ?>
