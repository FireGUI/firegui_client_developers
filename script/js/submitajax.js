'use strict';
var formAjaxSubmittedFormId = null;
var formAjaxShownMessage = null;


var handleSuccess = function (msg) {

    switch (parseInt(msg.status)) {
        case 0:
            //Show message
            error(msg.txt);
            break;

        case 1:
            //Redirect
            window.location = msg.txt;
            break;

        case 2:
            //Refresh
            window.location.reload();
            break;

        case 3:
            //Alert
            alert(msg.txt);
            break;

        case 4:
            //Alert e refresh
            alert(msg.txt);
            if (msg.hasOwnProperty('timeout')) {
                setTimeout(function () {
                    window.location.reload();
                }, msg.timeout);
            } else {
                window.location.reload();
            }
            break;

        case 5:
            // Success

            success(msg.txt);
            break;
        case 6:
            // Success

            success(msg.txt);
            break;
        case 9:
            // Callback
            eval(msg.txt);
            break;
    }
};


//var iShowCounter = 0;
function loading(bShow) {
    if (bShow) {
        //        $('.js_loading_overlay').fadeIn();
        $('.js_loading').fadeIn();
        // Se in 4 secondi non ho ottenuto una risposta, nascondo automaticamente il loader
        setTimeout(loading, 4000, false);
    } else {
        //        $('.js_loading_overlay').fadeOut();
        $('.js_loading').fadeOut();
    }
}









$(document).ready(function () {
    $('body').on('submit', '.formAjax', function (e) {
        e.preventDefault();
        var form = $(this);
        if (formAjaxShownMessage) {
            formAjaxShownMessage.fadeOut('fast', function () {
                formAjaxShownMessage.removeClass('alert-success alert-danger').addClass('hide').css({ 'display': '', 'opacity': '' });
                formAjaxShownMessage = null;
                formAjaxSend(form);
            });
        } else {
            formAjaxSend(form);
        }
        return false;
    });



    $('body').on('click', '.js_confirm_button', function (e) {
        var text = $(this).data('confirm-text');
        if (!confirm(text ? text : 'Confermi?')) {
            e.preventDefault();             // Prevent follow links
            e.stopPropagation();            // Prevent propagation on parent DOM elements
            e.stopImmediatePropagation();   // Prevent other handlers to be fired
            return false;                   // Better sure than sorry :)
        }
    });

    $('body').on('click', '.js_link_ajax', function (e) {
        e.preventDefault();             // Prevent follow links
        e.stopPropagation();            // Prevent propagation on parent DOM elements
        e.stopImmediatePropagation();   // Prevent other handlers to be fired

        /*if($(this).hasClass('js_confirm_button')) {
            // Ask confirm first
            var text = $(this).attr('data-confirm-text');
            if( ! confirm(text? text: 'Confermi?')) {
                return;
            }
        }*/

        var url = $(this).attr('href');
        loading(true);
        $.ajax({
            url: url,
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
                        "z-index": 99999999,
                        "background-color": '#fff'
                    });
                    $('body').prepend(errorContainer);
                }

                errorContainer.html("Errore ajax:" + xhr.responseText);
            }
        });
    });
});


function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}



function formAjaxSend(form, ajaxOverrideOptions) {

    refreshCkeditors();
    if (form instanceof Element || typeof form === 'string') {
        form = $(form);
    }

    if (!(form instanceof jQuery)) {
        alert("Si è verificato un errore tecnico inviando il form. Contattare l'amministrazione");
        return false;
    }

    formAjaxSubmittedFormId = form.attr('id');

    var action = form.attr('action');
    var formEvents = $._data(form[0], 'events');
    var data = new FormData(form[0]);

    var ajaxOptions = {
        url: action,
        type: 'POST',
        data: data,
        async: false,
        cache: false,
        contentType: false,
        processData: false,

        dataType: "json",
        complete: function () {
            form.trigger('form-ajax-complete');
            loading(false);
        },
        success: function (msg) {
            if (formEvents && formEvents.hasOwnProperty('form-ajax-success')) {
                // Custom call
                form.trigger('form-ajax-success', msg);

            } else {
                // Default
                handleSuccess(msg);
            }

            // Eventually close all modals if needed
            if (typeof msg.close_modals !== 'undefined' && msg.close_modals) {
                closeContainingPopups(form);
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            msg = { 'status': 0, 'txt': 'Oh no! Something wrong. Please try again' };
            handleSuccess(msg);
            if (formEvents && formEvents.hasOwnProperty('form-ajax-error')) {
                // Custom call
                form.trigger('form-ajax-error', [xhr, ajaxOptions, thrownError]);
            } else {
                // Default
                var errorBox = $('#submitajax-error');
                if (!errorBox.length) {
                    errorBox = $('<div id="submitajax-error">').prependTo($('body'));
                    errorBox.css({ 'background-color': '#fff', 'z-index': 999999, 'padding': 15 });
                }
                errorBox.html("Errore ajax:<br/>" + xhr.responseText);
                errorBox.append($('<button>').text('Chiudi').on('click', function () {
                    errorBox.remove();
                }));
            }
        }
    };

    if (ajaxOverrideOptions && typeof ajaxOverrideOptions === 'object') {
        $.extend(ajaxOptions, ajaxOverrideOptions);
    }

    loading(true);
    $.ajax(ajaxOptions);
}







function error(txt, idform) {
    console.log(idform);
    if (typeof idform != 'undefined') {

        formAjaxShownMessage = $('#msg_' + idform).html(txt);
    } else {

        formAjaxShownMessage = $('#msg_' + formAjaxSubmittedFormId).html(txt);
    }


    // Reset della proprietà css inline display in modo che non interferisca con
    // le classi di bootstrap
    formAjaxShownMessage.css('display', '');

    if (txt) {
        formAjaxShownMessage.removeClass('hide hidden alert-success').addClass('alert-danger');
    } else {
        formAjaxShownMessage.addClass('hide hidden').hide();
    }
}


function success(txt) {
    formAjaxShownMessage = $('#msg_' + formAjaxSubmittedFormId).html(txt);

    // Reset della proprietà css inline display in modo che non interferisca con
    // le classi di bootstrap
    formAjaxShownMessage.css('display', '');

    var current = formAjaxShownMessage;

    if (txt) {
        current.removeClass('hide hidden alert-danger').addClass('alert-success');
        setTimeout(function () {
            current.fadeOut(function () {
                current.addClass('hide hidden').html('');

                if (current === formAjaxShownMessage) {
                    formAjaxShownMessage = null;
                }
            });
        }, 8000);
    } else {
        current.addClass('hide hidden');
    }
}

function refreshCkeditors() {
    if (typeof CKEDITOR == 'undefined') {
        return;
    }

    var id;
    for (id in CKEDITOR.instances) {
        CKEDITOR.instances[id].updateElement();
    }
}


function closeContainingPopups(el) {
    // Try to close bootstrap modals
    var bsModalParent = el.parents('.modal');
    if (bsModalParent.size()) {
        try {
            bsModalParent.data('bs.modal').askConfirmationOnClose = false;
        } catch (e) { }
        bsModalParent.modal('hide');
    }

    // Try to close fancybox
    var fancyboxParent = el.parents('.fancybox-opened');
    if (fancyboxParent.size()) {
        $.fancybox.close();
    }
}

