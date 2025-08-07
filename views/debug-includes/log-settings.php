<?php
/**
 * Log Settings Component
 * Displays log configuration options including retention, cleanup, and notifications
 */

// Ensure variables are available from parent context
$retention_days = $retention_days ?? (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = $auto_cleanup ?? get_option('ctm_log_auto_cleanup', true);
$email_notifications = $email_notifications ?? get_option('ctm_log_email_notifications', false);
$notification_email = $notification_email ?? get_option('ctm_log_notification_email', get_option('admin_email'));
?>

<div class="space-y-8">
    <!-- Log Settings Overview Section -->
    <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-6 rounded-lg border border-orange-200">
        <div class="flex items-center mb-4">
            <div class="bg-orange-100 p-2 rounded-lg mr-3">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-orange-700"><?php _e('Log Settings', 'call-tracking-metrics'); ?></h3>
        </div>
        <p class="mb-4"><?php _e('Configure how debug logs are managed. Set retention periods, enable automatic cleanup, and receive email notifications for critical errors. Keeping logs well-managed helps maintain site performance and security.', 'call-tracking-metrics'); ?></p>
        <ul class="list-disc pl-6 mb-4 space-y-2">
            <li class="flex items-start">
                <span class="mr-2">📅</span>
                <?php _e('Set how many days logs are kept (1-365 days)', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">🧹</span>
                <?php _e('Enable or disable automatic log cleanup', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">📧</span>
                <?php _e('Configure email notifications for errors', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">📮</span>
                <?php _e('Set the notification email address', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">💾</span>
                <?php _e('Update log settings instantly', 'call-tracking-metrics'); ?>
            </li>
        </ul>
    </div>

    <!-- Log Settings Form Section -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
        <form id="log-settings-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="log_retention_days" class="block text-sm font-medium text-gray-700 mb-2"><?php _e('Log Retention (Days)', 'call-tracking-metrics'); ?></label>
                    <input type="number" id="log_retention_days" name="log_retention_days" min="1" max="365" value="<?= $retention_days ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500"><?php _e('Logs older than this will be automatically deleted (1-365 days)', 'call-tracking-metrics'); ?></p>
                </div>

                <div>
                    <label for="log_notification_email" class="block text-sm font-medium text-gray-700 mb-2"><?php _e('Notification Email', 'call-tracking-metrics'); ?></label>
                    <input type="email" id="log_notification_email" name="log_notification_email" value="<?= esc_attr($notification_email) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-sm text-gray-500"><?php _e('Email address for log notifications and reports', 'call-tracking-metrics'); ?></p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" id="log_auto_cleanup" name="log_auto_cleanup" <?= $auto_cleanup ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="log_auto_cleanup" class="ml-2 block text-sm text-gray-900"><?php _e('Enable automatic log cleanup based on retention period', 'call-tracking-metrics'); ?></label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="log_email_notifications" name="log_email_notifications" <?= $email_notifications ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="log_email_notifications" class="ml-2 block text-sm text-gray-900"><?php _e('Send email notifications for critical errors', 'call-tracking-metrics'); ?></label>
                </div>
            </div>

            <div class="flex justify-center my-4">
                <button type="button" onclick="updateLogSettings()" id="update-log-settings-btn" class="bg-blue-600 hover:bg-blue-700 !text-white font-medium px-6 py-2 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?php _e('Update Settings', 'call-tracking-metrics'); ?>
                </button>
            </div>
        
        </form>
    </div>

    <!-- Clear All Logs Section -->
    <div class="bg-white rounded-xl shadow-lg border border-red-200 p-8 mt-8">
        <div class="text-center">
            <div class="bg-red-100 p-3 rounded-full inline-flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-red-700 mb-2"><?php _e('Clear All Logs', 'call-tracking-metrics'); ?></h3>
            <p class="text-gray-600 mb-6"><?php _e('This will permanently delete all debug logs, form logs, and log history. This action cannot be undone.', 'call-tracking-metrics'); ?></p>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-medium text-red-800"><?php _e('Warning', 'call-tracking-metrics'); ?></h4>
                        <p class="text-sm text-red-700 mt-1"><?php _e('This action will remove all logs including daily logs, form-specific logs, and log history. This is irreversible.', 'call-tracking-metrics'); ?></p>
                    </div>
                </div>
            </div>

            <div class="flex justify-center space-x-4">
                <button type="button" onclick="showClearAllLogsModal()" id="clear-all-logs-btn" class="bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <?php _e('Clear All Logs', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>

    function updateLogSettings() {
        const button = document.getElementById('update-log-settings-btn');
        const form = document.getElementById('log-settings-form');
        const originalText = button.textContent;
        
        // Disable button and show loading state
        button.disabled = true;
        button.textContent = '<?php _e('Updating...', 'call-tracking-metrics'); ?>';
        
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
                ctmShowToast(data.data.message, 'success');
                
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
                    ctmShowToast(`Settings updated: ${updateDetails.join(', ')}`, 'info');
                }, 1000);
                
            } else {
                ctmShowToast(data.data.message || '<?php _e('Failed to update log settings', 'call-tracking-metrics'); ?>', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating log settings:', error);
            ctmShowToast('<?php _e('Network error occurred while updating settings', 'call-tracking-metrics'); ?>', 'error');
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.textContent = originalText;
        });
    }

    function testEmail() {
        const btn = document.getElementById('test-email-btn');
        const emailField = document.getElementById('test-email-to');
        const originalText = btn.textContent;
        
        const email = emailField.value.trim();
        if (!email || !email.includes('@')) {
            ctmShowToast('Please enter a valid email address', 'error');
            return;
        }
        
        // Disable button and show loading state
        btn.disabled = true;
        btn.textContent = 'Sending...';
        
        // Get form data
        const formData = new FormData();
        formData.append('action', 'ctm_test_email');
        formData.append('email_to', email);
        formData.append('nonce', '<?= wp_create_nonce('ctm_test_email') ?>');
        
        console.log('Sending test email to:', email);
        
        // Send AJAX request
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Test email response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Test email response data:', data);
            if (data.success) {
                ctmShowToast(data.data.message, 'success');
                emailField.value = '';
            } else {
                ctmShowToast(data.data.message || 'Failed to send test email', 'error');
            }
        })
        .catch(error => {
            console.error('Test email error:', error);
            ctmShowToast('Network error while sending test email: ' + error.message, 'error');
        })
        .finally(() => {
            // Re-enable button
            btn.disabled = false;
            btn.textContent = originalText;
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
                emailLabel.innerHTML = emailLabel.innerHTML.replace('<?php _e('Notification Email', 'call-tracking-metrics'); ?>', '<?php _e('Notification Email', 'call-tracking-metrics'); ?> *');
            }
        } else {
            emailField.required = false;
            emailLabel?.classList.remove('text-red-600');
            if (emailLabel) {
                emailLabel.innerHTML = emailLabel.innerHTML.replace('<?php _e('Notification Email', 'call-tracking-metrics'); ?> *', '<?php _e('Notification Email', 'call-tracking-metrics'); ?>');
            }
        }
    });

    // Clear All Logs functionality
    function showClearAllLogsModal() {
        // Create modal HTML
        const modalHTML = `
            <div id="clear-all-logs-modal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
                <div class="relative mx-auto p-6 border w-96 shadow-lg rounded-lg bg-white">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4"><?php _e('Clear All Logs', 'call-tracking-metrics'); ?></h3>
                        <div class="mt-2 px-7 py-3">
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-red-700 font-medium">
                                            <?php _e('This action cannot be undone!', 'call-tracking-metrics'); ?>
                                        </p>
                                        <p class="text-sm text-red-600 mt-1">
                                            <?php _e('All debug logs, form logs, and log history will be permanently deleted.', 'call-tracking-metrics'); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500">
                                <?php _e('Are you sure you want to clear all logs? This will remove all debugging information and cannot be recovered.', 'call-tracking-metrics'); ?>
                            </p>
                        </div>
                        <div class="flex space-x-3 mt-6">
                            <button type="button" onclick="closeClearAllLogsModal()" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 transition-colors">
                                <?php _e('Cancel', 'call-tracking-metrics'); ?>
                            </button>
                            <button type="button" onclick="clearAllLogs()" class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 transition-colors">
                                <?php _e('Clear All Logs', 'call-tracking-metrics'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    function closeClearAllLogsModal() {
        const modal = document.getElementById('clear-all-logs-modal');
        if (modal) {
            modal.remove();
            document.body.style.overflow = '';
        }
    }

    function clearAllLogs() {
        const button = document.getElementById('clear-all-logs-btn');
        const originalText = button.textContent;
        
        // Disable button and show loading state
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <?php _e('Clearing...', 'call-tracking-metrics'); ?>
        `;
        
        // Close modal
        closeClearAllLogsModal();
        
        // Get form data
        const formData = new FormData();
        formData.append('action', 'ctm_clear_all_logs');
        formData.append('nonce', '<?= wp_create_nonce('ctm_clear_all_logs') ?>');
        
        // Send AJAX request
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ctmShowToast(data.data.message, 'success');
                
                // Reload page after a short delay to reflect changes
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                ctmShowToast(data.data.message || '<?php _e('Failed to clear all logs', 'call-tracking-metrics'); ?>', 'error');
            }
        })
        .catch(error => {
            console.error('Error clearing all logs:', error);
            ctmShowToast('<?php _e('Network error occurred while clearing logs', 'call-tracking-metrics'); ?>', 'error');
        })
        .finally(() => {
            // Re-enable button
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeClearAllLogsModal();
        }
    });
</script>