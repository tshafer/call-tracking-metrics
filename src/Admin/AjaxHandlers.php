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
} 