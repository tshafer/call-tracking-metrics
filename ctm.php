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

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit('Direct access forbidden.');
}

// Load Composer autoloader for dependency management
require_once __DIR__ . '/vendor/autoload.php';

// Import required classes
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;
use CTM\Admin\Options;
use CTM\Admin\LoggingSystem;

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
        // Initialize core services
        $this->apiService = new ApiService($this->ctmHost);
        $this->cf7Service = new CF7Service();
        $this->gfService = new GFService();
        $this->adminOptions = new Options();

        // Initialize logging system (must be done early)
        LoggingSystem::initializeLoggingSystem();

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
        add_filter('gform_confirmation', [$this, 'gfConfirmation'], 10, 1);
        add_action('wp_footer', [$this, 'cf7Confirmation'], 10, 1);
    }

    /**
     * Register conditional hooks based on settings
     * 
     * @since 2.0.0
     * @return void
     */
    private function registerConditionalHooks(): void
    {
        // Add dashboard widget if enabled in settings
        if (get_option('ctm_api_dashboard_enabled')) {
            add_action('wp_dashboard_setup', [$this->adminOptions, 'addDashboardWidget']);
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
        // Only inject tracking script on frontend pages
        if (!is_admin()) {
            echo $this->getTrackingScript();
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
            $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret);
            
            // Log the submission for debugging and monitoring
            LoggingSystem::logDebug([
                'type' => 'cf7',
                'form_id' => $form->id(),
                'form_title' => $form->title(),
                'payload' => $result,
                'response' => $response,
                'timestamp' => current_time('mysql')
            ]);
        }
    }

    /**
     * Handle Gravity Forms confirmation (placeholder for future enhancement)
     * 
     * Currently returns the confirmation as-is. This method can be enhanced
     * to modify the confirmation message or add tracking code.
     * 
     * @since 2.0.0
     * @param string|array $confirmation The confirmation message or redirect
     * @return string|array The potentially modified confirmation
     */
    public function gfConfirmation($confirmation)
    {
        // Future enhancement: Add custom confirmation tracking
        return $confirmation;
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
            $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret);
            
            // Log the submission for debugging and monitoring
            LoggingSystem::logDebug([
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
            if (substr($script, 0, 2) === '//') {
                return '<script data-cfasync="false" async src="' . esc_url($script) . '"></script>';
            }
            // Return script as-is (may contain inline JavaScript)
            return $script;
        }
        
        // Use account-specific tracking script
        $accountId = get_option('ctm_api_auth_account');
        return '<script data-cfasync="false" async src="//' . esc_attr($accountId) . '.tctm.co/t.js"></script>';
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
new CallTrackingMetrics();
