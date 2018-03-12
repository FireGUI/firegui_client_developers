<h3 class="page-title">Configurazione mail-box</h3>



<div class="row" style="margin-bottom:10px">
    <div class="col-md-12">
        <a href="<?php echo base_url('mailbox'); ?>" class="btn yellow-crusta">
            <i class="fa fa-envelope-o"></i>
            &nbsp;
            Torna alla mailbox
        </a>
        <a href="<?php echo base_url('mailbox/form'); ?>" class="js_open_modal btn blue-steel">
            <i class="fa fa-plus"></i>
            &nbsp;
            Configura nuova casella
        </a>
    </div>
</div>



<div class="row">
    <div class="col-md-9">
        <?php echo $this->load->view('partials/connect'); ?>
    </div>
</div>
