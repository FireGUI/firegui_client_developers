<?php
$itemId = "chats{$grid['grids']['grids_id']}";
$userField = empty($grid['replaces']['user']['fields_name'])? null: $grid['replaces']['user']['fields_name'];
$dateField = empty($grid['replaces']['date']['fields_name'])? null: $grid['replaces']['date']['fields_name'];
$items = array();

if (isset($grid_data['data'])) {
    $item = array();
    foreach ($grid_data['data'] as $x => $dato) {
        
        $thisUser = $userField? $dato[$userField]: null;
        $thisDate = $dateField? strtotime($dato[$dateField]): null;
        
        if (empty($item) OR $item['user'] != $thisUser OR is_null($thisDate) OR is_null($item['date']) OR ($thisDate-$item['date']) > 300) {    // Se sono passati più di 5 min tra un messaggio e l'altro non raggruppare
            if ($item) {
                $items[] = $item;
            }
            
            $item = array(
                'thumb' => empty($grid['replaces']['thumbnail'])? 'assets/img/avatar1.jpg': base_url_template('uploads/' . $dato[$grid['replaces']['thumbnail']['fields_name']]),
                'username' => isset($grid['replaces']['username'])?
                    (
                        ($dato[$grid['replaces']['username']['fields_name']] != null)?
                            $this->datab->build_grid_cell($grid['replaces']['username'], $dato):
                            '<strong>Cliente</strong>'
                    ):
                    null,
                'date' => $thisDate,
                'body' => '',
                'user' => $thisUser,
                'class' => $userField? (($dato[$userField] == $this->auth->get('id'))? 'out': 'in'): (($x%2 == 0) ? 'in' : 'out')
            );
        }
        
        $item['body'] .= (isset($grid['replaces']['text'])? $this->datab->build_grid_cell($grid['replaces']['text'], $dato): '') .
                    ((!empty($grid['replaces']['file']) && !empty($dato[$grid['replaces']['file']['fields_name']]))? $this->datab->build_grid_cell($grid['replaces']['file'], $dato): '') . '<br/>';
        
    }
    
    if ($item) {
        $items[] = $item;
    }
}
?>
<div class="portlet-body" <?php echo "id='{$itemId}'"; ?>>
    <div class="scroller" style="min-height:300px;max-height: 435px;overflow-y:auto" data-always-visible="1" data-rail-visible1="1">
        <ul class="chats">
            <?php foreach ($items as $item): ?>
                <li class="<?php echo $item['class']; ?>">
                    <img class="avatar img-responsive" alt="" src="<?php echo $item['thumb']; ?>" />
                    <div class="message">
                        <span class="arrow"></span>
                        <a href="#" class="name"><?php echo $item['username']; ?></a>

                        <span class="datetime"><?php echo date('d/m/Y H:i', $item['date']); ?></span>
                        <span class="body"><?php echo $item['body']; ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <form class="chat-form" action="<?php echo base_url("db_ajax/new_chat_message/{$grid['grids']['grids_id']}"); ?>">
        <input type="hidden" name="user" value="<?php echo $this->auth->get('id'); ?>" />
        
        <?php if(isset($grid['replaces']['value_id'])): ?>
            <input type="hidden" name="value_id" value="<?php echo $value_id; ?>" />
        <?php endif; ?>
            
        <div class="input-cont">   
            <input class="form-control" type="text" name="text" placeholder="Scrivi un messaggio..." />
        </div>
        <div class="btn-cont"> 
            <span class="arrow"></span>
            <button class="btn blue icn-only"><i class="icon-ok icon-white"></i></button>
        </div>
    </form>
</div>


<script>

    var ChatWidget = function() {
        
        return {
            
            element: $('#<?php echo $itemId; ?>'),
            
            sendMessage: function(event) {
                
                event.preventDefault();
                var widget = event.data;
                
                var form = $(this);
                $.post(form.attr('action'), form.serialize(), function(json) {
                    widget.appendMessage(json); $('[name=text]', form).val('');
                    
                    // Se siamo in una modale comunichiamo che i dati sono stati
                    // salvati
                    $('.modal').each(function() {
                        try {
                            $(this).data('bs.modal').askConfirmationOnClose = false;
                        } catch (e) {}
                    });
                }, 'json');
                
            },
            
            appendMessage: function(message) {
                var chatContainer = $('.chats', this.element);
                //var thisClass = ($('li:last-child', chatContainer).hasClass('in')? 'out': 'in');
                var thisClass = 'out';  // I miei messaggi sono sempre a dx...
                var listItem = $('<li/>').addClass(thisClass);
                listItem.append(
                    $('<img/>').attr('src', message.thumbnail).addClass('avatar img-responsive'),
                    $('<div/>').addClass('message').append(
                        $('<span/>').addClass('arrow'),
                        $('<a/>').addClass('name').attr('href', '#').html(message.username),
                        $('<span/>').addClass('datetime').html(message.date),
                        $('<span/>').addClass('body').html(message.text)
                    )
                ).appendTo(chatContainer);
                this.scrollChat();
            },
            
            scrollChat: function() {
                var scroller = $('.scroller', this.element);
                scroller.scrollTop(scroller.height());
            },
            
            init: function() {
                $('.chat-form', this.element).on('submit', this, this.sendMessage);
                setTimeout(this.scrollChat, 800);
            }
        };
        
    }();


    $(document).ready(ChatWidget.init());
    
</script>