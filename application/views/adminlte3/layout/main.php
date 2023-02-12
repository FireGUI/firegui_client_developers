<html lang="en" class="" style="height: auto;">

<!-- BEGIN HEAD -->

<head>
    <?php echo $head; ?>
</head>
<!-- END HEAD -->

<body class="layout-navbar-fixed layout-fixed layout-footer-fixed"
    data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>"
    data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>"
    data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>"
    data-base_url_builder="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>">
    <script src="<?php echo base_url("assets/js/core/grep_config.js?v=" . VERSION); ?>"></script>

    <div class="wrapper">

        <?php echo $header; ?>

        <aside class="main-sidebar sidebar-dark-primary elevation-1">
            <?php echo $sidebar; ?>
        </aside>

        <div id="js_layout_content_wrapper" class="content-wrapper js_page_content"
            data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>">
            <?php echo $page; ?>
        </div>

        <?php echo $footer; ?>
    </div>
</body>

</html>