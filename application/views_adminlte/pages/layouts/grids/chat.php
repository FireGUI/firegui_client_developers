<?php
$itemId = "chats{$grid['grids']['grids_id']}";
$userField = empty($grid['replaces']['user']['fields_name']) ? null : $grid['replaces']['user']['fields_name'];
$dateField = empty($grid['replaces']['date']['fields_name']) ? null : $grid['replaces']['date']['fields_name'];
$items = array();

if (isset($grid_data['data'])) {
    $item = array();
    foreach ($grid_data['data'] as $x => $dato) {
        $thisUser = $userField ? $dato[$userField] : null;
        $thisDate = $dateField ? strtotime($dato[$dateField]) : null;

        if (empty($item) or $item['user'] != $thisUser or is_null($thisDate) or is_null($item['date']) or ($thisDate - $item['date']) > 60) {    // Se sono passati più di 1 min tra un messaggio e l'altro non raggruppare
            if ($item) {
                $items[] = $item;
            }
            $item = array(
                'username' => isset($grid['replaces']['username']) ?
                    (
                        ($dato[$grid['replaces']['username']['fields_name']] != null) ?
                        $this->datab->build_grid_cell($grid['replaces']['username'], $dato) :
                        '<strong>User</strong>') :
                    null,
                'date' => $thisDate,
                'body' => '',
                'user' => $thisUser,
                'class' => $userField ? (($dato[$userField] == $this->auth->get('id')) ? 'right' : 'out') : (($x % 2 == 0) ? 'out' : 'right'),
                'id' => $dato[$grid['replaces']['value_id']['fields_name']]
            );

            if (isset($grid['replaces']['thumbnail'])) {
                if (!empty($dato[$grid['replaces']['thumbnail']['fields_name']])) {
                    $item['thumb'] = base_url_uploads('uploads/' . $dato[$grid['replaces']['thumbnail']['fields_name']]);
                } else {
                    $item['thumb'] = base_url('/images/user.png');
                }
            }
        }

        $item['body'] .= (isset($grid['replaces']['text']) ? $this->datab->build_grid_cell($grid['replaces']['text'], $dato) : '') .
            ((!empty($grid['replaces']['file']) && !empty($dato[$grid['replaces']['file']['fields_name']])) ? $this->datab->build_grid_cell($grid['replaces']['file'], $dato) : '') . '<br/>';
    }

    if ($item) {
        $items[] = $item;
    }
}
?>

<div class="direct-chat direct-chat-primary" <?php echo "id='{$itemId}'"; ?>>
    <div class="scroller" data-always-visible="1" data-rail-visible1="1">
        <ul class="direct-chat-messages chats">
            <?php if (!empty($items)) : ?>
                <?php foreach ($items as $item) : ?>
                    <li class="direct-chat-msg <?php echo $item['class']; ?>" data-id="<?php echo $item['id'] ?? null ?>">
                        <div class="direct-chat-primary clearfix">
                            <a href="#" class="direct-chat-name pull-left name"><?php echo $item['username']; ?></a>
                            <span class="direct-chat-timestamp pull-right"><span class="datetime"><?php echo date('d/m/Y H:i', $item['date']); ?></span>&nbsp;<span class="actions"></span></span>
                        </div>
                        <?php if (isset($item['thumb'])) : ?><img class="direct-chat-img avatar" src="<?php echo (!empty($item['thumb'])) ? $item['thumb'] : base_url('images/user.png'); ?>" alt="message user image"><?php endif; ?>
                        <div class="direct-chat-text body">
                            <?php echo $item['body']; ?>
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

<style>
    <?php if (!isset($grid['replaces']['thumbnail'])) : ?>.right .direct-chat-text {
        margin-right: 1px !important;
    }

    .direct-chat-text {
        margin-left: 1px !important;
    }

    <?php endif; ?>
</style>

<script>
    var ChatWidget = function() {

        return {

            element: $('#<?php echo $itemId; ?>'),

            sendMessage: function(event) {

                event.preventDefault();
                var widget = event.data;

                var form = $(this);
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
                listItem.append(
                    $('<div/>').addClass('direct-chat-primary clearfix').append(
                        $('<a/>').addClass('direct-chat-name pull-left name').attr('href', '#').html(message.username),
                        $('<span/>').addClass('direct-chat-timestamp pull-right datetime').html(message.date),
                    ),
                    (typeof value !== "undefined" ? $('<img/>').attr('src', message.thumbnail).addClass('direct-chat-img avatar img-responsive') : ''),
                    $('<div/>').addClass('direct-chat-text body').append(
                        $('<span/>').html(message.text)
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