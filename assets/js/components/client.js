'use strict';

/* Load content ajax */


$('body').on('click', '.___js_ajax_content', function (e) {

    // Check if has a layout id to open
    var layout_id = $(this).data('layout-id');
    var link_href = $(this).attr('href');
    var get_params = link_href.split('?');
    if (get_params[1]) {
        get_params = '?' + get_params[1];
    } else {
        get_params = '';
    }
    var that = $(this);
    if (layout_id && !e.metaKey) {

        e.preventDefault();
        loading(true);
        $.ajax(base_url + 'main/get_layout_content/' + layout_id + get_params, {
            type: 'GET',
            dataType: 'json',
            complete: function () {
                //$('#js_page_content').stop(true, true).show();
                loading(false);
            },
            success: function (data) {
                if (data.status == 0) {
                    console.log(data.msg);
                }
                if (data.status == 1) {

                    if (data.type == 'pdf') {
                        //location.href = link_href;
                        window.open(link_href, '_blank');
                    } else {

                        $('.js_submenu_item.active').removeClass('active');

                        $('#js_layout_content_wrapper').data('layout-id', layout_id);
                        document.title = data.dati.title_prefix;
                        $('#js_page_content,.js_page_content').html(data.content); // 2022-04-22 - Added class because of Adminlte3
                        window.history.pushState("", "", link_href);
                        initComponents($('#js_page_content,.js_page_content'), true); // 2022-04-22 - Added class because of Adminlte3

                        // Fix for sidebar to active li
                        if ($(that).parent().hasClass('js_sidebar_menu_item')) {

                            $('.js_sidebar_menu_item').removeClass('active');
                            $('.js_sidebar_menu_item').removeClass('menu-open');
                            $('.treeview-menu').hide();
                        }
                        if ($(that).parent().hasClass('js_submenu_item')) {
                            $('.js_sidebar_menu_item').removeClass('active');
                            $('.js_sidebar_menu_item').removeClass('menu-open');
                            $('.treeview-menu').hide();

                            $(that).parent().addClass('active');
                            $(that).closest('.js_sidebar_menu_item').addClass('menu-open');
                            $(that).closest('.treeview-menu').show();
                        } else {
                            $(that).parent().addClass('active');
                        }
                    }
                }
            },
        });
        e.stopPropagation();
    } else {

    }
});


function destroyCkeditorInstances(instance = null) {
    try {
        if (instance) {
            var instance_name = instance.attr('id');
            if (CKEDITOR.instances[instance_name]) {
                CKEDITOR.instances[instance_name].destroy(true);
            }
        } else {
            for (instance_name in CKEDITOR.instances) {
                CKEDITOR.instances[instance_name].destroy(true);
            }
        }
    } catch (e) {

    }
}

function fillEditor(selector, content) {
    if (!(selector instanceof jQuery)) {
        selector = $(selector);
    }

    var selector_id = selector.attr('id');

    if (tinymce.get(selector_id)) {
        tinymce.activeEditor.setContent(content);
    } else {
        selector.val(content);
    }
}

function initTinymce(container = null) {
    if (!container) {
        container = $('body');
    }

    var tinymce_config = {
        selector: 'textarea.js_tinymce',
        height: 500,
        resize: true,
        autosave_ask_before_unload: false,
        powerpaste_allow_local_images: true,
        paste_data_images: true,
        relative_urls: false,
        remove_script_host: false,
        plugins: 'print preview paste importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern noneditable charmap quickbars emoticons',
        menubar: 'file edit view insert format tools table',
        toolbar: 'undo redo | bold italic underline strikethrough | fontselect fontsizeselect formatselect | alignleft aligncenter alignright alignjustify | outdent indent |  numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen  preview save print | insertfile image media template link anchor code | ltr rtl',
        toolbar_sticky: false,
        automatic_uploads: true,
        images_upload_handler: function (blobInfo, success, failure) {
            $.ajax({
                url: base_url + 'get_ajax/image_from_base64',
                type: 'post',
                dataType: 'json',
                async: false,
                data: {
                    [token_name]: token_hash,
                    base64: blobInfo.base64()
                },
                success: function (res) {
                    success(res.txt);
                },
                error: function (request, status, error) {

                }
            });
        },
        content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
    };

    $('textarea.js_tinymce', container).each(function () {
        var tinymce_id = $(this).attr('id');

        if (tinymce.get(tinymce_id)) {
            tinymce.remove();
        }

        tinymce_config.selector = 'textarea#' + tinymce_id;

        tinymce.init(tinymce_config);
    });
}

