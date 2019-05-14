$(document).ready(function () {
    $('.js-bulk-action > option[value=""]').attr('selected', 'selected').trigger('change');
    $("input:checkbox.js_bulk_check,.js-bulk-select-all").removeAttr('checked').trigger('change');
    $.uniform.update();
    $('.js-bulk-select-all').on('click', function () {
        var grid_container = $(this).closest('table.table');
        
        if ($(this).is(':checked')) {
            $('input[type="checkbox"].js_bulk_check', grid_container).attr('checked', 'checked').trigger('change');
        } else {
            $('input[type="checkbox"].js_bulk_check', grid_container).removeAttr('checked').trigger('change');
        }
        //Questo ridisegna le checkbox metronic
        $.uniform.update();
        
        var chkbx_ids = $("input:checkbox.js_bulk_check:checked", grid_container).map(function(){
            return $(this).val();
        }).get();
        
        if (chkbx_ids.length == 0) {
            $('.js-bulk-first-option', grid_container).html('');
            $('.js-bulk-action option', grid_container).attr('disabled', 'disabled');
        } else {
            $('.js-bulk-first-option', grid_container).html(chkbx_ids.length+' selected');
            $('.js-bulk-action option', grid_container).removeAttr('disabled');
        }
        
    });
    
    $("table").on('click',"input:checkbox.js_bulk_check",  function () {
        //Questo ridisegna le checkbox metronic
        $.uniform.update();
        var grid_container = $(this).closest('table.table');
        var chkbx_ids = $("input:checkbox.js_bulk_check:checked", grid_container).map(function(){
            return $(this).val();
        }).get();
        //alert(chkbx_ids.length);
        if (chkbx_ids.length == 0) {
            $('.js-bulk-first-option',grid_container).html('');
            $('.js-bulk-action option', grid_container).attr('disabled', 'disabled');
        } else {
            $('.js-bulk-first-option',grid_container).html(chkbx_ids.length+' selected');
            $('.js-bulk-action option', grid_container).removeAttr('disabled');
        }
    });
    
    $('.js-bulk-action').on('change', function () {
        var grid_container = $(this).closest('div[data-layout-box]');
        if ($(this).val() != '') {
            var chkbx_ids = $("input:checkbox.js_bulk_check:checked", grid_container).map(function(){
                return $(this).val();
              }).get();
            
            if ($(this).val() == 'bulk_edit') {
                
                var form_id = $(this).find(":selected").data('form_id');
                loadModal(base_url+'get_ajax/modal_form/'+form_id, {"ids":chkbx_ids}, null, 'POST');
            } else if ($(this).val() == 'bulk_delete') {
                var r = confirm("Confermi di voler eliminare "+chkbx_ids.length+" righe?");
                if (r == true) {
                    var url = base_url+'db_ajax/generic_delete/'+$(this).data('entity-name');
                    var data = {"ids":chkbx_ids};
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: data,
                        success: function(json) {
                            location.reload();
                        }
                    });
                }
            }
        }
    });
});