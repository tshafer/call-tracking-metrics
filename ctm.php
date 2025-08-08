<?php
/**
 * CallTrackingMetrics WordPress Plugin
 * 
 * This plugin integrates WordPress with CallTrackingMetrics (CTM) service to provide
 * comprehensive call tracking, form submission tracking, and analytics capabilities.
 * 
 * Features:
 * - API integration with CTM service
 * - Contact Form 7 (CF7) integration
 * - Gravity Forms (GF) integration  
 * - Debug logging and monitoring
 * - Dashboard widgets and analytics
 * - AJAX-powered admin interface
 * - Field mapping between forms and CTM
 * 
 * @package     CallTrackingMetrics
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @link        https://calltrackingmetrics.com
 * 
 * @wordpress-plugin
 * Plugin Name:       CallTrackingMetrics
 * Plugin URI:        https://calltrackingmetrics.com
 * Description:       A call tracking solution for WordPress.
 * Version:           2.0
 * Author:            CallTrackingMetrics Team
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       call-tracking-metrics
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      8.0
 * Network:           false
 */

// Prevent direct access to this file, except during tests or CLI
if (!defined('ABSPATH') && !defined('CTM_TESTING') && php_sapi_name() !== 'cli') {
    exit('Direct access forbidden.');
}

// Set up error handling to prevent blank pages
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    
    // Try to use internal logging if available
    if (class_exists('CTM\Admin\LoggingSystem')) {
        try {
            $loggingSystem = new CTM\Admin\LoggingSystem();
            if ($loggingSystem->isDebugEnabled()) {
                $loggingSystem->logActivity("Plugin Error: $message in $file on line $line", 'error');
            }
        } catch (Exception $e) {
            // Silently fail to avoid server log pollution
        }
    }
    
    return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
    // Try to use internal logging if available
    if (class_exists('CTM\Admin\LoggingSystem')) {
        try {
            $loggingSystem = new CTM\Admin\LoggingSystem();
            if ($loggingSystem->isDebugEnabled()) {
                $loggingSystem->logActivity("Plugin Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine(), 'error');
            }
        } catch (Exception $e) {
            // Silently fail to avoid server log pollution
        }
    }
    
    if (is_admin()) {
        echo '<div class="wrap"><div class="notice notice-error"><p>Plugin Error: Call Tracking Metrics encountered an error. Please check the error logs or contact support.</p></div></div>';
    }
});

// Load Composer autoloader for dependency management
require_once __DIR__ . '/vendor/autoload.php';

// Define plugin file constant for use throughout the plugin
if (!defined('CTM_PLUGIN_FILE')) {
    define('CTM_PLUGIN_FILE', __FILE__);
}

// Import required classes
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;
use CTM\Admin\Options;
use CTM\Admin\LoggingSystem;

/**
 * Get the CTM API base URL
 * 
 * Returns the configured API URL from settings, or the default URL if not set.
 * This function provides a centralized way to access the API URL throughout the plugin.
 * 
 * @since 2.0.0
 * @return string The API base URL
 */
if (!function_exists('ctm_get_api_url')) {
function ctm_get_api_url(): string
{
    $api_url = get_option('ctm_api_base_url', 'https://api.calltrackingmetrics.com');
    
    // Ensure URL is properly formatted
    $api_url = trim($api_url);
    
    // If empty, return default
    if (empty($api_url)) {
        return 'https://api.calltrackingmetrics.com';
    }
    
    // Ensure URL has protocol
    if (!preg_match('/^https?:\/\//', $api_url)) {
        $api_url = 'https://' . $api_url;
    }
    
    // Remove trailing slash
    return rtrim($api_url, '/');
}
}

/**
 * Main CallTrackingMetrics Plugin Class
 * 
 * This is the core plugin class that orchestrates all functionality including:
 * - Service integrations (API, CF7, GF)
 * - Admin interface management
 * - WordPress hook registration
 * - Form submission handling
 * - Tracking script injection
 * 
 * @since 2.0.0
 */
class CallTrackingMetrics
{
    /**
     * API service instance for CTM communication
     * 
     * @since 2.0.0
     * @var ApiService
     */
    private ApiService $apiService;

    /**
     * Contact Form 7 service handler
     * 
     * @since 2.0.0
     * @var CF7Service
     */
    private CF7Service $cf7Service;

    /**
     * Gravity Forms service handler
     * 
     * @since 2.0.0
     * @var GFService
     */
    private GFService $gfService;

    /**
     * Admin options and settings manager
     * 
     * @since 2.0.0
     * @var Options
     */
    private Options $adminOptions;

