/* Variabile globale per tracciare tutte le mappe create */
L.maps = {};


function initComponents() {

    /*
     * Form dates
     */
    $('.js_form_datepicker').datepicker({todayBtn: 'linked', format: 'dd/mm/yyyy', todayHighlight: true, weekStart: 1, language: 'it'});
    $('.js_form_timepicker').timepicker({autoclose: true, modalBackdrop: true, showMeridian: false, format: 'hh:ii', minuteStep: 5});
    $('.js_form_datetimepicker').datetimepicker({todayBtn: 'linked', format: 'dd/mm/yyyy hh:ii', minuteStep: 5, autoclose: true, pickerPosition: 'bottom-left', forceParse: false, todayHighlight: true, weekStart: 1, language: 'it'});

    $('.js_form_daterangepicker').each(function () {
        var jqDateRange = $(this);
        var sDate = $('input', jqDateRange).val();

        var start = new Date(), end = new Date();
        if (sDate) {
            var aDates = sDate.split(' - ');
            if (aDates.length === 2) {
                start = aDates[0];
                end = aDates[1];
            }
        }

        jqDateRange.daterangepicker({
            format: 'DD/MM/YYYY',
            separator: ' to ',
            startDate: start,
            endDate: end,
            locale: {
                applyLabel: 'Applica',
                cancelLabel: 'Annulla',
                fromLabel: 'Da',
                toLabel: 'A',
                weekLabel: 'W',
                customRangeLabel: 'Range custom',
                daysOfWeek: ["Do", "Lu", "Ma", "Me", "Gi", "Ve", "Sa"],
                monthNames: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
                firstDay: 1
            }
        }, function (start, end) {
            $('input', this.element).val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
        });
    });
    $('.js_form_colorpicker').colorpicker({format: 'hex'});


    /*
     * Grid-filtering forms
     */
    var forms = $('.js_filter_form');
    forms.each(function () {
        var form = $(this);
        var btn = $('.js_filter_form_add_row', form).on('click', function () {
            var container = $('.js_filter_form_rows_container', form),
                    rows = $('.js_filter_form_row', container);
            var size = rows.size();
            var obj = rows.filter(':first').clone();
            $('input, select', obj).each(function () {
                var name = 'conditions[' + size + '][' + $(this).attr('data-name') + ']';
                $(this).attr('name', name).removeAttr('data-name');
            });
            obj.removeClass('hide').appendTo(container);
        });
    });

    /*
     * Select, Multiselect e AjaxSelect
     */
    try {
        $('.js_multiselect:not(.select2-offscreen):not(.select2-container)').each(function () {
            var minInput = $(this).attr('data-minimum-input-length');
            $(this).select2({allowClear: true, minimumInputLength: minInput ? minInput : 0});
        });
        $('.select2me').select2({allowClear: true});
    } catch (e) {
    }

    var fieldsSources = [];
    $('[data-source-field]:not([data-source-field=""])').each(function () {
        // Prendo il form dell'elemento
        var jsMultiselect = $(this);
        var jqForm = jsMultiselect.parents('form');
        var sSourceField = jsMultiselect.attr('data-source-field');
        var sFieldRef = jsMultiselect.attr('data-ref');

        // Prendo il campo da osservare
        var jqField = $('[name="' + sSourceField + '"]', jqForm);
        jqField.on('change', function () {
            var previousValue = jsMultiselect.attr('data-val');
            jsMultiselect.select2('val', '');
            $('option', jsMultiselect).remove();

            loading(true);
            $.ajax(base_url + 'get_ajax/filter_multiselect_data', {
                type: 'POST',
                data: {field_name_to: jsMultiselect.attr('name'), field_ref: sFieldRef, field_from_val: jqField.val()},
                dataType: 'json',
                complete: function () {
                    loading(false);
                },
                success: function (data) {
                    $('<option></option>').appendTo(jsMultiselect);
                    var previousValueFound = false;
                    $.each(data, function (k, v) {
                        var jqOption = $('<option></option>').val(k).text(v);

                        if (previousValue == k) {
                            previousValueFound = true;
                        }

                        jsMultiselect.append(jqOption);
                    });

                    if (previousValueFound) {
                        jsMultiselect.select2('val', previousValue);
                    }
                }
            });
        });

        if ($.inArray(jqField.selector, fieldsSources) === -1) {
            fieldsSources.push(jqField.selector);
        }
    });

    $.each(fieldsSources, function (k, selector) {
        var field = $(selector);
        if (field.val() !== '') {
            field.trigger('change');
        }
    });




    /*
     * Select ajax
     */
    $('.js_select_ajax').each(function () {
        $(this).select2({
            allowClear: true,
            placeholder: 'Cerca valori',
            minimumInputLength: 0,
            ajax: {
                url: base_url + 'get_ajax/select_ajax_search',
                dataType: 'json',
                type: 'POST',
                data: function (term, page) {
                    var input = $(this);
                    // C'è un attributo data-referer che identifica il campo che richiede i dati?
                    // Se non c'è prendi il name...
                    var referer = input.data('referer');
                    if (!referer) {
                        referer = input.attr('name');
                    }
                    
                    return {q: term, limit: 100, table: input.attr('data-ref'), referer: referer};
                },
                results: function (data, page) {
                    return {results: data};
                }
            },
            initSelection: function (element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax(base_url + 'get_ajax/select_ajax_search', {
                        type: 'POST',
                        dataType: "json",
                        data: {table: $(element).attr('data-ref'), id: id}
                    }).done(function (data) {
                        callback(data);
                    });
                }
            },
            formatResult: function (rowData, container, query, escapeMarkup) {
                var markup = [];
                window.Select2.util.markMatch(rowData.name, query.term, markup, escapeMarkup);
                return markup.join('');
            },
            formatSelection: function (rowData) {
                return rowData.name;
            }
        });
    });

    $('.js_select_ajax_distinct').each(function () {
        $(this).select2({
            allowClear: true,
            placeholder: 'Cerca',
            minimumInputLength: 0,
            ajax: {
                url: base_url + 'get_ajax/get_distinct_values',
                dataType: 'json',
                type: 'POST',
                data: function (term, page) {
                    return {q: term, limit: 50, field: $(this).attr('data-field-id')};
                },
                results: function (data, page) {
                    return {results: data};
                }
            },
            initSelection: function (element, callback) {
                var id = $(element).val();
                if (id !== "") {
                    $.ajax(base_url + 'get_ajax/get_distinct_values', {
                        type: 'POST',
                        dataType: "json",
                        data: {limit: 50, field: $(element).attr('data-field-id'), table: $(element).attr('data-ref'), id: id}
                    }).done(function (data) {
                        callback(data);
                    });
                }
            },
            formatResult: function (rowData) {
                return rowData.name;
            },
            formatSelection: function (rowData) {
                return rowData.name;
            }
        });
    });


    /**
     * Fancybox
     */
    $('a.js_thumbnail:not([rel=""]), .fancybox').fancybox();


    /**
     * Double click on checked checkboxes and radio
     * to activate onclick actions by default
     */
    $('input[type=checkbox][onclick][checked]').click().click();
    $('input[type=radio][onclick][checked]').click().click();




    /**
     * DataTables
     */
    startDataTables();
    startAjaxTables();


    /*
     * Dopo aver inizializzato il tutto, trigger resize della finestra in
     * modo da attivare gestione dimensioni di grafici e varie
     */
    $(window).trigger('resize');
}


