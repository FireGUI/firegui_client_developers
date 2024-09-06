function initBulkGrids(container) {
    if (typeof container === 'undefined') {
        container = $('body');
    }
    $('.js-bulk-action > option[value=""]', container).attr('selected', 'selected').trigger('change');
    $('input:checkbox.js_bulk_check,.js-bulk-select-all', container).removeAttr('checked').trigger('change');
    $.uniform.update();
    $('.content-wrapper,.page-content-wrapper,.modal-content', container).on('click', '.js-bulk-select-all', function () {

        var grid_container = $(this).closest('table.table').closest('.grid,.tab-pane');
        if ($(this).is(':checked')) {

            //Show check all link
            
           

            $('input[type="checkbox"].js_bulk_check', grid_container).prop('checked', true).trigger('change');

            var checkedCount = $('input[type="checkbox"].js_bulk_check:checked', grid_container).length;
            //$(this).after('<span class="js_checked_counter" style="white-space: nowrap; margin-left: 5px;">' + checkedCount + ' selected</span>');
            //$('.js_checked_counter').after('<br /><a href="#" class="js-bulk-select-really-all">Select all records (not only this page)</a>');


        } else {
            $('.js_checked_counter', grid_container).remove();
            $('input[type="checkbox"].js_bulk_check', grid_container).prop('checked', false).trigger('change');
        }
        //Questo ridisegna le checkbox metronic
        $.uniform.update();

        var chkbx_ids = $('input:checkbox.js_bulk_check:checked', grid_container)
            .map(function () {
                return $(this).val();
            })
            .get();

        if (chkbx_ids.length == 0) {
            $('.js-bulk-first-option', grid_container).html('');
            $('.js-bulk-action option', grid_container).attr('disabled', 'disabled');
        } else {
            $('.js-bulk-first-option', grid_container).html(chkbx_ids.length + ' selected');
            $('.js-bulk-action option', grid_container).removeAttr('disabled');
        }
    });

    $('.content-wrapper,.page-content-wrapper,.modal-content', container).on('click', 'input:checkbox.js_bulk_check', function () {
        //Questo ridisegna le checkbox metronic
        $.uniform.update();
        var grid_container = $(this).closest('table.table').closest('.grid,.tab-pane');
        var chkbx_ids = $('input:checkbox.js_bulk_check:checked', grid_container)
            .map(function () {
                return $(this).val();
            })
            .get();
        //alert(chkbx_ids.length);
        if (chkbx_ids.length == 0) {
            $('.js-bulk-first-option', grid_container).html('');
            $('.js-bulk-action option', grid_container).attr('disabled', 'disabled');
        } else {
            $('.js-bulk-first-option', grid_container).html(chkbx_ids.length + ' selected');
            $('.js-bulk-action option', grid_container).removeAttr('disabled');
        }
    });

    $('.content-wrapper,.page-content-wrapper,.modal-content', container).on('change', '.js-bulk-action', function () {
        var grid_container = $(this).closest('div[data-layout-box]');
        if ($(this).val() != '') {

            var chkbx_ids = $('input:checkbox.js_bulk_check:checked', grid_container)
                .map(function () {
                    return $(this).val();
                })
                .get();


            // ------------------ New bulk action ---------------
            if ($(this).val() == 'bulk_action') {
                var bulk_type = $(this).find(':selected').data('bulk_type');
                var form_id = $(this).find(':selected').data('form_id');
                var custom_code = $(this).find(':selected').data('custom_code');

                // Get selected records
                var data_post = [];

                for (var i in chkbx_ids) {
                    data_post.push({ name: 'ids[]', value: chkbx_ids[i] });
                }

                // Check type action and run the code
                switch (bulk_type) {
                    case 'custom':

                        var url = base_url + custom_code + $(this).data('entity-name');
                        data_post.push({ name: token_name, value: token_hash });
                        loading(true);
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: data_post,
                            dataType: 'json',
                            success: function (json) {
                                loading(false);
                                handleSuccess(json);
                            },
                            error: function (msg) {
                                loading(false);
                                alert('Oh no... Something wrong');
                                console.log(msg);
                            }
                        });

                        break;
                    case 'edit_form':
                        loadModal(base_url + 'get_ajax/modal_form/' + form_id, data_post, null, 'POST');
                        break;
                    case 'delete':
                        var r = confirm('Confermi di voler eliminare ' + chkbx_ids.length + ' righe?');
                        if (r == true) {
                            var url = base_url + 'db_ajax/generic_delete/' + $(this).data('entity-name');
                            data_post.push({ name: token_name, value: token_hash });
                            $.ajax({
                                url: url,
                                type: 'POST',
                                data: data_post,
                                success: function (json) {
                                    location.reload();
                                },
                            });
                        }
                        break;
                    default:
                        alert('No bulk action found');
                        break;
                }

            }

            // ------------------ OLD bulk action ---------------
            if ($(this).val() == 'bulk_edit') {
                var data_post = [];

                for (var i in chkbx_ids) {
                    data_post.push({ name: 'ids[]', value: chkbx_ids[i] });
                }
                var form_id = $(this).find(':selected').data('form_id');

                loadModal(base_url + 'get_ajax/modal_form/' + form_id, data_post, null, 'POST');


            } else if ($(this).val() == 'bulk_delete') {
                var r = confirm('Confermi di voler eliminare ' + chkbx_ids.length + ' righe?');
                if (r == true) {
                    var url = base_url + 'db_ajax/generic_delete/' + $(this).data('entity-name');
                    var data_post = [];
                    data_post.push({ name: token_name, value: token_hash });
                    for (var i in chkbx_ids) {
                        data_post.push({ name: 'ids[]', value: chkbx_ids[i] });
                    }
                    $.ajax({
                        url: url,
                        type: 'POST',
                        dataType: 'json',
                        data: data_post,
                        success: function (json) {
                            //location.reload();
                            handleSuccess(json);
                        },
                    });
                }
            }
        }
    });
}
$(document).ready(function () {
    'use strict';
    initBulkGrids();

});
