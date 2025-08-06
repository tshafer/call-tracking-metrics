<?php
/**
 * Admin Options and Settings Management
 * 
 * This file contains the main Options class that handles WordPress admin
 * settings registration, page rendering, and form submission processing
 * for the CallTrackingMetrics plugin.
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
 * Main Admin Options Class
 * 
 * Handles the core WordPress admin functionality including:
 * - Settings registration with WordPress
 * - Admin page rendering and tab management
 * - Form submission processing
 * - Dashboard widget integration
 * - Notice generation and management
 * 
 * This class has been refactored to use dependency injection and delegates
 * complex functionality to specialized classes for better maintainability.
 * 
 * @since 2.0.0
 */
class Options
{
    /**
     * Settings page renderer instance
     * 
     * Handles all view rendering and tab content generation
     * 
     * @since 2.0.0
     * @var SettingsRenderer
     */
    private SettingsRenderer $renderer;

    /**
     * AJAX request handlers instance
     * 
     * Manages all AJAX endpoints and request processing
     * 
     * @since 2.0.0
     * @var AjaxHandlers
     */
    private AjaxHandlers $ajaxHandlers;

    /**
     * Logging system instance
     * 
     * Manages debug logging and activity tracking
     * 
     * @since 2.0.0
     * @var LoggingSystem
     */
    private LoggingSystem $loggingSystem;

    /**
     * Initialize the Options class with dependency injection
     * 
     * Creates instances of all required dependencies and sets up
     * the composition pattern for better separation of concerns.
     * 
     * @since 2.0.0
     */
    public function __construct($renderer = null, $ajaxHandlers = null, $loggingSystem = null)
    {
        $this->renderer = $renderer ?: new SettingsRenderer();
        $this->ajaxHandlers = $ajaxHandlers ?: new AjaxHandlers();
        $this->loggingSystem = $loggingSystem ?: new LoggingSystem();
    }

    /**
     * Register plugin settings with WordPress
     * 
     * Registers all plugin options with WordPress settings API.
     * These settings are automatically sanitized and stored by WordPress.
     * 
     * @since 2.0.0
     * @return void
     */
    public function registerSettings(): void
    {
        // API Configuration Settings
        register_setting("call-tracking-metrics", "ctm_api_key");
        register_setting("call-tracking-metrics", "ctm_api_secret");
        register_setting("call-tracking-metrics", "ctm_api_active_key");
        register_setting("call-tracking-metrics", "ctm_api_active_secret");
        register_setting("call-tracking-metrics", "ctm_api_auth_account");
        register_setting("call-tracking-metrics", "ctm_api_base_url", [
            'default' => 'https://api.calltrackingmetrics.com',
            'sanitize_callback' => [$this, 'sanitizeApiUrl']
        ]);
        
        // Tracking Script Settings
        register_setting("call-tracking-metrics", "call_track_account_script");
        
        // Feature Toggle Settings
        register_setting("call-tracking-metrics", "ctm_api_dashboard_enabled");
        register_setting("call-tracking-metrics", "ctm_api_tracking_enabled");
        register_setting("call-tracking-metrics", "ctm_api_cf7_enabled");
        register_setting("call-tracking-metrics", "ctm_api_gf_enabled");
        register_setting("call-tracking-metrics", "ctm_auto_inject_tracking_script");
        
        // Debugging Option
        register_setting("call-tracking-metrics", "ctm_debug_enabled");
        
        // Logging Settings
        register_setting("call-tracking-metrics", "ctm_api_cf7_logs");
        register_setting("call-tracking-metrics", "ctm_api_gf_logs");
        

    }

