<?php

/**
 * Attenzione!! Questo file è deprecato ed è stato spostato nel model
 * @deprecated
 */

/**
 * Fetch the value
 */
if ($field['fields_ref']) {
    $value = array_key_exists($field['fields_name'], $dato)? $dato[$field['fields_name']]: '';
} else {
    $value = array_key_exists($field['fields_name'], $dato)? $dato[$field['fields_name']]: '';
}

if($value !== '' && (!$field['fields_ref'] OR $value)) {
    if ($field['fields_ref']) {
        if(is_array($value)) {
            // Ho una relazione molti a molti - non mi serve alcuna informazione sui field ref, poiché ho già la preview stampata
            echo implode('<br/>', $value);
        } elseif(!empty($field['support_fields'])) {
            // Ho un field ref semplice - per stamparlo ho bisogno dei support fields (che sono i campi preview dell'entità referenziata)
            $link = $value? $this->datab->get_detail_layout_link($field['support_fields'][0]['fields_entity_id']): false;
            
            if(empty($field['support_fields'])) {
                // Non ho nessun campo di preview, quindi la preview sarà vuota - stampo solo l'ID del record
                $text = $value;
            } else {
                $hasAllFields = true;
                $_text = array();
                foreach ($field['support_fields'] as $support_field) {
                    if(array_key_exists($field['fields_name'].'_'.$support_field['fields_name'], $dato)) {
                        $_text[] = $dato[$field['fields_name'].'_'.$support_field['fields_name']];
                        
                    } elseif(array_key_exists($support_field['fields_name'], $dato)) {
                        // Appendo il nuovo campo preview all'array della preview $_text
                        $_text[] = $dato[$support_field['fields_name']];
                    } else {
                        // Non posso continuare a stampare la preview perché ci sono campi non presenti
                        $hasAllFields = false;
                        break;
                    }
                }
                
                if($hasAllFields) {
                    // La preview completa sta nell'arrat $_text
                    $text = implode(' ', $_text);
                } else {
                    // Non ho tutti i campi preview disponibili (ad es. nelle relazioni NxM), quindi faccio una chiamata alla get entity preview
                    $value_id = (int) $value;
                    $preview = $this->datab->get_entity_preview_by_name($field['fields_ref'], "{$field['fields_ref']}_id = '{$value_id}'", 1);
                    $text = isset($preview[$value])? $preview[$value]: $value;
                }
            }
            
            // C'è un link? stampo un <a></a> altrimenti stampo il testo puro e semplice
            echo $link? anchor(rtrim($link, '/') . '/' . $value, $text): $text;
        }
    } else {
        // Posso stampare il campo in base al tipo
        switch ($field['fields_draw_html_type']) {
            case 'upload':
                if($value) {
                    echo anchor(base_url_template("uploads/$value"), 'Scarica file', array('target' => '_blank'));
                }
                break;

            case 'upload_image':

                if($value) {
                    echo anchor(base_url_template("uploads/{$value}"), "<img src='".base_url_template("imgn/1/50/50/uploads/{$value}")."' style='width: 50px;' />", array('class' => 'fancybox', 'style' => 'width:50px'));
                } else {
                    $path = base_url_template('images/no-image-50x50.gif');
                    echo "<img src='{$path}' style='width: 50px;' />";
                }
                break;


            case 'textarea':
                $style = 'white-space: pre-line';
            case 'wysiwyg':
                if(empty($style)) {
                    $style = '';
                }
                
                $stripped = strip_tags($value);
                $value = preg_replace(array('#<script(.*?)>(.*?)</script>#is', '/<img[^>]+\>/i'), '', $value);
                //$value = $this->security->xss_clean($value);
                
                if(strlen($stripped) > 150) {
                    /*$modalKey = md5($value.microtime(true));
                    echo '<div data-target=".modal_'.$modalKey.'" data-toggle="modal" style="cursor:pointer;">'.nl2br(character_limiter($stripped, 130)).'</div>';
                    echo '<a href="#" data-target=".modal_'.$modalKey.'" data-toggle="modal">Vedi tutto</a>';
                    echo '<div class="modal modal_'.$modalKey.'">';
                    echo '<div class="modal-dialog"><div class="modal-content">';
                    echo '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button><h4 class="modal-title">Vedi tutto</h4></div>';
                    echo '<div class="modal-body" style="'.$style.'">'.(($field['fields_draw_html_type']=='textarea')? nl2br($stripped): $value).'</div>';
                    echo '</div></div></div>';*/
                    
                    $textContainerID = md5($value);
                    $javascript = "$(this).parent().hide(); $('.text_{$textContainerID}').show();";
                    
                    echo '<div><div onclick="'.$javascript.'" style="cursor:pointer;">'.nl2br(character_limiter($stripped, 130)).'</div>';
                    echo '<a onclick="'.$javascript.'" href="#">Vedi tutto</a></div>';
                    echo '<div class="text_'.$textContainerID.'" style="display:none;'.$style.'">'.(($field['fields_draw_html_type']=='textarea')? nl2br($stripped): $value).'</div>';
                } else {
                    echo (($field['fields_draw_html_type']=='textarea')? nl2br($stripped): $value);
                }
                break;

            case 'date':
                echo "<span class='hide'>{$value}</span>";
                echo dateFormat($value);
                break;

            case 'date_time':
                echo "<span class='hide'>{$value}</span>";
                echo dateTimeFormat($value);
                break;

            case 'stars':
                echo "<span class='hide'>{$value}</span>";
                for($i=1; $i<=5; $i++) {
                    $class = $i > $value ? 'icon-star-empty': 'icon-star';
                    echo "<i class='{$class}'></i>";
                }
                break;

            case 'radio':
            case 'checkbox':
                echo (($field['fields_type'] == 'BOOL')? (($value == 't')? 'Si' : 'No'): $value);
                break;

            default:
                
                if($field['fields_type'] === 'DATERANGE') {
                    
                    // Formato daterange 
                    $dates = dateRange_to_dates($value);
                    if(count($dates)==2) {
                        echo 'Dal ' . dateFormat($dates[0]) . ' al ' . dateFormat($dates[1]);
                    }
                    
                } elseif(filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    echo mailto($value);
                } elseif (filter_var($value, FILTER_VALIDATE_URL) || (preg_match("/\A^www.( [^\s]* ).[a-zA-Z]$\z/ix", $value) && filter_var('http://'.$value, FILTER_VALIDATE_URL) !== false )) {
                    
                    if(stripos($value, 'http://') === false) {
                        $value = 'http://'.$value;
                    }
                    
                    echo anchor($value, str_replace(array('http://', 'https://'), '', $value), array('target' => '_blank'));
                } else {
                    echo $value;
                }
                break;

        }
    }
} elseif($field['fields_draw_placeholder']) {
    echo "<small class='text-muted'>{$field['fields_draw_placeholder']}</small>";
}

