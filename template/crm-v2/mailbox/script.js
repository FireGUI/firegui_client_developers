var Inbox = function () {

    var controllerUrl = base_url + 'mailbox/widget/';
    var content = $('.inbox-content');
    var loading = $('.inbox-loading');
    var curPage = 1;
    var curView = 'inbox';
    var curSearch = '';
    var filter = [];

    var loadInbox = function (el, name, page) {
        
        if (!name) {
            name = curView;
        }
        
        if (typeof page !== 'number' || page < 1) {
            page = (curView === name)? curPage:1;
        }
        
        var url = controllerUrl;
        var title = $('.inbox-nav > li.' + name + ' a').attr('data-title');

        loading.show();
        toggleButton(el);

        var data = {
            page: page,
            current: name,
            search: curSearch,
            filter: filter,
        };


        $.ajax(url, {
            type: "GET",
            data: data,
            dataType: "html",
            success: function (res) {
                
                content.html('');
                toggleButton(el);

                $('.inbox-nav > li.active').removeClass('active');
                $('.inbox-nav > li.' + name).addClass('active');
                $('.inbox-header > h1').text(title);

                loading.hide();
                content.html(res);
                Metronic.initComponents();
                //App.fixContentHeight();
                //App.initUniform();
                
                //--
                curView = name;
                curPage = page;
            },
            error: function (xhr, ajaxOptions, thrownError) {
                toggleButton(el);
            },
        });
    };

    var loadMessage = function (el, mailId) {
        var url = controllerUrl + 'view/' + mailId;

        loading.show();
        content.html('');
        toggleButton(el);

        $.ajax(url, {
            type: "GET",
            dataType: "html",
            success: function (res) {
                toggleButton(el);
                
                $('.inbox-header > h1').text('View Message');

                loading.hide();
                content.html(res);
                Metronic.initComponents();
                //App.fixContentHeight();
                //App.initUniform();
            },
            error: function (xhr, ajaxOptions, thrownError) {toggleButton(el);},
        });
    };

    var initWysihtml5 = function () {
        $('.inbox-wysihtml5').wysihtml5({"stylesheets": ["assets/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]});
    };

    var initFileupload = function () {

        $('#fileupload').fileupload({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: 'assets/plugins/jquery-file-upload/server/php/',
            autoUpload: true
        });

        // Upload server status check for browsers with CORS support:
        if ($.support.cors) {
            $.ajax({
                url: 'assets/plugins/jquery-file-upload/server/php/',
                type: 'HEAD'
            }).fail(function () {
                $('<span class="alert alert-error"/>')
                        .text('Upload server currently unavailable - ' +
                                new Date())
                        .appendTo('#fileupload');
            });
        }
    }

    var loadCompose = function (el) {
        var url = controllerUrl + 'compose';

        loading.show();
        content.html('');
        toggleButton(el);

        // load the form via ajax
        $.ajax({
            type: "GET",
            cache: false,
            url: url,
            dataType: "html",
            success: function (res)
            {
                toggleButton(el);

                $('.inbox-nav > li.active').removeClass('active');
                $('.inbox-header > h1').text('Compose');

                loading.hide();
                content.html(res);

                initFileupload();
                initWysihtml5();

                $('.inbox-wysihtml5').focus();
                Metronic.initComponents();
                //App.fixContentHeight();
                //App.initUniform();
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                toggleButton(el);
            },
            async: false
        });
    }

    var loadReply = function (el) {
        var url = controllerUrl + 'reply';

        loading.show();
        content.html('');
        toggleButton(el);

        // load the form via ajax
        $.ajax({
            type: "GET",
            cache: false,
            url: url,
            dataType: "html",
            success: function (res)
            {
                toggleButton(el);

                $('.inbox-nav > li.active').removeClass('active');
                $('.inbox-header > h1').text('Reply');

                loading.hide();
                content.html(res);
                $('[name="message"]').val($('#reply_email_content_body').html());

                handleCCInput(); // init "CC" input field

                initFileupload();
                initWysihtml5();
                Metronic.initComponents();
                //App.fixContentHeight();
                //App.initUniform();
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                toggleButton(el);
            },
            async: false
        });
    }

    var loadSearchResults = function (el) {
        var url = 'inbox_search_result.html';

        loading.show();
        content.html('');
        toggleButton(el);

        $.ajax({
            type: "GET",
            cache: false,
            url: url,
            dataType: "html",
            success: function (res)
            {
                toggleButton(el);

                $('.inbox-nav > li.active').removeClass('active');
                $('.inbox-header > h1').text('Search');

                loading.hide();
                content.html(res);
                Metronic.initComponents();
                //App.fixContentHeight();
                //App.initUniform();
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                toggleButton(el);
            },
            async: false
        });
    }

    var handleCCInput = function () {
        var the = $('.inbox-compose .mail-to .inbox-cc');
        var input = $('.inbox-compose .input-cc');
        the.hide();
        input.show();
        $('.close', input).click(function () {
            input.hide();
            the.show();
        });
    }

    var handleBCCInput = function () {

        var the = $('.inbox-compose .mail-to .inbox-bcc');
        var input = $('.inbox-compose .input-bcc');
        the.hide();
        input.show();
        $('.close', input).click(function () {
            input.hide();
            the.show();
        });
    }

    var toggleButton = function (el) {
        if (typeof el == 'undefined') {
            return;
        }
        if (el.attr("disabled")) {
            el.attr("disabled", false);
        } else {
            el.attr("disabled", true);
        }
    }

    return {
        //main function to initiate the module
        init: function () {

            content.on('click', '.pagination-control a', function () {
                loadInbox($(this), $(this).data('current'), $(this).data('page'));
            });
            
            // handle compose btn click
            $('.inbox .compose-btn a').live('click', function () {
                loadCompose($(this));
            });

            // handle reply and forward button click
            $('.inbox .reply-btn').live('click', function () {
                loadReply($(this));
            });

            // handle view message
            $('.inbox-content .view-message').live('click', function () {
                loadMessage($(this), $(this).data('mail'));
            });

            // handle inbox listing
            $('.inbox-nav > li.inbox > a').click(function () {
                loadInbox($(this), 'inbox');
            });

            // handle sent listing
            $('.inbox-nav > li.sent > a').click(function () {
                loadInbox($(this), 'sent');
            });

            // handle draft listing
            $('.inbox-nav > li.draft > a').click(function () {
                loadInbox($(this), 'draft');
            });

            // handle trash listing
            $('.inbox-nav > li.trash > a').click(function () {
                loadInbox($(this), 'trash');
            });
            
            // handle fixed filters
            $('.inbox-nav .mailbox-filter input[name="filters[]"]').change(function () {
                
                var val = $(this).val();
                var index = filter.indexOf(val);
                
                if (index > -1) {
                    filter.splice(index, 1);
                }
                
                if ($(this).is(':checked')) {
                    filter.push(val);
                }
                
                loadInbox($(this));
            });

            //handle compose/reply cc input toggle
            $('.inbox-compose .mail-to .inbox-cc').live('click', function () {
                handleCCInput();
            });

            //handle compose/reply bcc input toggle
            $('.inbox-compose .mail-to .inbox-bcc').live('click', function () {
                handleBCCInput();
            });

            $('.inbox-nav > li.inbox > a').click();
            
            $('.js-search-mail').on('submit', function(e) {
                e.preventDefault();
                curSearch = $('input[name=q]', this).val();
                loadInbox();
            });
            
            content.on('click', '.js-remove-search', function() {
                var form = $('.js-search-mail');
                $('input[name=q]', form).val('');
                form.submit();
            });

        }

    };

}();