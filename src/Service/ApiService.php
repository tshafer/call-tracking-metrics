<?php
namespace CTM\Service;

use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Http\Client\RequestException;

/**
 * Handles API communication and account management for CallTrackingMetrics.
 */
class ApiService
{
    /** @var string */
    private $apiHost;
    /** @var HttpClient */
    private $http;

    public function __construct(string $apiHost, ?HttpClient $http = null)
    {
        $this->apiHost = $apiHost;
        $this->http = $http ?: new HttpClient();
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
            $response = $this->http->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($this->apiHost . '/api/v1/accounts/current.json');
            $data = $response->json();
            if ($data && isset($data['account'])) {
                return $data['account'];
            }
        } catch (RequestException $e) {
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
            $response = $this->http->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($url, $payload);
            $data = $response->json();
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('error_log')) error_log('CTM API response JSON error: ' . json_last_error_msg());
                return null;
            }
            return $data;
        } catch (RequestException $e) {
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
            $response = $this->http->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($url);
            error_log('ApiService::getAccountInfo HTTP request complete');
            $data = $response->json();
            error_log('ApiService::getAccountInfo response: ' . var_export($data, true));
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('error_log')) error_log('CTM API response JSON error: ' . json_last_error_msg());
                return null;
            }
            return $data;
        } catch (RequestException $e) {
            if (function_exists('error_log')) error_log('CTM API error: ' . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
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
            $response = $this->http->withHeaders([
                'Authorization' => 'Basic ' . base64_encode($apiKey . ':' . $apiSecret),
                'Content-Type' => 'application/json',
            ])->timeout(30)->get($url);
            $data = $response->json();
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (function_exists('error_log')) error_log('CTM API response JSON error: ' . json_last_error_msg());
                return null;
            }
            return $data;
        } catch (RequestException $e) {
            if (function_exists('error_log')) error_log('CTM API error: ' . $e->getMessage());
            return null;
        }
    }
} 