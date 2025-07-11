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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
    public function testAjaxTestApiConnectionReturnsCorrectMetadata() {
        $apiService = new \CTM\Service\ApiService('https://dummy-ctm-api.test');
        $apiAjax = new ApiAjax($apiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
        $called = false;
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
}