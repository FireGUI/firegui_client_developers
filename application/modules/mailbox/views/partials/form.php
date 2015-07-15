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
                            <input type="text" class="form-control" name="configs[mailbox_configs_port]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_port']: null; ?>" placeholder="143" />
                            <span class="help-block">
                                Porte standard IMAP: <strong>143</strong> in IMAP standard,
                                <strong>993</strong> in IMAPS (IMAP over SSL)
                                <strong>110</strong> in POP3
                            </span>
                        </div>
                    </div>

                    <?php /*
                    <div class="form-group">
                        <label class="control-label col-xs-3">Protocol</label>
                        <div class="col-xs-9">
                            <input type="text" class="form-control" name="configs[mailbox_configs_protocol]" value="<?php echo $data['edit']? $data['address']['mailbox_configs_protocol']: null; ?>" placeholder="/imap" />
                        </div>
                    </div>
                     */ ?>
                    <?php $selected = array_filter($data['edit']? explode('/', trim($data['address']['mailbox_configs_protocol'], '/')): []); ?>
                    <div class="form-group">
                        <label class="control-label col-xs-3">Protocollo</label>
                        <div class="col-xs-9">
                            <label class="radio-inline">
                                <input type="radio" name="flags[protocol]" value="imap" <?php echo in_array('imap', $selected)? 'checked':''; ?> />
                                IMAP <small>default</small>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="flags[protocol]" value="pop3" <?php echo in_array('pop3', $selected)? 'checked':''; ?> />
                                POP3
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="flags[protocol]" value="nntp" <?php echo in_array('nntp', $selected)? 'checked':''; ?> />
                                NNTP
                            </label>
                        </div>
                    </div>
                            
                    <div class="form-group">
                        <label class="control-label col-xs-3">Certificato</label>
                        <div class="col-xs-9">
                            <label class="radio-inline">
                                <input type="radio" name="flags[cert_validation]" value="validate-cert" <?php echo in_array('validate-cert', $selected)? 'checked':''; ?> />
                                Valida certificato
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="flags[cert_validation]" value="novalidate-cert" <?php echo in_array('novalidate-cert', $selected)? 'checked':''; ?> />
                                Non validare certificato
                            </label>
                        </div>
                    </div>
                            
                    <div class="form-group">
                        <label class="control-label col-xs-3">Flag vari</label>
                        <div class="col-xs-9" style="padding-left:35px;">
                            <label class="checkbox">
                                <input type="checkbox" name="flags[flags][]" value="anonymous" <?php echo in_array('anonymous', $selected)? 'checked':''; ?> />
                                Accesso come utente anonimo
                            </label>
                            <label class="checkbox">
                                <input type="checkbox" name="flags[flags][]" value="secure" <?php echo in_array('secure', $selected)? 'checked':''; ?> />
                                Non trasmette la password in chiaro
                            </label>
                            <label class="checkbox">
                                <input type="checkbox" name="flags[flags][]" value="norsh" <?php echo in_array('norsh', $selected)? 'checked':''; ?> />
                                Non usare RSH o SSH per preautenticazione di sessione IMAP
                            </label>
                            <label class="checkbox">
                                <input type="checkbox" name="flags[flags][]" value="ssl" <?php echo in_array('ssl', $selected)? 'checked':''; ?> />
                                Usa ssl
                            </label>
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