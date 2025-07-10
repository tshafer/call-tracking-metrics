<?php
// Debug tab view with comprehensive logging system
// Variables are passed from the parent context: $debugEnabled, $log

$retention_days = (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = get_option('ctm_log_auto_cleanup', true);
$email_notifications = get_option('ctm_log_email_notifications', false);
$notification_email = get_option('ctm_log_notification_email', get_option('admin_email'));

// Get log statistics and available dates
$log_stats = ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
$available_dates = [];

if ($debugEnabled) {
    // Get log index directly from WordPress options
    $log_index = get_option('ctm_log_index', []);
    if (is_array($log_index)) {
        $available_dates = array_reverse($log_index);
        
        // Calculate statistics
        $total_entries = 0;
        $total_size = 0;
        $type_counts = [];
        
        foreach ($log_index as $date) {
            $logs = get_option("ctm_daily_log_{$date}", []);
            if (is_array($logs)) {
                $total_entries += count($logs);
                $total_size += strlen(serialize($logs));
                
                foreach ($logs as $log_entry) {
                    $type = $log_entry['type'] ?? 'unknown';
                    $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
                }
            }
        }
        
        $log_stats = [
            'total_days' => count($log_index),
            'total_entries' => $total_entries,
            'total_size' => $total_size,
            'type_counts' => $type_counts,
            'oldest_log' => !empty($log_index) ? end($log_index) : null,
            'newest_log' => !empty($log_index) ? reset($log_index) : null
        ];
    }
}
?>

<div class="mb-12">
    <!-- Debug Status and Controls -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Debug & Logging System</h2>
            <?php if ($debugEnabled): ?>
                <span class="ml-auto px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">Active</span>
            <?php else: ?>
                <span class="ml-auto px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-full">Inactive</span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Debug Controls -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Debug Controls</h3>
                <div class="space-y-4">
                    <div class="flex flex-wrap gap-3">
                        <button type="button" onclick="toggleDebugMode()" id="toggle-debug-btn" class="<?= $debugEnabled ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' ?> text-white font-medium px-6 py-2 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <?= $debugEnabled ? 'Disable Debug Mode' : 'Enable Debug Mode' ?>
                        </button>
                        <button type="button" onclick="clearDebugLogs('debug_all')" class="bg-red-500 hover:bg-red-600 text-white font-medium px-6 py-2 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed" id="clear-debug-all-btn">
                            Clear All Logs
                        </button>
                    </div>
                    
                    <?php if ($debugEnabled): ?>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                <span class="text-green-800 font-medium">Debug mode is enabled</span>
                            </div>
                            <p class="text-green-700 text-sm mt-1">All API requests, responses, and errors are being logged daily.</p>
                        </div>
                    <?php else: ?>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                                <span class="text-gray-800 font-medium">Debug mode is disabled</span>
                            </div>
                            <p class="text-gray-600 text-sm mt-1">Enable debug mode to start logging plugin activity.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Log Statistics -->
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Log Statistics</h3>
                <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Log Days:</span>
                        <span class="font-medium"><?= $log_stats['total_days'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Entries:</span>
                        <span class="font-medium"><?= number_format($log_stats['total_entries']) ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Storage Size:</span>
                        <span class="font-medium"><?= size_format($log_stats['total_size']) ?></span>
                    </div>
                    <?php if (!empty($log_stats['type_counts'])): ?>
                        <div class="pt-2 border-t border-gray-200">
                            <span class="text-gray-600 text-sm">Entry Types:</span>
                            <div class="mt-1 space-y-1">
                                <?php foreach ($log_stats['type_counts'] as $type => $count): ?>
                                    <div class="flex justify-between text-sm">
                                        <span class="capitalize text-gray-600"><?= esc_html($type) ?>:</span>
                                        <span class="font-medium"><?= number_format($count) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($debugEnabled): ?>
    <!-- Log Settings -->
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

    <!-- Daily Logs -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Daily Debug Logs
        </h3>

        <?php if (empty($available_dates)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No debug logs found</h3>
                <p class="mt-2 text-gray-500">Enable debug mode to start logging plugin activity.</p>
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
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <h4 class="text-lg font-medium text-gray-900"><?= date('M j, Y', strtotime($date)) ?></h4>
                                    <div class="flex space-x-3 text-sm">
                                        <span class="text-gray-600"><?= $log_count ?> entries</span>
                                        <?php if ($error_count > 0): ?>
                                            <span class="text-red-600"><?= $error_count ?> errors</span>
                                        <?php endif; ?>
                                        <?php if ($warning_count > 0): ?>
                                            <span class="text-yellow-600"><?= $warning_count ?> warnings</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-2">
                                    <button onclick="toggleLogView('<?= $date ?>')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View Details
                                    </button>
                                    
                                    <button onclick="showEmailForm('<?= $date ?>')" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                        Email Log
                                    </button>
                                    
                                    <button type="button" onclick="clearDebugLogs('debug_single', '<?= $date ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed" id="clear-single-<?= $date ?>-btn">
                                        Clear
                                    </button>
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
                        <p class="text-gray-500">Showing latest 10 days. <?= count($available_dates) - 10 ?> more days available.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <!-- Debug Disabled State -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
        <div class="text-center py-12">
            <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-800 mb-3">Debug Mode is Disabled</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Debug mode is currently disabled. Enable it to start logging plugin activity, API requests, and troubleshooting information.
            </p>
            <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-lg mx-auto">
                <h4 class="font-medium text-gray-800 mb-2">What debug mode provides:</h4>
                <ul class="text-sm text-gray-600 space-y-1 text-left">
                    <li>• Detailed API request and response logging</li>
                    <li>• Error tracking and troubleshooting information</li>
                    <li>• Plugin activity monitoring</li>
                    <li>• Performance metrics and timing data</li>
                    <li>• Integration debugging for forms and webhooks</li>
                </ul>
            </div>
            <button type="button" onclick="toggleDebugMode()" id="toggle-debug-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-8 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                Enable Debug Mode
            </button>
        </div>
    </div>
    <?php endif; ?>
</div>

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
    document.getElementById('email-modal').classList.remove('hidden');
}

function hideEmailForm() {
    document.getElementById('email-modal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('email-modal').addEventListener('click', function(e) {
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
    container.insertBefore(messageDiv, container.firstChild);
    
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
document.getElementById('log_retention_days').addEventListener('input', function() {
    const value = parseInt(this.value);
    if (value < 1) {
        this.value = 1;
    } else if (value > 365) {
        this.value = 365;
    }
});

// Add email validation for notifications
document.getElementById('log_email_notifications').addEventListener('change', function() {
    const emailField = document.getElementById('log_notification_email');
    const emailLabel = emailField.previousElementSibling;
    
    if (this.checked) {
        emailField.required = true;
        emailLabel.classList.add('text-red-600');
        emailLabel.innerHTML = emailLabel.innerHTML.replace('Notification Email', 'Notification Email *');
    } else {
        emailField.required = false;
        emailLabel.classList.remove('text-red-600');
        emailLabel.innerHTML = emailLabel.innerHTML.replace('Notification Email *', 'Notification Email');
    }
});

// Initialize email field requirement state
document.addEventListener('DOMContentLoaded', function() {
    const emailNotifications = document.getElementById('log_email_notifications');
    if (emailNotifications && emailNotifications.checked) {
        emailNotifications.dispatchEvent(new Event('change'));
    }
});
 </script> 