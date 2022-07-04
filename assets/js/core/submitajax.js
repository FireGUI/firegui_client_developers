'use strict';
var formAjaxSubmittedFormId = null;
var formAjaxShownMessage = null;

var handleSuccess = function (msg, container = null) {
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
        case 7:
            refreshAjaxLayoutBoxes();
            if (container) {
                closeContainingPopups(container);
            }
            success(msg.txt);
            break;
        case 9:
            // Callback
            eval(msg.txt);
            break;
    }
};

function loading(bShow) {
    if (bShow) {
        $('.js_loading').fadeIn();
        // Se in 4 secondi non ho ottenuto una risposta, nascondo automaticamente il loader
        setTimeout(loading, 4000, false);
    } else {
        $('.js_loading').fadeOut();
    }
}

$(document).ready(function () {
    'use strict';
    $('body').on('submit', '.formAjax', function (e) {
        e.preventDefault();
        var form = $(this);
        if (formAjaxShownMessage) {
            formAjaxShownMessage.fadeOut(200, function () {
                formAjaxShownMessage.removeClass('alert-success alert-danger').addClass('hide').css({ display: '', opacity: '' });
            });
            setTimeout(function () {
                formAjaxShownMessage = null;
                formAjaxSend(form);
            }, 200);
        } else {
            formAjaxSend(form);
        }
        return false;
    });

    $('body').on('click', '.js_confirm_button', function (e) {
        var text = $(this).data('confirm-text');
        if (!confirm(text ? text : 'Confermi?')) {
            e.preventDefault(); // Prevent follow links
            e.stopPropagation(); // Prevent propagation on parent DOM elements
            e.stopImmediatePropagation(); // Prevent other handlers to be fired
            return false; // Better sure than sorry :)
        }
    });

    $('body').on('click', '.js_link_ajax', function (e) {
        e.preventDefault(); // Prevent follow links
        e.stopPropagation(); // Prevent propagation on parent DOM elements
        e.stopImmediatePropagation(); // Prevent other handlers to be fired

        var url = $(this).attr('href');
        var container = $(this);
        loading(true);
        $.ajax({
            url: url,
            dataType: 'json',
            timeout: 0,
            complete: function () {
                loading(false);
            },
            success: function (msg) {
                handleSuccess(msg, container);
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (typeof xhr.responseText !== 'undefined') {
                    var errorContainerID = 'ajax-error-container';
                    var errorContainer = $('#' + errorContainerID);

                    if (errorContainer.size() === 0) {
                        errorContainer = $('<div/>').attr('id', errorContainerID).css({
                            'z-index': 99999999,
                            'background-color': '#fff',
                        });
                        $('body').prepend(errorContainer);
                    }

                    errorContainer.html('Errore ajax:' + xhr.responseText);
                }

            },
        });
    });
});

function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if (new Date().getTime() - start > milliseconds) {
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
        timeout: 0,

        dataType: 'json',
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
            if (typeof msg.cache_tags !== 'undefined' && msg.cache_tags) {
console.log(msg.cache_tags);
//alert('lock!');
            } else if (typeof msg.refresh_grids !== 'undefined' && msg.refresh_grids && msg.related_entity) {
                refreshGridsByEntity(msg.related_entity);
                //refreshAjaxLayoutBoxes();
            }
            if (typeof msg.reset_form !== 'undefined' && msg.reset_form) {
                form[0].reset();
                try {
                    initComponents(form, true);
                } catch (err) {
                    console.log(err.message);
                }
            }

        },
        error: function (xhr, ajaxOptions, thrownError) {

            var msg = { status: 0, txt: 'Oh no! Something wrong. Please try again' };
            handleSuccess(msg);
            if (formEvents && formEvents.hasOwnProperty('form-ajax-error')) {
                // Custom call
                form.trigger('form-ajax-error', [xhr, ajaxOptions, thrownError]);
            } else {
                // Default
                var errorBox = $('#submitajax-error');
                if (!errorBox.length) {
                    errorBox = $('<div id="submitajax-error">').prependTo($('body'));
                    errorBox.css({ 'background-color': '#fff', 'z-index': 999999, padding: 15 });
                }
                errorBox.html('Errore ajax:<br/>' + xhr.responseText);
                errorBox.append(
                    $('<button>')
                        .text('Chiudi')
                        .on('click', function () {
                            errorBox.remove();
                        })
                );
            }
        },
    };

    if (ajaxOverrideOptions && typeof ajaxOverrideOptions === 'object') {
        $.extend(ajaxOptions, ajaxOverrideOptions);
    }

    loading(true);
    $.ajax(ajaxOptions);
}

function error(txt, idform) {
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
        formAjaxShownMessage = null;
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

function refreshGridsByEntity(entity_name) {
    $('.js_fg_grid_' + entity_name).DataTable().ajax.reload();

}

function refreshVisibleAjaxGrids(table) {
    if (typeof table === 'undefined') {
        $('.js_ajax_datatable:visible').DataTable().ajax.reload();
    } else {
        table.DataTable().ajax.reload();
    }

}

function refreshLayoutBox(lb_id, value_id) {
    var data = [];
    var selector = '.layout_box[data-layout-box="' + lb_id + '"]';
    var lb = $(selector);
    try {
        var token = JSON.parse(atob(this.grid.data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
    } catch (e) {
        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
    }


    var data_post = [];
    data_post.push({ name: token_name, value: token_hash });
    var get_params = lb.data('get_pars');
    if (get_params) {
        get_params = '?' + get_params;
    }
    $.ajax(base_url + 'get_ajax/get_layout_box_content/' + lb_id + '/' + value_id + get_params, {
        data: data_post,
        type: 'POST',
        async: true,
        success: function (html) {
            lb.html(html);
            initComponents(lb, true);
        },
    });
}

function refreshAjaxLayoutBoxes() {
    //refreshVisibleAjaxGrids();
    //TODO: check if box is refreshable

    var is_modal = $('.modal:visible').length;
    if ($('.layout_box:visible').length > 5 && !is_modal) {
        location.reload();
    } else {
        if (is_modal) {
            var container = $('.modal:visible');
        } else {
            var container = $('body');
        }
        $('.layout_box:visible', container).each(function () {

            if ($('.js_ajax_datatable:visible', $(this)).length > 0) {
                refreshVisibleAjaxGrids($('.js_ajax_datatable:visible', $(this)));
            } else {
                var lb = $(this);
                var lb_id = $(this).data('layout-box');
                var value_id = $(this).data('value_id');

                refreshLayoutBox(lb_id, value_id);
            }

        });
    }


}