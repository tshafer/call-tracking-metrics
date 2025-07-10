<?php
/**
 * Settings Page Renderer
 * 
 * This file contains the SettingsRenderer class that handles rendering
 * of admin settings pages, tab content generation, and view management
 * for the CallTrackingMetrics plugin admin interface.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       2.0.0
 */

namespace CTM\Admin;

/**
 * Settings Page Renderer Class
 * 
 * Handles all view rendering and content generation for the admin interface:
 * - Settings page tab content generation
 * - View file rendering with variable injection
 * - Tab-specific content formatting
 * - Debug information display
 * - Form state management
 * 
 * This class is responsible for the presentation layer of the admin interface,
 * separating view logic from business logic for better maintainability.
 * 
 * @since 2.0.0
 */
class SettingsRenderer
{
    /**
     * Render a view file with variables
     * 
     * Loads and renders a PHP view file with the provided variables
     * extracted into the local scope. Provides error handling for
     * missing view files.
     * 
     * @since 2.0.0
     * @param string $view The view filename (without .php extension)
     * @param array  $vars Variables to extract into the view scope
     * @return void
     */
    public function renderView(string $view, array $vars = []): void
    {
        $viewPath = plugin_dir_path(__FILE__) . '../../views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            echo "<div style='color:red'>View not found: $viewPath</div>";
            return;
        }
        
        // Extract variables into local scope for the view
        extract($vars);
        
