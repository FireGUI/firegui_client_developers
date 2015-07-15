<div class="inbox-header inbox-view-header">
    <h1 class="pull-left"><?php echo $data['mailbox_emails_subject']; ?> <a href="#"><?php echo $data['mailbox_configs_folders_alias']; ?></a></h1>
    <div class="pull-right"><i class="icon-print"></i></div>
</div>


<div class="inbox-view-info">
    <div class="row">
        <div class="col-md-7">
            <!--<img src="assets/img/avatar1_small.jpg" />--> 
            <span class="bold"><?php echo $data['mailbox_emails_addresses']['From']['name']; ?></span>
            <span>&#60;<?php echo $data['mailbox_emails_addresses']['From']['mail']; ?>&#62;</span> to <span class="bold"><?php echo $data['configs']['mailbox_configs_email']; ?></span> on <?php echo dateTimeFormat($data['mailbox_emails_date']); ?>
        </div>
        <div class="col-md-5 inbox-info-btn">
            <div class="btn-group">
                <button class="btn blue reply-btn">
                    <i class="icon-reply"></i> Reply
                </button>
                <button class="btn blue  dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-angle-down"></i>
                </button>
                <ul class="dropdown-menu pull-right">
                    <li><a href="#"><i class="icon-reply reply-btn"></i> Reply</a></li>
                    <li><a href="#"><i class="icon-arrow-right reply-btn"></i> Forward</a></li>
                    <!--<li><a href="#"><i class="icon-print"></i> Print</a></li>-->
                    <li class="divider"></li>
                    <li><a href="#"><i class="icon-ban-circle"></i> Spam</a></li>
                    <li><a href="#"><i class="icon-trash"></i> Delete</a></li>
                    <li>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="inbox-view"><?php echo utf8_decode($data['mailbox_emails_text_html'])? : '<p>' . nl2br(htmlspecialchars(utf8_decode($data['mailbox_emails_text_plain']))) . '</p>'; ?></div>
<hr>
<?php if ($data['mailbox_emails_attachments']): ?>
    <div class="inbox-attached">
        <div class="margin-bottom-15">
            <span><?php echo count($data['mailbox_emails_attachments']); ?> attachments</span> 
            <?php /*
            <a href="#">Download all attachments</a>   
            <a href="#">View all images</a>   
             */ ?>
        </div>
        <?php foreach($data['mailbox_emails_attachments'] as $file): ?>
            <?php if (file_exists(($filename=FCPATH . 'uploads/' . $file['mailbox_emails_attachments_file']))): ?>
                <div class="margin-bottom-25">
                    <?php if (in_array(pathinfo($filename, PATHINFO_EXTENSION), ['jpg', 'png', 'bmp'])): ?>
                        <img src="<?php echo base_url_template('uploads/' . $file['mailbox_emails_attachments_file']); ?>">
                    <?php endif; ?>
                    <div>
                        <strong><?php echo $file['mailbox_emails_attachments_name']; ?></strong>
                        <span><?php echo filesize($filename); ?></span>
                        <a href="<?php echo base_url_template('uploads/' . $file['mailbox_emails_attachments_file']); ?>" download="<?php echo $file['mailbox_emails_attachments_name']; ?>">Download</a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>