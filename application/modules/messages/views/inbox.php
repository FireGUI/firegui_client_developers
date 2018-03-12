<h3 class="page-title">Messages <small>write</small></h3>
<div class="row inbox">
    <div class="col-md-2">
        <ul class="inbox-nav margin-bottom-10">
            <li class="compose-btn">
                <a href="javascript:;" data-title="Compose" class="btn green"> 
                    <i class="fa fa-edit"></i> Compose
                </a>
            </li>
            <li class="inbox active">
                <a href="javascript:;" class="btn" data-title="Inbox">Inbox(3)</a>
                <b></b>
            </li>
        </ul>
    </div>
    <div class="col-md-10">
        <div class="inbox-header">
            <h1 class="pull-left">Inbox</h1>
        </div>
        <div class="inbox-loading">Loading...</div>
        <div class="inbox-content" data-view="<?php echo (isset($dati['view'])? $dati['view']: NULL) ?>"></div>
    </div>
</div>



<script type="text/javascript" charset="utf-8">
    var Inbox = function() {

        var content = $('.inbox-content');
        var loading = $('.inbox-loading');
        var defaultViewMessage = content.attr('data-view');

        var loadInbox = function(el, name) {
            var url = base_url + 'messages/get_ajax/inbox';
            var title = $('.inbox-nav > li.' + name + ' a').attr('data-title');

            loading.show();
            content.html('');
            toggleButton(el);

            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function(res)
                {
                    toggleButton(el);

                    $('.inbox-nav > li.active').removeClass('active');
                    $('.inbox-nav > li.' + name).addClass('active');
                    $('.inbox-header > h1').text(title);

                    loading.hide();
                    content.html(res);
                    App.fixContentHeight();
                    App.initUniform();
                },
                error: function(xhr, ajaxOptions, thrownError)
                {
                    toggleButton(el);
                },
                async: false
            });
        }

        var loadMessage = function(el, name, resetMenu) {
            
            var message;
                    
            if(typeof el !== 'undefined') {
                message = el.parents('tr').filter(':first').attr('data-message-id');
            } else {
                message = defaultViewMessage;
            }
            
            if(!message) {
                alert('Cannot load this message');
                return;
            }
            
            var url = base_url + 'messages/get_ajax/message/'+message;

            loading.show();
            content.html('');
            toggleButton(el);

            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function(res)
                {
                    toggleButton(el);

                    if (resetMenu) {
                        $('.inbox-nav > li.active').removeClass('active');
                    }
                    $('.inbox-header > h1').text('View Message');

                    loading.hide();
                    content.html(res);
                    App.fixContentHeight();
                    App.initUniform();
                },
                error: function(xhr, ajaxOptions, thrownError)
                {
                    toggleButton(el);
                },
                async: false
            });
        }

        var initWysihtml5 = function() {
            $('.inbox-wysihtml5').wysihtml5({
                "stylesheets": ["assets/plugins/bootstrap-wysihtml5/wysiwyg-color.css"]
            });
        }

        var loadCompose = function(el) {
            var url = base_url + 'messages/get_ajax/compose';

            loading.show();
            content.html('');
            toggleButton(el);

            // load the form via ajax
            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function(res)
                {
                    toggleButton(el);

                    $('.inbox-nav > li.active').removeClass('active');
                    $('.inbox-header > h1').text('Compose');

                    loading.hide();
                    content.html(res);
                    initWysihtml5();

                    $('.inbox-wysihtml5').focus();
                    App.fixContentHeight();
                    App.initUniform();
                },
                error: function(xhr, ajaxOptions, thrownError)
                {
                    toggleButton(el);
                },
                async: false
            });
        }

        var loadSearchResults = function(el) {
            var url = 'inbox_search_result.html';

            loading.show();
            content.html('');
            toggleButton(el);

            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function(res)
                {
                    toggleButton(el);

                    $('.inbox-nav > li.active').removeClass('active');
                    $('.inbox-header > h1').text('Search');

                    loading.hide();
                    content.html(res);
                    App.fixContentHeight();
                    App.initUniform();
                },
                error: function(xhr, ajaxOptions, thrownError)
                {
                    toggleButton(el);
                },
                async: false
            });
        }

        var toggleButton = function(el) {
            if (typeof el == 'undefined') {
                return;
            }
            if (el.attr("disabled")) {
                el.attr("disabled", false);
            } else {
                el.attr("disabled", true);
            }
        }


        var loadReply = function(el) {
            var message = el.attr('data-message-id');
            var url = base_url+'messages/get_ajax/compose/'+message;

            loading.show();
            content.html('');
            toggleButton(el);

            // load the form via ajax
            $.ajax({
                type: "GET",
                cache: false,
                url: url,
                dataType: "html",
                success: function(res)
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
                    App.fixContentHeight();
                    App.initUniform();
                },
                error: function(xhr, ajaxOptions, thrownError)
                {
                    toggleButton(el);
                },
                async: false
            });
        }



        $(document).ready(function() {

            // handle compose btn click
            $('.inbox .compose-btn a').live('click', function() {
                loadCompose($(this));
            });

            // handle reply and forward button click
            $('.inbox .reply-btn').live('click', function() {
                loadReply($(this));
            });

            // handle view message
            $('.inbox-content .view-message').live('click', function() {
                loadMessage($(this));
            });

            // handle inbox listing
            $('.inbox-nav > li.inbox > a').click(function() {
                loadInbox($(this), 'inbox');
            });

            // handle sent listing
            $('.inbox-nav > li.sent > a').click(function() {
                loadInbox($(this), 'sent');
            });

            // handle draft listing
            $('.inbox-nav > li.draft > a').click(function() {
                loadInbox($(this), 'draft');
            });

            // handle trash listing
            $('.inbox-nav > li.trash > a').click(function() {
                loadInbox($(this), 'trash');
            });

            //handle loading content based on URL parameter
            if (defaultViewMessage !== '') {
                loadMessage();
            } else if (App.getURLParameter("a") === "compose") {
                loadCompose();
            } else {
                $('.inbox-nav > li.inbox > a').click();
            }

        });

    }();
</script>