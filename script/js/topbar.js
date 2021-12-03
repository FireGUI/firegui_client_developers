

$(function () {
    'use strict';

    var toolBarEnabled = false;
    var myArguments;

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

        $('.js_layout').toggleClass('layout_highlight');
        $('.js_layout_box').toggleClass('box_highlight');
        $('.connectedSortable').toggleClass('row_highlight');
        $('.modal-content').toggleClass('box_highlight');

        $('.label_highlight').toggleClass('hide');
        $('.builder_toolbar_actions').toggleClass('hide');
        initBuilderTools();
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

// For resize boxes
function update_cols(layout_boxes_id, cols) {
    $.ajax({
        url: base_url + "builder/update_layout_box_cols/" + layout_boxes_id + "/" + cols,
        dataType: 'json',
        cache: false,
    });
}

function initBuilderTools() {

    /* */
    $('.js_layouts_boxes_title').each(function () {
        var title = $(this).text().trim();
        var layou_box_id = $(this).data('layou_box_id');
        $(this).replaceWith('<input data-layou_box_id="' + layou_box_id + '" class="form-control input-sm js_update_layout_box_title" type="text" name="title" value="' + title + '" />');
    });

    $('body').on('change', '.js_update_layout_box_title', function () {
        var my_layout_box_id = my_layout_box.data("layou_box_id");
        var new_title = $(this).val();
        alert(new_title);
        $.ajax({
            url: base_url + "builder/update_layout_box_title/" + my_layout_box_id,
            data: { title: new_title },
            dataType: 'json',
            cache: false,
        });
        my_container.remove();
    });

    /* Move to another layout */
    $('body').on('click', '.js_btn_move_to_layout', function () {

        var new_layout_id = prompt("Please enter the new layout ID where you want to move the box", "");
        if (!new_layout_id) {
            return false;
        } else {
            var my_container = $(this).closest('.js_container_layout_box');
            var my_layout_box = $('.js_layout_box', my_container);
            var my_layout_box_id = my_layout_box.data("id");

            $.ajax({
                url: base_url + "builder/move_layout_box/" + my_layout_box_id + "/" + new_layout_id,
                dataType: 'json',
                cache: false,
            });
            my_container.remove();
        }

    });

    /* Resize Boxes */
    $('body').on('click', '.js_btn_delete', function () {
        var my_container = $(this).closest('.js_container_layout_box');
        var my_layout_box = $('.js_layout_box', my_container);
        var my_layout_box_id = my_layout_box.data("id");
        $.ajax({
            url: base_url + "builder/delete_layout_box/" + my_layout_box_id,
            dataType: 'json',
            cache: false,
        });
        my_container.remove();
    });

    /* Resize Boxes */
    $('body').on('click', '.js_btn_plus', function () {
        var my_container = $(this).closest('.js_container_layout_box');
        var my_layout_box = $('.js_layout_box', my_container);
        console.log(my_layout_box);
        cols = parseInt(my_container.data('cols'));
        var my_layout_box_id = my_layout_box.data("id");

        if (cols < 12) {
            new_cols = cols + 1;
            my_container.removeClass("col-md-" + cols);
            my_container.addClass("col-md-" + new_cols);
            my_container.data('cols', new_cols);
            update_cols(my_layout_box_id, new_cols, my_container);
        }
        if (cols > 2) {
            //*****$('.kt-portlet__head-title', my_container).show();
        }
    });

    $('body').on('click', '.js_btn_minus', function () {
        var my_container = $(this).closest('.js_container_layout_box');
        var my_layout_box = $('.js_layout_box', my_container);
        console.log(my_layout_box);
        cols = parseInt(my_container.data('cols'));
        var my_layout_box_id = my_layout_box.data("id");

        if (cols > 1) {
            new_cols = cols - 1;
            my_container.removeClass("col-md-" + cols);
            my_container.addClass("col-md-" + new_cols);
            my_container.data('cols', new_cols);
            update_cols(my_layout_box_id, new_cols, my_container);
        }
        if (cols <= 3) {
            $('.kt-portlet__head-title', my_container).hide();
        }
    });



    /* Sort layoutboxes */
    console.log("start Sortable");
    $(".connectedSortable").sortable({
        placeholder: 'ui-state-highlight',
        connectWith: '.connectedSortable',
        handle: '.js_layout_box',
        forcePlaceholderSize: true,
        forceHelperSize: true,
        tolerance: 'pointer',
        revert: 'invalid',
        /* That's fired first */
        start: function (event, ui) {
            myArguments = {};
            //ui.placeholder.width(ui.item.width());
            //ui.placeholder.height(ui.item.height() - 20);

            ui.placeholder.css({
                width: ui.item.innerWidth() - 30 + 1,
                height: ui.item.innerHeight() - 15 + 1,
                padding: ui.item.css("padding"),
                marginTop: 0
            });
        },
        /* That's fired second */
        remove: function (event, ui) {
            /* Get array of items in the list where we removed the item */
            myArguments = assembleData(this, myArguments);
        },
        /* That's fired thrird */
        receive: function (event, ui) {
            /* Get array of items where we added a new item */
            myArguments = assembleData(this, myArguments);
        },
        update: function (e, ui) {
            if (this === ui.item.parent()[0]) {
                /* In case the change occures in the same container */
                if (ui.sender == null) {
                    myArguments = assembleData(this, myArguments);
                }
            }
        },
        /* That's fired last */
        stop: function (event, ui) {
            try {
                var token = JSON.parse(atob(datatable.data('csrf')));
                var token_name = token.name;
                var token_hash = token.hash;
            } catch (e) {
                var token = JSON.parse(atob($('body').data('csrf')));
                var token_name = token.name;
                var token_hash = token.hash;
            }
            myArguments[token_name] = token_hash;
            /* Send JSON to the server */
            console.log("Send JSON to the server:<pre>" + JSON.stringify(myArguments) + "</pre>");

            var current_layout_id = ui.item.closest('.js_layout').attr('data-layout_id');
            var last_box_moved = ui.item.attr('id');

            $.ajax({
                url: base_url + "builder/update_layout_box_position/" + current_layout_id + "/" + last_box_moved,
                type: 'post',
                dataType: 'json',
                data: myArguments,
                cache: false
            });
            console.log(myArguments);
        },
    });

    $('.connectedSortable .box-header, .connectedSortable .js_layout_box').css('cursor', 'move');

    // END Sortable
}

// Sortable function
function assembleData(object, arguments) {
    var data = $(object).sortable('toArray'); // Get array data
    var row_id = $(object).data("row"); // Get step_id and we will use it as property name
    var arrayLength = data.length; // no need to explain
    var current_layout_id = $(object).closest('.js_layout').attr('data-layout_id');

    /* Create step_id property if it does not exist */
    if (!arguments.hasOwnProperty(row_id)) {
        arguments[row_id] = new Array();
    }

    /* Loop through all items */
    for (var i = 0; i < arrayLength; i++) {
        if (data[i]) {
            var task_id = data[i];
            /* push all image_id onto property step_id (which is an array) */
            arguments[row_id].push(task_id);
        }
    }
    return arguments;
}

function openBuilderFrame(link) {
    $('#builderFrame').attr('src', link);
    $('#builderFrameWrapper').show();
}
function closeBuilderFrame() {
    $('#builderFrameWrapper').hide();
    refreshAjaxLayoutBoxes();
}
