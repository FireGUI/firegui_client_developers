<?php $itemId = "chats{$grid['grids']['grids_id']}"; ?>
<div class="portlet-body" <?php echo "id='{$itemId}'"; ?>>
    <div class="scroller" style="min-height:100px;max-height: 435px;overflow-y:auto" data-always-visible="1" data-rail-visible1="1">
        <ul class="chats">
            <?php if (isset($grid_data['data'])): ?>
            <?php $x=0;?>
                <?php foreach ($grid_data['data'] as $dato): ?>
                    <li class="<?php echo ($x%2 == 0) ? 'in' : 'out'; ?>">
                        <?php if(isset($grid['replaces']['thumbnail'])): ?>
                            <img class="avatar img-responsive" alt="" src="<?php echo base_url("uploads/{$dato[$grid['replaces']['thumbnail']['fields_name']]}"); ?>" />
                        <?php else: ?>
                            <img class="avatar img-responsive" alt="" src="assets/img/avatar1.jpg" />
                        <?php endif; ?>
                            
                        <div class="message">
                            <span class="arrow"></span>
                            <a href="#" class="name">
                                <?php 
                                if(isset($grid['replaces']['username'])) {
                                    echo ($dato[$grid['replaces']['username']['fields_name']] != null)? $this->datab->build_grid_cell($grid['replaces']['username'], $dato): '<strong>Cliente</strong>';
                                }
                                ?>
                                <?php /*if(isset($grid['replaces']['username'])): ?>
                                    <?php if ($dato[$grid['replaces']['username']['fields_name']] != null): ?>
                                        <?php $this->load->view('box/grid/td', array('field'=>$grid['replaces']['username'], 'dato'=>$dato)); ?>
                                    <?php else: ?>
                                        <strong>Cliente</strong>
                                    <?php endif; ?>
                                <?php endif;*/ ?>
                            </a>
                            <span class="datetime"><?php echo isset($grid['replaces']['date'])? dateFormat($dato[$grid['replaces']['date']['fields_name']]): null ?></span>
                            <span class="body">
                                <?php
                                if(isset($grid['replaces']['text'])) {
                                    //$this->load->view('box/grid/td', array('field'=>$grid['replaces']['text'], 'dato'=>$dato));
                                    echo $this->datab->build_grid_cell($grid['replaces']['text'], $dato);
                                }
                                if(!empty($grid['replaces']['file'])) {
                                    echo 'File allegato: <a href="http://sfera.h2-web.com/dev/mastercrm_h2/uploads/'.$dato[$grid['replaces']['file']['fields_name']].'" target="_blank">'.$dato[$grid['replaces']['file']['fields_name']].'</a>';
                                }
                                ?>
                            </span>
                        </div>
                    </li>
                    <?php $x++; ?>
                <?php endforeach; ?>
            <?php endif; ?>
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
                $.post(form.attr('action'), form.serialize(), function(json) { widget.appendMessage(json); $('[name=text]', form).val(''); }, 'json');
                
            },
                    
            appendMessage: function(message) {
                var chatContainer = $('.chats', this.element);
                var thisClass = ($('li:last-child', chatContainer).hasClass('in')? 'out': 'in');
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