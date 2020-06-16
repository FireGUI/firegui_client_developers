var token = JSON.parse(atob($('body').data('csrf')));
var token_name = token.name;
var token_hash = token.hash;

var Planner = function () {
    "use strict";

    return {
        container: $('#planner-container'),

        setLeadcolumn: function (leadId, newcolumn) {
            $.post(base_url + 'firecrm/main/editLead/' + leadId, {
                leads_status: newcolumn,
                [token_name]: token_hash
            }, function (output) {
                var status = parseInt(output.status);
                if (status > 0) {
                    alert(output.message);
                }
            }, 'json');
        },

        init: function () {

            $('#planner-container a[data-toggle=tab]').on('click', function () {
                $.cookie('planner-selected', $(this).attr('href'));
            });

            var toOpen = $.cookie('planner-selected');
            var openFirstOne = !toOpen;
            if (toOpen) {
                var toggle = $('#planner-container a[data-toggle=tab][href=' + toOpen + ']');
                if (toggle.size() > 0) {
                    toggle.click();
                } else {
                    openFirstOne = true;
                }
            }

            if (openFirstOne) {
                $('#planner-container a[data-toggle=tab]').filter(':first').click();
                $('#planner-container > [role=tabpanel] .tab-content > .tab-pane').filter(':first').addClass('active in');
            }


            /** Start sortable jquery ui **/
            if (!jQuery().sortable) {
                return;
            }

            $('.planner-body > .column', this.container).sortable({
                connectWith: '.column',
                items: '.task-box',
                opacity: 0.8,
                forceHelperSize: true,
                placeholder: 'portlet-sortable-placeholder',
                forcePlaceholderSize: true,
                tolerance: "pointer",
            }).on("sortupdate", function (event, ui) {

                var leadId = ui.item.attr('data-lead');

                if (!leadId) {
                    return;
                }

                var column = ui.item.parents('.column.sortable').filter(':first');
                var whatShouldUpdate = column.attr('data-update');

                if (ui.sender !== null && column[0] === ui.sender[0]) {
                    // Se l'elemento viene rilasciato sulla stessa colonna
                    // non c'è bisogno di fare update
                    return;
                }

                switch (whatShouldUpdate) {
                    case 'column':
                        Planner.setLeadcolumn(leadId, column.attr('data-column'));
                        break;

                    case 'status':
                        Planner.setLeadStatus(leadId, column.attr('data-status'));
                        break;
                }
            });

            $('.column').disableSelection();

            /** Bind lead events **/
            $('[data-lead] .portlet-body a').on('click', function (e) {
                e.stopPropagation();
            });


            var dragging = false, mdTimer;

            $('[data-lead] .lead-body .text').on('click', function () {

                if (dragging) {
                    return false;
                }

                var leadPortlet = $(this).parents('[data-lead]');
                var id = leadPortlet.attr('data-lead');
                loadModal(base_url + 'get_ajax/layout_modal/lead-details/' + id);

            }).on('mousedown', function () {
                dragging = false;
                mdTimer = window.setTimeout(function () {
                    dragging = true;
                }, 250);
            }).on('mouseup', function () {
                clearTimeout(mdTimer);
            });

            var mainContainer = Planner.container;
            $('.show-all', this.container).on('click', function () {
                mainContainer.animate({
                    'max-height': 'none'
                }, function () {
                    $(this).removeClass('limited');
                });
            });
        }
    };

}();



$(document).ready(function () {
    Planner.init();

});