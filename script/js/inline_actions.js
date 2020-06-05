$(document).ready(function () {

    $('table').on('mouseenter', 'td', function () {

        var inline_actions_container = $('.js_action_inline', $(this));
        if (inline_actions_container.length > 0) {
            inline_actions_container.css('visibility', 'visible');
        }
    });

    $('table').on('mouseleave', 'td', function () {

        $('.js_action_inline', $(this)).css('visibility', 'hidden');

    });
});
