function restoreRow(oTable, nRow) {
    var aData = oTable.fnGetData(nRow);
    var jqTds = $('>td', nRow);
    for (var i = 0, iLen = jqTds.length; i < iLen; i++) {
        oTable.fnUpdate(aData[i], nRow, i, false);
    }

    oTable.fnDraw();
}

function editRow(oTable, nRow) {
    var aData = oTable.fnGetData(nRow);
    var max = aData.length;
    var jqTds = $('>td', nRow);
    var jqThs = $('tr th', oTable);
    
    
    for(var i=0; i<max-2; i++) {
        jqTds[i].innerHTML = '<input type="text" class="form-control input-small" name="'+$(jqThs[i]).attr('data-name')+'" value="' + aData[i] + '">';
    }
    
    jqTds[max-2].innerHTML = '<a class="js_edit js_save" href="">Salva</a>';
    jqTds[max-1].innerHTML = '<a class="js_cancel" href="">Annulla</a>';
}

function saveRow(oTable, nRow) {
    var jqInputs = $('input', nRow);
    var sEntityName = oTable.attr('data-entity');
    
    var data = {};
    var id = '';
    jqInputs.each(function() {
        var name = $(this).attr('name');
        var val = $(this).val();
        if(name === sEntityName+'_id') {
            id = val;
        } else {
            data[name] = val;
        }
    });
    
    // Save data
    $.post(base_url+'db_ajax/generic_edit/'+sEntityName+'/'+id, data).success(function() {
        var max = jqInputs.size();
        for(var i=0; i<max; i++) {
            oTable.fnUpdate(jqInputs[i].value, nRow, i, false);
        }
        oTable.fnUpdate('<a href="#" class="js_edit">Modifica</a>', nRow, max-2, false);
        oTable.fnUpdate('<a class="js_cancel" href="#">Elimina</a>', nRow, max-1, false);
        oTable.fnDraw();
    });
    
}

function cancelEditRow(oTable, nRow) {
    var jqInputs = $('input', nRow);
    var max = jqInputs.size();
    for(var i=0; i<max; i++) {
        oTable.fnUpdate(jqInputs[i].val(), nRow, i, false);
    }
    oTable.fnUpdate('<a href="#" class="js_edit">Modifica</a>', nRow, max-2, false);
    oTable.fnDraw();
}



function initTable(gridID) {
    var oDataTable = $('#grid_' + gridID);
    var valueID = oDataTable.attr('data-value-id');
    var bEnableOrder = typeof (oDataTable.attr('data-prevent-order')) === 'undefined';
    var datatable =  oDataTable.dataTable({
        bSort: bEnableOrder,
        bRetrieve: true,
        bProcessing: true,
        sServerMethod: "POST",
        bServerSide: true,
        sAjaxSource: base_url + 'get_ajax/get_datatable_ajax/' + gridID + '/' + valueID,
        aLengthMenu: [10, 50, 100, 200, 500, 'Tutti'],
        oLanguage: {
            sUrl: base_url_template + "script/datatable.transl.json"
        }
    });
    
    return datatable;
}

    
        
        
        
        
/** Init ajax datatables **/
function startAjaxTables() {
    $('.js_ajax_datatable:not(.dataTable)').each(function() {
        var gridID = $(this).attr('data-grid-id');
        initTable(gridID).on('init', function(e) {
            var wrapper = e.target.parent;
            $('.dataTables_filter input', wrapper).addClass("form-control input-small"); // modify table search input
            $('.dataTables_length select', wrapper).addClass("form-control input-xsmall input-sm"); // modify table per page dropdown
            $('.dataTables_processing', wrapper).addClass("col-md-6"); // modify table per page dropdown
            
            $('.dataTables_info', wrapper).css({ "margin-top": '20px', position: 'static' });
            $('.dataTables_filter label, .dataTables_length label', wrapper).css('padding-bottom', 0).css('margin-bottom', 0);
            $('.dataTables_length', wrapper).parent().parent().height(0);
        });
    });


    $('.js_datatable_inline').each(function() {
        var gridID = $(this).attr('data-grid-id');
        var oTable = initTable(gridID);
        var nEditing = null;
        
        
        
        /**
         * Edit button pressed
         */
        $(this).on('click', '.js_edit', function(e) {
            e.preventDefault();

            /* Get the row as a parent of the link that was clicked on */
            var nRow = $(this).parents('tr')[0];
            if (nEditing !== null && nEditing != nRow) {
                /* Currently editing - but not this row - restore the old before continuing to edit mode */
                restoreRow(oTable, nEditing);
                editRow(oTable, nRow);
                nEditing = nRow;
            } else if (nEditing == nRow && $(this).hasClass('js_save')) {
                /* Editing this row and want to save it */
                saveRow(oTable, nEditing);
                nEditing = null;
            } else {
                /* No edit in progress - let's start one */
                editRow(oTable, nRow);
                nEditing = nRow;
            }
        });

        
        
        
        /**
         * Cancel button pressed
         */
        $(this).on('click', '.js_cancel', function(e) {
            e.preventDefault();
            if ($(this).attr("data-mode") == "new") {
                var nRow = $(this).parents('tr')[0];
                oTable.fnDeleteRow(nRow);
            } else {
                restoreRow(oTable, nEditing);
                nEditing = null;
            }
        });


        
        
        
        /**
         * Delete button pressed
         */
        $(this).on('click', '.js_delete', function(e) {
            e.preventDefault();

            if (confirm("Vuoi davvero eliminare la riga?") == false) {
                return;
            }
            
            var sEntityName = oTable.attr('data-entity');
            var nRow = $(this).parents('tr')[0];
            var aData = oTable.fnGetData(nRow);
            
            // TODO: ora si suppone che l'id sia sulla prima colonna...
            $.ajax(base_url+'db_ajax/generic_delete/'+sEntityName+'/'+aData[0], {
                success: function() {
                    oTable.fnDeleteRow(nRow);
                }
            });
        });




        
        
        
        /**
         * Create button pressed
         */
        $('.js_datatable_inline_add[data-grid-id="'+gridID+'"]').on('click', function(e) {
            e.preventDefault();
            
            // Devo sapere quante colonne ho per prima cosa
            var sEntityName = oTable.attr('data-entity');
            var jqThs = $('tr th', oTable);
    
            // Creo dei dati vuoti da inserire
            var data = {};
            jqThs.each(function() {
                var name = $(this).attr('data-name');
                if(typeof(name) !== 'undefined' && name && name !== sEntityName+'_id') {
                    data[name] = '';
                }
            });
            
            $.ajax(base_url+'db_ajax/generic_insert/'+sEntityName, {
                data: data,
                type: 'POST',
                success: function(msg) {
                    var id = parseInt(msg);
                    oTable.fnPageChange('last');
                }
            });
        });
    });
}
