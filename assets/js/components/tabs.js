function tabsInit(container) {
    $(function () {
        'use strict';

        $('.js-tabs', container).each(function () {
            // La stessa tab NON deve mai apparire in una stessa pagina più di una
            // volta

            var tabId = $(this).data('tabid');
            //console.log(tabId);


            var tabToggles = $('> ul > li > a', $(this));

            tabToggles.on('click', function () {
                var clicked = $(this);
                var index = tabToggles.index(clicked);

                if (index > -1) {
                    $.cookie('tab-' + tabId, index, {
                        path: '/',
                    });
                }
            });
            if (!$(this).data('tab-initialized')) {
                $(this).data('tab-initialized', true);
                tabToggles.on('shown.bs.tab', function (e) {
                    //alert(1);
                    var tablenode = $.fn.dataTable
                        .tables({
                            visible: true,
                            api: true,
                        })
                        .table()
                        .node();
                    if (typeof tablenode !== 'undefined') {
                        tablenode.style.width = '';
                    }

                    // $.fn.dataTable
                    //     .tables({
                    //         visible: true,
                    //         api: true,
                    //     })
                    //     .columns.adjust()
                    //     .draw();

                    //startAjaxTables();
                    // $.each($.fn.dataTable.tables(), function () {
                    //     if ($(this).parents('.tab-pane.active').length > 0) {
                    //         var parentTabContainer = $(this).parents('.tab-pane.active');
                    //         console.log($(this));
                    //         $(this).DataTable().ajax.reload();

                    //     }
                    // });
                    //console.log($(this).attr('href'));
                    initComponents($($(this).attr('href')));
                });
            }

        });
    });
}

