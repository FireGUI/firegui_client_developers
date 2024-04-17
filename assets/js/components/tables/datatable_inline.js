function CrmNewInlineTable(grid) {
    this.grid = grid;
}
try {
    var token = JSON.parse(atob(oDataTable.data('csrf')));
    var token_name = token.name;
    var token_hash = token.hash;
} catch (e) {
    var token = JSON.parse(atob($('body').data('csrf')));
    var token_name = token.name;
    var token_hash = token.hash;
}
CrmNewInlineTable.prototype.getDatatableHandler = function () {
    return this.grid.dataTable();
};
CrmNewInlineTable.prototype.getEntityName = function () {
    return this.grid.data('entity');
};
CrmNewInlineTable.prototype.createRow = function (id) {


    // Devo sapere quante colonne ho per prima cosa
    var sEntityName = this.grid.attr('data-entity');

    var parent_field = this.grid.attr('data-parent_field');
    var parent_id = this.grid.attr('data-parent_id');

    var jqThs = $('> thead > tr > th', this.grid);
    //var datatable = this.getDatatableHandler();

    var tr = $('<tr data-id="' + id + '"></tr>');

    var form_container = $('.js_inline_hidden_form_container[grid_id="' + this.grid.data('grid-id') + '"]').first();

    var form = $('form', form_container);

    //Inserisco un nuovo TR con i vari TD
    jqThs.each(function () {
        var name = $(this).attr('data-name');

        if (name == '_foo') {
            tr.append($('<td></td>'));
        } else if (typeof name == 'undefined') {
            //Vuol dire che sono nella colonna action o in un eval o in un checkbox
            tr.append($('<td></td>'));
        } else {
            //Trovo il campo tra quelli del form
            var fields = $('[name*="' + name + '"]', form);

            if (fields.length == 0) {
                //Vuol dire che nel form manca un campo che invece c'è come colonna nella grid
                tr.append($('<td><input class="form-control" type="text" readonly name="' + name + '" placeholder="' + $(this).html() + '" /></td>'));
            } else {
                //Nel caso di checkbox il campo potrebbe esserci più volte (per ovviare al problema che le checkbox non vengono inviate se non checkate, c'è un trick con un campo hidden che forza sempre l'invio del chk
                //In questo caso li stampo entrambi...
                var field_html = '';

                for (var i in fields.get()) {
                    var field = $(fields[i]);

                    //Prendo anche il container (per le date è il container che mi dice se devo stamparla come datepicker o meno...
                    var field_container = field.parent();

                    var cloned_field = field.clone();
                    cloned_field.removeClass('select2-hidden-accessible');
                    cloned_field.removeAttr('aria-hidden');
                    cloned_field.removeAttr('data-select2-id');
                    cloned_field.find('span').remove();
                    cloned_field.attr('placeholder', $(this).html());

                    if (field_container.hasClass('js_form_datetimepicker') || field_container.hasClass('js_form_datepicker')) {
                        field_html += field_container.prop('outerHTML');
                    } else {
                        field_html += cloned_field.prop('outerHTML');
                    }
                }

                tr.append($('<td>' + field_html + '</td>'));
            }
        }
    });

    //Aggiungo l'action per annullare e per salvare
    var button_save = $('<a class="btn btn-success _btn-xs pull-left js_save_row">Save</a>');
    tr.append($('<td>' + button_save.prop('outerHTML') + '</td>'));

    //Aggiungo comunque gli hidden
    $('[type="hidden"]', form).each(function () {
        if ($(this).attr('name') == parent_field) {
            $(this).val(parent_id);
        }
        var cloned = $(this).clone();
        cloned.val($(this).val());

        $('td', tr).last().append(cloned);
    });

    $('> tbody', this.grid).append(tr);

    initComponents(tr, true);
};