var initializing = false;

function initComponents(container, reset = false) {
    if (initializing && !reset) {
        return;
    }

    initializing = true;
    if (typeof container === 'undefined') {
        container = $('body');
    }
    if (reset) {
        try {
            $('select', container).each(function () {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    // Select2 has been initialized
                    $(this).select2('destroy');
                }
            });
        } catch (e) { }

        // destroyCkeditorInstances($('textarea.js_ckeditor', container));

    }

    $('textarea.js_ckeditor', container).each(function () {
        var ckeditor_id = $(this).attr('id');

        destroyCkeditorInstances($(this));

        CKEDITOR.replace(ckeditor_id, {
            filebrowserUploadUrl: base_url + 'db_ajax/ck_uploader'
        });
        CKEDITOR.config.toolbar = [
            ['Bold', 'Italic', 'Underline', 'StrikeThrough', '-', 'Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'NumberedList', 'BulletedList', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
        ];
    });

    initTinymce(container);

    /*
     * Form Multiple key values
     */

    $('.js_multiple_container').on('click', '.js_add_multiple_key_values', function (e) {
        e.stopPropagation();

        var my_multiple_container = $(this).closest('.js_multiple_container');
        var my_row_container = $('.js_multiple_row_container', my_multiple_container);
        var clone = $('.js_multiple_key_values_row', my_row_container).filter(':first').clone().appendTo(my_row_container);
        var count = $('.js_multiple_key_values_row', my_row_container).length;

        $('input,textarea', clone).each(function () {
            $(this).val('');
            var type = $(this).attr('data-type');
            var name = $(this).attr('data-name');
            $(this).attr('name', name + '[' + count + '][' + type + ']').removeAttr('data-name');
        });
        count++;
    });

    $('.js_multiple_container').on('click', '.js_remove_row', function (e) {
        var my_multiple_container = $(this).closest('.js_multiple_container');
        var my_row_container = $(this).closest('.js_multiple_key_values_row');
        if ($('.js_multiple_key_values_row', my_multiple_container).length > 1) {
            my_row_container.remove();
        }
    });

    $('.js_multiple_container').on('click', '.js_add_multiple_values', function (e) {
        e.stopPropagation();

        var my_multiple_container = $(this).closest('.js_multiple_container');
        var my_row_container = $('.js_multiple_row_container', my_multiple_container);
        var clone = $('.js_multiple_values_row', my_row_container).filter(':first').clone().appendTo(my_row_container);
        var count = $('.js_multiple_values_row', my_row_container).length;

        $('input', clone).each(function () {
            $(this).val('');
            var name = $(this).attr('data-name');
            $(this).attr('name', name + '[' + count + ']').removeAttr('data-name');
        });
        count++;
    });
    $('.js_multiple_container').on('click', '.js_remove_row', function (e) {
        var my_multiple_container = $(this).closest('.js_multiple_container');
        var my_row_container = $(this).closest('.js_multiple_values_row');
        if ($('.js_multiple_values_row', my_multiple_container).length > 1) {
            my_row_container.remove();
        }
    });

    /*
     * Form Todo values
     */

    $('.js_todo_container').on('click', '.js_add_multiple_key_values', function (e) {
        e.stopPropagation();

        var my_multiple_container = $(this).closest('.js_todo_container');
        var my_row_container = $('.js_multiple_row_container', my_multiple_container);
        var clone = $('.js_multiple_key_values_row', my_row_container).filter(':first').clone().appendTo(my_row_container);
        var count = $('.js_multiple_key_values_row', my_row_container).length;

        $('input,textarea', clone).each(function () {
            $(this).val('');
            var type = $(this).attr('data-type');
            var name = $(this).attr('data-name');
            $(this).attr('name', name + '[' + count + '][' + type + ']').removeAttr('data-name');
        });
        $('.js_container-checkbox', clone).prop('checked', false);
        $('textarea', clone).css('text-decoration', 'unset');
        count++;
    });

    $('.js_todo_container').on('click', '.js_remove_row', function (e) {
        var my_multiple_container = $(this).closest('.js_todo_container');
        var my_row_container = $(this).closest('.js_multiple_key_values_row');
        if ($('.js_multiple_key_values_row', my_multiple_container).length > 1) {
            my_row_container.remove();
        }
    });

    $(".js_todo_row_container").sortable();
    $(".js_todo_row_container").disableSelection();

    $(".js_multiple_row_container").on('keydown', '.js_todo_textarea', function (event) {

        if (event.which == 9) {

            //event.preventDefault();
            var my_multiple_container = $(this).closest('.js_todo_container');
            var my_row_container = $('.js_multiple_row_container', my_multiple_container);
            var clone = $('.js_multiple_key_values_row', my_row_container).filter(':first').clone().appendTo(my_row_container);
            var count = $('.js_multiple_key_values_row', my_row_container).length;

            $('input,textarea', clone).each(function () {
                $(this).val('');
                var type = $(this).attr('data-type');
                var name = $(this).attr('data-name');
                $(this).attr('name', name + '[' + count + '][' + type + ']').removeAttr('data-name');
            });
            $('.js_container-checkbox', clone).prop('checked', false);
            $('textarea', clone).css('text-decoration', 'unset');
            count++;
        }
    });
    $('.js_todo_textarea').each(function () {
        this.parentNode.dataset.replicatedValue = this.value
    });
    $('.js_todo_container').on('click', '.js_container-checkbox', function (e) {
        var my_row_container = $(this).closest('.js_multiple_key_values_row');
        if ($(this).is(':checked')) {
            $('.js_todo_textarea', my_row_container).css('text-decoration', 'line-through');
        } else {
            $('.js_todo_textarea', my_row_container).css('text-decoration', 'unset');
        }
    });
    /*
     * Form dates
     */
    

    $('.js_form_datepicker', container).datepicker({
        todayBtn: 'linked',
        format: 'dd/mm/yyyy',
        todayHighlight: true,
        weekStart: 1,
        language: lang_short_code,
    });

    $('.js_form_timepicker', container).timepicker({
        autoclose: true,
        modalBackdrop: false,
        showMeridian: false,
        format: 'hh:ii',
        minuteStep: 5,
    });

    $('.js_form_datetimepicker', container).datetimepicker({
        todayBtn: 'linked',
        format: 'dd/mm/yyyy hh:ii',
        minuteStep: 5,
        autoclose: true,
        pickerPosition: 'bottom-left',
        forceParse: false,
        todayHighlight: true,
        weekStart: 1,
        language: lang_short_code,
    });

    $('.js_form_daterangepicker', container).each(function () {
        var jqDateRange = $(this);
        var sDate = $('input', jqDateRange).val();

        var start = new Date(),
            end = new Date();
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
            opens: 'left',
            locale: {
                applyLabel: 'Applica',
                cancelLabel: 'Annulla',
                fromLabel: 'Da',
                toLabel: 'A',
                weekLabel: 'W',
                customRangeLabel: 'Range custom',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa'],
                monthNames: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
                format: 'DD/MM/YYYY',
                firstDay: 1,
            },
            ranges: {
                'Oggi': [moment(), moment()],
                'Ieri': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Ultimi 7 Giorni': [moment().subtract(6, 'days'), moment()],
                'Ultimi 30 Giorni': [moment().subtract(29, 'days'), moment()],
                'Mese corrente': [moment().startOf('month'), moment().endOf('month')],
                'Mese precedente': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Anno corrente': [moment().startOf('year'), moment().endOf('year')],
                'Anno precedente': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
            },
        },
            function (start, end) {
                $('input', this.element).val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));
            }
        );
    });

    $('.js_form_colorpicker', container).colorpicker({ format: 'hex' });

    /*
     * Grid-filtering forms
     */
    var forms = $('.js_filter_form', container);
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
        $('.js_multiselect:not(.select2-offscreen):not(.select2-container)', container).each(function () {
            var that = $(this);
            var minInput = that.data('minimum-input-length');
            that.select2({
                allowClear: true,
                minimumInputLength: minInput ? minInput : 0,
            });
        });

        $('.select2me', container).select2({ allowClear: true });
    } catch (e) { }

    $('.select2_standard', container).select2();



    /*
     * Select ajax
     */

    $('.js_select_ajax_new', container).each(function () {
        var get_params = $(this).closest('.layout_box').data('get_pars');
        if (get_params) {
            get_params = '?' + get_params;
        } else {
            get_params = '';
        }
        var input = $(this);

        var $allow_clear = false;
        var field_required = $(this).data('required');

        if (field_required == 0) {
            $allow_clear = true;
        }

        input.select2({
            ajax: {
                url: base_url + 'get_ajax/select_ajax_search' + get_params,
                dataType: 'json',
                delay: 250,
                type: 'POST',
                data: function (term, page) {
                    // C'è un attributo data-referer che identifica il campo che richiede i dati?
                    // Se non c'è prendi il name...
                    var referer = input.data('referer');
                    if (!referer) {
                        referer = input.attr('name');
                    }

                    var data_post = [];
                    data_post.push({ name: token_name, value: token_hash });
                    data_post.push({ name: 'q', value: term.term });
                    data_post.push({ name: 'limit', value: 100 });
                    data_post.push({ name: 'table', value: input.attr('data-ref') });
                    data_post.push({ name: 'referer', value: referer });

                    return data_post;
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.name,
                                id: item.id,
                            };
                        }),
                    };
                },

                cache: false,
            },
            placeholder: 'Ricerca...',
            escapeMarkup: function (markup) {
                return markup;
            }, // let our custom formatter work
            minimumInputLength: 1,
            templateSelection: formatRepoSelection,
            language: lang_short_code,
            allowClear: $allow_clear,
        });
    });

    var fieldsSources = [];
    $('[data-source-field]:not([data-source-field=""])', container).each(function () {
        // Prendo il form dell'elemento
        var jsMultiselect = $(this);
        var jqForm = jsMultiselect.parents('form');
        var sSourceField = jsMultiselect.attr('data-source-field');
        var sFieldRef = jsMultiselect.attr('data-ref');

        // Prendo il campo da osservare
        var jqField = $('[name="' + sSourceField + '"],[name="' + sSourceField + '[]"],[data-field_name="' + sSourceField + '"]', jqForm);

        jqField.on('change', function () {
            var previousValue = jsMultiselect.attr('data-val').split(',');
            jsMultiselect.select2('val', '');

            //se ho una select semplice devo saperlo perché così so come gestire il valore settato
            //var isNormalSelect = (jsMultiselect.is('select') && !jsMultiselect.attr('multiple'));
            var isNormalSelect = jsMultiselect.is('select') && !$(this).attr('multiple');

            var field_name_to = jsMultiselect.attr('name');
            if (field_name_to.indexOf('conditions[') !== -1) {
                field_name_to = jsMultiselect.data('field_name');
            }

            $('option', jsMultiselect).remove();

            loading(true);

            var data_post = [];
            data_post.push({ name: token_name, value: token_hash });
            data_post.push({ name: 'field_name_to', value: field_name_to });
            data_post.push({ name: 'field_ref', value: sFieldRef });
            if (isNormalSelect) {
                data_post.push({ name: 'field_from_val', value: jqField.val() });
            } else {
                var val_array = jqField.val();
                for (var i in val_array) {
                    data_post.push({ name: 'field_from_val[]', value: val_array[i] });
                }
            }

            $.ajax(base_url + 'get_ajax/filter_multiselect_data', {
                type: 'POST',
                data: data_post,
                dataType: 'json',
                async: true,
                complete: function () {
                    loading(false);
                },
                success: function (data) {
                    $('option', jsMultiselect).remove();
                    $('<option></option>').appendTo(jsMultiselect);
                    var previousValueFound = false;
                    $.each(data, function (k, v) {
                        var jqOption = $("<option></option>").val(v.id).text(v.value);

                        if ($.inArray(k, previousValue) > -1) {
                            previousValueFound = true;
                        }

                        jsMultiselect.append(jqOption);
                    });

                    if (previousValueFound) {
                        if (isNormalSelect) {
                            jsMultiselect.val(previousValue[0]); // Solo UN valore
                            jsMultiselect.select2('val', previousValue);
                        } else {
                            jsMultiselect.val(previousValue).trigger('change');
                            jsMultiselect.select2('data', previousValue);
                        }
                    }
                },
            });
        });

        if ($.inArray(jqField, fieldsSources) === -1) {
            fieldsSources.push(jqField);
        }
    });

    $.each(fieldsSources, function (k, selector) {
        var field = selector;

        if (field.val() !== '') {
            field.trigger('change');
        }
    });

    function formatRepo(repo) {
        if (repo.loading) {
            return repo.text;
        }

        var markup = '<div class=';
        select2 - result - repository;
        clearfix;
        '>' + '<div class=';
        select2 - result - repository__avatar;
        ('><img src=');
        (' + repo.owner.avatar_url + ');
        ' /></div>' + '<div class=';
        select2 - result - repository__meta;
        '>' + '<div class=';
        select2 - result - repository__title;
        '>' + repo.name + '</div>';

        if (repo.description) {
            markup += "<div class='select2-result-repository__description'>" + repo.description + '</div>';
        }

        markup += "<div class='select2-result-repository__statistics'>" + "<div class='select2-result-repository__forks'><i class='fa fa-flash'></i> " + repo.forks_count + ' Forks</div>' + "<div class='select2-result-repository__stargazers'><i class='fa fa-star'></i> " + repo.stargazers_count + ' Stars</div>' + "<div class='select2-result-repository__watchers'><i class='fa fa-eye'></i> " + repo.watchers_count + ' Watchers</div>' + '</div>' + '</div></div>';

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
                    // C'è un attributo data-referer che identifica il campo che richiede i dati?
                    // Se non c'è prendi il name...
                    var referer = input.data('referer');
                    if (!referer) {
                        referer = input.attr('name');
                    }
                    var data_post = [];
                    data_post.push({ name: token_name, value: token_hash });
                    data_post.push({ name: 'q', value: term });
                    data_post.push({ name: 'limit', value: 100 });
                    data_post.push({ name: 'table', value: input.attr('data-ref') });
                    data_post.push({ name: 'referer', value: referer });
                    return data_post;
                },
                results: function (data, page) {
                    return { results: data };
                },
            },

            initSelection: function (element, callback) {
                var id = element.val();
                if (id !== '') {
                    var data_post = [];
                    data_post.push({ name: token_name, value: token_hash });
                    data_post.push({ name: 'table', value: element.attr('data-ref') });
                    data_post.push({ name: 'id', value: id });

                    $.ajax(base_url + 'get_ajax/select_ajax_search', {
                        type: 'POST',
                        dataType: 'json',
                        data: data_post,
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
            },
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
                    var data_post = [];
                    data_post.push({ name: token_name, value: token_hash });
                    data_post.push({ name: 'q', value: term });
                    data_post.push({ name: 'limit', value: 50 });
                    data_post.push({
                        name: 'field',
                        value: $(this).attr('data-field-id'),
                    });
                    return data_post;
                },
                results: function (data, page) {
                    return { results: data };
                },
            },
            initSelection: function (element, callback) {
                var id = $(element).val();
                if (id !== '') {
                    var data_post = [];
                    data_post.push({ name: token_name, value: token_hash });

                    data_post.push({ name: 'limit', value: 50 });
                    data_post.push({
                        name: 'field',
                        value: $(element).attr('data-field-id'),
                    });
                    data_post.push({ name: 'table', value: $(element).attr('data-ref') });
                    data_post.push({ name: 'id', value: id });
                    $.ajax(base_url + 'get_ajax/get_distinct_values', {
                        type: 'POST',
                        dataType: 'json',
                        data: data_post,
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
            },
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
     * Tables
     */
    initTables(container);
    // Old for retro-compatibility
    startDataTables(container);
    startAjaxTables(container);
    startNewDatatableInline(container);

    /**
     * Charts
     */
    drawPieCharts();
    drawPieLegendCharts();

    /**
     * Tabs
     */
    // tabsInit();

    /**
     * Calendars
     */
    // initCalendars();
    /**
     * Maps
     */

    // mapsInit();
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

    $('.box').each(function () {
        try {
            $.fn.boxWidget.call($(this));
        } catch (e) {

        }

    });
    /**
     * Dopo aver inizializzato il tutto, trigger resize della finestra in
     * modo da attivare gestione dimensioni di grafici e varie
     */
    $(window).trigger('resize');

    initializing = false;
}

/*
 * Modal
 */
var mAjaxCall = null,
    mArgs = null;

function loadModal(url, data, callbackSuccess, method) {
    try {
        var token = JSON.parse(atob($(this).data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
    } catch (e) {
        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
    }

    var modalContainer = $('#js_modal_container');

    if (typeof data === 'undefined') {
        data = [];
        data.push({ name: token_name, value: token_hash });
    } else if (Array.isArray(data)) {
        data.push({ name: token_name, value: token_hash });
    } else {
        data[token_name] = token_hash;
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
            $('.modal', modalContainer)
                .modal()
                .on('shown.bs.modal', function (e) {
                    loading(false);
                    reset_theme_components();
                    console.log('Check here...');
                    //initComponents(modalContainer);
                    // Disable by default the confirmation request
                    $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = false;

                    if ($('form', modalContainer).length > 0) {
                        $('input:not([type=hidden]), select, textarea', modalContainer).on('change', function () {
                            $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose = true;
                        });
                    }
                })
                .on('hide.bs.modal', function (e) {
                    // FIX: ogni tanto viene lanciato un evento per niente - ad esempio sui datepicker
                    if ($('.modal', modalContainer).is(e.target)) {
                        var askConfirmationOnClose = $('.modal', modalContainer).data('bs.modal').askConfirmationOnClose;

                        if (askConfirmationOnClose && !confirm('Are you sure?')) {
                            // Stop hiding the modal
                            $('.modal', modalContainer).data('bs.modal').isShown = false;
                        } else {
                            $('.modal', modalContainer).data('bs.modal').isShown = true;
                        }
                    }
                })
                .on('hidden.bs.modal', function (e) {
                    mArgs = oldModalArgs;
                    if (typeof callbackSuccess === 'function') {
                        callbackSuccess();
                    }
                });
            mAjaxCall = null;
        },
        error: function () {
            mAjaxCall = null;
        },
    });
}

/** Fix per focus su select in modale **/
$.fn.modal.Constructor.prototype.enforceFocus = function () { };

function formatDate(dateTime, ignoreTimezone) {
    if (!ignoreTimezone) {
        dateTime.setMinutes(dateTime.getMinutes() + dateTime.getTimezoneOffset());
    }

    var day = dateTime.getDate();
    var month = dateTime.getMonth() + 1;

    var date = [day < 10 ? '0' + day : day, month < 10 ? '0' + month : month, dateTime.getFullYear()].join('/');

    var hours = dateTime.getHours();
    var minutes = dateTime.getMinutes();
    var time = [hours < 10 ? '0' + hours : hours, minutes < 10 ? '0' + minutes : minutes].join(':');

    return date + ' ' + time;
}

function isAlldayEvent(datefrom, dateto, format) {
    var start = format ? moment(datefrom, format) : moment(datefrom);
    var end = format ? moment(dateto, format) : moment(dateto);

    if (end.diff(start) !== 86400000) {
        // No an exact day
        return false;
    }

    if (start.minutes() !== 0 || end.minutes() !== 0) {
        // Minutes are not 0
        return false;
    }

    if (start.hours() !== 0 || end.hours() !== 0) {
        // Hours are not 0
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
    var data = { language: langId };
    try {
        var token = JSON.parse(atob(datatable.data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
    } catch (e) {
        var token = JSON.parse(atob($('body').data('csrf')));
        var token_name = token.name;
        var token_hash = token.hash;
    }
    data[token_name] = token_hash;

    $.post(
        base_url + 'db_ajax/changeLanguage', data,
        function (out) {
            if (out.success) {
                changeLanguageTemplate(out.lang);
            } else {
                alert('Impossibile impostare la lingua: ' + langId);
            }
        },
        'json'
    );
}

function changeLanguageTemplate(lang) {
    var langId = lang.id;

    $('[data-lang]').hide();
    $('[data-lang="' + langId + '"]').show();
    $('[data-toggle-lang]').removeClass('current');
    $('[data-toggle-lang="' + langId + '"]').addClass('current');

    $('.language-icon').hide();

    $('.language-flag').attr('src', lang.flag).show();
}

function openCreationForm(formId, entity, onSuccess) {
    $.getJSON(base_url + 'get_ajax/getLastRecord', { entity: entity }, function (json) {
        var currentLastId = json.data.id;

        $.fancybox.open({
            maxWidth: 600,
            padding: 25,
            wrapCSS: 'fancybox-white',
            type: 'ajax',
            href: base_url + 'main/form/' + formId,
            ajax: {
                data: { _raw: 1 },
            },
            beforeShow: function () {
                initComponents($('#form_' + formId), true);
            },
            afterShow: function () {
                initComponents($('#form_' + formId), true);
            },
            beforeClose: function () {
                $.getJSON(base_url + 'get_ajax/getLastRecord', { entity: entity }, function (json) {
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

        try {
            var token = JSON.parse(atob($(this).data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;
        } catch (e) {
            var token = JSON.parse(atob($('body').data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;
        }

        if (sUrl) {
            var data_post = [];
            data_post.push({ name: token_name, value: token_hash });

            loadModal(sUrl, data_post);
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

    $('body').tooltip({ selector: '[data-toggle=tooltip]', container: 'body' });
    //alert('todo');
    var list = $('<ul class="language-switch dropdown-menu">');
    $('.page-content > .layout-container > .page-title, .navbar-custom-menu > .nav > #languages').append(list);
    $.getJSON(base_url + 'get_ajax/langInfo', {}, function (json) {
        var curr = json.current;
        $.each(json.languages, function (i, lang) {
            var toggle = $('<a href="javascript:void(0)">');
            toggle.html('&nbsp;' + lang.name);
            toggle.prepend($('<img>').attr('src', lang.flag).attr('alt', lang.name));
            toggle.appendTo($('<li>').appendTo(list));
            toggle.attr('data-toggle-lang', lang.id);

            // Cosmetica button
            toggle.tooltip({
                title: lang.name,
                placement: 'bottom',
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
    $('body').on('click', '.paginate_button', function () {
        var table_container = $(this).closest('.dataTables_wrapper');

        $('html, body').animate({
            scrollTop: table_container.closest(".dataTables_wrapper").offset().top - 80
        }, 'fast');
    });

    $('body').on('keyup', '.js_decimal', function () {
        var val = $(this).val().replace(',', '.');
        val = val.replace(' ', '.');
        if (isNaN(val)) {
            val = val.replace(/[^0-9\-\.]/g, '');
            if (val.split('.').length > 2) val = val.replace(/\.+$/, '');
        }
        $(this).val(val);
    });
    $('body').on('keyup', '.js_integer', function () {
        var val = $(this).val().replace(/\D+/g, '');
        $(this).val(val);
    });
    $('body').on('keyup', '.js_money', function () {
        var val = $(this).val();
        val = val.replace(/[^\d,]/g, "")
            .replace(/^(\d*\,)(.*)\,(.*)$/, '$1$2$3')
            .replace(/\,(\d{2})\d+/, ',$1')
            .replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        var first_char = val.charAt(0);
        if (first_char === ',') {
            $(this).val('0' + val);
        } else {
            $(this).val(val);
        }
    });

    //jQuery in automatico appende ?_={timestamp} su tutti i js caricati da jquery.plugin. Così non lo fa...
    $.ajaxSetup({ cache: true });
});