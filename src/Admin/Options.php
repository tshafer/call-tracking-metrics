<?php
namespace CTM\Admin;

use Illuminate\Http\Client\Factory as HttpClient;

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
        
        
        $notices = [];
        $cf7_installed = class_exists('WPCF7_ContactForm');
        $gf_installed = class_exists('GFAPI');
        if (!$cf7_installed) {
            $cf7_url = admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term');
            ob_start();
            $this->renderView('notice-cf7', compact('cf7_url'));
            $notices[] = ob_get_clean();
        }
        if (!$gf_installed) {
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
        $this->renderView('settings-page', compact('notices', 'active_tab', 'tab_content'));
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
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com', new HttpClient());
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