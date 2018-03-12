<table class="table table-striped table-advance table-hover">
    <thead>
        <tr>
            <th colspan="2">
                <?php /*<input type="checkbox" class="mail-checkbox mail-group-checkbox">*/ ?>
                <?php if ($data['search']): ?>
                Ricerca: <a href="javascript:;" class="js-remove-search"><span class="label label-danger"><?php echo $data['search']; ?> <i class="fa fa-remove-circle"></i></span></a>
                <?php endif; ?>
                <div class="btn-group">
                    <?php /*
                    <a class="btn btn-sm blue" href="#" data-toggle="dropdown"> More
                        <i class="icon-angle-down"></i>
                    </a>
                     */ ?>
                    <ul class="dropdown-menu">
                        <li><a href="#"><i class="fa fa-pencil"></i> Mark as Read</a></li>
                        <li><a href="#"><i class="fa fa-ban-circle"></i> Spam</a></li>
                        <li class="divider"></li>
                        <li><a href="#"><i class="fa fa-trash"></i> Delete</a></li>
                    </ul>
                </div>
            </th>
            <th class="pagination-control" colspan="3">
                <span class="pagination-info"><?php echo $data['page_min']; ?>-<?php echo $data['page_max']; ?> of <?php echo $data['totals']; ?></span>
                <a class="btn btn-sm blue" data-current="<?php echo $data['current']; ?>" data-page="<?php echo $data['prev']; ?>"><i class="fa fa-angle-left"></i></a>
                <a class="btn btn-sm blue" data-current="<?php echo $data['current']; ?>" data-page="<?php echo $data['next']; ?>"><i class="fa fa-angle-right"></i></a>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data['emails'] as $mail): ?>
            <tr class="<?php echo (empty($mail['mailbox_emails_read']) OR $mail['mailbox_emails_read'] === 't') ? '' : 'unread' ?>">
                <td class="inbox-small-cells">
                    <?php if (!empty($mail['mailbox_emails_attachments'])): ?>
                        <i class="fa fa-paperclip"></i>
                    <?php endif; ?>
                    <!--<input type="checkbox" class="mail-checkbox">-->
                </td>
                <td data-mail="<?php echo $mail['mailbox_emails_id']; ?>" class="view-message hidden-xs"><?php echo $mail['mailbox_emails_addresses']['From']['name']; ?></td>
                <td data-mail="<?php echo $mail['mailbox_emails_id']; ?>" class="view-message "><?php echo $mail['mailbox_emails_subject']; ?></td>
                <td data-mail="<?php echo $mail['mailbox_emails_id']; ?>" class="view-message inbox-small-cells">
                    <?php /*if (!empty($mail['mailbox_emails_attachments'])): ?>
                        <i class="fa fa-paperclip"></i>
                    <?php endif;*/ ?>
                </td>
                <td data-mail="<?php echo $mail['mailbox_emails_id']; ?>" class="view-message text-right"><?php
                    $date = new DateTime($mail['mailbox_emails_date']);
                    $diff = $date->diff(new DateTime);
                    if ($diff instanceof DateInterval) {
                        if ($diff->days > 0) {
                            echo $date->format('d/m/Y');
                        } else {
                            echo $date->format('H:i');
                        }
                    }
                    ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
