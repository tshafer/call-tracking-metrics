<?php
namespace CTM\Admin;

/**
 * Handles all AJAX requests for the CTM plugin
 */
class AjaxHandlers
{
    private LoggingSystem $loggingSystem;
    private SettingsRenderer $renderer;

    public function __construct()
    {
        $this->loggingSystem = new LoggingSystem();
        $this->renderer = new SettingsRenderer();
    }

    /**
     * Register all AJAX handlers
     */
    public function registerHandlers(): void
    {
        add_action('wp_ajax_ctm_get_forms', [$this, 'ajaxGetForms']);
        add_action('wp_ajax_ctm_get_fields', [$this, 'ajaxGetFields']);
        add_action('wp_ajax_ctm_save_mapping', [$this, 'ajaxSaveMapping']);
        add_action('wp_ajax_ctm_dismiss_notice', [$this, 'ajaxDismissNotice']);
        add_action('wp_ajax_ctm_test_api_connection', [$this, 'ajaxTestApiConnection']);
        add_action('wp_ajax_ctm_clear_logs', [$this, 'ajaxClearLogs']);
        add_action('wp_ajax_ctm_update_log_settings', [$this, 'ajaxUpdateLogSettings']);
        add_action('wp_ajax_ctm_toggle_debug_mode', [$this, 'ajaxToggleDebugMode']);
        
        // Advanced Debug Features
        add_action('wp_ajax_ctm_simulate_api_request', [$this, 'ajaxSimulateApiRequest']);
        add_action('wp_ajax_ctm_health_check', [$this, 'ajaxHealthCheck']);
        add_action('wp_ajax_ctm_get_performance_metrics', [$this, 'ajaxGetPerformanceMetrics']);
        add_action('wp_ajax_ctm_analyze_issue', [$this, 'ajaxAnalyzeIssue']);
        add_action('wp_ajax_ctm_email_system_info', [$this, 'ajaxEmailSystemInfo']);
        add_action('wp_ajax_ctm_refresh_system_info', [$this, 'ajaxRefreshSystemInfo']);
        add_action('wp_ajax_ctm_auto_fix_issues', [$this, 'ajaxAutoFixIssues']);
        add_action('wp_ajax_ctm_full_diagnostic', [$this, 'ajaxFullDiagnostic']);
        add_action('wp_ajax_ctm_security_scan', [$this, 'ajaxSecurityScan']);
        add_action('wp_ajax_ctm_performance_analysis', [$this, 'ajaxPerformanceAnalysis']);
    }

    /**
     * AJAX: Return available forms for GF or CF7.
     */
    public function ajaxGetForms(): void
    {
        check_ajax_referer('ctm_mapping_nonce', 'nonce');
        $type = sanitize_text_field($_POST['form_type'] ?? '');
        $forms = [];
        
        if ($type === 'gf' && class_exists('GFAPI')) {
            $gf_forms = \GFAPI::get_forms();
            foreach ($gf_forms as $form) {
                $forms[] = ['id' => $form['id'], 'title' => $form['title']];
            }
        } elseif ($type === 'cf7' && class_exists('WPCF7_ContactForm')) {
            $cf7_forms = \WPCF7_ContactForm::find();
            foreach ($cf7_forms as $form) {
                $forms[] = ['id' => $form->id(), 'title' => $form->title()];
            }
        }
        
        wp_send_json_success($forms);
    }

    /**
     * AJAX: Return available fields for a given form.
     */
    public function ajaxGetFields(): void
    {
        check_ajax_referer('ctm_mapping_nonce', 'nonce');
        $type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = sanitize_text_field($_POST['form_id'] ?? '');
        $fields = [];
        
        if ($type === 'gf' && class_exists('GFAPI')) {
            $form = \GFAPI::get_form($form_id);
            if ($form && isset($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    $fields[] = ['id' => $field['id'], 'label' => $field['label'] ?? $field['type']];
                }
            }
        } elseif ($type === 'cf7' && class_exists('WPCF7_ContactForm')) {
            $form = \WPCF7_ContactForm::get_instance($form_id);
            if ($form && method_exists($form, 'scan_form_tags')) {
                foreach ($form->scan_form_tags() as $tag) {
                    $fields[] = ['id' => $tag->name, 'label' => $tag->name];
                }
            }
        }
        
        wp_send_json_success($fields);
    }

    /**
     * AJAX: Save mapping
     */
    public function ajaxSaveMapping(): void
    {
        check_ajax_referer('ctm_mapping_nonce', 'nonce');
        $type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = sanitize_text_field($_POST['form_id'] ?? '');
        $mapping = $_POST['mapping'] ?? [];
        
        if ($type && $form_id && is_array($mapping)) {
            $fieldMapping = new FieldMapping();
            $fieldMapping->saveFieldMapping($type, $form_id, $mapping);
            wp_send_json_success(['message' => 'Mapping saved.']);
        }
        
        wp_send_json_error(['message' => 'Invalid mapping data.']);
    }

    /**
     * AJAX: Dismiss plugin installation notice.
     */
    public function ajaxDismissNotice(): void
    {
        check_ajax_referer('ctm_dismiss_notice', 'nonce');
        $type = sanitize_text_field($_POST['notice_type'] ?? '');
        
        if ($type === 'cf7') {
            update_option('ctm_cf7_notice_dismissed', true);
            wp_send_json_success(['message' => 'CF7 notice dismissed.']);
        } elseif ($type === 'gf') {
            update_option('ctm_gf_notice_dismissed', true);
            wp_send_json_success(['message' => 'GF notice dismissed.']);
        }
        
        wp_send_json_error(['message' => 'Invalid notice type.']);
    }

