<?php
/**
 * Daily Logs Component
 * Displays daily debug logs with filtering, viewing, and management options
 */

// Ensure variables are available from parent context
$available_dates = $available_dates ?? [];
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
        <svg class="inline-block w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <?php _e('Daily Debug Logs', 'call-tracking-metrics'); ?>
    </h3>

    <?php if (empty($available_dates)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900"><?php _e('No debug logs found', 'call-tracking-metrics'); ?></h3>
            <p class="mt-2 text-gray-500"><?php _e('Enable debug mode to start logging plugin activity.', 'call-tracking-metrics'); ?></p>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php foreach (array_slice($available_dates, 0, 10) as $date): ?>
                <?php 
                $logs = get_option("ctm_daily_log_{$date}", []);
                $log_count = count($logs);
                $error_count = count(array_filter($logs, function($log) { return $log['type'] === 'error'; }));
                $warning_count = count(array_filter($logs, function($log) { return $log['type'] === 'warning'; }));
                ?>
                <div class="border border-gray-200 rounded-lg overflow-hidden mb-4">
                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <h4 class="text-lg font-medium text-gray-900"><?= date('M j, Y', strtotime($date)) ?></h4>
                                <div class="flex space-x-3 text-sm ml-4">
                                    <span class="text-gray-600"><?php _e('entries', 'call-tracking-metrics'); ?></span>
                                    <?php if ($error_count > 0): ?>
                                        <span class="text-red-600"><?php _e('errors', 'call-tracking-metrics'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($warning_count > 0): ?>
                                        <span class="text-yellow-600"><?php _e('warnings', 'call-tracking-metrics'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button onclick="toggleLogView('<?= $date ?>')" class="text-blue-600 hover:text-blue-800 text-sm font-medium"><?php _e('View Details', 'call-tracking-metrics'); ?></button>
                                
                                <button onclick="showEmailForm('<?= $date ?>')" class="text-green-600 hover:text-green-800 text-sm font-medium"><?php _e('Email Log', 'call-tracking-metrics'); ?></button>
                                
                                <button type="button" onclick="clearDebugLogs('debug_single', '<?= $date ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed" id="clear-single-<?= $date ?>-btn"><?php _e('Clear', 'call-tracking-metrics'); ?></button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="log-<?= $date ?>" class="hidden px-6 py-4 max-h-96 overflow-y-auto">
                        <div class="space-y-3">
                            <?php foreach (array_reverse($logs) as $entry): ?>
                                <?php
                                $type_colors = [
                                    'error' => 'text-red-800 bg-red-100',
                                    'warning' => 'text-yellow-800 bg-yellow-100',
                                    'info' => 'text-blue-800 bg-blue-100',
                                    'debug' => 'text-gray-800 bg-gray-100',
                                    'api' => 'text-purple-800 bg-purple-100',
                                    'config' => 'text-indigo-800 bg-indigo-100',
                                    'system' => 'text-green-800 bg-green-100'
                                ];
                                $color_class = $type_colors[$entry['type']] ?? 'text-gray-800 bg-gray-100';
                                ?>
                                <div class="border-l-4 border-gray-200 pl-4 py-2">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="<?= $color_class ?> px-2 py-1 text-xs font-medium rounded"><?= esc_html(strtoupper($entry['type'])) ?></span>
                                                <span class="text-sm text-gray-500"><?= esc_html($entry['timestamp']) ?></span>
                                            </div>
                                            <p class="text-gray-900 text-sm"><?= esc_html($entry['message']) ?></p>
                                            <?php if (!empty($entry['context'])): ?>
                                                <details class="mt-2">
                                                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">Context Details</summary>
                                                    <pre class="mt-1 text-xs text-gray-600 bg-gray-50 p-2 rounded overflow-x-auto"><?= esc_html(print_r($entry['context'], true)) ?></pre>
                                                </details>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($available_dates) > 10): ?>
                <div class="text-center py-4">
                    <p class="text-gray-500"><?php printf(__('Showing latest 10 days. %d more days available.', 'call-tracking-metrics'), count($available_dates) - 10); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Email Log Modal -->
<div id="email-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
        <button onclick="hideEmailForm()" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
        <h3 class="text-lg font-semibold text-gray-900 mb-4"><?php _e('Email Debug Log', 'call-tracking-metrics'); ?></h3>
        <form id="email-log-form" onsubmit="submitEmailLog(event)">
            <input type="hidden" id="email-log-date" name="log_date" value="">
            <label for="email-log-to" class="block text-sm font-medium text-gray-700 mb-1"><?php _e('Send to', 'call-tracking-metrics'); ?></label>
            <input type="email" id="email-log-to" name="to" class="w-full border border-gray-300 rounded px-3 py-2 mb-4" required>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="hideEmailForm()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded"><?php _e('Cancel', 'call-tracking-metrics'); ?></button>
                <button type="submit" id="email-log-send-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"><?php _e('Send', 'call-tracking-metrics'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
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
        document.getElementById('email-log-to').value = '<?= esc_js(get_option('admin_email')) ?>';
        document.getElementById('email-modal').classList.remove('hidden');
    }

    function hideEmailForm() {
        document.getElementById('email-modal').classList.add('hidden');
    }

    function submitEmailLog(e) {
        e.preventDefault();
        const btn = document.getElementById('email-log-send-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Sending...';

        const date = document.getElementById('email-log-date').value;
        const email_to = document.getElementById('email-log-to').value;

        const formData = new FormData();
        formData.append('action', 'ctm_email_daily_log');
        formData.append('nonce', '<?= wp_create_nonce('ctm_email_daily_log') ?>');
        formData.append('log_date', date);
        formData.append('email_to', email_to);

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDebugMessage(data.data.message, 'success');
                hideEmailForm();
            } else {
                showDebugMessage(data.data.message || 'Failed to email log', 'error');
            }
        })
        .catch(() => {
            showDebugMessage('Network error while emailing log', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
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


</script>