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
     * Field mapping management instance
     * 
     * Handles form field mapping between WordPress forms and CTM
     * 
     * @since 2.0.0
     * @var FieldMapping
     */
    private FieldMapping $fieldMapping;

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
    public function __construct($renderer = null, $ajaxHandlers = null, $fieldMapping = null, $loggingSystem = null)
    {
        $this->renderer = $renderer ?: new SettingsRenderer();
        $this->ajaxHandlers = $ajaxHandlers ?: new AjaxHandlers();
        $this->fieldMapping = $fieldMapping ?: new FieldMapping();
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
        
        // Tracking Script Settings
        register_setting("call-tracking-metrics", "call_track_account_script");
        
        // Feature Toggle Settings
        register_setting("call-tracking-metrics", "ctm_api_dashboard_enabled");
        register_setting("call-tracking-metrics", "ctm_api_tracking_enabled");
        register_setting("call-tracking-metrics", "ctm_api_cf7_enabled");
        register_setting("call-tracking-metrics", "ctm_api_gf_enabled");
        register_setting("call-tracking-metrics", "ctm_auto_inject_tracking_script");
        
        // Logging Settings
        register_setting("call-tracking-metrics", "ctm_api_cf7_logs");
        register_setting("call-tracking-metrics", "ctm_api_gf_logs");
    }

    /**
     * Register the settings page in the WordPress admin menu
     * 
     * Adds the CallTrackingMetrics settings page under the WordPress
     * Settings menu with appropriate permissions and callback.
     * 
     * @since 2.0.0
     * @return void
     */
    public function registerSettingsPage(): void
    {
        add_options_page(
            'CallTrackingMetrics',           // Page title
            'CallTrackingMetrics',           // Menu title
            'manage_options',                // Capability required
            'call-tracking-metrics',         // Menu slug
            [$this, 'renderSettingsPage']   // Callback function
        );
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
        
        // Enqueue JavaScript and CSS assets for field mapping
        $this->fieldMapping->enqueueMappingAssets();
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
        // Process form submissions if this is a POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleFormSubmission();
        }
        
        // Determine API connection status for conditional UI elements
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $apiStatus = 'not_tested';
        
        // Test API connection if credentials are available
        if ($apiKey && $apiSecret) {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
        }
        
        // Generate plugin notices (missing dependencies, etc.)
        $notices = $this->generateNotices();
        
        // Determine active tab from URL parameter
        $active_tab = $_GET['tab'] ?? 'general';
        
        // Generate content for the active tab
        $tab_content = $this->getTabContent($active_tab);
        
        // Render the complete settings page
        $this->renderer->renderView('settings-page', compact('notices', 'active_tab', 'tab_content', 'apiStatus'));
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
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
            exit;
        }
        
        // Handle clear all debug logs
        if (isset($_POST['clear_debug_log'])) {
            // Log the clear action before clearing (so it gets recorded)
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
            
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
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
            wp_redirect(admin_url('admin.php?page=ctm-settings&tab=debug'));
            exit;
        }
        
        // Handle general plugin settings
        if (isset($_POST['ctm_api_key'])) {
            $this->saveGeneralSettings();
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
        $trackingEnabled = isset($_POST['ctm_api_tracking_enabled']);
        $cf7Enabled = isset($_POST['ctm_api_cf7_enabled']);
        $gfEnabled = isset($_POST['ctm_api_gf_enabled']);
        $dashboardEnabled = isset($_POST['ctm_api_dashboard_enabled']);
        $autoInjectTracking = isset($_POST['ctm_auto_inject_tracking_script']) ? 1 : 0;
        
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
        update_option('ctm_api_tracking_enabled', $trackingEnabled);
        update_option('ctm_api_cf7_enabled', $cf7Enabled);
        update_option('ctm_api_gf_enabled', $gfEnabled);
        update_option('ctm_api_dashboard_enabled', $dashboardEnabled);
        update_option('ctm_auto_inject_tracking_script', $autoInjectTracking);
        
        // Save tracking script if provided
        if (isset($_POST['call_track_account_script'])) {
            // Save as raw HTML, not entities
            $raw_script = wp_unslash($_POST['call_track_account_script']);
            update_option('call_track_account_script', wp_kses_post($raw_script));
        } else {
            // Auto-fetch tracking code from CTM API if credentials are present
            if (!empty($apiKey) && !empty($apiSecret)) {
                $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
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
        
        // Log the settings change
        $this->loggingSystem->logActivity('General settings saved', 'config', [
            'tracking_enabled' => $trackingEnabled,
            'cf7_enabled' => $cf7Enabled,
            'gf_enabled' => $gfEnabled,
            'dashboard_enabled' => $dashboardEnabled
        ]);
        
        // Redirect to prevent form resubmission
        wp_redirect(add_query_arg(['tab' => 'general'], wp_get_referer()));
        exit;
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
                    $api = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
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
    // Delegation Methods for Field Mapping
    // ===================================================================

    /**
     * Get field mapping for a form (delegated to FieldMapping class)
     * 
     * @since 2.0.0
     * @param string     $form_type The form type ('gf' or 'cf7')
     * @param string|int $form_id   The form ID
     * @return array|null The field mapping or null if not found
     */
    public function getFieldMapping(string $form_type, $form_id): ?array
    {
        return $this->fieldMapping->getFieldMapping($form_type, $form_id);
    }

    /**
     * Save field mapping for a form (delegated to FieldMapping class)
     * 
     * @since 2.0.0
     * @param string     $form_type The form type ('gf' or 'cf7')
     * @param string|int $form_id   The form ID
     * @param array      $mapping   The field mapping array
     * @return void
     */
    public function saveFieldMapping(string $form_type, $form_id, array $mapping): void
    {
        $this->fieldMapping->saveFieldMapping($form_type, $form_id, $mapping);
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
    public static function logDebug($message): void
    {
        LoggingSystem::logDebug($message);
    }

    /**
     * Check if debug mode is enabled (delegated to LoggingSystem)
     * 
     * @since 2.0.0
     * @return bool True if debug mode is enabled
     */
    public static function isDebugEnabled(): bool
    {
        return LoggingSystem::isDebugEnabled();
    }
} 