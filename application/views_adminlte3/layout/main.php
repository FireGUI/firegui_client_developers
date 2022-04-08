<html lang="en" class="" style="height: auto;">

<!-- BEGIN HEAD -->

<head>
    <?php echo $head; ?>
</head>
<!-- END HEAD -->

<body class="layout-navbar-fixed layout-fixed layout-footer-fixed" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>" data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>" data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>" data-base_url_builder="<?php echo FIREGUI_BUILDER_BASEURL; ?>">
    <script src="<?php echo base_url_scripts("script/js/grep_config.js?v=" . VERSION); ?>"></script>

    <div class="wrapper">

        <?php echo $header; ?>


        <aside class="main-sidebar sidebar-dark-primary elevation-2">
            <?php  echo $sidebar; ?>
        </aside>

        <div id="js_layout_content_wrapper" class="content-wrapper" data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>">
            <?php if (is_maintenance()) : ?>
                <section class="content-header">
                    <div class="alert alert-warning mb-0">
                        <h5><?php e('Updates in progress'); ?></h5>

                        <div><?php e('Dear customer, we are making updates to your platform, the service may be subject to slight interruptions, we apologize for the inconvenience.'); ?></div>
                    </div>
                </section>
            <?php endif; ?>
            <div id="js_page_content">
                <?php echo $page; ?>
            </div>
        </div>


        <?php echo $footer; ?>


        <div id="sidebar-overlay"></div>
    </div>

<!--     <script src="<?php echo base_url('assets/plugins/jquery/jquery.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/core/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/plugins/core/js/adminlte.min.js'); ?>"></script> -->
</body>

</html>