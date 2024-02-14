'use strict';

/**
 * Displays a toast notification using either SweetAlert or Toastr based on the specified type.
 * The function can optionally refresh the page or navigate to a new URL upon completion of the toast.
 * Custom options passed will be filtered to only include options that are valid for the selected toast library,
 * ensuring that only applicable settings are applied.
 *
 * @param {string} title - The title of the toast.
 * @param {string} icon - The icon to display in the toast. For SweetAlert, any icon supported by SweetAlert can be used.
 *        For Toastr, valid values are 'success', 'info', 'warning', 'error'. Defaults to 'success' if an invalid value is provided.
 * @param {string} content - The HTML content to display in the toast.
 * @param {string} toastType - The type of toast to display ('toastr' or 'sweetalert'). Defaults to 'toastr'.
 * @param {boolean|string|number|null} refresh - Determines the refresh behavior after displaying the toast.
 *        If true, the page will be reloaded.
 *        If a string, the window will navigate to the URL specified.
 *        If a number, it represents the delay in seconds before executing the refresh action.
 *        If null or not provided, no refresh action will be taken.
 * @param {Object} [customOptions={}] - An optional object containing custom configuration options for the toast.
 *        These options will be filtered to ensure that only options applicable to the selected toast library are applied.
 *        This prevents the application of unsupported or invalid options.
 *
 * @example
 * // Display a success notification using SweetAlert with custom options and no refresh
 * toast('Success', 'success', '<p>Operation completed successfully!</p>', 'sweetalert', null, { timer: 3000 });
 *
 * @example
 * // Display an error notification using Toastr, reload the page after showing the toast, with custom options
 * toast('Error', 'error', 'An error occurred. Please try again.', 'toastr', true, { timeOut: 5000, closeButton: false });
 */
const toast = (title, icon, content, toastType = 'toastr', refresh = null, customOptions = {}) => {
    // Validate the toastType, default to 'toastr' if an invalid value is provided
    if (!$.inArray(toastType, ['toastr', 'sweetalert'])) {
        toastType = 'toastr';
    }
    
    // Funzione helper per filtrare e mantenere solo le opzioni valide in customOptions
    const filterOptions = (defaultOptions, customOptions) => {
        return Object.keys(customOptions)
            .filter(key => key in defaultOptions)
            .reduce((obj, key) => {
                obj[key] = customOptions[key];
                return obj;
            }, {});
    };
    
    // Function to handle page refresh or navigation after displaying the toast
    const handleRefresh = () => {
        if (refresh === true) {
            location.reload();
        } else if (typeof refresh === 'string') {
            window.location.href = refresh;
        }
    };
    
    if (toastType === 'sweetalert') {
        // Configuration for SweetAlert, can be overridden by customOptions
        let swalOptions = {
            title,
            icon,
            html: content,
            backdrop: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
        };
        
        // Set up timer and loading animation if refresh is a number
        let timerInterval;
        if ($.isNumeric(refresh)) {
            swalOptions.timer = (refresh * 1000);
            swalOptions.timerProgressBar = true;
            swalOptions.didOpen = () => {
                Swal.showLoading();
            };
            swalOptions.willClose = () => {
                clearInterval(timerInterval);
                
                location.reload();
            }
        }
        
        // Filtra customOptions basandosi su swalOptions
        let filteredCustomOptions = filterOptions(swalOptions, customOptions);
        
        // Merge customOptions into swalOptions, ensuring title, icon, and content are preserved
        swalOptions = {...swalOptions, ...filteredCustomOptions, title, icon, html: content};
        
        // Execute SweetAlert with the configured options
        const promise = Swal.fire(swalOptions);
        if (!$.isNumeric(refresh)) {
            promise.then(() => {
                handleRefresh();
            });
        }
    } else if (toastType === 'toastr') {
        // Validate icon type for Toastr, default to 'info' if not valid
        if (!['success', 'info', 'warning', 'error'].includes(icon)) {
            console.warn(`Invalid icon type "${icon}" for Toastr. Defaulting to 'info'.`);
            icon = 'info'; // Default to 'info' if the specified icon isn't valid
        }
        
        // Default Toastr options, can be overridden by customOptions
        let toastrOptions = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        };
        
        // Filtra customOptions basandosi su swalOptions
        let filteredCustomOptions = filterOptions(toastrOptions, customOptions);
        
        // Merge customOptions into toastrOptions
        toastrOptions = {...toastrOptions, ...filteredCustomOptions};
        
        // Apply the configured options to Toastr
        toastr.options = toastrOptions;
        
        // Display the Toastr notification
        toastr[icon](content, title);
        
        // Handle the refresh or navigation as configured
        handleRefresh();
    } else {
        // Log a warning if the toast type is not recognized
        console.warn('Unhandled toast type:', toastType);
    }
};
