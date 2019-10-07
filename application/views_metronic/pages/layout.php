<?php 
$iconsMapForContentType = [
    'grid' => 'fas fa-th',
    'form' => 'fas fa-edit',
    'chart' => 'far fa-chart-bar',
    'map' => 'fas fa-map-marker-alt',
    'calendar' => 'fas fa-calendar-alt',
];

$portletBgColorMap = [
    
];

?>
<div class="layout-container" data-layout="<?php echo $dati['layout_container']['layouts_id']; ?>">
    <?php echo $dati['pre-layout']; ?>

    <?php if($dati['show_title']): ?>
        <h3 class="page-title clearfix">
            <?php echo ucfirst(str_replace(array('_', '-'), ' ', $dati['layout_container']['layouts_title'])) ?>
            <small><?php echo $dati['layout_container']['layouts_subtitle']; ?></small>
        </h3>
    <?php endif; ?>
    <?php foreach ($dati['layout'] as $row): ?>
        <div class="row">
            <?php $max_col = 0; ?>
            <?php foreach ($row as $layout): ?>
                <?php
                // =============================================================
                // Calcolo se andare alla prossima riga
                $max_col += $layout['layouts_boxes_cols'];
                if($max_col > 12) {
                    $max_col = $layout['layouts_boxes_cols'];
                    echo '</div><div class="row">'; 
                }
                
                // =============================================================
                // Calcolo classi e colori
                $titleColor = '';
                $baseClasses = [$layout['layouts_boxes_content_type']];
                $userClasses = array_filter(explode(' ', $layout['layouts_boxes_css']));
                $isLight = in_array('light', $userClasses);
                $isGren = in_array('gren', $userClasses);
                $isBox = in_array('box', $userClasses);
                
                if ($isLight OR $isGren) {
                    // Devo colorare il titolo
                    $titleColor = $layout['layouts_boxes_color'];
                } else {
                    // Devo colorare il portlet
                    $userClasses[] = $layout['layouts_boxes_color'];
                }
                
                if ($isLight OR $isGren OR $isBox) {
                    $baseClasses[] = 'portlet';
                }
                
                $classes = array_unique(array_merge($baseClasses, $userClasses));
                ?>
                <div class="<?php echo sprintf('col-lg-%s', $layout['layouts_boxes_cols']); ?>" data-layout-box="<?php echo $layout['layouts_boxes_id']; ?>">
                    
                    <div class="<?php echo implode(' ', $classes); ?>">
                        <?php if($layout['layouts_boxes_titolable'] === DB_BOOL_TRUE): ?>
                            <div class="portlet-title <?php echo ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE) ? 'js_title_collapse' : ''; ?>">
                                <div class="caption">
                                    <i class="<?php echo $titleColor ? 'font-' . $titleColor:''; ?> <?php echo isset($iconsMapForContentType[$layout['layouts_boxes_content_type']]) ? $iconsMapForContentType[$layout['layouts_boxes_content_type']] : 'fas fa-bars'; ?>"></i>
                                    <span class="<?php echo ($titleColor ? 'font-' . $titleColor:'') . ' ' . ($isLight ? 'caption-subject bold uppercase': ''); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])); ?>
                                    </span>
                                </div>
                                <div class="tools">
                                    <?php if ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE): ?><a href="javascript:;" class="<?php echo ($layout['layouts_boxes_collapsed']==DB_BOOL_TRUE)? 'expand': 'collapse'; ?>"></a><?php endif; ?>
                                    <?php if ($layout['layouts_boxes_discardable'] === DB_BOOL_TRUE): ?><a href="javascript:;" class="remove"></a><?php endif; ?>
                                    <?php if ($layout['layouts_boxes_reloadable'] === DB_BOOL_TRUE): ?><a href="javascript:;" class="reload"></a><?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="portlet-body <?php echo $layout['layouts_boxes_content_type'] ?> <?php echo ($layout['layouts_boxes_collapsible']==DB_BOOL_TRUE && $layout['layouts_boxes_collapsed']==DB_BOOL_TRUE) ? 'display-hide' : ''; ?>">
                            <?php echo $layout['content'] ?>
                        </div>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>


    <?php echo $dati['post-layout']; ?>


    <?php if(isset($layout['layouts_fullscreen']) && $layout['layouts_fullscreen'] === DB_BOOL_TRUE): ?>
        <script>

            $(document).ready(function() {
                $('body').addClass('page-sidebar-closed').find('.page-sidebar-menu').addClass('page-sidebar-menu-closed');
            });

        </script>
    <?php endif; ?>
</div>
