<?php
$titleColor = '';
$baseClasses = [$layout['layouts_boxes_content_type']];
$userClasses = array_filter(explode(' ', $layout['layouts_boxes_css']));

$classes = array_unique(array_merge($baseClasses, $userClasses));
//If is a filter form and a filter is setup, bypass collapsed parameter
if ($layout['layouts_boxes_collapsed'] === DB_BOOL_TRUE && $layout['layouts_boxes_content_type'] == 'form') {
    $form = $this->datab->get_form($layout['layouts_boxes_content_ref']);
    if ($form['forms']['forms_layout'] == 'filter_select') {
        $sess_data = $this->session->userdata(SESS_WHERE_DATA) ?: [];
        $filterSessionKey = $form['forms']['forms_filter_session_key'];
        $_sess_where_data = array_get($sess_data, $filterSessionKey, []);

        if (!empty($_sess_where_data)) {
            $layout['layouts_boxes_collapsed'] = DB_BOOL_FALSE;
        }
    }
}

if ($layout['layouts_boxes_show_container'] === DB_BOOL_TRUE) {
    $classes[] = 'card';
}

if ($layout['layouts_boxes_collapsed'] === DB_BOOL_TRUE) {
    $classes[] = 'collapsed-card';
}

$classes[] = $layout['layouts_boxes_color'];

?>

<style>
    .hide {
        display: none;
    }
</style>

<div data-id="<?php echo $layout['layouts_boxes_id']; ?>" data-row="<?php echo $layout['layouts_boxes_row']; ?>" class="js_layout_box <?php echo implode(' ', $classes); ?>">
    <label class="label_highlight hide label_highlight_lb"> Layout Box #<?php echo $layout['layouts_boxes_id']; ?> <?php echo $layout['layouts_boxes_title'] . " [" . $layout['layouts_boxes_content_type'] . "]"; ?></label>

    <div class="builder_toolbar_actions hide">
        <!-- Builder actions -->
        <button type="button" class="btn btn-box-tool js_builder_toolbar_btn" data-action="edit_layout_box" data-element-type="layout" data-element-ref="<?php echo $layout['layouts_boxes_id'] ?>" data-toggle="tooltip" title="" data-original-title="Edit box">
            <i class="fas fa-cogs"></i>
        </button>

        <button type="button" class="btn btn-box-tool js_builder_toolbar_btn" data-action="option" data-element-type="<?php echo $layout['layouts_boxes_content_type'] ?>" data-element-ref="<?php echo $layout['layouts_boxes_content_ref'] ?>" data-toggle="tooltip" title="" data-original-title="<?php echo $layout['layouts_boxes_content_type'] ?> option">
            <i class="fas fa-edit"></i>
        </button>

        <button type="button" class="btn btn-box-tool js_builder_toolbar_btn " data-action="builder" data-element-type="<?php echo $layout['layouts_boxes_content_type'] ?>" data-element-ref="<?php echo $layout['layouts_boxes_content_ref'] ?>" data-toggle="tooltip" title="" data-original-title="<?php echo $layout['layouts_boxes_content_type'] ?> builder">
            <i class="fas fa-hat-wizard"></i>
        </button>

        <button href="javascript:void(0);" class="btn btn-box-tool js_btn_minus" data-toggle="tooltip" data-original-title="- columns">
            <i class="fas fa-caret-left"></i>
        </button>
        Size
        <button href="javascript:void(0);" class="btn btn-box-tool js_btn_plus" data-toggle="tooltip" data-original-title="+ columns">
            <i class="fas fa-caret-right"></i>
        </button>

        <button href="javascript:void(0);" class="btn btn-box-tool js_confirm_button js_btn_delete" data-toggle="tooltip" data-original-title="delete box">
            <i class="fas fa-trash"></i>
        </button>

        <button href="javascript:void(0);" class="btn btn-box-tool js_btn_move_to_layout" data-toggle="tooltip" data-original-title="Move to another layout">
            <i class="fas fa-sign-out-alt"></i>
        </button>

    </div>

    <!-- End Builder actions -->

    <?php if ($layout['layouts_boxes_titolable'] === DB_BOOL_TRUE) : ?>
        <div class="card-header">
            <h3 class="card-title">
                <i class="<?php echo $titleColor ? 'font-' . $titleColor : ''; ?> <?php echo isset($iconsMapForContentType[$layout['layouts_boxes_content_type']]) ? $iconsMapForContentType[$layout['layouts_boxes_content_type']] : 'fas fa-bars fa-fw'; ?>"></i>
                <span data-layou_box_id="<?php echo $layout['layouts_boxes_id']; ?>" class="js_layouts_boxes_title <?php echo ($titleColor ? 'font-' . $titleColor : ''); ?>">
                    <?php e(ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])), true, ['module_name' => $layout['layouts_module']]); ?>
                </span>
            </h3>
            
            <div class="card-tools">
                <?php if ($layout['layouts_boxes_collapsible'] === DB_BOOL_TRUE) : ?>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas <?php if ($layout['layouts_boxes_collapsed'] === DB_BOOL_TRUE) : ?>fa-plus<?php else : ?>fa-minus<?php endif; ?> fa-fw"></i>
                    </button>
                <?php endif; ?>

                <?php if ($layout['layouts_boxes_discardable'] === DB_BOOL_TRUE) : ?>
                    <button type="button" class="btn btn-tool" data-card-widget="remove">
                        <i class="fas fa-times fa-fw"></i>
                    </button>
                <?php endif; ?>

                <?php if ($layout['layouts_boxes_reloadable'] === DB_BOOL_TRUE) : ?>
                    <a href="javascript:;" class="reload"></a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
            
    <div class="<?php echo ($layout['layouts_boxes_show_container'] === DB_BOOL_TRUE) ? 'card-body' : 'pb-3'; ?> layout_box <?php echo $layout['layouts_boxes_content_type'] ?> <?php echo ($layout['layouts_boxes_collapsible'] == DB_BOOL_TRUE && $layout['layouts_boxes_collapsed'] == DB_BOOL_TRUE) ? 'display-hide' : ''; ?>" data-layout-box="<?php echo $layout['layouts_boxes_id']; ?>" data-value_id="<?php echo $value_id; ?>" data-get_pars="<?php echo $_SERVER['QUERY_STRING']; ?>">
        <?php echo $layout['content'] ?>
    </div>
</div>