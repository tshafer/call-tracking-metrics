<?php
namespace CTM\Admin;

/**
 * Handles admin options and settings for CallTrackingMetrics.
 */
class Options
{
    private SettingsRenderer $renderer;
    private AjaxHandlers $ajaxHandlers;
    private FieldMapping $fieldMapping;
    private LoggingSystem $loggingSystem;

    public function __construct()
    {
        $this->renderer = new SettingsRenderer();
        $this->ajaxHandlers = new AjaxHandlers();
        $this->fieldMapping = new FieldMapping();
        $this->loggingSystem = new LoggingSystem();
    }

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
     * Initialize all components
     */
    public function initialize(): void
    {
        // Register AJAX handlers
        $this->ajaxHandlers->registerHandlers();
        
        // Enqueue mapping assets
        $this->fieldMapping->enqueueMappingAssets();
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
        
        $notices = $this->generateNotices();
        $active_tab = $_GET['tab'] ?? 'general';
        $tab_content = $this->getTabContent($active_tab);
        
        $this->renderer->renderView('settings-page', compact('notices', 'active_tab', 'tab_content', 'apiStatus'));
    }

    /**
     * Generate plugin notices
     */
    private function generateNotices(): array
    {
        $notices = [];
        $cf7_installed = class_exists('WPCF7_ContactForm');
        $gf_installed = class_exists('GFAPI');
        
        if (!$cf7_installed && !get_option('ctm_cf7_notice_dismissed', false)) {
            $cf7_url = admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term');
            ob_start();
            $this->renderer->renderView('notice-cf7', compact('cf7_url'));
            $notices[] = ob_get_clean();
        }
        
        if (!$gf_installed && !get_option('ctm_gf_notice_dismissed', false)) {
            $gf_url = 'https://www.gravityforms.com/';
            ob_start();
            $this->renderer->renderView('notice-gf', compact('gf_url'));
            $notices[] = ob_get_clean();
        }
        
        return $notices;
    }

    /**
     * Get content for the specified tab
     */
    private function getTabContent(string $active_tab): string
    {
        switch ($active_tab) {
            case 'logs':
                return $this->renderer->getLogsTabContent();
            case 'mapping':
                return $this->renderer->getMappingTabContent();
            case 'api':
                return $this->renderer->getApiTabContent();
            case 'documentation':
                return $this->renderer->getDocumentationTabContent();
            case 'debug':
                return $this->renderer->getDebugTabContent();
            case 'general':
            default:
                return $this->renderer->getGeneralTabContent();
        }
    }

    /**
     * Handle form submissions
     */
    private function handleFormSubmission(): void
    {
        // Handle debug mode toggle
        if (isset($_POST['toggle_debug'])) {
            $current = get_option('ctm_debug_enabled', false);
            $new_value = !$current;
            update_option('ctm_debug_enabled', $new_value);
            
            $this->loggingSystem->logActivity(
                $new_value ? 'Debug mode enabled' : 'Debug mode disabled',
                'debug',
                ['previous_state' => $current, 'new_state' => $new_value]
            );
            
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
            exit;
        }
        
        // Handle clear debug log
        if (isset($_POST['clear_debug_log'])) {
            $this->loggingSystem->logActivity('All debug logs cleared', 'system');
            $this->loggingSystem->clearAllLogs();
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
            exit;
        }
        
        // Handle clear single day log
        if (isset($_POST['clear_single_log']) && !empty($_POST['log_date'])) {
            $log_date = sanitize_text_field($_POST['log_date']);
            $this->loggingSystem->logActivity("Log cleared for date: {$log_date}", 'system');
            $this->loggingSystem->clearDayLog($log_date);
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
            exit;
        }
        
        // Handle email log
        if (isset($_POST['email_log']) && !empty($_POST['log_date']) && !empty($_POST['email_to'])) {
            $log_date = sanitize_text_field($_POST['log_date']);
            $email_to = sanitize_email($_POST['email_to']);
            $result = $this->loggingSystem->emailLog($log_date, $email_to);
            
            if ($result) {
                $this->loggingSystem->logActivity("Log emailed for date: {$log_date} to: {$email_to}", 'system');
            } else {
                $this->loggingSystem->logActivity("Failed to email log for date: {$log_date} to: {$email_to}", 'error');
            }
            
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
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
            
            $this->loggingSystem->logActivity("Log settings updated - Retention: {$retention_days} days", 'system');
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
            exit;
        }
        
        // Handle general settings
        if (isset($_POST['ctm_api_key'])) {
            $this->saveGeneralSettings();
        }
    }

    /**
     * Save general settings
     */
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
            $this->loggingSystem->logActivity('API Key updated', 'config', [
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
        
        $this->loggingSystem->logActivity('General settings saved', 'config', [
            'tracking_enabled' => $trackingEnabled,
            'cf7_enabled' => $cf7Enabled,
            'gf_enabled' => $gfEnabled,
            'dashboard_enabled' => $dashboardEnabled
        ]);
        
        wp_redirect(add_query_arg(['tab' => 'general'], wp_get_referer()));
        exit;
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
     * Get field mapping (delegated to FieldMapping class)
     */
    public function getFieldMapping(string $form_type, $form_id): ?array
    {
        return $this->fieldMapping->getFieldMapping($form_type, $form_id);
    }

    /**
     * Save field mapping (delegated to FieldMapping class)
     */
    public function saveFieldMapping(string $form_type, $form_id, array $mapping): void
    {
        $this->fieldMapping->saveFieldMapping($form_type, $form_id, $mapping);
    }

    /**
     * Legacy debug logging method for backwards compatibility
     */
    public static function logDebug($message): void
    {
        LoggingSystem::logDebug($message);
    }

    /**
     * Check if debug mode is enabled (delegated to LoggingSystem)
     */
    public static function isDebugEnabled(): bool
    {
        return LoggingSystem::isDebugEnabled();
    }
} 