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

<body class="hold-transition skin-blue sidebar-mini">
    <div class="wrapper">

        <header class="main-header">

            <?php echo $header; ?>
        </header>
        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">

            <?php echo $sidebar; ?>

        </aside>
        <!-- Content Wrapper. Contains page content -->

        <div class="content-wrapper">

            <?php if (!empty($this->settings['settings_maintenance_mode']) && $this->settings['settings_maintenance_mode'] == DB_BOOL_TRUE) : ?>
                <section class="content-header">
                    <div class="callout callout-danger">
                        <h4><?php e('Updates in progress'); ?></h4>

                        <p><?php e('Dear customer, we are making updates to your platform, the service may be subject to slight interruptions, we apologize for the inconvenience.'); ?></p>
                    </div>
                </section>
            <?php endif; ?>

            <?php echo $page; ?>
        </div>
        <?php echo $footer; ?>
    </div>

</body>
<!-- END BODY -->

</html>