function startDataTables() {
    
    $('.js_datatable:not(.dataTable)').each(function() {
        var bEnableOrder = typeof($(this).attr('data-prevent-order')) === 'undefined';
        $(this).dataTable({
            bSort: bEnableOrder,
            aaSorting: [],
            stateSave: true,
            aLengthMenu: [10, 50, 100, 200, 500,  1000, 'Tutti'],
            "oLanguage": {
                "sUrl": base_url_scripts + "script/datatable.transl.json"
            }
        });
        var id = '#' + $(this).attr('id') + '_wrapper';
        $(id + ' .dataTables_filter input').addClass("form-control input-small"); // modify table search input
        $(id + ' .dataTables_length select').addClass("form-control input-xsmall"); // modify table per page dropdown
        $(id + ' .dataTables_info').css({ "margin-top": '20px', position: 'static' });
    });
    
    
    
    /*
     * Datatables
     */
    $('.js_datatable_slim:not(.dataTable)').each(function() {
        
        var oDataTable = $(this);
        
        var bEnableOrder = typeof(oDataTable.attr('data-prevent-order')) === 'undefined';
        var aoColumns = [];
        $('> thead > tr > th', oDataTable).each(function() {
            var coldef = null;
            coldef = {
                bSortable: bEnableOrder && (typeof ($(this).attr('data-prevent-order')) === 'undefined')
            };

            aoColumns.push(coldef);
        });
        
        oDataTable.dataTable({
            bSort: bEnableOrder,
            
            aaSorting: [],
            aoColumns: aoColumns,
            iDisplayLength: 5,
            bFilter: false,
            stateSave: true,
            bLengthChange: false,
            oLanguage: { sUrl: base_url_scripts + "script/datatable.transl.json" }
        }).on('init', function(e) {
            var wrapper = e.target.parent;
            $('.dataTables_filter input', wrapper).addClass("form-control input-small"); // modify table search input
            $('.dataTables_length select', wrapper).addClass("form-control input-xsmall input-sm"); // modify table per page dropdown
            $('.dataTables_processing', wrapper).addClass("col-md-6"); // modify table per page dropdown
            
            $('.dataTables_info', wrapper).css({ "margin-top": '20px', position: 'static' });
            $('.dataTables_filter label, .dataTables_length label', wrapper).css('padding-bottom', 0).css('margin-bottom', 0);
            $('.dataTables_length', wrapper).parent().parent().height(0);
        });
    });
    
    
}

