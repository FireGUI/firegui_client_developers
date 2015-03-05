<h3 class="page-title">Importer <small>import result</small></h3>


<div class="row">
    <div class="col-md-4">
        <div class="portlet box blue">
            <div class="portlet-title">
                <div class="caption">
                    <i class="icon-upload-alt"></i> Import over
                </div>
            </div>
            <div class="portlet-body">
                <?php if($dati['count']==1): ?>
                    <?php echo $dati['count']; ?> record imported.
                <?php else: ?>
                    <?php echo (int) $dati['count']; ?> records imported.
                <?php endif; ?>
                    
                    
                <br/>
                <br/>
                <a href="<?php echo base_url('importer'); ?>">Back</a>
            </div>
        </div>
    </div>
</div>