    /**
     * Register the settings page in the WordPress admin menu
     * 
     * Adds the CallTrackingMetrics settings page as a primary menu item
     * in the WordPress admin sidebar with appropriate permissions and callback.
     * 
     * @since 2.0.0
     * @return void
     */
    public function registerSettingsPage(): void
    {
        // Get the logo file URL
        $logo_url = \plugins_url('assets/images/ctm_logo-mark_cyan_400x400.png', CTM_PLUGIN_FILE);
        $logo_path = \plugin_dir_path(CTM_PLUGIN_FILE) . 'assets/images/ctm_logo-mark_cyan_400x400.png';
        
        // Use custom icon if file exists, otherwise fallback to dashicon
        $icon_url = file_exists($logo_path) ? $logo_url : 'dashicons-chart-area';
        
        add_menu_page(
            __('CallTrackingMetrics', 'call-tracking-metrics'),           // Page title
            __('Call Tracking', 'call-tracking-metrics'),                 // Menu title (shortened)
            'manage_options',                // Capability required
            'call-tracking-metrics',         // Menu slug
            [$this, 'renderSettingsPage'],   // Callback function
            $icon_url,                       // Custom icon or fallback
            30                                // Position (after Posts, before Media)
        );
        
        // Add CSS for custom menu icon
        add_action('admin_head', [$this, 'addMenuIconCSS']);
        
        // Add admin notices for settings page
        add_action('admin_notices', [$this, 'displaySettingsNotices']);
    }
    
