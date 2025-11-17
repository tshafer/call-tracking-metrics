<?php
if (!defined('CTM_TESTING')) {
    define('CTM_TESTING', true);
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class CTMFunctionsTest extends TestCase
{
    use MonkeyTrait;
    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
    }
    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }
    public function testCtmGetApiUrlReturnsDefaultWhenNoOptionSet()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return $default;
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://api.calltrackingmetrics.com', $result);
    }

    public function testCtmGetApiUrlReturnsConfiguredUrl()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return 'https://custom.api.com';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://custom.api.com', $result);
    }

    public function testCtmGetApiUrlHandlesEmptyString()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return '';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://api.calltrackingmetrics.com', $result);
    }

    public function testCtmGetApiUrlHandlesWhitespace()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return '  https://custom.api.com  ';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://custom.api.com', $result);
    }

    public function testCtmGetApiUrlAddsHttpsProtocol()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return 'custom.api.com';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://custom.api.com', $result);
    }

    public function testCtmGetApiUrlAddsHttpsProtocolForHttpUrls()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return 'http://custom.api.com';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('http://custom.api.com', $result);
    }

    public function testCtmGetApiUrlRemovesTrailingSlash()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return 'https://custom.api.com/';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://custom.api.com', $result);
    }

    public function testCtmGetApiUrlHandlesMultipleTrailingSlashes()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return 'https://custom.api.com///';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://custom.api.com', $result);
    }

    public function testCtmGetApiUrlHandlesComplexUrl()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return '  custom.api.com/path/to/api///  ';
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://custom.api.com/path/to/api', $result);
    }

    public function testCtmGetApiUrlHandlesNullValue()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return null;
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://api.calltrackingmetrics.com', $result);
    }

    public function testCtmGetApiUrlHandlesFalseValue()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default) {
            if ($key === 'ctm_api_base_url') return false;
            return $default;
        });
        
        $result = ctm_get_api_url();
        $this->assertEquals('https://api.calltrackingmetrics.com', $result);
    }

    public function testCtmGetAvailableApiEndpointsReturnsDefaults()
    {
        \Brain\Monkey\Filters\expectApplied('ctm_available_api_endpoints')
            ->once()
            ->with(\Mockery::type('array'))
            ->andReturnUsing(function($endpoints) {
                return $endpoints;
            });

        $result = ctm_get_available_api_endpoints();
        $this->assertArrayHasKey('https://api.calltrackingmetrics.com', $result);
        $this->assertArrayHasKey('https://api.calltrackingmetrics.de', $result);
        $this->assertEquals(__('Global (.com)', 'call-tracking-metrics'), $result['https://api.calltrackingmetrics.com']);
        $this->assertEquals(__('Europe (.de)', 'call-tracking-metrics'), $result['https://api.calltrackingmetrics.de']);
    }

    public function testCtmGetAvailableApiEndpointsRespectsFilters()
    {
        $custom = ['https://custom.example' => 'Custom'];

        \Brain\Monkey\Filters\expectApplied('ctm_available_api_endpoints')
            ->once()
            ->andReturn($custom);

        $result = ctm_get_available_api_endpoints();
        $this->assertSame($custom, $result);
    }
} 