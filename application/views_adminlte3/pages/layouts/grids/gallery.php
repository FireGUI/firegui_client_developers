<?php if (isset($grid_data['data'])) : ?>
    <?php $has_action = grid_has_action($grid['grids']); ?>
    <?php if (strpos($grid['grids']['entity_name'], 'media_') === 0 && !empty($value_id)) : ?>
        <ul class="list-inline text-right" style='padding:0;margin:0'>
            <li><a href="<?php echo base_url("media/modal_upload/{$grid['grids']['entity_name']}/{$value_id}"); ?>" class="btn green js_open_modal"><i class="fas fa-upload"></i> Carica media</a></li>
        </ul>
        <div class="clearfix"></div>
        <br />
    <?php endif; ?>

    <ul class="list-inline">
        <?php foreach ($grid_data['data'] as $k => $dato) : ?>
            <li data-id="<?php echo $dato[$grid['grids']['entity_name'] . "_id"]; ?>">
                <?php foreach ($grid['grids_fields'] as $field) : ?>
                    <?php if (in_array($field['fields_draw_html_type'], array('upload_image', 'upload'))) : ?>

                        <div class="upload-thumbnail thumbnail text-center">
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

                                    $img_url = ($this->config->item('cdn') && $this->config->item('cdn')['enabled']) ? base_url_uploads("uploads/thumbnails/{$dato[$field['fields_name']]}") : base_url_admin("imgn/1/75/75/uploads/{$dato[$field['fields_name']]}");
                                    $attributes = array('class' => 'js_thumbnail', 'rel' => "grid_gallery_{$grid['grids']['grids_id']}", 'style' => 'height: 75px');
                                    break;

                                case '':
                                    $img_url = base_url() . 'imgn/1/350/350/images/no_image.png';
                                    $attributes = array('style' => 'height: 75px');
                                    break;

                                default:
                                    $img_url = base_url_admin("images/document.png");
                                    $attributes = array('download' => $dato[$field['fields_name']], 'style' => 'height: 75px');
                                    break;
                            }

                            echo anchor($link_url, "<img src='{$img_url}' data-toggle='tooltip' title='{$file}' style='height:75px' />", $attributes);
                            ?>
                            <?php if ($has_action) : ?>
                                <div class="text-right">
                                    <?php $this->load->view('box/grid/actions', array('links' => $grid['grids']['links'], 'id' => $dato[$grid['grids']['entity_name'] . "_id"], 'row_data' => $dato)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php else : ?>
                        <?php echo $this->datab->build_grid_cell($field, $dato); ?>
                    <?php endif; ?>

                    <br />
                <?php endforeach; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>


<div class="clearfix"></div>