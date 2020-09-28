<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>

        <div class="col-md-12 js_barcode_grid" grid_id="<?php echo $grid['grids']['grids_id']; ?>">
            <?php foreach ($grid['grids_fields'] as $field) : ?>
                <div class="col-md-12">
                    <?php
                    $field_label = $field['grids_fields_column_name'];
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();

                    foreach ([$generator::TYPE_CODE_128, $generator::TYPE_EAN_13] as $type) :
                    ?>
                        <?php
                        // debug($type);
                        // debug($dato[$field['fields_name']]);
                        switch ($type) {
                            case $generator::TYPE_EAN_13:
                                $value = preg_replace('/\D/', '', $dato[$field['fields_name']]);
                                $value_no_check = $value; //substr($value, 0, 12);
                                //dd($generator);
                                break;
                            default:
                                $value = $dato[$field['fields_name']];
                                $value_no_check = $value;
                                break;
                        }
                        if (!$value) {
                            continue;
                        }
                        $base64img = base64_encode($generator->getBarcode($value_no_check, $type));
                        ?>
                        <div class="text-center js_barcode_container" style="margin-bottom:56px;" data-type="<?php echo $type; ?>" data-value="<?php echo base64_encode($value); ?>" data-url="main/print_barcode/">

                            <div><strong><?php echo $type; ?></strong></div>
                            <div><label><?php e($field_label); ?></label></div>

                            <div><img src="data:image/png;base64,<?php echo ($base64img); ?>" /><br /></div>
                            <div><small><?php echo $value; ?></small></div>

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
                    <?php endforeach; ?>
                    <hr />
                </div>
            <?php endforeach; ?>
        </div>



    <?php endforeach; ?>

    <script>
        $(document).ready(function() {
            'use strict';
            //$('.js_barcode_grid').hide();

            $('.js_print').on('click', function() {
                var container = $(this).closest('.js_barcode_container');
                var w = $('[name="w"]', container).val();
                var h = $('[name="h"]', container).val();
                var left = $('[name="left"]', container).val();
                var top = $('[name="top"], container').val();
                var type = container.data('type');
                var value = container.data('value');
                var pars = '&w=' + w + '&h=' + h + '&left=' + left + '&top=' + top;
                var url = '<?php echo base_url(); ?>' + container.data('url') + type + '/?val=' + value + pars;
                window.open(url, '_blank');
            });

        });
    </script>

<?php endif; ?>