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
 * Description:       A call tracking solution for WordPress - tracks errors, analytics, security, performance and more
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
    error_log("CTM Plugin Error: $message in $file on line $line");
    return true;
});

// Set up exception handler
set_exception_handler(function($exception) {
    error_log("CTM Plugin Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
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

        // Register core WordPress hooks
        $this->registerCoreHooks();
        
        // Register plugin functionality hooks
        $this->registerPluginHooks();
        
        // Register conditional hooks (dashboard widgets)
        $this->registerConditionalHooks();

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
            
            // Localize general tab data
            wp_localize_script('ctm-general-tab-js', 'ctmGeneralData', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_dismiss_notice'),
                'testNonce' => wp_create_nonce('ctm_test_api_connection'),
            ]);
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
        
        // Get form submission data
        $dataObject = \WPCF7_Submission::get_instance();
        $data = $dataObject->get_posted_data();
        
        // Process the submission through CF7 service
        $result = $this->cf7Service->processSubmission($form, $data);
        
        // Send processed data to CTM API if credentials are available
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        
        if ($result && $apiKey && $apiSecret) {
            $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret, $form->id());
            
            // Log the submission for debugging and monitoring
            $this->loggingSystem->logDebug([
                'type' => 'cf7',
                'form_id' => $form->id(),
                'form_title' => $form->title(),
                'payload' => $result,
                'response' => $response,
                'timestamp' => current_time('mysql')
            ]);

            // Check for API errors and surface to user
            if (!$response || (isset($response['status']) && $response['status'] !== 'success')) {
                $reason = $response['reason'] ?? 'Unknown error';
                $userMessage = '';
                if (stripos($reason, 'throttle') !== false || stripos($reason, 'rate limit') !== false) {
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
            $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret, $form['id']);
            
            // Log the submission for debugging and monitoring
            $this->loggingSystem->logDebug([
                'type' => 'gf',
                'form_id' => $form['id'],
                'form_title' => $form['title'],
                'entry_id' => $entry['id'],
                'payload' => $result,
                'response' => $response,
                'timestamp' => current_time('mysql')
            ]);
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
