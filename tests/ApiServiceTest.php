<?php

namespace CTM\Service;
if (!function_exists('CTM\Service\home_url')) {
    function home_url() {
        return 'https://example.com';
    }
}

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;
use CTM\Service\ApiService;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class ApiServiceTest extends TestCase
{
    use MonkeyTrait;
    protected ApiService $apiService;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        $this->apiService = new ApiService('https://dummy-ctm-api.test');

    }
    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }


    public function testGetAccountInfoReturnsNullOnFailure()
    {
        $result = $this->apiService->getAccountInfo('invalid', 'invalid');
        $this->assertNull($result, 'Should return null for invalid credentials');
    }

    public function testSubmitFormReactorReturnsNullOnFailure()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('Simulated failure');
        });
        $formData = ['test' => 'data'];
        $result = $this->apiService->submitFormReactor($formData, 'invalid', 'invalid');
        $this->assertNull($result, 'Should return null for failed submission');
    }

    // Example: test a successful API response
    public function testGetAccountInfoReturnsAccountOnSuccess()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['accounts' => [['id' => '123', 'name' => 'Test Account']]])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['accounts' => [['id' => '123', 'name' => 'Test Account']]]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = null) {
            return $default;
        });
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);

        $result = $this->apiService->getAccountInfo('valid', 'valid');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('account', $result);
        $this->assertEquals('123', $result['account']['id']);
    }

    public function testGetAccountInfoReturnsNullOnInvalidJson()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => '{invalid json}'
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{invalid json}');
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $result = $this->apiService->getAccountInfo('valid', 'valid');
        $this->assertNull($result, 'Should return null for invalid JSON');
    }

    public function testGetAccountInfoThrowsException()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('Simulated exception');
        });
        $result = $this->apiService->getAccountInfo('valid', 'valid');
        $this->assertNull($result, 'Should return null on exception');
    }

    public function testGetAccountByIdReturnsDetailsOnSuccess()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['id' => '123', 'name' => 'Test Account'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['id' => '123', 'name' => 'Test Account']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $result = $this->apiService->getAccountById('123', 'valid', 'valid');
        $this->assertIsArray($result);
        $this->assertEquals('123', $result['id']);
    }

    public function testGetAccountByIdReturnsNullOn404()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 404],
            'body' => json_encode(['error' => 'Not found'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['error' => 'Not found']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(404);
        $result = $this->apiService->getAccountById('404', 'valid', 'valid');
        $this->assertNull($result, 'Should return null on 404');
    }

    public function testGetAccountByIdReturnsNullOnException()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('Simulated exception');
        });
        $result = $this->apiService->getAccountById('123', 'valid', 'valid');
        $this->assertNull($result, 'Should return null on exception');
    }

    public function testSubmitFormReactorReturnsResponseOnSuccess()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['success' => true])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['success' => true]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $result = $this->apiService->submitFormReactor(['foo' => 'bar'], 'valid', 'valid');
        $this->assertIsArray($result);
        $this->assertTrue($result['success']);
    }

    public function testSubmitFormReactorReturnsNullOn400()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 400],
            'body' => json_encode(['error' => 'Bad request'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['error' => 'Bad request']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(400);
        $result = $this->apiService->submitFormReactor(['foo' => 'bar'], 'valid', 'valid');
        $this->assertNull($result, 'Should return null on 400');
    }

    public function testSubmitFormReactorReturnsNullOnException()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('Simulated exception');
        });
        $result = $this->apiService->submitFormReactor(['foo' => 'bar'], 'valid', 'valid');
        $this->assertNull($result, 'Should return null on exception');

    }

    public function testSubmitFormReactorReturnsThrottlingError()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 429],
            'body' => json_encode(['status' => 'error', 'reason' => 'Rate limit exceeded'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['status' => 'error', 'reason' => 'Rate limit exceeded']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(429);
        $result = $this->apiService->submitFormReactor(['foo' => 'bar'], 'valid', 'valid');
        $this->assertNull($result, 'Should return null for throttling error');
    }

    public function testGetFormsReturnsNullWhenAccountInfoFails()
    {
        // Mock wp_remote_request to return error for account info
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 401],
            'body' => json_encode(['error' => 'Unauthorized'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['error' => 'Unauthorized']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(401);
        
        $result = $this->apiService->getForms('invalid', 'invalid');
        $this->assertNull($result, 'Should return null when account info fails');
    }

    public function testGetFormsReturnsNullOnError()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 500],
            'body' => json_encode(['error' => 'Server error'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['error' => 'Server error']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        $result = $this->apiService->getForms('valid', 'valid');
        $this->assertNull($result, 'Should return null on error');
    }

    public function testGetTrackingNumbersReturnsNumbersOnSuccess()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['numbers' => [['id' => 1, 'number' => '1234567890']]])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['numbers' => [['id' => 1, 'number' => '1234567890']]]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $result = $this->apiService->getTrackingNumbers('valid', 'valid');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('numbers', $result);
    }

    public function testGetTrackingNumbersReturnsNullOnError()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 500],
            'body' => json_encode(['error' => 'Server error'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['error' => 'Server error']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        $result = $this->apiService->getTrackingNumbers('valid', 'valid');
        $this->assertNull($result, 'Should return null on error');
    }

    public function testGetCallsReturnsCallsOnSuccess()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['calls' => [['id' => 1, 'duration' => 60]]])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['calls' => [['id' => 1, 'duration' => 60]]]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $result = $this->apiService->getCalls('valid', 'valid');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('calls', $result);
    }

    public function testGetCallsReturnsNullOnError()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 500],
            'body' => json_encode(['error' => 'Server error'])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['error' => 'Server error']));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        $result = $this->apiService->getCalls('valid', 'valid');
        $this->assertNull($result, 'Should return null on error');
    }

    public function testValidateCredentialsReturnsTrueForValid()
    {
        $apiService = new ApiService('https://dummy-ctm-api.test');
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['accounts' => [['id' => 1]]])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['accounts' => [['id' => 1]]]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $this->assertTrue($apiService->validateCredentials('valid', 'valid'));
    }
    public function testValidateCredentialsReturnsFalseOnException()
    {
        $apiService = new ApiService('https://dummy-ctm-api.test');
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('Simulated error');
        });
        $this->assertFalse($apiService->validateCredentials('invalid', 'invalid'));
    }

    public function testCheckApiHealthReturnsTrueIfApiUp()
    {
        \Brain\Monkey\Functions\when('wp_remote_get')->justReturn([
            'response' => ['code' => 200],
            'body' => 'pong'
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $this->assertTrue($this->apiService->checkApiHealth());
    }

    public function testCheckApiHealthReturnsFalseIfApiDown()
    {
        \Brain\Monkey\Functions\when('wp_remote_get')->justReturn([
            'response' => ['code' => 500],
            'body' => 'error'
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(500);
        $this->assertFalse($this->apiService->checkApiHealth());
    }

    public function testSetTimeoutSetsTimeoutCorrectly()
    {
        $this->apiService->setTimeout(42);
        $this->assertEquals(42, $this->apiService->getTimeout());
    }

    public function testSetTimeoutMinimumOneSecond()
    {
        $this->apiService->setTimeout(0);
        $this->assertEquals(1, $this->apiService->getTimeout());
    }

    public function testGetBaseUrlReturnsCorrectUrl()
    {
        $this->assertEquals('https://dummy-ctm-api.test', $this->apiService->getBaseUrl());
    }

    public function testGetTimeoutReturnsCorrectTimeout()
    {
        $this->apiService->setTimeout(15);
        $this->assertEquals(15, $this->apiService->getTimeout());
    }

    public function testGetTrackingScriptReturnsScriptOnSuccess()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['scripts' => ['<script>test</script>']])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['scripts' => ['<script>test</script>']]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $result = $this->apiService->getTrackingScript('123', 'valid', 'valid');
        $this->assertIsArray($result);
        $this->assertArrayHasKey('scripts', $result);
        $this->assertEquals(['<script>test</script>'], $result['scripts']);
    }

    public function testGetTrackingScriptReturnsNullOnException()
    {
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('Simulated exception');
        });
        $result = $this->apiService->getTrackingScript('123', 'valid', 'valid');
        $this->assertNull($result, 'Should return null on exception');
    }

    public function testTrackApiCallUpdatesOption()
    {
        // We'll call a public method that triggers trackApiCall (e.g., getAccountInfo)
        $calls = [];
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) use (&$calls) {
            if ($key === 'ctm_api_calls_24h') return $calls;
            return $default;
        });
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) use (&$calls) {
            if ($key === 'ctm_api_calls_24h') {
                $calls = $value;
                return true;
            }
            return false;
        });
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn([
            'response' => ['code' => 200],
            'body' => json_encode(['accounts' => [['id' => '123', 'name' => 'Test Account']]])
        ]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['accounts' => [['id' => '123', 'name' => 'Test Account']]]));
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        $this->apiService->getAccountInfo('valid', 'valid');
        $this->assertNotEmpty($calls, 'ctm_api_calls_24h should be updated');
        $this->assertGreaterThanOrEqual(1, count($calls));
    }

    public function testTrackApiResponseTimeUpdatesOption()
    {
        // We'll call a public method that triggers trackApiResponseTime (simulate via reflection if not called directly)
        $response_times = [];
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) use (&$response_times) {
            if ($key === 'ctm_api_response_times') return $response_times;
            return $default;
        });
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) use (&$response_times) {
            if ($key === 'ctm_api_response_times') {
                $response_times = $value;
                return true;
            }
            return false;
        });
        // Use reflection to call private method
        $ref = new \ReflectionClass($this->apiService);
        $method = $ref->getMethod('trackApiResponseTime');
        $method->setAccessible(true);
        $method->invoke($this->apiService, 123.45);
        $this->assertNotEmpty($response_times, 'ctm_api_response_times should be updated');
        $this->assertContains(123.45, $response_times);
    }
} 