    /**
     * Logging system for debugging and monitoring
     * 
     * @since 2.0.0
     * @var LoggingSystem
     */
    private LoggingSystem $loggingSystem;

    /**
     * CTM API host URL
     * 
     * @since 2.0.0
     * @var string
     */
    private string $ctmHost = 'https://api.calltrackingmetrics.com';

    /**
     * Initialize the plugin
     * 
     * Sets up all services, registers WordPress hooks, and initializes
     * the admin interface and logging system.
     * 
     * @since 2.0.0
     */
    public function __construct()
    {
        // Set up global error handling to prevent white screens
        $this->setupGlobalErrorHandling();
        
        // Load plugin translations
        /** @noinspection PhpUndefinedFunctionInspection */
        add_action('init', function() {
            \load_plugin_textdomain('call-tracking-metrics', false, dirname(\plugin_basename(__FILE__)) . '/languages');
        });
        // Initialize core services
        $this->apiService = new ApiService($this->ctmHost);
        $this->cf7Service = new CF7Service();
        $this->gfService = new GFService();
        $this->adminOptions = new Options();
        $this->loggingSystem = new \CTM\Admin\LoggingSystem();
        $this->loggingSystem->initializeLoggingSystem();

        // Initialize admin components (AJAX handlers, mapping assets, etc.)
        $this->adminOptions->initialize();
        
        // Initialize form logs AJAX handlers
        $this->initializeFormLogsAjax();
        
        // Initialize form usage AJAX handlers
        $this->initializeFormUsageAjax();
        
        // Initialize log loading AJAX handlers
        $this->initializeLogLoadingAjax();

        // Register core WordPress hooks
        $this->registerCoreHooks();
        
        // Register plugin functionality hooks
        $this->registerPluginHooks();
        
        // Register conditional hooks (dashboard widgets)
        $this->registerConditionalHooks();
        
        // Check forms for phone numbers and show warnings
        add_action('admin_notices', [$this, 'showPhoneNumberWarnings']);

        // Register activation/deactivation hooks
        $this->registerLifecycleHooks();
    }

    /**
     * Register core WordPress admin hooks
     * 
     * @since 2.0.0
     * @return void
     */
    private function registerCoreHooks(): void
    {
        add_action('admin_init', [$this->adminOptions, 'registerSettings']);
        add_action('admin_menu', [$this->adminOptions, 'registerSettingsPage']);
        
    }

    /**
     * Register plugin functionality hooks
     * 
     * @since 2.0.0
     * @return void
     */
    private function registerPluginHooks(): void
    {
        // Frontend tracking script injection
        add_action('wp_head', [$this, 'printTrackingScript'], 10);
        
        // Form integration initialization
        add_action('init', [$this, 'formInit']);
        
        // Admin dashboard integration
        add_action('admin_menu', [$this, 'attachDashboard']);
        
        // Form confirmation handlers
        add_action('wp_footer', [$this, 'cf7Confirmation'], 10, 1);

        // Enqueue Tailwind CSS for admin pages (settings, debug, etc.)
        add_action('admin_enqueue_scripts', function($hook) {
            // Only enqueue on the CallTrackingMetrics admin pages
            if (strpos($hook, 'call-tracking-metrics') === false) return;

            $css_file = plugin_dir_path(__FILE__) . 'css/optmized.css';
            $version = file_exists($css_file) ? filemtime($css_file) : '2.0.0';
            wp_enqueue_style(
                'ctm-tailwind',
                plugins_url('css/optmized.css', __FILE__),
                [],
                $version
            );
        });
        // Merge: Enqueue debug JS and localize export nonce for debug tab
        add_action('admin_enqueue_scripts', function($hook) {
            // Only enqueue on the CallTrackingMetrics debug/settings page
            if (strpos($hook, 'call-tracking-metrics') === false) return;
            
            wp_enqueue_script(
                'ctm-toast',
                plugins_url('assets/js/toast.js', __FILE__),
                [],
                null,
                true
            );

            // Enqueue debug JS if not already enqueued
            wp_enqueue_script(
                'ctm-debug-js',
                plugins_url('assets/js/debug.js', __FILE__),
                ['jquery'],
                '2.0.0',
                true
            );
            
            // Enqueue API tab JS
            wp_enqueue_script(
                'ctm-api-tab-js',
                plugins_url('assets/js/api-tab.js', __FILE__),
                ['jquery'],
                '2.0.0',
                true
            );
            
            // Enqueue Documentation tab JS
            wp_enqueue_script(
                'ctm-documentation-tab-js',
                plugins_url('assets/js/documentation-tab.js', __FILE__),
                [],
                '2.0.0',
                true
            );
            
            // Enqueue Notice dismiss JS
            wp_enqueue_script(
                'ctm-notice-dismiss-js',
                plugins_url('assets/js/notice-dismiss.js', __FILE__),
                [],
                '2.0.0',
                true
            );
            
            // Localize export diagnostic report nonce
            wp_localize_script('ctm-debug-js', 'ctmDebugVars', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'ctm_export_diagnostic_report_nonce' => wp_create_nonce('ctm_export_diagnostic_report'),
            ]);
            
            // Localize notice dismiss data
            wp_localize_script('ctm-notice-dismiss-js', 'ctmNoticeData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_dismiss_notice'),
            ]);
            
