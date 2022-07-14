<section class="content-header">

    <ol class="breadcrumb">
        <li> <?php e('Cache manager'); ?></li>

    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-10">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Cache status</h3>
                </div>

                <div class="box-body no-padding">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 10px">Cache</th>
                                <th>Driver</th>
                                <th>Status</th>

                                <th>Space occupied</th>
                                <th>Last update</th>
                                <th style="width: 40px">Actions</th>
                            </tr>
                            <?php foreach ($dati['caches'] as $key => $cache) : ?>
                            <tr>
                                <td><strong><?php echo $cache['label']; ?></strong></td>
                                <td><?php echo $cache['driver']; ?></td>
                                <td>
                                <div class="material-switch">
                                    <span style="display:none">
                                        <?php echo ($cache['active']) ? t('Yes') : t('No'); ?>
                                    </span>

                                    <input class="js_cache_switch_active" id="switch_active_<?php echo $key; ?>" value="<?php echo DB_BOOL_TRUE; ?>" name="switch_active[<?php echo $key; ?>]" data-key="<?php echo $key; ?>" type="checkbox" <?php if ($cache['active']) : ?> checked="checked" <?php endif; ?> />
                                    <label for="switch_active_<?php echo $key; ?>" class="label-success"></label>
                                    </div>
                                </td>
                                <td><?php echo human_filesize($cache['space']); ?></td>
                                <td><?php echo date('d-m-Y H:i:s',$cache['last_update']); ?></td>
                                <td>
                                        <a href="<?php echo base_url("main/cache_control/clear/".$key); ?>" class="btn btn-danger"><span class="fas fa-trash"></span></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            </div>

        </div>

    </div>

</section>

<script>
    $(() => {
        $('.js_cache_switch_active').on('change', function () {
            loading(true);
            var key = $(this).data('key');
            var value = $(this).is(':checked')?1:0;
            $.ajax({
                url: base_url + 'main/cache_switch_active/' + key + '/' + value,
                //dataType: 'json',
                complete: function () {
                    loading(false);
                },
                success: function (msg) {
                    handleSuccess(msg);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    var errorContainerID = 'ajax-error-container';
                    var errorContainer = $('#' + errorContainerID);

                    if (errorContainer.size() === 0) {
                        errorContainer = $('<div/>').attr('id', errorContainerID).css({
                            'z-index': 99999999,
                            'background-color': '#fff',
                        });
                        $('body').prepend(errorContainer);
                    }

                    errorContainer.html('Ajax error:' + xhr.responseText);
                },
            });
        });
    });
</script>