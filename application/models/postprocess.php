<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * 
 *  SI SUPPONE CHE NEL REF DI UN FIELDS CI SARà IL NOME TABELLA E NON L'ID O IL NOME DEL FIELDS CHE DEVE JOINARE... PERCHè? PERCHè SI è STABILITO, CHE OGNI TABELLA DI SUPPORTO O ENTITà AVRà PER FORZA
 * UN CAMPO NOMETABELLA_ID E QUINDI IN AUTOMATICO, INSERENDO IL NOME TABELLA VERRà PRESO QUEL CAMPO CONCATENANDO _ID... NON è UN ACCROCCHIO ANCHE SE LO PUò SEMBRARE...
 * TUTTO QUESTO FORSE PER EVITARE DI DOVE INSERIRE NEI FIELDS ANCHE LA TABELLA O ENTITà A CUI FANNO RIFERIMENTO ED IL CAMPO DA JOINARE...
 * 
 */

class Postprocess extends CI_Model {

    var $template = array();

    function __construct() {
        parent :: __construct();
    }

    
    public function new_mass_notifications($to_entity, $message) {
        
        $entity = $this->datab->get_entity_by_name($to_entity);
        $data_entity = $this->datab->get_data_entity($entity);
        $field_id = $entity['entity_name']."_id";
        
        foreach ($data_entity['data'] as $dato) {
            $this->new_notification($dato[$field_id] ,$message);
        }
    }
    
    public function new_notification($to_id, $message) {
        $notification = array('to_id' => $to_id,
                              'message' => $message);
        
        $this->db->insert('notifications', $notification);
    }
    
    
    
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */