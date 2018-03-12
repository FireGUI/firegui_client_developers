<table class="table table-striped table-advance table-hover">
    <thead>
        <tr>
            <th colspan="3">
                <input type="checkbox" class="mail-checkbox mail-group-checkbox">
                <span class="btn-group">
                    <a class="btn btn-sm blue" href="#" data-toggle="dropdown"> More<i class="fa fa-angle-down"></i></a>
                    <ul class="dropdown-menu">
                        <li><a href="#"><i class="fa fa-pencil"></i> Mark as Read</a></li>
                        <li><a href="#"><i class="fa fa-ban-circle"></i> Spam</a></li>
                        <li class="divider"></li>
                        <li><a href="#"><i class="fa fa-trash"></i> Delete</a></li>
                    </ul>
                </span>
            </th>
            <th class="pagination-control" colspan="3">
                <span class="pagination-info">1-30 of 789</span>
                <a class="btn btn-sm blue"><i class="fa fa-angle-left"></i></a>
                <a class="btn btn-sm blue"><i class="fa fa-angle-right"></i></a>
            </th>
        </tr>
    </thead>

    <tbody>
        <?php foreach($dati['messages'] as $message): ?>
            <tr class="unread" data-message-id="<?php echo $message[MESSAGES_TABLE.'_id']; ?>">
                <td class="inbox-small-cells">
                    <input type="checkbox" class="mail-checkbox">
                </td>
                <td class="inbox-small-cells"></td>
                <td class="view-message  hidden-xs"><?php echo "{$message[MESSAGES_USER_NAME]} {$message[MESSAGES_USER_LASTNAME]}"; ?></td>
                <td class="view-message "><?php echo character_limiter(strip_tags($message[MESSAGES_TABLE_TEXT_FIELD]), 100); ?></td>
                <td class="view-message  text-right inbox-small-cells"><?php echo date('d/m/Y', strtotime($message[MESSAGES_TABLE_DATE_FIELD])); ?></td>
                <td class="view-message  text-right inbox-small-cells"><?php echo date('H:i', strtotime($message[MESSAGES_TABLE_DATE_FIELD])); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>