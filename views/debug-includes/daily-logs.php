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