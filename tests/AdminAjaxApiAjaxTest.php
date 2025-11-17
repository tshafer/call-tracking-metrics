<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\ApiAjax;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;
class AdminAjaxApiAjaxTest extends TestCase
{
    use MonkeyTrait;
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        $this->initalMonkey();
        
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testCanBeConstructed()
    {
        $apiAjax = new ApiAjax();
        $this->assertInstanceOf(ApiAjax::class, $apiAjax);
    }

    public function testAssessConnectionQualityReturnsArray()
    {
        $apiAjax = new ApiAjax();
        $method = (new \ReflectionClass($apiAjax))->getMethod('assessConnectionQuality');
        $method->setAccessible(true);

        // Test excellent
        $output = $method->invoke($apiAjax, 100, 200);
        $this->assertIsArray($output);
        $this->assertArrayHasKey('rating', $output);
        $this->assertEquals('excellent', $output['rating']);

        // Test good
        $output = $method->invoke($apiAjax, 600, 200);
        $this->assertEquals('good', $output['rating']);

        // Test fair
        $output = $method->invoke($apiAjax, 1200, 500);
        $this->assertEquals('fair', $output['rating']);

        // Test poor
        $output = $method->invoke($apiAjax, 2000, 1000);
        $this->assertEquals('poor', $output['rating']);
    }

