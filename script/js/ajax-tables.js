/**
 * Crea nuovo oggetto inline table
 * 
 * @param {jQuery} grid
 * @returns {CrmInlineTable}
 */
function CrmInlineTable(grid) {
    this.grid = grid;
    this.nEditing = null;
}

/**
 * Get the datatable object
 * @returns {jQuery}
 */
CrmInlineTable.prototype.getDatatableHandler = function () {
    return this.grid.dataTable();
};

/**
 * Get the name of the entity
 * @returns {String}
 */
CrmInlineTable.prototype.getEntityName = function () {
    return this.grid.data('entity');
};

/**
 * Register event handlers
 * @returns {null}
 */
CrmInlineTable.prototype.registerEvents = function () {

    var inlineTable = this;
    var gridID = this.grid.data('grid-id');

    // Edit record
    this.grid.on('click', '.js_edit', function (e) {
        e.preventDefault();

        /* Get the row as a parent of the link that was clicked on */
        var button = $(this);
        var nRow = button.parents('tr')[0];

        if (inlineTable.nEditing !== null && inlineTable.nEditing != nRow) {
            // Currently editing - but not this row - restore the old before
            // continuing to edit mode
            inlineTable.restoreRow();
            inlineTable.editRow(nRow);

        } else if (inlineTable.nEditing == nRow && button.hasClass('js_save')) {
            // Editing this row and want to save it
            inlineTable.saveRow();

        } else {
            // No edit in progress - let's start one
            inlineTable.editRow(nRow);

        }
    });

    // Cancel edit mode
    this.grid.on('click', '.js_cancel', function (e) {
        e.preventDefault();
        inlineTable.restoreRow();
    });

    // Delete record
    this.grid.on('click', '.js_delete', function (e) {
        e.preventDefault();

        if (confirm("Vuoi davvero eliminare la riga?") == false) {
            return;
        }

        var nRow = $(this).parents('tr')[0];
        inlineTable.deleteRow(nRow);
    });

    // Create empty record
    $('.js_datatable_inline_add[data-grid-id="' + gridID + '"]').on('click', function (e) {
        e.preventDefault();
        inlineTable.createRow();
    });
};

/**
 * Ripristina una riga in modalità di modifica annullando tutte le modifche
 * effettuate
 * 
 * @returns {null}
 */
CrmInlineTable.prototype.restoreRow = function () {

    var datatable = this.getDatatableHandler();
    var nRow = this.nEditing;

    var aData = datatable.fnGetData(nRow);
    var jqTds = $('>td', nRow);
    for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
        datatable.fnUpdate(aData[i], nRow, i, false);
    }
    datatable.fnDraw();
    this.nEditing = null;
};


/**
 * Inizia la modalità di modifica su una riga
 * 
 * @param {jQuery} nRow
 * @returns {null}
 */
CrmInlineTable.prototype.editRow = function (nRow) {

    var datatable = this.getDatatableHandler();

    var aData = datatable.fnGetData(nRow);
    var max = aData.length;
    var jqTds = $('>td', nRow);
    var jqThs = $('tr th', datatable);

    for (var i = 0; i < max - 2; i++) {
        jqTds[i].innerHTML = '<input type="text" class="form-control input-small" name="' + $(jqThs[i]).attr('data-name') + '" value="' + (aData[i] ? aData[i] : '') + '">';
    }

    jqTds[max - 2].innerHTML = '<a class="js_edit js_save" href="">Salva</a>';
    jqTds[max - 1].innerHTML = '<a class="js_cancel" href="">Annulla</a>';

    this.nEditing = nRow;
};


/**
 * Salva la riga corrente su database
 * 
 * @returns {null}
 */
CrmInlineTable.prototype.saveRow = function () {

    var datatable = this.getDatatableHandler();

    var jqInputs = $('input', this.nEditing);
    var sEntityName = datatable.attr('data-entity');

    var data = {};
    var id = '';
    jqInputs.each(function () {

        var input = $(this);
        var name = input.attr('name');

        if (name === sEntityName + '_id') {
            id = input.val();
        } else {
            data[name] = input.val();
        }
    });

    // Save data
    $.post(base_url + 'db_ajax/datatable_inline_edit/' + sEntityName + '/' + id, data).success(function () {
        var max = jqInputs.size();
        for (var i = 0; i < max; i++) {
            datatable.fnUpdate(jqInputs[i].value, this.nEditing, i, false);
        }
        datatable.fnUpdate('<a href="#" class="js_edit">Modifica</a>', this.nEditing, max - 2, false);
        datatable.fnUpdate('<a class="js_cancel" href="#">Elimina</a>', this.nEditing, max - 1, false);
        datatable.fnDraw();
    });

    this.nEditing = null;
};

