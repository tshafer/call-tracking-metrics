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
            <h3 class="text-xl font-semibold text-orange-700"><?php _e('⚙️ Log Settings', 'call-tracking-metrics'); ?></h3>
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
                <button type="button" onclick="updateLogSettings()" id="update-log-settings-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"><?php _e('Update Log Settings', 'call-tracking-metrics'); ?></button>
            </div>
        
        </form>
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
</script>