<?php foreach($notifications as $notification): ?>
    <li data-notification="<?php echo $notification['notifications_id']; ?>" class="<?php echo ($notification['notifications_read']==='f')? 'unread': ''; ?>">
        <?php
        
        if (filter_var($notification['notifications_link'], FILTER_VALIDATE_URL)) {
            
            // Il link è un URL intero,
            // quindi inseriscilo così senza toccarlo
            $link = $notification['notifications_link'];
            
        } elseif (is_numeric($notification['notifications_link'])) {
            
            // Il link è numerico
            // quindi assumo che sia l'id del layout che devo linkare
            $link = base_url("main/layout/{$notification['notifications_link']}");
            
        } elseif ($notification['notifications_link']) {
            
            // Il link non è né un URL, né un numero, ma non è vuoto,
            // quindi assumo che sia un URI e lo wrappo con base_url();
            $link = base_url($notification['notifications_link']);
            
        } else {
            
            // Non è stato inserito nessun link
            // quindi metti un'azione vuota nell'href
            $link = 'javascript:void(0);';
            
        }
        
        ?>
        <a href="<?php echo $link; ?>">
                <?php switch ($notification['notifications_type']) {
                    
                    case NOTIFICATION_TYPE_ERROR:
                        $class = 'label-danger';
                        $icon = 'icon-exclamation-sign';
                        break;
                    
                    case NOTIFICATION_TYPE_INFO:
                        $class = 'label-info';
                        $icon = 'icon-bullhorn';
                        break;
                    
                    case NOTIFICATION_TYPE_MESSAGE:
                        $class = 'label-success';
                        $icon = 'icon-comments-alt';
                        break;
                    
                    case NOTIFICATION_TYPE_WARNING:
                    default :
                        $class = 'label-warning';
                        $icon = 'icon-bell';
                        break;
                } ?>
            <span class="label label-sm label-icon <?php echo $class; ?>"><i class="<?php echo $icon; ?>"></i></span>
            <?php
            echo $notification['notifications_message'];
            $nDate = new DateTime($notification['notifications_date_creation']);
            $diff = $nDate->diff(new DateTime);
            
            if ($diff->d < 1) {
                $date = $nDate->format('H:i');
            } elseif ($diff->days == 1) {
                $date = 'yesterday';
            } else {
                $date = $nDate->format('d M');
            }
            ?>
            <span class="time pull-right"><?php echo $date; ?></span>
            <div class="clearfix"></div>
        </a>
    </li>
<?php endforeach; ?>