/**
 * Crea una record nuovo e inseriscilo nella tabella
 * @returns {undefined}
 */
CrmInlineTable.prototype.createRow = function () {
    // Devo sapere quante colonne ho per prima cosa
    var sEntityName = this.grid.attr('data-entity');
    var jqThs = $('tr th', this.grid);
    var datatable = this.getDatatableHandler();

    // Creo dei dati vuoti da inserire
    var data = {};
    jqThs.each(function () {
        var name = $(this).attr('data-name');
        if (typeof (name) !== 'undefined' && name && name !== sEntityName + '_id') {
            data[name] = '';
        }
    });

    $.ajax(base_url + 'db_ajax/datatable_inline_insert/' + sEntityName, {
        data: data,
        type: 'POST',
        success: function (msg) {
            //var id = parseInt(msg);
            datatable.fnPageChange('last');
        }
    });

};

/**
 * Rimuovi una riga 
 * 
 * @param {jQuery} nRow
 * @returns {undefined}
 */
CrmInlineTable.prototype.deleteRow = function (nRow) {

    var datatable = this.getDatatableHandler();
    var aData = datatable.fnGetData(nRow);

    // TODO: ora si suppone che l'id sia sulla prima colonna... da rivedere per
    // generalizzare queste tabelle
    $.ajax(base_url + 'db_ajax/generic_delete/' + this.getEntityName() + '/' + aData[0], {
        success: function () {
            datatable.fnDeleteRow(nRow);
        }
    });
};


//MP - 20190206 - Nuove inline table con form



function initTableAjax(grid) {

    var oDataTable = grid;//$('#grid_' + gridID);
    var valueID = oDataTable.attr('data-value-id');
    var getParameters = oDataTable.data('get_pars'); //Questu servono per portarsi dietro eventuali parametri get che non vengono passati al get_datatable_ajax (filtri o altro...)

    var where_append = oDataTable.data('where_append');
    if (typeof (where_append) === 'undefined') {
        where_append = '';
    }

    var bEnableOrder = typeof (oDataTable.attr('data-prevent-order')) === 'undefined';
    var defaultLimit = parseInt(oDataTable.attr('default-limit'));
    //console.log('limite2:' + defaultLimit);
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
        //sScrollX: "100%",

        aoColumns: aoColumns,
        aaSorting: [],
        bRetrieve: true,
        bProcessing: true,
        sServerMethod: "POST",
        bServerSide: true,
        sAjaxSource: base_url + 'get_ajax/get_datatable_ajax/' + oDataTable.data('grid-id') + '/' + valueID + '?' + getParameters + '&where_append=' + where_append,
        aLengthMenu: [10, 50, 100, 200, 500, 1000, 'Tutti'],
        iDisplayLength: defaultLimit,
        //bLengthChange: false,
        oLanguage: {
            sUrl: base_url_scripts + "script/datatable.transl.json"
        }
    });
    //console.log(datatable);
    return datatable;
}


/** Init ajax datatables **/
function startAjaxTables() {

    $('.js_ajax_datatable:not(.dataTable)').each(function () {
        var gridID = $(this).attr('data-grid-id');
        var grid = $(this);
        initTableAjax(grid).on('init', function (e) {
            var wrapper = e.target.parent;
            $('.dataTables_filter input', wrapper).addClass("form-control input-small"); // modify table search input
            $('.dataTables_length select', wrapper).addClass("form-control input-xsmall input-sm"); // modify table per page dropdown
            $('.dataTables_processing', wrapper).addClass("col-md-6"); // modify table per page dropdown

            $('.dataTables_info', wrapper).css({ "margin-top": '20px', position: 'static' });
            $('.dataTables_filter label, .dataTables_length label', wrapper).css('padding-bottom', 0).css('margin-bottom', 0);
            $('.dataTables_length', wrapper).parent().parent().height(0);
        });
    });


    $('.js_datatable_inline').each(function () {
        var grid = $(this);
        initTable(grid);

        var dtInline = new CrmInlineTable(grid);
        dtInline.registerEvents();
    });


}

