
<!-- BEGIN NOTIFICATION DROPDOWN -->
<li class="dropdown" id="header_notification_bar">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown"
       data-close-others="true">
        <i class="icon-comment"></i>
        <span class="js_notification_number_label badge"></span>
    </a>
    <ul class="dropdown-menu extended notification">
        <li><p>Hai <span class="js_notification_number"></span> nuove notifiche</p></li>
        <li><ul class="js_notification_dropdown_list dropdown-menu-list scroller" style="height: 250px;"></ul></li>
        <?php /*
        <li class="external">   
            <a href="#">See all notifications <i class="m-icon-swapright"></i></a>
        </li>
         */ ?>
    </ul>
</li>
<!-- END NOTIFICATION DROPDOWN -->




<script>

    var CrmNotifier = {
        
        /**
         * jQuery object
         * @type {jQuery}
         */
        listContainer: $('#header_notification_bar'),
        
        /**
         * Number of the unread notifications
         * @type Number
         */
        number: 0,
        
        audio: null,
        played: false,
        
        
        fetch: function() {
            console.log('Fetching notification list');
            var notifier = this;
            $.ajax({
                url: base_url + 'get_ajax/dropdown_notification_list',
                dataType: 'json',
                success: function(json) {
                    notifier.number = json.count;
                    notifier.showUnread(json.view);
                    if (notifier.number > 0) {
                        notifier.playNotificationSound();
                    }
                }
            });
        },
        
        showUnread: function(html) {
            
            if(typeof html === 'string') {
                $('.js_notification_dropdown_list', this.listContainer).html(html);
            }
                
            $('.js_notification_number_label', this.listContainer).text(this.number > 0? this.number: '');
            $('.js_notification_number', this.listContainer).text(this.number);
            
        },
        
        playNotificationSound: function() {
            if (!this.played) {
                var notificationSound = base_url_template + 'script/js/notify-sound.mp3';
                if (!this.audio) {
                    this.audio = $('<audio/>').hide().append($('<source/>').attr('src', notificationSound)).appendTo('body');
                }

                this.audio[0].play();
                this.played = true;
                
                // Suona una volta ogni ora e mezza se la pagina non è
                // refreshata
                var context = this;
                setTimeout(function () { context.played = false; }, 5400000);
            }
        },
        
        setRead: function(notificationId) {
            if(this.number < 1 && !notificationId) {
                return null;
            }
            
            console.log(notificationId? 'Reading notification' + notificationId: 'Reading all notifications');
            return $.ajax(base_url + 'db_ajax/notify_read/' + ((typeof notificationId === 'undefined')? '': notificationId));
        },
        
        
        readAll: function() {
            var ajax = this.setRead();
            if (ajax !== null) {
                var notifier = this;
                ajax.success(function() {
                    notifier.number = 0; 
                    notifier.showUnread();
                });
            }
        },
        
        
        readAndGoto: function(notificationId, link) {
            var ajax = this.setRead(notificationId);
            if (ajax !== null) {
                ajax.success(function() {
                    console.log('Going to ' + link);
                    window.location = link;
                });
            }
        },
        
        
        init: function() {
            var notifier = this;
            notifier.fetch();
            
            setInterval(function() {
                notifier.fetch();
            }, 30000);
            
            /*notifier.listContainer.on('mouseenter', function(e) {
                notifier.readAll();
            });*/
        
            notifier.listContainer.on('click', '[data-notification]', function(e) {
                e.preventDefault();
                
                var $this = $(this);
                notifier.readAndGoto($this.data('notification'), $('a', $this).attr('href'));
            });
            
        }
        
    };



    $(document).ready(function() {
        CrmNotifier.init();
    });

</script>