    /**
     * AJAX: Test API Connection with comprehensive logging
     */
    public function ajaxTestApiConnection(): void
    {
        $start_time = microtime(true);
        check_ajax_referer('ctm_test_api_connection', 'nonce');
        
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $api_secret = sanitize_text_field($_POST['api_secret'] ?? '');
        
        // Prepare response metadata
        $response_data = [
            'timestamp' => current_time('mysql'),
            'request_id' => wp_generate_uuid4(),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => '2.0',
            'api_endpoint' => 'https://api.calltrackingmetrics.com',
            'request_method' => 'GET',
            'auth_method' => 'Basic Authentication'
        ];
        
        if (empty($api_key) || empty($api_secret)) {
            wp_send_json_error([
                'message' => 'API Key and Secret are required',
                'details' => [
                    'Please provide both API Key and API Secret',
                    'API credentials cannot be empty',
                    'Check your CTM account for valid API keys'
                ],
                'metadata' => $response_data,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
            ]);
            return;
        }
        
        // Validate API key format (basic validation)
        if (strlen($api_key) < 20 || strlen($api_secret) < 20) {
            wp_send_json_error([
                'message' => 'Invalid API credential format',
                'details' => [
                    'API keys should be at least 20 characters long',
                    'Ensure you copied the complete API key and secret',
                    'Check for extra spaces or missing characters'
                ],
                'metadata' => $response_data,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
            ]);
            return;
        }
        
        try {
            // Create API service instance
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            
            $api_start_time = microtime(true);
            
            // Test basic account info
            $accountInfo = $apiService->getAccountInfo($api_key, $api_secret);
            $api_response_time = round((microtime(true) - $api_start_time) * 1000, 2);
            
            $response_data['api_response_time'] = $api_response_time;
            $response_data['account_endpoint'] = '/api/v1/accounts/';
            
            if (!$accountInfo || !isset($accountInfo['account'])) {
                $error_details = [
                    'Authentication failed - check your API credentials',
                    'Ensure your CTM account has API access enabled',
                    'Verify you\'re using the correct API environment',
                    'Check if your account subscription includes API access'
                ];
                
                // Add specific error based on response
                if (!$accountInfo) {
                    $error_details[] = 'No response received from CTM API';
                    $error_details[] = 'This may indicate network connectivity issues';
                } else {
                    $error_details[] = 'API responded but account data was missing';
                    $error_details[] = 'This typically indicates authentication failure';
                }
                
                wp_send_json_error([
                    'message' => 'Failed to connect to CTM API',
                    'details' => $error_details,
                    'metadata' => $response_data,
                    'api_response' => $accountInfo,
                    'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
                ]);
                return;
            }
            
            $account = $accountInfo['account'];
            $account_details = null;
            $details_response_time = null;
            
            // Try to get additional account details
            if (isset($account['id'])) {
                $details_start_time = microtime(true);
                $account_details = $apiService->getAccountById($account['id'], $api_key, $api_secret);
                $details_response_time = round((microtime(true) - $details_start_time) * 1000, 2);
                
                $response_data['details_response_time'] = $details_response_time;
                $response_data['details_endpoint'] = '/api/v1/accounts/' . $account['id'];
            }
            
            // Update WordPress options with successful connection
            update_option('ctm_api_key', $api_key);
            update_option('ctm_api_secret', $api_secret);
            update_option('ctm_api_auth_account', $account['id'] ?? '');
            
            // Prepare comprehensive success response
            $total_execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            wp_send_json_success([
                'message' => 'API Connection successful',
                'account_info' => $accountInfo,
                'account_details' => $account_details,
                'account_id' => $account['id'] ?? 'N/A',
                'connection_quality' => $this->assessConnectionQuality($api_response_time, $details_response_time),
                'metadata' => $response_data,
                'performance' => [
                    'total_execution_time' => $total_execution_time,
                    'api_response_time' => $api_response_time,
                    'details_response_time' => $details_response_time,
                    'network_overhead' => $total_execution_time - $api_response_time - ($details_response_time ?? 0)
                ],
                'capabilities' => [
                    'account_access' => true,
                    'details_access' => $account_details !== null,
                    'api_version' => 'v1'
                ]
            ]);
            
        } catch (\Exception $e) {
            $total_execution_time = round((microtime(true) - $start_time) * 1000, 2);
            
            // Enhanced error details based on exception type
            $error_details = [
                'Exception: ' . get_class($e),
                'Error: ' . $e->getMessage()
            ];
            
            // Add context-specific troubleshooting
            if (strpos($e->getMessage(), 'timeout') !== false) {
                $error_details[] = 'Request timed out - check network connectivity';
                $error_details[] = 'CTM API may be experiencing high load';
            } elseif (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'certificate') !== false) {
                $error_details[] = 'SSL/TLS certificate issue detected';
                $error_details[] = 'Check server SSL configuration';
            } elseif (strpos($e->getMessage(), 'DNS') !== false) {
                $error_details[] = 'DNS resolution failure';
                $error_details[] = 'Check domain name resolution';
            } else {
                $error_details[] = 'Check your API credentials';
                $error_details[] = 'Verify CTM service status';
                $error_details[] = 'Contact support if problem persists';
            }
            
            wp_send_json_error([
                'message' => 'API Connection failed: ' . $e->getMessage(),
                'details' => $error_details,
                'metadata' => $response_data,
                'exception' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ],
                'execution_time' => $total_execution_time
            ]);
        }
    }
    
    /**
     * AJAX: Clear logs without page refresh
     */
    public function ajaxClearLogs(): void
    {
        check_ajax_referer('ctm_clear_logs', 'nonce');
        
        $log_type = sanitize_text_field($_POST['log_type'] ?? '');
        $log_date = sanitize_text_field($_POST['log_date'] ?? '');
        
        try {
            switch ($log_type) {
                case 'cf7':
                    update_option('ctm_api_cf7_logs', '[]');
                    $this->loggingSystem->logActivity('CF7 logs cleared via AJAX', 'system');
                    wp_send_json_success(['message' => 'CF7 logs cleared successfully']);
                    break;
                    
                case 'gf':
                    update_option('ctm_api_gf_logs', '[]');
                    $this->loggingSystem->logActivity('GF logs cleared via AJAX', 'system');
                    wp_send_json_success(['message' => 'Gravity Forms logs cleared successfully']);
                    break;
                    
                case 'debug_all':
                    $this->loggingSystem->logActivity('All debug logs cleared via AJAX', 'system');
                    $this->loggingSystem->clearAllLogs();
                    wp_send_json_success(['message' => 'All debug logs cleared successfully']);
                    break;
                    
                case 'debug_single':
                    if (!empty($log_date)) {
                        $this->loggingSystem->logActivity("Debug log cleared for date: {$log_date} via AJAX", 'system');
                        $this->loggingSystem->clearDayLog($log_date);
                        wp_send_json_success(['message' => "Debug log for {$log_date} cleared successfully"]);
                    } else {
                        wp_send_json_error(['message' => 'Log date is required for single day clear']);
                    }
                    break;
                    
                default:
                    wp_send_json_error(['message' => 'Invalid log type specified']);
                    break;
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Error clearing logs: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Update log settings without page refresh
     */
    public function ajaxUpdateLogSettings(): void
    {
        check_ajax_referer('ctm_update_log_settings', 'nonce');
        
        try {
            $retention_days = (int) ($_POST['log_retention_days'] ?? 7);
            $retention_days = max(1, min(365, $retention_days)); // Between 1-365 days
            
            $auto_cleanup = isset($_POST['log_auto_cleanup']) && $_POST['log_auto_cleanup'] === '1';
            $email_notifications = isset($_POST['log_email_notifications']) && $_POST['log_email_notifications'] === '1';
            $notification_email = sanitize_email($_POST['log_notification_email'] ?? '');
            
            // Validate email if notifications are enabled
            if ($email_notifications && empty($notification_email)) {
                wp_send_json_error(['message' => 'Notification email is required when email notifications are enabled']);
                return;
            }
            
            // Update options
            update_option('ctm_log_retention_days', $retention_days);
            update_option('ctm_log_auto_cleanup', $auto_cleanup);
            update_option('ctm_log_email_notifications', $email_notifications);
            update_option('ctm_log_notification_email', $notification_email);
            
            // Log the settings update
            $this->loggingSystem->logActivity("Log settings updated via AJAX - Retention: {$retention_days} days", 'system', [
                'retention_days' => $retention_days,
                'auto_cleanup' => $auto_cleanup,
                'email_notifications' => $email_notifications,
                'notification_email' => $notification_email ? 'set' : 'empty'
            ]);
            
            wp_send_json_success([
                'message' => 'Log settings updated successfully',
                'settings' => [
                    'retention_days' => $retention_days,
                    'auto_cleanup' => $auto_cleanup,
                    'email_notifications' => $email_notifications,
                    'notification_email' => $notification_email
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Error updating log settings: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX: Toggle debug mode and return updated page content
     */
    public function ajaxToggleDebugMode(): void
    {
        check_ajax_referer('ctm_toggle_debug_mode', 'nonce');
        
        try {
            $current = get_option('ctm_debug_enabled', false);
            $new_value = !$current;
            
            update_option('ctm_debug_enabled', $new_value);
            
            // Log the debug mode change
            $this->loggingSystem->logActivity("Debug mode " . ($new_value ? 'enabled' : 'disabled') . " via AJAX", 'system', [
                'previous_state' => $current,
                'new_state' => $new_value,
                'user_id' => get_current_user_id()
            ]);
            
            // Get updated debug tab content
            $updated_content = $this->renderer->getDebugTabContent();
            
            wp_send_json_success([
                'message' => 'Debug mode ' . ($new_value ? 'enabled' : 'disabled') . ' successfully',
                'debug_enabled' => $new_value,
                'updated_content' => $updated_content,
                'action' => $new_value ? 'enabled' : 'disabled'
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Error toggling debug mode: ' . $e->getMessage()]);
        }
    }

    /**
     * Assess connection quality based on response times
     */
    private function assessConnectionQuality($api_time, $details_time): array
    {
        $total_time = $api_time + ($details_time ?? 0);
        
        if ($total_time < 500) {
            $quality = 'excellent';
            $color = 'green';
        } elseif ($total_time < 1000) {
            $quality = 'good';
            $color = 'blue';
        } elseif ($total_time < 2000) {
            $quality = 'fair';
            $color = 'yellow';
        } else {
            $quality = 'poor';
            $color = 'red';
        }
        
        return [
            'rating' => $quality,
            'color' => $color,
            'total_time' => $total_time,
            'description' => "Connection quality: {$quality} ({$total_time}ms total)"
        ];
    }

    /**
     * AJAX: Simulate API Request for testing
     */
    public function ajaxSimulateApiRequest(): void
    {
        check_ajax_referer('ctm_simulate_api_request', 'nonce');
        
        $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
        $method = sanitize_text_field($_POST['method'] ?? 'GET');
        
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        
        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
            return;
        }
        
        try {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            
            switch ($endpoint) {
                case '/api/v1/accounts/':
                    $result = $apiService->getAccountInfo($apiKey, $apiSecret);
                    break;
                case '/api/v1/forms':
                    $result = $apiService->getForms($apiKey, $apiSecret);
                    break;
                case '/api/v1/tracking_numbers':
                    $result = $apiService->getTrackingNumbers($apiKey, $apiSecret);
                    break;
                case '/api/v1/calls':
                    $result = $apiService->getCalls($apiKey, $apiSecret);
                    break;
                default:
                    wp_send_json_error(['message' => 'Unsupported endpoint']);
                    return;
            }
            
            wp_send_json_success([
                'endpoint' => $endpoint,
                'method' => $method,
                'response' => $result,
                'timestamp' => current_time('mysql')
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method
            ]);
        }
    }

    /**
     * AJAX: Run Plugin Health Check
     */
    public function ajaxHealthCheck(): void
    {
        check_ajax_referer('ctm_health_check', 'nonce');
        
        $checks = [];
        
        // API Status Checks
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        
        // API Key Check
        if ($apiKey && $apiSecret) {
            $checks[] = ['name' => 'API Key', 'status' => 'pass', 'message' => 'Configured'];
        } else {
            $checks[] = ['name' => 'API Key', 'status' => 'fail', 'message' => 'Not configured'];
        }
        
        // API Connection Check
        if ($apiKey && $apiSecret) {
            try {
                $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                if ($accountInfo && isset($accountInfo['account'])) {
                    $checks[] = ['name' => 'API Connection', 'status' => 'pass', 'message' => 'Connected'];
                    $checks[] = ['name' => 'Account Access', 'status' => 'pass', 'message' => 'Accessible'];
                } else {
                    $checks[] = ['name' => 'API Connection', 'status' => 'fail', 'message' => 'Failed'];
                    $checks[] = ['name' => 'Account Access', 'status' => 'fail', 'message' => 'No access'];
                }
            } catch (\Exception $e) {
                $checks[] = ['name' => 'API Connection', 'status' => 'fail', 'message' => 'Connection failed'];
                $checks[] = ['name' => 'Account Access', 'status' => 'fail', 'message' => 'No access'];
            }
        } else {
            $checks[] = ['name' => 'API Connection', 'status' => 'fail', 'message' => 'No credentials'];
            $checks[] = ['name' => 'Account Access', 'status' => 'fail', 'message' => 'No credentials'];
        }
        
        // Form Integration Checks
        $cf7_active = class_exists('WPCF7_ContactForm');
        $gf_active = class_exists('GFAPI');
        
        $checks[] = ['name' => 'Contact Form 7', 'status' => $cf7_active ? 'pass' : 'warning', 'message' => $cf7_active ? 'Installed' : 'Not installed'];
        $checks[] = ['name' => 'Gravity Forms', 'status' => $gf_active ? 'pass' : 'warning', 'message' => $gf_active ? 'Installed' : 'Not installed'];
        
        // Field Mappings Check
        $cf7_mappings = get_option('ctm_cf7_field_mappings', []);
        $gf_mappings = get_option('ctm_gf_field_mappings', []);
        $has_mappings = !empty($cf7_mappings) || !empty($gf_mappings);
        $checks[] = ['name' => 'Field Mappings', 'status' => $has_mappings ? 'pass' : 'warning', 'message' => $has_mappings ? 'Configured' : 'Not configured'];
        
        // Server Environment Checks
        $checks[] = ['name' => 'PHP Version', 'status' => version_compare(PHP_VERSION, '7.4', '>=') ? 'pass' : 'warning', 'message' => PHP_VERSION];
        $checks[] = ['name' => 'cURL Extension', 'status' => function_exists('curl_init') ? 'pass' : 'fail', 'message' => function_exists('curl_init') ? 'Available' : 'Missing'];
        $checks[] = ['name' => 'SSL Support', 'status' => extension_loaded('openssl') ? 'pass' : 'fail', 'message' => extension_loaded('openssl') ? 'Available' : 'Missing'];
        
        // Memory Check
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $memory_status = $memory_limit >= 128 * 1024 * 1024 ? 'pass' : 'warning';
        $checks[] = ['name' => 'Memory Limit', 'status' => $memory_status, 'message' => size_format($memory_limit)];
        
        // Plugin Status Checks
        $checks[] = ['name' => 'Plugin Version', 'status' => 'pass', 'message' => '2.0'];
        
        // Database Tables Check
        global $wpdb;
        $table_check = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}options'");
        $checks[] = ['name' => 'Database Tables', 'status' => $table_check ? 'pass' : 'fail', 'message' => $table_check ? 'Accessible' : 'Error'];
        
        // File Permissions Check
        $upload_dir = wp_upload_dir();
        $writable = is_writable($upload_dir['basedir']);
        $checks[] = ['name' => 'File Permissions', 'status' => $writable ? 'pass' : 'warning', 'message' => $writable ? 'Writable' : 'Limited'];
        
        // Debug Mode Check
        $debug_enabled = get_option('ctm_debug_enabled', false);
        $checks[] = ['name' => 'Debug Mode', 'status' => $debug_enabled ? 'pass' : 'warning', 'message' => $debug_enabled ? 'Enabled' : 'Disabled'];
        
        wp_send_json_success(['checks' => $checks]);
    }

    /**
     * AJAX: Get Performance Metrics
     */
    public function ajaxGetPerformanceMetrics(): void
    {
        check_ajax_referer('ctm_get_performance_metrics', 'nonce');
        
        global $wpdb;
        
        // Get client-side metrics if provided
        $client_metrics = isset($_POST['client_metrics']) ? json_decode(stripslashes($_POST['client_metrics']), true) : null;
        
        // Memory & Processing
        $memory_limit = ini_get('memory_limit');
        $memory_limit_bytes = wp_convert_hr_to_bytes($memory_limit);
        $current_usage = memory_get_usage(true);
        $memory_percentage = $memory_limit_bytes > 0 ? round(($current_usage / $memory_limit_bytes) * 100, 1) . '%' : 'N/A';
        
        // Database Performance
        $total_queries = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
        
        // Query time calculation
        $query_time = 'N/A';
        if (defined('SAVEQUERIES') && constant('SAVEQUERIES') && isset($wpdb->queries)) {
            $total_time = 0;
            foreach ($wpdb->queries as $query) {
                $total_time += $query[1];
            }
            $query_time = round($total_time * 1000, 2) . 'ms';
        } else {
            $query_time = 'N/A (Enable SAVEQUERIES)';
        }
        
        // Server load
        $server_load = 'N/A';
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $server_load = round($load[0], 2) . ' (1min)';
        }
        
        // Disk space
        $disk_space = 'N/A';
        $upload_dir = wp_upload_dir();
        if (function_exists('disk_free_space') && isset($upload_dir['basedir'])) {
            $free_bytes = disk_free_space($upload_dir['basedir']);
            $disk_space = $free_bytes ? size_format($free_bytes) . ' free' : 'N/A';
        }
        
        // Enhanced TTFB calculation
        $ttfb = 'N/A';
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $ttfb = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms';
        }
        
        // Process client-side metrics with enhanced debugging and validation
        $dom_ready = 'N/A (Client-side)';
        $load_complete = 'N/A (Client-side)';
        $scripts_loaded = 'N/A (Client-side)';
        $styles_loaded = 'N/A (Client-side)';
        $images_loaded = 'N/A (Client-side)';
        
        if ($client_metrics && is_array($client_metrics)) {
            // Debug log the received metrics
            error_log('CTM Performance: Received client metrics: ' . json_encode($client_metrics));
            
            // DOM Content Loaded - more flexible validation
            if (isset($client_metrics['domContentLoaded'])) {
                $dom_value = floatval($client_metrics['domContentLoaded']);
                if ($dom_value > 0 && $dom_value < 60000) { // Reasonable range: 0-60 seconds
                    $dom_ready = round($dom_value, 2) . 'ms';
                } else if ($dom_value > 0) {
                    $dom_ready = round($dom_value, 2) . 'ms (high)';
                } else {
                    $dom_ready = 'N/A (invalid timing)';
                }
            }
            
            // Load Complete
            if (isset($client_metrics['loadComplete'])) {
                $load_value = floatval($client_metrics['loadComplete']);
                if ($load_value > 0 && $load_value < 120000) { // Reasonable range: 0-120 seconds
                    $load_complete = round($load_value, 2) . 'ms';
                } else if ($load_value > 0) {
                    $load_complete = round($load_value, 2) . 'ms (high)';
                } else {
                    $load_complete = 'N/A (invalid timing)';
                }
            }
            
            // Scripts Loaded
            if (isset($client_metrics['scriptsLoaded'])) {
                $scripts_count = intval($client_metrics['scriptsLoaded']);
                if ($scripts_count >= 0) {
                    $scripts_loaded = $scripts_count . ' scripts';
                }
            }
            
            // Styles Loaded
            if (isset($client_metrics['stylesLoaded'])) {
                $styles_count = intval($client_metrics['stylesLoaded']);
                if ($styles_count >= 0) {
                    $styles_loaded = $styles_count . ' stylesheets';
                }
            }
            
            // Images Loaded
            if (isset($client_metrics['imagesLoaded'])) {
                $images_count = intval($client_metrics['imagesLoaded']);
                if ($images_count >= 0) {
                    $images_loaded = $images_count . ' images';
                }
            }
        } else {
            // Log when no client metrics are received
            error_log('CTM Performance: No client metrics received or invalid format');
        }

        $metrics = [
            // Memory & Processing
            'current_memory' => size_format(memory_get_usage(true)),
            'peak_memory' => size_format(memory_get_peak_usage(true)),
            'memory_percentage' => $memory_percentage,
            'memory_limit' => $memory_limit,
            'execution_time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms',
            'time_limit' => ini_get('max_execution_time') . 's',
            'cpu_usage' => function_exists('sys_getloadavg') ? round(sys_getloadavg()[0], 2) : 'N/A',
            
            // Database Performance
            'current_queries' => get_num_queries(),
            'total_queries' => $total_queries,
            'query_time' => $query_time,
            'total_query_time' => $query_time,
            'slow_queries' => $this->getSlowQueries(),
            'cache_hits' => $this->getCacheHits(),
            'cache_misses' => $this->getCacheMisses(),
            'db_version' => $GLOBALS['wpdb']->db_version(),
            
            // Page Load Performance (enhanced with client-side data)
            'page_load_time' => round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms',
            'server_response' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . 'ms' : 'N/A',
            'server_load' => $server_load,
            'ttfb' => $ttfb,
            'dom_ready' => $dom_ready,
            'load_complete' => $load_complete,
            'scripts_loaded' => $scripts_loaded,
            'styles_loaded' => $styles_loaded,
            'images_loaded' => $images_loaded,
            
            // WordPress Performance
            'active_plugins' => count(get_option('active_plugins', [])),
            'theme_load_time' => $this->calculateThemeLoadTime(),
            'plugin_load_time' => $this->calculatePluginLoadTime(),
            'admin_queries' => is_admin() ? get_num_queries() : 'N/A',
            'frontend_queries' => $this->getFrontendQueries(),
            'cron_jobs' => count(_get_cron_array()),
            
            // Real-time Metrics
            'current_timestamp' => current_time('Y-m-d H:i:s'),
            'disk_space' => $disk_space,
            'disk_usage' => $disk_space,
            'network_io' => $this->getNetworkIO(),
            'active_sessions' => $this->getActiveSessions(),
            'error_rate' => $this->getErrorRate(),
            'last_updated' => current_time('H:i:s'),
            
            // Grid metrics for top section
            'memory_usage' => size_format(memory_get_usage(true)),
            'db_queries' => get_num_queries(),
            'api_calls' => $this->getApiCalls24h(),
            'api_response_time' => $this->getApiResponseTime()
        ];
        
        wp_send_json_success($metrics);
    }

    /**
     * AJAX: Analyze Common Issues
     */
    public function ajaxAnalyzeIssue(): void
    {
        check_ajax_referer('ctm_analyze_issue', 'nonce');
        
        $issueType = sanitize_text_field($_POST['issue_type'] ?? '');
        
        $analysis = $this->analyzeIssueType($issueType);
        
        wp_send_json_success(['analysis' => $analysis]);
    }

    /**
     * Analyze specific issue types
     */
    private function analyzeIssueType(string $issueType): array
    {
        switch ($issueType) {
            case 'api_credentials':
                return $this->analyzeApiCredentials();
            case 'form_integration':
                return $this->analyzeFormIntegration();
            case 'network_connectivity':
                return $this->analyzeNetworkConnectivity();
            case 'plugin_conflicts':
                return $this->analyzePluginConflicts();
            default:
                return [
                    'title' => 'Unknown Issue Type',
                    'description' => 'The selected issue type is not recognized.',
                    'status' => 'error',
                    'issues' => ['Unknown issue type selected'],
                    'solutions' => ['Please select a valid issue type from the list']
                ];
        }
    }

    private function analyzeApiCredentials(): array
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $issues = [];
        $solutions = [];
        
        if (!$apiKey || !$apiSecret) {
            $issues[] = 'API credentials are not configured';
            $solutions[] = 'Enter your API key and secret in the General tab';
            $solutions[] = 'Contact CallTrackingMetrics support to obtain API credentials';
        } else {
            try {
                $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                
                if (!$accountInfo || !isset($accountInfo['account'])) {
                    $issues[] = 'API credentials are invalid or expired';
                    $solutions[] = 'Verify your API key and secret are correct';
                    $solutions[] = 'Check if your CTM account has API access enabled';
                    $solutions[] = 'Contact CallTrackingMetrics support if credentials should be valid';
                }
            } catch (\Exception $e) {
                $issues[] = 'API connection failed: ' . $e->getMessage();
                $solutions[] = 'Check your internet connection';
                $solutions[] = 'Verify CallTrackingMetrics API service is available';
                $solutions[] = 'Check if your server can make HTTPS requests';
            }
        }
        $status = empty($issues) ? 'healthy' : 'issues_found';
        $score = $status === 'healthy' ? 100 : 0;
        return [
            'title' => 'API Credentials Analysis',
            'description' => 'Checking the validity and configuration of your CallTrackingMetrics API credentials.',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }

    private function analyzeFormIntegration(): array
    {
        $issues = [];
        $solutions = [];
        
        $cf7_active = class_exists('WPCF7_ContactForm');
        $gf_active = class_exists('GFAPI');
        
        if (!$cf7_active && !$gf_active) {
            $issues[] = 'No supported form plugins detected';
            $solutions[] = 'Install Contact Form 7 or Gravity Forms';
            $solutions[] = 'Activate the form plugin after installation';
        } else {
            $cf7_enabled = get_option('ctm_api_cf7_enabled');
            $gf_enabled = get_option('ctm_api_gf_enabled');
            
            if ($cf7_active && !$cf7_enabled) {
                $issues[] = 'Contact Form 7 is installed but CTM integration is disabled';
                $solutions[] = 'Enable Contact Form 7 integration in General settings';
            }
            
            if ($gf_active && !$gf_enabled) {
                $issues[] = 'Gravity Forms is installed but CTM integration is disabled';
                $solutions[] = 'Enable Gravity Forms integration in General settings';
            }
            
            // Check for field mappings
            if ($cf7_enabled && $cf7_active) {
                $cf7_forms = \WPCF7_ContactForm::find();
                $mapped_forms = 0;
                foreach ($cf7_forms as $form) {
                    $mapping = get_option("ctm_mapping_cf7_{$form->id()}", []);
                    if (!empty($mapping)) {
                        $mapped_forms++;
                    }
                }
                if (count($cf7_forms) > 0 && $mapped_forms === 0) {
                    $issues[] = 'Contact Form 7 forms have no field mappings configured';
                    $solutions[] = 'Configure field mappings in the Field Mapping tab';
                }
            }
        }
        $status = empty($issues) ? 'healthy' : (count($issues) === 1 ? 'warning' : 'issues_found');
        $score = $status === 'healthy' ? 100 : ($status === 'warning' ? 70 : 0);
        return [
            'title' => 'Form Integration Analysis',
            'description' => 'Checking the configuration and setup of form plugin integrations.',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }

    private function analyzeNetworkConnectivity(): array
    {
        $issues = [];
        $solutions = [];
        
        // Test basic connectivity
        if (!function_exists('curl_init') && !function_exists('file_get_contents')) {
            $issues[] = 'No HTTP request methods available';
            $solutions[] = 'Enable cURL extension in PHP';
            $solutions[] = 'Contact your hosting provider for assistance';
        }
        
        // Test SSL support
        if (!extension_loaded('openssl')) {
            $issues[] = 'SSL/TLS support is not available';
            $solutions[] = 'Enable OpenSSL extension in PHP';
            $solutions[] = 'Contact your hosting provider to enable SSL support';
        }
        
        // Test actual API connectivity using /api/v1/accounts (requires authentication)
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if (!$apiKey || !$apiSecret) {
            $issues[] = 'API credentials are not configured';
            $solutions[] = 'Enter your API key and secret in the General tab';
        } else {
            try {
                $response = wp_remote_get('https://api.calltrackingmetrics.com/api/v1/accounts', [
                    'timeout' => 10,
                    'sslverify' => true,
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                        'Accept' => 'application/json',
                    ],
                ]);
                
                if (is_wp_error($response)) {
                    $issues[] = 'Cannot connect to CallTrackingMetrics API: ' . $response->get_error_message();
                    $solutions[] = 'Check your server\'s internet connection';
                    $solutions[] = 'Verify firewall settings allow HTTPS connections';
                    $solutions[] = 'Contact your hosting provider if connection issues persist';
                } elseif (wp_remote_retrieve_response_code($response) >= 400) {
                    $issues[] = 'CallTrackingMetrics API returned error: ' . wp_remote_retrieve_response_code($response);
                    $solutions[] = 'Check CallTrackingMetrics service status';
                    $solutions[] = 'Verify your API credentials are correct and have access';
                    $solutions[] = 'Try again later if this is a temporary issue';
                }
            } catch (\Exception $e) {
                $issues[] = 'Network connectivity test failed: ' . $e->getMessage();
                $solutions[] = 'Check your server\'s network configuration';
                $solutions[] = 'Contact your hosting provider for network troubleshooting';
            }
        }
        $status = empty($issues) ? 'healthy' : (count($issues) === 1 ? 'warning' : 'issues_found');
        $score = $status === 'healthy' ? 100 : ($status === 'warning' ? 70 : 0);
        return [
            'title' => 'Network Connectivity Analysis',
            'description' => 'Testing network connectivity and ability to reach CallTrackingMetrics API (accounts endpoint).',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }

    private function analyzePluginConflicts(): array
    {
        $issues = [];
        $solutions = [];
        
        // Check for common conflicting plugins
        $active_plugins = get_option('active_plugins', []);
        $potential_conflicts = [
            'wp-rocket/wp-rocket.php' => 'WP Rocket (caching)',
            'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
            'wp-super-cache/wp-cache.php' => 'WP Super Cache',
            'autoptimize/autoptimize.php' => 'Autoptimize',
        ];
        
        foreach ($potential_conflicts as $plugin_file => $plugin_name) {
            if (in_array($plugin_file, $active_plugins)) {
                $issues[] = "Potential conflict with {$plugin_name}";
                $solutions[] = "Configure {$plugin_name} to exclude CTM plugin files from optimization";
                $solutions[] = "Add CTM AJAX endpoints to {$plugin_name} exclusion list";
            }
        }
        
        // Check for JavaScript conflicts
        if (wp_script_is('jquery', 'done')) {
            // jQuery is loaded, check version
            global $wp_scripts;
            if (isset($wp_scripts->registered['jquery'])) {
                $jquery_version = $wp_scripts->registered['jquery']->ver;
                if (version_compare($jquery_version, '1.12', '<')) {
                    $issues[] = 'jQuery version is outdated: ' . $jquery_version;
                    $solutions[] = 'Update WordPress to get newer jQuery version';
                    $solutions[] = 'Check for plugins that force older jQuery versions';
                }
            }
        }
        
        // Check PHP error reporting
        if (ini_get('display_errors')) {
            $issues[] = 'PHP error display is enabled (may interfere with AJAX)';
            $solutions[] = 'Disable display_errors in PHP configuration';
            $solutions[] = 'Use error logging instead of display_errors';
        }
        $status = empty($issues) ? 'healthy' : (count($issues) === 1 ? 'warning' : 'issues_found');
        $score = $status === 'healthy' ? 100 : ($status === 'warning' ? 70 : 0);
        return [
            'title' => 'Plugin Conflicts Analysis',
            'description' => 'Checking for potential conflicts with other plugins and system settings.',
            'status' => $status,
            'score' => $score,
            'issues' => $issues,
            'solutions' => $solutions
        ];
    }

    /**
     * AJAX: Email System Information Report
     */
    public function ajaxEmailSystemInfo(): void
    {
        check_ajax_referer('ctm_email_system_info', 'nonce');
        
        $email_to = sanitize_email($_POST['email_to'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $additional_message = sanitize_textarea_field($_POST['message'] ?? '');
        
        if (!$email_to) {
            wp_send_json_error(['message' => 'Email address is required']);
            return;
        }
        
        if (!$subject) {
            $subject = 'System Information Report - ' . get_bloginfo('name');
        }
        
        // Generate comprehensive system information
        $system_info = $this->generateSystemInfoReport();
        
        // Prepare email content
        $email_body = '';
        
        if ($additional_message) {
            $email_body .= "Message from sender:\n";
            $email_body .= $additional_message . "\n\n";
            $email_body .= str_repeat('-', 50) . "\n\n";
        }
        
        $email_body .= $system_info;
        
        // Send email
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
        
        $sent = wp_mail($email_to, $subject, $email_body, $headers);
        
        if ($sent) {
            wp_send_json_success([
                'message' => 'System information email sent successfully to ' . $email_to
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Failed to send email. Please check your email configuration.'
            ]);
        }
    }

    /**
     * Generate comprehensive system information report
     */
    private function generateSystemInfoReport(): string
    {
        $report = "=== SYSTEM INFORMATION REPORT ===\n";
        $report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
        $report .= "Site: " . get_bloginfo('name') . "\n\n";
        
        // WordPress Environment
        $report .= "=== WORDPRESS ENVIRONMENT ===\n";
        $report .= "WordPress Version: " . get_bloginfo('version') . "\n";
        $report .= "Site URL: " . home_url() . "\n";
        $report .= "Admin URL: " . admin_url() . "\n";
        $report .= "WordPress Language: " . get_locale() . "\n";
        $report .= "WordPress Debug: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n";
        $report .= "WordPress Memory Limit: " . WP_MEMORY_LIMIT . "\n";
        $report .= "Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n\n";
        
        // Server Environment
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
        
        // Database
        $report .= "=== DATABASE ===\n";
        $report .= "Database Version: " . $GLOBALS['wpdb']->db_version() . "\n";
        $report .= "Database Host: " . DB_HOST . "\n";
        $report .= "Database Name: " . DB_NAME . "\n";
        $report .= "Database Charset: " . DB_CHARSET . "\n";
        $report .= "Table Prefix: " . $GLOBALS['wpdb']->prefix . "\n\n";
        
        // PHP Extensions
        $report .= "=== PHP EXTENSIONS ===\n";
        $extensions = [
            'cURL' => function_exists('curl_init'),
            'OpenSSL' => extension_loaded('openssl'),
            'mbstring' => extension_loaded('mbstring'),
            'GD Library' => extension_loaded('gd'),
            'XML' => extension_loaded('xml'),
            'JSON' => extension_loaded('json'),
            'ZIP' => extension_loaded('zip')
        ];
        
        foreach ($extensions as $name => $available) {
            $report .= $name . ": " . ($available ? 'Available' : 'Missing') . "\n";
        }
        $report .= "\n";
        
        // CallTrackingMetrics Plugin
        $report .= "=== CALLTRACKINGMETRICS PLUGIN ===\n";
        $report .= "Plugin Version: 2.0\n";
        $report .= "Debug Mode: " . (get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled') . "\n";
        $report .= "API Key Configured: " . (get_option('ctm_api_key') ? 'Yes' : 'No') . "\n";
        $report .= "CF7 Integration: " . (get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled') . "\n";
        $report .= "GF Integration: " . (get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled') . "\n\n";
        
        // Theme & Plugins
        $report .= "=== THEME & PLUGINS ===\n";
        $report .= "Active Theme: " . wp_get_theme()->get('Name') . "\n";
        $report .= "Theme Version: " . wp_get_theme()->get('Version') . "\n";
        $report .= "Active Plugins: " . count(get_option('active_plugins', [])) . "\n";
        $report .= "Contact Form 7: " . (class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed') . "\n";
        $report .= "Gravity Forms: " . (class_exists('GFAPI') ? 'Installed' : 'Not Installed') . "\n\n";
        
        // Current Performance
        $report .= "=== CURRENT PERFORMANCE ===\n";
        $report .= "Memory Usage: " . size_format(memory_get_usage(true)) . "\n";
        $report .= "Peak Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
        $report .= "Database Queries: " . get_num_queries() . "\n";
        $report .= "Admin Email: " . get_option('admin_email') . "\n";
        $report .= "Timezone: " . (get_option('timezone_string') ?: 'UTC') . "\n\n";
        
        $report .= "=== END REPORT ===\n";
        
        return $report;
    }

    /**
     * AJAX: Refresh System Information
     */
    public function ajaxRefreshSystemInfo(): void
    {
        check_ajax_referer('ctm_refresh_system_info', 'nonce');
        
        try {
            // Generate fresh system information
            $system_info = [
                'php_version' => PHP_VERSION,
                'wp_version' => get_bloginfo('version'),
                'memory_usage' => size_format(memory_get_usage(true)) . ' / ' . ini_get('memory_limit'),
                'db_queries' => get_num_queries(),
                'wordpress_env' => [
                    'version' => get_bloginfo('version'),
                    'language' => get_locale(),
                    'debug_mode' => WP_DEBUG ? 'Enabled' : 'Disabled',
                    'memory_limit' => WP_MEMORY_LIMIT,
                    'multisite' => is_multisite() ? 'Yes' : 'No',
                    'timezone' => get_option('timezone_string') ?: 'UTC'
                ],
                'server_env' => [
                    'php_version' => PHP_VERSION,
                    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                    'operating_system' => PHP_OS,
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time') . 's',
                    'upload_max_size' => ini_get('upload_max_filesize')
                ],
                'database_info' => [
                    'version' => $GLOBALS['wpdb']->db_version(),
                    'host' => DB_HOST,
                    'charset' => DB_CHARSET,
                    'table_prefix' => $GLOBALS['wpdb']->prefix
                ]
            ];
            
            wp_send_json_success([
                'message' => 'System information refreshed successfully',
                'system_info' => $system_info,
                'timestamp' => current_time('mysql')
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Failed to refresh system information: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * AJAX: Auto-fix common issues detected by health check
     */
    public function ajaxAutoFixIssues(): void
    {
        check_ajax_referer('ctm_auto_fix_issues', 'nonce');
        
        $fixes = [];
        $fixed_count = 0;
        
        try {
            // Fix 1: Enable debug mode if disabled
            $debug_enabled = get_option('ctm_debug_enabled', false);
            if (!$debug_enabled) {
                update_option('ctm_debug_enabled', true);
                $fixes[] = [
                    'issue' => 'Debug Mode Disabled',
                    'status' => 'fixed',
                    'message' => 'Debug mode has been enabled for better troubleshooting'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Debug Mode',
                    'status' => 'skipped',
                    'message' => 'Debug mode was already enabled'
                ];
            }
            
            // Fix 2: Set default log retention if not configured
            $retention_days = get_option('ctm_log_retention_days', null);
            if ($retention_days === null || $retention_days < 1) {
                update_option('ctm_log_retention_days', 7);
                $fixes[] = [
                    'issue' => 'Log Retention Settings',
                    'status' => 'fixed',
                    'message' => 'Set log retention to 7 days (recommended default)'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Log Retention Settings',
                    'status' => 'skipped',
                    'message' => 'Log retention already configured'
                ];
            }
            
            // Fix 3: Enable auto-cleanup if disabled
            $auto_cleanup = get_option('ctm_log_auto_cleanup', null);
            if ($auto_cleanup === null || !$auto_cleanup) {
                update_option('ctm_log_auto_cleanup', true);
                $fixes[] = [
                    'issue' => 'Auto Log Cleanup',
                    'status' => 'fixed',
                    'message' => 'Enabled automatic log cleanup to prevent disk space issues'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Auto Log Cleanup',
                    'status' => 'skipped',
                    'message' => 'Auto cleanup already enabled'
                ];
            }
            
            // Fix 4: Clear old logs if too many exist
            $log_index = get_option('ctm_log_index', []);
            if (is_array($log_index) && count($log_index) > 30) {
                // Keep only last 7 days of logs
                $logs_to_keep = array_slice($log_index, -7);
                $logs_to_remove = array_diff($log_index, $logs_to_keep);
                
                foreach ($logs_to_remove as $date) {
                    delete_option("ctm_daily_log_{$date}");
                }
                
                update_option('ctm_log_index', $logs_to_keep);
                $fixes[] = [
                    'issue' => 'Excessive Log Files',
                    'status' => 'fixed',
                    'message' => 'Cleaned up ' . count($logs_to_remove) . ' old log files to free disk space'
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Log File Cleanup',
                    'status' => 'skipped',
                    'message' => 'Log files are within acceptable limits'
                ];
            }
            
            // Fix 5: Set notification email if empty
            $notification_email = get_option('ctm_log_notification_email', '');
            $admin_email = get_option('admin_email', '');
            if (empty($notification_email) && !empty($admin_email)) {
                update_option('ctm_log_notification_email', $admin_email);
                $fixes[] = [
                    'issue' => 'Notification Email Missing',
                    'status' => 'fixed',
                    'message' => 'Set notification email to admin email: ' . $admin_email
                ];
                $fixed_count++;
            } else {
                $fixes[] = [
                    'issue' => 'Notification Email',
                    'status' => 'skipped',
                    'message' => 'Notification email already configured'
                ];
            }
            
            // Fix 6: Check and fix file permissions (if possible)
            $upload_dir = wp_upload_dir();
            if (!empty($upload_dir['basedir']) && is_writable($upload_dir['basedir'])) {
                $fixes[] = [
                    'issue' => 'File Permissions',
                    'status' => 'skipped',
                    'message' => 'Upload directory permissions are correct'
                ];
            } else {
                $fixes[] = [
                    'issue' => 'File Permissions',
                    'status' => 'failed',
                    'message' => 'Upload directory is not writable - manual intervention required'
                ];
            }
            
            // Fix 7: Memory limit check
            $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
            $recommended_memory = 256 * 1024 * 1024; // 256MB
            
            if ($memory_limit < $recommended_memory) {
                $fixes[] = [
                    'issue' => 'Low Memory Limit',
                    'status' => 'failed',
                    'message' => 'Memory limit is ' . size_format($memory_limit) . '. Recommend increasing to 256MB+ in php.ini'
                ];
            } else {
                $fixes[] = [
                    'issue' => 'Memory Limit',
                    'status' => 'skipped',
                    'message' => 'Memory limit is adequate: ' . size_format($memory_limit)
                ];
            }
            
            // Fix 8: Check cURL availability
            if (!function_exists('curl_init')) {
                $fixes[] = [
                    'issue' => 'cURL Extension Missing',
                    'status' => 'failed',
                    'message' => 'cURL extension is not available - contact hosting provider'
                ];
            } else {
                $fixes[] = [
                    'issue' => 'cURL Extension',
                    'status' => 'skipped',
                    'message' => 'cURL extension is available'
                ];
            }
            
            wp_send_json_success([
                'message' => "Auto-fix completed: {$fixed_count} issues fixed",
                'fixes' => $fixes,
                'summary' => [
                    'total_checks' => count($fixes),
                    'fixed' => $fixed_count,
                    'skipped' => count(array_filter($fixes, fn($f) => $f['status'] === 'skipped')),
                    'failed' => count(array_filter($fixes, fn($f) => $f['status'] === 'failed'))
                ]
            ]);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Auto-fix encountered an error: ' . $e->getMessage(),
                'fixes' => $fixes
            ]);
        }
    }

    /**
     * AJAX: Run Full Diagnostic
     */
    public function ajaxFullDiagnostic(): void
    {
        check_ajax_referer('ctm_full_diagnostic', 'nonce');
        
        try {
            $diagnostic_results = [
                'passed_checks' => 0,
                'warning_checks' => 0,
                'failed_checks' => 0,
                'critical_issues' => [],
                'categories' => []
            ];
            
            // Run API Credentials Analysis
            $api_analysis = $this->analyzeApiCredentials();
            $api_score = isset($api_analysis['score']) && is_numeric($api_analysis['score']) ? $api_analysis['score'] : 0;
            $diagnostic_results['categories']['api_credentials'] = [
                'title' => 'API Credentials',
                'description' => 'Validation of CallTrackingMetrics API connectivity and authentication',
                'status' => $api_analysis['status'],
                'score' => $api_score,
                'issues' => $api_analysis['issues'] ?? [],
                'recommendations' => $api_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on API analysis
            if ($api_analysis['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($api_analysis['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($api_score < 30) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'API Credentials Failed',
                        'description' => 'Cannot connect to CallTrackingMetrics API. Plugin functionality will be severely limited.',
                        'auto_fix_available' => false,
                        'fix_id' => 'api_credentials'
                    ];
                }
            }
            
            // Run Form Integration Analysis
            $form_analysis = $this->analyzeFormIntegration();
            $form_score = isset($form_analysis['score']) && is_numeric($form_analysis['score']) ? $form_analysis['score'] : 0;
            $diagnostic_results['categories']['form_integration'] = [
                'title' => 'Form Integration',
                'description' => 'Analysis of Contact Form 7 and Gravity Forms integration status',
                'status' => $form_analysis['status'],
                'score' => $form_score,
                'issues' => $form_analysis['issues'] ?? [],
                'recommendations' => $form_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on form analysis
            if ($form_analysis['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($form_analysis['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
            }
            
            // Run Network Connectivity Analysis
            $network_analysis = $this->analyzeNetworkConnectivity();
            $network_score = isset($network_analysis['score']) && is_numeric($network_analysis['score']) ? $network_analysis['score'] : 0;
            $diagnostic_results['categories']['network_connectivity'] = [
                'title' => 'Network Connectivity',
                'description' => 'Testing network connectivity and DNS resolution for CTM services',
                'status' => $network_analysis['status'],
                'score' => $network_score,
                'issues' => $network_analysis['issues'] ?? [],
                'recommendations' => $network_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on network analysis
            if ($network_analysis['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($network_analysis['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($network_score < 40) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'Network Connectivity Issues',
                        'description' => 'Cannot reach CallTrackingMetrics servers. Check firewall and DNS settings.',
                        'auto_fix_available' => false,
                        'fix_id' => 'network_connectivity'
                    ];
                }
            }
            
            // Run Plugin Conflicts Analysis
            $conflicts_analysis = $this->analyzePluginConflicts();
            $conflicts_score = isset($conflicts_analysis['score']) && is_numeric($conflicts_analysis['score']) ? $conflicts_analysis['score'] : 0;
            $diagnostic_results['categories']['plugin_conflicts'] = [
                'title' => 'Plugin Conflicts',
                'description' => 'Scanning for potential conflicts with other WordPress plugins',
                'status' => $conflicts_analysis['status'],
                'score' => $conflicts_score,
                'issues' => $conflicts_analysis['issues'] ?? [],
                'recommendations' => $conflicts_analysis['recommendations'] ?? []
            ];
            
            // Update counters based on conflicts analysis
            if ($conflicts_analysis['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($conflicts_analysis['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
            }
            
            // Additional System Health Checks
            $system_health = $this->runSystemHealthChecks();
            $system_score = isset($system_health['score']) && is_numeric($system_health['score']) ? $system_health['score'] : 0;
            $diagnostic_results['categories']['system_health'] = [
                'title' => 'System Health',
                'description' => 'WordPress environment and server configuration analysis',
                'status' => $system_health['status'],
                'score' => $system_score,
                'issues' => $system_health['issues'] ?? [],
                'recommendations' => $system_health['recommendations'] ?? []
            ];
            
            // Update counters based on system health
            if ($system_health['status'] === 'healthy') {
                $diagnostic_results['passed_checks']++;
            } elseif ($system_health['status'] === 'warning') {
                $diagnostic_results['warning_checks']++;
            } else {
                $diagnostic_results['failed_checks']++;
                if ($system_score < 50) {
                    $diagnostic_results['critical_issues'][] = [
                        'title' => 'System Health Issues',
                        'description' => 'WordPress environment has configuration issues that may affect plugin performance.',
                        'auto_fix_available' => true,
                        'fix_id' => 'system_health'
                    ];
                }
            }
            
            wp_send_json_success($diagnostic_results);
            
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Full diagnostic encountered an error: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }

    /**
     * Run system health checks
     */
    private function runSystemHealthChecks(): array
    {
        $issues = [];
        $recommendations = [];
        $score = 100;
        
        // Check PHP version
        $php_version = PHP_VERSION;
        if (version_compare($php_version, '7.4', '<')) {
            $issues[] = 'PHP version is outdated (' . $php_version . '). Recommend PHP 7.4+';
            $score -= 20;
        } elseif (version_compare($php_version, '8.0', '<')) {
            $recommendations[] = 'Consider upgrading to PHP 8.0+ for better performance';
            $score -= 5;
        }
        
        // Check WordPress version
        $wp_version = get_bloginfo('version');
        if (version_compare($wp_version, '5.8', '<')) {
            $issues[] = 'WordPress version is outdated (' . $wp_version . '). Recommend WordPress 5.8+';
            $score -= 15;
        }
        
        // Check memory limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        $recommended_memory = 256 * 1024 * 1024; // 256MB
        if ($memory_limit < $recommended_memory) {
            $issues[] = 'Memory limit is low (' . size_format($memory_limit) . '). Recommend 256MB+';
            $score -= 15;
        }
        
        // Check max execution time
        $max_execution_time = ini_get('max_execution_time');
        if ($max_execution_time > 0 && $max_execution_time < 30) {
            $issues[] = 'Max execution time is low (' . $max_execution_time . 's). Recommend 30s+';
            $score -= 10;
        }
        
        // Check SSL/HTTPS
        if (!is_ssl()) {
            $issues[] = 'Site is not using HTTPS. SSL is required for secure API communication';
            $score -= 25;
        }
        
        // Check file permissions
        $upload_dir = wp_upload_dir();
        if (!is_writable($upload_dir['basedir'])) {
            $issues[] = 'Upload directory is not writable. May affect log file storage';
            $score -= 20;
        }
        
        // Check required extensions
        $required_extensions = ['curl', 'json', 'mbstring'];
        foreach ($required_extensions as $ext) {
            if (!extension_loaded($ext)) {
                $issues[] = "Required PHP extension '{$ext}' is not loaded";
                $score -= 15;
            }
        }
        
        // Check debug mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $recommendations[] = 'WP_DEBUG is enabled. Consider disabling in production';
            $score -= 5;
        }
        
        // Determine status
        $status = 'healthy';
        if ($score < 70) {
            $status = 'critical';
        } elseif ($score < 85) {
            $status = 'warning';
        }
        
        return [
            'status' => $status,
            'score' => max(0, $score),
            'issues' => $issues,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Calculate plugin load time
     */
    private function calculatePluginLoadTime(): string
    {
        // Get WordPress load time as a proxy for plugin load time
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $total_load_time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            
            // Estimate plugin portion (rough approximation)
            $active_plugins_count = count(get_option('active_plugins', []));
            if ($active_plugins_count > 0) {
                // Rough estimate: assume plugins take 20-40% of total load time
                $plugin_percentage = min(40, max(20, $active_plugins_count * 2));
                $estimated_plugin_time = round(($total_load_time * $plugin_percentage) / 100, 2);
                return $estimated_plugin_time . 'ms (est.)';
            }
        }
        
        return 'N/A';
    }

    /**
     * Calculate theme load time
     */
    private function calculateThemeLoadTime(): string
    {
        // Get current theme information
        $current_theme = wp_get_theme();
        $theme_name = $current_theme->get('Name');
        
        if (isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            $total_load_time = round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2);
            
            // Estimate theme portion based on complexity factors
            $complexity_score = 0;
            
            // Factor 1: Theme complexity based on template files
            $theme_dir = get_template_directory();
            if (is_dir($theme_dir)) {
                $php_files = glob($theme_dir . '/*.php');
                $template_count = count($php_files);
                
                // More templates = more complex theme
                if ($template_count > 50) {
                    $complexity_score += 3; // Complex theme
                } elseif ($template_count > 20) {
                    $complexity_score += 2; // Medium theme
                } else {
                    $complexity_score += 1; // Simple theme
                }
            }
            
            // Factor 2: Check for common performance-heavy features
            $functions_php = $theme_dir . '/functions.php';
            if (file_exists($functions_php)) {
                $functions_content = file_get_contents($functions_php);
                
                // Check for performance indicators
                if (strpos($functions_content, 'wp_enqueue_script') !== false) {
                    $complexity_score += 1; // Custom scripts
                }
                if (strpos($functions_content, 'wp_enqueue_style') !== false) {
                    $complexity_score += 1; // Custom styles
                }
                if (strpos($functions_content, 'add_action') !== false) {
                    $hook_count = substr_count($functions_content, 'add_action');
                    $complexity_score += min(2, $hook_count / 10); // Many hooks
                }
                if (strpos($functions_content, 'WP_Query') !== false || 
                    strpos($functions_content, 'get_posts') !== false) {
                    $complexity_score += 1; // Custom queries
                }
            }
            
            // Factor 3: Check for CSS/JS files
            $style_css = $theme_dir . '/style.css';
            if (file_exists($style_css)) {
                $css_size = filesize($style_css);
                if ($css_size > 100000) { // > 100KB
                    $complexity_score += 2;
                } elseif ($css_size > 50000) { // > 50KB
                    $complexity_score += 1;
                }
            }
            
            // Factor 4: Check if it's a known framework/parent theme
            $parent_theme = $current_theme->parent();
            if ($parent_theme) {
                $complexity_score += 1; // Child themes add overhead
            }
            
            // Common heavy themes detection
            $heavy_themes = ['avada', 'divi', 'enfold', 'betheme', 'x-theme', 'jupiter'];
            $theme_slug = strtolower($current_theme->get_stylesheet());
            foreach ($heavy_themes as $heavy_theme) {
                if (strpos($theme_slug, $heavy_theme) !== false) {
                    $complexity_score += 3;
                    break;
                }
            }
            
            // Calculate estimated theme load time
            // Base: 5-15% of total load time, increased by complexity
            $base_percentage = 8; // Base 8%
            $complexity_percentage = min(15, $complexity_score * 2); // Up to 15% more
            $theme_percentage = $base_percentage + $complexity_percentage;
            
            $estimated_theme_time = round(($total_load_time * $theme_percentage) / 100, 2);
            
            // Add context about theme complexity
            if ($complexity_score >= 8) {
                return $estimated_theme_time . 'ms (complex theme)';
            } elseif ($complexity_score >= 5) {
                return $estimated_theme_time . 'ms (medium theme)';
            } else {
                return $estimated_theme_time . 'ms (simple theme)';
            }
        }
        
        return 'N/A (timing unavailable)';
    }

    /**
     * Get API calls in the last 24 hours
     */
    private function getApiCalls24h(): string
    {
        global $wpdb;
        
        // Check for API call logs in WordPress options or custom table
        $api_calls_option = get_option('ctm_api_calls_24h', null);
        
        if ($api_calls_option !== null && is_array($api_calls_option)) {
            // Clean old entries (older than 24 hours)
            $current_time = time();
            $twenty_four_hours_ago = $current_time - (24 * 60 * 60);
            
            $recent_calls = array_filter($api_calls_option, function($timestamp) use ($twenty_four_hours_ago) {
                return $timestamp >= $twenty_four_hours_ago;
            });
            
            // Update the option with cleaned data
            update_option('ctm_api_calls_24h', $recent_calls);
            
            $call_count = count($recent_calls);
            
            if ($call_count === 0) {
                return '0 calls (24h)';
            } elseif ($call_count === 1) {
                return '1 call (24h)';
            } else {
                return number_format($call_count) . ' calls (24h)';
            }
        }
        
        // Fallback: Try to estimate from debug logs
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        if (file_exists($debug_log) && is_readable($debug_log)) {
            $file_size = filesize($debug_log);
            if ($file_size > 0) {
                $handle = fopen($debug_log, 'r');
                if ($handle) {
                    // Read last 50KB for recent API calls
                    $read_size = min(51200, $file_size);
                    fseek($handle, max(0, $file_size - $read_size));
                    $log_content = fread($handle, $read_size);
                    fclose($handle);
                    
                    // Count CallTrackingMetrics API calls in recent logs
                    $api_call_patterns = [
                        'calltrackingmetrics.com',
                        'CTM API',
                        'api.calltrackingmetrics',
                        'CallTrackingMetrics API'
                    ];
                    
                    $api_calls = 0;
                    foreach ($api_call_patterns as $pattern) {
                        $api_calls += substr_count(strtolower($log_content), strtolower($pattern));
                    }
                    
                    if ($api_calls > 0) {
                        return $api_calls . ' calls (est. from logs)';
                    }
                }
            }
        }
        
        // Fallback: Check if API credentials are configured
        $api_key = get_option('ctm_api_key');
        $api_secret = get_option('ctm_api_secret');
        
        if ($api_key && $api_secret) {
            // API is configured but no tracking data available
            return '0 calls (tracking not enabled)';
        }
        
        return 'API not configured';
    }

    /**
     * Get average API response time
     */
    private function getApiResponseTime(): string
    {
        // Check for stored API response times from recent calls
        $response_times = get_option('ctm_api_response_times', []);
        
        if (!empty($response_times) && is_array($response_times)) {
            // Clean old entries (older than 24 hours)
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $recent_times = [];
            
            foreach ($response_times as $timestamp => $response_time) {
                if ($timestamp >= $twenty_four_hours_ago) {
                    $recent_times[] = $response_time;
                }
            }
            
            if (!empty($recent_times)) {
                $avg_response_time = round(array_sum($recent_times) / count($recent_times), 2);
                $count = count($recent_times);
                
                // Add performance context
                if ($avg_response_time < 200) {
                    return $avg_response_time . 'ms (excellent)';
                } elseif ($avg_response_time < 500) {
                    return $avg_response_time . 'ms (good)';
                } elseif ($avg_response_time < 1000) {
                    return $avg_response_time . 'ms (fair)';
                } else {
                    return $avg_response_time . 'ms (slow)';
                }
            }
        }
        
        // Fallback: Test API response time now
        $api_key = get_option('ctm_api_key');
        $api_secret = get_option('ctm_api_secret');
        
        if ($api_key && $api_secret) {
            $start_time = microtime(true);
            
            // Make a simple API call to test response time
            $response = wp_remote_head('https://api.calltrackingmetrics.com/api/v1/accounts', [
                'timeout' => 10,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($api_key . ':' . $api_secret),
                    'Accept' => 'application/json',
                ],
            ]);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if (!is_wp_error($response)) {
                $http_code = wp_remote_retrieve_response_code($response);
                
                if ($http_code >= 200 && $http_code < 400) {
                    // Store this response time for future reference
                    $this->storeApiResponseTime($response_time);
                    
                    // Add performance context
                    if ($response_time < 200) {
                        return $response_time . 'ms (live test, excellent)';
                    } elseif ($response_time < 500) {
                        return $response_time . 'ms (live test, good)';
                    } elseif ($response_time < 1000) {
                        return $response_time . 'ms (live test, fair)';
                    } else {
                        return $response_time . 'ms (live test, slow)';
                    }
                } else {
                    return 'Error: HTTP ' . $http_code;
                }
            } else {
                return 'Error: ' . $response->get_error_message();
            }
        }
        
        return 'API not configured';
    }

    /**
     * Store API response time for monitoring
     */
    private function storeApiResponseTime(float $responseTime): void
    {
        try {
            $response_times = get_option('ctm_api_response_times', []);
            
            if (!is_array($response_times)) {
                $response_times = [];
            }
            
            // Add current response time with timestamp
            $response_times[time()] = $responseTime;
            
            // Clean old entries (older than 24 hours)
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $response_times = array_filter($response_times, function($timestamp) use ($twenty_four_hours_ago) {
                return $timestamp >= $twenty_four_hours_ago;
            }, ARRAY_FILTER_USE_KEY);
            
            // Limit to prevent excessive data storage (keep last 100 response times max)
            if (count($response_times) > 100) {
                $response_times = array_slice($response_times, -100, null, true);
            }
            
            update_option('ctm_api_response_times', $response_times);
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting API monitoring
            error_log('CTM API Response Time Storage Error: ' . $e->getMessage());
        }
    }

    /**
     * Get frontend queries
     */
    private function getFrontendQueries(): string
    {
        global $wpdb;
        
        if (is_admin()) {
            // For admin pages, show total queries but indicate it's admin context
            $total_queries = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
            return $total_queries . ' (Admin)';
        } else {
            // For frontend, show actual queries
            $total_queries = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
            return $total_queries . ' (Frontend)';
        }
    }

    /**
     * Get Network I/O metrics
     */
    private function getNetworkIO(): string
    {
        // Test network connectivity and speed
        $start_time = microtime(true);
        
        // Use WordPress HTTP API for better compatibility
        $response = wp_remote_head('https://api.calltrackingmetrics.com/api/v1/accounts', [
            'timeout' => 5,
            'sslverify' => true
        ]);
        
        if (is_wp_error($response)) {
            return 'Error: ' . $response->get_error_message();
        }
        
        $response_time = round((microtime(true) - $start_time) * 1000, 2);
        $response_code = wp_remote_retrieve_response_code($response);
        
        if ($response_code >= 200 && $response_code < 300) {
            return $response_time . 'ms (Good)';
        } elseif ($response_code >= 400) {
            return $response_time . 'ms (HTTP ' . $response_code . ')';
        }
        
        return $response_time . 'ms';
    }

    /**
     * Get Active Sessions
     */
    private function getActiveSessions(): string
    {
        global $wpdb;
        
        // Count active user sessions from WordPress
        $logged_in_users = count_users();
        $total_users = $logged_in_users['total_users'];
        
        // Check for active sessions in the last hour
        $active_sessions = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE meta_key LIKE %s 
             AND meta_value > %d",
            'session_tokens',
            time() - 3600 // Last hour
        ));
        
        if ($active_sessions > 0) {
            return $active_sessions . ' active';
        } elseif ($total_users > 0) {
            return '0 active (' . $total_users . ' total users)';
        }
        
        return 'No sessions tracked';
    }

    /**
     * Get Error Rate
     */
    private function getErrorRate(): string
    {
        // Check WordPress debug log for recent errors
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        
        if (!file_exists($debug_log) || !is_readable($debug_log)) {
            return 'No debug log found';
        }
        
        $file_size = filesize($debug_log);
        if ($file_size === 0) {
            return '0 errors (Clean log)';
        }
        
        // Read last 10KB of log file for recent errors
        $handle = fopen($debug_log, 'r');
        if ($handle) {
            $read_size = min(10240, $file_size); // 10KB or file size
            fseek($handle, max(0, $file_size - $read_size));
            $log_content = fread($handle, $read_size);
            fclose($handle);
            
            // Count error occurrences in recent log
            $error_count = substr_count($log_content, '[error]') + 
                          substr_count($log_content, 'Fatal error') + 
                          substr_count($log_content, 'PHP Warning') +
                          substr_count($log_content, 'PHP Notice');
            
            if ($error_count === 0) {
                return '0 recent errors';
            } elseif ($error_count < 5) {
                return $error_count . ' recent errors (Low)';
            } elseif ($error_count < 20) {
                return $error_count . ' recent errors (Medium)';
            } else {
                return $error_count . ' recent errors (High)';
            }
        }
        
        return 'Cannot read debug log';
    }

    /**
     * Get slow queries from debug.log
     */
    private function getSlowQueries(): string
    {
        global $wpdb;
        
        // First check if SAVEQUERIES is enabled for real-time analysis
        if (defined('SAVEQUERIES') && constant('SAVEQUERIES') && isset($wpdb->queries)) {
            $slow_count = 0;
            $total_slow_time = 0;
            
            foreach ($wpdb->queries as $query) {
                $query_time = floatval($query[1]);
                if ($query_time > 0.1) { // Consider queries > 100ms as slow
                    $slow_count++;
                    $total_slow_time += $query_time;
                }
            }
            
            if ($slow_count > 0) {
                $avg_slow_time = round($total_slow_time / $slow_count * 1000, 2);
                return $slow_count . ' slow queries (avg: ' . $avg_slow_time . 'ms)';
            } else {
                return '0 slow queries';
            }
        }
        
        // Fallback to debug log analysis
        $debug_log = WP_CONTENT_DIR . '/debug.log';
        if (!file_exists($debug_log) || !is_readable($debug_log)) {
            return 'N/A (Enable SAVEQUERIES for analysis)';
        }

        $file_size = filesize($debug_log);
        if ($file_size === 0) {
            return '0 slow queries (Clean log)';
        }

        $handle = fopen($debug_log, 'r');
        if ($handle) {
            $read_size = min(10240, $file_size);
            fseek($handle, max(0, $file_size - $read_size));
            $log_content = fread($handle, $read_size);
            fclose($handle);

            // Count slow query patterns
            $slow_queries = substr_count($log_content, 'slow query') + 
                          substr_count($log_content, 'Slow query') +
                          substr_count($log_content, 'Query took');
             
            return $slow_queries . ' slow queries (from log)';
        }
        
        return 'N/A (Cannot analyze queries)';
    }

    /**
     * Get cache hits from cache plugins
     */
    private function getCacheHits(): string
    {
        // Priority 1: Try to get actual cache hit statistics
        
        // Method 1: Standard wp_cache_get_stats() function
        if (function_exists('wp_cache_get_stats')) {
            $stats = \wp_cache_get_stats();
            if (isset($stats['hits']) && is_numeric($stats['hits'])) {
                $hits = intval($stats['hits']);
                if ($hits > 0) {
                    return number_format($hits) . ' hits';
                } else {
                    return '0 hits (cache active)';
                }
            }
        }
        
        // Method 2: Redis Object Cache statistics
        if (class_exists('Redis') && function_exists('wp_cache_get_stats')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'get_stats')) {
                    $cache_stats = $wp_object_cache->get_stats();
                    if (isset($cache_stats['hits'])) {
                        return number_format($cache_stats['hits']) . ' hits (Redis)';
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 3: Memcached statistics
        if (class_exists('Memcached') && function_exists('wp_cache_get_stats')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'getStats')) {
                    $memcached_stats = $wp_object_cache->getStats();
                    if (is_array($memcached_stats)) {
                        foreach ($memcached_stats as $server_stats) {
                            if (isset($server_stats['get_hits'])) {
                                return number_format($server_stats['get_hits']) . ' hits (Memcached)';
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 4: W3 Total Cache specific statistics
        if (class_exists('W3_Config')) {
            try {
                if (function_exists('w3_instance') && function_exists('W3TC')) {
                    $w3tc_config = \w3_instance('W3_Config');
                    if (method_exists($w3tc_config, 'get_stats')) {
                        $w3_stats = $w3tc_config->get_stats();
                        if (isset($w3_stats['cache_hits'])) {
                            return number_format($w3_stats['cache_hits']) . ' hits (W3TC)';
                        }
                    }
                }
                return 'W3TC active (stats N/A)';
            } catch (\Exception $e) {
                return 'W3TC active (stats error)';
            }
        }
        
        // Method 5: WP Rocket cache detection
        if (class_exists('WP_Rocket\Engine\Cache\WPCache')) {
            // WP Rocket doesn't expose hit statistics easily
            return 'WP Rocket active (stats N/A)';
        }
        
        // Method 6: WP Super Cache
        if (function_exists('wp_cache_is_enabled') && \wp_cache_is_enabled()) {
            // Check if we can get any statistics from WP Super Cache
            global $wp_super_cache_stats;
            if (isset($wp_super_cache_stats) && is_array($wp_super_cache_stats)) {
                if (isset($wp_super_cache_stats['cache_hits'])) {
                    return number_format($wp_super_cache_stats['cache_hits']) . ' hits (WP Super Cache)';
                }
            }
            return 'WP Super Cache active';
        }
        
        // Method 7: LiteSpeed Cache
        if (class_exists('LiteSpeed\Core') || defined('LSCWP_V')) {
            return 'LiteSpeed Cache active (stats N/A)';
        }
        
        // Method 8: WP Fastest Cache
        if (class_exists('WpFastestCache')) {
            return 'WP Fastest Cache active (stats N/A)';
        }
        
        // Method 9: Cachify
        if (class_exists('Cachify')) {
            return 'Cachify active (stats N/A)';
        }
        
        // Method 10: External object cache detection
        if (\wp_using_ext_object_cache()) {
            return 'External object cache active';
        }
        
        // Method 11: Basic object cache detection
        if (class_exists('Redis') || class_exists('Memcached')) {
            return 'Object cache available';
        }
        
        return 'No caching detected';
    }

    /**
     * Get cache misses from cache plugins
     */
    private function getCacheMisses(): string
    {
        // Priority 1: Try to get actual cache miss statistics with hit ratios
        
        // Method 1: Standard wp_cache_get_stats() function
        if (function_exists('wp_cache_get_stats')) {
            $stats = \wp_cache_get_stats();
            if (isset($stats['misses']) && is_numeric($stats['misses'])) {
                $hits = isset($stats['hits']) && is_numeric($stats['hits']) ? intval($stats['hits']) : 0;
                $misses = intval($stats['misses']);
                $total = $hits + $misses;
                
                if ($total > 0) {
                    $hit_ratio = round(($hits / $total) * 100, 1);
                    return number_format($misses) . ' misses (' . $hit_ratio . '% hit rate)';
                } else if ($misses > 0) {
                    return number_format($misses) . ' misses';
                } else {
                    return '0 misses (cache active)';
                }
            }
        }
        
        // Method 2: Redis Object Cache statistics
        if (class_exists('Redis')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'get_stats')) {
                    $cache_stats = $wp_object_cache->get_stats();
                    if (isset($cache_stats['misses']) && isset($cache_stats['hits'])) {
                        $hits = intval($cache_stats['hits']);
                        $misses = intval($cache_stats['misses']);
                        $total = $hits + $misses;
                        
                        if ($total > 0) {
                            $hit_ratio = round(($hits / $total) * 100, 1);
                            return number_format($misses) . ' misses (' . $hit_ratio . '% hit rate, Redis)';
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 3: Memcached statistics
        if (class_exists('Memcached')) {
            try {
                global $wp_object_cache;
                if (isset($wp_object_cache) && method_exists($wp_object_cache, 'getStats')) {
                    $memcached_stats = $wp_object_cache->getStats();
                    if (is_array($memcached_stats)) {
                        foreach ($memcached_stats as $server_stats) {
                            if (isset($server_stats['get_misses']) && isset($server_stats['get_hits'])) {
                                $hits = intval($server_stats['get_hits']);
                                $misses = intval($server_stats['get_misses']);
                                $total = $hits + $misses;
                                
                                if ($total > 0) {
                                    $hit_ratio = round(($hits / $total) * 100, 1);
                                    return number_format($misses) . ' misses (' . $hit_ratio . '% hit rate, Memcached)';
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Continue to next method
            }
        }
        
        // Method 4: Check for caching plugins without detailed stats
        $cache_plugins = [];
        
        if (class_exists('WP_Rocket\Engine\Cache\WPCache')) {
            $cache_plugins[] = 'WP Rocket';
        }
        if (class_exists('W3_Config')) {
            $cache_plugins[] = 'W3TC';
        }
        if (function_exists('wp_cache_is_enabled') && \wp_cache_is_enabled()) {
            $cache_plugins[] = 'WP Super Cache';
        }
        if (class_exists('LiteSpeed\Core') || defined('LSCWP_V')) {
            $cache_plugins[] = 'LiteSpeed';
        }
        if (class_exists('WpFastestCache')) {
            $cache_plugins[] = 'WP Fastest Cache';
        }
        if (class_exists('Cachify')) {
            $cache_plugins[] = 'Cachify';
        }
        if (\wp_using_ext_object_cache()) {
            $cache_plugins[] = 'External Object Cache';
        }
        
        if (!empty($cache_plugins)) {
            $plugin_list = implode(', ', $cache_plugins);
            return 'Cache active (' . $plugin_list . ') - stats N/A';
        }
        
        return 'No cache miss tracking';
    }

    /**
     * AJAX: Security Vulnerability Scan
     */
    public function ajaxSecurityScan(): void
    {
        check_ajax_referer('ctm_security_scan', 'nonce');
        $score = 100;
        $vulnerabilities = [];
        $recommendations = [];
        $details = [];

        // 1. Security Headers
        $headers = [
            'Strict-Transport-Security',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'Referrer-Policy',
            'Content-Security-Policy',
            'Permissions-Policy',
        ];
        $missing_headers = [];
        foreach ($headers as $header) {
            if (!array_key_exists($header, headers_list())) {
                $missing_headers[] = $header;
            }
        }
        if (!empty($missing_headers)) {
            $score -= 10;
            $vulnerabilities[] = [
                'title' => 'Missing security headers',
                'description' => implode(', ', $missing_headers),
                'severity' => 'high'
            ];
            $recommendations[] = 'Add recommended security headers to your web server configuration.';
            $details['missing_headers'] = $missing_headers;
        }

        // 2. File Permissions (wp-config.php, .htaccess, uploads)
        $wp_config = ABSPATH . 'wp-config.php';
        $htaccess = ABSPATH . '.htaccess';
        $uploads = wp_get_upload_dir()['basedir'];
        if (file_exists($wp_config) && substr(sprintf('%o', fileperms($wp_config)), -3) > 644) {
            $score -= 10;
            $vulnerabilities[] = [
                'title' => 'wp-config.php permissions are too loose',
                'description' => 'wp-config.php permissions should be 640 or 600.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Set wp-config.php permissions to 640 or 600.';
        }
        if (file_exists($htaccess) && substr(sprintf('%o', fileperms($htaccess)), -3) > 644) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => '.htaccess permissions are too loose',
                'description' => '.htaccess permissions should be 644.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Set .htaccess permissions to 644.';
        }
        if (is_dir($uploads) && substr(sprintf('%o', fileperms($uploads)), -3) > 755) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => 'Uploads directory permissions are too loose',
                'description' => 'Uploads directory permissions should be 755.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Set uploads directory permissions to 755.';
        }

        // 3. wp-config.php Security
        $wp_config_content = file_exists($wp_config) ? file_get_contents($wp_config) : '';
        if ($wp_config_content && strpos($wp_config_content, 'DISALLOW_FILE_EDIT') === false) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => 'DISALLOW_FILE_EDIT is not set',
                'description' => 'Add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Add define(\'DISALLOW_FILE_EDIT\', true); to wp-config.php.';
        }
        if ($wp_config_content && strpos($wp_config_content, 'FORCE_SSL_ADMIN') === false) {
            $score -= 5;
            $vulnerabilities[] = [
                'title' => 'FORCE_SSL_ADMIN is not set',
                'description' => 'Add define(\'FORCE_SSL_ADMIN\', true); to wp-config.php.',
                'severity' => 'medium'
            ];
            $recommendations[] = 'Add define(\'FORCE_SSL_ADMIN\', true); to wp-config.php.';
        }

        // 4. Plugin Vulnerability Check (WordPress.org API, basic)
        $plugins = get_plugins();
        foreach ($plugins as $plugin_file => $plugin_data) {
            // Check for known vulnerable plugins (example: hardcoded, real implementation would use an API)
            $vuln_plugins = [
                'hello.php' => 'Hello Dolly (example)',
            ];
            if (isset($vuln_plugins[$plugin_file])) {
                $score -= 20;
                $vulnerabilities[] = [
                    'title' => 'Vulnerable plugin detected',
                    'description' => $vuln_plugins[$plugin_file],
                    'severity' => 'high'
                ];
                $recommendations[] = 'Deactivate or remove vulnerable plugins.';
            }
        }

        // Clamp score
        $score = max(0, min(100, $score));

        wp_send_json_success([
            'results' => [
                'security_score' => $score,
                'vulnerabilities' => $vulnerabilities,
                'recommendations' => $recommendations,
                'details' => $details
            ]
        ]);
    }

    /**
     * AJAX: Performance Analysis
     */
    public function ajaxPerformanceAnalysis(): void
    {
        check_ajax_referer('ctm_performance_analysis', 'nonce');
        global $wpdb;
        $metrics = [];
        $optimizations = [];
        $score = 100;

        // 1. Page Load Time (approximate)
        $metrics['load_time'] = isset($_SERVER['REQUEST_TIME_FLOAT']) ? round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) : null;
        if ($metrics['load_time'] > 2000) {
            $score -= 20;
            $optimizations[] = 'Optimize page load time (reduce to under 2s).';
        }

        // 2. Database Queries
        $metrics['db_queries'] = isset($wpdb->num_queries) ? $wpdb->num_queries : get_num_queries();
        if ($metrics['db_queries'] > 100) {
            $score -= 10;
            $optimizations[] = 'Reduce the number of database queries.';
        }

        // 3. Memory Usage
        $metrics['memory_usage'] = round(memory_get_usage(true) / 1024 / 1024, 2); // MB
        if ($metrics['memory_usage'] > 128) {
            $score -= 10;
            $optimizations[] = 'Optimize memory usage (keep under 128MB if possible).';
        }

        // 4. Cache Hit Rate (basic, if available)
        $cache_hit_rate = null;
        if (function_exists('wp_cache_get_stats')) {
            $stats = wp_cache_get_stats();
            if (isset($stats['hits']) && isset($stats['misses'])) {
                $total = $stats['hits'] + $stats['misses'];
                $cache_hit_rate = $total > 0 ? round($stats['hits'] / $total * 100, 1) : null;
            }
        }
        $metrics['cache_hit_rate'] = ($cache_hit_rate !== null) ? $cache_hit_rate : 'N/A';
        if (is_numeric($cache_hit_rate) && $cache_hit_rate < 80) {
            $score -= 10;
            $optimizations[] = 'Improve cache hit rate (target 80%+).';
        }

        // 5. Plugin Load Time
        $metrics['plugin_load_time'] = method_exists($this, 'calculatePluginLoadTime') ? $this->calculatePluginLoadTime() : 'N/A';
        if (is_numeric($metrics['plugin_load_time']) && $metrics['plugin_load_time'] > 500) {
            $score -= 10;
            $optimizations[] = 'Reduce plugin load time.';
        }

        // 6. Theme Load Time
        $metrics['theme_load_time'] = method_exists($this, 'calculateThemeLoadTime') ? $this->calculateThemeLoadTime() : 'N/A';
        if (is_numeric($metrics['theme_load_time']) && $metrics['theme_load_time'] > 500) {
            $score -= 10;
            $optimizations[] = 'Reduce theme load time.';
        }

        // Clamp score
        $score = max(0, min(100, $score));

        wp_send_json_success([
            'results' => [
                'performance_score' => $score,
                'metrics' => $metrics,
                'optimizations' => $optimizations
            ]
        ]);
    }
} 