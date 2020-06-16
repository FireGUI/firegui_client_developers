$(document).ready(function () {
    "use strict";

    $('[data-task] .task-body .text').on('click', function () {
        var taskPortlet = $(this).parents('[data-task]');
        var id = taskPortlet.attr('data-task');
        loadModal(base_url + 'get_ajax/layout_modal/72/' + id);
    });

    var mainContainer = $('#planner-container');
    $('.show-all', mainContainer).on('click', function () {
        mainContainer.animate({
            'max-height': 'none'
        }, function () {
            $(this).removeClass('limited');
        });
    });
});