var easylogin = {
    form_field_selector: '.webauthn_enable',
    proceed_btn_selector: '.js_easylogin_proceed',
    later_btn_selector: '.js_easylogin_later',
    never_btn_selector: '.js_easylogin_never',
    available: function () {
        return !(!window.fetch || !navigator.credentials || !navigator.credentials.create);
    },


    init: function () {
        if (this.available) {
            var $proceed_btn = $(this.proceed_btn_selector);
            if ($proceed_btn.length) {
                //I'm in the easylogin page
                this.attachBtnListeners();
            } else {
                //I'm in the login page
                var $form_field = $(this.form_field_selector);
                $form_field.val(1);

                var cookie_email = this.getEmailCookie();
                if (cookie_email) {
                    this.checkRegistration(cookie_email);
                }
            }





        } else {
            $form_field.val(0);
            log('Browser not supported.');
        }
    },

    attachBtnListeners: function () {
        var self = this;
        var $proceed_btn = $(this.proceed_btn_selector);
        $proceed_btn.on('click', function () {
            window.fetch(base_url + 'Webauthn/getCreateArgs', { method: 'GET', cache: 'no-cache' }).then(function (response) {
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

                    self.setEmailCookie(json.email);
                } else {
                    throw new Error(json.msg);
                }

                // catch errors
            }).catch(function (err) {

                window.alert(err.message || 'unknown error occured');
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
                signature: cred.response.signature ? self.arrayBufferToBase64(cred.response.signature) : null
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

                window.alert(json.msg || 'login success');
            } else {
                throw new Error(json.msg);
            }

            // catch errors
        }).catch(function (err) {

            window.alert(err.message || 'unknown error occured');
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
    setEmailCookie: function (email) {
        var expires = "";
        var days = 365;
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = "webauthn_email=" + (email || "") + expires + "; path=/";
    },
    getEmailCookie: function () {
        var nameEQ = "webauthn_email=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return false;
    }

};
$(() => {
    'use strict';
    easylogin.init();
});