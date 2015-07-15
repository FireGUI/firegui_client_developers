<?php


require_once(__DIR__ . '/../ImapMailbox.php');


class Imap_mailbox extends CI_Model {
    
    const ADDR_TYPE_FROM    = 1;
    const ADDR_TYPE_TO      = 2;
    const ADDR_TYPE_CC      = 3;
    const ADDR_TYPE_REPLYTO = 4;
    
    private $baseUpload;


    private $notices = array();
    
    
    public function __construct() {
        parent::__construct();
        $this->baseUpload = FCPATH . 'uploads';
    }


    
    
    public function listMailboxFolders($configId) {
        return $this->connect($configId)->getListingFolders();
    }
    
    public function listRegisteredFolders($configId) {
        $folders = $this->db->get_where('mailbox_configs_folders', array('mailbox_configs_folders_config' => $configId))->result_array();
        $_return = [];
        foreach ($folders as $folder) {
            $_return[$folder['mailbox_configs_folders_name']] = $folder;
        }
        return $_return;
    }
    
    public function upsertFolder($configId, array $folder) {
        
        if (!is_numeric($configId) OR $configId < 1 OR !$this->getConfig($configId)) {
            throw new Exception('Provide a valid configuration id');
        }
        
        if (empty($folder['mailbox_configs_folders_name'])) {
            throw new Exception('The folder name is empty');
        }
        
        // Force the config id and prevent id updation
        $folder['mailbox_configs_folders_config'] = $configId;
        $folder['mailbox_configs_folders_alias'] = empty($folder['mailbox_configs_folders_alias'])? $folder['mailbox_configs_folders_name']: $folder['mailbox_configs_folders_alias'];
        unset($folder['mailbox_configs_folders_id']);
        
        $cfgFolder = $this->db->get_where('mailbox_configs_folders', array('mailbox_configs_folders_name'=>$folder['mailbox_configs_folders_name'], 'mailbox_configs_folders_config' => $configId));
        if ($cfgFolder->num_rows() > 0) {
            return $this->apilib->edit('mailbox_configs_folders', $cfgFolder->row()->mailbox_configs_folders_id, $folder);
        } else {
            return $this->apilib->create('mailbox_configs_folders', $folder);
        }
    }
    
    
    public function removeAccount($configId) {
        
        if (!$configId) {
            return;
        }
        
        $this->db->trans_start();
        $this->db->delete('mailbox_configs', ['mailbox_configs_id' => $configId]);
        $this->db->where('mailbox_configs_folders_config NOT IN (SELECT mailbox_configs_id FROM mailbox_configs)')->delete('mailbox_configs_folders');
        $this->wipeOrphanEmails();
        $this->db->trans_complete();
    }
    
    public function wipeAccountEmails($configId) {
        $this->db->trans_start();
        $this->db->where("mailbox_emails_folder IN (SELECT mailbox_configs_folders_id FROM mailbox_configs_folders WHERE mailbox_configs_folders_config = '{$configId}')")->delete('mailbox_emails');
        $this->wipeOrphanEmails();
        $this->db->trans_complete();
    }
    
    public function wipeOrphanEmails() {
        $this->db->where('mailbox_emails_folder NOT IN (SELECT mailbox_configs_folders_id FROM mailbox_configs_folders)')->delete('mailbox_emails');
        $this->db->where('mailbox_emails_addresses_email NOT IN (SELECT mailbox_emails_id FROM mailbox_emails)')->delete('mailbox_emails_addresses');
        $this->db->where('mailbox_emails_attachments_email NOT IN (SELECT mailbox_emails_id FROM mailbox_emails)')->delete('mailbox_emails_attachments');
    }






