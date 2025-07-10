<?php
/**
 * Debug Modals Component
 * Contains email modals for debug log and system information functionality
 */

// Ensure variables are available from parent context
$notification_email = $notification_email ?? get_option('ctm_log_notification_email', get_option('admin_email'));
?>

<!-- Email Log Modal -->
<div id="email-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Email Debug Log</h3>
            <form method="post" id="email-form">
                <input type="hidden" id="email-log-date" name="log_date" value="">
                <div class="mb-4">
                    <label for="email_to" class="block text-sm font-medium text-gray-700 mb-2">Email To:</label>
                    <input type="email" id="email_to" name="email_to" value="<?= esc_attr($notification_email) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEmailForm()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" name="email_log" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email System Info Modal -->
<div id="email-system-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Email System Information</h3>
            <form id="email-system-form">
                <div class="mb-4">
                    <label for="system_email_to" class="block text-sm font-medium text-gray-700 mb-2">Email To:</label>
                    <input type="email" id="system_email_to" name="email_to" value="<?= esc_attr($notification_email) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="system_email_subject" class="block text-sm font-medium text-gray-700 mb-2">Subject:</label>
                    <input type="text" id="system_email_subject" name="subject" value="System Information Report - <?= get_bloginfo('name') ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="mb-4">
                    <label for="system_email_message" class="block text-sm font-medium text-gray-700 mb-2">Additional Message (Optional):</label>
                    <textarea id="system_email_message" name="message" rows="3" placeholder="Add any additional context or notes..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="hideEmailSystemForm()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="send-system-email-btn" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div> 