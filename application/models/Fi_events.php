<?php


class Fi_events extends CI_Model
{

    public function __construct()
    {

        parent::__construct();
    }

    public function getHooksContents($hook_key, $valueId = null)
    {
        $events = $this->db
            ->where('fi_events_type', 'hook')
            ->where("fi_events_ref <> ''", null, false)
            ->order_by('fi_events_order ASC, fi_events_creation_date')
            ->get('fi_events')
            ->result_array();
        $content = '';
        foreach ($events as $key => $event) {
            $json_data = json_decode($event['fi_events_json_data'], true);

            if ($json_data['_when'] . '-' . $json_data['hook']['hooks_ref'] != $hook_key) {
                unset($events[$key]);
            } else {
                $events[$key]['hooks_content'] = $json_data['fi_events_actiondata']['code'];
            }
        }

        $plainHookContent = trim(implode(PHP_EOL, array_key_map($events, 'hooks_content', '')));

        if ($plainHookContent) {
            ob_start();
            $value_id = $valueId;   // per comodità e uniformità...
            eval(' ?> ' . $plainHookContent . ' <?php ');
            $content .= ob_get_clean();
        }





        return $content;
    }


    public function getCrons()
    {
        $events = $this->db
            ->join('crons', "fi_events_cron_id = crons_id", "LEFT")
            ->where('fi_events_type', 'cron')
            ->order_by('fi_events_order ASC, fi_events_creation_date')
            ->get('fi_events')
            ->result_array();

        return $events;
    }
}
