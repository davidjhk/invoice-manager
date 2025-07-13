/**
 * Invoice Manager - Common JavaScript Functions
 * 
 * This file contains reusable JavaScript functions that are used across
 * multiple pages and components in the Invoice Manager application.
 */

/**
 * Phone Number Utilities
 */
const PhoneFormatter = {
    /**
     * Format phone number as user types
     * Supports US format: (XXX) XXX-XXXX and +1 (XXX) XXX-XXXX
     * 
     * @param {string} value - Raw phone number input
     * @returns {string} - Formatted phone number
     */
    formatPhoneNumber: function(value) {
        // Remove all non-digits
        let digits = value.replace(/\D/g, '');
        
        // Limit to 11 digits maximum (1 + 10 digits for US numbers)
        if (digits.length > 11) {
            digits = digits.substring(0, 11);
        }
        
        if (digits.length === 11 && digits.charAt(0) === '1') {
            // Format as +1 (XXX) XXX-XXXX
            return '+1 (' + digits.substring(1, 4) + ') ' + digits.substring(4, 7) + '-' + digits.substring(7, 11);
        } else if (digits.length === 10) {
            // Format as (XXX) XXX-XXXX
            return '(' + digits.substring(0, 3) + ') ' + digits.substring(3, 6) + '-' + digits.substring(6, 10);
        } else if (digits.length > 6) {
            // Partial formatting XXX-XXXX
            return digits.substring(0, 3) + '-' + digits.substring(3);
        } else if (digits.length > 3) {
            // Partial formatting XXX-
            return digits.substring(0, 3) + '-' + digits.substring(3);
        }
        
        return digits;
    },

    /**
     * Initialize phone number formatting for input elements
     * 
     * @param {string|Element|NodeList} selector - CSS selector, element, or NodeList
     */
    initPhoneFormatting: function(selector) {
        let elements;
        
        if (typeof selector === 'string') {
            elements = document.querySelectorAll(selector);
        } else if (selector.nodeName) {
            elements = [selector];
        } else if (selector.length !== undefined) {
            elements = selector;
        } else {
            console.error('Invalid selector provided to initPhoneFormatting');
            return;
        }
        
        Array.from(elements).forEach(element => {
            element.addEventListener('input', function() {
                this.value = PhoneFormatter.formatPhoneNumber(this.value);
            });
        });
    },

    /**
     * Initialize phone formatting using jQuery (for backward compatibility)
     * 
     * @param {string} jquerySelector - jQuery selector
     */
    initPhoneFormattingJQuery: function(jquerySelector) {
        $(jquerySelector).on('input', function() {
            this.value = PhoneFormatter.formatPhoneNumber(this.value);
        });
    }
};

/**
 * Modal Utilities
 */
const ModalUtils = {
    /**
     * Ensure modal is completely hidden and backdrop is removed
     * 
     * @param {string} modalSelector - Modal selector (e.g., '#myModal')
     */
    ensureModalClosed: function(modalSelector) {
        $(modalSelector).modal('hide');
        
        // Clean up any remaining backdrop
        setTimeout(() => {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        }, 300);
    },

    /**
     * Setup modal cleanup event listener
     * 
     * @param {string} modalSelector - Modal selector (e.g., '#myModal')
     */
    setupModalCleanup: function(modalSelector) {
        $(modalSelector).on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
        });
    }
};

/**
 * Form Utilities
 */
const FormUtils = {
    /**
     * Setup form validation for Bootstrap validation
     * 
     * @param {string} formSelector - Form selector (e.g., '#myForm')
     */
    setupFormValidation: function(formSelector) {
        const form = document.querySelector(formSelector);
        if (!form) return;
        
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    },

    /**
     * Reset form and remove validation classes
     * 
     * @param {string} formSelector - Form selector (e.g., '#myForm')
     */
    resetFormValidation: function(formSelector) {
        const form = document.querySelector(formSelector);
        if (!form) return;
        
        form.classList.remove('was-validated');
        form.reset();
    }
};

/**
 * Currency Utilities
 */
const CurrencyUtils = {
    /**
     * Format number as currency
     * 
     * @param {number} amount - Amount to format
     * @param {string} currency - Currency code (default: 'USD')
     * @param {string} locale - Locale string (default: 'en-US')
     * @returns {string} - Formatted currency string
     */
    formatCurrency: function(amount, currency = 'USD', locale = 'en-US') {
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
};

/**
 * Date Utilities
 */
const DateUtils = {
    /**
     * Format date for input[type="date"]
     * 
     * @param {Date} date - Date object
     * @returns {string} - Formatted date string (YYYY-MM-DD)
     */
    formatDateForInput: function(date) {
        return date.toISOString().split('T')[0];
    },

    /**
     * Add days to a date
     * 
     * @param {Date} date - Base date
     * @param {number} days - Number of days to add
     * @returns {Date} - New date object
     */
    addDays: function(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }
};

/**
 * AJAX Utilities
 */
const AjaxUtils = {
    /**
     * Get CSRF token for AJAX requests
     * 
     * @returns {string} - CSRF token
     */
    getCsrfToken: function() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : '';
    },

    /**
     * Get CSRF parameter name
     * 
     * @returns {string} - CSRF parameter name
     */
    getCsrfParam: function() {
        const metaTag = document.querySelector('meta[name="csrf-param"]');
        return metaTag ? metaTag.getAttribute('content') : '_csrf';
    },

    /**
     * Prepare FormData with CSRF token
     * 
     * @param {HTMLFormElement|FormData} formOrData - Form element or FormData
     * @returns {FormData} - FormData with CSRF token
     */
    prepareFormData: function(formOrData) {
        let formData;
        
        if (formOrData instanceof FormData) {
            formData = formOrData;
        } else {
            formData = new FormData(formOrData);
        }
        
        // Add CSRF token
        formData.append(this.getCsrfParam(), this.getCsrfToken());
        
        return formData;
    }
};

/**
 * Notification Utilities
 */
const NotificationUtils = {
    /**
     * Show success message
     * 
     * @param {string} message - Success message
     * @param {number} delay - Delay before showing (default: 0)
     */
    showSuccess: function(message, delay = 0) {
        setTimeout(() => {
            alert(message); // TODO: Replace with better notification system
        }, delay);
    },

    /**
     * Show error message
     * 
     * @param {string} message - Error message
     * @param {object} errors - Additional error details (optional)
     */
    showError: function(message, errors = null) {
        console.error('Error:', message, errors);
        let errorMessage = message;
        if (errors) {
            errorMessage += '\nErrors: ' + JSON.stringify(errors);
        }
        alert(errorMessage); // TODO: Replace with better notification system
    }
};

// Export for use in other files (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        PhoneFormatter,
        ModalUtils,
        FormUtils,
        CurrencyUtils,
        DateUtils,
        AjaxUtils,
        NotificationUtils
    };
}