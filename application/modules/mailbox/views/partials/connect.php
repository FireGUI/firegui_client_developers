<div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="icon-list-alt"></i> Connetti indirizzo e-mail
        </div>
    </div>

    <div class="portlet-body">
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
                            <a href="<?php echo base_url("mailbox/folders/{$config['mailbox_configs_id']}"); ?>" class="js_open_modal btn btn-xs purple"><i class="icon-folder-open"></i></a>
                            <a href="<?php echo base_url("mailbox/form/{$config['mailbox_configs_id']}"); ?>" class="js_open_modal btn btn-xs blue"><i class="icon-edit"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>