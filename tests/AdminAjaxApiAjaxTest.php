<?php
require_once __DIR__ . '/../src/Admin/Ajax/ApiAjax.php';
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\ApiAjax;
use Brain\Monkey;

class AdminAjaxApiAjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        \Brain\Monkey\Functions\when('current_time')->justReturn('2024-01-01 00:00:00');
        \Brain\Monkey\Functions\when('wp_generate_uuid4')->justReturn('uuid-1234');
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('5.8');
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        // Mock wp_remote_request to return a fake successful response
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            return [
                'response' => ['code' => 200],
                'body' => json_encode([
                    'account' => ['id' => 1],
                    'forms' => [],
                    'numbers' => [],
                    'calls' => []
                ])
            ];
        });

        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{}');
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
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        // Simulate empty POST
        $_POST['api_key'] = '';
        $_POST['api_secret'] = '';
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }

    public function testAjaxTestApiConnectionShortCredentials()
    {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = 'short';
        $_POST['api_secret'] = 'short';
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('Invalid API credential format', $called['message']);
    }

    public function testAjaxTestApiConnectionHandlesException()
    {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->will($this->throwException(new \Exception('timeout')));
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertStringContainsString('Failed to connect to CTM API', $called['message']);
    }

    public function testAjaxSimulateApiRequestNoCredentials()
    {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn(false);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('API credentials not configured', $called['message']);
    }

    public function testAjaxSimulateApiRequestUnsupportedEndpoint()
    {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $_POST['endpoint'] = '/api/v1/unknown';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('Unsupported endpoint', $called['message']);
    }

    public function testAjaxSimulateApiRequestSuccess()
    {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo', 'getForms', 'getTrackingNumbers', 'getCalls'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->willReturn(['account' => ['id' => 1]]);
        $mockApiService->method('getForms')->willReturn(['forms' => []]);
        $mockApiService->method('getTrackingNumbers')->willReturn(['numbers' => []]);
        $mockApiService->method('getCalls')->willReturn(['calls' => []]);
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/accounts/', $called['endpoint']);
        $_POST['endpoint'] = '/api/v1/forms';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/forms', $called['endpoint']);
        $_POST['endpoint'] = '/api/v1/tracking_numbers';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/tracking_numbers', $called['endpoint']);
        $_POST['endpoint'] = '/api/v1/calls';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('/api/v1/calls', $called['endpoint']);
    }

    // Additional tests for broader coverage
    public function testAjaxTestApiConnectionInvalidNonce() {
        $apiAjax = $this->getMockBuilder(ApiAjax::class)
            ->onlyMethods(['assessConnectionQuality'])
            ->getMock();
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(false);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
    }
    public function testAjaxTestApiConnectionEmptyApiKey() {
        $apiAjax = $this->getMockBuilder(ApiAjax::class)
            ->onlyMethods(['assessConnectionQuality'])
            ->getMock();
        $_POST['api_key'] = '';
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }
    public function testAjaxTestApiConnectionEmptyApiSecret() {
        $apiAjax = $this->getMockBuilder(ApiAjax::class)
            ->onlyMethods(['assessConnectionQuality'])
            ->getMock();
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = '';
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('API Key and Secret are required', $called['message']);
    }
    public function testAjaxTestApiConnectionApiServiceReturnsNull() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->willReturn(null);
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('Failed to connect to CTM API', $called['message']);
    }
    public function testAjaxTestApiConnectionApiServiceReturnsNoAccount() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->willReturn(['foo' => 'bar']);
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('Failed to connect to CTM API', $called['message']);
    }
    public function testAjaxTestApiConnectionApiServiceReturnsAccountNoId() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo', 'getAccountById'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->willReturn(['account' => []]);
        $mockApiService->method('getAccountById')->willReturn(null);
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertEquals('API Connection successful', $called['message']);
        $this->assertEquals('N/A', $called['account_id']);
    }
    public function testAjaxTestApiConnectionApiServiceThrowsSslException() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->will($this->throwException(new \Exception('SSL error')));
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertStringContainsString('SSL', implode(' ', $called['details']));
    }
    public function testAjaxTestApiConnectionApiServiceThrowsDnsException() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->will($this->throwException(new \Exception('DNS error')));
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertStringContainsString('DNS', implode(' ', $called['details']));
    }
    public function testAjaxTestApiConnectionApiServiceThrowsGenericException() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->will($this->throwException(new \Exception('Some error')));
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertStringContainsString('Failed to connect to CTM API', $called['message']);
    }
    public function testAjaxTestApiConnectionUpdatesOptions() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo', 'getAccountById'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->willReturn(['account' => ['id' => 123]]);
        $mockApiService->method('getAccountById')->willReturn(['id' => 123]);
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        $updated = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($k, $v = null) use (&$updated) { $updated[$k] = $v; return true; });
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertArrayHasKey('account_id', $called);
        $this->assertEquals(123, $updated['ctm_api_auth_account']);
    }
    public function testAjaxTestApiConnectionReturnsCorrectMetadata() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAccountInfo', 'getAccountById'])
            ->getMock();
        $mockApiService->method('getAccountInfo')->willReturn(['account' => ['id' => 123]]);
        $mockApiService->method('getAccountById')->willReturn(['id' => 123]);
        $apiAjax = new ApiAjax($mockApiService);
        $_POST['api_key'] = str_repeat('a', 21);
        $_POST['api_secret'] = str_repeat('b', 21);
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        $apiAjax->ajaxTestApiConnection();
        $this->assertIsArray($called);
        $this->assertArrayHasKey('metadata', $called);
        $this->assertArrayHasKey('timestamp', $called['metadata']);
    }
    public function testAjaxSimulateApiRequestPostMethod() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'POST';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('POST', $called['method']);
    }
    public function testAjaxSimulateApiRequestPutMethod() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'PUT';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('PUT', $called['method']);
    }
    public function testAjaxSimulateApiRequestDeleteMethod() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'DELETE';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('DELETE', $called['method']);
    }
    public function testAjaxSimulateApiRequestThrowsException() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockApiService->method('getAccountInfo')->will($this->throwException(new \Exception('Simulated error')));
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        $apiAjax->ajaxSimulateApiRequest();
        $this->assertIsArray($called);
        $this->assertEquals('Simulated error', $called['message']);
    }
    public function testAjaxSimulateApiRequestReturnsTimestamp() {
        $mockApiService = $this->getMockBuilder(\CTM\Service\ApiService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $apiAjax = new ApiAjax($mockApiService);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        $called = false;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $_POST['endpoint'] = '/api/v1/accounts/';
        $_POST['method'] = 'GET';
        // Simulate a valid response for getAccountInfo
        $mockApiService->method('getAccountInfo')->willReturn(['account' => ['id' => 1]]);
        $apiAjax->ajaxSimulateApiRequest();
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