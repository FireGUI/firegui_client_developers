<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Postprocess extends CI_Model
{

    var $template = array();

    function __construct()
    {
        parent::__construct();
    }


    public function new_mass_notifications($to_entity, $message)
    {

        $entity = $this->datab->get_entity_by_name($to_entity);
        $data_entity = $this->datab->get_data_entity($entity);
        $field_id = $entity['entity_name'] . "_id";

        foreach ($data_entity['data'] as $dato) {
            $this->new_notification($dato[$field_id], $message);
        }
    }

    public function new_notification($to_id, $message)
    {
        $notification = array(
            'to_id' => $to_id,
            'message' => $message
        );

        $this->db->insert('notifications', $notification);
    }
}
