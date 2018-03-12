<?php

class Widget extends MX_Controller {

    const LIMIT = 30;

    public function index() {

        $page = $this->input->get('page')? : 1;
        if (!is_numeric($page) OR $page < 1) {
            $page = 1;
        }

        $offset = ($page - 1) * self::LIMIT;
        $current = $this->input->get('current');

        $filter = $this->loadIndexFilters($current);
        
        
        $searchFilters = [];
        
        $fixedFilter = $this->input->get('filter');
        if ($fixedFilter) {
            $allFixedFilters = unserialize(MAILBOX_FLAG_FILTERS);
            foreach ($fixedFilter as $key) {
                if (isset($allFixedFilters[$key])) {
                    $searchFilters[] = $allFixedFilters[$key];
                }
            }
        }
        
        $search = trim($this->input->get('search'))?:null;
        if ($search) {
            $searchFilters[] = $search;
        }
        
        foreach ($searchFilters as $q) {
            if ($q) {
                $filter[] = "(
                        mailbox_emails_subject ILIKE '%{$q}%' OR 
                        mailbox_emails_text_plain ILIKE '%{$q}%' OR 
                        mailbox_emails_text_html ILIKE '%{$q}%' OR 
                        mailbox_emails_id IN (
                            SELECT mailbox_emails_addresses_email
                            FROM mailbox_emails_addresses
                            WHERE (
                                mailbox_emails_addresses_name ILIKE '%{$q}%' OR 
                                mailbox_emails_addresses_address ILIKE '%{$q}%'
                            )
                        )
                    )";
            }
        }

        $data['search'] = $search;
        $data['emails'] = $this->elaborateMailList($this->apilib->search('mailbox_emails', $filter, self::LIMIT, $offset, 'mailbox_emails_date', 'desc'));
        $size = count($data['emails']);
        $data['totals'] = $this->apilib->count('mailbox_emails', $filter);
        $data['prev'] = max([1, $page - 1]);
        $data['next'] = min([$page + 1, ceil($data['totals'] / self::LIMIT)]);
        $data['page_min'] = $offset + 1;
        $data['page_max'] = $data['page_min'] + $size - 1;
        $data['current'] = $current;


        $this->load->view('partials/widget/inbox', ['data' => $data]);
    }

    public function compose() {
        $this->load->view('partials/widget/compose');
    }

    public function reply() {
        $this->load->view('partials/widget/reply');
    }

    public function view($mailId = null) {

        if (!$mailId) {
            echo 'Il messaggio non esiste';
            return;
        }

        $data = $this->elaborateMail($this->apilib->view('mailbox_emails', $mailId));

        if (empty($data)) {
            echo 'Il messaggio non esiste';
            return;
        }
        
        $data['configs'] = $this->db->get_where('mailbox_configs', ['mailbox_configs_id' => $data['mailbox_configs_folders_config']])->row_array();
        $this->load->view('partials/widget/view', compact('data'));
    }

    public function test() {
        $emails = $this->elaborateMailList($this->apilib->search('mailbox_emails', [], 25, 0, 'mailbox_emails_date', 'desc'));
        debug($emails);
    }

    /*
     * ========================================
     * Utility
     * ========================================
     */

    private function loadIndexFilters($name) {

        $userId = $this->auth->get('id');
        $filters[] = "mailbox_configs_folders_config IN (SELECT mailbox_configs_id FROM mailbox_configs WHERE mailbox_configs_user = '{$userId}')";

        switch ($name) {
            case 'sent':
                $filters['mailbox_configs_folders_uscita'] = 't';
                break;

            default :
                $filters['mailbox_configs_folders_uscita'] = 'f';
        }

        return $filters;
    }

    private function elaborateMailList(array $emails) {
        return array_map([$this, 'elaborateMail'], $emails);
    }

    public function elaborateMail(array $mail) {
        $addresses = [];
        foreach ($mail['mailbox_emails_addresses'] as $address) {
            $addresses[$address['mailbox_addresses_types_value']][] = [
                'name' => $address['mailbox_emails_addresses_name']? : $address['mailbox_emails_addresses_address'],
                'mail' => $address['mailbox_emails_addresses_address'],
            ];
        }

        $addresses['From'] = isset($addresses['From'][0]) ? $addresses['From'][0] : ['name' => 'sconosciuto', 'mail' => 'sconosciuto'];
        $mail['mailbox_emails_addresses'] = $addresses;
        return $mail;
    }

}