CrmNewInlineTable.prototype.editRow = function (tr, id) {

    var nRow = tr[0];
    var datatable = this.getDatatableHandler();
    var aData = datatable.fnGetData(nRow);
    var entityName = this.getEntityName();
    //Step 1: clono il form

    this.createRow(id);

    var row_with_form = $('tbody tr:last', this.grid);
    var form_container = $('.js_inline_hidden_form_container[grid_id="' + this.grid.data('grid-id') + '"]').first();

    var form = $('form', form_container);
    //Step 2: popolo i valori del form clonato in base ai valori dell'attuale riga (faccio un ajax per avere i dati completi, più sicuro)
    $.ajax(base_url + 'get_ajax/getJsonRecord/' + entityName + '/' + id, {
        dataType: 'json',
        success: function (data) {
            var selects_vals = [];
            $(':input', row_with_form).each(function () {
                //Se il name contiene [] allora è una multiselect
                if ($(this).attr('name') && $(this).attr('name').endsWith('[]')) {
                    var field_name = $(this)
                        .attr('name')
                        .substring(0, $(this).attr('name').length - 2);
                    if (data.data[field_name]) {
                        $(this).val(Object.keys(data.data[field_name])).trigger('change');
                    }
                } else {
                    if ($(this).is(':checkbox')) {
                        if ($(this).val() == data.data[$(this).attr('name')]) {
                            $(this).attr('checked', 'checked').trigger('change');
                        }
                    } else {
                        if ($(this).data('notmodifiable') != 1) {
                            //Mi salvo i valori delle input select per risettarli dopo (qualora un trigger change dovesse scatenare il reload dei valori di una tendina con quel field_ref)
                            if ($(this).is('select')) {
                                var valore = data.data[$(this).attr('name')];
                                var current_sel = { name: $(this).attr('name'), val: valore };
                                selects_vals.push(current_sel);
                                $(this).val(data.data[$(this).attr('name')]); //Se sono select, triggero il change solo alla fine di tutto (serve per essere sicuri di avere almeno in un preciso istante tutte le tendine col val corretto)
                                //Nel dubbio assegno anche l'attributo selected...
                                $('option[value=' + valore + ']', $(this)).attr('selected', 'selected');
                            } else {
                                //var form_field_name = $(this).attr('name');

                                if (data.data[$(this).attr('name')]) {
                                    $(this).val(data.data[$(this).attr('name')]).trigger('change');
                                }
                            }
                        }
                    }
                }
            });
            //Adesso è il momento di triggerare il change sulle select
            for (i in selects_vals) {
                var name = selects_vals[i].name;
                $('[name="' + name + '"]', row_with_form).trigger('change');
            }
            //Non mi accontento! Dopo il change, nel dubbio risetto i valori cachati precedentemente. Questo per essere sicuro abbia selezionato effettivamente i valori giusti.
            for (i in selects_vals) {
                var select_data = selects_vals[i];
                var name = select_data.name;
                var val = select_data.val;
                $('[name="' + name + '"]', row_with_form).val(val); //Volutamente non triggero il change in questo caso altrimenti rischio di svuotare nuovamente altre select
            }

            //Forzo l'elemento id nel form per fare in modo che poi il sistema capisca che è una edit al saveRow... (vd sotto)
            $('td', row_with_form)
                .first()
                .append('<input type="hidden" name="' + entityName + '_id" value="' + data.data[entityName + '_id'] + '" />');

            //Hidden fields must be always cloned (ex.: csrf field...)
            $('[type="hidden"]', form).each(function () {
                if ($('[name="' + $(this).attr('name') + '"]', row_with_form).length == 0) {
                    $('td', row_with_form).first().append($(this).clone());
                }

            });
        },
    });

    //Step 3: posiziono e faccio apparire il form con la stessa logica del nuova riga, ovvero con i campi in corrispondenza della colonna corretta
    tr.replaceWith(row_with_form);
};

CrmNewInlineTable.prototype.deleteRow = function (nRow, id) {
    var datatable = this.getDatatableHandler();
    var aData = datatable.fnGetData(nRow);

    $.ajax(base_url + 'db_ajax/generic_delete/' + this.getEntityName() + '/' + id, {
        success: function () {
            datatable.fnDeleteRow(nRow);
        },
    });
};

/**
 * Salva la riga corrente su database
 *
 * @returns {null}
 */
