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

    fetch: function () {
        var notifier = this;
        $.ajax({
            url: base_url + 'get_ajax/dropdown_notification_list',
            dataType: 'json',
            success: function (json) {
                notifier.number = json.count;
                notifier.showUnread(json.view);

                $.each(json.data, function (index, notification) {
                    if (
                        notification.notifications_read == '0'
                        && notification.notifications_type == '5'
                    ) {
                        notifier.readAndOpenModal(notification.notifications_id, { title: notification.notifications_title, message: notification.notifications_message });
                    }
                });

                if (notifier.number > 0) {
                    var baseSoundFolder = base_url_scripts + 'script/js/';

                    notifier.playNotificationSound(baseSoundFolder + 'notify-sound.mp3');
                }
            },
        });
    },

    showUnread: function (html) {
        if (typeof html === 'string') {
            $('.js_notification_dropdown_list', this.listContainer).html(html);
        }

        $('.js_notification_number_label', this.listContainer).text(this.number > 0 ? this.number : '');
        $('.js_notification_number', this.listContainer).text(this.number);
    },

    playNotificationSound: function (notificationSound) {
        if (!notificationSound) {
            alert('There are new notification');
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

    isPlayable: function () {
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

    setRead: function (notificationId) {
        if (this.number < 1 && !notificationId) {
            return null;
        }

        return $.ajax(base_url + 'db_ajax/notify_read/' + (typeof notificationId === 'undefined' ? '' : notificationId));
    },

    readAll: function () {
        var ajax = this.setRead();
        if (ajax !== null) {
            var notifier = this;
            ajax.success(function () {
                notifier.number = 0;
                notifier.showUnread();
            });
        }
    },

    readAndGoto: function (notificationId, link) {
        var that = this;
        var ajax = this.setRead(notificationId);
        if (ajax !== null) {
            ajax.success(function () {
                'use strict';
                if (link === 'javascript:void(0);') {
                    that.fetch();
                } else {
                    window.location = link;
                }
            });
        }
    },

    readAndOpenModal: function (notificationId, data) {
        var that = this;

        var ajax = this.setRead(notificationId);
        if (ajax !== null) {
            ajax.success(function () {
                'use strict';
                bootbox.alert({
                    title: data.title ?? 'New Notification',
                    message: data.message,
                });
            });
        }
    },

    init: function () {
        var notifier = this;
        notifier.fetch();

        setInterval(function () {
            notifier.fetch();
        }, 5 * 60 * 1000); // 5 min

        notifier.listContainer.on('click', '[data-notification]', function (e) {
            e.preventDefault();

            var $this = $(this);
            if ($('a', $this).attr('href')) {
                notifier.readAndGoto($this.data('notification'), $('a', $this).attr('href'));
            } else {
                notifier.readAndOpenModal($this.data('notification'), $this.data());
            }

        });
    },
};

$(document).ready(function () {
    'use strict';
    CrmNotifier.init();
});
