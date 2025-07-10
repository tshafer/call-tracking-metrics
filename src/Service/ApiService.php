<?php
/**
 * CallTrackingMetrics API Service
 * 
 * This file contains the ApiService class that handles all communication
 * with the CallTrackingMetrics API including authentication, data submission,
 * and account information retrieval.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Service
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       1.0.0
 */

namespace CTM\Service;

/**
 * CallTrackingMetrics API Service Class
 * 
 * Handles all interactions with the CallTrackingMetrics API including:
 * - HTTP client configuration and request handling
 * - Authentication with API credentials
 * - Account information retrieval
 * - Form submission data transmission
 * - Error handling and response processing
 * 
 * This service uses WordPress HTTP API for reliable communication
 * and includes comprehensive error handling and logging.
 * 
 * @since 1.0.0
 */
class ApiService
{
    /**
     * Base URL for the CallTrackingMetrics API
     * 
     * @since 1.0.0
     * @var string
     */
    private string $baseUrl;

    /**
     * HTTP client timeout in seconds
     * 
     * @since 1.0.0
     * @var int
     */
    private int $timeout = 30;

    /**
     * User agent string for API requests
     * 
     * @since 1.0.0
     * @var string
     */
    private string $userAgent;

    /**
     * Initialize the API service
     * 
     * Sets up the base URL and user agent for API communication.
     * The base URL should include the protocol and domain.
     * 
     * @since 1.0.0
     * @param string $baseUrl The base URL for the CTM API
     */
    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->userAgent = 'CallTrackingMetrics-WordPress-Plugin/2.0 (+' . home_url() . ')';
    }

    /**
     * Get account information from the CTM API
     * 
     * Retrieves basic account information using the provided API credentials.
     * This is typically used for authentication testing and account validation.
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null The account information array or null on failure
     */
    public function getAccountInfo(string $apiKey, string $apiSecret): ?array
    {
        $endpoint = '/api/v1/accounts/';
        
        try {
            $response = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            
            // Return the first account if multiple accounts exist
            if (isset($response['accounts']) && is_array($response['accounts']) && !empty($response['accounts'])) {
                return ['account' => $response['accounts'][0]];
            }
            
            // Handle single account response
            if (isset($response['account'])) {
                return $response;
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log('CTM API Error (getAccountInfo): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed account information by account ID
     * 
     * Retrieves comprehensive account details for a specific account ID.
     * This provides more detailed information than the basic account info.
     * 
     * @since 1.0.0
     * @param string $accountId The account ID to retrieve details for
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null The detailed account information or null on failure
     */
    public function getAccountById(string $accountId, string $apiKey, string $apiSecret): ?array
    {
        $endpoint = "/api/v1/accounts/{$accountId}";
        
        try {
            return $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
        } catch (\Exception $e) {
            error_log('CTM API Error (getAccountById): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Submit form data to the CTM Form Reactor API
     * 
     * Sends processed form submission data to CallTrackingMetrics for
     * lead tracking and analytics. The data should be pre-formatted
     * according to CTM API specifications.
     * 
     * @since 1.0.0
     * @param array  $formData  The formatted form submission data
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null The API response or null on failure
     */
    public function submitFormReactor(array $formData, string $apiKey, string $apiSecret): ?array
    {
        $endpoint = '/api/v1/form_reactor';
        
        try {
            return $this->makeRequest('POST', $endpoint, $formData, $apiKey, $apiSecret);
        } catch (\Exception $e) {
            error_log('CTM API Error (submitFormReactor): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all forms associated with an account
     * 
     * Retrieves a list of all forms configured in the CTM account.
     * This can be used for form mapping and configuration purposes.
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null Array of forms or null on failure
     */
    public function getForms(string $apiKey, string $apiSecret): ?array
    {
        $endpoint = '/api/v1/forms';
        
        try {
            return $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
        } catch (\Exception $e) {
            error_log('CTM API Error (getForms): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get tracking numbers for an account
     * 
     * Retrieves all tracking phone numbers associated with the account.
     * This can be used for call tracking and analytics purposes.
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null Array of tracking numbers or null on failure
     */
    public function getTrackingNumbers(string $apiKey, string $apiSecret): ?array
    {
        $endpoint = '/api/v1/tracking_numbers';
        
        try {
            return $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
        } catch (\Exception $e) {
            error_log('CTM API Error (getTrackingNumbers): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get call data for analytics
     * 
     * Retrieves call data and analytics from the CTM API.
     * Can be filtered by date range and other parameters.
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param array  $params    Query parameters for filtering (optional)
     * @return array|null Array of call data or null on failure
     */
    public function getCalls(string $apiKey, string $apiSecret, array $params = []): ?array
    {
        $endpoint = '/api/v1/calls';
        
        // Add query parameters if provided
        if (!empty($params)) {
            $endpoint .= '?' . http_build_query($params);
        }
        
        try {
            return $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
        } catch (\Exception $e) {
            error_log('CTM API Error (getCalls): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Make an HTTP request to the CTM API
     * 
     * Core method that handles all HTTP communication with the API including
     * authentication, request formatting, error handling, and response processing.
     * 
     * @since 1.0.0
     * @param string $method    HTTP method (GET, POST, PUT, DELETE)
     * @param string $endpoint  API endpoint path
     * @param array  $data      Request data for POST/PUT requests
     * @param string $apiKey    API key for authentication
     * @param string $apiSecret API secret for authentication
     * @return array The decoded API response
     * @throws \Exception On HTTP errors or invalid responses
     */
    private function makeRequest(string $method, string $endpoint, array $data = [], string $apiKey = '', string $apiSecret = ''): array
    {
        $url = $this->baseUrl . $endpoint;
        
        // Prepare request arguments
        $args = [
            'method'  => strtoupper($method),
            'timeout' => $this->timeout,
            'headers' => [
                'User-Agent'   => $this->userAgent,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ];

        // Add authentication if credentials provided
        if (!empty($apiKey) && !empty($apiSecret)) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($apiKey . ':' . $apiSecret);
        }

        // Add request body for POST/PUT requests
        if (in_array($method, ['POST', 'PUT']) && !empty($data)) {
            $args['body'] = json_encode($data);
        }

        // Make the HTTP request using WordPress HTTP API
        $response = wp_remote_request($url, $args);

        // Check for WordPress HTTP errors
        if (is_wp_error($response)) {
            throw new \Exception('HTTP request failed: ' . $response->get_error_message());
        }

        // Get response details
        $httpCode = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        // Handle HTTP error status codes
        if ($httpCode >= 400) {
            $errorMessage = "HTTP {$httpCode}";
            
            // Try to extract error message from response body
            $errorData = json_decode($body, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($errorData['error'])) {
                $errorMessage .= ': ' . $errorData['error'];
            } elseif (json_last_error() === JSON_ERROR_NONE && isset($errorData['message'])) {
                $errorMessage .= ': ' . $errorData['message'];
            }
            
            throw new \Exception($errorMessage);
        }

        // Decode JSON response
        $decodedResponse = json_decode($body, true);
        
        // Check for JSON decode errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response: ' . json_last_error_msg());
        }

        // Track successful API call for performance monitoring
        $this->trackApiCall();

        return $decodedResponse;
    }

    /**
     * Validate API credentials
     * 
     * Tests the provided API credentials by making a simple API call.
     * This is useful for validating credentials during setup.
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key to validate
     * @param string $apiSecret The API secret to validate
     * @return bool True if credentials are valid, false otherwise
     */
    public function validateCredentials(string $apiKey, string $apiSecret): bool
    {
        try {
            $accountInfo = $this->getAccountInfo($apiKey, $apiSecret);
            return $accountInfo !== null && isset($accountInfo['account']);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get API service health status
     * 
     * Checks if the CTM API service is accessible and responding.
     * This can be used for monitoring and diagnostics.
     * 
     * @since 1.0.0
     * @return bool True if API is accessible, false otherwise
     */
    public function checkApiHealth(): bool
    {
        try {
            // Make a simple request without authentication to check connectivity
            $response = wp_remote_get($this->baseUrl . '/api/v1/ping', [
                'timeout' => 10,
                'headers' => [
                    'User-Agent' => $this->userAgent,
                ],
            ]);
            
            return !is_wp_error($response) && wp_remote_retrieve_response_code($response) < 400;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set custom timeout for API requests
     * 
     * Allows customization of the HTTP timeout for API requests.
     * Useful for slower connections or large data transfers.
     * 
     * @since 1.0.0
     * @param int $timeout Timeout in seconds
     * @return void
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = max(1, $timeout); // Ensure minimum 1 second timeout
    }

    /**
     * Get the current API base URL
     * 
     * @since 1.0.0
     * @return string The current base URL
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get the current timeout setting
     * 
     * @since 1.0.0
     * @return int The current timeout in seconds
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Track API call for performance monitoring
     * 
     * Records the timestamp of successful API calls for 24-hour tracking.
     * This data is used by the performance monitor to show API usage.
     * 
     * @since 2.0.0
     * @return void
     */
    private function trackApiCall(): void
    {
        try {
            $current_calls = get_option('ctm_api_calls_24h', []);
            
            if (!is_array($current_calls)) {
                $current_calls = [];
            }
            
            // Add current timestamp
            $current_calls[] = time();
            
            // Clean old entries (older than 24 hours)
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $current_calls = array_filter($current_calls, function($timestamp) use ($twenty_four_hours_ago) {
                return $timestamp >= $twenty_four_hours_ago;
            });
            
            // Limit to prevent excessive data storage (keep last 1000 calls max)
            if (count($current_calls) > 1000) {
                $current_calls = array_slice($current_calls, -1000);
            }
            
            update_option('ctm_api_calls_24h', $current_calls);
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting API calls
            error_log('CTM API Call Tracking Error: ' . $e->getMessage());
        }
    }
} 