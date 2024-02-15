"use strict";

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
 * @param {number} [retryCount=0] Number of retry attempts for the request in case of failure.
 * @param {number} [retryDelay=1000] Delay between retries in milliseconds.
 * @returns {Promise<Object|String>} A Promise that resolves to the response data. The response is parsed as JSON by default but can be parsed differently based on the 'Accept' header or the response content type.
 *
 *
 * Example usages:
 *
 * // Basic GET request
 * request('https://example.com/api/data')
 *   .then(data => console.log(data))
 *   .catch(error => console.error(error));
 *
 * // POST request with JSON data
 * request('https://example.com/api/post', { name: 'John Doe', age: 30 }, 'POST', true)
 *   .then(data => console.log(data))
 *   .catch(error => console.error(error));
 *
 * // PUT request with JSON data
 * request('https://example.com/api/update/1', { name: 'Jane Doe', age: 25 }, 'PUT', true)
 *   .then(data => console.log(data))
 *   .catch(error => console.error(error));
 *
 * // DELETE request
 * request('https://example.com/api/delete/1', {}, 'DELETE')
 *   .then(data => console.log(data))
 *   .catch(error => console.error(error));
 *
 * // Using FormData for file uploads
 * let formData = new FormData();
 * formData.append('file', fileInput.files[0]);
 * request('https://example.com/api/upload', formData, 'POST', false, true)
 *   .then(data => console.log(data))
 *   .catch(error => console.error(error));
 *
 * // Retry logic example: Retrying a GET request up to 3 times in case of failure
 * request('https://example.com/api/data', {}, 'GET', false, false, {}, {}, 3, 2000)
 *   .then(data => console.log(data))
 *   .catch(error => console.error(`Final error after retries: ${error}`));
 */


async function request(url = "", data = {}, method = "GET", payload = false, useFormData = false, headers = {}, customOptions = {}, retryCount = 0, retryDelay = 1000) {
    const validMethods = ["GET", "POST", "PUT", "PATCH", "DELETE"];
    
    // Normalize the method to uppercase for consistency
    method = method.toUpperCase();
    
    // Check that the method is supported
    if (!validMethods.includes(method)) {
        throw new Error(`HTTP method ${method} is not supported.`);
    }
    
    // Setup base options for fetch API
    let fetchOptions = {
        method: method, // *GET, POST, PUT, PATCH, DELETE
        mode: "cors", // no-cors, *cors, same-origin
        cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
        credentials: "same-origin", // include, *same-origin, omit
        headers: {},
        redirect: "follow", // manual, *follow, error
        referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
    };
    
    // Override default fetch options with custom options, except for 'method'
    fetchOptions = { ...fetchOptions, ...customOptions, method: method };
    fetchOptions.headers = { ...fetchOptions.headers, ...headers };
    
    // Logic for methods that can contain a body
    if (["POST", "PUT", "PATCH", "DELETE"].includes(method)) {
        if (useFormData) {
            // Convert data object to FormData if not already
            if (!(data instanceof FormData)) {
                const formData = new FormData();
                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });
                data = formData;
            }
            // Use FormData instance directly as the body
            fetchOptions.body = data;
        } else if (payload) {
            // If payload is true, treat data as JSON
            fetchOptions.body = JSON.stringify(data);
            fetchOptions.headers = {
                ...fetchOptions.headers,
                "Content-Type": "application/json",
                Accept: "application/json",
            };
        } else {
            // Otherwise, treat data as URL-encoded form data
            let formBody = [];
            if (data) {
                Object.keys(data).forEach(property => {
                    const encodedKey = encodeURIComponent(property);
                    const encodedValue = encodeURIComponent(data[property]);
                    formBody.push(encodedKey + "=" + encodedValue);
                });
            }
            formBody = formBody.join("&");
            fetchOptions.body = formBody;
            fetchOptions.headers = {
                ...fetchOptions.headers,
                "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
            };
        }
    }
    
    // Adjust URL for GET and HEAD methods
    if (["GET", "HEAD"].includes(method)) {
        if (data) {
            const queryString = Object.keys(data).map(property => `${encodeURIComponent(property)}=${encodeURIComponent(data[property])}`).join("&");
            url += (url.includes("?") ? "&" : "?") + queryString;
        }
    }
    
    try {
        const response = await fetch(url, fetchOptions);
        
        // Verifica se lo stato della risposta indica un errore
        if (!response.ok) {
            throw new Error(`${response.status} ${response.statusText}`);
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
        console.error(`Request failed: ${err.message}`);
        
        // Check for retry logic
        if (retryCount > 0) {
            console.log(`Retrying request... Attempts left: ${retryCount}`);
            await new Promise(resolve => setTimeout(resolve, retryDelay));
            return request(url, data, method, payload, useFormData, headers, customOptions, retryCount - 1, retryDelay);
        } else {
            throw new Error(`Fetch error after retries: ${err.message}`);
        }
    }
}