<?php
/**
 * System Information Panel Component
 * Displays comprehensive system information with copy functionality
 */

// Helper function to get system info as an array
function ctm_get_system_info_array() {
    global $wpdb;
    return [
        'php_version' => PHP_VERSION,
        'wp_version' => get_bloginfo('version'),
        'memory_usage' => size_format(memory_get_usage(true)),
        'db_queries' => get_num_queries(),
        'wordpress_env' => [
            'version' => get_bloginfo('version'),
            'language' => get_locale(),
            'debug_mode' => WP_DEBUG ? 'Enabled' : 'Disabled',
            'memory_limit' => WP_MEMORY_LIMIT,
            'multisite' => is_multisite() ? 'Yes' : 'No',
            'timezone' => get_option('timezone_string') ?: 'UTC',
        ],
        'server_env' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'os' => PHP_OS,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_size' => ini_get('upload_max_filesize'),
        ],
        'database_info' => [
            'db_version' => $wpdb->db_version(),
            'db_host' => DB_HOST,
            'db_name' => DB_NAME,
            'table_prefix' => $wpdb->prefix,
            'db_charset' => DB_CHARSET,
            'current_queries' => get_num_queries(),
        ],
    ];
}

// Helper function to get system info as a report string
function ctm_get_system_info_report() {
    global $wpdb;
    $report = "=== SYSTEM INFORMATION REPORT ===\n";
    $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
    $report .= "Site: " . get_bloginfo('name') . "\n\n";
    $report .= "=== WORDPRESS ENVIRONMENT ===\n";
    $report .= "WordPress Version: " . get_bloginfo('version') . "\n";
    $report .= "Site URL: " . home_url() . "\n";
    $report .= "Admin URL: " . admin_url() . "\n";
    $report .= "WordPress Language: " . get_locale() . "\n";
    $report .= "WordPress Debug: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n";
    $report .= "WordPress Memory Limit: " . WP_MEMORY_LIMIT . "\n";
    $report .= "Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n\n";
    $report .= "=== SERVER ENVIRONMENT ===\n";
    $report .= "PHP Version: " . PHP_VERSION . "\n";
    $report .= "PHP SAPI: " . php_sapi_name() . "\n";
    $report .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
    $report .= "Operating System: " . PHP_OS . "\n";
    $report .= "Memory Limit: " . ini_get('memory_limit') . "\n";
    $report .= "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
    $report .= "Max Input Vars: " . ini_get('max_input_vars') . "\n";
    $report .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
    $report .= "Post Max Size: " . ini_get('post_max_size') . "\n";
    $report .= "Max File Uploads: " . ini_get('max_file_uploads') . "\n\n";
    $report .= "=== DATABASE ===\n";
    $report .= "Database Version: " . $wpdb->db_version() . "\n";
    $report .= "Database Host: " . DB_HOST . "\n";
    $report .= "Database Name: " . DB_NAME . "\n";
    $report .= "Database Charset: " . DB_CHARSET . "\n";
    $report .= "Table Prefix: " . $wpdb->prefix . "\n\n";
    $report .= "=== PHP EXTENSIONS ===\n";
    $report .= "cURL: " . (function_exists('curl_init') ? 'Available' : 'Missing') . "\n";
    $report .= "OpenSSL: " . (extension_loaded('openssl') ? 'Available' : 'Missing') . "\n";
    $report .= "mbstring: " . (extension_loaded('mbstring') ? 'Available' : 'Missing') . "\n";
    $report .= "GD Library: " . (extension_loaded('gd') ? 'Available' : 'Missing') . "\n";
    $report .= "XML: " . (extension_loaded('xml') ? 'Available' : 'Missing') . "\n";
    $report .= "JSON: " . (extension_loaded('json') ? 'Available' : 'Missing') . "\n";
    $report .= "ZIP: " . (extension_loaded('zip') ? 'Available' : 'Missing') . "\n\n";
    $report .= "=== CALLTRACKINGMETRICS PLUGIN ===\n";
    $report .= "Plugin Version: 2.0\n";
    $report .= "Debug Mode: " . (get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled') . "\n";
    $report .= "API Key Configured: " . (get_option('ctm_api_key') ? 'Yes' : 'No') . "\n";
    $report .= "CF7 Integration: " . (get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled') . "\n";
    $report .= "GF Integration: " . (get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled') . "\n\n";
    $report .= "=== THEME & PLUGINS ===\n";
    $report .= "Active Theme: " . wp_get_theme()->get('Name') . "\n";
    $report .= "Theme Version: " . wp_get_theme()->get('Version') . "\n";
    $report .= "Active Plugins: " . count(get_option('active_plugins', [])) . "\n";
    $report .= "Contact Form 7: " . (class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed') . "\n";
    $report .= "Gravity Forms: " . (class_exists('GFAPI') ? 'Installed' : 'Not Installed') . "\n\n";
    $report .= "=== CURRENT PERFORMANCE ===\n";
    $report .= "Memory Usage: " . size_format(memory_get_usage(true)) . "\n";
    $report .= "Peak Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
    $report .= "Database Queries: " . get_num_queries() . "\n";
    $report .= "Admin Email: " . get_option('admin_email') . "\n";
    $report .= "Timezone: " . (get_option('timezone_string') ?: 'UTC') . "\n\n";
    $report .= "=== END REPORT ===";
    return $report;
}

$system_info = ctm_get_system_info_array();
$system_info_report = ctm_get_system_info_report();
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <h3 class="text-2xl font-extrabold text-gray-900"><?php _e('System Information Panel', 'call-tracking-metrics'); ?></h3>
        </div>
        <div class="flex justify-center my-4">
            <button id="ctm-export-system-info" class="bg-gray-600 hover:bg-gray-700 !text-white font-medium px-6 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap" type="button">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span><?php _e('Export System Info', 'call-tracking-metrics'); ?></span>
            </button>
        </div>
    </div>
    
    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center" data-metric="php_version">
            <div class="text-2xl font-bold text-blue-600" data-field="php_version"><?= esc_html($system_info['php_version']) ?></div>
            <div class="text-xs text-blue-700"><?php _e('PHP Version', 'call-tracking-metrics'); ?></div>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center" data-metric="wp_version">
            <div class="text-2xl font-bold text-green-600" data-field="wp_version"><?= esc_html($system_info['wp_version']) ?></div>
            <div class="text-xs text-green-700"><?php _e('WordPress', 'call-tracking-metrics'); ?></div>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center" data-metric="memory_usage">
            <div class="text-2xl font-bold text-purple-600" data-field="memory_usage"><?= esc_html($system_info['memory_usage']) ?></div>
            <div class="text-xs text-purple-700"><?php _e('Memory Usage', 'call-tracking-metrics'); ?></div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-center" data-metric="db_queries">
            <div class="text-2xl font-bold text-orange-600" data-field="db_queries"><?= esc_html($system_info['db_queries']) ?></div>
            <div class="text-xs text-orange-700"><?php _e('DB Queries', 'call-tracking-metrics'); ?></div>
        </div>
    </div>

    <!-- Detailed System Information -->
    <div class="space-y-4">
        <!-- WordPress Environment -->
        <div class="border border-gray-200 rounded-lg" data-section="wordpress-env">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="flex items-center gap-1">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                    </svg>
                    <h4 class="text-lg font-bold text-gray-900"><?php _e('WordPress Environment', 'call-tracking-metrics'); ?></h4>
                </div>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Version:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="version"><?= esc_html($system_info['wordpress_env']['version']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Language:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="language"><?= esc_html($system_info['wordpress_env']['language']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Debug Mode:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= WP_DEBUG ? 'text-yellow-600' : 'text-green-600' ?>" data-field="debug_mode">
                        <?= esc_html($system_info['wordpress_env']['debug_mode']) ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="memory_limit"><?= esc_html($system_info['wordpress_env']['memory_limit']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Multisite:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="multisite"><?= esc_html($system_info['wordpress_env']['multisite']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Timezone:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="timezone"><?= esc_html($system_info['wordpress_env']['timezone']) ?></span>
                </div>
            </div>
        </div>

        <!-- Server Environment -->
        <div class="border border-gray-200 rounded-lg" data-section="server-env">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="flex items-center gap-1">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <h4 class="text-lg font-bold text-gray-900"><?php _e('Server Environment', 'call-tracking-metrics'); ?></h4>
                </div>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('PHP Version:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="php_version"><?= esc_html($system_info['server_env']['php_version']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Server Software:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium text-xs" data-field="server_software"><?= esc_html($system_info['server_env']['server_software']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Operating System:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="os"><?= esc_html($system_info['server_env']['os']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="memory_limit"><?= esc_html($system_info['server_env']['memory_limit']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Max Execution Time:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="max_execution_time"><?= esc_html($system_info['server_env']['max_execution_time']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Upload Max Size:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="upload_max_size"><?= esc_html($system_info['server_env']['upload_max_size']) ?></span>
                </div>
            </div>
        </div>

        <!-- Database Information -->
        <div class="border border-gray-200 rounded-lg" data-section="database-info">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="flex items-center gap-1">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    <h4 class="text-lg font-bold text-gray-900"><?php _e('Database Information', 'call-tracking-metrics'); ?></h4>
                </div>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Database Version:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="db_version"><?= esc_html($system_info['database_info']['db_version']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Database Host:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium text-xs" data-field="db_host"><?= esc_html($system_info['database_info']['db_host']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Database Name:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="db_name"><?= esc_html($system_info['database_info']['db_name']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Table Prefix:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="table_prefix"><?= esc_html($system_info['database_info']['table_prefix']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Database Charset:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="db_charset"><?= esc_html($system_info['database_info']['db_charset']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Current Queries:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium" data-field="current_queries"><?= esc_html($system_info['database_info']['current_queries']) ?></span>
                </div>
            </div>
        </div>

        <!-- PHP Extensions -->
        <div class="border border-gray-200 rounded-lg">
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="flex items-center gap-1">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h4 class="text-lg font-bold text-gray-900"><?php _e('PHP Extensions', 'call-tracking-metrics'); ?></h4>
                </div>
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
            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                <div class="flex items-center gap-1">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v3m0 0v8a2 2 0 002 2h10a2 2 0 002-2V8m0 0V5a1 1 0 00-1-1h-2M7 4h10"/>
                    </svg>
                    <h4 class="text-lg font-bold text-gray-900"><?php _e('CallTrackingMetrics Plugin', 'call-tracking-metrics'); ?></h4>
                </div>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Plugin Version:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium">2.0</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Debug Mode:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= get_option('ctm_debug_enabled') ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('API Key:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= get_option('ctm_api_key') ? 'text-green-600' : 'text-red-600' ?>">
                        <?= get_option('ctm_api_key') ? 'Configured' : 'Not Set' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('CF7 Integration:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= get_option('ctm_api_cf7_enabled') ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('GF Integration:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= get_option('ctm_api_gf_enabled') ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Contact Form 7:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= class_exists('WPCF7_ContactForm') ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Gravity Forms:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium <?= class_exists('GFAPI') ? 'text-green-600' : 'text-gray-600' ?>">
                        <?= class_exists('GFAPI') ? 'Installed' : 'Not Installed' ?>
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600"><?php _e('Active Plugins:', 'call-tracking-metrics'); ?></span>
                    <span class="font-medium"><?= count(get_option('active_plugins', [])) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 flex flex-wrap gap-3">
        <button onclick="copySystemInfo()" id="copy-system-btn" class="bg-blue-600 hover:bg-blue-700 !text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
            <?php _e('Copy to Clipboard', 'call-tracking-metrics'); ?>
        </button>
        <button onclick="emailSystemInfo()" class="bg-green-600 hover:bg-green-700 !text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <?php _e('Email System Info', 'call-tracking-metrics'); ?>
        </button>
        <button onclick="refreshSystemInfo()" id="refresh-system-btn" class="bg-gray-600 hover:bg-gray-700 !text-white font-medium px-4 py-2 rounded-lg transition duration-200 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <?php _e('Refresh System Info', 'call-tracking-metrics'); ?>
        </button>
    </div>
</div>

<!-- Email System Info Modal -->
<div id="email-system-modal" class="fixed inset-0 bg-black bg-opacity-75 hidden z-[999999] flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Email System Information</h3>
        <form id="email-system-form">
            <div class="mb-4">
                <label for="system_email_to" class="block text-sm font-medium text-gray-700 mb-2">Email To:</label>
                <input type="email" id="system_email_to" name="email_to" value="<?= esc_attr($notification_email ?? get_option('admin_email')) ?>" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
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
                <button type="button" onclick="hideEmailSystemForm()" class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </button>
                <button type="submit" id="send-system-email-btn" class="px-4 py-2 bg-green-600 !text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Send Email
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Use the PHP-generated report for JS
const SYSTEM_INFO_REPORT = <?= json_encode($system_info_report) ?>;

function updateSystemInfoDisplay(systemInfo) {
  // Update the metric cards
  for (const key of ['php_version', 'wp_version', 'memory_usage', 'db_queries']) {
    const el = document.querySelector(`[data-field="${key}"]`);
    if (el && systemInfo[key]) {
      el.textContent = systemInfo[key];
    }
  }
  // Update detailed sections
  if (systemInfo.wordpress_env) {
    updateSectionFields('wordpress-env', systemInfo.wordpress_env);
  }
  if (systemInfo.server_env) {
    updateSectionFields('server-env', systemInfo.server_env);
  }
  if (systemInfo.database_info) {
    updateSectionFields('database-info', systemInfo.database_info);
  }
}

function updateSectionFields(section, data) {
  const sectionEl = document.querySelector(`[data-section="${section}"]`);
  if (!sectionEl) return;
  for (const key in data) {
    const el = sectionEl.querySelector(`[data-field="${key}"]`);
    if (el) {
      el.textContent = data[key];
    }
  }
}

    function copySystemInfo() {
        const button = document.getElementById('copy-system-btn');
        const originalText = button.innerHTML;
        
        console.log('Copy button clicked'); // Debug log
        
        // Show loading state
        button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Copying...';
        button.disabled = true;
        
        const systemInfo = SYSTEM_INFO_REPORT;
        
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
                
                ctmShowToast('System information copied to clipboard!', 'success');
                
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
                    
                    ctmShowToast('System information copied to clipboard!', 'success');
                    
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
                
                ctmShowToast('Failed to copy to clipboard. Please copy the information manually from the display above.', 'error');
                
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
        console.log('[CTM] Email System Info button clicked');
        
        const modal = document.getElementById('email-system-modal');
        console.log('[CTM] Modal element found:', modal);
        
        if (modal) {
            console.log('[CTM] Modal classes before:', modal.className);
            modal.classList.remove('hidden');
            console.log('[CTM] Modal classes after:', modal.className);
            console.log('[CTM] Modal is now visible');
            
            // Test if modal is actually visible
            setTimeout(() => {
                const isVisible = !modal.classList.contains('hidden');
                console.log('[CTM] Modal visibility check:', isVisible);
                if (!isVisible) {
                    console.error('[CTM] Modal is still hidden after removing hidden class');
                    ctmShowToast('Modal visibility issue detected', 'error');
                }
            }, 100);
        } else {
            console.error('[CTM] Email system modal not found!');
            ctmShowToast('Email modal not found. Please refresh the page.', 'error');
            
            // Try to create a simple alert as fallback
            const email = prompt('Enter email address to send system info to:');
            if (email && email.includes('@')) {
                console.log('[CTM] Using fallback email prompt:', email);
                // You could add a simple AJAX call here as fallback
                ctmShowToast('Fallback email function not implemented yet', 'warning');
            }
        }
    }

    function hideEmailSystemForm() {
        console.log('[CTM] Hiding email system form');
        const modal = document.getElementById('email-system-modal');
        if (modal) {
            modal.classList.add('hidden');
            console.log('[CTM] Modal hidden successfully');
        } else {
            console.error('[CTM] Modal not found when trying to hide');
        }
    }

    // Attach email system info form handler after DOM is loaded

document.addEventListener('DOMContentLoaded', function() {
    // Auto-load system info on page load
    console.log('[CTM] Page loaded, auto-loading system info');
    refreshSystemInfo();
    
    const emailSystemForm = document.getElementById('email-system-form');
    if (emailSystemForm) {
        console.log('[CTM] Email system form found');
        emailSystemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('[CTM] Email System Info form submitted');

            const button = document.getElementById('send-system-email-btn');
            const originalText = button.textContent;

            button.disabled = true;
            button.textContent = 'Sending...';

            const emailTo = document.getElementById('system_email_to').value;
            const subject = document.getElementById('system_email_subject').value;
            const message = document.getElementById('system_email_message').value;

            console.log('[CTM] Email form data:', { emailTo, subject, message });

            const formData = new FormData();
            formData.append('action', 'ctm_email_system_info');
            formData.append('email_to', emailTo);
            formData.append('subject', subject);
            formData.append('message', message);
            formData.append('nonce', '<?= wp_create_nonce('ctm_email_system_info') ?>');

            console.log('[CTM] Sending AJAX request to:', '<?= admin_url('admin-ajax.php') ?>');

            fetch('<?= admin_url('admin-ajax.php') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('[CTM] Email response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('[CTM] Email AJAX response:', data);
                if (data.success) {
                    ctmShowToast('System information email sent successfully!', 'success');
                    hideEmailSystemForm();
                } else {
                    console.error('[CTM] Email failed:', data.data);
                    ctmShowToast('Failed to send email: ' + (data.data?.message || 'Unknown error'), 'error');
                }
            })
            .catch(error => {
                console.error('[CTM] Email AJAX error:', error);
                ctmShowToast('Network error while sending email: ' + error.message, 'error');
            })
            .finally(() => {
                button.disabled = false;
                button.textContent = originalText;
            });
        });
    }
});


function refreshSystemInfo() {
    const button = document.getElementById('refresh-system-btn');
    const originalText = button.textContent;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Refreshing...
    `;
    
    ctmShowToast('Refreshing system information...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_refresh_system_info');
    formData.append('nonce', '<?= wp_create_nonce('ctm_refresh_system_info') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update system info panels with fresh data
            if (data.data && data.data.system_info) {
                updateSystemInfoDisplay(data.data.system_info);
            }
            ctmShowToast('System information refreshed successfully', 'success');
        } else {
            const errorMessage = (data && data.data && data.data.message) || 
                                (data && data.message) || 
                                'Failed to refresh system information';
            ctmShowToast('Failed to refresh system info: ' + errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Error refreshing system info:', error);
        ctmShowToast('Error refreshing system information: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh System Info
        `;
    });
}

function showDebugMessage(message, type = 'info') {
  const container = document.getElementById('ctm-toast-container');
  if (!container) return;

  // Remove any existing toasts after a short delay
  Array.from(container.children).forEach(child => {
    child.style.opacity = 0;
    setTimeout(() => child.remove(), 500);
  });

  // Toast color based on type
  let bg = 'bg-blue-600';
  if (type === 'success') bg = 'bg-green-600';
  if (type === 'error') bg = 'bg-red-600';
  if (type === 'warning') bg = 'bg-yellow-600';

  // Create toast element
  const toast = document.createElement('div');
  toast.className = `${bg} !text-white px-4 py-2 rounded shadow mb-2 transition-opacity duration-500`;
  toast.style.opacity = 1;
  toast.textContent = message;

  container.appendChild(toast);

  // Fade out and remove after 3 seconds
  setTimeout(() => {
    toast.style.opacity = 0;
    setTimeout(() => toast.remove(), 500);
  }, 3000);
}

</script>
 