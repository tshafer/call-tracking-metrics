<?php
namespace CTM\Service;

use CTM\Admin\Options;

/**
 * Handles API communication and account management for CallTrackingMetrics.
 */
class ApiService
{
    /** @var string */
    private $apiHost;

    /** @var Options */
    private $options;

    public function __construct(string $apiHost)
    {
        $this->apiHost = $apiHost;
        $this->options = new Options();
    }

    /**
     * Log API activity with comprehensive details (only for failures/errors)
     */
    private function logApiActivity(string $method, string $url, array $context = [], string $type = 'error'): void
    {
        if (!Options::isDebugEnabled()) {
            return;
        }

        $message = "API {$method} request to {$url}";
        $this->options->logActivity($message, $type, $context);
    }

    /**
     * Update the authenticated account using API credentials.
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @return string|null Account ID or null on failure
     */
    public function updateAccount(string $apiKey, string $apiSecret): ?string
    {
        if (empty($apiKey) || empty($apiSecret)) {
            $this->options->logActivity('API updateAccount failed: Missing credentials', 'error');
            return null;
        }
        
        $url = $this->apiHost . '/api/v1/accounts/current.json';
        $start_time = microtime(true);
        
        try {
            $response = wp_remote_get($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if (is_wp_error($response)) {
                $error_msg = $response->get_error_message();
                $this->options->logActivity('API updateAccount failed', 'error', [
                    'error' => $error_msg,
                    'response_time' => $response_time,
                    'url' => $url
                ]);
                return null;
            }
            
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            // Only log if there are issues
            if ($status_code >= 400) {
                $this->options->logActivity('API updateAccount failed with HTTP error', 'error', [
                    'status_code' => $status_code,
                    'response_time' => $response_time,
                    'response_body_preview' => substr($body, 0, 200)
                ]);
            }
            
            if ($data && isset($data['account'])) {
                // Success - no logging needed
                return $data['account'];
            } else {
                $this->options->logActivity('API updateAccount: No account data in response', 'warning', [
                    'status_code' => $status_code,
                    'response_body_preview' => substr($body, 0, 200)
                ]);
            }
        } catch (\Exception $e) {
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            $this->options->logActivity('API updateAccount exception', 'error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'response_time' => $response_time
            ]);
        }
        return null;
    }

    /**
     * Submit form data to the CallTrackingMetrics FormReactor API.
     *
     * @param array $payload The form data to send
     * @param string $apiKey
     * @param string $apiSecret
     * @return array|null API response as array, or null on failure
     */
    public function submitFormReactor(array $payload, string $apiKey, string $apiSecret): ?array
    {
        $url = $this->apiHost . '/api/v1/formreactor/submit';
        $start_time = microtime(true);
        
        try {
            $response = wp_remote_post($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload)
            ]);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if (is_wp_error($response)) {
                $error_msg = $response->get_error_message();
                $this->options->logActivity('FormReactor submission failed', 'error', [
                    'error' => $error_msg,
                    'response_time' => $response_time,
                    'payload_preview' => array_slice($payload, 0, 5, true)
                ]);
                return null;
            }
            
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->options->logActivity('FormReactor JSON decode error', 'error', [
                    'json_error' => json_last_error_msg(),
                    'response_time' => $response_time,
                    'status_code' => $status_code,
                    'body_preview' => substr($body, 0, 200)
                ]);
                return null;
            }
            
            // Only log if submission failed
            if ($status_code >= 400 || !$data) {
                $this->options->logActivity('FormReactor submission failed', 'error', [
                    'status_code' => $status_code,
                    'response_time' => $response_time,
                    'response_size' => strlen($body),
                    'body_preview' => substr($body, 0, 200)
                ]);
                return null;
            }
            
            // Success - no logging needed
            return $data;
        } catch (\Exception $e) {
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            $this->options->logActivity('FormReactor submission exception', 'error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'payload_preview' => array_slice($payload, 0, 5, true),
                'response_time' => $response_time
            ]);
            return null;
        }
    }

    /**
     * Fetch account info from the CallTrackingMetrics API.
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @return array|null Account info as array, or null on failure
     */
    public function getAccountInfo(string $apiKey, string $apiSecret): ?array
    {
        $url = $this->apiHost . '/api/v1/accounts/current.json';
        $start_time = microtime(true);
        
        try {
            $response = wp_remote_get($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if (is_wp_error($response)) {
                $error_msg = $response->get_error_message();
                $this->options->logActivity('Get account info failed', 'error', [
                    'error' => $error_msg,
                    'response_time' => $response_time,
                    'url' => $url
                ]);
                return null;
            }
            
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->options->logActivity('Account info JSON decode error', 'error', [
                    'json_error' => json_last_error_msg(),
                    'response_time' => $response_time,
                    'status_code' => $status_code,
                    'body_preview' => substr($body, 0, 200)
                ]);
                return null;
            }
            
            // Only log if there are issues
            if ($status_code >= 400) {
                $this->options->logActivity('Get account info failed with HTTP error', 'error', [
                    'status_code' => $status_code,
                    'response_time' => $response_time,
                    'body_preview' => substr($body, 0, 200)
                ]);
                return null;
            }
            
            if (!$data || !isset($data['account'])) {
                $this->options->logActivity('Account info missing in response', 'warning', [
                    'status_code' => $status_code,
                    'response_time' => $response_time,
                    'body_preview' => substr($body, 0, 200)
                ]);
                return null;
            }
            
            // Success - no logging needed
            return $data;
        } catch (\Exception $e) {
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            $this->options->logActivity('Get account info exception', 'error', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'response_time' => $response_time
            ]);
            return null;
        }
    }

    /**
     * Fetch account details by account ID from the CallTrackingMetrics API.
     *
     * @param string|int $accountId
     * @param string $apiKey
     * @param string $apiSecret
     * @return array|null Account info as array, or null on failure
     */
    public function getAccountById($accountId, string $apiKey, string $apiSecret): ?array
    {
        $url = $this->apiHost . '/api/v1/accounts/' . urlencode($accountId);
        $start_time = microtime(true);
        
        try {
            $response = wp_remote_get($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            
            if (is_wp_error($response)) {
                $error_msg = $response->get_error_message();
                $this->options->logActivity('Get account by ID failed', 'error', [
                    'account_id' => $accountId,
                    'error' => $error_msg,
                    'response_time' => $response_time
                ]);
                return null;
            }
            
            $status_code = wp_remote_retrieve_response_code($response);
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->options->logActivity('Get account by ID JSON decode error', 'error', [
                    'account_id' => $accountId,
                    'json_error' => json_last_error_msg(),
                    'response_time' => $response_time,
                    'status_code' => $status_code
                ]);
                return null;
            }
            
            // Only log if there are issues
            if ($status_code >= 400) {
                $this->options->logActivity('Get account by ID failed with HTTP error', 'error', [
                    'account_id' => $accountId,
                    'status_code' => $status_code,
                    'response_time' => $response_time,
                    'body_preview' => substr($body, 0, 200)
                ]);
                return null;
            }
            
            // Success - no logging needed
            return $data;
        } catch (\Exception $e) {
            $response_time = round((microtime(true) - $start_time) * 1000, 2);
            $this->options->logActivity('Get account by ID exception', 'error', [
                'account_id' => $accountId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
                'response_time' => $response_time
            ]);
            return null;
        }
    }
} 