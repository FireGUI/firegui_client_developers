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
                        <h4><?php e('Aggiornamenti in corso'); ?></h4>

                        <p><?php e('Gentile cliente, stiamo effettuando degli aggiornamenti alla tua piattaforma, il servizio potrebbe subire leggere interruzioni, ci scusiamo per il disagio.'); ?></p>
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