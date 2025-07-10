<?php
// JavaScript for debug functionality
// This file contains all the JavaScript functions needed for the debug components
?>

<script>
// Debug functionality JavaScript

function toggleLogView(date) {
    const logDiv = document.getElementById('log-' + date);
    if (logDiv.classList.contains('hidden')) {
        logDiv.classList.remove('hidden');
    } else {
        logDiv.classList.add('hidden');
    }
}

function showEmailForm(date) {
    document.getElementById('email-log-date').value = date;
    document.getElementById('email-modal').classList.remove('hidden');
}

function hideEmailForm() {
    document.getElementById('email-modal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('email-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideEmailForm();
    }
});

function clearDebugLogs(logType, logDate = '') {
    const buttonId = logType === 'debug_all' ? 'clear-debug-all-btn' : `clear-single-${logDate}-btn`;
    const button = document.getElementById(buttonId);
    const originalText = button.textContent;
    
    // Confirm action
    const confirmMessage = logType === 'debug_all' 
        ? 'Are you sure you want to clear all debug logs? This action cannot be undone.'
        : `Are you sure you want to clear the debug log for ${logDate}? This action cannot be undone.`;
    
    if (!confirm(confirmMessage)) {
        return;
    }
    
    // Disable button and show loading state
    button.disabled = true;
    button.textContent = 'Clearing...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'ctm_clear_logs');
    formData.append('log_type', logType);
    if (logDate) {
        formData.append('log_date', logDate);
    }
    formData.append('nonce', '<?= wp_create_nonce('ctm_clear_logs') ?>');
    
    // Send AJAX request
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showDebugMessage(data.data.message, 'success');
            
            if (logType === 'debug_all') {
                // Reload the page to show empty state
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // Remove the specific log container
                const logContainer = button.closest('.border.border-gray-200.rounded-lg.overflow-hidden');
                if (logContainer) {
                    logContainer.style.transition = 'opacity 0.5s ease';
                    logContainer.style.opacity = '0';
                    setTimeout(() => {
                        logContainer.remove();
                        
                        // Check if there are any remaining logs
                        const remainingLogs = document.querySelectorAll('.border.border-gray-200.rounded-lg.overflow-hidden');
                        if (remainingLogs.length === 0) {
                            // Show "no logs" message
                            const logsContainer = document.querySelector('.space-y-4');
                            if (logsContainer) {
                                logsContainer.innerHTML = `
                                    <div class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-4 text-lg font-medium text-gray-900">No debug logs found</h3>
                                        <p class="mt-2 text-gray-500">Enable debug mode to start logging plugin activity.</p>
                                    </div>
                                `;
                            }
                        }
                    }, 500);
                }
            }
        } else {
            showDebugMessage(data.data.message || 'Failed to clear logs', 'error');
        }
    })
    .catch(error => {
        console.error('Error clearing logs:', error);
        showDebugMessage('Network error occurred while clearing logs', 'error');
    })
    .finally(() => {
        // Re-enable button if it still exists
        if (button && button.parentNode) {
            button.disabled = false;
            button.textContent = originalText;
        }
    });
}

function showDebugMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `p-4 mb-4 rounded-lg border-l-4 ${
        type === 'success' ? 'bg-green-50 border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-50 border-red-400 text-red-700' :
        'bg-blue-50 border-blue-400 text-blue-700'
    }`;
    
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' ? 
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                    type === 'error' ?
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <span class="font-medium">${message}</span>
        </div>
    `;
    
    // Insert at top of debug container
    const container = document.querySelector('.mb-12');
    if (container) {
        container.insertBefore(messageDiv, container.firstChild);
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s ease';
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 500);
    }, 5000);
}

function toggleDebugMode() {
    const button = document.getElementById('toggle-debug-btn');
    const originalText = button.textContent;
    
    // Disable button and show loading state
    button.disabled = true;
    button.textContent = 'Processing...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'ctm_toggle_debug_mode');
    formData.append('nonce', '<?= wp_create_nonce('ctm_toggle_debug_mode') ?>');
    
    // Send AJAX request
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage(data.data.message, 'success');
            
            // Update the entire debug tab content
            const debugTabContent = document.querySelector('.bg-gray-50.p-6.rounded-b-lg');
            if (debugTabContent) {
                debugTabContent.innerHTML = data.data.updated_content;
            } else {
                // Fallback: reload the page if we can't find the tab content
                window.location.reload();
            }
            
            // Show additional feedback
            setTimeout(() => {
                const action = data.data.action;
                if (action === 'enabled') {
                    showDebugMessage('Debug logging is now active. All plugin activity will be recorded.', 'info');
                } else {
                    showDebugMessage('Debug logging has been stopped. Existing logs are preserved.', 'info');
                }
            }, 1000);
            
        } else {
            showDebugMessage(data.data.message || 'Failed to toggle debug mode', 'error');
            // Re-enable button on error
            button.disabled = false;
            button.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error toggling debug mode:', error);
        showDebugMessage('Network error occurred while toggling debug mode', 'error');
        // Re-enable button on error
        button.disabled = false;
        button.textContent = originalText;
    });
}

function updateLogSettings() {
    const button = document.getElementById('update-log-settings-btn');
    const form = document.getElementById('log-settings-form');
    const originalText = button.textContent;
    
    // Disable button and show loading state
    button.disabled = true;
    button.textContent = 'Updating...';
    
    // Get form data
    const formData = new FormData();
    formData.append('action', 'ctm_update_log_settings');
    formData.append('log_retention_days', document.getElementById('log_retention_days').value);
    formData.append('log_notification_email', document.getElementById('log_notification_email').value);
    formData.append('log_auto_cleanup', document.getElementById('log_auto_cleanup').checked ? '1' : '0');
    formData.append('log_email_notifications', document.getElementById('log_email_notifications').checked ? '1' : '0');
    formData.append('nonce', '<?= wp_create_nonce('ctm_update_log_settings') ?>');
    
    // Send AJAX request
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage(data.data.message, 'success');
            
            // Show what was updated
            const settings = data.data.settings;
            let updateDetails = [];
            
            if (settings.retention_days) {
                updateDetails.push(`Retention: ${settings.retention_days} days`);
            }
            
            if (settings.auto_cleanup) {
                updateDetails.push('Auto-cleanup: enabled');
            } else {
                updateDetails.push('Auto-cleanup: disabled');
            }
            
            if (settings.email_notifications) {
                updateDetails.push('Email notifications: enabled');
            } else {
                updateDetails.push('Email notifications: disabled');
            }
            
            if (settings.notification_email) {
                updateDetails.push(`Email: ${settings.notification_email}`);
            }
            
            // Show detailed update message after a short delay
            setTimeout(() => {
                showDebugMessage(`Settings updated: ${updateDetails.join(', ')}`, 'info');
            }, 1000);
            
        } else {
            showDebugMessage(data.data.message || 'Failed to update log settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating log settings:', error);
        showDebugMessage('Network error occurred while updating settings', 'error');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Add form validation
document.getElementById('log_retention_days')?.addEventListener('input', function() {
    const value = parseInt(this.value);
    if (value < 1) {
        this.value = 1;
    } else if (value > 365) {
        this.value = 365;
    }
});

// Add email validation for notifications
document.getElementById('log_email_notifications')?.addEventListener('change', function() {
    const emailField = document.getElementById('log_notification_email');
    const emailLabel = emailField?.previousElementSibling;
    
    if (this.checked) {
        emailField.required = true;
        emailLabel?.classList.add('text-red-600');
        if (emailLabel) {
            emailLabel.innerHTML = emailLabel.innerHTML.replace('Notification Email', 'Notification Email *');
        }
    } else {
        emailField.required = false;
        emailLabel?.classList.remove('text-red-600');
        if (emailLabel) {
            emailLabel.innerHTML = emailLabel.innerHTML.replace('Notification Email *', 'Notification Email');
        }
    }
});

// System Information Functions
<?php
// Generate system information for JavaScript
$system_info_report = "=== SYSTEM INFORMATION REPORT ===\n";
$system_info_report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
$system_info_report .= "Site: " . get_bloginfo('name') . "\n\n";

$system_info_report .= "=== WORDPRESS ENVIRONMENT ===\n";
$system_info_report .= "WordPress Version: " . get_bloginfo('version') . "\n";
$system_info_report .= "Site URL: " . home_url() . "\n";
$system_info_report .= "Admin URL: " . admin_url() . "\n";
$system_info_report .= "WordPress Language: " . get_locale() . "\n";
$system_info_report .= "WordPress Debug: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "WordPress Memory Limit: " . WP_MEMORY_LIMIT . "\n";
$system_info_report .= "Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n\n";

$system_info_report .= "=== SERVER ENVIRONMENT ===\n";
$system_info_report .= "PHP Version: " . PHP_VERSION . "\n";
$system_info_report .= "PHP SAPI: " . php_sapi_name() . "\n";
$system_info_report .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
$system_info_report .= "Operating System: " . PHP_OS . "\n";
$system_info_report .= "Memory Limit: " . ini_get('memory_limit') . "\n";
$system_info_report .= "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
$system_info_report .= "Max Input Vars: " . ini_get('max_input_vars') . "\n";
$system_info_report .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
$system_info_report .= "Post Max Size: " . ini_get('post_max_size') . "\n";
$system_info_report .= "Max File Uploads: " . ini_get('max_file_uploads') . "\n\n";

$system_info_report .= "=== DATABASE ===\n";
$system_info_report .= "Database Version: " . $GLOBALS['wpdb']->db_version() . "\n";
$system_info_report .= "Database Host: " . DB_HOST . "\n";
$system_info_report .= "Database Name: " . DB_NAME . "\n";
$system_info_report .= "Database Charset: " . DB_CHARSET . "\n";
$system_info_report .= "Table Prefix: " . $GLOBALS['wpdb']->prefix . "\n\n";

$system_info_report .= "=== PHP EXTENSIONS ===\n";
$system_info_report .= "cURL: " . (function_exists('curl_init') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "OpenSSL: " . (extension_loaded('openssl') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "mbstring: " . (extension_loaded('mbstring') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "GD Library: " . (extension_loaded('gd') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "XML: " . (extension_loaded('xml') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "JSON: " . (extension_loaded('json') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "ZIP: " . (extension_loaded('zip') ? 'Available' : 'Missing') . "\n\n";

$system_info_report .= "=== CALLTRACKINGMETRICS PLUGIN ===\n";
$system_info_report .= "Plugin Version: 2.0\n";
$system_info_report .= "Debug Mode: " . (get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "API Key Configured: " . (get_option('ctm_api_key') ? 'Yes' : 'No') . "\n";
$system_info_report .= "CF7 Integration: " . (get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "GF Integration: " . (get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled') . "\n\n";

$system_info_report .= "=== THEME & PLUGINS ===\n";
$system_info_report .= "Active Theme: " . wp_get_theme()->get('Name') . "\n";
$system_info_report .= "Theme Version: " . wp_get_theme()->get('Version') . "\n";
$system_info_report .= "Active Plugins: " . count(get_option('active_plugins', [])) . "\n";
$system_info_report .= "Contact Form 7: " . (class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed') . "\n";
$system_info_report .= "Gravity Forms: " . (class_exists('GFAPI') ? 'Installed' : 'Not Installed') . "\n\n";

$system_info_report .= "=== CURRENT PERFORMANCE ===\n";
$system_info_report .= "Memory Usage: " . size_format(memory_get_usage(true)) . "\n";
$system_info_report .= "Peak Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
$system_info_report .= "Database Queries: " . get_num_queries() . "\n";
$system_info_report .= "Admin Email: " . get_option('admin_email') . "\n";
$system_info_report .= "Timezone: " . (get_option('timezone_string') ?: 'UTC') . "\n\n";

$system_info_report .= "=== END REPORT ===";
?>

function copySystemInfo() {
    const button = document.getElementById('copy-system-btn');
    const originalText = button.innerHTML;
    
    console.log('Copy button clicked'); // Debug log
    
    // Show loading state
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Copying...';
    button.disabled = true;
    
    const systemInfo = <?= json_encode($system_info_report) ?>;
    
    console.log('System info length:', systemInfo ? systemInfo.length : 'undefined'); // Debug log
    
    // Check if clipboard API is available
    if (navigator.clipboard && window.isSecureContext) {
        console.log('Using modern clipboard API'); // Debug log
        navigator.clipboard.writeText(systemInfo).then(() => {
            console.log('Clipboard write successful'); // Debug log
            // Success state
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
            
            showDebugMessage('System information copied to clipboard!', 'success');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-600', 'hover:bg-green-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.disabled = false;
            }, 3000);
        }).catch((error) => {
            console.error('Clipboard API failed:', error); // Debug log
            fallbackCopyToClipboard();
        });
    } else {
        console.log('Using fallback clipboard method'); // Debug log
        fallbackCopyToClipboard();
    }
    
    function fallbackCopyToClipboard() {
        try {
            // Fallback for older browsers or non-secure contexts
            const textArea = document.createElement('textarea');
            textArea.value = systemInfo;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            if (successful) {
                console.log('Fallback copy successful'); // Debug log
                // Success state
                button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
                
                showDebugMessage('System information copied to clipboard!', 'success');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600', 'hover:bg-green-700');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    button.disabled = false;
                }, 3000);
            } else {
                throw new Error('execCommand copy failed');
            }
        } catch (error) {
            console.error('Fallback copy failed:', error); // Debug log
            // Show error state
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>Failed';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-red-600', 'hover:bg-red-700');
            
            showDebugMessage('Failed to copy to clipboard. Please copy the information manually from the display above.', 'error');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.disabled = false;
            }, 3000);
        }
    }
}

// Email System Information
function emailSystemInfo() {
    document.getElementById('email-system-modal')?.classList.remove('hidden');
}

function hideEmailSystemForm() {
    document.getElementById('email-system-modal')?.classList.add('hidden');
}

// Handle email system info form submission
document.getElementById('email-system-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const button = document.getElementById('send-system-email-btn');
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Sending...';
    
    const formData = new FormData();
    formData.append('action', 'ctm_email_system_info');
    formData.append('email_to', document.getElementById('system_email_to').value);
    formData.append('subject', document.getElementById('system_email_subject').value);
    formData.append('message', document.getElementById('system_email_message').value);
    formData.append('nonce', '<?= wp_create_nonce('ctm_email_system_info') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage('System information email sent successfully!', 'success');
            hideEmailSystemForm();
        } else {
            showDebugMessage('Failed to send email: ' + (data.data?.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error while sending email', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
});

// Close system email modal when clicking outside
document.getElementById('email-system-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideEmailSystemForm();
    }
});

// API Request Simulator
function simulateApiRequest() {
    const endpoint = document.getElementById('api-endpoint').value;
    const method = document.getElementById('api-method').value;
    const button = document.getElementById('simulate-btn');
    const responseDiv = document.getElementById('api-response');
    const responseContent = document.getElementById('api-response-content');
    
    button.disabled = true;
    button.textContent = 'Sending...';
    responseDiv.classList.add('hidden');
    
    const formData = new FormData();
    formData.append('action', 'ctm_simulate_api_request');
    formData.append('endpoint', endpoint);
    formData.append('method', method);
    formData.append('nonce', '<?= wp_create_nonce('ctm_simulate_api_request') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        responseDiv.classList.remove('hidden');
        responseContent.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            showDebugMessage('API request completed successfully', 'success');
        } else {
            showDebugMessage('API request failed: ' + (data.data?.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        responseDiv.classList.remove('hidden');
        responseContent.textContent = 'Error: ' + error.message;
        showDebugMessage('Network error during API simulation', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Send Test Request';
    });
}

// Plugin Health Check
function runHealthCheck() {
    const button = document.getElementById('health-check-btn');
    const resultsDiv = document.getElementById('health-results');
    
    button.disabled = true;
    button.textContent = 'Running Checks...';
    
    resultsDiv.innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div></div>';
    
    const formData = new FormData();
    formData.append('action', 'ctm_health_check');
    formData.append('nonce', '<?= wp_create_nonce('ctm_health_check') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const checks = data.data.checks;
            let html = '';
            
            checks.forEach(check => {
                const statusColor = check.status === 'pass' ? 'text-green-600' : 
                                  check.status === 'warning' ? 'text-yellow-600' : 'text-red-600';
                const icon = check.status === 'pass' ? '✓' : 
                           check.status === 'warning' ? '⚠' : '✗';
                
                html += `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">${check.name}</span>
                        <span class="${statusColor} font-medium">${icon} ${check.message}</span>
                    </div>
                `;
            });
            
            resultsDiv.innerHTML = html;
            
            const failedChecks = checks.filter(c => c.status === 'fail').length;
            const warningChecks = checks.filter(c => c.status === 'warning').length;
            
            if (failedChecks === 0 && warningChecks === 0) {
                showDebugMessage('All health checks passed!', 'success');
            } else if (failedChecks > 0) {
                showDebugMessage(`Health check completed with ${failedChecks} failures and ${warningChecks} warnings`, 'error');
            } else {
                showDebugMessage(`Health check completed with ${warningChecks} warnings`, 'warning');
            }
        } else {
            resultsDiv.innerHTML = '<div class="text-center text-red-600 py-4">Health check failed</div>';
            showDebugMessage('Health check failed to run', 'error');
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = '<div class="text-center text-red-600 py-4">Network error</div>';
        showDebugMessage('Network error during health check', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Run Health Check';
    });
}

// Performance Monitor
let pageLoadStart = performance.now();
let autoRefreshInterval = null;
let autoRefreshEnabled = false;

function refreshPerformance() {
    const button = document.getElementById('refresh-performance-btn');
    const originalText = button ? button.innerHTML : '';
    
    // Show loading state
    if (button) {
        button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refreshing...';
        button.disabled = true;
    }
    
    const currentTime = performance.now();
    const loadTime = Math.round(currentTime - pageLoadStart);
    
    // Update page load time if element exists
    const loadTimeElement = document.getElementById('page-load-time');
    if (loadTimeElement) {
        loadTimeElement.textContent = loadTime + 'ms';
    }
    
    // Fetch fresh performance data via AJAX
    const formData = new FormData();
    formData.append('action', 'ctm_get_performance_metrics');
    formData.append('nonce', '<?= wp_create_nonce('ctm_get_performance_metrics') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const metrics = data.data;
            
            // Update all performance metrics if elements exist
            const updates = {
                'current-memory': metrics.current_memory,
                'peak-memory': metrics.peak_memory,
                'memory-percentage': metrics.memory_percentage,
                'current-queries': metrics.current_queries,
                'total-queries': metrics.total_queries,
                'query-time': metrics.query_time,
                'page-load-time': metrics.page_load_time,
                'server-response': metrics.server_response,
                'server-load': metrics.server_load,
                'current-timestamp': metrics.current_timestamp,
                'disk-space': metrics.disk_space
            };
            
            Object.entries(updates).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element && value !== undefined) {
                    element.textContent = value;
                }
            });
            
            // Update last updated time
            const lastUpdated = document.getElementById('last-updated');
            if (lastUpdated) {
                lastUpdated.textContent = new Date().toLocaleTimeString();
            }
            
            if (!autoRefreshEnabled) {
                showDebugMessage('Performance metrics refreshed successfully!', 'success');
            }
        } else {
            if (!autoRefreshEnabled) {
                showDebugMessage('Failed to refresh performance metrics', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error fetching performance metrics:', error);
        if (!autoRefreshEnabled) {
            showDebugMessage('Network error while refreshing performance metrics', 'error');
        }
    })
    .finally(() => {
        // Reset button
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function toggleAutoRefresh() {
    const button = document.getElementById('auto-refresh-btn');
    
    if (!button) return;
    
    if (autoRefreshEnabled) {
        // Disable auto-refresh
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        autoRefreshEnabled = false;
        button.textContent = 'Auto: OFF';
        button.classList.remove('bg-green-600', 'hover:bg-green-700');
        button.classList.add('bg-gray-600', 'hover:bg-gray-700');
        showDebugMessage('Auto-refresh disabled', 'info');
    } else {
        // Enable auto-refresh
        autoRefreshInterval = setInterval(refreshPerformance, 30000); // Refresh every 30 seconds
        autoRefreshEnabled = true;
        button.textContent = 'Auto: ON';
        button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        button.classList.add('bg-green-600', 'hover:bg-green-700');
        showDebugMessage('Auto-refresh enabled (30s intervals)', 'success');
    }
}

// Error Analyzer JavaScript Functions

function toggleIssueCategory(categoryId) {
    const category = document.getElementById(categoryId);
    const chevron = document.getElementById(categoryId + '-chevron');
    
    if (category.classList.contains('hidden')) {
        category.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        category.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}

function runSecurityScan() {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Running Security Vulnerability Scan</h5>
                <p class="text-sm text-gray-600">Checking for security issues and vulnerabilities...</p>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Scanning security headers</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Checking file permissions</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Analyzing wp-config.php security</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Scanning for plugin vulnerabilities</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Checking SSL configuration</span>
                </div>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_security_scan');
    formData.append('nonce', '<?= wp_create_nonce('ctm_security_scan') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySecurityScanResults(data.data.results);
            addToDiagnosticHistory('Security Scan', data.data.results.security_score);
            showDebugMessage('Security scan completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Security scan failed</div>';
            showDebugMessage('Security scan failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error during security scan</div>';
        showDebugMessage('Network error during security scan', 'error');
    });
}

function runPerformanceAnalysis() {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Running Performance Analysis</h5>
                <p class="text-sm text-gray-600">Analyzing site performance and optimization opportunities...</p>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Analyzing caching configuration</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Checking database query performance</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Analyzing resource loading times</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Checking CDN configuration</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Measuring page load metrics</span>
                </div>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_performance_analysis');
    formData.append('nonce', '<?= wp_create_nonce('ctm_performance_analysis') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPerformanceAnalysisResults(data.data.results);
            addToDiagnosticHistory('Performance Analysis', data.data.results.performance_score);
            showDebugMessage('Performance analysis completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Performance analysis failed</div>';
            showDebugMessage('Performance analysis failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error during performance analysis</div>';
        showDebugMessage('Network error during performance analysis', 'error');
    });
}

function scheduleHealthCheck() {
    showDebugMessage('Opening health monitoring scheduler...', 'info');
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h5 class="text-lg font-semibold text-gray-800 mb-4">Schedule Health Monitoring</h5>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monitoring Frequency</label>
                    <select id="monitoring-frequency" class="w-full p-2 border border-gray-300 rounded">
                        <option value="hourly">Every Hour</option>
                        <option value="daily" selected>Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                    <input type="email" id="notification-email" placeholder="admin@example.com" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="critical-only" class="rounded">
                    <label for="critical-only" class="text-sm text-gray-700">Only notify for critical issues</label>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button onclick="saveHealthMonitoring()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Save Settings
                </button>
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.onclick = (e) => {
        if (e.target === modal) closeModal();
    };
    
    window.closeModal = () => {
        document.body.removeChild(modal);
        delete window.closeModal;
        delete window.saveHealthMonitoring;
    };
    
    window.saveHealthMonitoring = () => {
        const frequency = document.getElementById('monitoring-frequency').value;
        const email = document.getElementById('notification-email').value;
        const criticalOnly = document.getElementById('critical-only').checked;
        
        const formData = new FormData();
        formData.append('action', 'ctm_schedule_health_check');
        formData.append('frequency', frequency);
        formData.append('email', email);
        formData.append('critical_only', criticalOnly ? '1' : '0');
        formData.append('nonce', '<?= wp_create_nonce('ctm_schedule_health_check') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDebugMessage('Health monitoring scheduled successfully', 'success');
            } else {
                showDebugMessage('Failed to schedule health monitoring', 'error');
            }
            closeModal();
        })
        .catch(error => {
            showDebugMessage('Network error while scheduling', 'error');
            closeModal();
        });
    };
}

function autoFixIssue(fixId) {
    if (!confirm('Are you sure you want to apply this automated fix? This action cannot be undone without a manual rollback.')) {
        return;
    }
    
    showDebugMessage('Applying automated fix...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_auto_fix_issue');
    formData.append('fix_id', fixId);
    formData.append('nonce', '<?= wp_create_nonce('ctm_auto_fix_issue') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage(`Auto-fix applied successfully: ${data.data.description}`, 'success');
            
            // Show rollback option if available
            if (data.data.rollback_available) {
                setTimeout(() => {
                    if (confirm('Auto-fix applied successfully. Would you like to create a rollback point for this change?')) {
                        createRollbackPoint(fixId, data.data.rollback_data);
                    }
                }, 2000);
            }
            
            // Refresh the analysis to show updated status
            setTimeout(() => {
                if (typeof runFullDiagnostic === 'function') {
                    runFullDiagnostic();
                }
            }, 3000);
        } else {
            showDebugMessage(`Auto-fix failed: ${data.data.error}`, 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error during auto-fix', 'error');
    });
}

function createRollbackPoint(fixId, rollbackData) {
    const formData = new FormData();
    formData.append('action', 'ctm_create_rollback_point');
    formData.append('fix_id', fixId);
    formData.append('rollback_data', JSON.stringify(rollbackData));
    formData.append('nonce', '<?= wp_create_nonce('ctm_create_rollback_point') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage('Rollback point created successfully', 'success');
            updateRollbackHistory(fixId, rollbackData);
        } else {
            showDebugMessage('Failed to create rollback point', 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error creating rollback point', 'error');
    });
}

function updateRollbackHistory(fixId, rollbackData) {
    // Store rollback information in localStorage for quick access
    const rollbacks = JSON.parse(localStorage.getItem('ctm_rollback_history') || '[]');
    rollbacks.unshift({
        id: fixId,
        timestamp: new Date().toISOString(),
        description: rollbackData.description || 'Auto-fix rollback point',
        data: rollbackData
    });
    
    // Keep only last 10 rollback points
    if (rollbacks.length > 10) {
        rollbacks.splice(10);
    }
    
    localStorage.setItem('ctm_rollback_history', JSON.stringify(rollbacks));
}

function showRollbackManager() {
    const rollbacks = JSON.parse(localStorage.getItem('ctm_rollback_history') || '[]');
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <h5 class="text-lg font-semibold text-gray-800 mb-4">Rollback Manager</h5>
            <div class="space-y-3">
                ${rollbacks.length === 0 ? 
                    '<div class="text-center text-gray-500 py-8">No rollback points available</div>' :
                    rollbacks.map(rollback => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded">
                            <div>
                                <div class="font-medium text-gray-800">${rollback.description}</div>
                                <div class="text-sm text-gray-600">${new Date(rollback.timestamp).toLocaleString()}</div>
                            </div>
                            <button onclick="executeRollback('${rollback.id}')" class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">
                                Rollback
                            </button>
                        </div>
                    `).join('')
                }
            </div>
            <div class="flex space-x-3 mt-6">
                <button onclick="clearRollbackHistory()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Clear History
                </button>
                <button onclick="closeRollbackModal()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Close
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.closeRollbackModal = () => {
        document.body.removeChild(modal);
        delete window.closeRollbackModal;
        delete window.executeRollback;
        delete window.clearRollbackHistory;
    };
    
    window.executeRollback = (rollbackId) => {
        if (!confirm('Are you sure you want to rollback this change? This will reverse the automated fix.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'ctm_execute_rollback');
        formData.append('rollback_id', rollbackId);
        formData.append('nonce', '<?= wp_create_nonce('ctm_execute_rollback') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDebugMessage('Rollback executed successfully', 'success');
                closeRollbackModal();
                
                // Refresh analysis
                setTimeout(() => {
                    if (typeof runFullDiagnostic === 'function') {
                        runFullDiagnostic();
                    }
                }, 2000);
            } else {
                showDebugMessage(`Rollback failed: ${data.data.error}`, 'error');
            }
        })
        .catch(error => {
            showDebugMessage('Network error during rollback', 'error');
        });
    };
    
    window.clearRollbackHistory = () => {
        if (confirm('Are you sure you want to clear all rollback history?')) {
            localStorage.removeItem('ctm_rollback_history');
            showDebugMessage('Rollback history cleared', 'info');
            closeRollbackModal();
        }
    };
}