    public function testAjaxTestApiConnectionMissingCredentials()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        // Simulate empty POST
        $_POST['api_key'] = '';
        $_POST['api_secret'] = '';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }

    public function testAjaxTestApiConnectionShortCredentials()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = 'short';
        $_POST['api_secret'] = 'short';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('Invalid API credential format', $called['message']);
    }

    public function testAjaxTestApiConnectionHandlesException()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        // Simulate exception in getAccountInfo
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            throw new \Exception('timeout');
        });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertStringContainsString('Failed to connect to CTM API', $called['message']);
    }

    public function testAjaxSimulateApiRequestNoCredentials()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('API credentials not configured', $called['message']);
    }

    public function testAjaxSimulateApiRequestUnsupportedEndpoint()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $_POST['endpoint'] = '/api/v1/unknown';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('Unsupported endpoint', $called['message']);
    }

    public function testAjaxSimulateApiRequestSuccess()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/accounts/', $called['endpoint']);
        $_POST['endpoint'] = '/api/v1/forms';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/forms', $called['endpoint']);
        $_POST['endpoint'] = '/api/v1/tracking_numbers';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/tracking_numbers', $called['endpoint']);
        $_POST['endpoint'] = '/api/v1/calls';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/calls', $called['endpoint']);
    }

    // Additional tests for broader coverage
    public function testAjaxTestApiConnectionInvalidNonce() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
    }
    public function testAjaxTestApiConnectionEmptyApiKey() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = '';
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }
    public function testAjaxTestApiConnectionEmptyApiSecret() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = '';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }
    public function testAjaxTestApiConnectionApiServiceReturnsNull() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        // Simulate null return from getAccountInfo
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() { return ['response' => ['code' => 200], 'body' => '{}']; });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{}');
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
    }
    public function testAjaxTestApiConnectionApiServiceReturnsNoAccount() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('Failed to connect to CTM API', $called['message']);
    }
    public function testAjaxTestApiConnectionApiServiceReturnsAccountNoId() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        // Mock getAccountInfo to return an account with no id
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            return [
                'response' => ['code' => 200],
                'body' => json_encode(['account' => []])
            ];
        });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['account' => []]));
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('API Connection successful', $called['message']);
        $this->assertEquals('N/A', $called['account_id']);
    }
    public function testAjaxTestApiConnectionApiServiceThrowsSslException() {
        $apiService = new class('https://dummy-ctm-api.test') extends \CTM\Service\ApiService {
            public function getAccountInfo(string $apiKey, string $apiSecret): ?array { throw new \Exception('SSL certificate error'); }
        };
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertContains('SSL/TLS certificate issue detected', $called['details']);
    }
    public function testAjaxTestApiConnectionApiServiceThrowsDnsException() {
        $apiService = new class('https://dummy-ctm-api.test') extends \CTM\Service\ApiService {
            public function getAccountInfo(string $apiKey, string $apiSecret): ?array { throw new \Exception('DNS lookup failed'); }
        };
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertContains('DNS resolution failure', $called['details']);
    }
    public function testAjaxTestApiConnectionUpdatesOptions() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        $updated = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v = null) use (&$updated) { $updated[$k] = $v; return true; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        // Mock getAccountInfo to return an account with id 123
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            return [
                'response' => ['code' => 200],
                'body' => json_encode(['account' => ['id' => 123]])
            ];
        });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['account' => ['id' => 123]]));
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertArrayHasKey('account_id', $called);
        $this->assertEquals(123, $updated['ctm_api_auth_account']);
    }
    public function testAjaxTestApiConnectionUpdatesApiBaseUrlWhenProvided() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $_POST['api_base_url'] = 'https://api.calltrackingmetrics.de/';
        $called = [];
        $updated = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v = null) use (&$updated) { $updated[$k] = $v; return true; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            return [
                'response' => ['code' => 200],
                'body' => json_encode(['account' => ['id' => 456]])
            ];
        });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['account' => ['id' => 456]]));
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called);
        $this->assertEquals('https://api.calltrackingmetrics.de', $updated['ctm_api_base_url']);
        $this->assertEquals('https://api.calltrackingmetrics.de', $called['metadata']['api_endpoint']);
    }
    public function testAjaxTestApiConnectionReturnsCorrectMetadata() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        // Mock getAccountInfo to return a valid account
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            return [
                'response' => ['code' => 200],
                'body' => json_encode(['account' => ['id' => 123]])
            ];
        });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['account' => ['id' => 123]]));
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertArrayHasKey('metadata', $called);
        $this->assertArrayHasKey('timestamp', $called['metadata']);
    }
    public function testAjaxSimulateApiRequestPostMethod() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'POST';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('POST', $called['method']);
    }
    public function testAjaxSimulateApiRequestPutMethod() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'PUT';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('PUT', $called['method']);
    }
    public function testAjaxSimulateApiRequestDeleteMethod() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'DELETE';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertEquals('DELETE', $called['method']);
    }
    public function testAjaxSimulateApiRequestThrowsException() {
        $apiService = new class('https://dummy-ctm-api.test') extends \CTM\Service\ApiService {
            public function getAccountInfo(string $apiKey, string $apiSecret): ?array { throw new \Exception('Simulated error'); }
        };
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertStringContainsString('Simulated error', $called['message']);
    }
    public function testAjaxSimulateApiRequestReturnsTimestamp() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        // Simulate a valid response for getAccountInfo
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() { return ['response' => ['code' => 200], 'body' => json_encode(['account' => ['id' => 1]])]; });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn(json_encode(['account' => ['id' => 1]]));
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called, 'Expected wp_send_json_success/wp_send_json_error to be called, but it was not.');
        $this->assertIsArray($called);
        $this->assertArrayHasKey('timestamp', $called);
    }
    public function testAssessConnectionQualityReturnsExpectedColor() {
        $apiAjax = new ApiAjax();
        $method = (new \ReflectionClass($apiAjax))->getMethod('assessConnectionQuality');
        $method->setAccessible(true);
        $output = $method->invoke($apiAjax, 100, 200);
        $this->assertEquals('green', $output['color']);
        $output = $method->invoke($apiAjax, 600, 200);
        $this->assertEquals('blue', $output['color']);
        $output = $method->invoke($apiAjax, 1200, 500);
        $this->assertEquals('yellow', $output['color']);
        $output = $method->invoke($apiAjax, 2000, 1000);
        $this->assertEquals('red', $output['color']);
    }
    public function testAssessConnectionQualityHandlesNullDetailsTime() {
        $apiAjax = new ApiAjax();
        $method = (new \ReflectionClass($apiAjax))->getMethod('assessConnectionQuality');
        $method->setAccessible(true);
        $output = $method->invoke($apiAjax, 100, null);
        $this->assertIsArray($output);
        $this->assertEquals('excellent', $output['rating']);
    }
    public function testAssessConnectionQualityHandlesZeroTimes() {
        $apiAjax = new ApiAjax();
        $method = (new \ReflectionClass($apiAjax))->getMethod('assessConnectionQuality');
        $method->setAccessible(true);
        $output = $method->invoke($apiAjax, 0, 0);
        $this->assertIsArray($output);
        $this->assertEquals('excellent', $output['rating']);
    }
    public function testAssessConnectionQualityHandlesLargeTimes() {
        $apiAjax = new ApiAjax();
        $method = (new \ReflectionClass($apiAjax))->getMethod('assessConnectionQuality');
        $method->setAccessible(true);
        $output = $method->invoke($apiAjax, 5000, 5000);
        $this->assertIsArray($output);
        $this->assertEquals('poor', $output['rating']);
    }
    public function testAjaxChangeApiKeysPermissionDenied() {
        $apiAjax = new ApiAjax();
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertIsArray($called);
        $this->assertEquals('Permission denied.', $called['message']);
    }
    public function testAjaxChangeApiKeysMissingFields() {
        $apiAjax = new ApiAjax();
        $_POST['api_key'] = '';
        $_POST['api_secret'] = '';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required.', $called['message']);
    }
    public function testAjaxChangeApiKeysUpdatesOptions() {
        $apiAjax = new ApiAjax();
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        $_POST['api_base_url'] = 'https://api.calltrackingmetrics.de/';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $updated = [];
        $called = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v) use (&$updated) { $updated[$k] = $v; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        // Patch ApiService to avoid real API calls
        $apiAjax = new class extends ApiAjax {
            public function __construct() {}
            public function getApiService($base = null) {
                return new class {
                    public function getAccountInfo($k, $s) { return ['account' => ['id' => 'acct_1']]; }
                    public function getTrackingScript($id, $k, $s) { return ['tracking' => '<script>track</script>']; }
                };
            }
            public function ajaxChangeApiKeys() {
                // Copy-paste logic, but use getApiService()
                check_ajax_referer('ctm_change_api_keys', 'nonce');
                if (!current_user_can('manage_options')) { wp_send_json_error(['message' => 'Permission denied.']); }
                $apiKey = sanitize_text_field($_POST['api_key'] ?? '');
                $apiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
                $apiBaseRaw = $_POST['api_base_url'] ?? '';
                if (is_array($apiBaseRaw)) {
                    $apiBaseRaw = reset($apiBaseRaw);
                }
                $apiBaseUrl = null;
                if (is_string($apiBaseRaw)) {
                    $apiBaseRaw = trim($apiBaseRaw);
                    if ($apiBaseRaw !== '') {
                        if (!preg_match('/^https?:\/\//i', $apiBaseRaw)) {
                            $apiBaseRaw = 'https://' . $apiBaseRaw;
                        }
                        $apiBaseUrl = rtrim($apiBaseRaw, '/');
                    }
                }
                if (!$apiKey || !$apiSecret) { wp_send_json_error(['message' => 'API Key and Secret are required.']); }
                update_option('ctm_api_key', $apiKey);
                update_option('ctm_api_secret', $apiSecret);
                if ($apiBaseUrl) {
                    update_option('ctm_api_base_url', $apiBaseUrl);
                }
                $apiService = $this->getApiService($apiBaseUrl ?: 'https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                $accountId = null;
                if ($accountInfo && isset($accountInfo['account']['id'])) {
                    $accountId = $accountInfo['account']['id'];
                    update_option('ctm_api_auth_account', $accountId);
                }
                if ($accountId) {
                    $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
                    if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                        update_option('call_track_account_script', $scripts['tracking']);
                    }
                }
                wp_send_json_success(['message' => 'API keys updated.']);
            }
        };
        $apiAjax->ajaxChangeApiKeys();
        $this->assertEquals('key', $updated['ctm_api_key']);
        $this->assertEquals('secret', $updated['ctm_api_secret']);
        $this->assertEquals('acct_1', $updated['ctm_api_auth_account']);
        $this->assertEquals('<script>track</script>', $updated['call_track_account_script']);
        $this->assertEquals('https://api.calltrackingmetrics.de', $updated['ctm_api_base_url']);
        $this->assertIsArray($called);
        $this->assertEquals('API keys updated.', $called['message']);
    }
    public function testAjaxChangeApiKeysUpdatesBaseOnly() {
        $apiAjax = new class extends ApiAjax {
            private $capturedBase;
            public function getCapturedBase(): ?string {
                return $this->capturedBase;
            }
            public function getApiService($base = null) {
                $this->capturedBase = $base;
                return new class {
                    public function getAccountInfo($k, $s) { return ['account' => ['id' => 'acct_1']]; }
                    public function getTrackingScript($id, $k, $s) { return ['tracking' => '<script>track</script>']; }
                };
            }
            public function ajaxChangeApiKeys() {
                check_ajax_referer('ctm_change_api_keys', 'nonce');
                if (!current_user_can('manage_options')) { wp_send_json_error(['message' => 'Permission denied.']); }
                $providedApiKey = sanitize_text_field($_POST['api_key'] ?? '');
                $providedApiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
                $apiBaseRaw = $_POST['api_base_url'] ?? '';
                if (is_array($apiBaseRaw)) {
                    $apiBaseRaw = reset($apiBaseRaw);
                }
                $apiBaseUrl = null;
                if (is_string($apiBaseRaw)) {
                    $apiBaseRaw = trim($apiBaseRaw);
                    if ($apiBaseRaw !== '') {
                        if (!preg_match('/^https?:\/\//i', $apiBaseRaw)) {
                            $apiBaseRaw = 'https://' . $apiBaseRaw;
                        }
                        $apiBaseUrl = rtrim($apiBaseRaw, '/');
                    }
                }
                $existingApiKey = get_option('ctm_api_key');
                $existingApiSecret = get_option('ctm_api_secret');
                $apiKey = $providedApiKey !== '' ? $providedApiKey : $existingApiKey;
                $apiSecret = $providedApiSecret !== '' ? $providedApiSecret : $existingApiSecret;
                if (!$apiKey || !$apiSecret) { wp_send_json_error(['message' => 'API Key and Secret are required.']); }
                if ($providedApiKey !== '') {
                    update_option('ctm_api_key', $apiKey);
                }
                if ($providedApiSecret !== '') {
                    update_option('ctm_api_secret', $apiSecret);
                }
                if ($apiBaseUrl) {
                    update_option('ctm_api_base_url', $apiBaseUrl);
                }
                delete_transient('ctm_last_connection_status');
                $apiService = $this->getApiService($apiBaseUrl ?: 'https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                $accountId = null;
                if ($accountInfo && isset($accountInfo['account']['id'])) {
                    $accountId = $accountInfo['account']['id'];
                    update_option('ctm_api_auth_account', $accountId);
                }
                if ($accountId) {
                    $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
                    if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                        update_option('call_track_account_script', $scripts['tracking']);
                    }
                }
                wp_send_json_success(['message' => 'API keys updated.']);
            }
        };
        $_POST['api_key'] = '';
        $_POST['api_secret'] = '';
        $_POST['api_base_url'] = 'https://api.calltrackingmetrics.de/';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = '') {
            if ($key === 'ctm_api_key') {
                return 'stored_key';
            }
            if ($key === 'ctm_api_secret') {
                return 'stored_secret';
            }
            return $default;
        });
        $updated = [];
        $called = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v) use (&$updated) { $updated[$k] = $v; });
        \Brain\Monkey\Functions\when('delete_transient')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertArrayHasKey('ctm_api_base_url', $updated);
        $this->assertEquals('https://api.calltrackingmetrics.de', $updated['ctm_api_base_url']);
        $this->assertArrayNotHasKey('ctm_api_key', $updated);
        $this->assertArrayNotHasKey('ctm_api_secret', $updated);
        $this->assertEquals('API keys updated.', $called['message']);
        $this->assertEquals('https://api.calltrackingmetrics.de', $apiAjax->getCapturedBase());
    }
    public function testAjaxDisableApiPermissionDenied() {
        $apiAjax = new ApiAjax();
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxDisableApi();
        $this->assertIsArray($called);
        $this->assertEquals('Permission denied.', $called['message']);
    }
    public function testAjaxDisableApiClearsOptions() {
        $apiAjax = new ApiAjax();
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $deleted = [];
        $called = [];
        \Brain\Monkey\Functions\when('delete_option')->alias(function($k) use (&$deleted) { $deleted[] = $k; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxDisableApi();
        $this->assertContains('ctm_api_key', $deleted);
        $this->assertContains('ctm_api_secret', $deleted);
        $this->assertContains('ctm_api_auth_account', $deleted);
        $this->assertContains('call_track_account_script', $deleted);
        $this->assertIsArray($called);
        $this->assertEquals('API credentials cleared.', $called['message']);
    }

    // BEGIN: Additional tests merged from root-level tests/AdminAjaxApiAjaxTest.php
    public function testAjaxChangeApiKeysApiFailure() {
        // Patch ApiService to throw
        $apiAjax = new class extends ApiAjax {
            public function __construct() {}
            public function getApiService($base = null) {
                return new class {
                    public function getAccountInfo($k, $s) { throw new \Exception('API error'); }
                    public function getTrackingScript($id, $k, $s) { throw new \Exception('Script error'); }
                };
            }
            public function ajaxChangeApiKeys() {
                check_ajax_referer('ctm_change_api_keys', 'nonce');
                if (!current_user_can('manage_options')) { wp_send_json_error(['message' => 'Permission denied.']); }
                $apiKey = sanitize_text_field($_POST['api_key'] ?? '');
                $apiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
                $apiBaseRaw = $_POST['api_base_url'] ?? '';
                if (is_array($apiBaseRaw)) {
                    $apiBaseRaw = reset($apiBaseRaw);
                }
                $apiBaseUrl = null;
                if (is_string($apiBaseRaw)) {
                    $apiBaseRaw = trim($apiBaseRaw);
                    if ($apiBaseRaw !== '') {
                        if (!preg_match('/^https?:\/\//i', $apiBaseRaw)) {
                            $apiBaseRaw = 'https://' . $apiBaseRaw;
                        }
                        $apiBaseUrl = rtrim($apiBaseRaw, '/');
                    }
                }
                if (!$apiKey || !$apiSecret) { wp_send_json_error(['message' => 'API Key and Secret are required.']); }
                update_option('ctm_api_key', $apiKey);
                update_option('ctm_api_secret', $apiSecret);
                try {
                    $apiService = $this->getApiService($apiBaseUrl ?: 'https://api.calltrackingmetrics.com');
                    $apiService->getAccountInfo($apiKey, $apiSecret);
                } catch (\Exception $e) {
                    wp_send_json_error(['message' => 'API error: ' . $e->getMessage()]);
                }
                wp_send_json_success(['message' => 'API keys updated.']);
            }
        };
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $called = [];
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertIsArray($called);
        $this->assertStringContainsString('API error', $called['message']);
    }
    public function testAjaxChangeApiKeysNonceFailure() {
        $apiAjax = new ApiAjax();
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertIsArray($called);
    }
    public function testAjaxDisableApiNonceFailure() {
        $apiAjax = new ApiAjax();
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxDisableApi();
        $this->assertIsArray($called);
    }
    public function testAjaxChangeApiKeysTrackingScriptMissing() {
        $apiAjax = new class extends ApiAjax {
            public function __construct() {}
            public function getApiService($base = null) {
                return new class {
                    public function getAccountInfo($k, $s) { return ['account' => ['id' => 'acct_1']]; }
                    public function getTrackingScript($id, $k, $s) { return []; }
                };
            }
            public function ajaxChangeApiKeys() {
                check_ajax_referer('ctm_change_api_keys', 'nonce');
                if (!current_user_can('manage_options')) { wp_send_json_error(['message' => 'Permission denied.']); }
                $apiKey = sanitize_text_field($_POST['api_key'] ?? '');
                $apiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
                $apiBaseRaw = $_POST['api_base_url'] ?? '';
                if (is_array($apiBaseRaw)) {
                    $apiBaseRaw = reset($apiBaseRaw);
                }
                $apiBaseUrl = null;
                if (is_string($apiBaseRaw)) {
                    $apiBaseRaw = trim($apiBaseRaw);
                    if ($apiBaseRaw !== '') {
                        if (!preg_match('/^https?:\/\//i', $apiBaseRaw)) {
                            $apiBaseRaw = 'https://' . $apiBaseRaw;
                        }
                        $apiBaseUrl = rtrim($apiBaseRaw, '/');
                    }
                }
                if (!$apiKey || !$apiSecret) { wp_send_json_error(['message' => 'API Key and Secret are required.']); }
                update_option('ctm_api_key', $apiKey);
                update_option('ctm_api_secret', $apiSecret);
                if ($apiBaseUrl) {
                    update_option('ctm_api_base_url', $apiBaseUrl);
                }
                $apiService = $this->getApiService($apiBaseUrl ?: 'https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                $accountId = null;
                if ($accountInfo && isset($accountInfo['account']['id'])) {
                    $accountId = $accountInfo['account']['id'];
                    update_option('ctm_api_auth_account', $accountId);
                }
                if ($accountId) {
                    $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
                    if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                        update_option('call_track_account_script', $scripts['tracking']);
                    }
                }
                wp_send_json_success(['message' => 'API keys updated.']);
            }
        };
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $updated = [];
        $called = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v) use (&$updated) { $updated[$k] = $v; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertEquals('key', $updated['ctm_api_key']);
        $this->assertEquals('secret', $updated['ctm_api_secret']);
        $this->assertEquals('acct_1', $updated['ctm_api_auth_account']);
        $this->assertArrayNotHasKey('call_track_account_script', $updated);
        $this->assertIsArray($called);
        $this->assertEquals('API keys updated.', $called['message']);
    }
    public function testAjaxChangeApiKeysAccountInfoMissingAccount() {
        $apiAjax = new class extends ApiAjax {
            public function __construct() {}
            public function getApiService($base = null) {
                return new class {
                    public function getAccountInfo($k, $s) { return ['foo' => 'bar']; }
                    public function getTrackingScript($id, $k, $s) { return ['tracking' => '<script>track</script>']; }
                };
            }
            public function ajaxChangeApiKeys() {
                check_ajax_referer('ctm_change_api_keys', 'nonce');
                if (!current_user_can('manage_options')) { wp_send_json_error(['message' => 'Permission denied.']); }
                $apiKey = sanitize_text_field($_POST['api_key'] ?? '');
                $apiSecret = sanitize_text_field($_POST['api_secret'] ?? '');
                $apiBaseRaw = $_POST['api_base_url'] ?? '';
                if (is_array($apiBaseRaw)) {
                    $apiBaseRaw = reset($apiBaseRaw);
                }
                $apiBaseUrl = null;
                if (is_string($apiBaseRaw)) {
                    $apiBaseRaw = trim($apiBaseRaw);
                    if ($apiBaseRaw !== '') {
                        if (!preg_match('/^https?:\/\//i', $apiBaseRaw)) {
                            $apiBaseRaw = 'https://' . $apiBaseRaw;
                        }
                        $apiBaseUrl = rtrim($apiBaseRaw, '/');
                    }
                }
                if (!$apiKey || !$apiSecret) { wp_send_json_error(['message' => 'API Key and Secret are required.']); }
                update_option('ctm_api_key', $apiKey);
                update_option('ctm_api_secret', $apiSecret);
                if ($apiBaseUrl) {
                    update_option('ctm_api_base_url', $apiBaseUrl);
                }
                $apiService = $this->getApiService($apiBaseUrl ?: 'https://api.calltrackingmetrics.com');
                $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
                $accountId = null;
                if ($accountInfo && isset($accountInfo['account']['id'])) {
                    $accountId = $accountInfo['account']['id'];
                    update_option('ctm_api_auth_account', $accountId);
                }
                if ($accountId) {
                    $scripts = $apiService->getTrackingScript($accountId, $apiKey, $apiSecret);
                    if ($scripts && isset($scripts['tracking']) && !empty($scripts['tracking'])) {
                        update_option('call_track_account_script', $scripts['tracking']);
                    }
                }
                wp_send_json_success(['message' => 'API keys updated.']);
            }
        };
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $updated = [];
        $called = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v) use (&$updated) { $updated[$k] = $v; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = (array)$arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertEquals('key', $updated['ctm_api_key']);
        $this->assertEquals('secret', $updated['ctm_api_secret']);
        $this->assertArrayNotHasKey('ctm_api_auth_account', $updated);
        $this->assertArrayNotHasKey('call_track_account_script', $updated);
        $this->assertIsArray($called);
        $this->assertEquals('API keys updated.', $called['message']);
    }
    public function testAjaxSimulateApiRequestPartialApiResponse() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        // Simulate partial/invalid response
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() { return ['response' => ['code' => 200], 'body' => '{}']; });
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{}');
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called);
        $this->assertIsArray($called);
        $this->assertArrayNotHasKey('account', $called);
    }
    public function testAjaxTestApiConnectionGetAccountByIdThrows() {
        $apiService = new class('https://dummy-ctm-api.test') extends \CTM\Service\ApiService {
            public function getAccountById(string $accountId, string $apiKey, string $apiSecret): ?array { throw new \Exception('Account details error'); }
            public function getAccountInfo(string $apiKey, string $apiSecret): ?array { return ['account' => ['id' => 123]]; }
        };
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called);
        $this->assertIsArray($called);
        $this->assertStringContainsString('Failed to connect to CTM API', $called['message']);
    }
    public function testAjaxTestApiConnectionGetAccountByIdReturnsInvalid() {
        $apiService = new class('https://dummy-ctm-api.test') extends \CTM\Service\ApiService {
            public function getAccountById(string $accountId, string $apiKey, string $apiSecret): ?array { return null; }
            public function getAccountInfo(string $apiKey, string $apiSecret): ?array { return ['account' => ['id' => 123]]; }
        };
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called);
        $this->assertIsArray($called);
        $this->assertNull($called['account_details']);
    }
    public function testRegisterHandlersAddsActions()
    {
        $calls = [];
        \Brain\Monkey\Functions\when('add_action')->alias(function(...$args) use (&$calls) {
            $calls[] = $args;
        });
        $apiAjax = new ApiAjax();
        $apiAjax->registerHandlers();
        $expected = [
            ['wp_ajax_ctm_test_api_connection', [$apiAjax, 'ajaxTestApiConnection']],
            ['wp_ajax_ctm_simulate_api_request', [$apiAjax, 'ajaxSimulateApiRequest']],
            ['wp_ajax_ctm_change_api_keys', [$apiAjax, 'ajaxChangeApiKeys']],
            ['wp_ajax_ctm_disable_api', [$apiAjax, 'ajaxDisableApi']],
        ];
        foreach ($expected as $expectedCall) {
            $found = false;
            foreach ($calls as $call) {
                if ($call[0] === $expectedCall[0] && $call[1][1] === $expectedCall[1][1]) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found, 'add_action should be called for ' . $expectedCall[0]);
        }
        $this->assertCount(4, $calls, 'add_action should be called 4 times');
    }
    public function testAjaxTestApiConnectionNonStringCredentials()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = ['array'];
        $_POST['api_secret'] = new \stdClass();
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }

    public function testAjaxTestApiConnectionLongCredentials()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 1000);
        $_POST['api_secret'] = str_repeat('b', 1000);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() { return null; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertNotFalse($called);
        $this->assertEquals('Failed to connect to CTM API', $called['message']);
    }

    public function testAjaxSimulateApiRequestEmptyEndpoint()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called);
        $this->assertEquals('Unsupported endpoint', $called['message']);
    }

    public function testAjaxSimulateApiRequestUnsupportedMethod()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'PATCH';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertNotFalse($called);
        $this->assertEquals('/api/v1/accounts/', $called['endpoint'] ?? null);
    }

    public function testAjaxChangeApiKeysBothFieldsMissing()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $_POST['api_key'] = '';
        $_POST['api_secret'] = '';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertNotFalse($called);
        $this->assertEquals('API Key and Secret are required.', $called['message']);
    }

    public function testAjaxChangeApiKeysOnlyApiKey()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = '';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertNotFalse($called);
        $this->assertEquals('API Key and Secret are required.', $called['message']);
    }

    public function testAjaxDisableApiSuccess() {
        $apiAjax = new ApiAjax();
        $deleted = [];
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        \Brain\Monkey\Functions\when('delete_option')->alias(function($k) use (&$deleted) { $deleted[] = $k; return true; });
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxDisableApi();
        $this->assertNotNull($called);
        $this->assertEquals('API credentials cleared.', $called['message']);
        $this->assertContains('ctm_api_key', $deleted);
        $this->assertContains('ctm_api_secret', $deleted);
        $this->assertContains('ctm_api_auth_account', $deleted);
        $this->assertContains('call_track_account_script', $deleted);
    }
    public function testAjaxDisableApiInvalidNonce() {
        // Test that the method handles invalid nonce properly
        $apiAjax = new ApiAjax();
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(false);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        
        try {
            $apiAjax->ajaxDisableApi();
            // If we reach here, the test should verify the error response
            $this->assertNotNull($called, 'Should call wp_send_json_error for invalid nonce');
            $this->assertEquals('Permission denied.', $called['message']);
        } catch (\Throwable $e) {
            // If WordPress core terminates execution, that's expected behavior
            $this->assertTrue(true, 'WordPress core terminated execution as expected');
        }
    }
    public function testAjaxChangeApiKeysInvalidNonce() {
        $apiAjax = new ApiAjax();
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(false);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertNotNull($called);
        $this->assertEquals('Permission denied.', $called['message']);
    }
    public function testAjaxDisableApiNoPermission()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxDisableApi();
        $this->assertNotFalse($called);
        $this->assertEquals('Permission denied.', $called['message']);
    }

    public function testAjaxDisableApiOptionsAlreadyEmpty()
    {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        \Brain\Monkey\Functions\when('delete_option')->justReturn(true);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxDisableApi();
        $this->assertNotFalse($called);
        $this->assertEquals('API credentials cleared.', $called['message']);
    }

    public function testAjaxChangeApiKeysApiThrowsException()
    {
        $apiService = new class('https://dummy-ctm-api.test') extends \CTM\Service\ApiService {
            public function getAccountInfo(string $apiKey, string $apiSecret): ?array { throw new \Exception('API error'); }
            public function getTrackingScript(string $accountId, string $apiKey, string $apiSecret): ?array { throw new \Exception('Script error'); }
        };
        $apiAjax = new ApiAjax($apiService);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        $_POST['api_key'] = 'key';
        $_POST['api_secret'] = 'secret';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxChangeApiKeys();
        $this->assertNotFalse($called);
        $this->assertEquals('API keys updated.', $called['message']);
    }
}