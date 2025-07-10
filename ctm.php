<?php
/*
Plugin Name: CallTrackingMetrics
Plugin URI: https://calltrackingmetrics.com
Description: A call tracking solution for WordPress - tracks errors, analytics, security, performance and more
Version: 2.0
Author: CallTrackingMetrics Team
*/

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/vendor/autoload.php';

use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;
use CTM\Admin\Options;
use CTM\Admin\LoggingSystem;

class CallTrackingMetrics
{
    private ApiService $apiService;
    private CF7Service $cf7Service;
    private GFService $gfService;
    private Options $adminOptions;
    private string $ctmHost = 'https://api.calltrackingmetrics.com';

    public function __construct()
    {
        $this->apiService = new ApiService($this->ctmHost);
        $this->cf7Service = new CF7Service();
        $this->gfService = new GFService();
        $this->adminOptions = new Options();

        // Initialize logging system once
        LoggingSystem::initializeLoggingSystem();

        // Initialize admin components (includes AJAX handlers and mapping assets)
        $this->adminOptions->initialize();

        // Register WordPress hooks
        add_action('admin_init', [$this->adminOptions, 'registerSettings']);
        add_action('admin_menu', [$this->adminOptions, 'registerSettingsPage']);
        
        // Plugin functionality hooks
        add_action('wp_head', [$this, 'printTrackingScript'], 10);
        add_action('init', [$this, 'formInit']);
        add_action('admin_menu', [$this, 'attachDashboard']);
        add_filter('gform_confirmation', [$this, 'gfConfirmation'], 10, 1);
        add_action('wp_footer', [$this, 'cf7Confirmation'], 10, 1);
        
        if (get_option('ctm_api_dashboard_enabled')) {
            add_action('wp_dashboard_setup', [$this->adminOptions, 'addDashboardWidget']);
        }

        // Register activation/deactivation hooks
        register_activation_hook(__FILE__, [LoggingSystem::class, 'onPluginActivation']);
        register_deactivation_hook(__FILE__, [LoggingSystem::class, 'onPluginDeactivation']);
    }

    /** Print the tracking script in the site head. */
    public function printTrackingScript(): void
    {
        if (!is_admin()) {
            echo $this->getTrackingScript();
        }
    }

    /** Initialize form integrations. */
    public function formInit(): void
    {
        if ($this->cf7Enabled() && $this->cf7Active()) {
            add_action('wpcf7_before_send_mail', [$this, 'submitCF7'], 10, 2);
        }
        if ($this->gfEnabled() && $this->gfActive()) {
            add_action('gform_after_submission', [$this, 'submitGF'], 10, 2);
        }
    }

    /** Output the CF7 confirmation JS. */
    public function cf7Confirmation(): void
    {
        echo "<script type='text/javascript'>\ndocument.addEventListener('wpcf7mailsent', function(event) {\n  try { __ctm.tracker.trackEvent('', ' ', 'form'); __ctm.tracker.popQueue(); } catch(e) { console.log(e); }\n}, false);\n</script>";
    }

    /** Handle CF7 submission and send to API. */
    public function submitCF7($form, &$abort): void
    {
        if (true === $abort) return;
        $dataObject = \WPCF7_Submission::get_instance();
        $data = $dataObject->get_posted_data();
        $result = $this->cf7Service->processSubmission($form, $data);
        // Send $result to API using $this->apiService
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if ($result && $apiKey && $apiSecret) {
            $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret);
            LoggingSystem::logDebug([
                'type' => 'cf7',
                'payload' => $result,
                'response' => $response
            ]);
        }
    }

    /** Output the GF confirmation JS. */
    public function gfConfirmation($confirmation)
    {
        return $confirmation;
    }

    /** Handle GF submission and send to API. */
    public function submitGF($entry, $form): void
    {
        $result = $this->gfService->processSubmission($entry, $form);
        if ($result === null) return;
        // Send $result to API using $this->apiService
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if ($result && $apiKey && $apiSecret) {
            $response = $this->apiService->submitFormReactor($result, $apiKey, $apiSecret);
            LoggingSystem::logDebug([
                'type' => 'gf',
                'payload' => $result,
                'response' => $response
            ]);
        }
    }

    /** Attach dashboard widget (stub for future modularization). */
    public function attachDashboard(): void {}

    // --- Helper methods for plugin state ---
    private function cf7Enabled(): bool { return (bool) get_option('ctm_api_cf7_enabled', true); }
    private function cf7Active(): bool { return is_plugin_active('contact-form-7/wp-contact-form-7.php'); }
    private function gfEnabled(): bool { return (bool) get_option('ctm_api_gf_enabled', true); }
    private function gfActive(): bool { return is_plugin_active('gravityforms/gravityforms.php'); }

    /** Get the tracking script for the site. */
    private function getTrackingScript(): string
    {
        $script = get_option('call_track_account_script');
        if (!$this->authorizing() || !$this->authorized()) {
            if (substr($script, 0, 2) === '//') {
                return '<script data-cfasync="false" async src="' . $script . '"></script>';
            }
            return $script;
        }
        return '<script data-cfasync="false" async src="//' . get_option('ctm_api_auth_account') . '.tctm.co/t.js"></script>';
    }

    private function authorizing(): bool
    {
        return (bool) (get_option('ctm_api_key') && get_option('ctm_api_secret'));
    }
    private function authorized(): bool
    {
        return (bool) (get_option('ctm_api_auth_account') || !get_option('ctm_api_key') || !get_option('ctm_api_secret'));
    }
}

// Bootstrap the plugin
new CallTrackingMetrics();