function displaySecurityScanResults(results) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Security Scan Results</h5>
                <div class="flex items-center space-x-4">
                    <div class="text-2xl font-bold ${results.security_score >= 80 ? 'text-green-600' : results.security_score >= 60 ? 'text-yellow-600' : 'text-red-600'}">
                        ${results.security_score}/100
                    </div>
                    <div class="text-sm text-gray-600">Security Score</div>
                </div>
            </div>
    `;
    
    if (results.vulnerabilities && results.vulnerabilities.length > 0) {
        html += `
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h6 class="font-semibold text-red-800 mb-3">🚨 Security Vulnerabilities Found</h6>
                <div class="space-y-2">
        `;
        
        results.vulnerabilities.forEach(vuln => {
            html += `
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="font-medium text-red-800">${vuln.title}</div>
                    <div class="text-sm text-red-700">${vuln.description}</div>
                    <div class="text-xs text-red-600 mt-1">Severity: ${vuln.severity}</div>
                </div>
            `;
        });
        
        html += '</div></div>';
    }
    
    if (results.recommendations) {
        html += `
            <div class="space-y-4">
                <h6 class="font-semibold text-green-600">🛡️ Security Recommendations</h6>
                <div class="space-y-2">
        `;
        
        results.recommendations.forEach(rec => {
            html += `<div class="p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">• ${rec}</div>`;
        });
        
        html += '</div></div>';
    }
    
    html += '</div>';
    analysisDiv.innerHTML = html;
}

function displayPerformanceAnalysisResults(results) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Performance Analysis Results</h5>
                <div class="flex items-center space-x-4">
                    <div class="text-2xl font-bold ${results.performance_score >= 80 ? 'text-green-600' : results.performance_score >= 60 ? 'text-yellow-600' : 'text-red-600'}">
                        ${results.performance_score}/100
                    </div>
                    <div class="text-sm text-gray-600">Performance Score</div>
                </div>
            </div>
    `;
    
    if (results.metrics) {
        html += `
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded">
                    <div class="text-sm text-blue-600">Page Load Time</div>
                    <div class="text-lg font-semibold text-blue-800">${results.metrics.load_time}ms</div>
                </div>
                <div class="p-3 bg-purple-50 border border-purple-200 rounded">
                    <div class="text-sm text-purple-600">Database Queries</div>
                    <div class="text-lg font-semibold text-purple-800">${results.metrics.db_queries}</div>
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded">
                    <div class="text-sm text-green-600">Memory Usage</div>
                    <div class="text-lg font-semibold text-green-800">${results.metrics.memory_usage}MB</div>
                </div>
                <div class="p-3 bg-orange-50 border border-orange-200 rounded">
                    <div class="text-sm text-orange-600">Cache Hit Rate</div>
                    <div class="text-lg font-semibold text-orange-800">${results.metrics.cache_hit_rate}%</div>
                </div>
            </div>
        `;
    }
    
    if (results.optimizations) {
        html += `
            <div class="space-y-4">
                <h6 class="font-semibold text-green-600">⚡ Performance Optimizations</h6>
                <div class="space-y-2">
        `;
        
        results.optimizations.forEach(opt => {
            html += `<div class="p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">• ${opt}</div>`;
        });
        
        html += '</div></div>';
    }
    
    html += '</div>';
    analysisDiv.innerHTML = html;
}

