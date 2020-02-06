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
    <body class="page-md page-header-fixed">
        
        <!-- BEGIN HEADER -->   
        <div class="page-header md-shadow-z-1-i navbar navbar-fixed-top" id="js-header-top-bar">
           <?php echo $header; ?>
            
        </div>
        <!-- END HEADER -->
        
        
        <div class="clearfix"></div>
        
        
        <!-- BEGIN CONTAINER -->
        <div class="page-container">
            
            <!-- BEGIN SIDEBAR -->
            <div class="page-sidebar-wrapper">
                <div class="page-sidebar navbar-collapse collapse">
                    <?php echo $sidebar; ?>
                </div>
            </div>
            <!-- END SIDEBAR -->
            
            <!-- BEGIN PAGE -->
            <div class="page-content-wrapper">
                <div class="page-content">
                    <?php if (!empty($this->settings['settings_maintenance_mode']) && $this->settings['settings_maintenance_mode'] == DB_BOOL_TRUE): ?>
                        <div class="Metronic-alerts alert alert-danger">
                            <h4><i class="fa-lg fas fa-exclamation-triangle"></i>  Aggiornamenti in corso</h4>

                            <p>Gentile cliente, stiamo effettuando degli aggiornamenti alla tua piattaforma, il servizio potrebbe subire leggere interruzioni, ci scusiamo per il disagio.</p>
                        </div>
                    <?php endif; ?>

                    <?php echo $page; ?>
                </div>
            </div>
            <!-- END PAGE -->
            
        </div>
        <!-- END CONTAINER -->
        
        
        <?php echo $footer; ?>
        
    </body>
    <!-- END BODY -->
    
</html>