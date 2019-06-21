function CrmNewInlineTable(grid) {
    this.grid = grid;
}

CrmNewInlineTable.prototype.getDatatableHandler = function () {
    return this.grid.dataTable();
};
CrmNewInlineTable.prototype.getEntityName = function () {
    return this.grid.data('entity');
};
CrmNewInlineTable.prototype.createRow = function () {
    // Devo sapere quante colonne ho per prima cosa
    var sEntityName = this.grid.attr('data-entity');
    var jqThs = $('tr th', this.grid);
    var datatable = this.getDatatableHandler();
    var tr = $('<tr></tr>');
    var form_container = $('.js_inline_hidden_form_container[grid_id="' + this.grid.data('grid-id') + '"]');
    var form = $('form', form_container);


    //Inserisco un nuovo TR con i vari TD
    jqThs.each(function () {
        var name = $(this).attr('data-name');
        console.log(name);
        if (name == '_foo') {
            tr.append($('<td></td>'));
        } else if (typeof name == 'undefined') { //Vuol dire che sono nella colonna action o in un eval
            //tr.append($('<td></td>'));
        } else {
            //Trovo il campo tra quelli del form
            var fields = $('[name*="' + name + '"]', form);
            console.log(name);
            if (fields.length == 0) { //Vuol dire che nel form manca un campo che invece c'è come colonna nella grid
                //console.log('TODO: autogenerare un campo in base al tipo di field? Per ora semplice input text...');
                tr.append($('<td><input class="form-control" type="text" readonly name="' + name + '" placeholder="' + $(this).html() + '" /></td>'));
            } else {
                //Nel caso di checkbox il campo potrebbe esserci più volte (per ovviare al problema che le checkbox non vengono inviate se non checkate, c'è un trick con un campo hidden che forza sempre l'invio del chk
                //In questo caso li stampo entrambi...
                var field_html = '';
                //console.log(fields.get());
                for (var i in fields.get()) {
                    var field = $(fields[i]);
                    
                    var cloned_field = field.clone();
                    //cloned_field.attr('placeholder', name);
                    cloned_field.attr('placeholder', $(this).html());

                    cloned_field.removeClass('select2-hidden-accessible');
                    field_html += cloned_field.prop('outerHTML');
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
        $('td',tr).last().append($(this).clone());
    });

    $('tbody', this.grid).append(tr);

};

CrmNewInlineTable.prototype.editRow = function (tr, id) {
    var nRow = tr[0];
    var datatable = this.getDatatableHandler();
    var aData = datatable.fnGetData(nRow);
    var entityName = this.getEntityName();
    //Step 1: clono il form
    console.log('clono il form');
    this.createRow();
    
    var row_with_form = $('tr:last', this.grid);
    
    //Step 2: popolo i valori del form clonato in base ai valori dell'attuale riga (faccio un ajax per avere i dati completi, più sicuro)
    $.ajax(base_url + 'get_ajax/getJsonRecord/' + entityName + '/' + id, {
        dataType: "json",
        success: function (data) {
            $(':input', row_with_form).each(function () {
                //Se il name contiene [] allora è una multiselect
                if ($(this).attr('name').endsWith('[]')) {
                    var field_name = $(this).attr('name').substring(0, $(this).attr('name').length-2);
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
                            $(this).val(data.data[$(this).attr('name')]).trigger('change');
                        }                        
                    }
                    
                }
                
            });
            
            //Forzo l'elemento id nel form per fare in modo che poi il sistema capisca che è una edit al saveRow... (vd sotto)
            $('td',row_with_form).first().append('<input type="hidden" name="'+entityName+'_id" value="'+data.data[entityName+'_id']+'" />');
        }
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
        }
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

    // Save data
    $.post(base_url + 'db_ajax/datatable_inline_edit/' + sEntityName + '/' + id, data)
            .success(function () {
                
                datatable.fnDraw();
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
    $('.js_datatable_inline_add[data-grid-id="' + gridID + '"]').on('click', function (e) {
        e.preventDefault();

        inlineTable.createRow();
    });
    
    // Edit record
    this.grid.on('click', '.js_edit', function (e) {
        e.preventDefault();
        
        /* Get the row as a parent of the link that was clicked on */
        var button = $(this);
        var tr = button.parents('tr');
        //var nRow = button.parents('tr')[0];
        var id = button.data('id');
        inlineTable.editRow(tr, id);
        
    });
    
    // Delete record
    this.grid.on('click', '.js_delete', function (e) {
        e.preventDefault();
        
        if (confirm("Are you sure?") == false) {
            return;
        }
        var id = $(this).data('id');
        var nRow = $(this).parents('tr')[0];
        inlineTable.deleteRow(nRow, id);
    });
};

function initTable(gridID) {
    var oDataTable = $('#grid_' + gridID);
    var valueID = oDataTable.attr('data-value-id');
    var getParameters = oDataTable.data('get_pars'); //Questu servono per portarsi dietro eventuali parametri get che non vengono passati al get_datatable_ajax (filtri o altro...)
    var bEnableOrder = typeof (oDataTable.attr('data-prevent-order')) === 'undefined';
    var defaultLimit = parseInt(oDataTable.attr('default-limit'));
    
    var aoColumns = [];
    $('> thead > tr > th', oDataTable).each(function () {
        var coldef = null;
        coldef = {
            bSortable: bEnableOrder && (typeof ($(this).attr('data-prevent-order')) === 'undefined')
        };

        aoColumns.push(coldef);
    });



    var datatable = oDataTable.dataTable({
        stateSave: true,
        bSort: bEnableOrder,
        aoColumns: aoColumns,
        /*scrollX: true,*/
        aaSorting: [],
        bRetrieve: true,
        bProcessing: true,
        sServerMethod: "POST",
        bServerSide: true,
        sAjaxSource: base_url + 'get_ajax/get_datatable_ajax/' + gridID + '/' + valueID + '?' + getParameters,
        aLengthMenu: [10, 50, 100, 200, 500, 'Tutti'],
        iDisplayLength: defaultLimit,
        //bLengthChange: false,
        oLanguage: {
            sUrl: base_url_scripts + "script/datatable.transl.json"
        }
    });

    return datatable;
}

function startNewDatatableInline() {
    $('.js_datatable_new_inline').each(function () {
        var grid = $(this);
        if (!grid.data('inline_initializated')) {
            grid.data('inline_initializated', true);

            initTable(grid.attr('data-grid-id'));

            var dtInline = new CrmNewInlineTable(grid);
            dtInline.registerEvents();
        }
    });


}