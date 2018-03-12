
<!-- BEGIN INBOX DROPDOWN -->
<li class="dropdown" id="header_inbox_bar">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <i class="fa fa-envelope"></i>
        <span class="js_message_number badge"></span>
    </a>
    <ul class="dropdown-menu extended inbox">
        <li>
            <p>You have <strong class="js_message_number"></strong> new messages</p>
        </li>
        <li>
            <ul class="js_messages_dropdown dropdown-menu-list scroller" style="height: 250px;">
            </ul>
        </li>
        <li class="external">   
            <a href="<?php echo base_url('messages'); ?>">See all messages <i class="m-icon-swapright"></i></a>
        </li>
    </ul>
</li>
<!-- END INBOX DROPDOWN -->




<script>

    $(document).ready(function() {

        function fetch_messages() {
            $.ajax({
                url: base_url + 'messages/get_ajax/dropdown_message_list',
                dataType: 'json',
                success: function(json) {
                    $('.js_messages_dropdown').html(json.view);
                    $('.js_message_number').text(json.count>0? json.count: '');
                    setTimeout(fetch_messages, 30000);
                }
            });
        }
        
        fetch_messages();
        
    });

</script>