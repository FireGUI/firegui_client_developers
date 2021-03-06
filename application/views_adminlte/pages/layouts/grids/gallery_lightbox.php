<?php if (isset($grid_data['data'])) : ?>

    <div class="image-row">
        <?php foreach ($grid_data['data'] as $k => $dato) : ?>
            <div class="image-set" data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                    <?php if (in_array($field['fields_draw_html_type'], array('upload_image', 'upload'))) : ?>

                        <?php
                        $file = empty($dato[$field['fields_name']]) ? '' : $dato[$field['fields_name']];

                        // Valuta estensione del file - se non è jpg o png linka il file
                        $ext = pathinfo($file, PATHINFO_EXTENSION);
                        $link_url = base_url_uploads("uploads/{$dato[$field['fields_name']]}");

                        switch (strtolower($ext)) {
                            case 'png':
                            case 'jpg':
                            case 'jpeg':
                            case 'bmp':
                                $img_url = ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) ? base_url_uploads("uploads/thumbnails/{$dato[$field['fields_name']]}") : base_url_admin("imgn/1/150/150/uploads/{$dato[$field['fields_name']]}");
                                $attributes = ['data-lightbox' => "grid_gallery_{$grid['grids']['grids_id']}"];
                                break;
                            case '':
                                $img_url = base_url() . 'imgn/1/350/350/images/no_image.png';
                                $attributes = [];
                                break;
                            default:
                                $img_url = base_url_admin("images/document.png");
                                $attributes = ['download' => $dato[$field['fields_name']], 'style' => 'height: 75px'];
                                break;
                        }

                        echo anchor($link_url, "<img class='img-thumbnail' src='{$img_url}' title='{$file}' />", $attributes);
                        ?>

                    <?php endif; ?>


                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>


<div class="clearfix"></div>