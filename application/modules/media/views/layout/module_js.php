<!-- BEGIN Module Related Javascript -->
<script src="<?php echo base_url_template('template/crm-v2/assets/global/plugins/dropzone/dropzone.js'); ?>"></script>

<script>
    $(document).ready(function() {
        
        var jqSelEntity = $('#js_upload_form [name="entity_id"]');
        var jqSelField  = $('#js_upload_form [name="fields_id"]');
        var jqSelValue  = $('#js_upload_form [name="value"]');
        
        
        jqSelEntity.on('change', function() {
            var sEntityName = $('option', jqSelEntity).filter(':selected').attr('data-name');
            jqSelValue.attr('data-ref', sEntityName);
            
            $('option', jqSelField).remove();
            $.ajax(base_url+'get_ajax/entity_fields', {
                type: 'POST',
                data: { entity_id: jqSelEntity.val() },
                dataType: 'json',
                success: function(json) {
                    $.each(json, function(k, value) {
                        var jqOpt = $('<option></option>').val(value.fields_id).html(value.fields_draw_label);
                        jqSelField.append(jqOpt);
                    });
                    jqSelField.prepend($('<option></option>'));
                }
            });
        });
    });
</script>

<!-- END Module Related Javascript -->