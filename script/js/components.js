/* Variabile globale per tracciare tutte le mappe create */
L.maps = {};


function initComponents() {
    /*
     * Form dates
     */

    $('.js_form_datepicker').datepicker({
        todayBtn: 'linked',
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        weekStart: 1,
        language: 'it'
    });
    $('.js_form_timepicker').timepicker({
        autoclose: true,
        modalBackdrop: false,
        showMeridian: false,
        format: 'hh:ii',
        minuteStep: 5
    });
    $('.js_form_datetimepicker').datetimepicker({
        todayBtn: 'linked',
        format: 'dd/mm/yyyy hh:ii',
        minuteStep: 5,
        autoclose: true,
        pickerPosition: 'bottom-left',
        forceParse: false,
        todayHighlight: true,
        weekStart: 1,
        language: 'it'
    });

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
        
        //console.log(start);
        
        jqDateRange.daterangepicker({
            format: 'DD/MM/YYYY',
            separator: ' to ',
            startDate: start,
            endDate: end,
            opens: 'left',
            locale: {
                applyLabel: 'Applica',
                cancelLabel: 'Annulla',
                fromLabel: 'Da',
                toLabel: 'A',
                weekLabel: 'W',
                customRangeLabel: 'Range custom',
                daysOfWeek: ["Do", "Lu", "Ma", "Me", "Gi", "Ve", "Sa"],
                monthNames: ["Gen", "Feb", "Mar", "Apr", "Mag", "Giu", "Lug", "Ago", "Set", "Ott", "Nov", "Dic"],
                format: 'DD/MM/YYYY',
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
            var that = $(this);
            var minInput = that.data('minimum-input-length');
            that.select2({allowClear: true, minimumInputLength: minInput ? minInput : 0});
        });
        $('.select2me').select2({allowClear: true});
    } catch (e) {
    }
    
    $('.select2_standard').select2();

    var fieldsSources = [];
    $('[data-source-field]:not([data-source-field=""])').each(function () {
        
        console.log($(this).attr('name'));
        
        // Prendo il form dell'elemento
        var jsMultiselect = $(this);
        var jqForm = jsMultiselect.parents('form');
        var sSourceField = jsMultiselect.attr('data-source-field');
        var sFieldRef = jsMultiselect.attr('data-ref');

        // Prendo il campo da osservare
        var jqField = $('[name="' + sSourceField + '"]', jqForm);
        
        
        console.log(sSourceField);
        
        jqField.on('change', function () {
            
            
            console.log($(this));
            
            var previousValue = jsMultiselect.attr('data-val').split(',');
            jsMultiselect.select2('val', '');

            //se ho una select semplice devo saperlo perché così so come gestire il valore settato
            var isNormalSelect = (jsMultiselect.is('select') && !jsMultiselect.attr('multiple'));
            
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
                    $('option', jsMultiselect).remove();
                    $('<option></option>').appendTo(jsMultiselect);
                    var previousValueFound = false;
                    $.each(data, function (k, v) {
                        var jqOption = $('<option></option>').val(k).text(v);

                        if ($.inArray(k, previousValue) > -1) {
                            previousValueFound = true;
                        }

                        jsMultiselect.append(jqOption);
                    });

                    
                    if (previousValueFound) {
                        if (isNormalSelect) {
                            
                            jsMultiselect.val(previousValue[0]);  // Solo UN valore
                            jsMultiselect.select2('val', previousValue);
                        } else {
                            
                            jsMultiselect.select2('val', previousValue);
                        }
                    }
                }
            });
        });

        if ($.inArray(jqField, fieldsSources) === -1) {
            //console.log(jqField);
            fieldsSources.push(jqField);
        }
    });

    $.each(fieldsSources, function (k, selector) {
        var field = selector;//$(selector);
        //console.log(selector);
        //alert(field.val());
        if (field.val() !== '') {
            
            field.trigger('change');
            
        }
    });


    /*
     * Select ajax
     */


    $('.js_select_ajax_new').each(function () {
        var input = $(this);

        input.select2({
            ajax: {
                url: base_url + 'get_ajax/select_ajax_search',
                dataType: 'json',
                delay: 250,
                type: 'POST',
                data: function (term, page) {
                    //var input = $(this); [???]
                    // C'è un attributo data-referer che identifica il campo che richiede i dati?
                    // Se non c'è prendi il name...
                    var referer = input.data('referer');
                    if (!referer) {
                        referer = input.attr('name');
                    }

                    return {q: term, limit: 100, table: input.attr('data-ref'), referer: referer};
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id
                            }
                        })
                    };
                },
                
//                processResults: function (data, params) {
//                    
//                    
//                    // parse the results into the format expected by Select2
//                    // since we are using custom formatting functions we do not need to
//                    // alter the remote JSON data, except to indicate that infinite
//                    // scrolling can be used
//                    params.page = params.page || 1;
//
//                    return {
//                        results: data,
//                        pagination: {
//                            more: (params.page * 30) < data.total_count
//                        }
//                    };
//                },
                
                
                cache: false
            },
            placeholder: 'Ricerca...',
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            //templateResult: formatRepo,
            templateSelection: formatRepoSelection,
            language: "it"
        });
    });

    function formatRepo(repo) {
        
        console.log(repo);
        
        if (repo.loading) {
            return repo.text;
        }

        var markup = "<div class="
        select2 - result - repository
        clearfix
        ">" +
        "<div class="
        select2 - result - repository__avatar
        "><img src="
        " + repo.owner.avatar_url + "
        " /></div>" +
        "<div class="
        select2 - result - repository__meta
        ">" +
        "<div class="
        select2 - result - repository__title
        ">" + repo.name + "</div>";

        if (repo.description) {
            markup += "<div class='select2-result-repository__description'>" + repo.description + "</div>";
        }

        markup += "<div class='select2-result-repository__statistics'>" +
            "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> " + repo.forks_count + " Forks</div>" +
            "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + repo.stargazers_count + " Stars</div>" +
            "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + repo.watchers_count + " Watchers</div>" +
            "</div>" +
            "</div></div>";

        return markup;
    }

    function formatRepoSelection(repo) {
        return repo.full_name || repo.text;
    }


    $('.js_select_ajax').each(function () {
        var input = $(this);

        input.select2({
            allowClear: true,
            placeholder: 'Cerca valori',
            minimumInputLength: 0,
            ajax: {
                url: base_url + 'get_ajax/select_ajax_search',
                dataType: 'json',
                type: 'POST',
                data: function (term, page) {
                    //var input = $(this); [???]
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
                var id = element.val();
                if (id !== "") {
                    $.ajax(base_url + 'get_ajax/select_ajax_search', {
                        type: 'POST',
                        dataType: "json",
                        data: {table: element.attr('data-ref'), id: id}
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
                        data: {
                            limit: 50,
                            field: $(element).attr('data-field-id'),
                            table: $(element).attr('data-ref'),
                            id: id
                        }
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
    startNewDatatableInline();

    /**
     * Lancia evento `init.crm.components` per permettere ad eventuali hook
     * caricati nella pagina di inizializzarsi...
     *
     * Per gestire questi eventi è sufficiente aggiungere un handler al document
     * per l'evento `init.crm.components`
     *
     * $(document).on('init.crm.components', function() {
     *      ...
     * });
     */
    $.event.trigger('init.crm.components');


    /**
     * Dopo aver inizializzato il tutto, trigger resize della finestra in
     * modo da attivare gestione dimensioni di grafici e varie
     */
    $(window).trigger('resize');
}


/*
 * Modal
 */
var mAjaxCall = null, mArgs = null;

function loadModal(url, data, callbackSuccess, method) {

    var modalContainer = $('#js_modal_container');
    if (typeof data === 'undefined') {
        data = {};
    }

    /*** La modale è già aperta? */
    var modalLoaded = modalContainer.find('> .modal');
    if (modalLoaded.length > 0 && modalLoaded.is(':visible')) {
        // Chiudila e apri la nuova.. valutare riapertura
        modalLoaded.modal('hide').on('hidden.bs.modal', function (e) {
            // Una volta che la vecchia modale è realmente nascosta, richiama
            // questa funzione
            loadModal(url, data, callbackSuccess, method);
        });
        return;
    }


    if (mAjaxCall !== null) {
        mAjaxCall.abort();
    }
    loading(true);

    mAjaxCall = $.ajax({
        url: url,
        type: method ? method.toUpperCase() : 'POST',
        data: data,
        dataType: 'html',
        success: function (data) {

            // Salva i vecchi argomenti per riapertura
            var oldModalArgs = mArgs;
            mArgs = {
                url: url,
                data: data,
                fn: callbackSuccess,
                verb: method,
            };

            modalContainer.html(data);
            $('.modal', modalContainer).modal()
                .on('shown.bs.modal', function (e) {
                    loading(false);
                    reset_theme_components();

                    // Disable by default the confirmation request
                    $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = false;

                    if ($('form', modalContainer).length > 0) {
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

                mArgs = oldModalArgs;
                if (typeof callbackSuccess === 'function') {
                    callbackSuccess();
                }

                // Se c'erano degli mArgs allora significa che ho chiuso una
                // modale per riaprirne una nuova... quiiiiindi richiama
                // loadModals
//                if (mArgs) {
//                    loadModal(mArgs.url, mArgs.data, mArgs.fn, mArgs.verb);
//                }
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
    var start = format ? moment(datefrom, format) : moment(datefrom);
    var end = format ? moment(dateto, format) : moment(dateto);

    if (end.diff(start) !== 86400000) {   // No an exact day
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
    $.post(base_url + 'db_ajax/changeLanguage', {language: langId}, function (out) {
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


function openCreationForm(formId, entity, onSuccess) {
    //var whnd = window.open(base_url + 'main/form/'+formId, '_blank', 'height=400,width=600,menubar=no,status=no');
    $.getJSON(base_url + 'get_ajax/getLastRecord', {entity: entity}, function (json) {

        var currentLastId = json.data.id;

        $.fancybox.open({
            maxWidth: 600,
            padding: 25,
            wrapCSS: 'fancybox-white',
            type: 'ajax',
            href: base_url + 'main/form/' + formId,
            ajax: {
                data: {_raw: 1}
            },
            beforeShow: function () {
                initComponents();
            },
            afterShow: function () {
                initComponents();
            },
            beforeClose: function () {
                $.getJSON(base_url + 'get_ajax/getLastRecord', {entity: entity}, function (json) {
                    if (json.status === 0 && json.data.id && currentLastId != json.data.id) {
                        onSuccess(json.data.id, json.data.preview);
                    }
                });
            },
        });
    });
}


$(function () {

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


    var list = $('<ul class="language-switch pull-right list-inline">');
    $('.page-content > .layout-container > .page-title').append(list);
    $.getJSON(base_url + 'get_ajax/langInfo', {}, function (json) {
        var curr = json.current;
        $.each(json.languages, function (i, lang) {

            var toggle = $('<a href="javascript:void(0)">');
            toggle.append($('<img>').attr('src', lang.flag).attr('alt', lang.name));
            toggle.appendTo($('<li>').appendTo(list));
            toggle.attr('data-toggle-lang', lang.id);

            // Cosmetica button
            toggle.tooltip({
                title: lang.name,
                placement: 'bottom'
            });

            toggle.on('click', function () {
                changeLanguage(lang.id);
            });
        });

        if (curr) {
            changeLanguageTemplate(curr.id);
        }
    });


    $('body').on('click', '.lang-flag', function () {
        var inlinelist = list.clone().addClass('floating').insertAfter($(this));
        var flags = $('[data-toggle-lang]', inlinelist);

        flags.on('click', function () {
            var id = $(this).data('toggle-lang');
            changeLanguage(parseInt(id));
            inlinelist.remove();
        });
    });

    $('.content').on('keyup', '.decimal', function () {
        var val = $(this).val().replace(',', '.');
        if (isNaN(val)) {
            val = val.replace(/[^0-9\.]/g, '');
            if (val.split('.').length > 2)
                val = val.replace(/\.+$/, "");
        }
        $(this).val(val);
    });

    //jQuery in automatico appende ?_={timestamp} su tutti i js caricati da jquery.plugin. Così non lo fa...
    $.ajaxSetup({cache: true});
});

