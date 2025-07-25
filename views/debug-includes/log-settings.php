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

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
    <div class="flex items-center mb-4 gap-1">
        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <h3 class="text-lg font-bold text-gray-900"><?php _e('Log Settings', 'call-tracking-metrics'); ?></h3>
    </div>

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

<script>

    function updateLogSettings() {
        const button = document.getElementById('update-log-settings-btn');
        const form = document.getElementById('log-settings-form');
        const originalText = button.textContent;
        
        // Disable button and show loading state
        button.disabled = true;
        button.textContent = <?php _e('Updating...', 'call-tracking-metrics'); ?>;
        
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
                showDebugMessage(data.data.message || <?php _e('Failed to update log settings', 'call-tracking-metrics'); ?>, 'error');
            }
        })
        .catch(error => {
            console.error('Error updating log settings:', error);
            showDebugMessage(<?php _e('Network error occurred while updating settings', 'call-tracking-metrics'); ?>, 'error');
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