<?php foreach($messages as $message): ?>
    <li>  
        <a href="<?php echo base_url("messages/view/{$message[MESSAGES_TABLE.'_id']}"); ?>">
            <span class="photo">
                <?php if($message[MESSAGES_USER_THUMB]): ?>
                    <img src="<?php echo base_url("uploads/{$message[MESSAGES_USER_THUMB]}"); ?>" style="width: 45px; height: auto;"/>
                <?php else: ?>
                    <img src="http://www.placehold.it/45x45/EFEFEF/AAAAAA&text=no+image" style="width: 45px; height: auto;" />
                <?php endif; ?>
            </span>
            <span class="subject">
                <span class="from"><?php echo "{$message[MESSAGES_USER_NAME]} {$message[MESSAGES_USER_LASTNAME]}"; ?></span>
                <span class="time"><?php echo date('d/m/Y H:i', strtotime($message[MESSAGES_TABLE_DATE_FIELD])); ?></span>
            </span>
            <span class="message"><?php echo character_limiter(strip_tags($message[MESSAGES_TABLE_TEXT_FIELD]), 12); ?></span>  
        </a>
    </li>
<?php endforeach; ?>