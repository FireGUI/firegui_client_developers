<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->

<!-- BEGIN HEAD -->

<head>
    <?php echo $head; ?>
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->


<body class="hold-transition skin-blue sidebar-mini" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>" data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>" data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>" data-base_url_builder="<?php echo FIREGUI_BUILDER_BASEURL; ?>">
    <script src="<?php echo base_url_scripts("script/js/grep_config.js?v=" . VERSION); ?>"></script>
    <div class="wrapper">

        <header class="main-header">

            <?php echo $header; ?>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">

            <?php echo $sidebar; ?>

        </aside>
        <!-- Content Wrapper. Contains page content -->

        <div id="js_layout_content_wrapper" class="content-wrapper" data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>">

            <?php if (is_maintenance()) : ?>
                <section class="content-header">
                    <div class="callout callout-warning">
                        <h4><?php e('Updates in progress'); ?></h4>

                        <p><?php e('Dear customer, we are making updates to your platform, the service may be subject to slight interruptions, we apologize for the inconvenience.'); ?></p>
                    </div>
                </section>
            <?php endif; ?>
            <div id="js_page_content">
                <?php echo $page; ?>
            </div>
        </div>
        <?php echo $footer; ?>
    </div>


    <!-- Vertical toolbar -->
    <div id="builder_toolbar" style="display:none">
        <?php if ($this->auth->is_admin()) : ?>
            <div class="material-switch">
                <input id="js_toolbar_maintenance" type="checkbox" <?php if (is_maintenance()) : ?>checked="checked" <?php endif; ?> value="1">
                <label for="js_toolbar_maintenance" class="label-success" data-toggle="tooltip" data-placement="bottom" data-container="body" title="Maintenance mode"></label>
            </div>

            <div class="btn-toolbar">
                <div class="btn-group-horizontal" role="group" aria-label="...">

                    <button id="js_toolbar_vblink" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="Open layout on Visual Builder">
                        <span class="fas fa-external-link-alt"></span></button>

                    <button id="js_toolbar_vbframe" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="Open on Frame Builder">
                        <span class="fas fa-tv"></span></button>

                    <button id="js_toolbar_highlighter" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="Highlight elements"><span class="fas fa-highlighter"></span></button>

                    <button id="js_toolbar_console" class="btn btn-default" data-toggle="tooltip" data-placement="bottom" data-container="body" title="Dev Console"><span class="fas fa-terminal"></span></button>

                    <button id="js_toolbar_download_dump" class="btn btn-default btn-spaced" data-toggle="tooltip" data-placement="left" data-container="body" title="Download Dump"><span class="fas fa-download"></span></button>
                    <button id="js_toolbar_download_zip" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Download Full Zip"><span class="fas fa-cloud-download-alt"></span></button>
                    <!--
                    <button id="js_toolbar_backup" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Backup & Restore"><span class="fas fa-download"></span></button>
                    <button id="js_toolbar_query" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Query"><span class="fas fa-pen"></span></button>
                    -->
                    <button id="js_toolbar_exit" class="btn btn-default btn-spaced" data-toggle="tooltip" data-placement="bottom" data-container="body" title="Close Toolbar"><span class="fas fa-sign-out-alt"></span></button>
                </div>

                <!-- Dropdown  
               <div class="btn-group open">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="fas fa-cogs"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="#">Download Dump</a></li>
                    <li><a href="#">Download Full Zip</a></li>

                    <li class="divider"></li>
                    <li><a href="#">Separated link</a></li>
                </ul>
            </div>-->
            </div>
        <?php endif; ?>
    </div>

    <div id="builderFrameWrapper">
        <iframe id="builderFrame" src=""></iframe>
    </div>
</body>
<!-- END BODY -->

</html>