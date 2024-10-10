<?php
log_message('debug', 'Started migration 4.1.4...');
log_message('debug', 'Inserting emails');
$this->db->insert('emails', [
    'emails_key' => 'reset_password_request',
    'emails_language' => 'en',
    'emails_subject' => 'Password recovery',
    'emails_template' => "Hi, <b>{user_name}</b><br/>this email was sent to you because you requested a password reset on <b>{sender_name}</b>.<br/>If you did not request a password reset, please ignore this email. Otherwise, click on the link below<br/><a href='{reset_link}'>{reset_link}</a>",
    'emails_headers' => null,
]);

$this->db->insert('emails', [
    'emails_key' => 'reset_password_complete',
    'emails_language' => 'en',
    'emails_subject' => 'Password reset complete',
    'emails_template' => "Hi, <b>{user_name}</b><br/>Your password on <b>{sender_name}</b> has been changed.<br/><br/> Your new password is <b>{new_password}</b><br/><br/> You can now log in by clicking the link below:<br/> <a href='{login_link}'>{login_link}</a>",
    'emails_headers' => null,
]);

$this->db->insert('emails', [
    'emails_key' => 'confirm_code',
    'emails_language' => 'en',
    'emails_subject' => 'Confirm code',
    'emails_template' => "Hi, <b>{user_name}</b>,<br/><br/>You requested to confirm your identity on <b>{sender_name}</b>.<br/> Please use the following code to complete the process:<br/><br/> <h2 style='font-size: 24px; margin: 0'><b>{verification_code}</b></h2><br/> If you did not request this, please ignore this email.",
    'emails_headers' => null,
]);
log_message('debug', 'Ended migration 4.1.4...');
