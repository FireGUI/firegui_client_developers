<?php foreach ($data as $link): ?>
    <a href="<?php echo $this->datab->generate_menu_link($link, $value_id, $layout_data_detail); ?>"
       <?php echo ($link['layouts_pdf']==DB_BOOL_TRUE) ? 'target="_blank"': ''; ?>
       class="icon-btn menu-<?php echo $link['menu_id'] ?>">
        <i class="<?php echo $link['menu_icon_class']; ?>"></i>
        <div><?php echo $link['menu_label']; ?></div>
    </a>
<?php endforeach; ?>