<form id="configs-form" class="form-horizontal formAjax" method="POST" action="<?php echo base_url($data['edit']? "mailbox/address/{$data['address']['mailbox_configs_id']}": "mailbox/address"); ?>">
    <input type="hidden" name="configs[mailbox_configs_user]" value="<?php echo $this->auth->get('id'); ?>" />
    
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="myModalLabel"><?php echo $data['edit']? 'Modifica connessione': 'Nuova connessione'; ?></h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label col-xs-3">E-mail</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="configs[mailbox_configs_email]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_email']: null; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3">Password</label>
                        <div class="col-xs-9">
                            <input type="password" class="form-control" name="configs[mailbox_configs_password]" placeholder="<?php echo $data['edit']? 'Lascia vuoto per mantenere': ''; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3">Server</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="configs[mailbox_configs_server]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_server']: null; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3">Port</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="configs[mailbox_configs_port]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_port']: null; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3">Protocol</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="configs[mailbox_configs_protocol]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_protocol']: null; ?>" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="control-label col-xs-3">Encoding</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="configs[mailbox_configs_encoding]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_encoding']: null; ?>" />
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