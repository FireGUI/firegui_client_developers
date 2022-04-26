$(document).ready(function () {
    'use strict';
    $('body').on('mouseenter', 'table tr', function () {
        var inline_actions_container = $('.js_action_inline', $(this));

        if (inline_actions_container.length > 0) {
            inline_actions_container.css('visibility', 'visible');
        }
    });

    $('body').on('mouseleave', 'table tr', function () {
        $('.js_action_inline', $(this)).css('visibility', 'hidden');
    });
    $('body').on('change', '.js_switch_bool', function () {
        var field_name = $(this).data('field_name');
        var row_id = $(this).data('row_id');
        loading(true);
        $.ajax({
            url: base_url + 'db_ajax/switch_bool/' + field_name + '/' + row_id,
            dataType: 'json',
            complete: function () {
                loading(false);
            },
            success: function (msg) {
                handleSuccess(msg);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                var errorContainerID = 'ajax-error-container';
                var errorContainer = $('#' + errorContainerID);

                if (errorContainer.size() === 0) {
                    errorContainer = $('<div/>').attr('id', errorContainerID).css({
                        'z-index': 99999999,
                        'background-color': '#fff',
                    });
                    $('body').prepend(errorContainer);
                }

                errorContainer.html('Ajax error:' + xhr.responseText);
            },
        });
    });
});