/*
 * Modal
 */
var mAjaxCall = null;
function loadModal(url, data, callbackSuccess, method) {
    var modalContainer = $('#js_modal_container');
    if (typeof data === 'undefined') {
        data = {};
    }

    if (mAjaxCall !== null) {
        mAjaxCall.abort();
    }
    loading(true);

    mAjaxCall = $.ajax({
        url: url,
        type: method? method.toUpperCase(): 'POST',
        data: data,
        dataType: 'html',
        success: function (data) {
            modalContainer.html(data);
            $('.modal', modalContainer).modal()
                    .on('shown.bs.modal', function (e) {
                        loading(false);
                        Metronic.initUniform();
                        initComponents();

                        // Disable by default the confirmation request
                        $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = false;

                        if ($('form', modalContainer).size() > 0) {
                            $('input:not([type=hidden]), select, textarea', modalContainer).on('change', function () {
                                $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = true;
                            });
                        }

                    }).on('hide.bs.modal', function (e) {
                // FIX: ogni tanto viene lanciato un evento per niente - ad esempio sui datepicker
                if ($('.modal', modalContainer).is(e.target)) {
                    var askConfirmationOnClose = $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose;
                    if (askConfirmationOnClose && !confirm('Ci sono dati non salvati, uscendo ogni modifica sarà persa. Vuoi uscire comunque?')) {
                        // Stop hiding the modal
                        $('.modal', modalContainer).data('bs.modal').isShown = false;
                    } else {
                        $('.modal', modalContainer).data('bs.modal').isShown = true;
                    }
                }
            }).on('hidden.bs.modal', function (e) {
                if (typeof callbackSuccess !== 'undefined') {
                    callbackSuccess();
                }
            });
            mAjaxCall = null;
        },
        error: function () {
            mAjaxCall = null;
        }
    });
}

