<?php
/**
 * Log Settings Component
 * Streamlined log configuration options
 */

// Get current settings
$retention_days = (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = get_option('ctm_log_auto_cleanup', true);
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200">
    <div class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50 transition-colors" onclick="togglePanel('log-settings')">
        <div class="flex items-center gap-2">
            <svg id="log-settings-icon" class="w-5 h-5 text-orange-600 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg);">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900"><?php _e('Log Settings', 'call-tracking-metrics'); ?></h3>
        </div>
    </div>
    
    <div id="log-settings-content" class="border-t border-gray-200 p-6">
        <form id="log-settings-form" class="space-y-4">
            <!-- Retention Settings -->
            <div>
                <label for="log_retention_days" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Retention (Days)', 'call-tracking-metrics'); ?></label>
                <input type="number" id="log_retention_days" name="log_retention_days" min="1" max="365" value="<?= $retention_days ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Checkboxes -->
            <div class="space-y-3">
                <div class="flex items-center">
                    <input type="checkbox" id="log_auto_cleanup" name="log_auto_cleanup" <?= $auto_cleanup ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="log_auto_cleanup" class="ml-2 text-sm text-gray-900"><?php _e('Auto-cleanup logs', 'call-tracking-metrics'); ?></label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 pt-4 border-t border-gray-200">
                <button type="button" onclick="updateLogSettings()" id="update-log-settings-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?php _e('Update', 'call-tracking-metrics'); ?>
                </button>
                <button type="button" onclick="clearAllLogs()" id="clear-all-logs-btn" class="bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <?php _e('Clear All', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function updateLogSettings() {
        const button = document.getElementById('update-log-settings-btn');
        const originalText = button.textContent;
        
        button.disabled = true;
        button.textContent = '<?php _e('Updating...', 'call-tracking-metrics'); ?>';
        
        const formData = new FormData();
        formData.append('action', 'ctm_update_log_settings');
        formData.append('log_retention_days', document.getElementById('log_retention_days').value);
        formData.append('log_auto_cleanup', document.getElementById('log_auto_cleanup').checked ? '1' : '0');
        formData.append('nonce', '<?= wp_create_nonce('ctm_update_log_settings') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.success) {
                ctmShowToast('Settings updated successfully', 'success');
            } else {
                ctmShowToast('Failed to update settings', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating log settings:', error);
            ctmShowToast('Network error occurred', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = originalText;
        });
    }

    function clearAllLogs() {
        if (!confirm('<?php _e('Are you sure you want to clear all logs? This action cannot be undone.', 'call-tracking-metrics'); ?>')) {
            return;
        }
        
        const button = document.getElementById('clear-all-logs-btn');
        const originalText = button.textContent;
        
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <?php _e('Clearing...', 'call-tracking-metrics'); ?>
        `;
        
        const formData = new FormData();
        formData.append('action', 'ctm_clear_all_logs');
        formData.append('nonce', '<?= wp_create_nonce('ctm_clear_all_logs') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                ctmShowToast('All logs cleared successfully', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                ctmShowToast('Failed to clear logs', 'error');
            }
        })
        .catch(error => {
            console.error('Error clearing logs:', error);
            ctmShowToast('Network error occurred', 'error');
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
    }

    // Form validation
    document.getElementById('log_retention_days')?.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (value < 1) this.value = 1;
        if (value > 365) this.value = 365;
    });
</script>