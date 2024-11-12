<?php


class Fi_events extends CI_Model
{
    private $_events = [];
    public function __construct()
    {

        parent::__construct();
    }

    public function getHooksContents($hook_key, $valueId = null)
    {
        if (empty($this->_events)) {
            $this->_events = $this->db
                ->where('fi_events_type', 'hook')
                ->where("fi_events_ref <> ''", null, false)
                ->order_by('fi_events_order ASC, fi_events_creation_date')
                ->get('fi_events')
                ->result_array();
        }
        $events = $this->_events;
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
            $value_id = $valueId; // per comodità e uniformità...
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


    //Admin utils
    public function additionalData($event)
    {
        //Human readable text
        $hr_text = '';
        $bottom_right_text = '';

        $action_data = json_decode($event['fi_events_actiondata'], true);
        $icon = $action = '';
        //join ref_id
        $ref = 'unknown';

        if ($event['fi_events_when'] == 'save') {
            $event['fi_events_when'] = 'Insert/Edit record';
        } elseif ($event['fi_events_when'] == 'pre-save') {
            $event['fi_events_when'] = 'Pre-Insert/Pre-Edit record';
        }



        //Analizzo il type per recuperare altre informazioni
        switch ($event['fi_events_type']) {
            case 'database':
                $event['fi_events_type_read'] = 'DB Trigger';
                $entity = $this->db->get_where('entity', ['entity_id' => $event['fi_events_ref_id']])->row();
                $ref = $entity->entity_name;

                $hr_text = "When {$event['fi_events_when']} data on {$entity->entity_name}, ";
                break;
            case 'hook':
                $extra_data = json_decode($event['fi_events_json_data'], true);
                //debug($extra_data, true);
                if ($extra_data['hook']['hooks_type'] == 'template_hook') {

                    $event['fi_events_type_read'] = 'Hook ' . $event['fi_events_when'] . ' '
                        . $extra_data['hook']['hooks_ref'];

                    if ($event['fi_events_when'] == 'pre') {
                        $hr_text .= 'Before output ';
                    } else {
                        $hr_text .= 'After output ';
                    }

                    $hr_text .= $extra_data['hook']['hooks_ref'] . ', ';

                    $ref = $extra_data['hook']['hooks_ref'];
                } else { //Vecchi hook, ancora funziojnanti, ma con vecchie logiche legate alla tabella hooks
                    if (stripos($event['fi_events_when'], 'post')) {
                        $hr_text .= "After";
                    } else {
                        $hr_text .= "Before";
                    }
                    $hr_text .= " output ";

                    $event['fi_events_type_read'] = 'Hook ' . $event['fi_events_when'];
                    if (stripos($event['fi_events_when'], 'layout')) {
                        $layout = $this->db->get_where('layouts', ['layouts_id' => $event['fi_events_ref_id']])->row();
                        if ($layout) {
                            $ref = $layout->layouts_title;

                            $hr_text .= ' layout ';
                        } else {
                            $ref = 'Every Layout';
                        }
                    } elseif (stripos($event['fi_events_when'], 'grid')) {
                        $grid = $this->db->get_where('grids', ['grids_id' => $event['fi_events_ref_id']])->row();

                        if ($grid) {
                            $hr_text .= ' grid ';
                            $ref = $grid->grids_name;
                        } else {
                            $ref = 'Every Grid';
                        }
                    } elseif (stripos($event['fi_events_when'], 'form')) {
                        $form = $this->db->get_where('forms', ['forms_id' => $event['fi_events_ref_id']])->row();

                        if ($form) {
                            $ref = $form->forms_name;
                            $hr_text .= ' form ';
                        } else {
                            $ref = 'Every form';
                        }
                    }
                    $hr_text .= " '$ref', ";
                }

                break;
            case 'cron':
                $event['fi_events_type_read'] = 'Cron';
                $event['fi_events_when'] = 'Every';
                //$layout = $this->db->get_where('layouts', ['layouts_id' => $event['fi_events_ref_id']])->row();

                $ref = EVENTS_BUILDER_CRONS['cron_every']['steps'][$event['fi_events_cron_frequency']];

                //debug($event,true);
                if ($event['fi_events_action'] != 'curl') {
                    $bottom_right_text = "Last execution:<br /><strong>" . (($event['crons_last_execution']) ?: 'never') . "</strong>";
                }
                $hr_text .= "Every <strong>$ref</strong> ";
                break;

            default:
                //debug($event, true);
                //debug($event,true);
                break;
        }
        //Analizzo l'action per recuperare altre informazioni
        switch ($event['fi_events_action']) {
            case 'custom_code':

                $action = 'Custom code';
                $icon = 'fa-code';
                $hr_text .= "execute a custom code.";
                break;
            case 'curl':
                $action = 'cURL';
                $icon = 'fa-link';
                $hr_text .= "cUrl to <br /><strong>" . truncate_in_the_middle($action_data['url'], 100) . "</strong><br /><br />"
                    . "Last execution:<br /><strong>" . (($event['crons_last_execution']) ?: 'never') . "</strong>";
                break;
            case 'notify':
                $action = 'Notify';
                $icon = 'fa-email';
                $hr_text .= "send a notification...";
                break;

            default:

                $action = 'unknown';
                $icon = 'fa-question';
                debug($event, true);
                break;
        }

        $event['additional_data'] = [
            'ref_text' => $ref,
            'human_readable' => $hr_text,
            'bottom_right_text' => $bottom_right_text,
            'action' => $action,
            'icon' => $icon,
        ];
        return $event;
    }
    public function delete_event($event_id)
    {
        $event = $this->db->get_where('fi_events', ['fi_events_id' => $event_id])->row_array();
        switch ($event['fi_events_type']) {
            case 'database':
                $this->db->where('post_process_id', $event['fi_events_post_process_id'])->delete('post_process');
                break;
            case 'hook':
            case 'form':
                $this->db->where('hooks_id', $event['fi_events_hook_id'])->delete('hooks');
                break;
            case 'cron':
                $this->db->where('crons_id', $event['fi_events_cron_id'])->delete('crons');
                break;
            default:
                debug($event, true);

                break;
        }

        $this->db->where('fi_events_id', $event_id)->delete('fi_events');
    }
    public function new_event($data)
    {
        if (!empty($data['events_id'])) {
            $event_id = $data['events_id'];
            unset($data['events_id']);
        } else {
            $event_id = null;
        }
        $event = [
            'fi_events_title' => $data['fi_events_title'],
            'fi_events_json_data' => json_encode($data),
            'fi_events_type' => $data['fi_events_type'],
            'fi_events_action' => $data['fi_events_action'],
            'fi_events_actiondata' => json_encode($data['fi_events_actiondata']),
            'fi_events_active' => (empty($data['cron']['crons_active']) || $data['cron']['crons_active'] === DB_BOOL_FALSE) ? DB_BOOL_FALSE : DB_BOOL_TRUE,
            'fi_events_cli' => (empty($data['cron']['crons_cli']) || $data['cron']['crons_cli'] === DB_BOOL_FALSE) ? DB_BOOL_FALSE : DB_BOOL_TRUE,
            //'fi_events_id' => $event_id
        ];

        if ($event_id == null) {
            $event['fi_events_creation_date'] = date('Y-m-d H:i:s');
        }
        if (!empty($data['pp']['post_process_entity_id'])) {
            $data['pp']['post_process_entity_id'] = ($data['pp']['post_process_entity_id']) ? $data['pp']['post_process_entity_id'] : null;
        }





        switch ($event['fi_events_type']) {

            case 'database':
                //Solo i vecchi events richiedono di inserire anceh il relativo postprocess... i nuovi sono self-working.... :O
                if (!in_array($data['fi_events_action'], ['notify'])) {
                    $pp_id = $this->new_post_process($data, [
                        'post_process_what' => $data['fi_events_actiondata']['code']
                    ]);
                } else {
                    $pp_id = null;
                }

                //Dopo aver creato il pp, preparo i dati per inserire l'event
                if ($data['_when']) {
                    $event['fi_events_when'] = "{$data['_when']}-{$data['pp']['post_process_when']}";
                } else {
                    $event['fi_events_when'] = $data['pp']['post_process_when'];
                }

                $event['fi_events_post_process_id'] = $pp_id;

                $event['fi_events_ref_id'] = $data['pp']['post_process_entity_id'];
                $event['fi_events_apilib'] = $data['pp']['post_process_apilib'];
                $event['fi_events_api'] = $data['pp']['post_process_api']??null;
                $event['fi_events_crm'] = $data['pp']['post_process_crm'];
                $event['fi_events_module'] = $data['pp']['post_process_module'];

                $return = true;

                break;
            case 'hook':

                if ($data['hook']['hooks_type'] == 'template_hook') {
                    //Se è un template hook, non serve che creo il record hook vero e proprio, in quanto quella tabella serviva solo per retrocompatibilità
                    //Questi nuovi hook vengono direttamente gestiti dal client interrogando la tabella fi_events
                    $event['fi_events_when'] = $data['_when'];
                    $event['fi_events_ref'] = $data['hook']['hooks_ref'];
                    $event['fi_events_module'] = $data['hook']['hooks_module'];
                    $event['fi_events_hook_order'] = $data['hook']['hooks_order'];
                } else {
                    if ($data['_when']) {
                        $event['fi_events_when'] = "{$data['_when']}-{$data['hook']['hooks_type']}";
                    } else {
                        $event['fi_events_when'] = $data['hook']['hooks_type'];
                    }

                    $hook_id = $this->new_hook($data['hook'], [
                        'hooks_content' => $data['fi_events_actiondata']['code'],
                        'hooks_title' => $data['fi_events_title'],
                        'hooks_type' => $event['fi_events_when'],
                    ]);


                    $event['fi_events_hook_id'] = $hook_id;

                    $event['fi_events_ref_id'] = ($data['hook']['hooks_ref']) ? $data['hook']['hooks_ref'] : null;
                    $event['fi_events_hook_order'] = $data['hook']['hooks_order'];

                    $event['fi_events_module'] = $data['hook']['hooks_module'];
                }


                $return = true;
                break;
            case 'cron':
                if ($data['fi_events_action'] == 'curl') {
                    $data['cron']['crons_type'] = 'curl';
                }
                $cron_id = $this->new_cron($data['cron'], [
                    'crons_text' => (!empty($data['fi_events_actiondata']['code'])) ? $data['fi_events_actiondata']['code'] : '',
                    'crons_file' => (!empty($data['fi_events_actiondata']['url'])) ? $data['fi_events_actiondata']['url'] : '',
                    'crons_title' => $data['fi_events_title'],
                    'crons_type' => $data['cron']['crons_type'],
                    'crons_active' => $data['cron']['crons_active'],
                ]);
                $event['fi_events_cron_id'] = $cron_id;

                $event['fi_events_cron_frequency'] = $data['cron']['crons_frequency'];
                $event['fi_events_module'] = $data['cron']['crons_module'];

                $return = true;
                break;

            default:
                $return = "Applet type '{$data['fi_events_type']}' not recognized";
                break;
        }

        if ($return === true) { //If everything was right I can now insert or update event
            //Creo l'applet

            if ($event_id) {
                if (empty($event['fi_events_when'])) {
                    $event['fi_events_when'] = '';
                }
                $this->db->where('fi_events_id', $event_id)->update('fi_events', $event);
            } else {
                if (empty($event['fi_events_ref_id'])) {
                    $last_applet = $this->db->query("SELECT MAX(fi_events_order) as ordine FROM fi_events WHERE fi_events_type = '{$data['fi_events_type']}'");
                } else {
                    $last_applet = $this->db->query("SELECT MAX(fi_events_order) as ordine FROM fi_events WHERE fi_events_type = '{$data['fi_events_type']}' AND fi_events_ref_id = '{$event['fi_events_ref_id']}'");
                }

                if ($last_applet->num_rows() == 0) {
                    $event['fi_events_order'] = 1;
                } else {
                    $event['fi_events_order'] = $last_applet->row()->ordine + 1;
                }
                if (empty($event['fi_events_when'])) {
                    $event['fi_events_when'] = '';
                }
                $this->db->insert('fi_events', $event);
            }
        }
        return $return;
    }
    private function new_post_process($event, $additional_data = [])
    {

        $dati = $event['pp'];
        //debug($additional_data, true);
        if ($this->input->post('_when')) {
            $dati['post_process_when'] = "{$event['_when']}-{$event['pp']['post_process_when']}";
        }
        $dati = array_merge($dati, $additional_data);

        //debug($dati, true);

        $id = $dati['post_process_id'];
        unset($dati['post_process_id']);

        //debug($dati,true);
        // Normalizza gli scopes: se non sono ne 't' ne DB_BOOL_FALSE o se non ci sono assumi falso
        foreach (array('post_process_apilib', 'post_process_api', 'post_process_crm') as $fieldName) {
            if (empty($dati[$fieldName]) or !in_array($dati[$fieldName], array(DB_BOOL_TRUE, DB_BOOL_FALSE))) {
                $dati[$fieldName] = DB_BOOL_FALSE;
            }
        }

        // Post process syntax check
        $tmp = FCPATH . 'tmp-post-process.php';
        $content = implode(PHP_EOL, ['#!/usr/bin/php -q', '<?php', $dati['post_process_what'] /* , '?>' */]);

        @unlink($tmp);
        //        if (file_put_contents($tmp, $content, LOCK_EX) === false OR !file_exists($tmp)) {
        //            echo json_encode(array('status' => 0, 'txt' => 'Impossibile controllare sintassi del post process'));
        //            die();
        //        }
        //        
        //        $output = $return = null;
        //        exec("php -l {$tmp}", $output, $return);
        //        if ($return > 0) {
        //            echo json_encode(array('status' => 0, 'txt' => 'Il codice non è eseguibile da php'.$return));
        //            return;
        //        }
        //        @unlink($tmp);

        if ($id) {
            $this->db->update('post_process', $dati, array('post_process_id' => $id));
            return $id;
        } else {
            $this->db->insert('post_process', $dati);
            return $this->db->insert_id();
            //die($this->db->last_query());

        }
    }
    private function new_hook($event, $additional_data = [])
    {
        // debug($event);
        // debug($additional_data, true);
        $dati = $event;
        //debug($dati, true);
        $dati = array_merge($dati, $additional_data);
        if (empty($dati['hooks_ref'])) {
            $dati['hooks_ref'] = null;
        }
        //debug($dati, true);

        $id = $dati['hooks_id'];
        unset($dati['hooks_id']);

        // Hook syntax check
        $tmp = FCPATH . 'tmp-hook.php';
        $content = implode(PHP_EOL, ['#!/usr/bin/php -q', '<?php', $dati['hooks_content'] /* , '?>' */]);

        @unlink($tmp);
        //        if (file_put_contents($tmp, $content, LOCK_EX) === false OR !file_exists($tmp)) {
        //            echo json_encode(array('status' => 0, 'txt' => 'Impossibile controllare sintassi del post process'));
        //            die();
        //        }
        //        
        //        $output = $return = null;
        //        exec("php -l {$tmp}", $output, $return);
        //        if ($return > 0) {
        //            echo json_encode(array('status' => 0, 'txt' => 'Il codice non è eseguibile da php'.$return));
        //            return;
        //        }
        //        @unlink($tmp);

        if ($id) {
            $this->db->update('hooks', $dati, array('hooks_id' => $id));
            return $id;
        } else {
            $this->db->insert('hooks', $dati);
            return $this->db->insert_id();
            //die($this->db->last_query());

        }
    }
    public function new_cron($event, $additional_data = [])
    {

        $dati = $event;
        $dati = array_merge($dati, $additional_data);

        $id = $dati['crons_id'];
        unset($dati['crons_id']);

        // Hook syntax check
        $tmp = FCPATH . 'tmp-hook.php';
        $content = implode(PHP_EOL, ['#!/usr/bin/php -q', '<?php', $dati['crons_text'] /* , '?>' */]);

        @unlink($tmp);
        //        if (file_put_contents($tmp, $content, LOCK_EX) === false OR !file_exists($tmp)) {
        //            echo json_encode(array('status' => 0, 'txt' => 'Impossibile controllare sintassi del post process'));
        //            die();
        //        }
        //        
        //        $output = $return = null;
        //        exec("php -l {$tmp}", $output, $return);
        //        if ($return > 0) {
        //            echo json_encode(array('status' => 0, 'txt' => 'Il codice non è eseguibile da php'.$return));
        //            return;
        //        }
        //        @unlink($tmp);
        //debug($event, true);
        if ($id) {
            $this->db->update('crons', $dati, array('crons_id' => $id));
            return $id;
        } else {
            $this->db->insert('crons', $dati);
            return $this->db->insert_id();
            //die($this->db->last_query());

        }
    }


}