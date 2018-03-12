<h3 class="page-title">Mail-box</h3>



<div class="row" style="margin-bottom:10px">
    <div class="col-md-12">
        <a href="<?php echo base_url('mailbox/form'); ?>" class="js_open_modal btn blue-steel">
            <i class="fa fa-plus"></i>
            &nbsp;
            Configura nuova casella
        </a>
        <a href="<?php echo base_url('mailbox/configure'); ?>" class="btn blue-steel">
            <i class="fa fa-cogs"></i>
            &nbsp;
            Configurazione
        </a>
    </div>
</div>


<div class="row" style="margin-top: 30px">
    <div class="col-md-12">
        <?php echo $this->load->view('partials/widget'); ?>
    </div>
</div>