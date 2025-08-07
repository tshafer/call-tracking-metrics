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
    private int $timeout = 15;

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
     * Internal logging helper to prevent server log pollution
     * 
     * @since 2.0.0
     * @param string $message The message to log
     * @param string $type The log type (error, debug, api, etc.)
     */
    private function logInternal(string $message, string $type = 'debug'): void
    {
        if (class_exists('\CTM\Admin\LoggingSystem')) {
            $loggingSystem = new \CTM\Admin\LoggingSystem();
            if ($loggingSystem->isDebugEnabled()) {
                $loggingSystem->logActivity($message, $type);
            }
        }
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
        $start = microtime(true);
        try {
            $response = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
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
            $this->logInternal('API Error (getAccountInfo): ' . $e->getMessage(), 'error');
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
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getAccountById): ' . $e->getMessage(), 'error');
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
    public function submitFormReactor(array $formData, string $apiKey, string $apiSecret, $formId = null): ?array
    {
        $endpoint = '/api/v1/formreactor/'.$formId;
        $start = microtime(true);
        try {
            $data = $this->makeRequest(
                method: 'POST', 
                endpoint: $endpoint, 
                data: $formData, 
                apiKey: $apiKey, 
                apiSecret: $apiSecret,
                contentType: 'application/x-www-form-urlencoded'
            );
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            // Return null on non-2xx response
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (submitFormReactor): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get all form reactors for an account
     * 
     * Retrieves a list of all form reactors configured in the CTM account.
     * This can be used for form mapping and configuration purposes.
     * 
     * @since 1.0.0
     * @param string $accountId The account ID
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null Array of form reactors or null on failure
     */
    public function getFormReactors(string $accountId, string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        $endpoint = "/api/v1/accounts/{$accountId}/form_reactors";
        $params = [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage))
        ];
        
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getFormReactors): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get all form reactors for an account with automatic pagination
     * 
     * Retrieves all form reactors by automatically handling pagination.
     * 
     * @since 1.0.0
     * @param string $accountId The account ID
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param int    $perPage   Items per page (default: 50, max: 100)
     * @return array|null Array of all form reactors or null on failure
     */
    public function getAllFormReactors(string $accountId, string $apiKey, string $apiSecret, int $perPage = 50): ?array
    {
        $allFormReactors = [];
        $page = 1;
        $hasMorePages = true;
        
        while ($hasMorePages) {
            $response = $this->getFormReactors($accountId, $apiKey, $apiSecret, $page, $perPage);
            
            if (!$response || !isset($response['form_reactors'])) {
                break;
            }
            
            $formReactors = $response['form_reactors'];
            $allFormReactors = array_merge($allFormReactors, $formReactors);
            
            // Check if there are more pages
            $totalPages = $response['pagination']['total_pages'] ?? 1;
            $hasMorePages = $page < $totalPages;
            $page++;
            
                    // Safety check to prevent infinite loops
        if ($page > 50) {
            $this->logInternal('API Error: Pagination limit exceeded (50 pages)', 'error');
            break;
        }
        }
        
        return [
            'form_reactors' => $allFormReactors,
            'pagination' => [
                'total_items' => count($allFormReactors),
                'total_pages' => $page - 1,
                'per_page' => $perPage
            ]
        ];
    }

    /**
     * Get a specific form reactor by ID
     * 
     * Retrieves detailed information about a specific form reactor.
     * 
     * @since 1.0.0
     * @param string $accountId      The account ID
     * @param string $formReactorId  The form reactor ID
     * @param string $apiKey         The API key for authentication
     * @param string $apiSecret      The API secret for authentication
     * @return array|null Form reactor details or null on failure
     */
    public function getFormReactorById(string $accountId, string $formReactorId, string $apiKey, string $apiSecret): ?array
    {
        $endpoint = "/api/v1/accounts/{$accountId}/form_reactors/{$formReactorId}";
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getFormReactorById): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get forms from CTM API
     * 
     * Retrieves forms directly from the CTM API using the form_reactors endpoint.
     * This method handles the new API response format with 'forms' array.
     * 
     * @since 2.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param int    $page      Page number (default: 1)
     * @param int    $perPage   Items per page (default: 50, max: 100)
     * @return array|null Array of forms or null on failure
     */
    public function getFormsDirect(string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        $this->logInternal('Debug: ApiService::getFormsDirect - Starting');
        
        // First get account information to get the account ID
        $accountInfo = $this->getAccountInfo($apiKey, $apiSecret);
        if (!$accountInfo || !isset($accountInfo['account']['id'])) {
            $this->logInternal('API Error: Could not retrieve account information for forms', 'error');
            $this->logInternal('Debug: Account info response: ' . json_encode($accountInfo), 'debug');
            return null;
        }
        
        $accountId = $accountInfo['account']['id'];
        $this->logInternal('Debug: Using account ID: ' . $accountId);
        
        $endpoint = "/api/v1/accounts/{$accountId}/form_reactors";
        $params = [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage))
        ];
        
        $this->logInternal('Debug: Making request to endpoint: ' . $endpoint);
        $this->logInternal('Debug: Request params: ' . json_encode($params));
        
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            
            $this->logInternal('Debug: API response received in ' . round($elapsed, 2) . 'ms');
            $this->logInternal('Debug: Response keys: ' . implode(', ', array_keys($data)));
            
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                $this->logInternal('API Error: API returned error response: ' . json_encode($data), 'error');
                return null;
            }
            
            $this->logInternal('Debug: Successfully retrieved forms data');
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getFormsDirect): ' . $e->getMessage(), 'error');
            $this->logInternal('Debug: Exception stack trace: ' . $e->getTraceAsString(), 'debug');
            return null;
        }
    }

    /**
     * Get forms from CTM API (backward compatibility)
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @return array|null Array of forms or null on failure
     */
    public function getForms(string $apiKey, string $apiSecret): ?array
    {
        // First try the direct forms endpoint
        $forms = $this->getFormsDirect($apiKey, $apiSecret);
        if ($forms && isset($forms['forms'])) {
            return $forms;
        }
        
        // Fallback to account-based approach
        $accountInfo = $this->getAccountInfo($apiKey, $apiSecret);
        if (!$accountInfo || !isset($accountInfo['account']['id'])) {
            return null;
        }
        
        $accountId = $accountInfo['account']['id'];
        return $this->getFormReactors($accountId, $apiKey, $apiSecret);
    }

    /**
     * Get tracking numbers for an account with pagination support
     * 
     * Retrieves all tracking phone numbers associated with the account.
     * This can be used for call tracking and analytics purposes.
     * 
     * @since 1.0.0
     * @param string $apiKey    The API key for authentication
     * @param string $apiSecret The API secret for authentication
     * @param int    $page      Page number (default: 1)
     * @param int    $perPage   Items per page (default: 50, max: 100)
     * @return array|null Array of tracking numbers or null on failure
     */
    public function getTrackingNumbers(string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        $endpoint = '/api/v1/tracking_numbers';
        $params = [
            'page' => max(1, $page),
            'per_page' => min(100, max(1, $perPage))
        ];
        
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getTrackingNumbers): ' . $e->getMessage(), 'error');
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
        $start = microtime(true);
        try {
            $data = $this->makeRequest('GET', $endpoint, $params, $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            if (isset($data['error']) || (isset($data['status']) && $data['status'] === 'error')) {
                return null;
            }
            return $data;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getCalls): ' . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Get the tracking script for an account
     *
     * @param string $accountId
     * @param string $apiKey
     * @param string $apiSecret
     * @return array|null
     */
    public function getTrackingScript(string $accountId, string $apiKey, string $apiSecret): ?array
    {
        $start = microtime(true);
        try {
            $result = $this->makeRequest('GET', "/api/v1/accounts/{$accountId}/scripts", [], $apiKey, $apiSecret);
            $elapsed = (microtime(true) - $start) * 1000;
            $this->trackApiCall();
            $this->trackApiResponseTime($elapsed);
            return $result;
        } catch (\Exception $e) {
            $this->logInternal('API Error (getTrackingScript): ' . $e->getMessage(), 'error');
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
     * @param string $contentType Content-Type header (default: application/json)
     * @return array The decoded API response
     * @throws \Exception On HTTP errors or invalid responses
     */
    private function makeRequest(string $method, string $endpoint, array $data = [], string $apiKey = '', string $apiSecret = '', string $contentType = 'application/json'): array
    {
        $url = $this->baseUrl . $endpoint;
        
        // Use internal logging system
        $loggingSystem = null;
        if (class_exists('\CTM\Admin\LoggingSystem')) {
            $loggingSystem = new \CTM\Admin\LoggingSystem();
        }
        
        // Only log if debug mode is enabled and logging system is available
        $should_log = $loggingSystem && $loggingSystem->isDebugEnabled();
        
        if ($should_log) {
            $loggingSystem->logActivity("API Request - URL: {$url}, Method: {$method}", 'api');
        }
        
        $args = [
            'method'  => strtoupper($method),
            'timeout' => $this->timeout,
            'headers' => [
                'User-Agent'   => $this->userAgent,
                'Accept'       => 'application/json',
                'Content-Type' => $contentType,
            ],
        ];
        
        if (!empty($apiKey) && !empty($apiSecret)) {
            $args['headers']['Authorization'] = 'Basic ' . base64_encode($apiKey . ':' . $apiSecret);
            if ($should_log) {
                $loggingSystem->logActivity('API Request - Authorization header set', 'api');
            }
        }
        
        // Handle body encoding
        if (in_array($method, ['POST', 'PUT']) && !empty($data)) {
            if ($contentType === 'application/json') {
                $args['body'] = json_encode($data);
            } elseif ($contentType === 'application/x-www-form-urlencoded') {
                $args['body'] = http_build_query($data);
            } else {
                $args['body'] = $data;
            }
        }
        
        $response = \wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            $errorMessage = 'HTTP request failed: ' . $response->get_error_message();
            if ($should_log) {
                $loggingSystem->logActivity("API Error - {$errorMessage}", 'error');
            }
            throw new \Exception($errorMessage);
        }
        
        $statusCode = \wp_remote_retrieve_response_code($response);
        $body = \wp_remote_retrieve_body($response);
        
        if ($should_log) {
            $loggingSystem->logActivity("API Response - Status: {$statusCode}, Body length: " . strlen($body), 'api');
        }
        
        if ($statusCode >= 400) {
            $errorMessage = 'HTTP ' . $statusCode . ' error: ' . $body;
            if ($should_log) {
                $loggingSystem->logActivity("API Error - {$errorMessage}", 'error');
            }
            throw new \Exception($errorMessage);
        }
        
        $result = json_decode($body, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = 'Invalid JSON response: ' . json_last_error_msg();
            if ($should_log) {
                $loggingSystem->logActivity("API Error - {$errorMessage}", 'error');
                $loggingSystem->logActivity("API Error - Raw response: " . substr($body, 0, 500), 'error');
            }
            throw new \Exception($errorMessage);
        }
        
        if ($should_log) {
            $loggingSystem->logActivity('API Success - Successfully decoded JSON response', 'api');
        }
        
        return $result;
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
            
            return !is_wp_error($response) && \wp_remote_retrieve_response_code($response) < 400;
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
            $this->logInternal('API Call Tracking Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Track API response time for performance monitoring
     * 
     * Records the response time of successful API calls for 24-hour tracking.
     * This data is used by the performance monitor to show API response times.
     * 
     * @since 2.0.0
     * @param float $responseTime Response time in milliseconds
     * @return void
     */
    private function trackApiResponseTime(float $responseTime): void
    {
        try {
            $response_times = get_option('ctm_api_response_times', []);
            
            if (!is_array($response_times)) {
                $response_times = [];
            }
            
            // Add current response time with timestamp
            $response_times[time()] = $responseTime;
            
            // Clean old entries (older than 24 hours)
            $twenty_four_hours_ago = time() - (24 * 60 * 60);
            $response_times = array_filter($response_times, function($timestamp) use ($twenty_four_hours_ago) {
                return $timestamp >= $twenty_four_hours_ago;
            }, ARRAY_FILTER_USE_KEY);
            
            // Limit to prevent excessive data storage (keep last 100 response times max)
            if (count($response_times) > 100) {
                $response_times = array_slice($response_times, -100, null, true);
            }
            
            update_option('ctm_api_response_times', $response_times);
        } catch (\Exception $e) {
            // Silently fail to avoid disrupting API calls
            $this->logInternal('API Response Time Tracking Error: ' . $e->getMessage(), 'error');
        }
    }
} 