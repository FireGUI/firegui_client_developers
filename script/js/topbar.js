

$(function () {
    'use strict';

    var toolBarEnabled = false;

    jQuery(document).keydown(function (event) {
        // If Control or Command key is pressed and the S key is pressed
        // run save function. 83 is the key code for S.
        if ((event.ctrlKey || event.metaKey) && event.which == 68) {
            // Save Function
            event.preventDefault();

            if ($('#js_enable_dev').length) {

                if (toolBarEnabled == true) {
                    $('#builder_toolbar').hide();
                    toolBarEnabled = false;
                    localStorage.setItem('toolBarEnabled', 'false');
                } else {
                    $('#builder_toolbar').show();
                    toolBarEnabled = true;
                    localStorage.setItem('toolBarEnabled', 'true');
                }

            }
            return false;
        }
    }
    );

    if (localStorage.getItem("toolBarEnabled")) {
        toolBarEnabled = true;
        $('#builder_toolbar').show();
    }

    $('body').on('click', '#js_enable_dev', function () {


        // var data_post = [];
        // data_post.push({ name: "builderProjectHash", value: builderProjectHash });

        $('#builder_toolbar').show();
        toolBarEnabled = true;
        localStorage.setItem('toolBarEnabled', 'true');


        // $.ajax(base_url + 'firegui/projectConnect/', {
        //     type: 'POST',
        //     data: data_post,
        //     dataType: 'json',

        //     success: function (data) {


        //         $('#builder_toolbar').show();
        //         var toolBarEnabled = true;
        //         localStorage.setItem('toolBarEnabled', 'true');
        //         localStorage.setItem('toolBarToken', data);



        //     },
        // });
    });

    // ********* Console buttons and events *************

    $('.builder_console').on('click', '.js_console_command', function () {
        $(this).next().toggleClass('hide');
    });
    $('.builder_console').on('click', '.js_show_code', function () {
        $(this).next().next().toggleClass('hide');
    });
    $('.builder_console').on('click', '.fakeClose', function () {
        $('.builder_console').toggleClass('hide');
    });

    $('.builder_console').on('click', '.fakeZoom', function () {
        if ($('.builder_console').hasClass('full_size')) {
            window.scrollTo(0, document.body.scrollHeight);
        } else {
            window.scrollTo(0, 0);
        }

        $('.builder_console').toggleClass('full_size');
    });

    // ********* Toolbar buttons *************

    $('body').on('click', '#js_toolbar_vblink', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        //var token = localStorage.getItem('toolBarToken');
        window.open(base_url_builder + 'main/visual_builder/' + layout_id + '?hash=' + builderProjectHash, '_blank');
    });
    $('body').on('click', '#js_toolbar_vbframe', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/visual_builder/' + layout_id + '?hash=' + builderProjectHash);
    });

    $('body').on('click', '#js_toolbar_events', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/events_builder' + '?hash=' + builderProjectHash);
    });

    $('body').on('click', '#js_toolbar_entities', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/new_entity' + '?hash=' + builderProjectHash);
    });

    $('body').on('click', '#js_toolbar_backup', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/database_dumps' + '?hash=' + builderProjectHash);
    });

    $('body').on('click', '#js_toolbar_query', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/query' + '?hash=' + builderProjectHash);
    });


    // Exit dev mode
    $('body').on('click', '#js_toolbar_exit', function () {
        localStorage.removeItem("toolBarEnabled");
        localStorage.removeItem('toolBarToken');
        $('#builder_toolbar').hide();
    });

    $('body').on('click', '#js_toolbar_highlighter', function () {

        $('.box').toggleClass('box_highlight');
        $('.modal-content').toggleClass('box_highlight');
        $('.js_builder_toolbar_btn').toggleClass('hide');
    });

    $('body').on('click', '#js_toolbar_console', function () {
        $('.builder_console').toggleClass('hide');
        window.scrollTo(0, document.body.scrollHeight);
    });

    // Buttons actions

    $('body').on('click', '.js_builder_toolbar_btn', function () {
        var layout_id = $('#js_layout_content_wrapper').data('layout-id');
        var action = $(this).data('action');
        var element_type = $(this).data('element-type');
        var element_ref = $(this).data('element-ref');

        const json = { "action": action, "type": element_type, "ref": element_ref }
        const string = JSON.stringify(json); // convert Object to a String
        const encodedString = btoa(string);
        //window.open(base_url_builder + 'main/visual_builder/'+layout_id+'/'+encodedString, '_blank');
        openBuilderFrame(base_url_builder + 'main/visual_builder/' + layout_id + '/' + encodedString + '?hash=' + builderProjectHash);
    });


});

function openBuilderFrame(link) {
    $('#builderFrame').attr('src', link);
    $('#builderFrameWrapper').show();
}
function closeBuilderFrame() {
    $('#builderFrameWrapper').hide();
    refreshAjaxLayoutBoxes();
}
