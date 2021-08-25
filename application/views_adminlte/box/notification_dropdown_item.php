<?php foreach ($notifications as $notification) : ?>
    <?php echo sprintf('<li class="notification" data-notification="%s" class="%s">', $notification['notifications_id'], ($notification['notifications_read'] === DB_BOOL_FALSE) ? 'unread' : ''); ?>

    <a href="<?php echo $notification['href']; ?>">
        <h4>
            <?php echo e('Notification'); ?>
            <small><i class="fa fa-clock-o"></i><?php echo $notification['datespan']; ?></small>
        </h4>
        <p class="notification_text"><?php echo preg_replace('|<br\s*/?>|i', '&nbsp; ', $notification['notifications_message']); ?></p>
    </a>
    <?php echo '<li>'; ?>
<?php endforeach; ?>