    /**
     * Add CSS for custom menu icon
     * 
     * @since 2.0.0
     * @return void
     */
    public function addMenuIconCSS(): void
    {
        echo '<style>
        #adminmenu .toplevel_page_call-tracking-metrics .wp-menu-image img {
            width: 23px;
            height: 23px;
            padding: 0;
            margin: 0;
            border-radius: 2px;
            vertical-align: middle;
            display: inline-block;
            top: 7px;
            position: relative;
        }
        #adminmenu .toplevel_page_call-tracking-metrics .wp-menu-image:before {
            display: none;
        }
      
        </style>';
    }

    /**
     * Initialize all component subsystems
     * 
     * Sets up AJAX handlers, mapping assets, and other components
     * that require WordPress hooks to be registered.
     * 
     * @since 2.0.0
     * @return void
     */
    public function initialize(): void
    {
        // Register all AJAX endpoint handlers
        $this->ajaxHandlers->registerHandlers();

        // Get the tracking script from the API
        $this->getTrackingScriptFromApi();
        
        // Enqueue JavaScript and CSS assets for field mapping
        // Removed field mapping assets as per edit hint
        
        // Auto-disable integrations if required plugins are not available
        $this->autoDisableUnavailableIntegrations();
    }

    /**
     * Render the main plugin settings page
     * 
     * Handles both GET requests (display the page) and POST requests
     * (process form submissions). Generates tab content and notices
     * based on current plugin state and user permissions.
     * 
     * @since 2.0.0
     * @return void
     */
    public function renderSettingsPage(): void
    {
        try {
            // Handle form submissions first
            $this->handleFormSubmission();
            
            // Determine API connection status for conditional UI elements
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');
            $apiStatus = 'not_tested';
            
            // Test API connection if credentials are available (with timeout)
            if ($apiKey && $apiSecret) {
                try {
                    // Set a timeout for API calls to prevent 502 errors
                    set_time_limit(10); // 10 second timeout
                    $apiService = new \CTM\Service\ApiService(\ctm_get_api_url());
                    $apiService->setTimeout(10); // Reduce timeout for settings page
                    $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                    $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
                } catch (\Exception $e) {
                    // Log the error but don't break the page
                    if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                        $this->loggingSystem->logActivity('API connection test failed: ' . $e->getMessage(), 'error');
                    }
                    $apiStatus = 'not_connected';
                }
            }
            
            // Generate plugin notices (missing dependencies, etc.)
            $notices = $this->generateNotices();
            
            // Determine active tab from URL parameter
            $active_tab = $_GET['tab'] ?? 'general';
            
            // Generate content for the active tab
            $tab_content = $this->getTabContent($active_tab);
            
            // Render the complete settings page
            $this->renderer->renderView('settings-page', compact('notices', 'active_tab', 'tab_content', 'apiStatus'));
        } catch (\Exception $e) {
            // Log the error and show a user-friendly message
            if ($this->loggingSystem) {
                $this->loggingSystem->logActivity('Settings page error: ' . $e->getMessage(), 'error');
            }
            echo '<div class="wrap"><div class="notice notice-error"><p>Plugin Error: Unable to load Call Tracking Metrics settings. Please contact support.</p></div></div>';
        }
    }

    /**
     * Display admin notices for settings page
     * 
     * Shows success messages when settings are saved successfully.
     * This method is hooked to admin_notices to ensure proper timing.
     * 
     * @since 2.0.0
     * @return void
     */
    public function displaySettingsNotices(): void
    {
        // Only show notices on our settings page
        $screen = get_current_screen();
        if (!$screen || $screen->id !== 'toplevel_page_call-tracking-metrics') {
            return;
        }
        
        // Add success message if settings were updated
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo '<div class="notice notice-success is-dismissible"><p><strong>CallTrackingMetrics:</strong> ' . __('Settings saved successfully!', 'call-tracking-metrics') . '</p></div>';
        }
        
        // // Also check for any POST data that indicates a form was submitted
        // if (!empty($_POST) && !isset($_GET['settings-updated'])) {
        //     echo '<div class="notice notice-success is-dismissible" style="margin: 20px 0; padding: 20px; font-size: 16px; background: #d4edda; border: 2px solid #28a745; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"><p style="margin: 0; font-weight: bold; color: #155724;"><strong>' . __('Settings saved successfully!', 'call-tracking-metrics') . '</strong></p></div>';
        // }
    }

    /**
     * Generate plugin notices for missing dependencies
     * 
     * Checks for Contact Form 7 and Gravity Forms plugins and generates
     * dismissible notices if they're not installed but haven't been dismissed.
     * 
     * @since 2.0.0
     * @return array Array of notice HTML strings
     */
    private function generateNotices(): array
    {
        $notices = [];
        
        // Check plugin installation status
        $cf7_installed = class_exists('WPCF7_ContactForm');
        $gf_installed = class_exists('GFAPI');
        
        // Generate Contact Form 7 notice if needed
        if (!$cf7_installed && !get_option('ctm_cf7_notice_dismissed', false)) {
            $cf7_url = admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term');
            ob_start();
            $this->renderer->renderView('notice-cf7', compact('cf7_url'));
            $notices[] = ob_get_clean();
        }
        
        // Generate Gravity Forms notice if needed
        if (!$gf_installed && !get_option('ctm_gf_notice_dismissed', false)) {
            $gf_url = 'https://www.gravityforms.com/';
            ob_start();
            $this->renderer->renderView('notice-gf', compact('gf_url'));
            $notices[] = ob_get_clean();
        }
        
        return $notices;
    }

    /**
     * Get content for the specified admin tab
     * 
     * Routes tab requests to the appropriate content generation method
     * in the SettingsRenderer class. Falls back to general tab if
     * an invalid tab is requested.
     * 
     * @since 2.0.0
     * @param string $active_tab The requested tab identifier
     * @return string The rendered tab content HTML
     */
    private function getTabContent(string $active_tab): string
    {
        switch ($active_tab) {
            case 'logs':
                return $this->renderer->getLogsTabContent();
            case 'api':
                return $this->renderer->getApiTabContent();
            case 'import':
                return $this->renderer->getFormImportTabContent();
            case 'forms':
                return $this->renderer->getFormsTabContent();
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
     * Handle form submissions from the admin interface
     * 
     * Processes various form submissions including debug mode toggles,
     * log management actions, and general settings updates. Each form
     * type is handled separately with appropriate validation and logging.
     * 
     * @since 2.0.0
     * @return void
     */
    private function handleFormSubmission(): void
    {
        // Handle debug mode toggle
        if (isset($_POST['toggle_debug'])) {
            $current = get_option('ctm_debug_enabled', false);
            $new_value = !$current;
            update_option('ctm_debug_enabled', $new_value);
            
            // Log the debug mode change
            $this->loggingSystem->logActivity(
                $new_value ? 'Debug mode enabled' : 'Debug mode disabled',
                'debug',
                ['previous_state' => $current, 'new_state' => $new_value]
            );
            
            // Redirect to prevent form resubmission
            wp_redirect(admin_url('admin.php?page=call-tracking-metrics&tab=debug'));
            exit;
        }
        
        // Handle clear all debug logs
        if (isset($_POST['clear_debug_log'])) {
            // Log the clear action before clearing (so it gets recorded)
            $this->loggingSystem->logActivity('All debug logs cleared', 'system');
            $this->loggingSystem->clearAllLogs();
            wp_redirect(admin_url('admin.php?page=call-tracking-metrics&tab=debug'));
            exit;
        }
        
        // Handle clear single day log
        if (isset($_POST['clear_single_log']) && !empty($_POST['log_date'])) {
            $log_date = sanitize_text_field($_POST['log_date']);
            $this->loggingSystem->logActivity("Log cleared for date: {$log_date}", 'system');
            $this->loggingSystem->clearDayLog($log_date);
            wp_redirect(admin_url('admin.php?page=call-tracking-metrics&tab=debug'));
            exit;
        }
        
        // Handle email log functionality
        if (isset($_POST['email_log']) && !empty($_POST['log_date']) && !empty($_POST['email_to'])) {
            $log_date = sanitize_text_field($_POST['log_date']);
            $email_to = sanitize_email($_POST['email_to']);
            $result = $this->loggingSystem->emailLog($log_date, $email_to);
            
            // Log the email attempt result
            if ($result) {
                $this->loggingSystem->logActivity("Log emailed for date: {$log_date} to: {$email_to}", 'system');
            } else {
                $this->loggingSystem->logActivity("Failed to email log for date: {$log_date} to: {$email_to}", 'error');
            }
            
            wp_redirect(admin_url('admin.php?page=call-tracking-metrics&tab=debug'));
            exit;
        }
        
        // Handle log retention settings update
        if (isset($_POST['update_log_settings'])) {
            $retention_days = (int) ($_POST['log_retention_days'] ?? 7);
            $retention_days = max(1, min(365, $retention_days)); // Clamp between 1-365 days
            
            // Update log management settings
            update_option('ctm_log_retention_days', $retention_days);
            update_option('ctm_log_auto_cleanup', isset($_POST['log_auto_cleanup']));
            update_option('ctm_log_email_notifications', isset($_POST['log_email_notifications']));
            update_option('ctm_log_notification_email', sanitize_email($_POST['log_notification_email'] ?? ''));
            
            $this->loggingSystem->logActivity("Log settings updated - Retention: {$retention_days} days", 'system');
            wp_redirect(admin_url('admin.php?page=call-tracking-metrics&tab=debug'));
            exit;
        }
        
        // Handle general plugin settings
        if (isset($_POST['ctm_api_key']) && isset($_POST['ctm_settings_nonce']) && wp_verify_nonce($_POST['ctm_settings_nonce'], 'ctm_save_settings')) {
            // Debug: Log what's being submitted
            if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                $this->loggingSystem->logActivity('Form submitted with POST data: ' . json_encode($_POST), 'debug');
            }
            $this->saveGeneralSettings();
        }
    }
    /**
     * Get the tracking script from the API
     * 
     * @since 2.0.0
     * @return void
     */
    public function getTrackingScriptFromApi() 
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if (!empty($apiKey) && !empty($apiSecret)) {
            $apiService = new \CTM\Service\ApiService(\ctm_get_api_url());
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $accountId = null;
            if ($accountInfo && isset($accountInfo['account']['id'])) {
                $accountId = $accountInfo['account']['id'];
                update_option('ctm_api_auth_account', $accountId);
            }
            if ($accountId) {
                try {
                    $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
                    if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                        update_option('call_track_account_script', $scripts['tracking']);
                    }
                } catch (\Exception $e) {
                    // Optionally log error
                }
            }
        }
    }
    /**
     * Save general plugin settings
     * 
     * Processes and saves the main plugin configuration including
     * API credentials, feature toggles, and tracking script settings.
     * Includes validation and activity logging.
     * 
     * @since 2.0.0
     * @return void
     */
    private function saveGeneralSettings(): void
    {
        // Sanitize and extract form data
        // Only update API key/secret if present in POST, otherwise keep existing
        $apiKey = isset($_POST['ctm_api_key']) ? sanitize_text_field($_POST['ctm_api_key']) : get_option('ctm_api_key');
        $apiSecret = isset($_POST['ctm_api_secret']) ? sanitize_text_field($_POST['ctm_api_secret']) : get_option('ctm_api_secret');
        $apiBaseUrl = isset($_POST['ctm_api_base_url']) ? sanitize_text_field($_POST['ctm_api_base_url']) : get_option('ctm_api_base_url');
        $trackingEnabled = isset($_POST['ctm_api_tracking_enabled']) ? 1 : 0;
        $cf7Enabled = isset($_POST['ctm_api_cf7_enabled']) ? 1 : 0;
        $gfEnabled = isset($_POST['ctm_api_gf_enabled']) ? 1 : 0;
        $dashboardEnabled = isset($_POST['ctm_dashboard_enabled']) ? 1 : 0;
        $autoInjectTracking = isset($_POST['ctm_auto_inject_tracking_script']) ? 1 : 0;
        $debugEnabled = isset($_POST['ctm_debug_enabled']) ? 1 : 0;
        
        // Auto-disable integrations if required plugins are not available
        $cf7_plugin_active = function_exists('is_plugin_active') ? is_plugin_active('contact-form-7/wp-contact-form-7.php') : false;
        if (!$cf7_plugin_active && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) {
            $cf7Enabled = false;
            $this->loggingSystem->logActivity('Contact Form 7 integration auto-disabled - plugin not available', 'config');
            // Add admin notice for CF7 auto-disable
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning is-dismissible"><p><strong>CallTrackingMetrics:</strong> Contact Form 7 integration was automatically disabled because the Contact Form 7 plugin is not installed or activated. <a href="' . admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term') . '" target="_blank">Install Contact Form 7</a> to enable this integration.</p></div>';
            });
        } else {
            // Debug: Log what we found for CF7
            if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                $cf7_class_exists = class_exists('WPCF7_ContactForm');
                $cf7_function_exists = function_exists('wpcf7_contact_form');
                $this->loggingSystem->logActivity("CF7 detection - Plugin active: " . ($cf7_plugin_active ? 'true' : 'false') . ", Class exists: " . ($cf7_class_exists ? 'true' : 'false') . ", Function exists: " . ($cf7_function_exists ? 'true' : 'false'), 'debug');
            }
        }
        
        // Only auto-disable GF if the user didn't explicitly try to enable it
        $user_tried_to_enable_gf = isset($_POST['ctm_api_gf_enabled']);
        $gf_plugin_active = function_exists('is_plugin_active') ? is_plugin_active('gravityforms/gravityforms.php') : false;
        $gf_class_exists = class_exists('GFAPI');
        $gf_function_exists = function_exists('gravity_form');
        $gf_available = $gf_plugin_active || $gf_class_exists;
        
        // Debug: Always log GF detection for troubleshooting
        if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
            $this->loggingSystem->logActivity("GF detection - Plugin active: " . ($gf_plugin_active ? 'true' : 'false') . ", Class exists: " . ($gf_class_exists ? 'true' : 'false') . ", Function exists: " . ($gf_function_exists ? 'true' : 'false') . ", Available: " . ($gf_available ? 'true' : 'false') . ", User tried to enable: " . ($user_tried_to_enable_gf ? 'true' : 'false'), 'debug');
        }
        
        // Only auto-disable if not available and user didn't try to enable
        if (!$gf_available && !$user_tried_to_enable_gf) {
            $gfEnabled = false;
            $this->loggingSystem->logActivity('Gravity Forms integration auto-disabled - plugin not available', 'config');
            // Add admin notice for GF auto-disable
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning is-dismissible"><p><strong>CallTrackingMetrics:</strong> Gravity Forms integration was automatically disabled because the Gravity Forms plugin is not installed or activated. <a href="' . admin_url('plugin-install.php?s=gravity+forms&tab=search&type=term') . '" target="_blank">Install Gravity Forms</a> to enable this integration.</p></div>';
            });
        }
        
        // Log API key changes for security auditing
        $old_key = get_option('ctm_api_key');
        if ($old_key !== $apiKey) {
            $this->loggingSystem->logActivity('API Key updated', 'config', [
                'old_key_partial' => substr($old_key, 0, 8) . '...',
                'new_key_partial' => substr($apiKey, 0, 8) . '...'
            ]);
        }
        
        // Save settings to WordPress options
        update_option('ctm_api_key', $apiKey);
        update_option('ctm_api_secret', $apiSecret);
        update_option('ctm_api_base_url', $apiBaseUrl);
        update_option('ctm_api_tracking_enabled', $trackingEnabled);
        update_option('ctm_api_cf7_enabled', $cf7Enabled);
        update_option('ctm_api_gf_enabled', $gfEnabled);
        update_option('ctm_dashboard_enabled', $dashboardEnabled);
        update_option('ctm_auto_inject_tracking_script', $autoInjectTracking);
        update_option('ctm_debug_enabled', $debugEnabled);
        
        // Debug: Log what was saved
        if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
            $this->loggingSystem->logActivity('Settings saved - CF7 enabled: ' . ($cf7Enabled ? 'true' : 'false') . ', GF enabled: ' . ($gfEnabled ? 'true' : 'false'), 'debug');
        }
        
        // Save tracking script if provided
        if (isset($_POST['call_track_account_script'])) {
            // Save as raw HTML, not entities
            $raw_script = wp_unslash($_POST['call_track_account_script']);
            update_option('call_track_account_script', wp_kses_post($raw_script));
        }
        
        // Log the settings change
        $this->loggingSystem->logActivity('General settings saved', 'config', [
            'tracking_enabled' => $trackingEnabled,
            'cf7_enabled' => $cf7Enabled,
            'gf_enabled' => $gfEnabled,
                'dashboard_enabled' => $dashboardEnabled,
            'debug_enabled' => $debugEnabled,
            'auto_inject_tracking' => $autoInjectTracking
        ]);
        
        // Redirect to prevent form resubmission with success message
        $referer = wp_get_referer();
        if (!$referer) {
            $referer = admin_url('admin.php?page=call-tracking-metrics&tab=general');
        }
        wp_redirect(add_query_arg(['tab' => 'general', 'settings-updated' => 'true'], $referer));
        exit;
    }

    /**
     * Auto-disable integrations if required plugins are not available
     * 
     * Checks if Contact Form 7 and Gravity Forms are installed and active.
     * If not, automatically disables the corresponding integration settings
     * to prevent confusion and ensure data integrity.
     * 
     * @since 2.0.0
     * @return void
     */
    private function autoDisableUnavailableIntegrations(): void
    {
        $changes_made = false;
        
        // Check Contact Form 7 integration
        $cf7_plugin_active = function_exists('is_plugin_active') ? is_plugin_active('contact-form-7/wp-contact-form-7.php') : false;
        if (!$cf7_plugin_active && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form') && get_option('ctm_api_cf7_enabled', false)) {
            update_option('ctm_api_cf7_enabled', false);
            $this->loggingSystem->logActivity('Contact Form 7 integration auto-disabled - plugin not available', 'config');
            $changes_made = true;
            // Add admin notice for CF7 auto-disable on page load
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning is-dismissible"><p><strong>CallTrackingMetrics:</strong> Contact Form 7 integration was automatically disabled because the Contact Form 7 plugin is not installed or activated. <a href="' . admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term') . '" target="_blank">Install Contact Form 7</a> to enable this integration.</p></div>';
            });
        } else {
            // Debug: Log what we found for CF7 in auto-disable check
            if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
                $cf7_class_exists = class_exists('WPCF7_ContactForm');
                $cf7_function_exists = function_exists('wpcf7_contact_form');
                $cf7_enabled = get_option('ctm_api_cf7_enabled', false);
                $this->loggingSystem->logActivity("CF7 auto-disable check - Plugin active: " . ($cf7_plugin_active ? 'true' : 'false') . ", Class exists: " . ($cf7_class_exists ? 'true' : 'false') . ", Function exists: " . ($cf7_function_exists ? 'true' : 'false') . ", Currently enabled: " . ($cf7_enabled ? 'true' : 'false'), 'debug');
            }
        }
        
        // Check Gravity Forms integration - only auto-disable if currently enabled but plugin not available
        $gf_currently_enabled = get_option('ctm_api_gf_enabled', false);
        $gf_plugin_active = function_exists('is_plugin_active') ? is_plugin_active('gravityforms/gravityforms.php') : false;
        $gf_class_exists = class_exists('GFAPI');
        $gf_function_exists = function_exists('gravity_form');
        $gf_available = $gf_plugin_active || $gf_class_exists;
        
        // Debug: Log what we found for GF in auto-disable check
        if ($this->loggingSystem && $this->loggingSystem->isDebugEnabled()) {
            $this->loggingSystem->logActivity("GF auto-disable check - Plugin active: " . ($gf_plugin_active ? 'true' : 'false') . ", Class exists: " . ($gf_class_exists ? 'true' : 'false') . ", Function exists: " . ($gf_function_exists ? 'true' : 'false') . ", Available: " . ($gf_available ? 'true' : 'false') . ", Currently enabled: " . ($gf_currently_enabled ? 'true' : 'false'), 'debug');
        }
        
        // Only auto-disable if not available and currently enabled
        if (!$gf_available && $gf_currently_enabled) {
            update_option('ctm_api_gf_enabled', false);
            $this->loggingSystem->logActivity('Gravity Forms integration auto-disabled - plugin not available', 'config');
            $changes_made = true;
            // Add admin notice for GF auto-disable on page load
            add_action('admin_notices', function() {
                echo '<div class="notice notice-warning is-dismissible"><p><strong>CallTrackingMetrics:</strong> Gravity Forms integration was automatically disabled because the Gravity Forms plugin is not installed or activated. <a href="' . admin_url('plugin-install.php?s=gravity+forms&tab=search&type=term') . '" target="_blank">Install Gravity Forms</a> to enable this integration.</p></div>';
            });
        }
        
        // Log summary if any changes were made
        if ($changes_made) {
            $this->loggingSystem->logActivity('Integration settings auto-corrected on page load', 'system');
        }
    }

    /**
     * Add a dashboard widget for call statistics
     * 
     * Registers a WordPress dashboard widget that displays call tracking
     * statistics and metrics from the CTM API.
     * 
     * @since 2.0.0
     * @return void
     */
    public function addDashboardWidget(): void
    {
        wp_add_dashboard_widget(
            'ctm_dashboard_widget',                    // Widget ID
            'CallTrackingMetrics Call Stats',         // Widget title
            [$this, 'renderDashboardWidget']          // Callback function
        );
    }

    /**
     * Render the dashboard widget content
     * 
     * Displays call statistics and metrics in the WordPress dashboard.
     * Currently shows placeholder data but can be enhanced to fetch
     * real data from the CTM API.
     * 
     * @since 2.0.0
     * @return void
     */
    public function renderDashboardWidget(): void
    {
        // Use inline styles for dashboard widget (no Tailwind available)
        ?>
        <div style="padding: 10px; font-family: sans-serif;">
            <?php
            // --- Widget logic: show last 30 days, like legacy ctm.php ---
            $dates = [];
            $calls = [];
            $error = '';
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');
            $accountId = get_option('ctm_api_auth_account');
            $totalCalls = 0;
            $lastCall = null;
            $firstCall = null;
            $callsByDay = [];
            if ($apiKey && $apiSecret && $accountId && class_exists('CTM\\Service\\ApiService')) {
                try {
                    $api = new \CTM\Service\ApiService(\ctm_get_api_url());
                    $since = date('Y-m-d', strtotime('-29 days'));
                    $until = date('Y-m-d');
                    $params = [
                        'start_date' => $since,
                        'end_date' => $until,
                        'group_by' => 'date',
                        'per_page' => 1000
                    ];
                    $result = $api->getCalls($apiKey, $apiSecret, $params);
                    if (isset($result['calls']) && is_array($result['calls'])) {
                        foreach ($result['calls'] as $call) {
                            $date = isset($call['date']) ? substr($call['date'], 0, 10) : (isset($call['start_time']) ? substr($call['start_time'], 0, 10) : null);
                            if ($date) {
                                if (!isset($callsByDay[$date])) $callsByDay[$date] = 0;
                                $callsByDay[$date]++;
                                $totalCalls++;
                                // Track first and last call
                                if (!$firstCall || $call['start_time'] < $firstCall['start_time']) {
                                    $firstCall = $call;
                                }
                                if (!$lastCall || $call['start_time'] > $lastCall['start_time']) {
                                    $lastCall = $call;
                                }
                            }
                        }
                        // Fill in all days (even with 0 calls)
                        for ($i = 29; $i >= 0; $i--) {
                            $d = date('Y-m-d', strtotime("-$i days"));
                            $dates[] = date('M j', strtotime($d));
                            $calls[] = isset($callsByDay[$d]) ? $callsByDay[$d] : 0;
                        }
                    } else {
                        $error = 'No call data found.';
                    }
                } catch (\Throwable $e) {
                    $error = 'Error fetching call data: ' . $e->getMessage();
                }
            } else {
                $error = 'API credentials not set.';
            }
            ?>
            <div style="margin-bottom: 1.5rem;">
                <canvas id="ctm-calls-chart" height="80" style="width: 100%; max-width: 100%; border-radius: 0.5rem; background: #f3f4f6; box-shadow: 0 1px 4px rgba(0,0,0,0.04);"></canvas>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                <?php if ($error): ?>
                    document.addEventListener("DOMContentLoaded", function() {
                        var ctx = document.getElementById("ctm-calls-chart");
                        if (ctx) {
                            let errorDiv = document.createElement("div");
                            errorDiv.style.marginBottom = "1rem";
                            errorDiv.style.color = "#fff";
                            errorDiv.style.background = "#dc2626";
                            errorDiv.style.borderRadius = "0.5rem";
                            errorDiv.style.padding = "0.75rem";
                            errorDiv.style.fontWeight = "600";
                            errorDiv.innerHTML = "<span><?php echo addslashes($error); ?></span>";
                            ctx.parentNode.insertBefore(errorDiv, ctx);
                            ctx.style.display = 'none';
                        }
                    });
                <?php else: ?>
                    if (window.Chart) {
                        var ctx = document.getElementById("ctm-calls-chart").getContext("2d");
                        new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: <?php echo json_encode($dates); ?>,
                                datasets: [{
                                    label: "Calls",
                                    data: <?php echo json_encode($calls); ?>,
                                    backgroundColor: "#2563eb",
                                    borderRadius: 8,
                                    maxBarThickness: 32
                                }]
                            },
                            options: {
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { stepSize: 1 }
                                    }
                                },
                                animation: { duration: 1200 }
                            }
                        });
                    }
                <?php endif; ?>
            </script>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div style="flex: 1 1 120px; min-width: 120px; background: #f3f4f6; border-radius: 0.5rem; padding: 1rem; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                    <div style="font-size: 2rem; margin-bottom: 0.25rem;">📈</div>
                    <div style="font-size: 1.125rem; font-weight: bold;"><?php echo esc_html($totalCalls); ?></div>
                    <div style="color: #2563eb; font-size: 0.875rem;">Total Calls (30d)</div>
                </div>
                <div style="flex: 1 1 120px; min-width: 120px; background: #f3f4f6; border-radius: 0.5rem; padding: 1rem; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                    <div style="font-size: 2rem; margin-bottom: 0.25rem;">🕒</div>
                    <div style="font-size: 1.125rem; font-weight: bold;">
                        <?php
                        if ($lastCall && isset($lastCall['start_time'])) {
                            echo esc_html(date('M j, Y H:i', strtotime($lastCall['start_time'])));
                        } else {
                            echo '—';
                        }
                        ?>
                    </div>
                    <div style="color: #2563eb; font-size: 0.875rem;">Last Call</div>
                </div>
                <div style="flex: 1 1 120px; min-width: 120px; background: #f3f4f6; border-radius: 0.5rem; padding: 1rem; text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,0.04);">
                    <div style="font-size: 2rem; margin-bottom: 0.25rem;">📅</div>
                    <div style="font-size: 1.125rem; font-weight: bold;">
                        <?php
                        if ($firstCall && isset($firstCall['start_time'])) {
                            echo esc_html(date('M j, Y H:i', strtotime($firstCall['start_time'])));
                        } else {
                            echo '—';
                        }
                        ?>
                    </div>
                    <div style="color: #2563eb; font-size: 0.875rem;">First Call (30d)</div>
                </div>
            </div>
        </div>
        <?php
    }

    // ===================================================================
    // Static Methods for Backwards Compatibility
    // ===================================================================

    /**
     * Legacy debug logging method for backwards compatibility
     * 
     * @since 2.0.0
     * @param mixed $message The message to log
     * @return void
     */
    public function logDebug($message): void
    {
        if (isset($this->loggingSystem)) {
            $this->loggingSystem->logDebug($message);
        }
    }

    /**
     * Check if debug mode is enabled (delegated to LoggingSystem)
     * 
     * @since 2.0.0
     * @return bool True if debug mode is enabled
     */
    public function isDebugEnabled(): bool
    {
        return isset($this->loggingSystem) ? $this->loggingSystem->isDebugEnabled() : false;
    }

    /**
     * Sanitize the API URL setting
     * 
     * Ensures the API URL is properly formatted and valid
     * 
     * @since 2.0.0
     * @param string $url The API URL to sanitize
     * @return string The sanitized API URL
     */
    public function sanitizeApiUrl(string $url): string
    {
        $url = trim($url);
        
        // If empty, return default
        if (empty($url)) {
            return 'https://api.calltrackingmetrics.com';
        }
        
        // Ensure URL has protocol
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }
        
        // Remove trailing slash
        $url = rtrim($url, '/');
        
        // Basic URL validation
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }
        
        // If validation fails, return default
        return 'https://api.calltrackingmetrics.com';
    }
    

} 