function addToDiagnosticHistory(type, score) {
    const historyDiv = document.getElementById('diagnostic-history');
    if (!historyDiv) return;
    
    const now = new Date();
    const timeStr = now.toLocaleTimeString();
    
    // Remove "no diagnostics" message if present
    const noDataMsg = historyDiv.querySelector('.text-center');
    if (noDataMsg) {
        noDataMsg.remove();
    }
    
    const scoreColor = score >= 80 ? 'text-green-600' : score >= 60 ? 'text-yellow-600' : 'text-red-600';
    
    const historyItem = document.createElement('div');
    historyItem.className = 'flex items-center justify-between p-2 bg-white border border-gray-200 rounded text-sm';
    historyItem.innerHTML = `
        <div>
            <span class="font-medium">${type}</span>
            <span class="text-gray-500 ml-2">${timeStr}</span>
        </div>
        <span class="font-semibold ${scoreColor}">${score}/100</span>
    `;
    
    historyDiv.insertBefore(historyItem, historyDiv.firstChild);
    
    // Keep only last 5 items
    const items = historyDiv.querySelectorAll('.flex');
    if (items.length > 5) {
        items[items.length - 1].remove();
    }
}

function clearDiagnosticHistory() {
    const historyDiv = document.getElementById('diagnostic-history');
    if (historyDiv) {
        historyDiv.innerHTML = '<div class="text-center text-gray-500 text-sm py-4">No recent diagnostics found</div>';
        showDebugMessage('Diagnostic history cleared', 'info');
    }
}

