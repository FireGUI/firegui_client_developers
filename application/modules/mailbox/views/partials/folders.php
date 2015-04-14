<form id="folders-form" class="form-horizontal formAjax" method="POST" action="<?php echo base_url("mailbox/address"); ?>">
    <input type="hidden" name="configs" value="<?php echo $data['config_id']; ?>" />
    
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="myModalLabel">Gestisci indirizzi per la connessione</h4>
                </div>
                <div class="modal-body">
                    
                    <?php debug($data); ?>
                    
                    <div class="form-group">
                        <label class="control-label col-xs-3">NAME</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="folders[NAME][alias]" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn blue"><?php echo $data['edit']? 'Modifica': 'Crea'; ?></button>
                </div>
            </div>
        </div>
    </div>
    
</form>