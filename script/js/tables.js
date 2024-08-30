'use strict';


function initTabelWithPars(grid, pars) {
    //console.log(pars);
    var table = null;
    var oDataTable = grid;
    var valueID = grid.attr('data-value-id');
    var getParameters = pars.get_pars; //Questu servono per portarsi dietro eventuali parametri get che non vengono passati al get_datatable_ajax (filtri o altro...)

    var where_append = pars.where_append;

    if (typeof where_append === 'undefined') {
        where_append = '';
    }

    var bEnableOrder = typeof grid.attr('data-prevent-order') === 'undefined';
    var defaultLimit = parseInt(grid.attr('data-default-limit'));



    var totalable = pars.totalable;
    if (typeof totalable === 'undefined') {
        totalable = 0;
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


    //Check if the table must be initialized with datatables
    if (pars.datatable) {
        var aoColumns = [];
        $('> thead > tr > th', oDataTable).each(function () {
            var coldef = null;
            coldef = {
                bSortable: bEnableOrder && typeof $(this).attr('data-prevent-order') === 'undefined',
            };

            if ($(this).data('totalable')) {
                coldef.className = 'dt-right';
            }

            aoColumns.push(coldef);
        });
        
        var gridId = grid.data('grid-id');
        var customId = 'DataTable_' + gridId;

        var datatableOptions = {
            stateSave: true, 
            stateLoadCallback: function (settings) {
                var stored = localStorage.getItem('DataTables_' + customId + '_' + location.pathname);
                return stored ? JSON.parse(stored) : null;
            },
            stateSaveCallback: function (settings, data) {
                localStorage.setItem('DataTables_' + customId + '_' + location.pathname, JSON.stringify(data));
            },
            bSort: bEnableOrder,
            aoColumns: aoColumns,
            aaSorting: [],
            aLengthMenu: [
                [5, 10, 15, 20, 25, 50, 100, 200, 500],
                [5, 10, 15, 20, 25, 50, 100, 200, 500],
            ],
            pageLength: defaultLimit,
            searching: (pars.searchable) ? true : false,
            oLanguage: {
                sUrl: base_url_scripts + 'script/dt_translations/datatable.' + lang_short_code + '.json',
            },
            drawCallback: function (settings) {
                if (pars.ajax) {
                    // This is a hot-fix for situations where you filter a table that has N pages,
                    // then change the filter by reducing the number of pages, the table init loops,
                    // this fix checks that if the offset is greater than total records,
                    // then does a page reset and reinitializes the table
                    if (settings._iDisplayStart > settings._iRecordsTotal) {
                        grid.fnPageChange(0);
                        grid.fnDestroy();
                        grid.fnPageChange(0);

                        initTabelWithPars(grid, pars);
                        initComponents(grid);

                        return;
                    }
                    initComponents(grid);
                }
            },
            footerCallback: function (row, data, start, end, display) {
                if (totalable == 1) {
                    var api = this.api(),
                        data;
                    $(api.column(0).footer()).html('Totals:');
                    // converting to interger to find total
                    var floatVal = function (i) {
                        i = i.toString();
                        i = i.replace(/[^\d.-]/g, '');
                        if (i != '') {
                            return parseFloat(i);
                        } else {
                            return 0;
                        }
                    };

                    api.columns().every(function () {
                        var values = this.data();
                        var footer = this.footer();

                        if ($(footer).data('totalable') == 1) {
                            var total = 0;
                            for (var i = 0; i < values.length; i++) {
                                if ($.isValidSelector(values[i]) && $(values[i]).data('totalablevalue') !== null && typeof $(values[i]).data('totalablevalue') !== 'undefined') {
                                    total += floatVal($(values[i]).data('totalablevalue'));
                                } else {
                                    total += floatVal(values[i]);
                                }
                            }

                            var totalFixed = total.toFixed(2);
                            var splitted = totalFixed.split('.');
                            var decimals = splitted[1];
                            var totalString = splitted[0].toString();
                            var totalThousandDot = totalString.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                            var totalHtml = totalThousandDot + "," + decimals;

                            $(footer).html(totalHtml);
                        }
                    });
                }
            },
            
        };

        if (pars.ajax) {
            var appendOptions = {
                bRetrieve: true,
                bProcessing: true,
                sServerMethod: 'POST',
                bServerSide: true,
                sAjaxSource: base_url + 'get_ajax/get_datatable_ajax/' + oDataTable.data('grid-id') + '/' + valueID + '?' + getParameters + '&where_append=' + where_append,
                fnServerParams: function (aoData) {
                    aoData.push({ name: token_name, value: token_hash });
                },
                fnServerData: function (sSource, aoData, fnCallback) {



                    $.ajax({
                        dataType: 'json',
                        type: 'POST',
                        url: sSource,
                        data: aoData,
                        success: function (response) {
                            //debugger
                            if (response.profiler) {
                                if (confirm('Do you want to see the profiler of grid id:' + oDataTable.data('grid-id') + '?')) {
                                    $('#codeigniter-profiler').html($(response.profiler).contents().not('script,style'));
                                }
                            }

                            // Chiama la funzione di callback di DataTables per processare la risposta
                            fnCallback(response);
                        },
                        error: function (request, error) {
                            //console.log(message);
                            if (typeof request.responseText !== 'undefined') {
                                $('.callout.callout-warning').remove();
                                $('.content-header:first').append('<div class="callout callout-warning"><h4>Problem occurred</h4><p>A component of this page seems to be corrupted. Please check table \'' + oDataTable.data('grid-id') + '\'.</p><a href="#" onclick="javascript:$(\'.js_error_code\').toggle();">Show/hide error</a><code class="js_error_code" style="display:none;">' + request.responseText + '</code></div>');
                            }
                        },
                    });
                },
            };
        } else {
            var appendOptions = {};
        }

        var datatable = oDataTable
            .on('error.dt', function (e, settings, techNote, message) {

                if (typeof message !== 'undefined') {
                    $('.content-header').append('<div class="callout callout-warning"><h4>Problem occurred</h4><p>A component of this page seems to be corrupted. Please check table \'' + oDataTable.data('grid-id') + "'.</p><code>" + message + '</code></div>');
                }
            })
            .on('init', function (e) {
                var wrapper = e.target.parent;
                $('.dataTables_filter input', wrapper).addClass('form-control input-small'); // modify table search input
                $('.dataTables_length select', wrapper).addClass('form-control input-xsmall input-sm'); // modify table per page dropdown
                $('.dataTables_processing', wrapper).addClass('col-md-6'); // modify table per page dropdown

                $('.dataTables_info', wrapper).css({
                    'margin-top': '20px',
                    position: 'static',
                });
                $('.dataTables_filter label, .dataTables_length label', wrapper).css('padding-bottom', 0).css('margin-bottom', 0);
                $('.dataTables_length', wrapper).parent().parent().height(0);
            })
            .dataTable({ ...datatableOptions, ...appendOptions });

        table = datatable;
    } else {
        table = grid;
        //console.log('Gestire tabelle non datatable');
    }

    if (pars.inline) {

        var dtInline = new CrmNewInlineTable(table);
        dtInline.registerEvents();
    }

    return table;

}

/** Init ajax datatables **/
function initTables(container) {
    $('.js_table:visible', container).each(function () {
        var grid = $(this);
        if (grid.data('tableInitialized') != true) {
            grid.data('tableInitialized', true);
            var gridID = grid.attr('data-grid-id');


            //Get parameters
            var gridParameters = grid.data();

            initTabelWithPars(grid, gridParameters);
        }
    });


}

$.fn.dataTable.ext.errMode = 'none';
