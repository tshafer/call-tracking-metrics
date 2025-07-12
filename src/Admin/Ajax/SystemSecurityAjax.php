<?php
namespace CTM\Admin\Ajax;

use CTM\Admin\LoggingSystem;
use CTM\Admin\SettingsRenderer;

class SystemSecurityAjax {
    private $loggingSystem;
    private $renderer;

    public function __construct(LoggingSystem $loggingSystem, SettingsRenderer $renderer)
    {
        $this->loggingSystem = $loggingSystem;
        $this->renderer = $renderer;
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_security_scan', [$this, 'ajaxSecurityScan']);
    }

    /**
     * AJAX: Security Vulnerability Scan
     */
    public function ajaxSecurityScan(): void
    {
        check_ajax_referer('ctm_security_scan', 'nonce');
        $score = 100;
        $vulnerabilities = [];
        $recommendations = [];
        $details = [];
        // Example: Check for vulnerable plugins (simplified)
        $plugins = get_plugins();
        foreach ($plugins as $plugin_file => $plugin_data) {
            // Check for known vulnerable plugins (example: hardcoded, real implementation would use an API)
            $vuln_plugins = [
                'hello.php' => 'Hello Dolly (example)',
            ];
            if (isset($vuln_plugins[$plugin_file])) {
                $score -= 20;
                $vulnerabilities[] = [
                    'title' => 'Vulnerable plugin detected',
                    'description' => $vuln_plugins[$plugin_file],
                    'severity' => 'high'
                ];
                $recommendations[] = 'Deactivate or remove vulnerable plugins.';
            }
        }
        // Clamp score
        $score = max(0, min(100, $score));
        wp_send_json_success([
            'results' => [
                'security_score' => $score,
                'vulnerabilities' => $vulnerabilities,
                'recommendations' => $recommendations,
                'details' => $details
            ]
        ]);
    }
} 