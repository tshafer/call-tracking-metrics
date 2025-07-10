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
        error_log('renderSettingsPage called');
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
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
        $log = get_option('ctm_debug_log', []);
        if (!is_array($log)) $log = [];
        ob_start();
        $this->renderView('debug-tab', [
            'debugEnabled' => $debugEnabled,
            'log' => $log,
        ]);
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
} 