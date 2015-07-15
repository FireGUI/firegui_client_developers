<div class="inbox-view-info">
    <div class="row">
        <div class="col-md-7">
            <?php if($dati['message'][MESSAGES_USER_THUMB]): ?>
                <img src="<?php echo base_url("uploads/{$dati['message'][MESSAGES_USER_THUMB]}"); ?>" style="width: 50px; height: auto;"/>
            <?php else: ?>
                <img src="http://www.placehold.it/50x50/EFEFEF/AAAAAA&text=no+image" style="width: 50px; height: auto;" />
            <?php endif; ?>
            <span class="bold"><?php echo "{$dati['message'][MESSAGES_USER_NAME]} {$dati['message'][MESSAGES_USER_LASTNAME]}"; ?></span> a <span class="bold">me</span> il <?php echo date('d/m/Y H:i', strtotime($dati['message'][MESSAGES_TABLE_DATE_FIELD])); ?>
        </div>
        <div class="col-md-5 inbox-info-btn">
            <div class="btn-group">
                <button class="btn blue reply-btn" data-message-id="<?php echo $dati['message'][MESSAGES_TABLE.'_id']; ?>">
                    <i class="icon-reply"></i> Reply
                </button>
            </div>
        </div>
    </div>
</div>


<div class="inbox-view"><?php echo $dati['message'][MESSAGES_TABLE_TEXT_FIELD]; ?></div>
<hr/>