$(function () {
    'use strict';

    $('.js-tabs').each(function () {
        // La stessa tab NON deve mai apparire in una stessa pagina più di una
        // volta
        var tabId = $(this).attr('class');
        var tabs = $('.' + tabId).filter(':first');
        var tabToggles = $('> ul > li > a', tabs);

        tabToggles.on('click', function () {
            var clicked = $(this);
            var index = tabToggles.index(clicked);

            if (index > -1) {
                $.cookie('tab-' + tabId, index, {
                    path: '/'
                });
            }


        });

        tabToggles.on('shown.bs.tab', function (e) {
            //            $(window).trigger('resize');
            //            $.each($.fn.dataTable.tables(), function () {
            //                console.log($(this));
            //            });
            //            $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
            //            $.fn.dataTable.tables( { visible: true, api: true } ).draw(); //Prima c'era il destroy cghe metteva a posto le cose. Il problema è che col destroy toglie la riga col cerca e col visualizza n elementi...
            var tablenode = $.fn.dataTable.tables({
                visible: true,
                api: true
            }).table().node();
            if (typeof tablenode !== 'undefined') {
                tablenode
                    .style
                    .width = '';
            }
            //console.log($.fn.dataTable.tables());
            // $.each($.fn.dataTable.tables({
            //     visible: true,
            //     api: true
            // }), function() {
            //     if ($(this).parents('.active').length > 0) {
            //         $(this).columns.adjust().draw();
            //     }
            // });
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust().draw();

        });
    });





});