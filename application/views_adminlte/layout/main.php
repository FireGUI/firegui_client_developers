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

<body class="hold-transition skin-blue sidebar-mini" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>">
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
            <div class="js_loading">
                <img src="<?php echo base_url_admin('images/loader.gif'); ?>" />
            </div>
        </div>
        <?php echo $footer; ?>
    </div>


    <!-- Vertical toolbar -->
    <div id="builder_toolbar" style="display:none">
        <div class="btn-toolbar">

            <div class="btn-group-vertical" role="group" aria-label="...">
                <button id="js_toolbar_vblink" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Open layout on Visual Builder">
                    <span class="fas fa-external-link-alt"></span></button>

                <button id="js_toolbar_vbframe" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Open on Frame Builder">
                    <span class="fas fa-tv"></span></button>

                <button id="js_toolbar_highlighter" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Highlight elements"><span class="fas fa-highlighter"></span></button>
            </div>
            <div class="btn-group-vertical" role="group" aria-label="...">
                <button id="js_toolbar_events" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Events & Triggers"><span class="fas fa-random"></span></button>

                <button id="js_toolbar_entities" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Entities"><span class="fas fa-layer-group"></span></button>

                <button id="js_toolbar_backup" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Backup & Restore"><span class="fas fa-download"></span></button>

                <button id="js_toolbar_query" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Query"><span class="fas fa-pen"></span></button>

            </div>

            <div class="btn-group-vertical" role="group" aria-label="...">
                <button id="js_toolbar_exit" class="btn btn-default" data-toggle="tooltip" data-placement="left" data-container="body" title="Close Toolbar"><span class="fas fa-sign-out-alt"></span></button>
            </div>


            <!--<div class="btn-group-vertical" role="group" aria-label="...">
        <div class="btn-group dropup" 
             data-toggle="tooltip" 
             data-placement="left" 
             data-container="body" 
             title="Settings">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-cog"></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-right">
              <li><a href="#">Action one</a></li>
              <li><a href="#">Action two</a></li>
              <li><a href="#">Action three</a></li>
        </ul>
        </div>
      </div>-->
        </div>
    </div>

    <div id="builderFrameWrapper">
        <button OnClick="closeBuilderFrame();">Hide Builder</button>
        <iframe id="builderFrame" src=""></iframe>
    </div>
</body>
<!-- END BODY -->

</html>