var nome_form = null;
var formMessage = null;


var handleSuccess = function(msg) {

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
            window.location.reload();
            break;
            
        case 5:
            // Success
            success(msg.txt);
            break;
    }
    
    // Eventually close all modals if needed
    if(typeof msg.close_modals !== 'undefined' && msg.close_modals) {
        $('.modal').each(function() {
            try {
                $(this).data('bs.modal').askConfirmationOnClose = false;
            } catch (e) {}
            
            $(this).modal('hide');
        });
    }

};


//var iShowCounter = 0;
function loading(bShow) {
    if(bShow) {
        $('.js_loading').fadeIn();
        // Se in 2 secondi non ho ottenuto una risposta, nascondo automaticamente il loader
        setTimeout(loading, 2000, false);
    } else {
        $('.js_loading').fadeOut();
    }
}









$(document).ready(function() {
    $('body').on('submit', '.formAjax', function(e) {
        e.preventDefault();
        var action = $(this).attr('action');
        nome_form = $(this).attr('id');
        
        var form = $(this);
        if (formMessage === null) {
            invio_form(action, form);
        } else {
            formMessage.fadeOut('fast', function() {
                formMessage.removeClass('alert-success alert-danger').addClass('hide').css({'display':'', 'opacity': ''});
                formMessage = null;
                invio_form(action, form);
            });
        }
        return false;
    });
        
        
    $('body').on('click', '.js_link_ajax', function(e) {
        e.preventDefault();

        if($(this).hasClass('js_confirm_button')) {
            // Ask confirm first
            var text = $(this).attr('data-confirm-text');
            if( ! confirm(text? text: 'Confermi?')) {
                return;
            }
        }


        var url = $(this).attr('href');
        loading(true);
        $.ajax({
            url: url,
            dataType: 'json',
            complete: function() {
                loading(false);
            },
            success: function(msg) {
                handleSuccess(msg);
            },
            error: function(xhr, ajaxOptions, thrownError) {

                var errorContainerID = 'ajax-error-container';
                var errorContainer = $('#' + errorContainerID);

                if(errorContainer.size() === 0) {
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



function invio_form(action, form) {
    try {
        $('.formAjax #description').val(CKEDITOR.instances.description.getData());
    } catch (e) {}
    var data = new FormData(form[0]);
    loading(true);
    $.ajax({
        url: action,
        type: 'POST',
        data: data,
        async: false,
        cache: false,
        contentType: false,
        processData: false,

        dataType: "json",
        complete: function() {
            loading(false);
        },
        success: function(msg) {
            handleSuccess(msg);
        },
        error: function(xhr, ajaxOptions, thrownError) {
            $('body').prepend("Errore ajax:" + xhr.responseText);
        },
    });
}







function error(txt) {

    var console = $('#msg_' + nome_form);
    console.html(txt);
    formMessage = console;

    if (txt) {
        console.removeClass('hide alert-success').addClass('alert-danger');
    } else {
        console.addClass('hide');
    }

}


function success(txt) {

    var console = $('#msg_' + nome_form);
    console.html(txt);
    formMessage = console;

    if (txt) {
        console.removeClass('hide alert-danger').addClass('alert-success');
        setTimeout(function() {
            console.fadeOut(function() {
                console.addClass('hide').html('').css('display', '');
                if (formMessage === console) {
                    formMessage = null;
                }
            });
        }, 8000);
    } else {
        console.addClass('hide');
    }

}