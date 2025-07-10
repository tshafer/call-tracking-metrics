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
    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Log Settings
    </h3>

    <form id="log-settings-form" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="log_retention_days" class="block text-sm font-medium text-gray-700 mb-2">Log Retention (Days)</label>
                <input type="number" id="log_retention_days" name="log_retention_days" min="1" max="365" value="<?= $retention_days ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-sm text-gray-500">Logs older than this will be automatically deleted (1-365 days)</p>
            </div>

            <div>
                <label for="log_notification_email" class="block text-sm font-medium text-gray-700 mb-2">Notification Email</label>
                <input type="email" id="log_notification_email" name="log_notification_email" value="<?= esc_attr($notification_email) ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-sm text-gray-500">Email address for log notifications and reports</p>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" id="log_auto_cleanup" name="log_auto_cleanup" <?= $auto_cleanup ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="log_auto_cleanup" class="ml-2 block text-sm text-gray-900">
                    Enable automatic log cleanup based on retention period
                </label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="log_email_notifications" name="log_email_notifications" <?= $email_notifications ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="log_email_notifications" class="ml-2 block text-sm text-gray-900">
                    Send email notifications for critical errors
                </label>
            </div>
        </div>

        <button type="button" onclick="updateLogSettings()" id="update-log-settings-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            Update Log Settings
        </button>
    </form>
</div> 