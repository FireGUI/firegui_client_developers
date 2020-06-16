$(document).ready(function () {

    $('table').on('mouseenter', 'tr', function () {

        var inline_actions_container = $('.js_action_inline', $(this));
        if (inline_actions_container.length > 0) {
            inline_actions_container.css('visibility', 'visible');
        }
    });

    $('table').on('mouseleave', 'tr', function () {

        $('.js_action_inline', $(this)).css('visibility', 'hidden');

    });
});
