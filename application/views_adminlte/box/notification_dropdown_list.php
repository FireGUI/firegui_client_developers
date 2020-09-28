<!-- BEGIN NOTIFICATION DROPDOWN -->
<li class="dropdown notifications-menu" id="header_notification_bar">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <i class="far fa-bell"></i>
        <span class="js_notification_number_label badge badge-danger"></span>
    </a>
    <ul class="dropdown-menu">

        <li class="header">
            <h5><?php e('You have'); ?> <span class="js_notification_number bold">0
                    <!-- ... notification count here ... --></span> <?php e('new notifications'); ?></h5>
            <a href="#" onclick="CrmNotifier.readAll();return false;" role='button'><?php e('mark as read'); ?></a>
        </li>

        <li>
            <!-- inner menu: contains the actual data -->
            <ul class=" menu js_notification_dropdown_list dropdown-menu-list scroller firegui_notification">
                <li>
                    <!--                                <a href="#">
                                                <i class="fas fa-users text-aqua"></i> 5 new members joined today
                                            </a>-->
                </li>
            </ul>
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
    'use strict';
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
            'use strict';
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

            if (typeof html === 'string') {
                $('.js_notification_dropdown_list', this.listContainer).html(html);
            }

            $('.js_notification_number_label', this.listContainer).text(this.number > 0 ? this.number : '');
            $('.js_notification_number', this.listContainer).text(this.number);

        },

        playNotificationSound: function(notificationSound) {

            if (!notificationSound) {
                alert('<?php e('There are new notification') ?>');
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
            'use strict';
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
            return diff / 1000 > 1500;
        },

        setRead: function(notificationId) {
            if (this.number < 1 && !notificationId) {
                return null;
            }

            return $.ajax(base_url + 'db_ajax/notify_read/' + ((typeof notificationId === 'undefined') ? '' : notificationId));
        },

        readAll: function() {
            'use strict';
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
                    'use strict';
                    if (link === 'javascript:void(0);') {
                        that.fetch();
                    } else {
                        window.location = link;
                    }
                });
            }
        },

        init: function() {
            'use strict';
            var notifier = this;
            notifier.fetch();

            setInterval(function() {
                'use strict';
                notifier.fetch();
            }, 5 * 60 * 1000); // 5 min

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
        'use strict';
        CrmNotifier.init();
    });
</script>