    /**
     * @param int $configsId
     * @param string $folder
     * @return ImapMailbox
     */
    public function connect($configsId, $folder = null) {
        
        $configs = $this->apilib->view('mailbox_configs', $configsId);

        $server = $configs['mailbox_configs_server'];
        $port = $configs['mailbox_configs_port'];
        $protocol = trim($configs['mailbox_configs_protocol'], '/');
        
        $attachDir = implode(DIRECTORY_SEPARATOR, [rtrim($this->baseUpload, DIRECTORY_SEPARATOR),'attachments',$configsId]);

        if ($folder) {
            if(is_numeric($folder)) {
                $cfgFolder = $this->db->get_where('mailbox_configs_folders', array('mailbox_configs_folders_id' => $folder, 'mailbox_configs_folders_config' => $configsId))->row();
                if (empty($cfgFolder)) {
                    throw new Exception('The folder not exists');
                }

                $folder = $cfgFolder->mailbox_configs_folders_name;
            }
            
            $cfgFolder = $this->db->get_where('mailbox_configs_folders', array('mailbox_configs_folders_name' => $folder, 'mailbox_configs_folders_config' => $configsId))->row();
            if (empty($cfgFolder)) {
                throw new Exception(sprintf('The folder %s not exists in the configuration', $folder));
            }
            
            $attachDir.= DIRECTORY_SEPARATOR . $cfgFolder->mailbox_configs_folders_id;
        } else {
            $attachDir.= DIRECTORY_SEPARATOR . 'default';
        }

        $connStr = '{' . $server . ($port? ':' . $port: '') . ($protocol? '/'.$protocol: '') . '}' . ($folder?:'');

        if (!is_dir($attachDir)) {
            mkdir($attachDir, 0777, true);
        }

        $mailbox = new ImapMailbox($connStr, $configs['mailbox_configs_email'], $configs['mailbox_configs_password'], $attachDir, 'utf-8');
        $mailbox->setConnectionArgs(0, 0, [
            'DISABLE_AUTHENTICATOR' => 'GSSAPI'
        ]);
        return $mailbox;
    }
    
    
    
    public function getUserConfigs($userId) {
        
        if (!is_numeric($userId) OR $userId < 1) {
            die('Provide a valid user id');
        }
        
        return $this->apilib->search('mailbox_configs', array('mailbox_configs_user' => $userId));
    }
    
