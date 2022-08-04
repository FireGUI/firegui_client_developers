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


<body class="hold-transition skin-blue sidebar-mini" data-csrf="<?php echo base64_encode(json_encode(get_csrf())); ?>" data-base_url="<?php echo base_url(); ?>" data-base_url_admin="<?php echo base_url_admin(); ?>" data-base_url_template="<?php echo base_url_template(); ?>" data-base_url_scripts="<?php echo base_url_scripts(); ?>" data-base_url_uploads="<?php echo base_url_uploads(); ?>" data-base_url_builder="<?php echo OPENBUILDER_BUILDER_BASEURL; ?>">
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
            <div class="js_page_content" data-layout-id="<?php echo (!empty($dati['layout_id'])) ? $dati['layout_id'] : ''; ?>"  data-value_id="<?php echo (!empty($value_id)) ? $value_id : ''; ?>" data-title="<?php echo (!empty($dati['title_prefix'])) ? $dati['title_prefix'] : ''; ?>" data-related_entities="<?php echo (!empty($dati['related_entities']))?implode(',',$dati['related_entities']):'' ; ?>">
                <?php echo $page; ?>
            </div>
        </div>
        <?php echo $footer; ?>
    </div>


</body>
<!-- END BODY -->

</html>