<?php if (isset($grid_data['data'])) : ?>
    <?php foreach ($grid_data['data'] as $dato) : ?>

        <div class="col-md-12">
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
                                dd($generator);
                                break;
                            default:
                                $value = $dato[$field['fields_name']];
                                break;
                        }
                        if (!$value) {
                            continue;
                        }
                        ?>
                        <div>

                            <div class="text-center"><strong><?php echo $type; ?></strong></div>
                            <div class="text-center"><label><?php echo $field_label; ?></label></div>

                            <div class="text-center"><img src="data:image/png;base64,<?php echo (base64_encode($generator->getBarcode($value, $type))); ?>" /><br /></div>
                            <div class="text-center"><small><?php echo $value; ?></small></div>
                            <a class="btn btn-default" href="<?php echo base_url('main/print_barcode/' . $type . '/?val=' . base64_encode($value)); ?>" target="_blank"><?php e('Print'); ?></a>
                        </div>
                    <?php endforeach; ?>
                    <hr />
                </div>
            <?php endforeach; ?>
        </div>

    <?php endforeach; ?>
<?php endif; ?>