            // Enqueue General tab JS
            wp_enqueue_script(
                'ctm-general-tab-js',
                plugins_url('assets/js/general-tab.js', __FILE__),
                [],
                '2.0.0',
                true
            );
            
            // Enqueue Form Logs JS
            wp_enqueue_script(
                'ctm-form-logs-js',
                plugins_url('assets/js/form-logs.js', __FILE__),
                ['jquery'],
                '2.0.0',
                true
            );
            
            // Enqueue unified preview JS
            wp_enqueue_script(
                'ctm-preview-js',
                plugins_url('assets/js/ctm-preview.js', __FILE__),
                ['jquery'],
                '2.0.0',
                true
            );
            
            // Localize general tab data
            wp_localize_script('ctm-general-tab-js', 'ctmGeneralData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_dismiss_notice'),
                'testNonce' => wp_create_nonce('ctm_test_api_connection'),
            ]);
            
            // Localize form logs data
            wp_localize_script('ctm-form-logs-js', 'ctmFormLogsData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_form_logs'),
                'debug_enabled' => get_option('ctm_debug_enabled', false),
            ]);
            
            // Add modal styles
            wp_add_inline_style('ctm-tailwind', '
                body.ctm-modal-open #adminmenumain,
                body.ctm-modal-open #adminmenuwrap,
                body.ctm-modal-open #adminmenu {
                    display: none !important;
                }
                body.ctm-modal-open #wpcontent {
                    margin-left: 0 !important;
                }
                body.ctm-modal-open #wpfooter {
                    margin-left: 0 !important;
                }
                body.ctm-modal-open #ctm-form-logs-modal {
                    z-index: 999999 !important;
                }
            ');
        });

    }

    /**
     * Register conditional hooks based on settings
     * 
     * @since 2.0.0
     * @return void
     */
    private function registerConditionalHooks(): void
    {
        // Always register the dashboard widget hook, but check the setting inside
        add_action('wp_dashboard_setup', [$this, 'maybeAddDashboardWidget']);
    }
    
    /**
     * Conditionally add dashboard widget based on settings
     * 
     * @since 2.0.0
     * @return void
     */
    public function maybeAddDashboardWidget(): void
    {
        // Only add dashboard widget if enabled in settings
        if (get_option('ctm_dashboard_enabled')) {
            $this->adminOptions->addDashboardWidget();
        }
    }

    /**
     * Set up global error handling to prevent white screens
     * 
     * @since 2.0.0
     * @return void
     */
    private function setupGlobalErrorHandling(): void
    {
        // Set up error handler for fatal errors
        set_error_handler(function($severity, $message, $file, $line) {
            // Only handle errors if they're not being suppressed
            if (!(error_reporting() & $severity)) {
                return false;
            }
            
            // Log the error but don't throw an exception
            if (class_exists('\CTM\Admin\LoggingSystem')) {
                $loggingSystem = new \CTM\Admin\LoggingSystem();
                if ($loggingSystem->isDebugEnabled()) {
                    $loggingSystem->logActivity("PHP Error: {$message} in {$file} on line {$line}", 'error');
                }
            }
            
            // Return false to let PHP handle the error normally
            return false;
        });
        
        // Set up exception handler for uncaught exceptions
        set_exception_handler(function($exception) {
            // Log the exception but don't cause a white screen
            if (class_exists('\CTM\Admin\LoggingSystem')) {
                $loggingSystem = new \CTM\Admin\LoggingSystem();
                if ($loggingSystem->isDebugEnabled()) {
                    $loggingSystem->logActivity("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine(), 'error');
                }
            }
            
            // Don't output anything to prevent white screens
            return true;
        });
    }

    /**
     * Register plugin activation and deactivation hooks
     * 
     * @since 2.0.0
     * @return void
     */
    private function registerLifecycleHooks(): void
    {
        register_activation_hook(__FILE__, [LoggingSystem::class, 'onPluginActivation']);
        register_deactivation_hook(__FILE__, [LoggingSystem::class, 'onPluginDeactivation']);
    }

    /**
     * Print the CTM tracking script in the site head
     * 
     * Injects the CallTrackingMetrics JavaScript tracking code into the
     * website's <head> section for visitor and call tracking.
     * 
     * @since 2.0.0
     * @return void
     */
    public function printTrackingScript(): void
    {
        // Only inject tracking script on frontend pages and if enabled
        if (!is_admin() && get_option('ctm_auto_inject_tracking_script')) {
            $script = $this->getTrackingScript();
            if ($script) {
                echo $script;
            }
        }
    }

    /**
     * Initialize form integrations based on enabled plugins
     * 
     * Conditionally sets up Contact Form 7 and Gravity Forms integrations
     * based on plugin availability and user settings.
     * 
     * @since 2.0.0
     * @return void
     */
    public function formInit(): void
    {
        // Initialize Contact Form 7 integration if enabled and active
        if ($this->cf7Enabled() && $this->cf7Active()) {
            add_action('wpcf7_before_send_mail', [$this, 'submitCF7'], 10, 2);
        }
        
        // Initialize Gravity Forms integration if enabled and active
        if ($this->gfEnabled() && $this->gfActive()) {
            add_action('gform_after_submission', [$this, 'submitGF'], 10, 2);
        }
    }

    /**
     * Output Contact Form 7 confirmation tracking JavaScript
     * 
     * Injects JavaScript code that tracks CF7 form submissions
     * for analytics and conversion tracking.
     * 
     * @since 2.0.0
     * @return void
     */
    public function cf7Confirmation(): void
    {
        // JavaScript event listener for CF7 mail sent event
        echo "<script type='text/javascript'>\n";
        echo "document.addEventListener('wpcf7mailsent', function(event) {\n";
        echo "  try { \n";
        echo "    __ctm.tracker.trackEvent('', ' ', 'form'); \n";
        echo "    __ctm.tracker.popQueue(); \n";
        echo "  } catch(e) { \n";
        echo "    console.log('CTM tracking error:', e); \n";
        echo "  }\n";
        echo "}, false);\n";
        echo "</script>";
    }

    /**
     * Handle Contact Form 7 submission and send data to CTM API
     * 
     * Processes CF7 form submissions, formats the data, and sends it
     * to the CallTrackingMetrics API for lead tracking.
     * 
     * @since 2.0.0
     * @param object $form The CF7 form object
     * @param bool   $abort Reference to abort flag
     * @return void
     */
    public function submitCF7($form, &$abort): void
    {
        // Don't process if form submission is aborted
        if (true === $abort) {
            return;
        }
        
        // Set error handler to prevent fatal errors
        $original_error_handler = set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        try {
            // Get form submission data
            $dataObject = \WPCF7_Submission::get_instance();
            
            $data = $dataObject->get_posted_data();
            
            // Process the submission through CF7 service
            $result = $this->cf7Service->processSubmission($form, $data);
            
            // Send processed data to CTM API if credentials are available
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');
            
            if ($result && $apiKey && $apiSecret) {
                try {
                    $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret, $form->id());
                    
                    // Log the submission for debugging and monitoring
                    $this->loggingSystem->logFormSubmission(
                        'cf7',
                        $form->id(),
                        $form->title(),
                        $result,
                        $response,
                        ['entry_id' => null] // CF7 doesn't have entry IDs like GF
                    );

                    // Check for API errors and surface to user
                    if (!$response || (isset($response['status']) && $response['status'] !== 'success')) {
                        $reason = $response['reason'] ?? 'Unknown error';
                        $errorType = $response['error_type'] ?? '';
                        $userMessage = '';
                        
                        // Handle specific error types
                        if ($errorType === 'phone_format') {
                            $userMessage = 'Please enter a valid phone number in international format (e.g., +1234567890).';
                        } elseif (stripos($reason, 'throttle') !== false || stripos($reason, 'rate limit') !== false) {
                            $userMessage = 'You are submitting too quickly. Please wait a moment and try again.';
                        } elseif (stripos($reason, 'phone') !== false) {
                            $userMessage = 'A valid phone number is required.';
                        } elseif (stripos($reason, 'email') !== false) {
                            $userMessage = 'A valid email address is required.';
                        } elseif (stripos($reason, 'required') !== false) {
                            $userMessage = 'Please fill out all required fields.';
                        } else {
                            $userMessage = 'Submission failed: ' . esc_html($reason);
                        }
                        $abort = true;
                        // Show error to user using CF7 API
                        if (method_exists($form, 'set_invalid_fields')) {
                            $form->set_invalid_fields([['name' => '', 'reason' => $userMessage]]);
                        } elseif (method_exists($form, 'set_invalid_field')) {
                            $form->set_invalid_field('', $userMessage);
                        }
                        // No fallback: $abort = true will prevent mail send and CF7 will show a generic error
                    }
                } catch (\Throwable $e) {
                    // Log the API error (catch both Exception and Error)
                    $this->logInternal('CF7 API Error: ' . $e->getMessage(), 'error');
                    
                    // Don't abort the form submission - just log the error
                    // This prevents white screens while still tracking the issue
                    $this->loggingSystem->logFormSubmission(
                        'cf7',
                        $form->id(),
                        $form->title(),
                        $result,
                        ['error' => $e->getMessage()],
                        ['entry_id' => null]
                    );
                }
            }
        } catch (\Throwable $e) {
            // Log any unexpected errors but don't break the form submission
            $this->logInternal('CF7 Submission Error: ' . $e->getMessage(), 'error');
            
            // Don't set $abort = true to prevent white screens
            // Just log the error and let the form submission continue
        } finally {
            // Restore original error handler
            if ($original_error_handler) {
                set_error_handler($original_error_handler);
            }
        }
    }

    /**
     * Handle Gravity Forms submission and send data to CTM API
     * 
     * Processes GF form submissions, formats the data, and sends it
     * to the CallTrackingMetrics API for lead tracking.
     * 
     * @since 2.0.0
     * @param array $entry The GF entry data
     * @param array $form  The GF form configuration
     * @return void
     */
    public function submitGF($entry, $form): void
    {
        // Set error handler to prevent fatal errors
        $original_error_handler = set_error_handler(function($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
        
        try {
            // Process the submission through GF service
            $result = $this->gfService->processSubmission($entry, $form);
       
            // Skip if processing failed
            if ($result === null) {
                return;
            }
            
            // Send processed data to CTM API if credentials are available
            $apiKey = get_option('ctm_api_key');
            $apiSecret = get_option('ctm_api_secret');
            
            if ($result && $apiKey && $apiSecret) {
                try {
                    $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret, $form['id']);

                    // Log the submission for debugging and monitoring
                    $this->loggingSystem->logFormSubmission(
                        'gf',
                        $form['id'],
                        $form['title'],
                        $result,
                        $response,
                        ['entry_id' => $entry['id']]
                    );
                    
                    // Check for API errors and log them
                    if (!$response || (isset($response['status']) && $response['status'] !== 'success')) {
                        $reason = $response['reason'] ?? 'Unknown error';
                        $errorType = $response['error_type'] ?? '';
                        
                        if ($errorType === 'phone_format') {
                            $this->logInternal('GF Submission Error: Phone number format error - ' . $reason, 'error');
                        } else {
                            $this->logInternal('GF Submission Error: ' . $reason, 'error');
                        }
                    }
                } catch (\Throwable $e) {
                    // Log the API error (catch both Exception and Error)
                    $this->logInternal('GF API Error: ' . $e->getMessage(), 'error');
                    
                    // Don't break the form submission - just log the error
                    // This prevents white screens while still tracking the issue
                    $this->loggingSystem->logFormSubmission(
                        'gf',
                        $form['id'],
                        $form['title'],
                        $result,
                        ['error' => $e->getMessage()],
                        ['entry_id' => $entry['id']]
                    );

                // Return a JSON response to the frontend indicating success or error
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    if (isset($response) && isset($response['status']) && $response['status'] === 'success') {
                        wp_send_json_success([
                            'message' => 'Form submitted successfully',
                            'response' => $response,
                        ]);
                    } else {
                        $errorMsg = $response['reason'] ?? ($e->getMessage() ?? 'Unknown error');
                        wp_send_json_error([
                            'message' => 'Failed to submit form',
                            'error' => $errorMsg,
                        ]);
                    }
                    // Always exit after sending JSON response in AJAX context
                    exit;
                }
                }
            }
        } catch (\Throwable $e) {
            // Log any unexpected errors but don't break the form submission
            $this->logInternal('GF Submission Error: ' . $e->getMessage(), 'error');
            
            // Don't throw the exception to prevent white screens
            // Just log the error and let the form submission continue
        } finally {
            // Restore original error handler
            if ($original_error_handler) {
                set_error_handler($original_error_handler);
            }
        }
    }

    /**
     * Initialize form logs AJAX handlers
     * 
     * @since 2.0.0
     * @return void
     */
    private function initializeFormLogsAjax(): void
    {
        // Register AJAX handlers for form logs
        add_action('wp_ajax_ctm_get_form_logs', [$this, 'ajaxGetFormLogs']);
        add_action('wp_ajax_ctm_clear_form_logs', [$this, 'ajaxClearFormLogs']);
        add_action('wp_ajax_ctm_get_form_log_stats', [$this, 'ajaxGetFormLogStats']);
    }

    /**
     * Initialize form usage AJAX handlers
     * 
     * @since 2.0.0
     * @return void
     */
    private function initializeFormUsageAjax(): void
    {
        // Initialize the FormUsageAjax handler
        new \CTM\Admin\Ajax\FormUsageAjax();
    }

    /**
     * Initialize log loading AJAX handlers
     * 
     * @since 2.0.0
     * @return void
     */
    private function initializeLogLoadingAjax(): void
    {
        add_action('wp_ajax_ctm_load_more_logs', [$this, 'ajaxLoadMoreLogs']);
        add_action('wp_ajax_ctm_load_more_days', [$this, 'ajaxLoadMoreDays']);
    }

    /**
     * AJAX handler for getting form-specific logs
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxGetFormLogs(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = (int) ($_POST['form_id'] ?? 0);
        
        if (empty($form_type) || empty($form_id)) {
            wp_send_json_error(['message' => 'Form type and form ID are required']);
        }
        
        try {
            $logs = $this->loggingSystem->getFormLogs($form_type, $form_id);
            wp_send_json_success([
                'logs' => $logs,
                'count' => count($logs)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get form logs: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for clearing form-specific logs
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxClearFormLogs(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = (int) ($_POST['form_id'] ?? 0);
        
        if (empty($form_type) || empty($form_id)) {
            wp_send_json_error(['message' => 'Form type and form ID are required']);
        }
        
        try {
            $this->loggingSystem->clearFormLogs($form_type, $form_id);
            wp_send_json_success(['message' => 'Form logs cleared successfully']);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to clear form logs: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for getting form log statistics
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxGetFormLogStats(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $stats = $this->loggingSystem->getFormLogStatistics();
            wp_send_json_success($stats);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get form log statistics: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for loading more logs
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxLoadMoreLogs(): void
    {
        check_ajax_referer('ctm_load_more_logs', 'nonce');

        $date = sanitize_text_field($_POST['date'] ?? '');
        $offset = (int) ($_POST['offset'] ?? 0);
        $limit = (int) ($_POST['limit'] ?? 20);

        if (empty($date)) {
            wp_send_json_error(['message' => 'Date is required']);
            return;
        }

        try {
            // Get logs for the specific date using the new database method
            $logs = $this->loggingSystem->getLogsForDate($date);
            
            if (empty($logs)) {
                wp_send_json_error(['message' => 'No logs found for this date']);
                return;
            }

            // Get the subset of logs based on offset and limit
            $total_count = count($logs);
            $entries = array_slice($logs, $offset, $limit);
            $has_more = ($offset + $limit) < $total_count;

            wp_send_json_success([
                'entries' => $entries,
                'total' => $total_count,
                'has_more' => $has_more,
                'offset' => $offset + $limit
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to load more logs: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for loading more days
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxLoadMoreDays(): void
    {
        check_ajax_referer('ctm_load_more_days', 'nonce');
        $offset = (int) ($_POST['offset'] ?? 0);
        $limit = (int) ($_POST['limit'] ?? 5);
        
        try {
            // Get available dates using the new database method
            $available_dates = $this->loggingSystem->getAvailableLogDates();
            if (empty($available_dates)) {
                wp_send_json_error(['message' => 'No log dates found']);
                return;
            }
            
            $total_days = count($available_dates);
            $requested_dates = array_slice($available_dates, $offset, $limit);
            $has_more = ($offset + $limit) < $total_days;
            
            $days = [];
            foreach ($requested_dates as $date) {
                $logs = $this->loggingSystem->getLogsForDate($date);
                if (empty($logs)) continue;
                
                $error_count = 0;
                $warning_count = 0;
                $info_count = 0;
                $debug_count = 0;
                
                foreach ($logs as $entry) {
                    switch ($entry['type']) {
                        case 'error': $error_count++; break;
                        case 'warning': $warning_count++; break;
                        case 'info': $info_count++; break;
                        case 'debug': $debug_count++; break;
                    }
                }
                
                $days[] = [
                    'date' => $date,
                    'logs' => $logs,
                    'error_count' => $error_count,
                    'warning_count' => $warning_count,
                    'info_count' => $info_count,
                    'debug_count' => $debug_count,
                    'total_count' => count($logs)
                ];
            }
            
            wp_send_json_success([
                'days' => $days,
                'total_days' => $total_days,
                'has_more' => $has_more,
                'offset' => $offset + $limit
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to load more days: ' . $e->getMessage()]);
        }
    }

    /**
     * Attach dashboard widget (stub for future modularization)
     * 
     * This method is reserved for future dashboard functionality.
     * Currently serves as a placeholder for additional dashboard features.
     * 
     * @since 2.0.0
     * @return void
     */
    public function attachDashboard(): void
    {
        // Future enhancement: Additional dashboard functionality
    }

    /**
     * Check for forms without phone numbers and show warnings
     * 
     * @since 2.0.0
     * @return void
     */
    public function checkFormsWithoutPhone(): void
    {
        $formsWithoutPhone = [];
        
        // Check CF7 forms
        if ($this->cf7Active()) {
            $cf7Forms = \WPCF7_ContactForm::find(['posts_per_page' => -1]);
            foreach ($cf7Forms as $form) {
                if (!$this->cf7Service->hasPhoneField($form)) {
                    $formsWithoutPhone[] = [
                        'type' => 'CF7',
                        'id' => $form->id(),
                        'title' => $form->title(),
                        'edit_url' => admin_url('admin.php?page=wpcf7&post=' . $form->id() . '&action=edit')
                    ];
                }
            }
        }
        
        // Check GF forms
        if ($this->gfActive()) {
            $gfForms = \GFAPI::get_forms();
            foreach ($gfForms as $form) {
                if (!$this->gfService->hasPhoneField($form)) {
                    $formsWithoutPhone[] = [
                        'type' => 'GF',
                        'id' => $form['id'],
                        'title' => $form['title'],
                        'edit_url' => admin_url('admin.php?page=gf_edit_forms&id=' . $form['id'])
                    ];
                }
            }
        }
        
        // Store the results for display
        if (!empty($formsWithoutPhone)) {
            update_option('ctm_forms_without_phone', $formsWithoutPhone);
        } else {
            delete_option('ctm_forms_without_phone');
        }
    }

    /**
     * Show warnings for forms without phone numbers
     * 
     * @since 2.0.0
     * @return void
     */
    public function showPhoneNumberWarnings(): void
    {
        // Only show on CTM admin pages
        if (!isset($_GET['page']) || $_GET['page'] !== 'call-tracking-metrics') {
            return;
        }
        
        // Check for forms without phone numbers
        $this->checkFormsWithoutPhone();
        $formsWithoutPhone = get_option('ctm_forms_without_phone', []);
        
        if (!empty($formsWithoutPhone)) {
            $count = count($formsWithoutPhone);
            $formTypes = array_unique(array_column($formsWithoutPhone, 'type'));
            $formTypeText = implode(' and ', $formTypes);
            
            echo '<div class="notice notice-warning is-dismissible">';
            echo '<p><strong>CallTrackingMetrics Warning:</strong> ';
            echo sprintf(
                '%d form%s without phone number fields detected. Forms without phone numbers will not work with CTM. ',
                $count,
                $count === 1 ? '' : 's'
            );
            echo '<a href="' . admin_url('admin.php?page=call-tracking-metrics&tab=forms') . '">View forms</a>';
            echo '</p>';
            
            if ($count <= 5) {
                echo '<ul style="margin-left: 20px; margin-top: 10px;">';
                foreach ($formsWithoutPhone as $form) {
                    echo '<li><strong>' . esc_html($form['title']) . '</strong> (' . esc_html($form['type']) . ') - ';
                    echo '<a href="' . esc_url($form['edit_url']) . '">Edit form</a></li>';
                }
                echo '</ul>';
            }
            
            echo '</div>';
        }
    }

    // ===================================================================
    // Helper Methods for Plugin State Management
    // ===================================================================

    /**
     * Check if Contact Form 7 integration is enabled
     * 
     * @since 2.0.0
     * @return bool True if CF7 integration is enabled
     */
    private function cf7Enabled(): bool 
    { 
        return (bool) get_option('ctm_api_cf7_enabled', true); 
    }

    /**
     * Check if Contact Form 7 plugin is active
     * 
     * @since 2.0.0
     * @return bool True if CF7 plugin is active
     */
    private function cf7Active(): bool 
    { 
        return is_plugin_active('contact-form-7/wp-contact-form-7.php'); 
    }

    /**
     * Check if Gravity Forms integration is enabled
     * 
     * @since 2.0.0
     * @return bool True if GF integration is enabled
     */
    private function gfEnabled(): bool 
    { 
        return (bool) get_option('ctm_api_gf_enabled', true); 
    }

    /**
     * Check if Gravity Forms plugin is active
     * 
     * @since 2.0.0
     * @return bool True if GF plugin is active
     */
    private function gfActive(): bool 
    { 
        return is_plugin_active('gravityforms/gravityforms.php'); 
    }

    /**
     * Generate the appropriate tracking script for the site
     * 
     * Returns either a custom tracking script or the default CTM tracking
     * script based on authentication status and configuration.
     * 
     * @since 2.0.0
     * @return string The tracking script HTML
     */
    private function getTrackingScript(): string
    {
        $script = get_option('call_track_account_script');
        // Use custom script if not authorizing or not authorized
        if (!$this->authorizing() || !$this->authorized()) {
            // Handle protocol-relative URLs
            if ($script && substr($script, 0, 2) === '//') {
                return '<script data-cfasync="false" async src="' . esc_url($script) . '"></script>';
            }
            // Return script as-is if it looks like a <script> tag
            if ($script && strpos($script, '<script') !== false) {
                return $script;
            }
            // If it's a raw JS snippet, wrap it in a <script> tag
            if ($script && trim($script) !== '') {
                return '<script data-cfasync="false" async>' . $script . '</script>';
            }
            return '';
        }
        // Use account-specific tracking script
        $accountId = get_option('ctm_api_auth_account');
        if ($accountId) {
            return '<script data-cfasync="false" async src="//' . esc_attr($accountId) . '.tctm.co/t.js"></script>';
        }
        return '';
    }

    /**
     * Check if the plugin is in authorization mode
     * 
     * @since 2.0.0
     * @return bool True if API credentials are provided
     */
    private function authorizing(): bool
    {
        return (bool) (get_option('ctm_api_key') && get_option('ctm_api_secret'));
    }

    /**
     * Check if the plugin is properly authorized with CTM
     * 
     * @since 2.0.0
     * @return bool True if authorized or credentials are missing
     */
    private function authorized(): bool
    {
        return (bool) (get_option('ctm_api_auth_account') || !get_option('ctm_api_key') || !get_option('ctm_api_secret'));
    }
}

