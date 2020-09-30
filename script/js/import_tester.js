$(document).ready(function () {
    'use strict';
    $('.js_test_import').on('click', function () {
        var form = $(this).parents('form').filter(':first');
        var resultContainer = $('#js_import_test_result', form);
        resultContainer.addClass('hide');
        $.ajax({
            url: form.attr('action') + '/1',
            type: 'POST',
            data: new FormData(form[0]),
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (msg) {
                var status = parseInt(msg.status);
                resultContainer.html(msg.txt);
                if (status === 1) {
                    resultContainer.addClass('alert-success').removeClass('alert-danger');
                } else {
                    resultContainer.addClass('alert-danger').removeClass('alert-success');
                }

                if (msg.txt) {
                    resultContainer.removeClass('hide');
                }
            },
        });
    });
});
