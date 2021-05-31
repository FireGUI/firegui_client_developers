


function newregistration() {

    if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
        window.alert('Browser not supported.');
        return;
    }

    // get default args
    window.fetch('server.php?fn=getCreateArgs' + getGetParams(), { method: 'GET', cache: 'no-cache' }).then(function (response) {
        return response.json();

        // convert base64 to arraybuffer
    }).then(function (json) {

        // error handling
        if (json.success === false) {
            throw new Error(json.msg);
        }

        // replace binary base64 data with ArrayBuffer. a other way to do this
        // is the reviver function of JSON.parse()
        recursiveBase64StrToArrayBuffer(json);
        return json;

        // create credentials
    }).then(function (createCredentialArgs) {
        console.log(createCredentialArgs);
        return navigator.credentials.create(createCredentialArgs);

        // convert to base64
    }).then(function (cred) {
        return {
            clientDataJSON: cred.response.clientDataJSON ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
            attestationObject: cred.response.attestationObject ? arrayBufferToBase64(cred.response.attestationObject) : null
        };

        // transfer to server
    }).then(JSON.stringify).then(function (AuthenticatorAttestationResponse) {
        return window.fetch('server.php?fn=processCreate' + getGetParams(), { method: 'POST', body: AuthenticatorAttestationResponse, cache: 'no-cache' });

        // convert to JSON
    }).then(function (response) {
        return response.json();

        // analyze response
    }).then(function (json) {
        if (json.success) {
            reloadServerPreview();
            window.alert(json.msg || 'registration success');
        } else {
            throw new Error(json.msg);
        }

        // catch errors
    }).catch(function (err) {
        reloadServerPreview();
        window.alert(err.message || 'unknown error occured');
    });
}


/**
 * checks a FIDO2 registration
 * @returns {undefined}
 */
function checkregistration() {

    if (!window.fetch || !navigator.credentials || !navigator.credentials.create) {
        window.alert('Browser not supported.');
        return;
    }

    // get default args
    window.fetch('server.php?fn=getGetArgs' + getGetParams(), { method: 'GET', cache: 'no-cache' }).then(function (response) {
        return response.json();

        // convert base64 to arraybuffer
    }).then(function (json) {

        // error handling
        if (json.success === false) {
            throw new Error(json.msg);
        }

        // replace binary base64 data with ArrayBuffer. a other way to do this
        // is the reviver function of JSON.parse()
        recursiveBase64StrToArrayBuffer(json);
        return json;

        // create credentials
    }).then(function (getCredentialArgs) {
        return navigator.credentials.get(getCredentialArgs);

        // convert to base64
    }).then(function (cred) {
        return {
            id: cred.rawId ? arrayBufferToBase64(cred.rawId) : null,
            clientDataJSON: cred.response.clientDataJSON ? arrayBufferToBase64(cred.response.clientDataJSON) : null,
            authenticatorData: cred.response.authenticatorData ? arrayBufferToBase64(cred.response.authenticatorData) : null,
            signature: cred.response.signature ? arrayBufferToBase64(cred.response.signature) : null
        };

        // transfer to server
    }).then(JSON.stringify).then(function (AuthenticatorAttestationResponse) {
        return window.fetch('server.php?fn=processGet' + getGetParams(), { method: 'POST', body: AuthenticatorAttestationResponse, cache: 'no-cache' });

        // convert to json
    }).then(function (response) {
        return response.json();

        // analyze response
    }).then(function (json) {
        if (json.success) {
            reloadServerPreview();
            window.alert(json.msg || 'login success');
        } else {
            throw new Error(json.msg);
        }

        // catch errors
    }).catch(function (err) {
        reloadServerPreview();
        window.alert(err.message || 'unknown error occured');
    });
}

function clearregistration() {
    window.fetch('server.php?fn=clearRegistrations' + getGetParams(), { method: 'GET', cache: 'no-cache' }).then(function (response) {
        return response.json();

    }).then(function (json) {
        if (json.success) {
            reloadServerPreview();
            window.alert(json.msg);
        } else {
            throw new Error(json.msg);
        }
    }).catch(function (err) {
        reloadServerPreview();
        window.alert(err.message || 'unknown error occured');
    });
}

/**
 * convert RFC 1342-like base64 strings to array buffer
 * @param {mixed} obj
 * @returns {undefined}
 */
function recursiveBase64StrToArrayBuffer(obj) {
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
                recursiveBase64StrToArrayBuffer(obj[key]);
            }
        }
    }
}

/**
 * Convert a ArrayBuffer to Base64
 * @param {ArrayBuffer} buffer
 * @returns {String}
 */
function arrayBufferToBase64(buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return window.btoa(binary);
}

/**
 * Get URL parameter
 * @returns {String}
 */
function getGetParams() {
    let url = '';
    url += '&apple=' + (document.getElementById('cert_apple').checked ? '1' : '0');
    url += '&yubico=' + (document.getElementById('cert_yubico').checked ? '1' : '0');
    url += '&solo=' + (document.getElementById('cert_solo').checked ? '1' : '0');
    url += '&hypersecu=' + (document.getElementById('cert_hypersecu').checked ? '1' : '0');
    url += '&google=' + (document.getElementById('cert_google').checked ? '1' : '0');
    url += '&microsoft=' + (document.getElementById('cert_microsoft').checked ? '1' : '0');

    url += '&requireResidentKey=' + (document.getElementById('requireResidentKey').checked ? '1' : '0');


    //<!--$allowUsb=true, $allowNfc=true, $allowBle=true, $allowInternal-->
    url += '&type_usb=' + (document.getElementById('type_usb').checked ? '1' : '0');
    url += '&type_nfc=' + (document.getElementById('type_nfc').checked ? '1' : '0');
    url += '&type_ble=' + (document.getElementById('type_ble').checked ? '1' : '0');
    url += '&type_int=' + (document.getElementById('type_int').checked ? '1' : '0');

    url += '&fmt_android-key=' + (document.getElementById('fmt_android-key').checked ? '1' : '0');
    url += '&fmt_android-safetynet=' + (document.getElementById('fmt_android-safetynet').checked ? '1' : '0');
    url += '&fmt_apple=' + (document.getElementById('fmt_apple').checked ? '1' : '0');
    url += '&fmt_fido-u2f=' + (document.getElementById('fmt_fido-u2f').checked ? '1' : '0');
    url += '&fmt_none=' + (document.getElementById('fmt_none').checked ? '1' : '0');
    url += '&fmt_packed=' + (document.getElementById('fmt_packed').checked ? '1' : '0');
    url += '&fmt_tpm=' + (document.getElementById('fmt_tpm').checked ? '1' : '0');
    url += '&rpId=' + encodeURIComponent(document.getElementById('rpId').value);

    if (document.getElementById('userVerification_required').checked) {
        url += '&userVerification=required';

    } else if (document.getElementById('userVerification_preferred').checked) {
        url += '&userVerification=preferred';

    } else if (document.getElementById('userVerification_discouraged').checked) {
        url += '&userVerification=discouraged';
    }

    return url;
}

function reloadServerPreview() {
    let iframe = document.getElementById('serverPreview');
    iframe.src = iframe.src;
}

/**
 * force https on load
 * @returns {undefined}
 */
window.onload = function () {
    if (location.protocol !== 'https:' && location.host !== 'localhost') {
        location.href = location.href.replace('http', 'https');
    }
    if (!document.getElementById('rpId').value) {
        document.getElementById('rpId').value = location.hostname;
    }
}