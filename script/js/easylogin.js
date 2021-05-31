var easylogin = {
    form_field_selector: '.webauthn_enable',
    available: function () {
        return !(!window.fetch || !navigator.credentials || !navigator.credentials.create);
    },


    init: function () {
        if (this.available) {
            var $form_field = $(this.form_field_selector);
            $form_field.val(1);



        } else {
            $form_field.val(0);
            log('Browser not supported.');
        }
    }
};

$(() => {
    'use strict';
    easylogin.init();
});