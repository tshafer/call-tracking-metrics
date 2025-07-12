<?php
namespace CTM\Admin\Ajax;

use CTM\Service\ApiService;

class ApiAjax {
    private $apiService;

    public function __construct($apiService = null) {
        $this->apiService = $apiService ?: new ApiService('https://api.calltrackingmetrics.com');
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_test_api_connection', [$this, 'ajaxTestApiConnection']);
        add_action('wp_ajax_ctm_simulate_api_request', [$this, 'ajaxSimulateApiRequest']);
        // AJAX: Change API Keys
        add_action('wp_ajax_ctm_change_api_keys', [$this, 'ajaxChangeApiKeys']);
        // AJAX: Disable API
        add_action('wp_ajax_ctm_disable_api', [$this, 'ajaxDisableApi']);
    }

    public function ajaxTestApiConnection(): void
    {
        $start_time = microtime(true);
        check_ajax_referer('ctm_test_api_connection', 'nonce');
        $api_key = sanitize_text_field($_POST['api_key'] ?? '');
        $api_secret = sanitize_text_field($_POST['api_secret'] ?? '');
        $response_data = [
            'timestamp' => current_time('mysql'),
            'request_id' => wp_generate_uuid4(),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'plugin_version' => '2.0',
            'api_endpoint' => 'https://api.calltrackingmetrics.com',
            'request_method' => 'GET',
            'auth_method' => 'Basic Authentication'
        ];
        if (empty($api_key) || empty($api_secret)) {
            wp_send_json_error([
                'message' => 'API Key and Secret are required',
                'details' => [
                    'Please provide both API Key and API Secret',
                    'API credentials cannot be empty',
                    'Check your CTM account for valid API keys'
                ],
                'metadata' => $response_data,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
            ]);
            return;
        }
        if (!is_string($api_key) || !is_string($api_secret) || strlen($api_key) < 20 || strlen($api_secret) < 20) {
            wp_send_json_error([
                'message' => 'Invalid API credential format',
                'details' => [
                    'API keys should be at least 20 characters long',
                    'Ensure you copied the complete API key and secret',
                    'Check for extra spaces or missing characters'
                ],
                'metadata' => $response_data,
                'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
            ]);
            return;
        }
        try {
            $apiService = $this->apiService;
            $api_start_time = microtime(true);
            $accountInfo = $apiService->getAccountInfo($api_key, $api_secret);
            $api_response_time = round((microtime(true) - $api_start_time) * 1000, 2);
            $response_data['api_response_time'] = $api_response_time;
            $response_data['account_endpoint'] = '/api/v1/accounts/';
            if (!$accountInfo || !isset($accountInfo['account'])) {
                $error_details = [
                    'Authentication failed - check your API credentials',
                    'Ensure your CTM account has API access enabled',
                    'Verify you\'re using the correct API environment',
                    'Check if your account subscription includes API access'
                ];
                if (!$accountInfo) {
                    $error_details[] = 'No response received from CTM API';
                    $error_details[] = 'This may indicate network connectivity issues';
                } else {
                    $error_details[] = 'API responded but account data was missing';
                    $error_details[] = 'This typically indicates authentication failure';
                }
                wp_send_json_error([
                    'message' => 'Failed to connect to CTM API',
                    'details' => $error_details,
                    'metadata' => $response_data,
                    'api_response' => $accountInfo,
                    'execution_time' => round((microtime(true) - $start_time) * 1000, 2)
                ]);
                return;
            }
            $account = $accountInfo['account'];
            $account_details = null;
            $details_response_time = null;
            if (isset($account['id'])) {
                $details_start_time = microtime(true);
                $account_details = $apiService->getAccountById($account['id'], $api_key, $api_secret);
                $details_response_time = round((microtime(true) - $details_start_time) * 1000, 2);
                $response_data['details_response_time'] = $details_response_time;
                $response_data['details_endpoint'] = '/api/v1/accounts/' . $account['id'];
            }
            update_option('ctm_api_key', $api_key);
            update_option('ctm_api_secret', $api_secret);
            update_option('ctm_api_auth_account', $account['id'] ?? '');
            $total_execution_time = round((microtime(true) - $start_time) * 1000, 2);
            wp_send_json_success([
                'message' => 'API Connection successful',
                'account_info' => $accountInfo,
                'account_details' => $account_details,
                'account_id' => $account['id'] ?? 'N/A',
                'connection_quality' => $this->assessConnectionQuality($api_response_time, $details_response_time),
                'metadata' => $response_data,
                'performance' => [
                    'total_execution_time' => $total_execution_time,
                    'api_response_time' => $api_response_time,
                    'details_response_time' => $details_response_time,
                    'network_overhead' => $total_execution_time - $api_response_time - ($details_response_time ?? 0)
                ],
                'capabilities' => [
                    'account_access' => true,
                    'details_access' => $account_details !== null,
                    'api_version' => 'v1'
                ]
            ]);
        } catch (\Exception $e) {
            $total_execution_time = round((microtime(true) - $start_time) * 1000, 2);
            $error_details = [
                'Exception: ' . get_class($e),
                'Error: ' . $e->getMessage()
            ];
            if (strpos($e->getMessage(), 'timeout') !== false) {
                $error_details[] = 'Request timed out - check network connectivity';
                $error_details[] = 'CTM API may be experiencing high load';
            } elseif (strpos($e->getMessage(), 'SSL') !== false || strpos($e->getMessage(), 'certificate') !== false) {
                $error_details[] = 'SSL/TLS certificate issue detected';
                $error_details[] = 'Check server SSL configuration';
            } elseif (strpos($e->getMessage(), 'DNS') !== false) {
                $error_details[] = 'DNS resolution failure';
                $error_details[] = 'Check domain name resolution';
            } else {
                $error_details[] = 'Check your API credentials';
                $error_details[] = 'Verify CTM service status';
                $error_details[] = 'Contact support if problem persists';
            }
            wp_send_json_error([
                'message' => 'Failed to connect to CTM API: ' . $e->getMessage(),
                'details' => $error_details,
                'metadata' => $response_data,
                'exception' => [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine()
                ],
                'execution_time' => $total_execution_time
            ]);
        }
    }

