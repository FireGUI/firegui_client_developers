<?php


require_once(__DIR__ . '/../ImapMailbox.php');


class Imap_mailbox extends CI_Model {
    
    
    private $connections = array();
    
    
    
    public function listMailboxFolders($configsId) {
        
        return $this->connect($configsId)->getListingFolders();
        
    }
    
    
    
    /**
     * @param int $configsId
     * @param string $folder
     * @return ImapMailbox
     */
    public function connect($configsId, $folder = null) {
        
        $key = md5($configsId.$folder);
        
        if (empty($this->connections[$key])) {
            $configs = $this->apilib->view('mailbox_configs', $configsId);
            
            $server = $configs['mailbox_configs_server'];
            $port = $configs['mailbox_configs_port'];
            $protocol = trim($configs['mailbox_configs_protocol'], '/');
            
            $connStr = '{' . $server . ($port? ':' . $port: '') . ($protocol? '/'.$protocol: '') . '}' . ($folder?:'');
            $attachDir = FCPATH . 'uploads/attachments';
            
            if (!is_dir($attachDir)) {
                mkdir($attachDir);
            }
            
            
            $this->connections[$key] = new ImapMailbox($connStr, $configs['mailbox_configs_email'], $configs['mailbox_configs_password'], $attachDir, 'utf-8');
            
        }
        
        return $this->connections[$key];
        
    }
    
    
    
    
}
