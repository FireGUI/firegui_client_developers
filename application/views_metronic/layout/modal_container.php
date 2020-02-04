<?php
$sizeClass = '';
if (isset($size)) {
    switch ($size) {
        case 'small':
            $sizeClass = 'modal-sm';
            break;
        case 'large':
            $sizeClass = 'modal-lg';
            break;
    }
}
?>
<div class="modal fade modal-scroll" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog <?php echo $sizeClass; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?> <?php if (!empty($subtitle)) echo "<small>{$subtitle}</small>" ?> </h4>
            </div>
            <div class="modal-body">
                <?php echo $content; ?>
            </div>
        </div>
    </div>
</div>
