<?php if (isset($grid_data['data'])): ?>

    <div class="image-row">
        <?php foreach ($grid_data['data'] as $k => $dato): ?>
        <div class="image-set" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>" style="display: inline-block;margin-bottom: 6px;">
                <?php foreach ($grid['grids_fields'] as $field): ?>
                    <?php if (in_array($field['fields_draw_html_type'], ['upload_image'])): ?>
                        <?php
                        $file = empty($dato[$field['fields_name']]) ? '' : $dato[$field['fields_name']];

                        // Valuta estensione del file - se non è jpg o png linka il file
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        $link_url = base_url_template("uploads/{$dato[$field['fields_name']]}");
                        switch (strtolower($ext)) {
                            case 'png': case 'jpg': case 'jpeg': case 'bmp':
                                $img_url = base_url_template("imgn/1/150/150/uploads/{$dato[$field['fields_name']]}");
                                break;
                            case '':
                                $img_url = 'http://www.placehold.it/75x75/EFEFEF/AAAAAA&text=no+image';
                                break;
                            default :
                                $img_url = base_url_template("images/document.png");
                                break;
                        }
                        ?>

                        <img class="img-thumbnail" src='<?php echo $img_url; ?>' title='<?php echo $file; ?>' style='height:75px' />
                    <?php endif; ?>

                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<div class="clearfix"></div>
