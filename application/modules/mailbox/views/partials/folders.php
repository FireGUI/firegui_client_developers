<form id="folders-form" class="form-horizontal formAjax" method="POST" action="<?php echo base_url("mailbox/save_folders"); ?>">
    <input type="hidden" name="configs" value="<?php echo $data['config_id']; ?>" />

    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title" id="myModalLabel">Gestisci indirizzi per la connessione</h4>
                </div>
                <div class="modal-body">
                    <?php foreach ($data['folders'] as $k => $folder): ?>
                        <div class="panel panel-default">
                            <input type="hidden" name="folders[<?php echo $folder; ?>][mailbox_configs_folders_config]" value="<?php echo $data['config_id']; ?>" />
                            <div class="panel-heading">
                                <label style="cursor:pointer">
                                    <input class="js-folder-toggle" type="checkbox" data-folder-id="<?php echo $k; ?>" name="folders[<?php echo $folder; ?>][mailbox_configs_folders_attiva]" value="t" />
                                    &nbsp;
                                    Nome cartella: <strong><?php echo $folder; ?></strong>
                                </label>
                            </div>
                            <div class="panel-body js-folder-<?php echo $k ?>">
                                <div class="form-group">
                                    <label class="control-label col-xs-4">Alias</label>
                                    <div class="col-xs-8">
                                        <input type="text" class="form-control" name="folders[<?php echo $folder; ?>][mailbox_configs_folders_alias]" />
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="control-label col-xs-4">Tipo cartella</label>
                                    <div class="col-xs-8">
                                        <label class="radio">
                                            <input type="radio" name="folders[<?php echo $folder; ?>][mailbox_configs_folders_uscita]" value="f" />
                                            Posta in entrata
                                        </label>
                                        <label class="radio">
                                            <input type="radio" name="folders[<?php echo $folder; ?>][mailbox_configs_folders_uscita]" value="t" />
                                            Posta in uscita
                                        </label>
                                    </div>
                                </div>
                                
                            </div>
                        </div>

                    <?php endforeach; ?>
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
                
                $('input[name]', container).attr('disabled', !active);
                
            }).trigger('change');
        });

    </script>

</form>