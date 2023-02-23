<?php
    $itemId = "chats{$grid['grids']['grids_id']}";
    $userField = empty($grid['replaces']['user']['fields_name']) ? null : $grid['replaces']['user']['fields_name'];
    $dateField = empty($grid['replaces']['date']['fields_name']) ? null : $grid['replaces']['date']['fields_name'];
    $entityField = $grid['grids']['entity_name'];
    $isDeletedField = empty($grid['replaces']['is_deleted']['fields_name']) ? null : $grid['replaces']['is_deleted']['fields_name'];
    $items = [];
    
    if (isset($grid_data['data'])) {
        foreach ($grid_data['data'] as $x => $dato) {
            $thisUser = $userField ? $dato[$userField] : null;
            $thisDate = $dateField ? strtotime($dato[$dateField]) : null;
            
            $item = [
                    'username' => '',
                    'thumb' => '',
                    'date' => $thisDate,
                    'body' => $this->datab->build_grid_cell($grid['replaces']['text'], $dato),
                    'user' => $thisUser,
                    'data' => $dato,
                    'grid' => $grid,
                    'message_id' => $dato["{$entityField}_id"],
                    'is_deleted' => (!empty($isDeletedField) && $dato[$isDeletedField] == DB_BOOL_TRUE) ? DB_BOOL_TRUE : DB_BOOL_FALSE,
                    'class' => $userField ? (($dato[$userField] == $this->auth->get('id')) ? 'right' : 'out') : (($x % 2 == 0) ? 'out' : 'right'),
                    'id' => $dato[$grid['replaces']['value_id']['fields_name']]
            ];
            
            if (isset($grid['replaces']['username'])) {
                if (!empty($dato[$grid['replaces']['username']['fields_name']])) {
                    $item['username'] = $this->datab->build_grid_cell($grid['replaces']['username'], $dato);
                } else {
                    $item['username'] = '<strong>User</strong>';
                }
            } else {
                $item['username'] = '<strong>User</strong>';
            }
            
            if (isset($grid['replaces']['thumbnail'])) {
                if (!empty($dato[$grid['replaces']['thumbnail']['fields_name']])) {
                    $item['thumb'] = base_url_uploads('uploads/' . $dato[$grid['replaces']['thumbnail']['fields_name']]);
                } else {
                    $item['thumb'] = base_url('/images/user.png');
                }
            } else {
                $item['thumb'] = base_url('/images/user.png');
            }
            
            $items[] = $item;
        }
    }
?>

<div class="direct-chat direct-chat-primary" <?php echo "id='{$itemId}'"; ?>>
    <div class="scroller" data-always-visible="1" data-rail-visible1="1">
        <ul class="direct-chat-messages chats">
            <?php if (!empty($items)) : ?>
                <?php foreach ($items as $item) : ?>
                    <li class="direct-chat-msg <?php echo $item['class']; ?>" data-id="<?php echo $item['message_id']; ?>">
                        <div class="direct-chat-primary clearfix">
                            <a href="#" class="direct-chat-name pull-left name"><?php echo $item['username']; ?></a>
                            <span class="direct-chat-timestamp pull-right"><span class="datetime"><?php echo date('d/m/Y H:i', $item['date']); ?></span>&nbsp;<span class="actions"></span></span>
                            <?php if(!empty($isDeletedField) && $item['is_deleted'] == DB_BOOL_FALSE && $item['user'] == $this->auth->get('users_id')): ?>
                                <a href="#" class="direct-chat-delete pull-right delete" data-message_id="<?php echo $item['message_id']; ?>"><i class="fas fa-trash text-danger" data-toggle="tooltip" title="<?php e('Delete') ?>"></i>&nbsp;</a>
                            <?php endif; ?>
                        </div>
                        <?php if (isset($item['thumb'])) : ?><img class="direct-chat-img avatar" src="<?php echo (!empty($item['thumb'])) ? $item['thumb'] : base_url('images/user.png'); ?>" alt="message user image"><?php endif; ?>
                        <div class="direct-chat-text body">
                            <?php echo ($item['is_deleted'] == DB_BOOL_TRUE) ? '<i>' . t('Message deleted') . '</i>' : $item['body']; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="chat-info-message text-center"><img src="<?php echo base_url('images/messages.png'); ?>" style="height: 100px; width: 100px; margin: 0 auto;" alt=""><br /><?php e('<h3><b>No messages yet</b></h3>'); ?></div>
            <?php endif; ?>
        </ul>
        
        <form class="chat-form" action="<?php echo base_url("db_ajax/new_chat_message/{$grid['grids']['grids_id']}"); ?>">
            <?php add_csrf(); ?>
            
            <input type="hidden" name="user" value="<?php echo $this->auth->get('id'); ?>" />
            
            <?php if (isset($grid['replaces']['value_id'])) : ?>
                <input type="hidden" name="value_id" value="<?php echo (!empty($layout_data_detail)) ? ((array_key_exists($grid['replaces']['value_id']['fields_name'], $layout_data_detail)) ? $layout_data_detail[$grid['replaces']['value_id']['fields_name']] : $value_id) : $value_id; ?>" />
            <?php endif; ?>
            <div class="input-group">
                <input class="form-control" name="text" placeholder="<?php e('Write a message...') ?>">
                
                <div class="input-group-btn">
                    <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php /* if (!isset($grid['replaces']['thumbnail'])) : ?>
    <style>
        .right .direct-chat-text {
            margin-right: 1px !important;
        }
        
        .direct-chat-text {
            margin-left: 1px !important;
        }
    
    </style>
<?php endif; */ ?>

