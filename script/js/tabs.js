$(function () {
    'use strict';

    $('.js-tabs').each(function () {
        // La stessa tab NON deve mai apparire in una stessa pagina più di una
        // volta
        var tabId = $(this).data('tabid');


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

        tabToggles.on('shown.bs.tab', function (e) {
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

            $.fn.dataTable
                .tables({
                    visible: true,
                    api: true,
                })
                .columns.adjust()
                .draw();
        });
    });
});
