<!-- BEGIN NOTIFICATION DROPDOWN -->
<li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
        <i class="fas fa-bell"></i>
        <span class="js_notification_number_label badge badge-danger"></span>
    </a>
    <ul class="dropdown-menu" style="width:400px!important;max-width:none!important">
        <li class="external">
            <h3>Hai <span class="js_notification_number bold"><!-- ... notification count here ... --></span> nuove notifiche</h3>
            <a href="#" onclick="CrmNotifier.readAll();return false;" role='button'>segna come letto</a>
        </li>
        <li>
            <ul class="js_notification_dropdown_list dropdown-menu-list scroller" style="height: 450px"><!-- ... notification list here ... --></ul>
        </li>
        
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
            var notifier = this;
            $.ajax({
                url: base_url + 'get_ajax/dropdown_notification_list',
                dataType: 'json',
                success: function(json) {
                    notifier.number = json.count;
                    notifier.showUnread(json.view);
                    if (notifier.number > 0) {
                        var baseSoundFolder = base_url_scripts + 'script/js/';
                        //notifier.playNotificationSound(baseSoundFolder + (json.errors > 0? 'error-sound': 'notify-sound') + '.mp3');
                        notifier.playNotificationSound(baseSoundFolder + 'notify-sound.mp3');
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
        
        playNotificationSound: function(notificationSound) {
            
            if (!notificationSound) {
                alert('Ci sono nuove notifiche');
                return;
            }
            
            if (this.isPlayable()) {
                if (!this.audio) {
                    this.audio = $('<audio/>').hide().append($('<source/>')).appendTo('body');
                }
                
                $('source', this.audio).attr('src', notificationSound);

                this.audio[0].play();
                localStorage.setItem('played', new Date());
            }
        },
        
        isPlayable: function() {
            
            var time = localStorage.getItem('played');
            if (!time) {
                return true;
            }
            
            var diff = new Date() - new Date(time);
            if (isNaN(diff)) {
                return true;
            }
            
            // Diff è la differenza tra le due date in millisecondi
            // Se è maggiore di 1500 (25 min) allora è suonabile
            return diff/1000 > 1500;
        },
        
        
        
        setRead: function(notificationId) {
            if(this.number < 1 && !notificationId) {
                return null;
            }
            
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
            var that = this;
            var ajax = this.setRead(notificationId);
            if (ajax !== null) {
                ajax.success(function() {
                    if (link === 'javascript:void(0);') {
                        that.fetch();
                    } else {
                        window.location = link;
                    }
                });
            }
        },
        
        
        init: function() {
            var notifier = this;
            notifier.fetch();
            
            setInterval(function() {
                notifier.fetch();
            }, 5*60*1000);  // 5 min
            
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