// ===================================================================
// Plugin Bootstrap
// ===================================================================

/**
 * Initialize the CallTrackingMetrics plugin
 * 
 * This creates the main plugin instance and starts all functionality.
 * The plugin will automatically register all necessary hooks and
 * initialize all services.
 */
// Only instantiate if not running under test
if (!defined('CTM_TESTING')) {
    new CallTrackingMetrics();
}

add_action('admin_init', function() {
    global $wp_version;
    $min_wp = '6.5';
    $min_gf = '2.7';
    $min_cf7 = '5.7';
    $gf_active = class_exists('GFAPI');
    $cf7_active = class_exists('WPCF7_ContactForm');
    $notices = [];
    if (version_compare($wp_version, $min_wp, '<')) {
        $notices[] = 'CallTrackingMetrics requires WordPress ' . $min_wp . ' or higher. You are running ' . $wp_version . '.';
    }
    if ($gf_active) {
        if (defined('GF_VERSION')) {
            $gf_version = constant('GF_VERSION');
            if (version_compare($gf_version, $min_gf, '<')) {
                $notices[] = 'CallTrackingMetrics requires Gravity Forms ' . $min_gf . ' or higher. You are running ' . $gf_version . '.';
            }
        } // else: skip version check if GF_VERSION is not defined
    }
    // if ($cf7_active && defined('WPCF7_VERSION') && version_compare(WPCF7_VERSION, $min_cf7, '<')) {
    //     $notices[] = 'CallTrackingMetrics requires Contact Form 7 ' . $min_cf7 . ' or higher. You are running ' . WPCF7_VERSION . '.';
    // }
    // if(!get_option('call_track_account_script')) {
    //     $notices[] = 'CallTrackingMetrics tracking script is missing. Please save your API credentials to generate the tracking script.';
    // }
    if (!empty($notices)) {
        add_action('admin_notices', function() use ($notices) {
            foreach ($notices as $msg) {
                echo '<div class="notice notice-error"><p>' . esc_html($msg) . '</p></div>';
            }
        });
        // Optionally, disable integrations if version is not met
        update_option('ctm_api_gf_enabled', false);
        update_option('ctm_api_cf7_enabled', false);
    }
});

add_action('admin_footer', function() {
    echo '<div id="ctm-toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;"></div>';
});