    public function getConfig($configId) {
        
        if (!is_numeric($configId) OR $configId < 1) {
            die('Provide a valid configuration id');
        }
        
        return $this->apilib->view('mailbox_configs', $configId);
    }
    
    
    public function fetchEmailsFromConfigs($limit) {
        
        $qFolders = $this->db->query("
                SELECT mailbox_configs_folders.*, COALESCE (maxes.date, NOW() - INTERVAL '5 years') AS last_mail_date
                FROM mailbox_configs_folders
                JOIN mailbox_configs ON mailbox_configs_folders_config = mailbox_configs_id
                LEFT JOIN (
                    SELECT DISTINCT ON (mailbox_emails_folder) mailbox_emails_folder AS folder, MAX(mailbox_emails_date) AS date
                    FROM mailbox_emails
                    GROUP BY mailbox_emails_folder
                ) AS maxes ON maxes.folder = mailbox_configs_folders_id
                WHERE mailbox_configs_folders_attiva
            ");
        $folders = $qFolders->result_array();
        $qFolders->free_result();
        
        $folderUpdations = [];
        foreach ($folders as $folder) {
            try {
                $mbox = $this->connect($folder['mailbox_configs_folders_config'], $folder['mailbox_configs_folders_name']);
            } catch (Exception $ex) {
                echo $ex->getMessage() . '<br/>';
                continue;
            }
            
            /*$lastMailDate = new DateTime($folder['last_mail_date']);
            $from = $lastMailDate->format('Y-m-d');
            $emails = $mbox->searchMailBox('UNDELETED SINCE "' . $from . '"');*/
            $emails = $mbox->sortMails(SORTDATE);
            
            // Tiro via le email già inserite nel database
            if ($emails) {
                $strMailId = array_map(function($mailId) { return (string) $mailId; }, $emails);
                $query = $this->db->where_in('mailbox_emails_external_id', $strMailId)->get_where('mailbox_emails', array('mailbox_emails_folder' => $folder['mailbox_configs_folders_id']));
                $registered = array_key_map($query->result_array(), 'mailbox_emails_external_id');
                $emails = array_diff($emails, $registered);
                $query->free_result();
            }
            
            if (!$emails) {
                continue;
            }
            
            $i = 0;
            $this->db->trans_start();
            foreach (array_splice($emails, 0, $limit) as $emailId) {
                $created = $this->createMail($folder['mailbox_configs_folders_id'], $mbox->getMail($emailId));
                $created? $i++: null;
            }
            $this->db->trans_complete();
            
            // Disconnessione manuale dalla mailbox
            $mbox->disconnect();
            unset($mbox);
            
            $folderUpdations[$folder['mailbox_configs_folders_id']] = $i;
        }        
        
        return $folderUpdations;
        
    }
    
    
    protected function createMail($folderId, IncomingMail $email) {
        try {
            $mailboxEmail = $this->apilib->create('mailbox_emails', array(
                'mailbox_emails_folder'         => $folderId,
                'mailbox_emails_date'           => $email->date,
                'mailbox_emails_external_id'    => (string) $email->id,
                'mailbox_emails_subject'        => $email->subject,
                'mailbox_emails_text_plain'     => utf8_encode($email->textPlain),
                'mailbox_emails_text_html'      => utf8_encode($email->textHtml),
            ));

            $mbeId = $mailboxEmail['mailbox_emails_id'];
            $this->setMailAddresses($mbeId, $email->fromAddress, $email->fromName, $email->to, $email->cc, $email->replyTo);
            $this->setMailAttachments($mbeId, $email->getAttachments());
        } catch (Exception $ex) {
            echo '<pre>'.$ex->getMessage().'</pre>';
            $this->notices[] = $ex->getMessage();
            return false;
        }
        
        return true;
    }
    
    
    protected function setMailAddresses($mailId, $fromAddress, $fromName, $to, $cc, $replyTo) {
        $_from = $this->buildAddressArray($mailId, self::ADDR_TYPE_FROM, $fromAddress, $fromName);
        
        $_to = [];
        foreach ($to as $mail => $name) {
            $_to[] = $this->buildAddressArray($mailId, self::ADDR_TYPE_TO, $mail, $name);
        }
        
        $_cc = [];
        foreach ($cc as $mail => $name) {
            $_cc[] = $this->buildAddressArray($mailId, self::ADDR_TYPE_TO, $mail, $name);
        }
        
        $_replyto = [];
        foreach ($replyTo as $mail => $name) {
            $_replyto[] = $this->buildAddressArray($mailId, self::ADDR_TYPE_TO, $mail, $name);
        }
        
        $this->apilib->createMany('mailbox_emails_addresses', array_merge([$_from], $_to, $_cc, $_replyto));
    }



    protected function buildAddressArray($mailId, $type, $address, $name = null) {
        
        $validTypes = [
            self::ADDR_TYPE_FROM,
            self::ADDR_TYPE_TO,
            self::ADDR_TYPE_CC,
            self::ADDR_TYPE_REPLYTO
        ];
        
        if (!in_array($type, $validTypes)) {
            throw new Exception("Type not valid");
        }
        
        return [
            'mailbox_emails_addresses_email' => $mailId,
            'mailbox_emails_addresses_type' => $type,
            'mailbox_emails_addresses_address' => strtolower(trim($address)),
            'mailbox_emails_addresses_name' => trim($name)?:null
        ];
    }
    
    
    
    protected function setMailAttachments($mailId, array $attachments) {
        foreach ($attachments as $attachment) {
            if ($attachment instanceof IncomingMailAttachment) {
                $this->addMailAttachment($mailId, $attachment);
            }
        }
    }

    

    protected function addMailAttachment($mailId, IncomingMailAttachment $attachment) {
        
        $base = realpath($this->baseUpload);
        $file = realpath($attachment->filePath);
        $relpath = ltrim(substr($file, strlen($base)), DIRECTORY_SEPARATOR);
        
        $this->apilib->create('mailbox_emails_attachments', [
            'mailbox_emails_attachments_email' => $mailId,
            'mailbox_emails_attachments_external_id' => $attachment->id,
            'mailbox_emails_attachments_name' => $attachment->name,
            'mailbox_emails_attachments_file' => $relpath,
        ]);
        
    }
    
    
    
}
