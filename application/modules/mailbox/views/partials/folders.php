<form id="folders-form" class="formAjax form-horizontal" method="POST" action="<?php echo base_url("mailbox/save_folders"); ?>">
    <input type="hidden" name="configs" value="<?php echo $data['config_id']; ?>" />

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="myModalLabel">Gestisci indirizzi per la connessione</h4>
                </div>
                <div class="modal-body">
                    <?php if (isset($data['error'])): ?>
                        <div class="alert alert-danger">
                            <strong>I parametri di connessione non sono corretti</strong><br/>
                            <?php echo $data['error']; ?>
                        </div>
                    <?php else: ?>
                        <?php foreach ($data['folders'] as $k => $folder): ?>
                            <?php $real = isset($data['registered'][$folder])? $data['registered'][$folder]: []; ?>
                            <div class="row">
                                <div class="col-xs-12">
                                    <input type="hidden" name="folders[<?php echo $k; ?>][mailbox_configs_folders_name]" value="<?php echo $folder; ?>" />
                                    <label class="checkbox-inline">
                                        <input type="hidden" name="folders[<?php echo $k; ?>][mailbox_configs_folders_attiva]" value="f" />
                                        <input class="js-folder-toggle" type="checkbox" data-folder-id="<?php echo $k; ?>" name="folders[<?php echo $k; ?>][mailbox_configs_folders_attiva]" value="t" <?php echo (isset($real['mailbox_configs_folders_attiva']) && $real['mailbox_configs_folders_attiva']==='t')? 'checked': ''; ?> />
                                        <?php echo $folder; ?>
                                    </label>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-xs-12 <?php echo 'js-folder-'.$k ?>">
                                    <div class="form-group">
                                        <label class="control-label col-xs-2">Alias</label>
                                        <div class="col-xs-8">
                                            <input type="text" class="form-control input-sm" name="folders[<?php echo $k; ?>][mailbox_configs_folders_alias]" value="<?php echo (isset($real['mailbox_configs_folders_alias']) && $real['mailbox_configs_folders_alias']!==$folder)? $real['mailbox_configs_folders_alias']: null; ?>" />
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label col-xs-2">Tipo cartella</label>
                                        <div class="col-xs-10">
                                            <label class="radio-inline">
                                                <input type="radio" name="folders[<?php echo $k; ?>][mailbox_configs_folders_uscita]" value="f" <?php if(empty($real['mailbox_configs_folders_uscita']) OR $real['mailbox_configs_folders_uscita']==='f') echo 'checked' ?> />
                                                Posta in entrata
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="folders[<?php echo $k; ?>][mailbox_configs_folders_uscita]" value="t" <?php if(isset($real['mailbox_configs_folders_uscita']) && $real['mailbox_configs_folders_uscita']==='t') echo 'checked' ?> />
                                                Posta in uscita
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button class="btn blue">Salva cartelle</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        
        $(document).ready(function() {
            $('.js-folder-toggle').on('change', function() {
                
                var checkb = $(this);
                var active = checkb.is(':checked');
                var container = $('.js-folder-' + checkb.data('folder-id'));

                if (active) {
                    container.slideDown();
                } else {
                    container.slideUp();
                }
                
                //$('input[name]', container).attr('disabled', !active);
                
            }).trigger('change');
        });

    </script>

</form>