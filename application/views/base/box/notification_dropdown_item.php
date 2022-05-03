<?php foreach ($notifications as $notification) : ?>
    <?php
    //debug($notification);
    echo sprintf(
        '<li class="notification" data-notification="%s" class="%s" data-title="%s" data-message="%s" data-type="%s">',
        $notification['notifications_id'],
        ($notification['notifications_read'] === DB_BOOL_FALSE) ? 'unread' : '',
        $notification['notifications_title'],
        $notification['notifications_message'],
        $notification['notifications_type']
    );
    ?>

    <a href="<?php echo $notification['href']; ?>">
        <h4>
            <?php echo ($notification['notifications_title']) ?  $notification['notifications_title'] : 'Notification'; ?>
            <small><i class="fa fa-clock-o"></i><?php echo $notification['datespan']; ?></small>
        </h4>
        <p class="notification_text"><?php echo preg_replace('|<br\s*/?>|i', '&nbsp; ', $notification['notifications_message']); ?></p>
    </a>
    <?php echo '<li>'; ?>
<?php endforeach; ?>