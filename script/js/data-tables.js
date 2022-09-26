'use strict';
function startDataTables() {
    $('.js_datatable:not(.dataTable)').each(function () {
        var lengthMenu =
            typeof $(this).attr('data-lengthmenu') === 'undefined'
                ? [
                      [10, 50, 100, 200, 500],
                      [10, 50, 100, 200, 500],
                  ]
                : JSON.parse($(this).attr('data-lengthmenu'));

        var bEnableOrder = typeof $(this).attr('data-prevent-order') === 'undefined';
        $(this).dataTable({
            bSort: bEnableOrder,
            aaSorting: [],
            stateSave: true,
            aLengthMenu: lengthMenu,
            oLanguage: {
                sUrl: base_url_scripts + 'script/datatable.transl.json',
            },
        });
        var id = '#' + $(this).attr('id') + '_wrapper';
        $(id + ' .dataTables_filter input').addClass('form-control input-small'); // modify table search input
        $(id + ' .dataTables_length select').addClass('form-control input-xsmall'); // modify table per page dropdown
        $(id + ' .dataTables_info').css({
            'margin-top': '20px',
            position: 'static',
        });
    });

    /*
     * Datatables
     */
    $('.js_datatable_slim:not(.dataTable)').each(function () {
        var oDataTable = $(this);

        var totalable = oDataTable.data('totalable');
        if (typeof totalable === 'undefined') {
            totalable = 0;
        }

        var bEnableOrder = typeof oDataTable.attr('data-prevent-order') === 'undefined';
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

        oDataTable
            .dataTable({
                bSort: bEnableOrder,

                aaSorting: [],
                aoColumns: aoColumns,

                iDisplayLength: 5,
                bFilter: false,
                stateSave: true,
                bLengthChange: false,
                oLanguage: { sUrl: base_url_scripts + 'script/datatable.transl.json' },
                footerCallback: function (row, data, start, end, display) {
                    if (totalable == 1) {
                        var api = this.api(),
                            data;
                        $(api.column(0).footer()).html('Totals:');
                        // converting to interger to find total
                        var floatVal = function (i) {
                            i = i.replace(/[^\d.-]/g, '');

                            return parseFloat(i);
                        };

                        api.columns().every(function () {
                            var values = this.data();
                            var footer = this.footer();
                            if ($(footer).data('totalable') == 1) {
                                var total = 0;
                                for (var i = 0; i < values.length; i++) {
                                    if ($.isValidSelector(values[i]) && $(values[i]).data('totalablevalue')) {
                                        total += $(values[i]).data('totalablevalue');
                                    } else {
                                        total += floatVal(values[i]);
                                    }
                                }

                                $(footer).html(total.toFixed(2));
                            }
                        });
                    }
                },
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
            });
    });
}