/** Fix per focus su select in modale **/
$.fn.modal.Constructor.prototype.enforceFocus = function () {
};



function formatDate(dateTime, ignoreTimezone) {
    
    if (!ignoreTimezone) {
        dateTime.setMinutes(dateTime.getMinutes() + dateTime.getTimezoneOffset());
    }
    
    var day = dateTime.getDate();
    var month = dateTime.getMonth() + 1;

    var date = [
        day < 10 ? '0' + day : day,
        month < 10 ? '0' + month : month,
        dateTime.getFullYear(),
    ].join('/');

    var hours = dateTime.getHours();
    var minutes = dateTime.getMinutes();
    var time = [
        hours < 10 ? '0' + hours : hours,
        minutes < 10 ? '0' + minutes : minutes,
        //'00'
    ].join(':');
    
    return date + ' ' + time;
}


function isAlldayEvent(datefrom, dateto, format) {
    var start = format ? moment(datefrom, format): moment(datefrom);
    var end   = format ? moment(dateto, format): moment(dateto);
    
    if (end.diff(start)!==86400000) {   // No an exact day
        return false;
    }
    
    if (start.minutes() !== 0 || end.minutes() !== 0) {   // Minutes are not 0
        return false;
    }
    
    if (start.hours() !== 0 || end.hours() !== 0) {       // Hours are not 0
        return false;
    }
    
    return true;
    
}



function changeStarsStatus(el) {
    var star = $(el);
    var val = parseInt(star.attr('data-val'));
    $('input', star.parent()).val(val);
    $('.star', star.parent()).each(function () {
        if (parseInt($(this).attr('data-val')) <= val) {
            // Stella prima da attivare
            $('i', $(this)).removeClass('fa fa-star-o').addClass('fa fa-star');
        } else {
            // Stella dopo
            $('i', $(this)).removeClass('fa fa-star').addClass('fa fa-star-o');

        }
    });
}




function changeLanguage(langId) {
    $.post(base_url + 'db_ajax/changeLanguage', {language: langId}, function(out) {
        if (out.success) {
            changeLanguageTemplate(langId);
        } else {
            alert('Impossibile impostare la lingua: ' + langId);
        }
    }, 'json');
}

function changeLanguageTemplate(langId) {
    $('[data-lang]').hide();
    $('[data-lang="' + langId + '"]').show();
    $('[data-toggle-lang]').removeClass('current');
    $('[data-toggle-lang="' + langId + '"]').addClass('current');
}





$(function () {
    
    Metronic.init();    // init metronic core components
    Layout.init();      // init current layout
    
    /*try {
        load_calendar();
    } catch (e) {}*/
    /*try {
        Ready();
    } catch (e) {}*/
    
    
    initComponents();
    
    $('body').on('click', '.js_open_modal', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var sUrl = $(this).attr('href');
        if (sUrl) {
            loadModal(sUrl, {});
        }
    });

    $('body').on('click', '.js_title_collapse', function () {
        $('.expand, .collapse', $(this)).click();
        $(window).trigger('resize');
    });

    $('body').on('click', '.expand, .collapse', function (e) {
        e.stopPropagation();
        $(window).trigger('resize');
    });

    $('body').tooltip({selector: '[data-toggle=tooltip]', container: 'body'});
    
    
    
    var list = $('<ul id="language-switch" class="pull-right list-inline">');
    $('.page-content > .layout-container > .page-title').append(list);
    $.getJSON(base_url + 'get_ajax/langInfo', {}, function(json) {
        var curr = json.current;
        $.each(json.languages, function(i, lang) {
            
            var toggle = $('<a href="javascript:void(0)">');
            toggle.append($('<img>').attr('src', lang.flag).attr('alt', lang.name));
            toggle.appendTo($('<li>').appendTo(list));
            toggle.attr('data-toggle-lang', lang.id);
            
            // Cosmetica button
            toggle.tooltip({
                title: lang.name,
                placement: 'bottom'
            });
            
            toggle.on('click', function() {
                changeLanguage(lang.id);
            });
        });
        
        if (curr) {
            changeLanguageTemplate(curr.id);
        }
    });
    
    
});

    