        // Include the view file
        include $viewPath;
    }

    /**
     * Get general tab content
     * 
     * Generates the content for the general settings tab including
     * API configuration, feature toggles, and tracking script settings.
     * 
     * @since 2.0.0
     * @return string The rendered general tab content HTML
     */
    public function getGeneralTabContent(): string
    {
        // Get current settings values
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $accountId = get_option('ctm_api_auth_account');
        $trackingEnabled = get_option('ctm_api_tracking_enabled');
        $cf7Enabled = get_option('ctm_api_cf7_enabled');
        $gfEnabled = get_option('ctm_api_gf_enabled');
        $dashboardEnabled = get_option('ctm_api_dashboard_enabled');
        $trackingScript = get_option('call_track_account_script');
        
        // Check plugin availability
        $cf7_installed = class_exists('WPCF7_ContactForm');
        $gf_installed = class_exists('GFAPI');
        
        // Start output buffering
        ob_start();
        
        // Render the general tab view
        $this->renderView('general-tab', compact(
            'apiKey', 'apiSecret', 'accountId', 'trackingEnabled',
            'cf7Enabled', 'gfEnabled', 'dashboardEnabled', 'trackingScript',
            'cf7_installed', 'gf_installed'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get logs tab content
     * 
     * Generates the content for the logs tab including form submission
     * logs, log management controls, and log statistics.
     * 
     * @since 2.0.0
     * @return string The rendered logs tab content HTML
     */
    public function getLogsTabContent(): string
    {
        // Get log data
        $cf7Logs = get_option('ctm_api_cf7_logs', []);
        $gfLogs = get_option('ctm_api_gf_logs', []);
        
        // Calculate log statistics
        $cf7Count = count($cf7Logs);
        $gfCount = count($gfLogs);
        $totalLogs = $cf7Count + $gfCount;
        
        // Get recent logs (last 10)
        $recentCf7Logs = array_slice(array_reverse($cf7Logs), 0, 10);
        $recentGfLogs = array_slice(array_reverse($gfLogs), 0, 10);
        
        ob_start();
        
        $this->renderView('logs-tab', compact(
            'cf7Logs', 'gfLogs', 'cf7Count', 'gfCount', 'totalLogs',
            'recentCf7Logs', 'recentGfLogs'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get mapping tab content
     * 
     * Generates the content for the field mapping tab including
     * form selection, field mapping interface, and mapping preview.
     * 
     * @since 2.0.0
     * @return string The rendered mapping tab content HTML
     */
    public function getMappingTabContent(): string
    {
        // Check if required plugins are available
        $cf7_available = class_exists('WPCF7_ContactForm');
        $gf_available = class_exists('GFAPI');
        
        // Get available forms
        $cf7_forms = [];
        $gf_forms = [];
        
        if ($cf7_available) {
            $cf7_forms = $this->getCF7Forms();
        }
        
        if ($gf_available) {
            $gf_forms = $this->getGFForms();
        }
        
        // Get CTM field options for mapping
        $ctm_fields = $this->getCTMFields();
        
        ob_start();
        
        $this->renderView('mapping-tab', compact(
            'cf7_available', 'gf_available', 'cf7_forms', 'gf_forms', 'ctm_fields'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get API tab content
     * 
     * Generates the content for the API testing and diagnostics tab
     * including connection testing, account information, and API status.
     * 
     * @since 2.0.0
     * @return string The rendered API tab content HTML
     */
    public function getApiTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $accountInfo = null;
        $connectionStatus = 'not_tested';
        
        // Test API connection if credentials are available
        if ($apiKey && $apiSecret) {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $connectionStatus = $accountInfo ? 'connected' : 'error';
        }
        
        ob_start();
        
        $this->renderView('api-tab', compact(
            'apiKey', 'apiSecret', 'accountInfo', 'connectionStatus'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get documentation tab content
     * 
     * Generates the content for the documentation tab including
     * setup instructions, integration guides, and troubleshooting.
     * 
     * @since 2.0.0
     * @return string The rendered documentation tab content HTML
     */
    public function getDocumentationTabContent(): string
    {
        ob_start();
        
        $this->renderView('documentation-tab');
        
        return ob_get_clean();
    }

    /**
     * Get debug tab content
     * 
     * Generates the content for the debug tab including debug mode
     * controls, log settings, daily logs, and system information.
     * 
     * @since 2.0.0
     * @return string The rendered debug tab content HTML
     */
    public function getDebugTabContent(): string
    {
        // Get debug settings
        $debugEnabled = LoggingSystem::isDebugEnabled();
        $retentionDays = get_option('ctm_log_retention_days', 7);
        $autoCleanup = get_option('ctm_log_auto_cleanup', false);
        $emailNotifications = get_option('ctm_log_email_notifications', false);
        $notificationEmail = get_option('ctm_log_notification_email', '');
        
        // Get log statistics
        $logStats = null;
        $dailyLogs = [];
        
        if ($debugEnabled) {
            $loggingSystem = new LoggingSystem();
            $logStats = $loggingSystem->getLogStatistics();
            $dailyLogs = method_exists($loggingSystem, 'getDailyLogs') ? $loggingSystem->getDailyLogs() : [];
        }
        
        ob_start();
        
        $this->renderView('debug-tab', compact(
            'debugEnabled', 'retentionDays', 'autoCleanup', 
            'emailNotifications', 'notificationEmail', 'logStats', 'dailyLogs'
        ));
        
        return ob_get_clean();
    }

    /**
     * Get Contact Form 7 forms for mapping interface
     * 
     * @since 2.0.0
     * @return array Array of CF7 forms with id and title
     */
    private function getCF7Forms(): array
    {
        if (!class_exists('WPCF7_ContactForm')) {
            return [];
        }
        
        $forms = [];
        $cf7_forms = \WPCF7_ContactForm::find(['posts_per_page' => -1]);
        
        foreach ($cf7_forms as $form) {
            $forms[] = [
                'id' => $form->id(),
                'title' => $form->title(),
            ];
        }
        
        return $forms;
    }

    /**
     * Get Gravity Forms for mapping interface
     * 
     * @since 2.0.0
     * @return array Array of GF forms with id and title
     */
    private function getGFForms(): array
    {
        if (!class_exists('GFAPI')) {
            return [];
        }
        
        $forms = [];
        
        try {
            if (method_exists('\GFAPI', 'get_forms')) {
                $gf_forms = \GFAPI::get_forms();
                
                foreach ($gf_forms as $form) {
                    $forms[] = [
                        'id' => $form['id'],
                        'title' => $form['title'],
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log('CTM: Error getting GF forms: ' . $e->getMessage());
        }
        
        return $forms;
    }

    /**
     * Get CallTrackingMetrics field options for mapping
     * 
     * Returns the standard CTM fields that can be mapped to form fields.
     * These represent the common lead data fields in the CTM system.
     * 
     * @since 2.0.0
     * @return array Array of CTM field options
     */
    private function getCTMFields(): array
    {
        return [
            'name' => 'Full Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'company' => 'Company Name',
            'address' => 'Street Address',
            'city' => 'City',
            'state' => 'State/Province',
            'zip' => 'ZIP/Postal Code',
            'country' => 'Country',
            'website' => 'Website URL',
            'message' => 'Message/Comments',
            'subject' => 'Subject',
            'lead_source' => 'Lead Source',
            'campaign' => 'Campaign',
            'custom_field_1' => 'Custom Field 1',
            'custom_field_2' => 'Custom Field 2',
            'custom_field_3' => 'Custom Field 3',
        ];
    }
} 