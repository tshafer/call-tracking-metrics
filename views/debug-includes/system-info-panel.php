<?php
/**
 * System Information Panel Component
 * Displays comprehensive system information with copy functionality
 */

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

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
        </svg>
        System Information Panel
    </h3>
    
    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center" data-metric="php_version">
            <div class="text-2xl font-bold text-blue-600"><?= PHP_VERSION ?></div>
            <div class="text-xs text-blue-700">PHP Version</div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center" data-metric="wp_version">
            <div class="text-2xl font-bold text-green-600"><?= get_bloginfo('version') ?></div>
            <div class="text-xs text-green-700">WordPress</div>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center" data-metric="memory_usage">
            <div class="text-2xl font-bold text-purple-600"><?= size_format(memory_get_usage(true)) ?></div>
            <div class="text-xs text-purple-700">Memory Usage</div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center" data-metric="db_queries">
            <div class="text-2xl font-bold text-orange-600"><?= get_num_queries() ?></div>
            <div class="text-xs text-orange-700">DB Queries</div>
        </div>
    </div>

    <!-- Detailed System Information -->
    <div class="space-y-4">
        <!-- WordPress Environment -->
        <div class="border border-gray-200 rounded-lg" data-section="wordpress-env">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h4 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                    </svg>
                    WordPress Environment
                </h4>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Version:</span>
                    <span class="font-medium"><?= get_bloginfo('version') ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Language:</span>
                    <span class="font-medium"><?= get_locale() ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Debug Mode:</span>
                    <span class="font-medium <?= WP_DEBUG ? 'text-yellow-600' : 'text-green-600' ?>">
                        <?= WP_DEBUG ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Memory Limit:</span>
                    <span class="font-medium"><?= WP_MEMORY_LIMIT ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Multisite:</span>
                    <span class="font-medium"><?= is_multisite() ? 'Yes' : 'No' ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Timezone:</span>
                    <span class="font-medium"><?= get_option('timezone_string') ?: 'UTC' ?></span>
                </div>
            </div>
        </div>

        <!-- Server Environment -->
        <div class="border border-gray-200 rounded-lg" data-section="server-env">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h4 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    Server Environment
                </h4>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">PHP Version:</span>
                    <span class="font-medium"><?= PHP_VERSION ?></span>
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
                    <span class="text-gray-600">Upload Max Size:</span>
                    <span class="font-medium"><?= ini_get('upload_max_filesize') ?></span>
                </div>
            </div>
        </div>

        <!-- Database Information -->
        <div class="border border-gray-200 rounded-lg" data-section="database-info">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h4 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Database Information
                </h4>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
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
                    <span class="text-gray-600">Table Prefix:</span>
                    <span class="font-medium"><?= $GLOBALS['wpdb']->prefix ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Database Charset:</span>
                    <span class="font-medium"><?= DB_CHARSET ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Current Queries:</span>
                    <span class="font-medium"><?= get_num_queries() ?></span>
                </div>
            </div>
        </div>

        <!-- PHP Extensions -->
        <div class="border border-gray-200 rounded-lg">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h4 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    PHP Extensions
                </h4>
            </div>
            <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <?php
                $extensions = [
                    'cURL' => function_exists('curl_init'),
                    'OpenSSL' => extension_loaded('openssl'),
                    'mbstring' => extension_loaded('mbstring'),
                    'GD Library' => extension_loaded('gd'),
                    'XML' => extension_loaded('xml'),
                    'JSON' => extension_loaded('json'),
                    'ZIP' => extension_loaded('zip'),
                    'MySQLi' => extension_loaded('mysqli')
                ];
                
                foreach ($extensions as $name => $available): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-600"><?= $name ?>:</span>
                        <span class="font-medium <?= $available ? 'text-green-600' : 'text-red-600' ?>">
                            <?= $available ? '✓' : '✗' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Plugin Information -->
        <div class="border border-gray-200 rounded-lg">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                <h4 class="font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v3m0 0v8a2 2 0 002 2h10a2 2 0 002-2V8m0 0V5a1 1 0 00-1-1h-2M7 4h10"/>
                    </svg>
                    CallTrackingMetrics Plugin
                </h4>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
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
                    <span class="text-gray-600">API Key:</span>
                    <span class="font-medium <?= get_option('ctm_api_key') ? 'text-green-600' : 'text-red-600' ?>">
                        <?= get_option('ctm_api_key') ? 'Configured' : 'Not Set' ?>
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
                <div class="flex justify-between">
                    <span class="text-gray-600">Active Plugins:</span>
                    <span class="font-medium"><?= count(get_option('active_plugins', [])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 flex flex-wrap gap-3">
        <button onclick="copySystemInfo()" id="copy-system-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            Copy to Clipboard
        </button>
        <button onclick="emailSystemInfo()" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Email Report
        </button>
        <button onclick="refreshSystemInfo()" id="refresh-system-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh Data
        </button>
    </div>
</div>

 