function exportDiagnosticReport() {
    showDebugMessage('Generating diagnostic report...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_export_diagnostic_report');
    formData.append('nonce', '<?= wp_create_nonce('ctm_export_diagnostic_report') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create download link
            const blob = new Blob([data.data.report], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `ctm-diagnostic-report-${new Date().toISOString().slice(0, 10)}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showDebugMessage('Diagnostic report exported successfully', 'success');
        } else {
            showDebugMessage('Failed to export diagnostic report', 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error during export', 'error');
    });
}

function runFullDiagnostic() {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Running Full System Diagnostic</h5>
                <p class="text-sm text-gray-600">Analyzing all system components and configurations...</p>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Checking API connectivity...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Validating form integrations...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Testing network configuration...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Scanning for plugin conflicts...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_full_diagnostic');
    formData.append('nonce', '<?= wp_create_nonce('ctm_full_diagnostic') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDiagnosticResults(data.data);
            showDebugMessage('Full diagnostic completed successfully', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Diagnostic failed to run</div>';
            showDebugMessage('Full diagnostic failed to run', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error during diagnostic</div>';
        showDebugMessage('Network error during full diagnostic', 'error');
    });
}

function displayDiagnosticResults(results) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Diagnostic Results</h5>
                <div class="flex items-center space-x-4 text-sm">
                    <span class="text-green-600">✓ ${results.passed_checks} Passed</span>
                    <span class="text-yellow-600">⚠ ${results.warning_checks} Warnings</span>
                    <span class="text-red-600">✗ ${results.failed_checks} Failed</span>
                </div>
            </div>
    `;
    
    // Display critical issues first
    if (results.critical_issues && results.critical_issues.length > 0) {
        html += `
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h6 class="font-semibold text-red-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Critical Issues Requiring Immediate Attention
                </h6>
                <div class="space-y-3">
        `;
        
        results.critical_issues.forEach(issue => {
            html += `
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="font-medium text-red-800 mb-1">${issue.title}</div>
                    <div class="text-sm text-red-700 mb-2">${issue.description}</div>
                    ${issue.auto_fix_available ? 
                        `<button onclick="autoFixIssue('${issue.fix_id}')" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1 rounded">Auto-Fix</button>` : 
                        '<span class="text-xs text-red-600">Manual fix required</span>'
                    }
                </div>
            `;
        });
        
        html += '</div></div>';
    }
    
    // Display detailed results by category
    if (results.categories) {
        html += '<div class="space-y-6">';
        
        Object.entries(results.categories).forEach(([category, data]) => {
            const statusColor = data.status === 'healthy' ? 'green' : data.status === 'warning' ? 'yellow' : 'red';
            const statusIcon = data.status === 'healthy' ? '✓' : data.status === 'warning' ? '⚠' : '✗';
            
            html += `
                <div class="border border-gray-200 rounded-lg">
                    <div class="p-4 bg-${statusColor}-50 border-b border-${statusColor}-200">
                        <h6 class="font-semibold text-${statusColor}-800 flex items-center">
                            <span class="mr-2">${statusIcon}</span>
                            ${data.title}
                            <span class="ml-auto text-sm">${data.score}/100</span>
                        </h6>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-600 mb-3">${data.description}</p>
            `;
            
            if (data.issues && data.issues.length > 0) {
                html += '<div class="mb-3"><strong class="text-red-600">Issues:</strong><ul class="mt-1 space-y-1">';
                data.issues.forEach(issue => {
                    html += `<li class="text-sm text-red-700">• ${issue}</li>`;
                });
                html += '</ul></div>';
            }
            
            if (data.recommendations && data.recommendations.length > 0) {
                html += '<div><strong class="text-green-600">Recommendations:</strong><ul class="mt-1 space-y-1">';
                data.recommendations.forEach(rec => {
                    html += `<li class="text-sm text-green-700">• ${rec}</li>`;
                });
                html += '</ul></div>';
            }
            
            html += '</div></div>';
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    
    analysisDiv.innerHTML = html;
}

function checkIssue(issueType) {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Analyzing Issue</h5>
                <p class="text-sm text-gray-600">Running comprehensive analysis...</p>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_analyze_issue');
    formData.append('issue_type', issueType);
    formData.append('nonce', '<?= wp_create_nonce('ctm_analyze_issue') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayIssueAnalysis(data.data.analysis);
            showDebugMessage('Issue analysis completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Analysis failed</div>';
            showDebugMessage('Issue analysis failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error</div>';
        showDebugMessage('Network error during analysis', 'error');
    });
}

function displayIssueAnalysis(analysis) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <h5 class="text-xl font-semibold text-gray-800 mb-4">Issue Analysis Results</h5>
    `;
    
    if (analysis.issues && analysis.issues.length > 0) {
        html += '<div class="space-y-4">';
        
        analysis.issues.forEach(issue => {
            const severityColor = issue.severity === 'critical' ? 'red' : issue.severity === 'warning' ? 'yellow' : 'blue';
            
            html += `
                <div class="border border-${severityColor}-200 rounded-lg p-4 bg-${severityColor}-50">
                    <h6 class="font-semibold text-${severityColor}-800 mb-2">${issue.title}</h6>
                    <p class="text-sm text-${severityColor}-700 mb-3">${issue.description}</p>
                    
                    ${issue.solution ? `
                        <div class="bg-white p-3 rounded border border-${severityColor}-200">
                            <strong class="text-${severityColor}-800">Recommended Solution:</strong>
                            <p class="text-sm text-${severityColor}-700 mt-1">${issue.solution}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        });
        
        html += '</div>';
    } else {
        html += '<div class="text-center py-8 text-green-600">No issues detected in this category.</div>';
    }
    
    html += '</div>';
    
    analysisDiv.innerHTML = html;
}

// Initialize email field requirement state
document.addEventListener('DOMContentLoaded', function() {
    const emailNotifications = document.getElementById('log_email_notifications');
    if (emailNotifications && emailNotifications.checked) {
        emailNotifications.dispatchEvent(new Event('change'));
    }
    
    // Initialize performance monitoring on page load
    pageLoadStart = performance.now();
    setTimeout(refreshPerformance, 1000);
    
    // Load diagnostic history from localStorage if available
    const savedHistory = localStorage.getItem('ctm_diagnostic_history');
    if (savedHistory) {
        try {
            const history = JSON.parse(savedHistory);
            history.forEach(item => {
                addToDiagnosticHistory(item.type, item.score);
            });
        } catch (e) {
            console.log('Failed to load diagnostic history');
        }
    }
});

function refreshSystemInfo() {
    const button = document.getElementById('refresh-system-btn');
    const originalText = button.textContent;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Refreshing...
    `;
    
    showDebugMessage('Refreshing system information...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_refresh_system_info');
    formData.append('nonce', '<?= wp_create_nonce('ctm_refresh_system_info') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update system info panels with fresh data
            if (data.data && data.data.system_info) {
                updateSystemInfoDisplay(data.data.system_info);
            }
            showDebugMessage('System information refreshed successfully', 'success');
        } else {
            const errorMessage = (data && data.data && data.data.message) || 
                                (data && data.message) || 
                                'Unknown error occurred';
            showDebugMessage('Failed to refresh system info: ' + errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('System info refresh error:', error);
        showDebugMessage('Error refreshing system information: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh System Info
        `;
    });
}

function updateSystemInfoDisplay(systemInfo) {
    // Update the metric cards
    if (systemInfo.php_version) {
        const phpCard = document.querySelector('[data-metric="php_version"]');
        if (phpCard) {
            phpCard.querySelector('.text-2xl').textContent = systemInfo.php_version;
        }
    }
    
    if (systemInfo.wp_version) {
        const wpCard = document.querySelector('[data-metric="wp_version"]');
        if (wpCard) {
            wpCard.querySelector('.text-2xl').textContent = systemInfo.wp_version;
        }
    }
    
    if (systemInfo.memory_usage) {
        const memCard = document.querySelector('[data-metric="memory_usage"]');
        if (memCard) {
            memCard.querySelector('.text-2xl').textContent = systemInfo.memory_usage;
        }
    }
    
    if (systemInfo.db_queries) {
        const dbCard = document.querySelector('[data-metric="db_queries"]');
        if (dbCard) {
            dbCard.querySelector('.text-2xl').textContent = systemInfo.db_queries;
        }
    }
    
    // Update detailed information sections
    if (systemInfo.wordpress_env) {
        updateWordPressEnvironment(systemInfo.wordpress_env);
    }
    
    if (systemInfo.server_env) {
        updateServerEnvironment(systemInfo.server_env);
    }
    
    if (systemInfo.database_info) {
        updateDatabaseInfo(systemInfo.database_info);
    }
}

function updateWordPressEnvironment(wpEnv) {
    // Update WordPress environment details
    const wpSection = document.querySelector('[data-section="wordpress-env"]');
    if (wpSection && wpEnv) {
        Object.keys(wpEnv).forEach(key => {
            const element = wpSection.querySelector(`[data-field="${key}"]`);
            if (element) {
                element.textContent = wpEnv[key];
            }
        });
    }
}

function updateServerEnvironment(serverEnv) {
    // Update server environment details
    const serverSection = document.querySelector('[data-section="server-env"]');
    if (serverSection && serverEnv) {
        Object.keys(serverEnv).forEach(key => {
            const element = serverSection.querySelector(`[data-field="${key}"]`);
            if (element) {
                element.textContent = serverEnv[key];
            }
        });
    }
}

function updateDatabaseInfo(dbInfo) {
    // Update database information
    const dbSection = document.querySelector('[data-section="database-info"]');
    if (dbSection && dbInfo) {
        Object.keys(dbInfo).forEach(key => {
            const element = dbSection.querySelector(`[data-field="${key}"]`);
            if (element) {
                element.textContent = dbInfo[key];
            }
        });
    }
}

</script> 