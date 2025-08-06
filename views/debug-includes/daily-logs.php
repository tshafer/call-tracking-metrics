<?php
/**
 * Daily Logs Component
 * Displays daily debug logs with filtering, viewing, and management options
 * OPTIMIZED for performance with lazy loading and pagination
 */

// Ensure variables are available from parent context
$available_dates = $available_dates ?? [];
$log_stats = $log_stats ?? [];
?>

<div class="space-y-8">
    <!-- Daily Logs Overview Section -->
    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-6 rounded-lg border border-teal-200">
        <div class="flex items-center mb-4">
            <div class="bg-teal-100 p-2 rounded-lg mr-3">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-teal-700"><?php _e('Daily Logs', 'call-tracking-metrics'); ?></h3>
        </div>
        <p class="mb-4"><?php _e('View, filter, and manage daily debug logs. Each day\'s log shows all plugin activity, errors, warnings, and more. You can email logs, clear them, or view detailed context for each entry.', 'call-tracking-metrics'); ?></p>
        <ul class="list-disc pl-6 mb-4 space-y-2">
            <li class="flex items-start">
                <span class="mr-2">📅</span>
                <?php _e('Browse logs by date', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">📊</span>
                <?php _e('See error, warning, and info counts', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">🔍</span>
                <?php _e('View detailed log entries and context', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">📧</span>
                <?php _e('Email logs for support or archiving', 'call-tracking-metrics'); ?>
            </li>
            <li class="flex items-start">
                <span class="mr-2">🗑️</span>
                <?php _e('Clear logs for specific days', 'call-tracking-metrics'); ?>
            </li>
        </ul>
        
        <!-- Performance Stats -->
        <?php if (!empty($log_stats) && isset($log_stats['total_available_days'])): ?>
            <div class="bg-white bg-opacity-50 rounded-lg p-3 mt-4">
                <p class="text-sm text-teal-700">
                    <strong><?php _e('Performance Note:', 'call-tracking-metrics'); ?></strong> 
                    <?php printf(__('Showing latest 5 days of %d total available days. Use "Load More" to view older logs.', 'call-tracking-metrics'), $log_stats['total_available_days']); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Daily Logs Content Section -->
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
            <div class="space-y-4" id="logs-container">
                <?php foreach ($available_dates as $date): ?>
                    <?php
                    $logs = get_option("ctm_daily_log_{$date}", []);
                    if (empty($logs)) continue;
                    
                    // OPTIMIZATION: Only count first 50 entries for performance
                    $sample_logs = array_slice($logs, 0, 50);
                    $error_count = 0;
                    $warning_count = 0;
                    $info_count = 0;
                    $debug_count = 0;
                    
                    foreach ($sample_logs as $entry) {
                        switch ($entry['type']) {
                            case 'error':
                                $error_count++;
                                break;
                            case 'warning':
                                $warning_count++;
                                break;
                            case 'info':
                                $info_count++;
                                break;
                            case 'debug':
                                $debug_count++;
                                break;
                        }
                    }
                    
                    // Show total count if we sampled
                    $total_count = count($logs);
                    $sampled_count = count($sample_logs);
                    $count_display = $sampled_count < $total_count ? "{$sampled_count}+ of {$total_count}" : $total_count;
                    ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-4">
                                <h4 class="text-lg font-semibold text-gray-900"><?= esc_html($date) ?></h4>
                                <div class="flex space-x-2 text-sm">
                                    <?php if ($error_count > 0): ?>
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded"><?= $error_count ?> errors</span>
                                    <?php endif; ?>
                                    <?php if ($warning_count > 0): ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded"><?= $warning_count ?> warnings</span>
                                    <?php endif; ?>
                                    <?php if ($info_count > 0): ?>
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded"><?= $info_count ?> info</span>
                                    <?php endif; ?>
                                    <?php if ($debug_count > 0): ?>
                                        <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded"><?= $debug_count ?> debug</span>
                                    <?php endif; ?>
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded"><?= $count_display ?> total</span>
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                <button onclick="toggleLogView('<?= $date ?>')" class="text-blue-600 hover:text-blue-800 text-sm font-medium"><?php _e('View Details', 'call-tracking-metrics'); ?></button>
                                
                                <button onclick="showEmailForm('<?= $date ?>')" class="text-green-600 hover:text-green-800 text-sm font-medium"><?php _e('Email Log', 'call-tracking-metrics'); ?></button>
                                
                                <button type="button" onclick="clearDebugLogs('debug_single', '<?= $date ?>')" class="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed" id="clear-single-<?= $date ?>-btn"><?php _e('Clear', 'call-tracking-metrics'); ?></button>
                            </div>
                        </div>
                        
                        <div id="log-<?= $date ?>" class="hidden px-6 py-4 max-h-96 overflow-y-auto">
                            <div class="space-y-3">
                                <?php 
                                // OPTIMIZATION: Only show first 20 entries initially, with "Load More" button
                                $display_logs = array_slice(array_reverse($logs), 0, 20);
                                $has_more = count($logs) > 20;
                                ?>
                                <?php foreach ($display_logs as $entry): ?>
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
                                
                                <?php if ($has_more): ?>
                                    <div class="text-center pt-4">
                                        <button onclick="loadMoreLogs('<?= $date ?>', <?= count($display_logs) ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded text-sm">
                                            <?php _e('Load More Entries', 'call-tracking-metrics'); ?>
                                        </button>
                                        <p class="text-xs text-gray-500 mt-1"><?php printf(__('Showing %d of %d entries', 'call-tracking-metrics'), count($display_logs), count($logs)); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Load More Days Button -->
            <?php if (isset($log_stats['total_available_days']) && $log_stats['total_available_days'] > count($available_dates)): ?>
                <div class="text-center py-6">
                    <button onclick="loadMoreDays()" id="load-more-days-btn" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium">
                        <?php _e('Load More Days', 'call-tracking-metrics'); ?>
                    </button>
                    <p class="text-sm text-gray-500 mt-2">
                        <?php printf(__('Showing %d of %d available days', 'call-tracking-metrics'), count($available_dates), $log_stats['total_available_days']); ?>
                    </p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
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
                <button type="button" onclick="hideEmailForm()" class="bg-gray-600 hover:bg-gray-700 !text-white px-4 py-2 rounded flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <?php _e('Cancel', 'call-tracking-metrics'); ?>
                </button>
                <button type="submit" id="email-log-send-btn" class="bg-blue-600 hover:bg-blue-700 !text-white px-4 py-2 rounded flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <?php _e('Send', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentLoadedDays = <?= count($available_dates) ?>;
    let totalAvailableDays = <?= isset($log_stats['total_available_days']) ? $log_stats['total_available_days'] : 0 ?>;
    
    function toggleLogView(date) {
        const logDiv = document.getElementById('log-' + date);
        if (logDiv.classList.contains('hidden')) {
            logDiv.classList.remove('hidden');
        } else {
            logDiv.classList.add('hidden');
        }
    }

    function loadMoreLogs(date, currentCount) {
        // AJAX call to load more log entries for a specific date
        const formData = new FormData();
        formData.append('action', 'ctm_load_more_logs');
        formData.append('nonce', '<?= wp_create_nonce('ctm_load_more_logs') ?>');
        formData.append('date', date);
        formData.append('offset', currentCount);
        formData.append('limit', 20);

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const logContainer = document.querySelector(`#log-${date} .space-y-3`);
                const loadMoreBtn = logContainer.querySelector('button[onclick*="loadMoreLogs"]');
                
                // Add new entries
                data.data.entries.forEach(entry => {
                    const entryHtml = createLogEntryHtml(entry);
                    logContainer.insertBefore(entryHtml, loadMoreBtn);
                });
                
                // Update or remove load more button
                if (data.data.has_more) {
                    loadMoreBtn.onclick = () => loadMoreLogs(date, currentCount + data.data.entries.length);
                    loadMoreBtn.nextElementSibling.textContent = `Showing ${currentCount + data.data.entries.length} of ${data.data.total} entries`;
                } else {
                    loadMoreBtn.remove();
                    loadMoreBtn.nextElementSibling.remove();
                }
            } else {
                ctmShowToast('Failed to load more entries', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading more logs:', error);
            ctmShowToast('Network error while loading logs', 'error');
        });
    }

    function loadMoreDays() {
        const formData = new FormData();
        formData.append('action', 'ctm_load_more_days');
        formData.append('nonce', '<?= wp_create_nonce('ctm_load_more_days') ?>');
        formData.append('offset', currentLoadedDays);
        formData.append('limit', 5);

        const btn = document.getElementById('load-more-days-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Loading...';

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const container = document.getElementById('logs-container');
                
                // Add new day logs
                data.data.days.forEach(dayHtml => {
                    container.insertAdjacentHTML('beforeend', dayHtml);
                });
                
                currentLoadedDays += data.data.days.length;
                
                // Update or remove load more button
                if (currentLoadedDays >= totalAvailableDays) {
                    btn.remove();
                    btn.nextElementSibling.remove();
                } else {
                    btn.nextElementSibling.textContent = `Showing ${currentLoadedDays} of ${totalAvailableDays} available days`;
                }
            } else {
                ctmShowToast('Failed to load more days', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading more days:', error);
            ctmShowToast('Network error while loading days', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }

    function createLogEntryHtml(entry) {
        const typeColors = {
            'error': 'text-red-800 bg-red-100',
            'warning': 'text-yellow-800 bg-yellow-100',
            'info': 'text-blue-800 bg-blue-100',
            'debug': 'text-gray-800 bg-gray-100',
            'api': 'text-purple-800 bg-purple-100',
            'config': 'text-indigo-800 bg-indigo-100',
            'system': 'text-green-800 bg-green-100'
        };
        const colorClass = typeColors[entry.type] || 'text-gray-800 bg-gray-100';
        
        let contextHtml = '';
        if (entry.context && Object.keys(entry.context).length > 0) {
            contextHtml = `
                <details class="mt-2">
                    <summary class="text-xs text-gray-500 cursor-pointer hover:text-gray-700">Context Details</summary>
                    <pre class="mt-1 text-xs text-gray-600 bg-gray-50 p-2 rounded overflow-x-auto">${JSON.stringify(entry.context, null, 2)}</pre>
                </details>
            `;
        }
        
        return `
            <div class="border-l-4 border-gray-200 pl-4 py-2">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-1">
                            <span class="${colorClass} px-2 py-1 text-xs font-medium rounded">${entry.type.toUpperCase()}</span>
                            <span class="text-sm text-gray-500">${entry.timestamp}</span>
                        </div>
                        <p class="text-gray-900 text-sm">${entry.message}</p>
                        ${contextHtml}
                    </div>
                </div>
            </div>
        `;
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
        console.log('Email form submitted');
        
        const btn = document.getElementById('email-log-send-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Sending...';

        const date = document.getElementById('email-log-date').value;
        const email_to = document.getElementById('email-log-to').value;

        console.log('Email form data:', { date, email_to });

        // Validate inputs
        if (!date) {
            ctmShowToast('No log date provided', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
            return;
        }

        if (!email_to || !email_to.includes('@')) {
            ctmShowToast('Please enter a valid email address', 'error');
            btn.disabled = false;
            btn.textContent = originalText;
            return;
        }

        const formData = new FormData();
        formData.append('action', 'ctm_email_daily_log');
        formData.append('nonce', '<?= wp_create_nonce('ctm_email_daily_log') ?>');
        formData.append('log_date', date);
        formData.append('email_to', email_to);

        console.log('Sending email request:', {
            action: 'ctm_email_daily_log',
            log_date: date,
            email_to: email_to,
            nonce: '<?= wp_create_nonce('ctm_email_daily_log') ?>'
        });

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            console.log('Email response status:', response.status);
            console.log('Email response headers:', response.headers);
            return response.json();
        })
        .then(data => {
            console.log('Email response data:', data);
            if (data.success) {
                ctmShowToast(data.data.message, 'success');
                hideEmailForm();
            } else {
                console.error('Email failed:', data.data);
                ctmShowToast(data.data.message || 'Failed to email log', 'error');
            }
        })
        .catch((error) => {
            console.error('Email error:', error);
            ctmShowToast('Network error while emailing log: ' + error.message, 'error');
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
                ctmShowToast(data.data.message, 'success');
                
                // Remove the specific log container
                const logContainer = button.closest('.border.border-gray-200.rounded-lg.p-4.hover\\:shadow-md.transition-shadow');
                if (logContainer) {
                    logContainer.style.transition = 'opacity 0.5s ease';
                    logContainer.style.opacity = '0';
                    setTimeout(() => {
                        logContainer.remove();
                        
                        // Check if there are any remaining logs
                        const remainingLogs = document.querySelectorAll('.border.border-gray-200.rounded-lg.p-4.hover\\:shadow-md.transition-shadow');
                        if (remainingLogs.length === 0) {
                            // Show "no logs" message
                            const container = document.querySelector('.space-y-4');
                            if (container) {
                                container.innerHTML = `
                                    <div class="text-center py-12">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <h3 class="mt-4 text-lg font-medium text-gray-900"><?php _e('No debug logs found', 'call-tracking-metrics'); ?></h3>
                                        <p class="mt-2 text-gray-500"><?php _e('Enable debug mode to start logging plugin activity.', 'call-tracking-metrics'); ?></p>
                                    </div>
                                `;
                            }
                        }
                    }, 500);
                }
            } else {
                ctmShowToast(data.data.message || 'Failed to clear logs', 'error');
            }
        })
        .catch(() => {
            ctmShowToast('Network error occurred while clearing logs', 'error');
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