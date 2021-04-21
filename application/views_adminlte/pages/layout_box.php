<?php
$titleColor = '';
$baseClasses = [$layout['layouts_boxes_content_type']];
$userClasses = array_filter(explode(' ', $layout['layouts_boxes_css']));
$isLight = in_array('light', $userClasses);
$isGren = in_array('gren', $userClasses);
$isBox = in_array('box', $userClasses);

if ($isLight or $isGren) {
    // Devo colorare il titolo
    $titleColor = $layout['layouts_boxes_color'];
} else {
    // Devo colorare il portlet
    $userClasses[] = $layout['layouts_boxes_color'];
}

if ($isLight or $isGren or $isBox) {
    $baseClasses[] = 'portlet';
}

$classes = array_unique(array_merge($baseClasses, $userClasses));
?>

<div class="<?php if ($layout['layouts_boxes_show_container'] === DB_BOOL_TRUE) : ?>box<?php endif; ?> <?php echo $layout['layouts_boxes_css'] . " " . $layout['layouts_boxes_color']; ?> <?php echo ($layout['layouts_boxes_collapsed'] === DB_BOOL_TRUE) ? 'collapsed-box' : ''; ?>">

    <?php if ($layout['layouts_boxes_titolable'] === DB_BOOL_TRUE) : ?>

        <div class="box-header with-border <?php echo ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE) ? 'js_title_collapse' : ''; ?> ">

            <div class="box-title">
                <i class="<?php echo $titleColor ? 'font-' . $titleColor : ''; ?> <?php echo isset($iconsMapForContentType[$layout['layouts_boxes_content_type']]) ? $iconsMapForContentType[$layout['layouts_boxes_content_type']] : 'fas fa-bars'; ?>"></i>
                <span class="<?php echo ($titleColor ? 'font-' . $titleColor : '') . ' ' . ($isLight ? 'caption-subject bold uppercase' : ''); ?>">

                    <?php e(ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])), true, ['module_name' => $layout['layouts_module']]); ?>
                </span>
            </div>

            <div class="box-tools">

                <?php if ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE) : ?><button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fas <?php if ($layout['layouts_boxes_collapsed'] === DB_BOOL_TRUE) : ?>fa-plus<?php else : ?>fa-minus<?php endif; ?>"></i></button><?php endif; ?>
                <?php if ($layout['layouts_boxes_discardable'] === DB_BOOL_TRUE) : ?><button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fas fa-times"></i></button><?php endif; ?>
                <?php if ($layout['layouts_boxes_reloadable'] === DB_BOOL_TRUE) : ?><a href="javascript:;" class="reload"></a><?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="box-body layout_box <?php echo $layout['layouts_boxes_content_type'] ?> <?php echo ($layout['layouts_boxes_collapsible'] == DB_BOOL_TRUE && $layout['layouts_boxes_collapsed'] == DB_BOOL_TRUE) ? 'display-hide' : ''; ?>" data-layout-box="<?php echo $layout['layouts_boxes_id']; ?>" data-value_id="<?php echo $value_id; ?>">
        <?php echo $layout['content'] ?>
    </div>
</div>