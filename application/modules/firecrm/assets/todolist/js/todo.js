/*
 * Detact Mobile Browser
 */
if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    $('html').addClass('ismobile');
}

$(document).ready(function () {
    /*
     * Clear Notification
     */
    $('body').on('click', '[data-clear="notification"]', function (e) {

        return;
        e.preventDefault();

        var x = $(this).closest('.listview');
        var y = x.find('.lv-item');
        var z = y.size();

        $(this).parent().fadeOut();

        x.find('.list-group').prepend('<i class="grid-loading hide-it"></i>');
        x.find('.grid-loading').fadeIn(1500);

        var w = 0;
        y.each(function () {
            var z = $(this);
            setTimeout(function () {
                z.addClass('animated fadeOutRightBig').delay(1000).queue(function () {
                    z.remove();
                });
            }, w += 150);
        })

        //Popup empty message
        setTimeout(function () {
            $('#notifications').addClass('empty');
        }, (z * 150) + 200);
    });

    /*
     * Dropdown Menu
     */
    if ($('.dropdown')[0]) {
        //Propagate
        $('body').on('click', '.dropdown.open .dropdown-menu', function (e) {
            e.stopPropagation();
        });

        $('.dropdown').on('shown.bs.dropdown', function (e) {
            if ($(this).attr('data-animation')) {
                $animArray = [];
                $animation = $(this).data('animation');
                $animArray = $animation.split(',');
                $animationIn = 'animated ' + $animArray[0];
                $animationOut = 'animated ' + $animArray[1];
                $animationDuration = ''
                if (!$animArray[2]) {
                    $animationDuration = 500; //if duration is not defined, default is set to 500ms
                }
                else {
                    $animationDuration = $animArray[2];
                }

                $(this).find('.dropdown-menu').removeClass($animationOut)
                $(this).find('.dropdown-menu').addClass($animationIn);
            }
        });

        $('.dropdown').on('hide.bs.dropdown', function (e) {
            if ($(this).attr('data-animation')) {
                e.preventDefault();
                $this = $(this);
                $dropdownMenu = $this.find('.dropdown-menu');

                $dropdownMenu.addClass($animationOut);
                setTimeout(function () {
                    $this.removeClass('open')

                }, $animationDuration);
            }
        });
    }

    /*
     * Todo Add new item
     */
    if ($('#todo-lists')[0]) {
        //Add Todo Item
        $('body').on('click', '#add-tl-item .add-new-item', function () {
            $(this).parent().addClass('toggled').find('textarea').focus();
        });

        //Dismiss
        $('body').on('click', '.add-tl-actions > a', function (e) {
            e.preventDefault();
            var x = $(this).closest('#add-tl-item');
            var y = $(this).data('tl-action');

            if (y == "dismiss") {
                x.find('textarea').val('');
                x.removeClass('toggled');
            }

            if (y == "save") {
                var token = JSON.parse(atob($('body').data('csrf')));
                var token_name = token.name;
                var token_hash = token.hash;
                var text = x.find('textarea').val();
                var user = x.find('[name="todolist_user"]').val();
                var project_id = x.find('[name="todolist_project_id"]').val();
                var customer_id = x.find('[name="todolist_customer_id"]').val();
                x.find('textarea').val('');
                $.ajax({
                    method: 'post',
                    url: base_url + 'firecrm/todolist/create',
                    dataType: 'json',
                    data: {
                        todolist_project_id: project_id,
                        todolist_customer_id: customer_id,
                        todolist_user: user,
                        todolist_text: text,
                        [token_name]: token_hash
                    },
                    success: function (msg) {
                        var input = '<input type="checkbox" value="' + msg.data.todolist_id + '"><i class="input-helper"></i>';
                        var span = '<span>' + msg.data.todolist_text + '</span>';
                        var remover = '<span class="pull-right js_remove_todo" role="button" data-todo-id="' + msg.data.todolist_id + '"><i class="fas fa-trash"></i></span>';
                        $('.tl-body').append('<div class="checkbox media"><div class="media-body"><label>' + input + span + '</label>' + remover + '</div></div>');
                    },
                    error: function () {
                    }
                });
                x.removeClass('toggled');
            }
        });

        $('body').on('change', '.checkbox.media input[type="checkbox"]', function (e) {
            var token = JSON.parse(atob($('body').data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;
            var todoitem = $(this);
            var todoitemsId = $(this).val();

            $.post(base_url + 'firecrm/todolist/edit/' + todoitemsId, { [token_name]: token_hash, todolist_deleted: todoitem.is(':checked') ? '1' : '0' });
        });

        $('body').on('click', '.js_remove_todo', function () {
            var button = $(this);
            var todoitemsId = button.data('todo-id');

            if (!confirm('Are you sure to delete todo?')) {
                return;
            }

            var token = JSON.parse(atob($('body').data('csrf')));
            var token_name = token.name;
            var token_hash = token.hash;

            $.ajax({
                method: 'post',
                url: base_url + 'firecrm/todolist/delete/' + todoitemsId,
                dataType: 'json',
                data: {
                    [token_name]: token_hash
                },
                success: function () {
                    button.closest('.checkbox.media').fadeOut();
                },
                error: function () {

                }
            });
        });

    }

    /*
     * Auto Hight Textarea
     */
    if ($('.auto-size')[0]) {
        autosize($('.auto-size'));
    }



    /*
     * Text Feild
     */

    //Add blue animated border and remove with condition when focus and blur
    if ($('.fg-line')[0]) {
        $('body').on('focus', '.form-control', function () {
            $(this).closest('.fg-line').addClass('fg-toggled');
        })

        $('body').on('blur', '.form-control', function () {
            var p = $(this).closest('.form-group');
            var i = p.find('.form-control').val();

            if (p.hasClass('fg-float')) {
                if (i.length == 0) {
                    $(this).closest('.fg-line').removeClass('fg-toggled');
                }
            }
            else {
                $(this).closest('.fg-line').removeClass('fg-toggled');
            }
        });
    }

    //Add blue border for pre-valued fg-flot text feilds
    if ($('.fg-float')[0]) {
        $('.fg-float .form-control').each(function () {
            var i = $(this).val();

            if (!i.length == 0) {
                $(this).closest('.fg-line').addClass('fg-toggled');
            }

        });
    }

    /*
     * Audio and Video
     */
    if ($('audio, video')[0]) {
        $('video,audio').mediaelementplayer();
    }

    /*
     * Tag Select
     */
    if ($('.tag-select')[0]) {
        $('.tag-select').chosen({
            width: '100%',
            allow_single_deselect: true
        });
    }

    /*
     * Input Slider
     */
    //Basic
    if ($('.input-slider')[0]) {
        $('.input-slider').each(function () {
            var isStart = $(this).data('is-start');

            $(this).noUiSlider({
                start: isStart,
                range: {
                    'min': 0,
                    'max': 100,
                }
            });
        });
    }

    //Range slider
    if ($('.input-slider-range')[0]) {
        $('.input-slider-range').noUiSlider({
            start: [30, 60],
            range: {
                'min': 0,
                'max': 100
            },
            connect: true
        });
    }

    //Range slider with value
    if ($('.input-slider-values')[0]) {
        $('.input-slider-values').noUiSlider({
            start: [45, 80],
            connect: true,
            direction: 'rtl',
            behaviour: 'tap-drag',
            range: {
                'min': 0,
                'max': 100
            }
        });

        $('.input-slider-values').Link('lower').to($('#value-lower'));
        $('.input-slider-values').Link('upper').to($('#value-upper'), 'html');
    }

    /*
     * Input Mask
     */
    if ($('input-mask')[0]) {
        $('.input-mask').mask();
    }

    /*
     * Color Picker
     */
    if ($('.color-picker')[0]) {
        $('.color-picker').each(function () {
            $('.color-picker').each(function () {
                var colorOutput = $(this).closest('.cp-container').find('.cp-value');
                $(this).farbtastic(colorOutput);
            });
        });
    }

    /*
     * HTML Editor
     */
    if ($('.html-editor')[0]) {
        $('.html-editor').summernote({
            height: 150
        });
    }

    if ($('.html-editor-click')[0]) {
        //Edit
        $('body').on('click', '.hec-button', function () {
            $('.html-editor-click').summernote({
                focus: true
            });
            $('.hec-save').show();
        })

        //Save
        $('body').on('click', '.hec-save', function () {
            $('.html-editor-click').code();
            $('.html-editor-click').destroy();
            $('.hec-save').hide();
            notify('Content Saved Successfully!', 'success');
        });
    }

    //Air Mode
    if ($('.html-editor-airmod')[0]) {
        $('.html-editor-airmod').summernote({
            airMode: true
        });
    }

    /*
     * Date Time Picker
     */

    //Date Time Picker
    if ($('.date-time-picker')[0]) {
        $('.date-time-picker').datetimepicker();
    }

    //Time
    if ($('.time-picker')[0]) {
        $('.time-picker').datetimepicker({
            format: 'LT'
        });
    }

    //Date
    if ($('.date-picker')[0]) {
        $('.date-picker').datetimepicker({
            format: 'DD/MM/YYYY'
        });
    }

    /*
     * Form Wizard
     */

    if ($('.form-wizard-basic')[0]) {
        $('.form-wizard-basic').bootstrapWizard({
            tabClass: 'fw-nav',
            'nextSelector': '.next',
            'previousSelector': '.previous'
        });
    }


    /*
     * Waves Animation
     */
    //    (function () {
    //        Waves.attach('.btn:not(.btn-icon)');
    //        Waves.attach('.btn-icon', ['waves-circle', 'waves-float']);
    //        Waves.init();
    //    })();

    /*
     * Lightbox
    if ($('.lightbox')[0]) {
        $('.lightbox').lightGallery({
            enableTouch: true
        });
    }
     */

    /*
     * Link prevent
     */
    $('body').on('click', '.a-prevent', function (e) {
        e.preventDefault();
    });

    /*
     * Collaspe Fix
     */
    if ($('.collapse')[0]) {

        //Add active class for opened items
        $('.collapse').on('show.bs.collapse', function (e) {
            $(this).closest('.panel').find('.panel-heading').addClass('active');
        });

        $('.collapse').on('hide.bs.collapse', function (e) {
            $(this).closest('.panel').find('.panel-heading').removeClass('active');
        });

        //Add active class for pre opened items
        $('.collapse.in').each(function () {
            $(this).closest('.panel').find('.panel-heading').addClass('active');
        });
    }

    /*
     * Tooltips
     */
    if ($('[data-toggle="tooltip"]')[0]) {
        $('[data-toggle="tooltip"]').tooltip();
    }

    /*
     * Popover
     */
    if ($('[data-toggle="popover"]')[0]) {
        $('[data-toggle="popover"]').popover();
    }



    /*
     * IE 9 Placeholder
     */
    if ($('html').hasClass('ie9')) {
        $('input, textarea').placeholder({
            customClass: 'ie9-placeholder'
        });
    }

});