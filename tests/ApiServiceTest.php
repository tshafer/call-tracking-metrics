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

class ApiServiceTest extends TestCase
{
    protected ApiService $apiService;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        $this->apiService = new ApiService('https://dummy-ctm-api.test');

        // Mock wp_remote_request to simulate API failure by default
        \Brain\Monkey\Functions\when('wp_remote_request')->justReturn(['response' => ['code' => 401], 'body' => 'Unauthorized']);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('Unauthorized');
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(401);
        // Mock get_option to return null or dummy values as needed
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = null) {
            if ($key === 'some_option') return 'some_value';
            return $default;
        });
        // Mock update_option to always return true
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testGetAccountInfoReturnsNullOnFailure()
    {
        $result = $this->apiService->getAccountInfo('invalid', 'invalid');
        $this->assertNull($result, 'Should return null for invalid credentials');
    }

    public function testSubmitFormReactorReturnsNullOnFailure()
    {
        $formData = ['test' => 'data'];
        $result = $this->apiService->submitFormReactor($formData, 'invalid', 'invalid');
        $this->assertNull($result, 'Should return null for failed submission');
    }

    public function testValidateCredentialsReturnsFalseForInvalid()
    {
        $result = $this->apiService->validateCredentials('invalid', 'invalid');
        $this->assertFalse($result, 'Should return false for invalid credentials');
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
} 