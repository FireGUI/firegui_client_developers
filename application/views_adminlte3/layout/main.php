<html lang="en" class="" style="height: auto;">

<!-- BEGIN HEAD -->

<head>
    <?php echo $head; ?>
</head>
<!-- END HEAD -->

<body class="layout-navbar-fixed layout-fixed layout-footer-fixed" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>" data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>" data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>" data-base_url_builder="<?php echo FIREGUI_BUILDER_BASEURL; ?>">
    <script src="<?php echo base_url("assets/js/core/grep_config.js?v=" . VERSION); ?>"></script>
    
    <div class="wrapper">

        <?php echo $header; ?>

        <aside class="main-sidebar sidebar-dark-primary elevation-1">
            <?php echo $sidebar; ?>
        </aside>

        <div id="js_layout_content_wrapper" class="content-wrapper js_page_content" data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>">
            <?php if (is_maintenance()) : ?>
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="callout callout-warning mb-0">
                            <h5><?php e('Updates in progress'); ?></h5>

                            <div><?php e('Dear customer, we are making updates to your platform, the service may be subject to slight interruptions, we apologize for the inconvenience.'); ?></div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if ($dati['show_title'] == true && $dati['layout_container']['layouts_show_header'] == DB_BOOL_TRUE) : ?>
                <section class="content-header page-title">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>
                                    <?php e(ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title']))); ?>
                                    <small><?php e($dati['layout_container']['layouts_subtitle']); ?></small>
                                </h1>
                            </div>
                            <div class="col-sm-6">
                                <ol class="breadcrumb float-sm-right text-sm">
                                    <li class="breadcrumb-item">
                                        <a href="<?php echo base_url('main/layout/1'); ?>">
                                            <i class="fas fa-tachometer-alt"></i>
                                            Home
                                        </a>
                                    </li>
                                    <?php if (!empty($dati['layout_container']['modules_name'])) : ?>
                                        <li class="breadcrumb-item">
                                            <i class="fas fa-plug"></i>
                                            <?php echo $dati['layout_container']['modules_name'] . " (" . $dati['layout_container']['modules_version'] . ")"; ?>
                                        </li>
                                    <?php endif; ?>
                                    <li class="breadcrumb-item active"> <?php e(ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title']))); ?></li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?> 
            
            <?php echo $page; ?>
        </div>

        <?php echo $footer; ?>
    </div>
</body>

</html>