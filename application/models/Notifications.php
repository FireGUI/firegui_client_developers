<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * @property-read Crmentity $crmentity
 */
class Notifications extends CI_Model
{



    function __construct()
    {
        parent::__construct();
    }

    public function create($notification)
    {
        $this->db->insert('notifications', $notification);
        return $this->db->insert_id();
    }
    public function edit($notification_id, $notification)
    {
        $this->db
            ->where('notifications_id', $notification_id)
            ->update('notifications', $notification);
    }
    public function delete($notification_id)
    {
        $this->db
            ->where('notifications_id', $notification_id)
            ->delete('notifications');
    }
    public function setRead($notification_id, $read = true)
    {
        $this->db->update(
            'notifications',
            array('notifications_read' => ($read) ? DB_BOOL_TRUE : DB_BOOL_FALSE),
            array(
                'notifications_user_id' => $this->auth->get('id'),
                'notifications_id' => $notification_id,
            )
        );
    }
    public function setDesktopNotified($notification_id, $read = true)
    {
        $this->db->update(
            'notifications',
            array('notifications_desktop_notified' => ($read) ? DB_BOOL_TRUE : DB_BOOL_FALSE),
            array(
                'notifications_user_id' => $this->auth->get('id'),
                'notifications_id' => $notification_id,
            )
        );
    }
    public function setReadAll($read = true)
    {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");
        $this->db->update(
            'notifications',
            array(
                'notifications_read' => ($read) ? DB_BOOL_TRUE : DB_BOOL_FALSE
            ),
            array('notifications_user_id' => $user_id)
        );
    }
    public function search($where = [], $limit = null, $offset = 0)
    {
        $user_id = $this->auth->get(LOGIN_ENTITY . "_id");

        if (is_numeric($limit) && $limit > 0) {
            $this->db->limit($limit);
        }

        if (is_numeric($offset) && $offset > 0) {
            $this->db->offset($offset);
        }

        $notifications = $this->db->order_by('notifications_read')->order_by('notifications_date_creation', 'desc')->get_where('notifications', array('notifications_user_id' => $user_id))->result_array();

        return array_map(function ($notification) {
            switch (true) {
                case filter_var($notification['notifications_link'], FILTER_VALIDATE_URL):
                    // Il link è un URL intero, quindi inseriscilo così senza toccarlo
                    $href = $notification['notifications_link'];
                    break;

                case is_numeric($notification['notifications_link']):
                    // Il link è numerico, quindi assumo che sia l'id del layout che devo linkare
                    $href = base_url("main/layout/{$notification['notifications_link']}");
                    break;

                case $notification['notifications_link']:
                    // Il link non è né un URL, né un numero, ma non è vuoto, quindi assumo che sia un URI e lo wrappo con base_url();
                    $href = base_url($notification['notifications_link']);
                    break;

                default:
                    // Non è stato inserito nessun link quindi metti un'azione vuota nell'href
                    $href = null;
            }


            switch ($notification['notifications_type']) {
                case NOTIFICATION_TYPE_ERROR:
                    $label = ['class' => 'bg-red-thunderbird', 'icon' => 'fas fa-exclamation'];
                    break;

                case NOTIFICATION_TYPE_INFO:
                    $label = ['class' => 'bg-blue-steel', 'icon' => 'fas fa-bullhorn'];
                    break;

                case NOTIFICATION_TYPE_MESSAGE:
                    $label = ['class' => 'bg-green-jungle', 'icon' => 'fas fa-comment'];
                    break;

                case NOTIFICATION_TYPE_WARNING:
                default:
                    $label = ['class' => 'bg-yellow-gold', 'icon' => 'fas fa-bell'];
                    break;
            }

            $nDate = new DateTime($notification['notifications_date_creation']);
            $diff = $nDate->diff(new DateTime);

            switch (true) {
                case $diff->d < 1:
                    $datespan = $nDate->format('H:i');
                    break;
                case $diff->days == 1:
                    $datespan = 'yesterday';
                    break;
                default:
                    $datespan = $nDate->format('d M');
            }

            $notification['href'] = $href;
            $notification['label'] = $label;
            $notification['datespan'] = $datespan;
            return $notification;
        }, $notifications);
    }
}
