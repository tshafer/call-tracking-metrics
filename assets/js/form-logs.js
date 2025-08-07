/**
 * Form Logs JavaScript
 * 
 * Handles form-specific log viewing and management functionality
 * 
 * @since 2.0.0
 */

(function($) {
    'use strict';

    // Initialize form logs functionality
    function initFormLogs() {
        // Check if debug mode is enabled
        if (!window.ctmFormLogsData || !window.ctmFormLogsData.debug_enabled) {
            return; // Don't initialize if debug mode is disabled
        }
        
        // Handle escape key to close modal
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                const $modal = $('#ctm-form-logs-modal');
                if (!$modal.hasClass('hidden')) {
                    $modal.addClass('hidden');
                    $('body').removeClass('ctm-modal-open');
                }
            }
        });
        
        // Handle view logs button clicks
        $(document).on('click', '.ctm-view-form-logs', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const formType = $button.data('form-type');
            const formId = $button.data('form-id');
            const formTitle = $button.data('form-title');
            
            // Show loading state
            $button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...');
            
            // Fetch form logs
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctm_get_form_logs',
                    form_type: formType,
                    form_id: formId,
                    nonce: ctmFormLogsData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showFormLogsModal(formType, formId, formTitle, response.data.logs);
                    } else {
                        showError('Failed to load form logs: ' + (response.data?.message || 'Unknown error'));
                    }
                },
                error: function() {
                    showError('Failed to load form logs. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    $button.prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>View Logs');
                }
            });
        });

        // Handle clear logs button clicks
        $(document).on('click', '.ctm-clear-form-logs', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const formType = $button.data('form-type');
            const formId = $button.data('form-id');
            const formTitle = $button.data('form-title');
            
            if (!confirm(`Are you sure you want to clear all logs for "${formTitle}"? This action cannot be undone.`)) {
                return;
            }
            
            // Show loading state
            $button.prop('disabled', true).text('Clearing...');
            
            // Clear form logs
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctm_clear_form_logs',
                    form_type: formType,
                    form_id: formId,
                    nonce: ctmFormLogsData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showSuccess('Form logs cleared successfully');
                        // Refresh the logs display
                        refreshFormLogs(formType, formId, formTitle);
                    } else {
                        showError('Failed to clear form logs: ' + (response.data?.message || 'Unknown error'));
                    }
                },
                error: function() {
                    showError('Failed to clear form logs. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    $button.prop('disabled', false).text('Clear Logs');
                }
            });
        });

        // Handle reload logs button clicks
        $(document).on('click', '.ctm-reload-form-logs', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const formType = $button.data('form-type');
            const formId = $button.data('form-id');
            const formTitle = $button.data('form-title');
            
            // Show loading state
            $button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Loading...');
            
            // Reload form logs
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctm_get_form_logs',
                    form_type: formType,
                    form_id: formId,
                    nonce: ctmFormLogsData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const $modal = $('#ctm-form-logs-modal');
                        updateFormLogsContent($modal, formType, formId, formTitle, response.data.logs);
                        showSuccess('Logs refreshed successfully');
                    } else {
                        showError('Failed to reload logs: ' + (response.data?.message || 'Unknown error'));
                    }
                },
                error: function() {
                    showError('Failed to reload logs. Please try again.');
                },
                complete: function() {
                    // Reset button state
                    $button.prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Reload');
                }
            });
        });
    }

    // Show form logs modal
    function showFormLogsModal(formType, formId, formTitle, logs) {
        const modalId = 'ctm-form-logs-modal';
        let $modal = $('#' + modalId);
        
        // Create modal if it doesn't exist
        if ($modal.length === 0) {
            $modal = createFormLogsModal(modalId);
            $('body').append($modal);
        }
        
        // Update modal content
        updateFormLogsContent($modal, formType, formId, formTitle, logs);
        
        // Hide WordPress admin menu and show modal
        $('body').addClass('ctm-modal-open');
        $modal.removeClass('hidden');
    }

    // Create form logs modal
    function createFormLogsModal(modalId) {
        return $(`
            <div id="${modalId}" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
                <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white" style="max-height: 90vh;">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900" id="modal-title">Form Logs</h3>
                            <button type="button" class="ctm-close-modal text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <span class="text-sm text-gray-600" id="modal-form-info"></span>
                                    <span class="text-sm text-gray-500" id="modal-log-count"></span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button type="button" class="ctm-reload-form-logs inline-flex items-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Reload
                                    </button>
                                    <button type="button" class="ctm-clear-form-logs inline-flex items-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 hover:text-red-700 transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Clear Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-y-auto" style="max-height: calc(90vh - 200px);">
                            <div id="modal-logs-content" class="space-y-3">
                                <!-- Logs will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }

    // Update form logs content
    function updateFormLogsContent($modal, formType, formId, formTitle, logs) {
        // Update modal title and info
        $modal.find('#modal-title').text(`Form Logs - ${formTitle}`);
        $modal.find('#modal-form-info').text(`${formType.toUpperCase()} Form ID: ${formId}`);
        $modal.find('#modal-log-count').text(`${logs.length} log entries`);
        
        // Set data attributes for buttons
        $modal.find('.ctm-clear-form-logs, .ctm-reload-form-logs').data({
            'form-type': formType,
            'form-id': formId,
            'form-title': formTitle
        });
        
        // Sort logs by most recent first
        const sortedLogs = sortLogsByDate(logs);
        
        // Generate logs content
        const logsHtml = generateLogsHtml(sortedLogs);
        $modal.find('#modal-logs-content').html(logsHtml);
        
        // Handle close button
        $modal.find('.ctm-close-modal, .ctm-modal-overlay').off('click').on('click', function() {
            $modal.addClass('hidden');
            $('body').removeClass('ctm-modal-open');
        });
    }

    // Generate logs HTML
    function generateLogsHtml(logs) {
        if (logs.length === 0) {
            return `
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-500">No logs found for this form.</p>
                </div>
            `;
        }
        
        return logs.map(log => `
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${getLogTypeColor(log.type)}">
                            ${log.type}
                        </span>
                        <span class="text-sm text-gray-600">${formatTimestamp(log.timestamp)}</span>
                    </div>
                    <span class="text-xs text-gray-500">${log.form_type.toUpperCase()}</span>
                </div>
                <div class="text-sm text-gray-800 mb-2">${log.message}</div>
                ${log.payload ? `
                    <details class="mb-2">
                        <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Payload</summary>
                        <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(log.payload, null, 2)}</pre>
                    </details>
                ` : ''}
                ${log.response ? `
                    <details class="mb-2">
                        <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Response</summary>
                        <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(log.response, null, 2)}</pre>
                    </details>
                ` : ''}
                ${log.context ? `
                    <details>
                        <summary class="text-sm text-gray-600 cursor-pointer hover:text-gray-800">View Context</summary>
                        <pre class="mt-2 text-xs bg-gray-100 p-2 rounded overflow-x-auto">${JSON.stringify(log.context, null, 2)}</pre>
                    </details>
                ` : ''}
            </div>
        `).join('');
    }

    // Get log type color
    function getLogTypeColor(type) {
        const colors = {
            'form_submission': 'bg-green-100 text-green-800',
            'error': 'bg-red-100 text-red-800',
            'warning': 'bg-yellow-100 text-yellow-800',
            'info': 'bg-blue-100 text-blue-800',
            'debug': 'bg-gray-100 text-gray-800'
        };
        return colors[type] || colors.info;
    }

    // Format timestamp
    function formatTimestamp(timestamp) {
        return new Date(timestamp).toLocaleString();
    }

    // Sort logs by date (most recent first)
    function sortLogsByDate(logs) {
        return logs.sort((a, b) => {
            const dateA = new Date(a.timestamp);
            const dateB = new Date(b.timestamp);
            return dateB - dateA; // Most recent first
        });
    }

    // Refresh form logs
    function refreshFormLogs(formType, formId, formTitle) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_get_form_logs',
                form_type: formType,
                form_id: formId,
                nonce: ctmFormLogsData.nonce
            },
            success: function(response) {
                if (response.success) {
                    const $modal = $('#ctm-form-logs-modal');
                    updateFormLogsContent($modal, formType, formId, formTitle, response.data.logs);
                }
            }
        });
    }

    // Show success message
    function showSuccess(message) {
        // You can implement your own toast notification here
        alert(message);
    }

    // Show error message
    function showError(message) {
        // You can implement your own toast notification here
        alert('Error: ' + message);
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initFormLogs();
    });

})(jQuery); 