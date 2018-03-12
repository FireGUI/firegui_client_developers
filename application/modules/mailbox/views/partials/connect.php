
<div class="portlet box blue">
    <div class="portlet-title ">
        <div class="caption">
            <i class=" fa fa-pencil-square-o"></i>
            <span class=" ">Connetti indirizzo e-mail</span>
        </div>
        <div class="tools"></div>
    </div>
    
    <div class="portlet-body">
        <div class="table-scrollable">
            <table id="js_dtable" class="table table-striped table-condensed table-bordered">
                <thead>
                    <tr>
                        <th>E-mail</th>
                        <th>Server</th>
                        <th>Porta</th>
                        <th>Protocollo</th>
                        <th>Encoding</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dati['configs'] as $config): ?>
                        <tr>
                            <td><?php echo $config['mailbox_configs_email']; ?></td>
                            <td><?php echo $config['mailbox_configs_server']; ?></td>
                            <td><?php echo $config['mailbox_configs_port']; ?></td>
                            <td><?php echo $config['mailbox_configs_protocol']; ?></td>
                            <td><?php echo $config['mailbox_configs_encoding']; ?></td>
                            <td>
                                <a href="<?php echo base_url("mailbox/folders/{$config['mailbox_configs_id']}"); ?>" class="js_open_modal btn btn-xs purple"><i class="fa fa-folder-open"></i></a>
                                <a href="<?php echo base_url("mailbox/form/{$config['mailbox_configs_id']}"); ?>" class="js_open_modal btn btn-xs blue"><i class="fa fa-edit"></i></a>
                                <a href="<?php echo base_url("mailbox/remove_email_account/{$config['mailbox_configs_id']}"); ?>" class="btn btn-xs red js_confirm_button" data-confirm-text="Eliminare tutte le e-mail associate a questo account?" ><i class="fa fa-remove"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
    $(function() {
        $('.js_confirm_button').on('click', function(e) {
            if (!confirm($(this).data('confirm-text'))) {
                e.preventDefault();
            }
        });
    })
</script>