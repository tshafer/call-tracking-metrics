<?php

if (!defined('CTM_TESTING')) {
    define('CTM_TESTING', true);
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;
use CTM\Service\DuplicatePreventionService;

class DuplicatePreventionServiceTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        \Brain\Monkey\Functions\when('set_transient')->justReturn(true);
        \Brain\Monkey\Functions\when('delete_transient')->justReturn(true);
        \Brain\Monkey\Functions\when('get_option')->justReturn(true);
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($text) { return $text; });
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testIsDuplicateSubmissionReturnsFalseForFirstSubmission()
    {
        $service = new DuplicatePreventionService();
        
        // Mock that no transient exists (first submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $service->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertFalse($result);
    }

    public function testIsDuplicateSubmissionReturnsTrueForDuplicate()
    {
        $service = new DuplicatePreventionService();
        
        // Mock that transient exists (duplicate submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(time());
        
        $result = $service->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertTrue($result);
    }

    public function testGenerateTransientKeyCreatesUniqueKeys()
    {
        $service = new DuplicatePreventionService();
        
        // Use reflection to access private method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($service, 'session1', 'form1', 'cf7');
        $key2 = $method->invoke($service, 'session2', 'form1', 'cf7');
        $key3 = $method->invoke($service, 'session1', 'form2', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
        $this->assertNotEquals($key2, $key3);
    }

    public function testIsEnabledReturnsTrueByDefault()
    {
        $service = new DuplicatePreventionService();
        
        $result = $service->isEnabled();
        
        $this->assertTrue($result);
    }

    public function testGetSettingsReturnsExpectedStructure()
    {
        $service = new DuplicatePreventionService();
        
        $settings = $service->getSettings();
        
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('expiration_seconds', $settings);
        $this->assertArrayHasKey('use_ctm_session', $settings);
        $this->assertArrayHasKey('fallback_to_ip', $settings);
        
        $this->assertIsBool($settings['enabled']);
        $this->assertIsInt($settings['expiration_seconds']);
        $this->assertIsBool($settings['use_ctm_session']);
        $this->assertIsBool($settings['fallback_to_ip']);
    }
}
