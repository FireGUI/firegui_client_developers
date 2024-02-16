'use strict';
var formAjaxSubmittedFormId = null;
var formAjaxShownMessage = null;
var formAjaxIsSubmitting = false;

var handleSuccess = function (msg, container = null) {
    // console.log(msg);
    // alert(1);
    var submittedForm = $('#' + formAjaxSubmittedFormId);
    var submitBtn = $('button[type="submit"]', submittedForm);

    switch (parseInt(msg.status)) {
        case 0:
            //Show message
            error(msg.txt);
            submitBtn.show();
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
            submitBtn.show();
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
            submitBtn.show();
            break;
        case 6:
            // Success

            success(msg.txt, false);
            submitBtn.show();
            break;
        case 7:

            refreshAjaxLayoutBoxes();

            if (container) {

                closeContainingPopups(container);
            }

            success(msg.txt);
            submitBtn.show();
            break;
        case 9:
            // Callback
            submitBtn.show();
            eval(msg.txt);
            break;
        case 10:
            //Alert e redirect
            alert(msg.txt);
            if (msg.hasOwnProperty('timeout')) {
                setTimeout(function () {
                    window.location = msg.url;
                }, msg.timeout);
            } else {
                window.location = msg.url;
            }
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

        if (formAjaxIsSubmitting) {
            return false;
        }

        var submitBtn = $('button[type="submit"]', form);

        submitBtn.hide();

        formAjaxIsSubmitting = true;

        // return;
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
        formAjaxIsSubmitting = false;
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
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 0,

        dataType: 'json',
        complete: function () {
            form.trigger('form-ajax-complete');
            loading(false);
            formAjaxIsSubmitting = false;
        },
        success: function (msg) {

            if (formEvents && formEvents.hasOwnProperty('form-ajax-success')) {
                // Custom call
                form.trigger('form-ajax-success', msg);
            } else {
                // Default
                handleSuccess(msg);
            }
            //alert(1);
            // Eventually close all modals if needed
            if (typeof msg.close_modals !== 'undefined' && msg.close_modals) {
                closeContainingPopups(form);
            }
            if (typeof msg.cache_tags !== 'undefined' && msg.cache_tags) {

                for (var i in msg.cache_tags) {
                    var entity = msg.cache_tags[i];

                    refreshLayoutBoxesByEntity(entity);
                }
            }
            if (typeof msg.refresh_grids !== 'undefined' && msg.refresh_grids && msg.related_entity) {
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
    formAjaxShownMessage.css({ display: '', opacity: '' });

    if (txt) {
        formAjaxShownMessage.removeClass('hide hidden alert-success').addClass('alert-danger');
    } else {
        formAjaxShownMessage.addClass('hide hidden').hide();
    }
}

function success(txt, autohide = true) {
    formAjaxShownMessage = $('#msg_' + formAjaxSubmittedFormId).html(txt);

    // Reset della proprietà css inline display in modo che non interferisca con
    // le classi di bootstrap
    formAjaxShownMessage.css({ display: '', opacity: '' });

    var current = formAjaxShownMessage;

    if (txt) {
        current.removeClass('hide hidden alert-danger').addClass('alert-success');
        if (autohide) {
            setTimeout(function () {
                current.fadeOut(function () {
                    current.addClass('hide hidden').html('');

                    if (current === formAjaxShownMessage) {
                        formAjaxShownMessage = null;
                    }
                });
            }, 8000);
        }
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
    var dt = $('.js_ajax_datatable', $('.js_fg_grid_' + entity_name)).DataTable();
    if (dt.length > 0 && dt.ajax !== null) {
        dt.ajax.reload();
    }


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
    // if ($('.layout_box:visible').length > 5 && !is_modal) {
    //     location.reload();
    // } else {
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
    // }


}
var refreshed_layouts = [];
function refreshLayoutBoxesByEntity(entity_name) {

    var link_href = window.location.href;
    var get_params = link_href.split('?');
    if (get_params[1]) {
        get_params = '?' + get_params[1];
    } else {
        get_params = '';
    }
    $('.js_page_content').each(function () {

        var related_entities_string = $(this).data('related_entities');
        // console.log(related_entities_string);
        // alert('1');
        if (typeof related_entities_string != 'undefined') {
            var related_entities = related_entities_string.split(',');
            console.log(related_entities);
            console.log(entity_name);
            if (related_entities.includes(entity_name)) {
                loading(true);
                var layout_id = $(this).data('layout-id');

                if (refreshed_layouts.includes(layout_id)) {
                    //alert(2);
                    console.log('Already refreshed layout ' + layout_id);
                } else {
                    //alert(3);
                    var value_id = $(this).data('value_id');
                    refreshed_layouts.push(layout_id);
                    $.ajax(base_url + 'main/get_layout_content/' + layout_id + '/' + value_id + get_params, {
                        type: 'GET',
                        dataType: 'json',
                        complete: function () {
                            loading(false);
                        },
                        success: function (data) {

                            if (data.status == 0) {
                                console.log(data.msg);
                            }
                            if (data.status == 1) {

                                $('.js_page_content[data-layout-id="' + layout_id + '"]').remove();

                                $('.js_page_content').hide();
                                document.title = data.dati.title_prefix;
                                console.log('TODO: clone js_page_content instead of creating div...');

                                var clonedContainerHtml = '<div class="js_page_content" data-layout-id="' + layout_id + '" data-title="' + data.dati.title_prefix + '" data-related_entities="' + data.dati.related_entities.join(',') + '"></div>';
                                // $();
                                //clonedContainer.html(data.content);
                                $('#js_layout_content_wrapper').append(clonedContainerHtml);
                                var clonedContainer = $('.js_page_content[data-layout-id="' + layout_id + '"]');
                                clonedContainer.html(data.content);
                                window.history.pushState("", "", link_href);
                                initComponents(clonedContainer, true);



                            }
                            refreshed_layouts = [];
                        },
                    });
                }



            }
        }

    });
}
