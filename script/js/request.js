'use strict'

/**
 * Performs asynchronous AJAX requests with support for various HTTP methods, content types, and customized headers.
 *
 * @param {string} url The URL to make the request to.
 * @param {Object|FormData} data The data to be sent with the request. Can be a JavaScript object for JSON or form-encoded bodies, or a FormData object for multipart/form-data bodies.
 * @param {string} [method="GET"] The HTTP method to use for the request, such as "GET", "POST", "PUT", "PATCH", or "DELETE".
 * @param {boolean} [payload=false] Specifies if the data should be sent as a JSON payload. This parameter is considered when the content type is not explicitly set via headers or when `useFormData` is false.
 * @param {boolean} [useFormData=false] Specifies if the request body should be sent as FormData. This is useful for multipart/form-data content types, typically used for file uploads.
 * @param {Object} [headers={}] Additional headers to send with the request. These headers are merged with any default headers set by the fetch options, with precedence given to the headers specified in this parameter.
 * @param {Object} [customOptions={}] Additional fetch options to customize the request further. The 'method' specified in this object is ignored to ensure the method parameter is used.
 * @returns {Promise<Object|String>} A Promise that resolves to the response data. The response is parsed as JSON by default but can be parsed differently based on the 'Accept' header or the response content type.
 *
 * Example usage:
 * request('https://example.com/api/data', { key: 'value' }, 'POST', true, false, { 'Content-Type': 'application/json' })
 *   .then(data => console.log(data))
 *   .catch(error => console.error(error));
 */

async function request(url = '', data = {}, method = "GET", payload = false, useFormData = false, headers = {}, customOptions = {}) {
    const validMethods = ["GET", "POST", "PUT", "PATCH", "DELETE"];
    
    // Normalize the method to uppercase for consistency
    method = method.toUpperCase();
    
    // Check that the method is supported
    if (!validMethods.includes(method)) return false;
    
    // Setup base options for fetch API
    let fetchOptions = {
        method: method, // *GET, POST, PUT, PATCH, DELETE
        mode: 'cors', // no-cors, *cors, same-origin
        cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        credentials: 'same-origin', // include, *same-origin, omit
        headers: {},
        redirect: 'follow', // manual, *follow, error
        referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
    };
    
    // Override default fetch options with custom options, except for 'method'
    fetchOptions = {...fetchOptions, ...customOptions, method: method};
    fetchOptions.headers = {...fetchOptions.headers, ...headers};
    
    // Handle logic specific to POST method
    if (method === "POST") {
        // Treat data as FormData if requested
        if (useFormData) {
            // Convert data object to FormData if not already
            if (!(data instanceof FormData)) {
                const formData = new FormData();
                for (const key in data) {
                    if (data.hasOwnProperty(key)) {
                        formData.append(key, data[key]);
                    }
                }
                data = formData;
            }
            // Use FormData instance directly as the body
            fetchOptions.body = data;
        } else if (payload) {
            // If payload is true, treat data as JSON
            fetchOptions.body = JSON.stringify(data);
            fetchOptions.headers = {
                "Content-Type": "application/json",
                'Accept': 'application/json'
            };
        } else {
            // Otherwise, treat data as URL-encoded form data
            let formBody = [];
            for (const property in data) {
                const encodedKey = encodeURIComponent(property);
                const encodedValue = encodeURIComponent(data[property]);
                formBody.push(encodedKey + "=" + encodedValue);
            }
            formBody = formBody.join("&");
            fetchOptions.body = formBody;
            fetchOptions.headers = { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" };
        }
    } else if (method === "GET") {
        // Append data as query string for GET requests
        let queryString = '';
        for (const property in data) {
            if (queryString !== '') {
                queryString += '&';
            }
            queryString += encodeURIComponent(property) + '=' + encodeURIComponent(data[property]);
        }
        url += (url.indexOf('?') === -1 ? '?' : '&') + queryString;
    }
    
    try {
        const response = await fetch(url, fetchOptions);
        
        // Verifica se lo stato della risposta indica un errore
        if (!response.ok) { // response.ok è true per uno stato 200-299
            throw new Error(response.status + ' ' + response.statusText);
        }

        const text = await response.text(); // Parse it as text
        
        let data;
        try {
            data = JSON.parse(text); // Try to parse it as JSON
        } catch (err) {
            data = text;
        }

        return data;
    } catch (err) {
        throw new Error(err);
    }
}
