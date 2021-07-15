

$(function () {
    'use strict';

    if (localStorage.getItem("toolBarEnabled")) {
        var toolBarEnabled = true;
        $('#builder_toolbar').show();
    }

    $('body').on('click', '#js_enable_dev', function () {


        var data_post = [];
        data_post.push({ name: "builderProjectHash", value: builderProjectHash });

        $.ajax(base_url_builder + 'public/client/checkClientConnection/', {
            type: 'POST',
            data: data_post,
            dataType: 'json',

            success: function (data) {
                if (data.status == 1) {
                    $('#builder_toolbar').show();
                    var toolBarEnabled = true;
                    localStorage.setItem('toolBarEnabled', 'true');
                } else {
                    $('#builder_toolbar').hide();
                    var toolBarEnabled = false;
                    if (data.msg) {
                        alert(data.msg);
                    }
                }


            },
        });
    });


    // ********* Toolbar buttons *************

    $('body').on('click', '#js_toolbar_vblink', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');
        window.open(base_url_builder + 'main/visual_builder/' + layout_id, '_blank');
    });
    $('body').on('click', '#js_toolbar_vbframe', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/visual_builder/' + layout_id);
    });

    $('body').on('click', '#js_toolbar_events', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/events_builder');
    });

    $('body').on('click', '#js_toolbar_entities', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/new_entity');
    });

    $('body').on('click', '#js_toolbar_backup', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/database_dumps');
    });

    $('body').on('click', '#js_toolbar_query', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');
        openBuilderFrame(base_url_builder + 'main/query');
    });


    // Exit dev mode
    $('body').on('click', '#js_toolbar_exit', function () {
        localStorage.removeItem("toolBarEnabled");
        $('#builder_toolbar').hide();
    });

    $('body').on('click', '#js_toolbar_highlighter', function () {

        $('.box').toggleClass('box_highlight');
        $('.modal-content').toggleClass('box_highlight');
        $('.js_builder_toolbar_btn').toggleClass('hide');
    });

    // Buttons actions

    $('body').on('click', '.js_builder_toolbar_btn', function () {
        var layout_id = $('#js_layout_content_wapper').data('layout-id');

        var action = $(this).data('action');
        var element_type = $(this).data('element-type');
        var element_ref = $(this).data('element-ref');

        const json = { "action": action, "type": element_type, "ref": element_ref }
        const string = JSON.stringify(json); // convert Object to a String
        const encodedString = btoa(string);
        //window.open(base_url_builder + 'main/visual_builder/'+layout_id+'/'+encodedString, '_blank');
        openBuilderFrame(base_url_builder + 'main/visual_builder/' + layout_id + '/' + encodedString);
    });


});

function openBuilderFrame(link) {
    $('#builderFrame').attr('src', link);
    $('#builderFrameWrapper').show();
}
function closeBuilderFrame() {
    $('#builderFrameWrapper').hide();
}

