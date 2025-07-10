<?php
namespace CTM\Admin;

/**
 * Handles admin options and settings for CallTrackingMetrics.
 */
class Options
{
    /**
     * Register plugin settings with WordPress.
     */
    public function registerSettings(): void
    {
        register_setting("call-tracking-metrics", "ctm_api_key");
        register_setting("call-tracking-metrics", "ctm_api_secret");
        register_setting("call-tracking-metrics", "ctm_api_active_key");
        register_setting("call-tracking-metrics", "ctm_api_active_secret");
        register_setting("call-tracking-metrics", "ctm_api_auth_account");
        register_setting("call-tracking-metrics", "call_track_account_script");
        register_setting("call-tracking-metrics", "ctm_api_dashboard_enabled");
        register_setting("call-tracking-metrics", "ctm_api_tracking_enabled");
        register_setting("call-tracking-metrics", "ctm_api_cf7_enabled");
        register_setting("call-tracking-metrics", "ctm_api_gf_enabled");
        register_setting("call-tracking-metrics", "ctm_api_cf7_logs");
        register_setting("call-tracking-metrics", "ctm_api_gf_logs");
    }

    /**
     * Register the settings page in the WordPress admin menu.
     */
    public function registerSettingsPage(): void
    {
        add_options_page(
            'CallTrackingMetrics',
            'CallTrackingMetrics',
            'manage_options',
            'call-tracking-metrics',
            [$this, 'renderSettingsPage']
        );
    }

