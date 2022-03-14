<!-- BEGIN NOTIFICATION DROPDOWN -->
<li class="dropdown messages-menu" id="header_notification_bar">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="far fa-bell notification_icon"></i>
        <span class="js_notification_number_label badge bg-red"></span>
    </a>
    <ul class="dropdown-menu">

        <li class="header">
            <?php e('You have'); ?> <span class="js_notification_number bold">0</span> <?php e('new notifications'); ?>
        </li>

        <li>
            <!-- inner menu: contains the actual data -->
            <ul class="menu js_notification_dropdown_list dropdown-menu-list scroller firegui_notification">
                <li></li>
            </ul>
        </li>
        <li class="footer">
            <a href="#" onclick="CrmNotifier.readAll();return false;" role='button'><?php e('mark as read'); ?></a>
        </li>
    </ul>
</li>
<!-- END NOTIFICATION DROPDOWN -->