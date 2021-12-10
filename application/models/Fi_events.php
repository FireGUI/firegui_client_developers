<?php


class Notify extends CI_Model
{
    private $_actiondata;
    private $_data;
    private $_what;
    private $_to;
    private $_subject;
    private $_message;
    private $_templateEmail;
    private $_single_user;
    private $_email_from;
    private $_email_cc;
    private $_email_bcc;
    private $_email_recipients;
    public function __construct()
    {

        parent::__construct();
    }

    public function init($event, $data)
    {
        $this->_actiondata = json_decode($event['fi_events_actiondata'], true);
        $this->_data = $data;
        $this->_what = $this->_actiondata['what'];
        $this->_to = $this->_actiondata['to'];
        $this->_email_recipients = [];
        return $this;
    }

    public function run()
    {

        switch ($this->_what) {
            case 'email_custom':
                $this->buildEmailCustom();
                break;
            case 'email_tpl':
                $this->buildEmailTpl();
                break;
            default:
                debug("Action what '{$this->_what}' not recognized!");
                break;
        }
        //debug($this, true);
        switch ($this->_what) {
            case 'email_custom':
            case 'email_tpl':
                $this->sendEmails();
                break;
            default:
                debug("Action what '{$this->_what}' not recognized!");
                break;
        }
    }

    private function buildEmailCustom()
    {
        $this->_message = $this->_actiondata['message'];
        $this->_subject = $this->_actiondata['title_subject'];

        $this->_email_from = $this->_actiondata['email_from'];
        $this->_email_cc = $this->_actiondata['email_cc'];
        $this->_email_bcc = $this->_actiondata['email_bcc'];

        $this->buildEmailTo();
    }
    private function buildEmailTpl()
    {

        $this->_templateEmail = $this->_actiondata['email_template'];

        $this->buildEmailTo();
    }
    private function buildEmailTo()
    {
        switch ($this->_to) {
            case 'group':
                $this->buildEmailToGroup();
                break;
            case 'superadmin':
                $this->buildEmailToSuperadmin();
                break;
            case 'single_user':
                $this->buildEmailToSingleUser();
                break;
            case 'custom':
                $this->buildEmailToCustom();
                break;
            default:
                debug("Action to '{$this->_to}' not recognized!", true);
                break;
        }
    }
    private function buildEmailToGroup()
    {
        $this->_group = $this->_actiondata['group'];
        $users = $this->apilib->search(LOGIN_ENTITY, [
            LOGIN_ENTITY . "_id IN (SELECT permissions_user_id FROM permissions WHERE permissions_group = '{$this->_group}')"
        ]);
        foreach ($users as $user) {
            // $name = $user[LOGIN_NAME_FIELD];
            // $surname = $user[LOGIN_SURNAME_FIELD];
            $email = $user[LOGIN_USERNAME_FIELD];

            $this->_email_recipients[] = $email;
        }
    }
    private function buildEmailToSingleUser()
    {
        $this->_single_user = $this->_actiondata['single_user'];
        $user = $this->apilib->getById(LOGIN_ENTITY, $this->_single_user);

        // $name = $user[LOGIN_NAME_FIELD];
        // $surname = $user[LOGIN_SURNAME_FIELD];
        $email = $user[LOGIN_USERNAME_FIELD];

        $this->_email_recipients[] = $email;
    }
    private function buildEmailToSuperadmin()
    {

        $users = $this->apilib->search(LOGIN_ENTITY, [
            LOGIN_ENTITY . "_id IN (SELECT permissions_user_id FROM permissions WHERE permissions_admin = '" . DB_BOOL_TRUE . "')"
        ]);

        foreach ($users as $user) {
            // $name = $user[LOGIN_NAME_FIELD];
            // $surname = $user[LOGIN_SURNAME_FIELD];
            $email = $user[LOGIN_USERNAME_FIELD];

            $this->_email_recipients[] = $email;
        }
    }
    private function buildEmailToCustom()
    {
        $this->_email_custom = $this->_actiondata['custom'];


        $this->_email_recipients[] = $this->_email_custom;
    }

    private function sendEmails()
    {
        //debug($this->_data, true);
        foreach ($this->_email_recipients as $email) {
            $headers = [];

            if ($this->_email_from) {
                $headers['From'] = $this->_email_from;
            }

            if ($this->_email_from) {
                $headers['Cc'] = $this->_email_cc;
            }

            if ($this->_email_from) {
                $headers['Bcc'] = $this->_email_bcc;
            }

            if ($this->_what == 'email_custom') {
                //debug($this);
                $return = $this->mail_model->sendFromData($email, ['template' => $this->_message, 'subject' => $this->_subject], array_merge($this->_data, @(array)$this->_data['new']), $headers);
                //debug($return, true);
            } elseif ($this->_what == 'email_tpl') {
                $this->mail_model->send($email, $this->_templateEmail, '', $this->_data, $headers);
            } else {
                debug("what '{$this->_what}' not supported!");
            }
        }
    }
}