    /**
     * Render the plugin settings page.
     */
    public function renderSettingsPage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleFormSubmission();
        }
        
        // Check API connection status for tab visibility
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $apiStatus = 'not_tested';
        
        if ($apiKey && $apiSecret) {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
        }
        
        $notices = [];
        $cf7_installed = class_exists('WPCF7_ContactForm');
        $gf_installed = class_exists('GFAPI');
        if (!$cf7_installed && !get_option('ctm_cf7_notice_dismissed', false)) {
            $cf7_url = admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term');
            ob_start();
            $this->renderView('notice-cf7', compact('cf7_url'));
            $notices[] = ob_get_clean();
        }
        if (!$gf_installed && !get_option('ctm_gf_notice_dismissed', false)) {
            $gf_url = 'https://www.gravityforms.com/';
            ob_start();
            $this->renderView('notice-gf', compact('gf_url'));
            $notices[] = ob_get_clean();
        }
        $active_tab = $_GET['tab'] ?? 'general';
        error_log('Active tab: ' . $active_tab);
        switch ($active_tab) {
            case 'logs':
                $tab_content = $this->getLogsTabContent();
                break;
            case 'mapping':
                $tab_content = $this->getMappingTabContent();
                break;
            case 'api':
                $tab_content = $this->getApiTabContent();
                break;
            case 'documentation':
                $tab_content = $this->getDocumentationTabContent();
                break;
            case 'debug':
                $tab_content = $this->getDebugTabContent();
                break;
            case 'general':
            default:
                $tab_content = $this->getGeneralTabContent();
                break;
        }
        error_log('About to render settings-page view');
        $this->renderView('settings-page', compact('notices', 'active_tab', 'tab_content', 'apiStatus'));
        error_log('Finished rendering settings-page view');
    }

    private function handleFormSubmission(): void
    {
        // Handle debug mode toggle
        if (isset($_POST['toggle_debug'])) {
            $current = get_option('ctm_debug_enabled', false);
            $new_value = !$current;
            update_option('ctm_debug_enabled', $new_value);
            
            $this->logActivity(
                $new_value ? 'Debug mode enabled' : 'Debug mode disabled',
                'debug',
                ['previous_state' => $current, 'new_state' => $new_value]
            );
            
            wp_redirect(add_query_arg(['tab' => 'debug'], wp_get_referer()));
            exit;
        }
        
        // Handle clear debug log
        if (isset($_POST['clear_debug_log'])) {
            $this->clearAllLogs();
            $this->logActivity('All debug logs cleared', 'system');
            wp_redirect(add_query_arg(['tab' => 'debug'], wp_get_referer()));
            exit;
        }
        
        // Handle clear single day log
        if (isset($_POST['clear_single_log']) && !empty($_POST['log_date'])) {
            $log_date = sanitize_text_field($_POST['log_date']);
            $this->clearDayLog($log_date);
            $this->logActivity("Log cleared for date: {$log_date}", 'system');
            wp_redirect(add_query_arg(['tab' => 'debug'], wp_get_referer()));
            exit;
        }
        
        // Handle email log
        if (isset($_POST['email_log']) && !empty($_POST['log_date']) && !empty($_POST['email_to'])) {
            $log_date = sanitize_text_field($_POST['log_date']);
            $email_to = sanitize_email($_POST['email_to']);
            $this->emailLog($log_date, $email_to);
            $this->logActivity("Log emailed for date: {$log_date} to: {$email_to}", 'system');
            wp_redirect(add_query_arg(['tab' => 'debug'], wp_get_referer()));
            exit;
        }
        
        // Handle log retention settings
        if (isset($_POST['update_log_settings'])) {
            $retention_days = (int) ($_POST['log_retention_days'] ?? 7);
            $retention_days = max(1, min(365, $retention_days)); // Between 1-365 days
            
            update_option('ctm_log_retention_days', $retention_days);
            update_option('ctm_log_auto_cleanup', isset($_POST['log_auto_cleanup']));
            update_option('ctm_log_email_notifications', isset($_POST['log_email_notifications']));
            update_option('ctm_log_notification_email', sanitize_email($_POST['log_notification_email'] ?? ''));
            
            $this->logActivity("Log settings updated - Retention: {$retention_days} days", 'system');
            wp_redirect(add_query_arg(['tab' => 'debug'], wp_get_referer()));
            exit;
        }
        
        // Handle general settings
        if (isset($_POST['ctm_api_key'])) {
            $this->saveGeneralSettings();
        }
    }

    private function saveGeneralSettings(): void
    {
        $apiKey = sanitize_text_field($_POST['ctm_api_key']);
        $apiSecret = sanitize_text_field($_POST['ctm_api_secret']);
        $trackingEnabled = isset($_POST['ctm_api_tracking_enabled']);
        $cf7Enabled = isset($_POST['ctm_api_cf7_enabled']);
        $gfEnabled = isset($_POST['ctm_api_gf_enabled']);
        $dashboardEnabled = isset($_POST['ctm_api_dashboard_enabled']);
        
        // Log configuration changes
        $old_key = get_option('ctm_api_key');
        if ($old_key !== $apiKey) {
            $this->logActivity('API Key updated', 'config', [
                'old_key_partial' => substr($old_key, 0, 8) . '...',
                'new_key_partial' => substr($apiKey, 0, 8) . '...'
            ]);
        }
        
        update_option('ctm_api_key', $apiKey);
        update_option('ctm_api_secret', $apiSecret);
        update_option('ctm_api_tracking_enabled', $trackingEnabled);
        update_option('ctm_api_cf7_enabled', $cf7Enabled);
        update_option('ctm_api_gf_enabled', $gfEnabled);
        update_option('ctm_api_dashboard_enabled', $dashboardEnabled);
        
        if (!empty($_POST['call_track_account_script'])) {
            update_option('call_track_account_script', wp_kses_post($_POST['call_track_account_script']));
        }
        
        $this->logActivity('General settings saved', 'config', [
            'tracking_enabled' => $trackingEnabled,
            'cf7_enabled' => $cf7Enabled,
            'gf_enabled' => $gfEnabled,
            'dashboard_enabled' => $dashboardEnabled
        ]);
        
        wp_redirect(add_query_arg(['tab' => 'general'], wp_get_referer()));
        exit;
    }

    private function renderView($view, $vars = []) {
        $viewPath = plugin_dir_path(__FILE__) . '../../views/' . $view . '.php';
        error_log('Trying to load view: ' . $viewPath);
        if (!file_exists($viewPath)) {
            error_log('View not found: ' . $viewPath);
            echo "<div style='color:red'>View not found: $viewPath</div>";
            return;
        }
        error_log('Including view: ' . $viewPath);
        extract($vars);
        include $viewPath;
    }

    private function getGeneralTabContent(): string
    {
        error_log('getGeneralTabContent called');
        $apiKey = get_option('ctm_api_key'); error_log('apiKey: ' . var_export($apiKey, true));
        $apiSecret = get_option('ctm_api_secret'); error_log('apiSecret: ' . var_export($apiSecret, true));
        $accountId = get_option('ctm_api_auth_account'); error_log('accountId: ' . var_export($accountId, true));
        $dashboardEnabled = get_option('ctm_api_dashboard_enabled'); error_log('dashboardEnabled: ' . var_export($dashboardEnabled, true));
        $trackingEnabled = get_option('ctm_api_tracking_enabled'); error_log('trackingEnabled: ' . var_export($trackingEnabled, true));
        $cf7Enabled = get_option('ctm_api_cf7_enabled'); error_log('cf7Enabled: ' . var_export($cf7Enabled, true));
        $gfEnabled = get_option('ctm_api_gf_enabled'); error_log('gfEnabled: ' . var_export($gfEnabled, true));
        $debugEnabled = self::isDebugEnabled(); error_log('debugEnabled: ' . var_export($debugEnabled, true));
        $apiStatus = 'not_tested';
        $accountInfo = null;
        $acctDetails = null;
        if ($apiKey && $apiSecret) {
            error_log('About to create ApiService');
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            error_log('ApiService created');
            if (isset($_POST['ctm_test_api'])) {
                error_log('Testing API connection');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                error_log('API test result: ' . var_export($accountInfo, true));
                $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
            } else {
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                error_log('API info result: ' . var_export($accountInfo, true));
                $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
            }
            if ($apiStatus === 'connected' && $accountInfo && isset($accountInfo['account']['id'])) {
                error_log('Fetching account details');
                $acctDetails = $apiService->getAccountById($accountInfo['account']['id'], $apiKey, $apiSecret);
                error_log('Account details: ' . var_export($acctDetails, true));
            }
        }
        error_log('Rendering general-tab view');
        ob_start();
        $this->renderView('general-tab', [
            'apiKey' => $apiKey,
            'apiSecret' => $apiSecret,
            'accountId' => $accountId,
            'dashboardEnabled' => $dashboardEnabled,
            'trackingEnabled' => $trackingEnabled,
            'cf7Enabled' => $cf7Enabled,
            'gfEnabled' => $gfEnabled,
            'debugEnabled' => $debugEnabled,
            'apiStatus' => $apiStatus,
            'accountInfo' => $accountInfo,
            'acctDetails' => $acctDetails,
        ]);
        return ob_get_clean();
    }

    private function getLogsTabContent(): string
    {
        $cf7Logs = json_decode(get_option('ctm_api_cf7_logs', '[]'), true) ?: [];
        $gfLogs = json_decode(get_option('ctm_api_gf_logs', '[]'), true) ?: [];
        ob_start();
        $this->renderView('logs-tab', [
            'cf7Logs' => $cf7Logs,
            'gfLogs' => $gfLogs,
        ]);
        return ob_get_clean();
    }

    private function getMappingTabContent(): string
    {
        ob_start();
        $this->renderView('mapping-tab');
        return ob_get_clean();
    }

    private function getApiTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $apiStatus = 'not_tested';
        $accountInfo = null;
        
        if ($apiKey && $apiSecret) {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
        }
        
        ob_start();
        $this->renderView('api-tab', [
            'apiKey' => $apiKey,
            'apiSecret' => $apiSecret,
            'apiStatus' => $apiStatus,
            'accountInfo' => $accountInfo,
        ]);
        return ob_get_clean();
    }

    private function getDocumentationTabContent(): string
    {
        ob_start();
        $this->renderView('documentation-tab');
        return ob_get_clean();
    }

    private function getDebugTabContent(): string
    {
        $debugEnabled = self::isDebugEnabled();
        error_log('debugEnabled: ' . var_export($debugEnabled, true));
        
        // Legacy log support (for backwards compatibility)
        $log = get_option('ctm_debug_log', []);
        if (!is_array($log)) $log = [];
        
        ob_start();
        $this->renderView('debug-tab', compact('debugEnabled', 'log'));
        return ob_get_clean();
    }

    /**
     * Add a dashboard widget for call statistics.
     */
    public function addDashboardWidget(): void
    {
        wp_add_dashboard_widget(
            'ctm_dashboard_widget',
            'CallTrackingMetrics Call Stats',
            [$this, 'renderDashboardWidget']
        );
    }

    /**
     * Render the dashboard widget content.
     */
    public function renderDashboardWidget(): void
    {
        echo '<div style="padding:10px;">';
        echo '<h3 style="margin-top:0;">Recent Call Volume</h3>';
        // Placeholder: In a real implementation, fetch and display call stats from the API
        echo '<p><em>Call stats will appear here once API integration is complete.</em></p>';
        echo '<ul style="margin:0 0 10px 20px;">';
        for ($i = 6; $i >= 0; $i--) {
            $date = date('M j', strtotime("-$i days"));
            $calls = rand(0, 10); // Placeholder random data
            echo '<li>' . esc_html($date) . ': <strong>' . esc_html($calls) . '</strong> calls</li>';
        }
        echo '</ul>';
        echo '<span class="hint">Connect your account to see real data. <a href="options-general.php?page=call-tracking-metrics">Settings</a></span>';
        echo '</div>';
    }

    /**
     * Save field mapping for a given form.
     *
     * @param string $form_type 'gf' or 'cf7'
     * @param string|int $form_id
     * @param array $mapping
     */
    public function saveFieldMapping(string $form_type, $form_id, array $mapping): void
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        update_option($option_name, $mapping);
    }

    /**
     * Get field mapping for a given form.
     *
     * @param string $form_type 'gf' or 'cf7'
     * @param string|int $form_id
     * @return array|null
     */
    public function getFieldMapping(string $form_type, $form_id): ?array
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        return get_option($option_name, null);
    }

    /**
     * Register AJAX handlers and enqueue admin JS for field mapping UI.
     */
    public function enqueueMappingAssets(): void
    {
        add_action('admin_enqueue_scripts', function($hook) {
            if ($hook !== 'settings_page_call-tracking-metrics') return;
            
            // Enqueue mapping JS
            wp_enqueue_script('ctm-mapping-js', plugins_url('js/ctm-mapping.js', dirname(__FILE__, 2)), ['jquery'], null, true);
            wp_localize_script('ctm-mapping-js', 'ctmMappingAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_mapping_nonce'),
            ]);
        });
        add_action('wp_ajax_ctm_get_forms', [$this, 'ajaxGetForms']);
        add_action('wp_ajax_ctm_get_fields', [$this, 'ajaxGetFields']);
        add_action('wp_ajax_ctm_save_mapping', [$this, 'ajaxSaveMapping']);
        add_action('wp_ajax_ctm_dismiss_notice', [$this, 'ajaxDismissNotice']);
        add_action('wp_ajax_ctm_test_api_connection', [$this, 'ajaxTestApiConnection']);
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
            $form = \WPCF7_ContactForm::find($form_id);
            if ($form && method_exists($form, 'scan_form_tags')) {
                foreach ($form->scan_form_tags() as $tag) {
                    $fields[] = ['id' => $tag->name, 'label' => $tag->name];
                }
            }
        }
        wp_send_json_success($fields);
    }

    /**
     * AJAX: Save mapping (for future use, not used in current form submit).
     */
    public function ajaxSaveMapping(): void
    {
        check_ajax_referer('ctm_mapping_nonce', 'nonce');
        $type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = sanitize_text_field($_POST['form_id'] ?? '');
        $mapping = $_POST['mapping'] ?? [];
        if ($type && $form_id && is_array($mapping)) {
            $this->saveFieldMapping($type, $form_id, $mapping);
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

    public static function logDebug($message): void
    {
        $log = get_option('ctm_debug_log', []);
        if (!is_array($log)) $log = [];
        $log[] = [
            'date' => date('Y-m-d H:i:s'),
            'message' => is_string($message) ? $message : print_r($message, true),
        ];
        update_option('ctm_debug_log', $log);
    }

    public static function isDebugEnabled(): bool
    {
        return (bool) get_option('ctm_debug_enabled', false);
    }

    /**
     * Enhanced logging system with daily logs and categorization
     */
    public function logActivity(string $message, string $type = 'info', array $context = []): void
    {
        if (!self::isDebugEnabled()) {
            return; // Only log when debug mode is enabled
        }

        $log_entry = [
            'timestamp' => current_time('mysql'),
            'type' => $type, // info, error, warning, debug, api, config, system
            'message' => $message,
            'context' => $context,
            'user_id' => get_current_user_id(),
            'ip_address' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];

        $this->writeToLog($log_entry);
        
        // Auto-cleanup old logs
        $this->cleanupOldLogs();
    }

    /**
     * Write log entry to daily log file
     */
    private function writeToLog(array $log_entry): void
    {
        $log_date = date('Y-m-d');
        $daily_logs = get_option("ctm_daily_log_{$log_date}", []);
        
        if (!is_array($daily_logs)) {
            $daily_logs = [];
        }
        
        $daily_logs[] = $log_entry;
        
        // Keep only last 1000 entries per day to prevent memory issues
        if (count($daily_logs) > 1000) {
            $daily_logs = array_slice($daily_logs, -1000);
        }
        
        update_option("ctm_daily_log_{$log_date}", $daily_logs);
        
        // Update log index
        $this->updateLogIndex($log_date);
    }

    /**
     * Update the log index to track available log dates
     */
    private function updateLogIndex(string $log_date): void
    {
        $log_index = get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            $log_index = [];
        }
        
        if (!in_array($log_date, $log_index)) {
            $log_index[] = $log_date;
            // Keep index sorted
            sort($log_index);
            update_option('ctm_log_index', $log_index);
        }
    }

    /**
     * Get all available log dates
     */
    public function getAvailableLogDates(): array
    {
        $log_index = get_option('ctm_log_index', []);
        return is_array($log_index) ? array_reverse($log_index) : [];
    }

    /**
     * Get logs for a specific date
     */
    public function getLogsForDate(string $date): array
    {
        $logs = get_option("ctm_daily_log_{$date}", []);
        return is_array($logs) ? $logs : [];
    }

    /**
     * Clear logs for a specific date
     */
    public function clearDayLog(string $date): void
    {
        delete_option("ctm_daily_log_{$date}");
        
        // Update log index
        $log_index = get_option('ctm_log_index', []);
        if (is_array($log_index)) {
            $log_index = array_filter($log_index, function($d) use ($date) {
                return $d !== $date;
            });
            update_option('ctm_log_index', array_values($log_index));
        }
    }

    /**
     * Clear all logs
     */
    public function clearAllLogs(): void
    {
        $log_index = get_option('ctm_log_index', []);
        if (is_array($log_index)) {
            foreach ($log_index as $date) {
                delete_option("ctm_daily_log_{$date}");
            }
        }
        
        delete_option('ctm_log_index');
        delete_option('ctm_debug_log'); // Clear old format logs too
    }

    /**
     * Email log for a specific date
     */
    public function emailLog(string $date, string $email_to): bool
    {
        $logs = $this->getLogsForDate($date);
        
        if (empty($logs)) {
            return false;
        }

        $site_name = get_bloginfo('name');
        $subject = "CTM Debug Log for {$date} - {$site_name}";
        
        $message = "Debug log for {$date}\n";
        $message .= "Site: {$site_name}\n";
        $message .= "Generated: " . current_time('mysql') . "\n";
        $message .= str_repeat('=', 60) . "\n\n";
        
        foreach ($logs as $entry) {
            $message .= "[{$entry['timestamp']}] [{$entry['type']}] {$entry['message']}\n";
            
            if (!empty($entry['context'])) {
                $message .= "Context: " . print_r($entry['context'], true) . "\n";
            }
            
            $message .= "User: " . ($entry['user_id'] ? get_userdata($entry['user_id'])->user_login : 'Anonymous') . "\n";
            $message .= "IP: {$entry['ip_address']}\n";
            $message .= "Memory: " . size_format($entry['memory_usage']) . " (Peak: " . size_format($entry['memory_peak']) . ")\n";
            $message .= str_repeat('-', 40) . "\n\n";
        }

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_option('admin_email')
        ];

        return wp_mail($email_to, $subject, $message, $headers);
    }

    /**
     * Auto-cleanup old logs based on retention settings
     */
    private function cleanupOldLogs(): void
    {
        if (!get_option('ctm_log_auto_cleanup', true)) {
            return;
        }

        $retention_days = (int) get_option('ctm_log_retention_days', 7);
        $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
        
        $log_index = get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            return;
        }

        $updated = false;
        foreach ($log_index as $key => $date) {
            if ($date < $cutoff_date) {
                delete_option("ctm_daily_log_{$date}");
                unset($log_index[$key]);
                $updated = true;
            }
        }

        if ($updated) {
            update_option('ctm_log_index', array_values($log_index));
        }
    }

    /**
     * Get user IP address
     */
    private function getUserIP(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * Get log statistics
     */
    public function getLogStatistics(): array
    {
        $log_index = $this->getAvailableLogDates();
        $total_entries = 0;
        $total_size = 0;
        $type_counts = [];
        
        foreach ($log_index as $date) {
            $logs = $this->getLogsForDate($date);
            $total_entries += count($logs);
            $total_size += strlen(serialize($logs));
            
            foreach ($logs as $log) {
                $type = $log['type'] ?? 'unknown';
                $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
            }
        }
        
        return [
            'total_days' => count($log_index),
            'total_entries' => $total_entries,
            'total_size' => $total_size,
            'type_counts' => $type_counts,
            'oldest_log' => !empty($log_index) ? end($log_index) : null,
            'newest_log' => !empty($log_index) ? reset($log_index) : null
        ];
    }

    /**
     * Initialize the logging system (call this once from main plugin file)
     */
    public static function initializeLoggingSystem(): void
    {
        static $initialized = false;
        
        if ($initialized) {
            return; // Prevent duplicate initialization
        }
        
        // Schedule daily log cleanup if not already scheduled
        if (!wp_next_scheduled('ctm_daily_log_cleanup')) {
            wp_schedule_event(time(), 'daily', 'ctm_daily_log_cleanup');
        }
        
        // Register cleanup action
        add_action('ctm_daily_log_cleanup', [__CLASS__, 'performScheduledLogCleanup']);
        
        $initialized = true;
    }

    /**
     * Handle plugin activation - set up logging defaults
     */
    public static function onPluginActivation(): void
    {
        // Set default log settings if not already set
        if (get_option('ctm_log_retention_days') === false) {
            update_option('ctm_log_retention_days', 7);
        }
        
        if (get_option('ctm_log_auto_cleanup') === false) {
            update_option('ctm_log_auto_cleanup', true);
        }
        
        if (get_option('ctm_log_email_notifications') === false) {
            update_option('ctm_log_email_notifications', false);
        }
        
        if (get_option('ctm_log_notification_email') === false) {
            update_option('ctm_log_notification_email', get_option('admin_email'));
        }
        
        // Initialize logging system
        self::initializeLoggingSystem();
        
        // Log plugin activation
        $instance = new self();
        $instance->logActivity('CTM Plugin activated', 'system', [
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit')
        ]);
    }

    /**
     * Handle plugin deactivation - clean up scheduled tasks
     */
    public static function onPluginDeactivation(): void
    {
        // Remove scheduled cleanup
        wp_clear_scheduled_hook('ctm_daily_log_cleanup');
        
        // Log plugin deactivation
        $instance = new self();
        $instance->logActivity('CTM Plugin deactivated', 'system');
    }

    /**
     * Perform scheduled log cleanup
     */
    public static function performScheduledLogCleanup(): void
    {
        $instance = new self();
        $instance->performInstanceLogCleanup();
    }

    /**
     * Instance method for log cleanup
     */
    private function performInstanceLogCleanup(): void
    {
        if (!get_option('ctm_log_auto_cleanup', true)) {
            return;
        }

        $retention_days = (int) get_option('ctm_log_retention_days', 7);
        $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
        
        $log_index = get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            return;
        }

        $cleaned_count = 0;
        $cleaned_size = 0;
        
        foreach ($log_index as $key => $date) {
            if ($date < $cutoff_date) {
                $log_data = get_option("ctm_daily_log_{$date}", []);
                $cleaned_size += strlen(serialize($log_data));
                
                delete_option("ctm_daily_log_{$date}");
                unset($log_index[$key]);
                $cleaned_count++;
            }
        }

        if ($cleaned_count > 0) {
            update_option('ctm_log_index', array_values($log_index));
            
            // Log cleanup activity
            $this->logActivity('Automatic log cleanup completed', 'system', [
                'cleaned_days' => $cleaned_count,
                'cleaned_size' => size_format($cleaned_size),
                'retention_days' => $retention_days,
                'cutoff_date' => $cutoff_date
            ]);
            
            // Send email notification if enabled
            if (get_option('ctm_log_email_notifications', false)) {
                $this->sendCleanupNotification($cleaned_count, $cleaned_size, $retention_days);
            }
        }
    }

    /**
     * Send email notification about log cleanup
     */
    private function sendCleanupNotification(int $cleaned_count, int $cleaned_size, int $retention_days): void
    {
        $notification_email = get_option('ctm_log_notification_email', get_option('admin_email'));
        if (empty($notification_email)) {
            return;
        }

        $site_name = get_bloginfo('name');
        $subject = "CTM Log Cleanup Report - {$site_name}";
        
        $message = "Call Tracking Metrics Plugin - Log Cleanup Report\n\n";
        $message .= "Site: {$site_name}\n";
        $message .= "Date: " . current_time('Y-m-d H:i:s') . "\n";
        $message .= str_repeat('=', 50) . "\n\n";
        
        $message .= "Cleanup Summary:\n";
        $message .= "- Days cleaned: {$cleaned_count}\n";
        $message .= "- Data cleaned: " . size_format($cleaned_size) . "\n";
        $message .= "- Retention period: {$retention_days} days\n\n";
        
        $remaining_stats = $this->getLogStatistics();
        $message .= "Remaining Logs:\n";
        $message .= "- Total days: {$remaining_stats['total_days']}\n";
        $message .= "- Total entries: " . number_format($remaining_stats['total_entries']) . "\n";
        $message .= "- Storage size: " . size_format($remaining_stats['total_size']) . "\n\n";
        
        $message .= "This is an automated notification from the CTM plugin.\n";
        $message .= "You can adjust log settings in the WordPress admin panel.\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_option('admin_email')
        ];

        wp_mail($notification_email, $subject, $message, $headers);
    }
} 