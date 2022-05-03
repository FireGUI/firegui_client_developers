<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>

        <div class="col-md-12 js_barcode_grid" grid_id="<?php echo $grid['grids']['grids_id']; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
                <div class="col-md-12">
                    <?php
                    $field_label = $field['grids_fields_column_name'];
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

                    if (empty($dato[$field['fields_name']])) {
                        echo '<div class="callout callout-info">' . t('No barcode provided.') . '</div>';
                        continue;
                    }

                    $type = $generator::TYPE_EAN_13;

                    $barcodes = json_decode($dato[$field['fields_name']]);

                    //     $barcode_img = base64_encode($generator->getBarcode($barcode, $type));
                    ?>

                    <table class="table table-condensed">
                        <tbody>
                            <?php foreach ($barcodes as $barcode) : ?>
                                <tr>
                                    <td><?php echo $barcode; ?></td>
                                    <td><button type="button" class="btn btn-sm btn-info btn-view" data-barcode="<?php echo $barcode; ?>" data-barcode_b64="<?php echo base64_encode($barcode); ?>" data-barcode_img="data:image/png;base64,<?php echo base64_encode($generator->getBarcode($barcode, $type)); ?>"><i class="fas fa-eye fa-fw"></i></button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="text-center js_barcode_container hide" data-type="<?php echo $type; ?>" data-value="" data-url="main/print_barcode/">

                        <div><strong><?php echo $type; ?></strong></div>

                        <div><img class="barcode_img" /><br /></div>
                        <div><small class="barcode_label"></small></div>

                        <div class="row">
                            <div class="col-md-12 ">
                                Misure:
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>w:</label>
                                    <input class="form_control" type="text" name="w" size="3" /> mm
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>h:</label>
                                    <input class="form_control" type="text" name="h" size="3" /> mm
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>left:</label>
                                    <input class="form_control" type="text" name="left" size="3" /> mm
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>top:</label> <input class="form_control" type="text" name="top" size="3" /> mm
                                </div>
                            </div>

                            <a class="btn btn-default js_print" href="javascript:void(0);"><?php e('Print'); ?></a>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <script>
        $(document).ready(function() {
            'use strict';

            $('.btn-view').on('click', function() {
                var barcode_ct = $('.js_barcode_container', $('.js_barcode_grid'));

                barcode_ct.find('img.barcode_img').prop('src', '');
                barcode_ct.find('small.barcode_label').text('');
                barcode_ct.prop('data-value', '');

                var btn = $(this);

                var barcode = btn.data('barcode');
                var barcode_b64 = btn.data('barcode_b64');
                var barcode_img = btn.data('barcode_img');


                barcode_ct.find('img.barcode_img').prop('src', barcode_img);
                barcode_ct.find('small.barcode_label').text(barcode);
                barcode_ct.attr('data-value', barcode_b64);

                barcode_ct.removeClass('hide');

                $('.js_print').on('click', function() {
                    var container = $(this).closest('.js_barcode_container');
                    var w = $('[name="w"]', container).val();
                    var h = $('[name="h"]', container).val();
                    var left = $('[name="left"]', container).val();
                    var top = $('[name="top"]', container).val();
                    var type = container.data('type');
                    var value = container.attr('data-value');
                    var pars = '&w=' + w + '&h=' + h + '&left=' + left + '&top=' + top;
                    var url = '<?php echo base_url(); ?>' + container.data('url') + type + '/?val=' + value + pars;
                    window.open(url, '_blank');
                });
            });
        });
    </script>
<?php endif; ?>