    private function assessConnectionQuality($api_time, $details_time): array
    {
        $total_time = $api_time + ($details_time ?? 0);
        if ($total_time < 500) {
            $quality = 'excellent';
            $color = 'green';
        } elseif ($total_time < 1000) {
            $quality = 'good';
            $color = 'blue';
        } elseif ($total_time < 2000) {
            $quality = 'fair';
            $color = 'yellow';
        } else {
            $quality = 'poor';
            $color = 'red';
        }
        return [
            'rating' => $quality,
            'color' => $color,
            'total_time' => $total_time,
            'description' => "Connection quality: {$quality} ({$total_time}ms total)"
        ];
    }

    public function ajaxSimulateApiRequest(): void
    {
        check_ajax_referer('ctm_simulate_api_request', 'nonce');
        $endpoint = sanitize_text_field($_POST['endpoint'] ?? '');
        $method = sanitize_text_field($_POST['method'] ?? 'GET');
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
            return;
        }
        try {
            $apiService = $this->apiService;
            switch ($endpoint) {
                case '/api/v1/accounts/':
                    $result = $apiService->getAccountInfo($apiKey, $apiSecret);
                    break;
                case '/api/v1/forms':
                    $result = $apiService->getForms($apiKey, $apiSecret);
                    break;
                case '/api/v1/tracking_numbers':
                    $result = $apiService->getTrackingNumbers($apiKey, $apiSecret);
                    break;
                case '/api/v1/calls':
                    $result = $apiService->getCalls($apiKey, $apiSecret);
                    break;
                default:
                    wp_send_json_error(['message' => 'Unsupported endpoint']);
                    return;
            }
            wp_send_json_success([
                'endpoint' => $endpoint,
                'method' => $method,
                'response' => $result,
                'timestamp' => current_time('mysql')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method
            ]);
        }
    }

    public function ajaxChangeApiKeys() {
        check_ajax_referer('ctm_change_api_keys', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        $apiKey = sanitize_text_field($_POST['api_key'] ?? '');
        $apiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API Key and Secret are required.']);
        }
        update_option('ctm_api_key', $apiKey);
        update_option('ctm_api_secret', $apiSecret);
        // Fetch account info and tracking script
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
        wp_send_json_success(['message' => 'API keys updated.']);
    }

    public function ajaxDisableApi() {
        check_ajax_referer('ctm_disable_api', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        // Clear all API credentials and related options
        delete_option('ctm_api_key');
        delete_option('ctm_api_secret');
        delete_option('ctm_api_auth_account');
        delete_option('call_track_account_script');
        wp_send_json_success(['message' => 'API credentials cleared.']);
    }
} 