<script>
    var ChatWidget = function() {
        return {
            element: $('#<?php echo $itemId; ?>'),
            
            sendMessage: function(event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                event.stopPropagation();
                
                var widget = event.data;
                
                var form = $(this);

                var message_text = $('[name="text"]', form).val();

                if (!message_text || message_text.length <= 0) {
                    return;
                }

                $.post(form.attr('action'), form.serialize(), function(json) {
                    widget.appendMessage(json);
                    
                    $('[name=text]', form).val('');
                    
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
                $('.chat-info-message').remove();
                var chatContainer = $('.chats', this.element);
                var thisClass = 'direct-chat-msg right'; // I miei messaggi sono sempre a dx...
                var listItem = $('<li/>').addClass(thisClass);
                var isDeletedField = '<?php echo $isDeletedField ?>';
                listItem.append(
                        $('<div/>').addClass('direct-chat-primary clearfix').append(
                                $('<a/>').addClass('direct-chat-name pull-left name').attr('href', '#').html(message.username),
                                $('<span/>').addClass('direct-chat-timestamp pull-right datetime').html(message.date),
                                (isDeletedField.length > 0 ? $('<a/>').addClass('direct-chat-delete pull-right delete').attr('data-message_id', message.id).attr('href', '#').html('<i class="fas fa-trash text-danger"></i>&nbsp;') : ''),
                        ),
                        (typeof value !== "undefined" ? $('<img/>').attr('src', message.thumbnail).addClass('direct-chat-img avatar img-responsive') : ''),
                        $('<div/>').addClass('direct-chat-text body').append(
                                $('<span/>').html(message.text)
                        )
                ).appendTo(chatContainer);
                
                this.init();
            },
            
            scrollChat: function() {
                var scroller = $('.scroller', this.element);
                scroller.scrollTop(scroller.height());
            },
            
            deleteMessage: function() {
                if (!confirm('<?php e('Are you sure to delete this message?') ?>')) {
                    return;
                }
                
                var message_id = $(this).data('message_id');
                var chat_msg = $(this).closest('.direct-chat-msg');
                
                $.ajax({
                    url: base_url + 'db_ajax/switch_bool/<?php echo $isDeletedField; ?>/' + message_id,
                    async: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status == '5') {
                            $('.direct-chat-text', chat_msg).html('<i><?php e('Message deleted') ?></i>');
                            $('.direct-chat-delete', chat_msg).remove();
                        }
                    },
                })
            },
            
            init: function() {
                $('.chat-form', this.element).on('submit', this, this.sendMessage);
                setTimeout(this.scrollChat, 800);
                
                $('.direct-chat-delete', this.element).on('click', this, this.deleteMessage);
            }
        };
    }();
    
    $(document).ready(ChatWidget.init());
</script>
