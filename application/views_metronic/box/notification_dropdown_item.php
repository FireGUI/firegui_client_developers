<?php foreach($notifications as $notification): ?>
    <?php echo sprintf('<li data-notification="%s" class="%s">', $notification['notifications_id'], ($notification['notifications_read']===DB_BOOL_FALSE)? 'unread': ''); ?>
        <a href="<?php echo $notification['href']; ?>">
            <span class="time"><?php echo $notification['datespan']; ?></span>
            <span class="details">
                <span class="label label-sm label-icon <?php echo $notification['label']['class']; ?>">
                    <i class="<?php echo $notification['label']['icon']; ?>"></i>
                </span>
                <span class="message"><?php echo preg_replace('|<br\s*/?>|i', '&nbsp; ', strip_tags($notification['notifications_message'])); ?></span>
            </span>
        </a>
    <?php echo '<li>'; ?>
<?php endforeach; ?>
    