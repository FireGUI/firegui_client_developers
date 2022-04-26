var easylogin = {
    never_placeholder_cookie: '__never__',
    form_field_selector: '.webauthn_enable',
    login_box_selector: '.main_login_box',
    easylogin_box_selector: '.easylogin_box',
    proceed_btn_selector: '.js_easylogin_proceed',
    later_btn_selector: '.js_easylogin_later',
    never_btn_selector: '.js_easylogin_never',
    ask_for_easylogin_btn_selector: '.js_easylogin_ask',
    easylogin_name_selector: '.js_easylogin_name',
    easylogin_page_selector: '.js_easylogin_page',
    easylogin_back_button_selector: '.js_easylogin_back',

    available: function () {
        //return !(!window.fetch || !navigator.credentials || !navigator.credentials.create);

        if (window.PublicKeyCredential) {
            PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable()
                .then((available) => {
                    if (available) {
                        console.log("Supported.");
                        return true
                    } else {
                        console.log(
                            "WebAuthn supported, Platform Authenticator *not* supported."
                        );
                        return false
                    }
                })
                .catch((err) => console.log("Something went wrong."));
        } else {
            console.log("Not supported.");
            return false;
        }
        return false;
    },


    init: function () {

        var $form_field = $(this.form_field_selector);
        var $easylogin_page = $(this.easylogin_page_selector);
        if ($easylogin_page.length != 0) {//I'm in the easylogin page, so I have to save cookie before proceed
            this.setEasyloginCookie(atob($easylogin_page.data('user')));
        }
        var cookie_easylogin = this.getEasyloginCookie();
        if (this.available && cookie_easylogin != this.never_placeholder_cookie) {



            //I'm in the login page

            $form_field.val(1);

            if (cookie_easylogin) {
                var $login_box = $(this.login_box_selector);
                $login_box.hide();

                var $easylogin_box = $(this.easylogin_box_selector);
                $easylogin_box.show();

                try {
                    var data = JSON.parse(cookie_easylogin);
                    var name = data.display_name;

                    $(this.easylogin_name_selector).html(name);
                } catch (e) {
                    this.deleteEasyLoginCookie();

                }



                //this.checkRegistration(cookie_email);
            }
            this.attachBtnListeners();

        } else {
            $form_field.val(0);

        }
    },

    attachBtnListeners: function () {
        var self = this;

        var cookie_easylogin = this.getEasyloginCookie();
        var $ask_for_easylogin_btn = $(this.ask_for_easylogin_btn_selector);
        $ask_for_easylogin_btn.on('click', function () {
            var data = JSON.parse(cookie_easylogin);
            var email = data.email;
            self.checkRegistration(email);
        });

        var $later_btn = $(this.later_btn_selector);
        $later_btn.on('click', function () {

            location.href = base_url;
        });
        var $never_btn = $(this.never_btn_selector);
        $never_btn.on('click', function () {
            self.setEasyloginCookie(self.never_placeholder_cookie);
            location.href = base_url;
        });


        var $backbutton = $(this.easylogin_back_button_selector);
        $backbutton.on('click', function () {

            var $login_box = $(self.login_box_selector);
            $login_box.show();

            var $easylogin_box = $(self.easylogin_box_selector);
            $easylogin_box.hide();
        });

        var $proceed_btn = $(this.proceed_btn_selector);
        $proceed_btn.on('click', function () {

            var cookie_easylogin = self.getEasyloginCookie();
            console.log(cookie_easylogin);
            var data = JSON.parse(cookie_easylogin);

            console.log(data);

            window.fetch(base_url + 'Webauthn/getCreateArgs', { method: 'POST', body: JSON.stringify(data), cache: 'no-cache' }).then(function (response) {
                return response.json();

                // convert base64 to arraybuffer
            }).then(function (json) {
                console.log(json);
                // error handling
                if (json.success === false) {
                    throw new Error(json.msg);
                }

                // replace binary base64 data with ArrayBuffer. a other way to do this
                // is the reviver function of JSON.parse()
                self.recursiveBase64StrToArrayBuffer(json);
                return json;

                // create credentials
            }).then(function (createCredentialArgs) {
                console.log(createCredentialArgs);
                return navigator.credentials.create(createCredentialArgs);

                // convert to base64
            }).then(function (cred) {
                return {
                    clientDataJSON: cred.response.clientDataJSON ? self.arrayBufferToBase64(cred.response.clientDataJSON) : null,
                    attestationObject: cred.response.attestationObject ? self.arrayBufferToBase64(cred.response.attestationObject) : null
                };

                // transfer to server
            }).then(JSON.stringify).then(function (AuthenticatorAttestationResponse) {
                return window.fetch(base_url + 'Webauthn/processCreate', { method: 'POST', body: AuthenticatorAttestationResponse, cache: 'no-cache' });

                // convert to JSON
            }).then(function (response) {
                return response.json();

                // analyze response
            }).then(function (json) {
                if (json.success) {
                    //Create cookie for future access without password prompt
                    //window.alert(json.msg || 'registration success');

                    self.setEasyloginCookie(json.data);
                    location.href = base_url;
                } else {
                    throw new Error(json.msg);
                }

                // catch errors
            }).catch(function (err) {
                self.deleteEasyLoginCookie();
                console.log(err.message || 'unknown error occured. Redirecting to dashboard...');

                //alert('TODO: re-enable redirect');
                location.href = base_url;
            });
        });
    },
    checkRegistration: function (email) {
        // get default args
        //alert(email);
        var self = this;

        window.fetch(base_url + 'Webauthn/getGetArgs', { method: 'POST', body: JSON.stringify({ "email": email }), cache: 'no-cache' }).then(function (response) {
            return response.json();

            // convert base64 to arraybuffer
        }).then(function (json) {

            // error handling
            if (json.success === false) {
                throw new Error(json.msg);
            }

            // replace binary base64 data with ArrayBuffer. a other way to do this
            // is the reviver function of JSON.parse()
            self.recursiveBase64StrToArrayBuffer(json);
            return json;

            // create credentials
        }).then(function (getCredentialArgs) {
            return navigator.credentials.get(getCredentialArgs);

            // convert to base64
        }).then(function (cred) {
            return {
                id: cred.rawId ? self.arrayBufferToBase64(cred.rawId) : null,
                clientDataJSON: cred.response.clientDataJSON ? self.arrayBufferToBase64(cred.response.clientDataJSON) : null,
                authenticatorData: cred.response.authenticatorData ? self.arrayBufferToBase64(cred.response.authenticatorData) : null,
                signature: cred.response.signature ? self.arrayBufferToBase64(cred.response.signature) : null,
                email: email
            };

            // transfer to server
        }).then(JSON.stringify).then(function (AuthenticatorAttestationResponse) {

            return window.fetch(base_url + 'Webauthn/processGet', { method: 'POST', body: AuthenticatorAttestationResponse, cache: 'no-cache' });

            // convert to json
        }).then(function (response) {
            return response.json();

            // analyze response
        }).then(function (json) {
            if (json.success) {

                //window.alert(json.msg || 'login success');
                location.href = base_url;
            } else {
                throw new Error(json.msg);
            }

            // catch errors
        }).catch(function (err) {
            self.deleteEasyLoginCookie();
            console.log(err.message || 'unknown error occured');
            alert('Unknown error occurred. Redirecting to dashboard...');
            location.href = base_url;
        });
    },
    recursiveBase64StrToArrayBuffer: function (obj) {
        let prefix = '=?BINARY?B?';
        let suffix = '?=';
        if (typeof obj === 'object') {
            for (let key in obj) {
                if (typeof obj[key] === 'string') {
                    let str = obj[key];
                    if (str.substring(0, prefix.length) === prefix && str.substring(str.length - suffix.length) === suffix) {
                        str = str.substring(prefix.length, str.length - suffix.length);

                        let binary_string = window.atob(str);
                        let len = binary_string.length;
                        let bytes = new Uint8Array(len);
                        for (let i = 0; i < len; i++) {
                            bytes[i] = binary_string.charCodeAt(i);
                        }
                        obj[key] = bytes.buffer;
                    }
                } else {
                    this.recursiveBase64StrToArrayBuffer(obj[key]);
                }
            }
        }
    },
    arrayBufferToBase64: function (buffer) {
        let binary = '';
        let bytes = new Uint8Array(buffer);
        let len = bytes.byteLength;
        for (let i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    },
    setEasyloginCookie: function (data) {
        var expires = "";
        var days = 365;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = "webauthn_easylogin=" + (data || "") + expires + "; path=/";
    },
    getEasyloginCookie: function () {
        var nameEQ = "webauthn_easylogin=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return false;
    },
    deleteEasyLoginCookie: function () {
        document.cookie = "webauthn_easylogin=;expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/";
    }

};
$(() => {
    'use strict';
    easylogin.init();
});