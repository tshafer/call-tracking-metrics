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
    <!-- Advanced Debug Features -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        
        <!-- Feature 1: System Information Panel -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                </svg>
                System Information
            </h3>
            
            <!-- Scrollable System Info Container -->
            <div class="max-h-80 overflow-y-auto border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                <div class="space-y-3 text-sm">
                    <!-- WordPress Information -->
                    <div class="border-b border-gray-300 pb-2 mb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">WordPress Environment</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">WordPress Version:</span>
                                <span class="font-medium"><?= get_bloginfo('version') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Site URL:</span>
                                <span class="font-medium text-xs"><?= home_url() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Admin URL:</span>
                                <span class="font-medium text-xs"><?= admin_url() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">WordPress Language:</span>
                                <span class="font-medium"><?= get_locale() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">WordPress Debug:</span>
                                <span class="font-medium <?= WP_DEBUG ? 'text-yellow-600' : 'text-green-600' ?>"><?= WP_DEBUG ? 'Enabled' : 'Disabled' ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">WordPress Memory Limit:</span>
                                <span class="font-medium"><?= WP_MEMORY_LIMIT ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Multisite:</span>
                                <span class="font-medium"><?= is_multisite() ? 'Yes' : 'No' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Server Information -->
                    <div class="border-b border-gray-300 pb-2 mb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">Server Environment</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP Version:</span>
                                <span class="font-medium"><?= PHP_VERSION ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP SAPI:</span>
                                <span class="font-medium"><?= php_sapi_name() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Server Software:</span>
                                <span class="font-medium text-xs"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Operating System:</span>
                                <span class="font-medium"><?= PHP_OS ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory Limit:</span>
                                <span class="font-medium"><?= ini_get('memory_limit') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Max Execution Time:</span>
                                <span class="font-medium"><?= ini_get('max_execution_time') ?>s</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Max Input Vars:</span>
                                <span class="font-medium"><?= ini_get('max_input_vars') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Upload Max Size:</span>
                                <span class="font-medium"><?= ini_get('upload_max_filesize') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Post Max Size:</span>
                                <span class="font-medium"><?= ini_get('post_max_size') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Max File Uploads:</span>
                                <span class="font-medium"><?= ini_get('max_file_uploads') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Database Information -->
                    <div class="border-b border-gray-300 pb-2 mb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">Database</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Version:</span>
                                <span class="font-medium"><?= $GLOBALS['wpdb']->db_version() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Host:</span>
                                <span class="font-medium text-xs"><?= DB_HOST ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Name:</span>
                                <span class="font-medium"><?= DB_NAME ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Charset:</span>
                                <span class="font-medium"><?= DB_CHARSET ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Table Prefix:</span>
                                <span class="font-medium"><?= $GLOBALS['wpdb']->prefix ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Extensions & Features -->
                    <div class="border-b border-gray-300 pb-2 mb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">PHP Extensions</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">cURL:</span>
                                <span class="font-medium <?= function_exists('curl_init') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= function_exists('curl_init') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">OpenSSL:</span>
                                <span class="font-medium <?= extension_loaded('openssl') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= extension_loaded('openssl') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">mbstring:</span>
                                <span class="font-medium <?= extension_loaded('mbstring') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= extension_loaded('mbstring') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">GD Library:</span>
                                <span class="font-medium <?= extension_loaded('gd') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= extension_loaded('gd') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">XML:</span>
                                <span class="font-medium <?= extension_loaded('xml') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= extension_loaded('xml') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">JSON:</span>
                                <span class="font-medium <?= extension_loaded('json') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= extension_loaded('json') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">ZIP:</span>
                                <span class="font-medium <?= extension_loaded('zip') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= extension_loaded('zip') ? 'Available' : 'Missing' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Plugin Information -->
                    <div class="border-b border-gray-300 pb-2 mb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">CallTrackingMetrics Plugin</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Plugin Version:</span>
                                <span class="font-medium">2.0</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Debug Mode:</span>
                                <span class="font-medium <?= get_option('ctm_debug_enabled') ? 'text-green-600' : 'text-gray-600' ?>">
                                    <?= get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">API Key Configured:</span>
                                <span class="font-medium <?= get_option('ctm_api_key') ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= get_option('ctm_api_key') ? 'Yes' : 'No' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">CF7 Integration:</span>
                                <span class="font-medium <?= get_option('ctm_api_cf7_enabled') ? 'text-green-600' : 'text-gray-600' ?>">
                                    <?= get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">GF Integration:</span>
                                <span class="font-medium <?= get_option('ctm_api_gf_enabled') ? 'text-green-600' : 'text-gray-600' ?>">
                                    <?= get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Theme & Plugins -->
                    <div class="border-b border-gray-300 pb-2 mb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">Theme & Plugins</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Theme:</span>
                                <span class="font-medium"><?= wp_get_theme()->get('Name') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Theme Version:</span>
                                <span class="font-medium"><?= wp_get_theme()->get('Version') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Plugins:</span>
                                <span class="font-medium"><?= count(get_option('active_plugins', [])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Contact Form 7:</span>
                                <span class="font-medium <?= class_exists('WPCF7_ContactForm') ? 'text-green-600' : 'text-gray-600' ?>">
                                    <?= class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Gravity Forms:</span>
                                <span class="font-medium <?= class_exists('GFAPI') ? 'text-green-600' : 'text-gray-600' ?>">
                                    <?= class_exists('GFAPI') ? 'Installed' : 'Not Installed' ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">Current Performance</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory Usage:</span>
                                <span class="font-medium"><?= size_format(memory_get_usage(true)) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Peak Memory:</span>
                                <span class="font-medium"><?= size_format(memory_get_peak_usage(true)) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Queries:</span>
                                <span class="font-medium"><?= get_num_queries() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Admin Email:</span>
                                <span class="font-medium text-xs"><?= get_option('admin_email') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Timezone:</span>
                                <span class="font-medium"><?= get_option('timezone_string') ?: 'UTC' ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Report Generated:</span>
                                <span class="font-medium"><?= current_time('Y-m-d H:i:s') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button onclick="copySystemInfo()" id="copy-system-btn" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Copy 
                </button>
                
                <button onclick="emailSystemInfo()" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Email Report
                </button>
            </div>
        </div>

        <!-- Feature 2: API Request Simulator -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                API Request Simulator
            </h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Endpoint</label>
                    <select id="api-endpoint" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="/api/v1/accounts/">Account Info</option>
                        <option value="/api/v1/forms">Forms List</option>
                        <option value="/api/v1/tracking_numbers">Tracking Numbers</option>
                        <option value="/api/v1/calls">Recent Calls</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">HTTP Method</label>
                    <select id="api-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                    </select>
                </div>
                <button onclick="simulateApiRequest()" id="simulate-btn" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                    Send Test Request
                </button>
                <div id="api-response" class="hidden mt-4 p-3 bg-gray-50 rounded-lg border">
                    <div class="text-sm font-medium text-gray-700 mb-2">Response:</div>
                    <pre id="api-response-content" class="text-xs text-gray-600 overflow-x-auto"></pre>
                </div>
            </div>
        </div>

        <!-- Feature 3: Plugin Health Check -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
                Plugin Health Check
            </h3>
            <div id="health-results" class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm text-gray-600">Click "Run Health Check" to diagnose issues</span>
                </div>
            </div>
            <button onclick="runHealthCheck()" id="health-check-btn" class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                Run Health Check
            </button>
        </div>

        <!-- Feature 4: Performance Monitor -->
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
            <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                <svg class="w-6 h-6 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                Performance Monitor
            </h3>
            
            <!-- Performance Metrics Container -->
            <div id="performance-metrics" class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-4 mb-4 bg-gray-50">
                <div class="space-y-4 text-sm">
                    <!-- Memory & Processing -->
                    <div class="border-b border-gray-300 pb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">Memory & Processing</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Memory Usage:</span>
                                <span class="font-medium" id="current-memory"><?= size_format(memory_get_usage(true)) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Peak Memory Usage:</span>
                                <span class="font-medium" id="peak-memory"><?= size_format(memory_get_peak_usage(true)) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory Limit:</span>
                                <span class="font-medium"><?= ini_get('memory_limit') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Memory Usage %:</span>
                                <span class="font-medium" id="memory-percentage">
                                    <?php
                                    $memory_limit = ini_get('memory_limit');
                                    $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
                                    $current_usage = memory_get_usage(true);
                                    $percentage = $memory_limit_bytes > 0 ? round(($current_usage / $memory_limit_bytes) * 100, 1) : 0;
                                    echo $percentage . '%';
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP Max Execution Time:</span>
                                <span class="font-medium"><?= ini_get('max_execution_time') ?>s</span>
                            </div>
                        </div>
                    </div>

                    <!-- Database Performance -->
                    <div class="border-b border-gray-300 pb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">Database Performance</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Page Queries:</span>
                                <span class="font-medium" id="current-queries"><?= get_num_queries() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total DB Queries (Session):</span>
                                <span class="font-medium" id="total-queries">
                                    <?php
                                    global $wpdb;
                                    echo isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Query Time (Estimated):</span>
                                <span class="font-medium" id="query-time">
                                    <?php
                                    if (defined('SAVEQUERIES') && SAVEQUERIES && isset($wpdb->queries)) {
                                        $total_time = 0;
                                        foreach ($wpdb->queries as $query) {
                                            $total_time += $query[1];
                                        }
                                        echo round($total_time * 1000, 2) . 'ms';
                                    } else {
                                        echo 'N/A (Enable SAVEQUERIES)';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Database Version:</span>
                                <span class="font-medium"><?= $GLOBALS['wpdb']->db_version() ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Slow Query Threshold:</span>
                                <span class="font-medium"><?= defined('WP_DEBUG_LOG') && WP_DEBUG_LOG ? '2s' : 'N/A' ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Page Load Performance -->
                    <div class="border-b border-gray-300 pb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">Page Load Performance</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Page Generation Time:</span>
                                <span class="font-medium" id="page-load-time"><?= round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) ?>ms</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Server Response Time:</span>
                                <span class="font-medium" id="server-response">
                                    <?= isset($_SERVER['REQUEST_TIME_FLOAT']) ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) : 'N/A' ?>ms
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">PHP Version:</span>
                                <span class="font-medium"><?= PHP_VERSION ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Server Load:</span>
                                <span class="font-medium" id="server-load">
                                    <?php
                                    if (function_exists('sys_getloadavg')) {
                                        $load = sys_getloadavg();
                                        echo round($load[0], 2) . ' (1min)';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Opcache Status:</span>
                                <span class="font-medium">
                                    <?php
                                    if (function_exists('opcache_get_status')) {
                                        $opcache = opcache_get_status();
                                        echo $opcache && $opcache['opcache_enabled'] ? 'Enabled' : 'Disabled';
                                    } else {
                                        echo 'Not Available';
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- WordPress Performance -->
                    <div class="border-b border-gray-300 pb-3">
                        <h4 class="font-semibold text-gray-800 mb-2">WordPress Performance</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Plugins:</span>
                                <span class="font-medium"><?= count(get_option('active_plugins', [])) ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Active Theme:</span>
                                <span class="font-medium text-xs"><?= wp_get_theme()->get('Name') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Object Cache:</span>
                                <span class="font-medium <?= wp_using_ext_object_cache() ? 'text-green-600' : 'text-red-600' ?>">
                                    <?= wp_using_ext_object_cache() ? 'External' : 'Default' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">WP Debug Mode:</span>
                                <span class="font-medium <?= WP_DEBUG ? 'text-yellow-600' : 'text-green-600' ?>">
                                    <?= WP_DEBUG ? 'Enabled' : 'Disabled' ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">WordPress Version:</span>
                                <span class="font-medium"><?= get_bloginfo('version') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Cron Jobs:</span>
                                <span class="font-medium"><?= count(_get_cron_array()) ?> scheduled</span>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Metrics -->
                    <div>
                        <h4 class="font-semibold text-gray-800 mb-2">Real-time Metrics</h4>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Current Timestamp:</span>
                                <span class="font-medium" id="current-timestamp"><?= current_time('Y-m-d H:i:s') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Timezone:</span>
                                <span class="font-medium"><?= get_option('timezone_string') ?: 'UTC' ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Disk Space (Uploads):</span>
                                <span class="font-medium" id="disk-space">
                                    <?php
                                    $upload_dir = wp_upload_dir();
                                    if (function_exists('disk_free_space') && isset($upload_dir['basedir'])) {
                                        $free_bytes = disk_free_space($upload_dir['basedir']);
                                        echo $free_bytes ? size_format($free_bytes) . ' free' : 'N/A';
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Updated:</span>
                                <span class="font-medium text-blue-600" id="last-updated"><?= current_time('H:i:s') ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button onclick="refreshPerformance()" id="refresh-performance-btn" class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh Metrics
                </button>
                <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                    Auto: OFF
                </button>
            </div>
        </div>
    </div>

    <!-- Feature 5: Error Analyzer -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
            <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Error Analyzer & Troubleshooter
        </h3>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Common Issues</h4>
                <div class="space-y-2">
                    <button onclick="checkIssue('api_credentials')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border text-sm transition duration-200">
                        🔑 API Credentials Issues
                    </button>
                    <button onclick="checkIssue('form_integration')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border text-sm transition duration-200">
                        📝 Form Integration Problems
                    </button>
                    <button onclick="checkIssue('network_connectivity')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border text-sm transition duration-200">
                        🌐 Network Connectivity Issues
                    </button>
                    <button onclick="checkIssue('plugin_conflicts')" class="w-full text-left p-3 bg-gray-50 hover:bg-gray-100 rounded-lg border text-sm transition duration-200">
                        ⚠️ Plugin Conflicts
                    </button>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-700 mb-3">Analysis Results</h4>
                <div id="error-analysis" class="p-4 bg-gray-50 rounded-lg border min-h-[200px]">
                    <div class="text-center text-gray-500 mt-8">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-sm">Select an issue type to run analysis</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

// Advanced Debug Features JavaScript

<?php
// Generate system information for JavaScript
$system_info_report = "=== SYSTEM INFORMATION REPORT ===\n";
$system_info_report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
$system_info_report .= "Site: " . get_bloginfo('name') . "\n\n";

$system_info_report .= "=== WORDPRESS ENVIRONMENT ===\n";
$system_info_report .= "WordPress Version: " . get_bloginfo('version') . "\n";
$system_info_report .= "Site URL: " . home_url() . "\n";
$system_info_report .= "Admin URL: " . admin_url() . "\n";
$system_info_report .= "WordPress Language: " . get_locale() . "\n";
$system_info_report .= "WordPress Debug: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "WordPress Memory Limit: " . WP_MEMORY_LIMIT . "\n";
$system_info_report .= "Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n\n";

$system_info_report .= "=== SERVER ENVIRONMENT ===\n";
$system_info_report .= "PHP Version: " . PHP_VERSION . "\n";
$system_info_report .= "PHP SAPI: " . php_sapi_name() . "\n";
$system_info_report .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
$system_info_report .= "Operating System: " . PHP_OS . "\n";
$system_info_report .= "Memory Limit: " . ini_get('memory_limit') . "\n";
$system_info_report .= "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
$system_info_report .= "Max Input Vars: " . ini_get('max_input_vars') . "\n";
$system_info_report .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
$system_info_report .= "Post Max Size: " . ini_get('post_max_size') . "\n";
$system_info_report .= "Max File Uploads: " . ini_get('max_file_uploads') . "\n\n";

$system_info_report .= "=== DATABASE ===\n";
$system_info_report .= "Database Version: " . $GLOBALS['wpdb']->db_version() . "\n";
$system_info_report .= "Database Host: " . DB_HOST . "\n";
$system_info_report .= "Database Name: " . DB_NAME . "\n";
$system_info_report .= "Database Charset: " . DB_CHARSET . "\n";
$system_info_report .= "Table Prefix: " . $GLOBALS['wpdb']->prefix . "\n\n";

$system_info_report .= "=== PHP EXTENSIONS ===\n";
$system_info_report .= "cURL: " . (function_exists('curl_init') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "OpenSSL: " . (extension_loaded('openssl') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "mbstring: " . (extension_loaded('mbstring') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "GD Library: " . (extension_loaded('gd') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "XML: " . (extension_loaded('xml') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "JSON: " . (extension_loaded('json') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "ZIP: " . (extension_loaded('zip') ? 'Available' : 'Missing') . "\n\n";

$system_info_report .= "=== CALLTRACKINGMETRICS PLUGIN ===\n";
$system_info_report .= "Plugin Version: 2.0\n";
$system_info_report .= "Debug Mode: " . (get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "API Key Configured: " . (get_option('ctm_api_key') ? 'Yes' : 'No') . "\n";
$system_info_report .= "CF7 Integration: " . (get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "GF Integration: " . (get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled') . "\n\n";

$system_info_report .= "=== THEME & PLUGINS ===\n";
$system_info_report .= "Active Theme: " . wp_get_theme()->get('Name') . "\n";
$system_info_report .= "Theme Version: " . wp_get_theme()->get('Version') . "\n";
$system_info_report .= "Active Plugins: " . count(get_option('active_plugins', [])) . "\n";
$system_info_report .= "Contact Form 7: " . (class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed') . "\n";
$system_info_report .= "Gravity Forms: " . (class_exists('GFAPI') ? 'Installed' : 'Not Installed') . "\n\n";

$system_info_report .= "=== CURRENT PERFORMANCE ===\n";
$system_info_report .= "Memory Usage: " . size_format(memory_get_usage(true)) . "\n";
$system_info_report .= "Peak Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
$system_info_report .= "Database Queries: " . get_num_queries() . "\n";
$system_info_report .= "Admin Email: " . get_option('admin_email') . "\n";
$system_info_report .= "Timezone: " . (get_option('timezone_string') ?: 'UTC') . "\n\n";

$system_info_report .= "=== END REPORT ===";
?>

// Feature 1: System Information Panel
function copySystemInfo() {
    const button = document.getElementById('copy-system-btn');
    const originalText = button.innerHTML;
    
    console.log('Copy button clicked'); // Debug log
    
    // Show loading state
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Copying...';
    button.disabled = true;
    
    const systemInfo = <?= json_encode($system_info_report) ?>;
    
    console.log('System info length:', systemInfo ? systemInfo.length : 'undefined'); // Debug log
    
    // Check if clipboard API is available
    if (navigator.clipboard && window.isSecureContext) {
        console.log('Using modern clipboard API'); // Debug log
        navigator.clipboard.writeText(systemInfo).then(() => {
            console.log('Clipboard write successful'); // Debug log
            // Success state
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
            
            showDebugMessage('System information copied to clipboard!', 'success');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-600', 'hover:bg-green-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.disabled = false;
            }, 3000);
        }).catch((error) => {
            console.error('Clipboard API failed:', error); // Debug log
            fallbackCopyToClipboard();
        });
    } else {
        console.log('Using fallback clipboard method'); // Debug log
        fallbackCopyToClipboard();
    }
    
    function fallbackCopyToClipboard() {
        try {
            // Fallback for older browsers or non-secure contexts
            const textArea = document.createElement('textarea');
            textArea.value = systemInfo;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            if (successful) {
                console.log('Fallback copy successful'); // Debug log
                // Success state
                button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
                
                showDebugMessage('System information copied to clipboard!', 'success');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600', 'hover:bg-green-700');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    button.disabled = false;
                }, 3000);
            } else {
                throw new Error('execCommand copy failed');
            }
        } catch (error) {
            console.error('Fallback copy failed:', error); // Debug log
            // Show error state
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>Failed';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-red-600', 'hover:bg-red-700');
            
            showDebugMessage('Failed to copy to clipboard. Please copy the information manually from the display above.', 'error');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.disabled = false;
            }, 3000);
        }
    }
}


// Email System Information
function emailSystemInfo() {
    document.getElementById('email-system-modal').classList.remove('hidden');
}

function hideEmailSystemForm() {
    document.getElementById('email-system-modal').classList.add('hidden');
}

// Handle email system info form submission
document.getElementById('email-system-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const button = document.getElementById('send-system-email-btn');
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Sending...';
    
    const formData = new FormData();
    formData.append('action', 'ctm_email_system_info');
    formData.append('email_to', document.getElementById('system_email_to').value);
    formData.append('subject', document.getElementById('system_email_subject').value);
    formData.append('message', document.getElementById('system_email_message').value);
    formData.append('nonce', '<?= wp_create_nonce('ctm_email_system_info') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage('System information email sent successfully!', 'success');
            hideEmailSystemForm();
        } else {
            showDebugMessage('Failed to send email: ' + (data.data?.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error while sending email', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
});

// Close system email modal when clicking outside
document.getElementById('email-system-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideEmailSystemForm();
    }
});

// Feature 2: API Request Simulator
function simulateApiRequest() {
    const endpoint = document.getElementById('api-endpoint').value;
    const method = document.getElementById('api-method').value;
    const button = document.getElementById('simulate-btn');
    const responseDiv = document.getElementById('api-response');
    const responseContent = document.getElementById('api-response-content');
    
    button.disabled = true;
    button.textContent = 'Sending...';
    responseDiv.classList.add('hidden');
    
    const formData = new FormData();
    formData.append('action', 'ctm_simulate_api_request');
    formData.append('endpoint', endpoint);
    formData.append('method', method);
    formData.append('nonce', '<?= wp_create_nonce('ctm_simulate_api_request') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        responseDiv.classList.remove('hidden');
        responseContent.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            showDebugMessage('API request completed successfully', 'success');
        } else {
            showDebugMessage('API request failed: ' + (data.data?.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        responseDiv.classList.remove('hidden');
        responseContent.textContent = 'Error: ' + error.message;
        showDebugMessage('Network error during API simulation', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Send Test Request';
    });
}

// Feature 3: Plugin Health Check
function runHealthCheck() {
    const button = document.getElementById('health-check-btn');
    const resultsDiv = document.getElementById('health-results');
    
    button.disabled = true;
    button.textContent = 'Running Checks...';
    
    resultsDiv.innerHTML = '<div class="text-center py-4"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div></div>';
    
    const formData = new FormData();
    formData.append('action', 'ctm_health_check');
    formData.append('nonce', '<?= wp_create_nonce('ctm_health_check') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const checks = data.data.checks;
            let html = '';
            
            checks.forEach(check => {
                const statusColor = check.status === 'pass' ? 'text-green-600' : 
                                  check.status === 'warning' ? 'text-yellow-600' : 'text-red-600';
                const icon = check.status === 'pass' ? '✓' : 
                           check.status === 'warning' ? '⚠' : '✗';
                
                html += `
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-700">${check.name}</span>
                        <span class="${statusColor} font-medium">${icon} ${check.message}</span>
                    </div>
                `;
            });
            
            resultsDiv.innerHTML = html;
            
            const failedChecks = checks.filter(c => c.status === 'fail').length;
            const warningChecks = checks.filter(c => c.status === 'warning').length;
            
            if (failedChecks === 0 && warningChecks === 0) {
                showDebugMessage('All health checks passed!', 'success');
            } else if (failedChecks > 0) {
                showDebugMessage(`Health check completed with ${failedChecks} failures and ${warningChecks} warnings`, 'error');
            } else {
                showDebugMessage(`Health check completed with ${warningChecks} warnings`, 'warning');
            }
        } else {
            resultsDiv.innerHTML = '<div class="text-center text-red-600 py-4">Health check failed</div>';
            showDebugMessage('Health check failed to run', 'error');
        }
    })
    .catch(error => {
        resultsDiv.innerHTML = '<div class="text-center text-red-600 py-4">Network error</div>';
        showDebugMessage('Network error during health check', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Run Health Check';
    });
}

// Feature 4: Performance Monitor
let pageLoadStart = performance.now();
let autoRefreshInterval = null;
let autoRefreshEnabled = false;

function refreshPerformance() {
    const button = document.getElementById('refresh-performance-btn');
    const originalText = button ? button.innerHTML : '';
    
    // Show loading state
    if (button) {
        button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Refreshing...';
        button.disabled = true;
    }
    
    const currentTime = performance.now();
    const loadTime = Math.round(currentTime - pageLoadStart);
    
    // Update page load time if element exists
    const loadTimeElement = document.getElementById('page-load-time');
    if (loadTimeElement) {
        loadTimeElement.textContent = loadTime + 'ms';
    }
    
    // Fetch fresh performance data via AJAX
    const formData = new FormData();
    formData.append('action', 'ctm_get_performance_metrics');
    formData.append('nonce', '<?= wp_create_nonce('ctm_get_performance_metrics') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const metrics = data.data;
            
            // Update all performance metrics if elements exist
            const updates = {
                'current-memory': metrics.current_memory,
                'peak-memory': metrics.peak_memory,
                'memory-percentage': metrics.memory_percentage,
                'current-queries': metrics.current_queries,
                'total-queries': metrics.total_queries,
                'query-time': metrics.query_time,
                'page-load-time': metrics.page_load_time,
                'server-response': metrics.server_response,
                'server-load': metrics.server_load,
                'current-timestamp': metrics.current_timestamp,
                'disk-space': metrics.disk_space
            };
            
            Object.entries(updates).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element && value !== undefined) {
                    element.textContent = value;
                }
            });
            
            // Update last updated time
            const lastUpdated = document.getElementById('last-updated');
            if (lastUpdated) {
                lastUpdated.textContent = new Date().toLocaleTimeString();
            }
            
            if (!autoRefreshEnabled) {
                showDebugMessage('Performance metrics refreshed successfully!', 'success');
            }
        } else {
            if (!autoRefreshEnabled) {
                showDebugMessage('Failed to refresh performance metrics', 'error');
            }
        }
    })
    .catch(error => {
        console.error('Error fetching performance metrics:', error);
        if (!autoRefreshEnabled) {
            showDebugMessage('Network error while refreshing performance metrics', 'error');
        }
    })
    .finally(() => {
        // Reset button
        if (button) {
            button.innerHTML = originalText;
            button.disabled = false;
        }
    });
}

function toggleAutoRefresh() {
    const button = document.getElementById('auto-refresh-btn');
    
    if (!button) return;
    
    if (autoRefreshEnabled) {
        // Disable auto-refresh
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
        autoRefreshEnabled = false;
        button.textContent = 'Auto: OFF';
        button.classList.remove('bg-green-600', 'hover:bg-green-700');
        button.classList.add('bg-gray-600', 'hover:bg-gray-700');
        showDebugMessage('Auto-refresh disabled', 'info');
    } else {
        // Enable auto-refresh
        autoRefreshInterval = setInterval(refreshPerformance, 30000); // Refresh every 30 seconds
        autoRefreshEnabled = true;
        button.textContent = 'Auto: ON';
        button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        button.classList.add('bg-green-600', 'hover:bg-green-700');
        showDebugMessage('Auto-refresh enabled (30s intervals)', 'success');
    }
}

// Feature 5: Error Analyzer
function checkIssue(issueType) {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-sm text-gray-600">Analyzing...</p></div>';
    
    const formData = new FormData();
    formData.append('action', 'ctm_analyze_issue');
    formData.append('issue_type', issueType);
    formData.append('nonce', '<?= wp_create_nonce('ctm_analyze_issue') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const analysis = data.data.analysis;
            let html = `
                <div class="mb-4">
                    <h5 class="font-semibold text-gray-800 mb-2">${analysis.title}</h5>
                    <p class="text-sm text-gray-600 mb-3">${analysis.description}</p>
                </div>
            `;
            
            if (analysis.issues && analysis.issues.length > 0) {
                html += '<div class="mb-4"><h6 class="font-medium text-red-600 mb-2">Issues Found:</h6><ul class="space-y-1">';
                analysis.issues.forEach(issue => {
                    html += `<li class="text-sm text-red-700">• ${issue}</li>`;
                });
                html += '</ul></div>';
            }
            
            if (analysis.solutions && analysis.solutions.length > 0) {
                html += '<div class="mb-4"><h6 class="font-medium text-green-600 mb-2">Recommended Solutions:</h6><ul class="space-y-1">';
                analysis.solutions.forEach(solution => {
                    html += `<li class="text-sm text-green-700">• ${solution}</li>`;
                });
                html += '</ul></div>';
            }
            
            if (analysis.status === 'healthy') {
                html += '<div class="p-3 bg-green-50 border border-green-200 rounded-lg"><span class="text-green-800 font-medium">✓ No issues detected</span></div>';
            }
            
            analysisDiv.innerHTML = html;
            showDebugMessage('Issue analysis completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="text-center text-red-600 py-4">Analysis failed</div>';
            showDebugMessage('Issue analysis failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="text-center text-red-600 py-4">Network error</div>';
        showDebugMessage('Network error during analysis', 'error');
    });
}

// Initialize performance monitoring on page load
document.addEventListener('DOMContentLoaded', function() {
    pageLoadStart = performance.now();
    setTimeout(refreshPerformance, 1000);
});
 </script> 