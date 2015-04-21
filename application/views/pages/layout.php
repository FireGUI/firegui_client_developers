
<?php echo $dati['pre-layout']; ?>

<?php if($dati['show_title']): ?>
    <?php if($this->session->userdata('login_h2') OR gethostname() === 'sfera'): ?><a class="pull-right" target="_blank" style="opacity:0.5" href="<?php echo "http://sfera.h2-web.com/dev/masterTool/main/layouts/{$dati['layout_container']['layouts_id']}"; ?>">modifica layout</a><?php endif; ?>
    <h3 class="page-title">
        <?php echo ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title'])) ?>
        <small><?php echo $dati['layout_container']['layouts_subtitle']; ?></small>
    </h3>
<?php endif; ?>
<?php foreach ($dati['layout'] as $row): ?>
    <div class="row">
        <?php $max_col = 0; ?>
        <?php foreach ($row as $layout): ?>
            <?php
            $max_col += $layout['layouts_boxes_cols'];
            if($max_col > 12) {
                $max_col = $layout['layouts_boxes_cols'];
                echo '</div><div class="row">'; 
            }
            ?>
            <div class="col-md-<?php echo $layout['layouts_boxes_cols'] ?>">
                <div class="portlet <?php if($layout['layouts_boxes_content_type']=='form') echo "form" ?> <?php echo $layout['layouts_boxes_color']; ?> <?php echo $layout['layouts_boxes_css'] ?>">
                    <?php if($layout['layouts_boxes_titolable'] === 't'): ?>
                        <div class="portlet-title <?php if($layout['layouts_boxes_collapsible'] === 't') echo 'js_title_collapse' ?>">
                            <div class="caption">
                                <i class="icon-reorder"></i> <?php echo ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])); ?>
                            </div>
                            <div class="tools">
                                <?php if ($layout['layouts_boxes_collapsible'] === 't'): ?>
                                    <a href="javascript:;" class="<?php echo ($layout['layouts_boxes_collapsed']=='t'? 'expand': 'collapse'); ?>"></a>
                                <?php endif; ?>

                                <?php if ($layout['layouts_boxes_discardable'] === 't'): ?>
                                    <a href="javascript:;" class="remove"></a>
                                <?php endif; ?>

                                <?php if ($layout['layouts_boxes_reloadable'] === 't'): ?>
                                    <a href="javascript:;" class="reload"></a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="portlet-body <?php if($layout['layouts_boxes_content_type']=='form') echo "form" ?> <?php if($layout['layouts_boxes_collapsible']=='t' AND $layout['layouts_boxes_collapsed']=='t') echo 'display-hide'; ?>">
                        <?php echo $layout['content'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>


<?php echo $dati['post-layout']; ?>


<?php if(isset($layout['layouts_fullscreen']) && $layout['layouts_fullscreen'] === 't'): ?>
    <script>

        $(document).ready(function() {
            $('body').addClass('page-sidebar-closed');
        });

    </script>
<?php endif; ?>