CrmNewInlineTable.prototype.saveRow = function (button) {
    var row = button.closest('tr');
    var datatable = this.getDatatableHandler();

    var jqInputs = $(':input:not(:radio), :radio:checked', row);
    var sEntityName = datatable.attr('data-entity');

    var data = {};
    var id = '';

    jqInputs.each(function () {
        var input = $(this);
        var name = input.attr('name');

        if (name === sEntityName + '_id') {
            id = input.val();
        } else {
            if (input.is(':checkbox') && input.is(':checked')) {
                data[name] = input.val();
            } else {
                if (!(name in data)) {
                    data[name] = input.val();
                }
            }
        }
    });
    if (id) {
        append = '/1/' + id;
    } else {
        append = '';
    }
    // Save data
    var form_container = $('.js_inline_hidden_form_container[grid_id="' + this.grid.data('grid-id') + '"]').first();
    var form = $('form', form_container);
    //Perchè non postare direttamente alla save_form? Avremmo tutto potenzialmente...

    var grid = this.grid;

    $.post(form.attr('action') + append, data)
        .success(function () {
            if (grid.ajax) {
                row.remove();
                datatable.fnDraw();
            } else {
                var lb_id = grid.closest('.layout_box').data('layout-box');
                if (lb_id) {
                    refreshLayoutBox(lb_id, grid.data('value-id'));
                } else {
                    refreshVisibleAjaxGrids(grid);
                }

            }

        })
        .error(function () {
            console.log('ERRORE...');
        });
};
CrmNewInlineTable.prototype.registerEvents = function () {
    var inlineTable = this;
    var gridID = this.grid.data('grid-id');

    // Edit record
    this.grid.on('click', '.js_save_row', function (e) {
        inlineTable.saveRow($(this));
    });

    // Create empty record
    // this.grid.on('click', function (e) {
    //     e.preventDefault();

    //     inlineTable.createRow(0);
    // });

    // Edit record
    this.grid.on('click', '.js_edit', function (e) {
        e.preventDefault();



        /* Get the row as a parent of the link that was clicked on */
        var button = $(this);
        var tr = button.parents('tr:first');
        var id = button.data('id');

        inlineTable.editRow(tr, id);
    });

    // Delete record
    this.grid.on('click', '.js_delete', function (e) {
        e.preventDefault();

        if (confirm('Are you sure?') == false) {
            return;
        }
        var id = $(this).data('id');
        var nRow = $(this).parents('tr')[0];
        inlineTable.deleteRow(nRow, id);
    });

    // Create empty record
    $('.js_datatable_inline_add[data-grid-id="' + gridID + '"]').on('click', function (e) {

        e.preventDefault();
        inlineTable.createRow();
    });
};

function initTable(grid) {

    grid.data('ajaxTableInitialized', true);
    var oDataTable = grid;
    var valueID = oDataTable.attr('data-value-id');
    var getParameters = oDataTable.data('get_pars'); //Questu servono per portarsi dietro eventuali parametri get che non vengono passati al get_datatable_ajax (filtri o altro...)

    var where_append = oDataTable.data('where_append');

    if (typeof where_append === 'undefined') {
        where_append = '';
    }
    var getParameters = oDataTable.data('get_pars');
    if (typeof getParameters === 'undefined') {
        getParameters = '';
    }
    var no_server_side = oDataTable.data('no-server-side');
    if (typeof no_server_side === 'undefined') {
        no_server_side = false;
    } else {
        no_server_side = true;
    }
    var no_ordering = oDataTable.data('no-ordering');
    if (typeof no_ordering === 'undefined') {
        no_ordering = false;
    } else {
        no_ordering = true;
    }
    var bEnableOrder = typeof oDataTable.attr('data-prevent-order') === 'undefined';
    var defaultLimit = parseInt(oDataTable.attr('default-limit'));

    var aoColumns = [];
    $('> thead > tr > th', oDataTable).each(function () {
        var coldef = null;
        coldef = {
            bSortable: bEnableOrder && typeof $(this).attr('data-prevent-order') === 'undefined',
            defaultContent: '',
        };

        aoColumns.push(coldef);
    });

    var lengthMenu =
        typeof oDataTable.attr('data-lengthmenu') === 'undefined' ? [
            [10, 50, 100, 200, 500, -1],
            [10, 50, 100, 200, 500, 'Tutti'],
        ] :
            JSON.parse(oDataTable.attr('data-lengthmenu'));

    var datatable = oDataTable.dataTable({
        stateSave: true,
        bSort: bEnableOrder,
        aaSorting: [],
        ordering: !no_ordering,
        pageLength: defaultLimit,
        bRetrieve: !no_server_side,
        bProcessing: !no_server_side,
        sServerMethod: 'POST',
        bServerSide: !no_server_side,
        sAjaxSource: no_server_side ? null : base_url + 'get_ajax/get_datatable_ajax/' + oDataTable.data('grid-id') + '/' + valueID + '?' + getParameters + '&where_append=' + where_append,
        aLengthMenu: lengthMenu,
        iDisplayLength: defaultLimit,
        autoWidth: false,
        oLanguage: {
            sUrl: base_url_scripts + 'script/datatable.transl.json',
        },
        fnServerParams: function (aoData) {
            aoData.push({ name: token_name, value: token_hash });
            //console.log(aoData);
        },
    });

    return datatable;
}

function startNewDatatableInline(container) {
    $('.js_datatable_new_inline:not(.disabled)', container).each(function () {
        var grid = $(this);
        if (!grid.data('inline_initializated')) {
            grid.data('inline_initializated', true);

            initTable(grid);
            var dtInline = new CrmNewInlineTable(grid);
            dtInline.registerEvents();
        }
    });
}