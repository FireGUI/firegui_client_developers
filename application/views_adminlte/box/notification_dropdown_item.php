<?php foreach ($notifications as $notification) : ?>
    <?php echo sprintf('<li class="notification" data-notification="%s" class="%s">', $notification['notifications_id'], ($notification['notifications_read'] === DB_BOOL_FALSE) ? 'unread' : ''); ?>
    <a href="<?php echo $notification['href']; ?>">
        <i class="<?php echo $notification['label']['icon']; ?>"></i>
        <span class="time"><?php echo $notification['datespan']; ?>:</span>

        <span><?php echo preg_replace('|<br\s*/?>|i', '&nbsp; ', $notification['notifications_message']); ?></span>
    </a>
    <?php echo '<li>'; ?>
<?php endforeach; ?>