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
        
        // Check 1: API Credentials
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if ($apiKey && $apiSecret) {
            try {
                $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                if ($accountInfo && isset($accountInfo['account'])) {
                    $checks[] = ['name' => 'API Credentials', 'status' => 'pass', 'message' => 'Valid'];
                } else {
                    $checks[] = ['name' => 'API Credentials', 'status' => 'fail', 'message' => 'Invalid'];
                }
            } catch (\Exception $e) {
                $checks[] = ['name' => 'API Credentials', 'status' => 'fail', 'message' => 'Connection failed'];
            }
        } else {
            $checks[] = ['name' => 'API Credentials', 'status' => 'fail', 'message' => 'Not configured'];
        }
        
        // Check 2: WordPress Version
        $wp_version = get_bloginfo('version');
        if (version_compare($wp_version, '5.0', '>=')) {
            $checks[] = ['name' => 'WordPress Version', 'status' => 'pass', 'message' => $wp_version];
        } else {
            $checks[] = ['name' => 'WordPress Version', 'status' => 'warning', 'message' => $wp_version . ' (outdated)'];
        }
        
        // Check 3: PHP Version
        if (version_compare(PHP_VERSION, '7.4', '>=')) {
            $checks[] = ['name' => 'PHP Version', 'status' => 'pass', 'message' => PHP_VERSION];
        } else {
            $checks[] = ['name' => 'PHP Version', 'status' => 'warning', 'message' => PHP_VERSION . ' (outdated)'];
        }
        
        // Check 4: cURL Extension
        if (function_exists('curl_init')) {
            $checks[] = ['name' => 'cURL Extension', 'status' => 'pass', 'message' => 'Available'];
        } else {
            $checks[] = ['name' => 'cURL Extension', 'status' => 'fail', 'message' => 'Missing'];
        }
        
        // Check 5: SSL Support
        if (extension_loaded('openssl')) {
            $checks[] = ['name' => 'SSL Support', 'status' => 'pass', 'message' => 'Available'];
        } else {
            $checks[] = ['name' => 'SSL Support', 'status' => 'fail', 'message' => 'Missing'];
        }
        
        // Check 6: Memory Limit
        $memory_limit = wp_convert_hr_to_bytes(ini_get('memory_limit'));
        if ($memory_limit >= 128 * 1024 * 1024) { // 128MB
            $checks[] = ['name' => 'Memory Limit', 'status' => 'pass', 'message' => size_format($memory_limit)];
        } else {
            $checks[] = ['name' => 'Memory Limit', 'status' => 'warning', 'message' => size_format($memory_limit) . ' (low)'];
        }
        
        // Check 7: Form Plugins
        $cf7_active = class_exists('WPCF7_ContactForm');
        $gf_active = class_exists('GFAPI');
        
        if ($cf7_active || $gf_active) {
            $active_forms = [];
            if ($cf7_active) $active_forms[] = 'Contact Form 7';
            if ($gf_active) $active_forms[] = 'Gravity Forms';
            $checks[] = ['name' => 'Form Plugins', 'status' => 'pass', 'message' => implode(', ', $active_forms)];
        } else {
            $checks[] = ['name' => 'Form Plugins', 'status' => 'warning', 'message' => 'None detected'];
        }
        
        wp_send_json_success(['checks' => $checks]);
    }

    /**
     * AJAX: Get Performance Metrics
     */
    public function ajaxGetPerformanceMetrics(): void
    {
        check_ajax_referer('ctm_get_performance_metrics', 'nonce');
        
        wp_send_json_success([
            'memory_usage' => size_format(memory_get_usage(true)),
            'peak_memory' => size_format(memory_get_peak_usage(true)),
            'db_queries' => get_num_queries(),
            'timestamp' => current_time('mysql')
        ]);
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
        
        return [
            'title' => 'API Credentials Analysis',
            'description' => 'Checking the validity and configuration of your CallTrackingMetrics API credentials.',
            'status' => empty($issues) ? 'healthy' : 'issues_found',
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
        
        return [
            'title' => 'Form Integration Analysis',
            'description' => 'Checking the configuration and setup of form plugin integrations.',
            'status' => empty($issues) ? 'healthy' : 'issues_found',
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
        
        // Test actual API connectivity
        try {
            $response = wp_remote_get('https://api.calltrackingmetrics.com/api/v1/ping', [
                'timeout' => 10,
                'sslverify' => true
            ]);
            
            if (is_wp_error($response)) {
                $issues[] = 'Cannot connect to CallTrackingMetrics API: ' . $response->get_error_message();
                $solutions[] = 'Check your server\'s internet connection';
                $solutions[] = 'Verify firewall settings allow HTTPS connections';
                $solutions[] = 'Contact your hosting provider if connection issues persist';
            } elseif (wp_remote_retrieve_response_code($response) >= 400) {
                $issues[] = 'CallTrackingMetrics API returned error: ' . wp_remote_retrieve_response_code($response);
                $solutions[] = 'Check CallTrackingMetrics service status';
                $solutions[] = 'Try again later if this is a temporary issue';
            }
        } catch (\Exception $e) {
            $issues[] = 'Network connectivity test failed: ' . $e->getMessage();
            $solutions[] = 'Check your server\'s network configuration';
            $solutions[] = 'Contact your hosting provider for network troubleshooting';
        }
        
        return [
            'title' => 'Network Connectivity Analysis',
            'description' => 'Testing network connectivity and ability to reach CallTrackingMetrics API.',
            'status' => empty($issues) ? 'healthy' : 'issues_found',
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
        
        return [
            'title' => 'Plugin Conflicts Analysis',
            'description' => 'Checking for potential conflicts with other plugins and system settings.',
            'status' => empty($issues) ? 'healthy' : 'issues_found',
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
} 