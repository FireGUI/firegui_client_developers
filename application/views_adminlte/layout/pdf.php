
<page backtop="5mm" backleft="5mm" backright="5mm" backbottom="5mm">

    <page_header>
        
    </page_header>

    
    <?php
    foreach ($dati['layout'] as $row) {
        foreach ($row as $layout) {
            echo (($layout['layouts_boxes_titolable'] === 't')? '<h2>'.ucfirst(str_replace('_', ' ', $layout['layouts_boxes_title'])).'</h2>': '')."<div>{$layout['content']}</div>";
        }
    }
    ?>
    
    
    <page_footer>
        
    </page_footer>    
</page>

