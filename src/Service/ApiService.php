<?php
namespace CTM\Service;

/**
 * Handles API communication and account management for CallTrackingMetrics.
 */
class ApiService
{
    /** @var string */
    private $apiHost;

    public function __construct(string $apiHost)
    {
        $this->apiHost = $apiHost;
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
            return null;
        }
        
        try {
            $response = wp_remote_get($this->apiHost . '/api/v1/accounts/current.json', [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            if (is_wp_error($response)) {
                if (function_exists('error_log')) error_log('CTM API error: ' . $response->get_error_message());
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if ($data && isset($data['account'])) {
                return $data['account'];
            }
        } catch (\Exception $e) {
            if (function_exists('error_log')) error_log('CTM API error: ' . $e->getMessage());
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
        
        try {
            $response = wp_remote_post($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($payload)
            ]);
            
            if (is_wp_error($response)) {
                if (function_exists('error_log')) error_log('CTM API error: ' . $response->get_error_message());
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('error_log')) error_log('CTM API response JSON error: ' . json_last_error_msg());
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            if (function_exists('error_log')) error_log('CTM API error: ' . $e->getMessage());
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
        error_log('ApiService::getAccountInfo called');
        
        try {
            error_log('ApiService::getAccountInfo making HTTP request to ' . $url);
            
            $response = wp_remote_get($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            error_log('ApiService::getAccountInfo HTTP request complete');
            
            if (is_wp_error($response)) {
                if (function_exists('error_log')) error_log('CTM API error: ' . $response->get_error_message());
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            error_log('ApiService::getAccountInfo response: ' . var_export($data, true));
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('error_log')) error_log('CTM API response JSON error: ' . json_last_error_msg());
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            error_log('CTM API fatal error: ' . $e->getMessage());
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
        
        try {
            $response = wp_remote_get($url, [
                'timeout' => 30,
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                    'Content-Type' => 'application/json',
                ]
            ]);
            
            if (is_wp_error($response)) {
                if (function_exists('error_log')) error_log('CTM API error: ' . $response->get_error_message());
                return null;
            }
            
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('error_log')) error_log('CTM API response JSON error: ' . json_last_error_msg());
                return null;
            }
            
            return $data;
        } catch (\Exception $e) {
            if (function_exists('error_log')) error_log('CTM API error: ' . $e->getMessage());
            return null;
        }
    }
} 