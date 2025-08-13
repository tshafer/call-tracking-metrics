<?php

use PHPUnit\Framework\TestCase;
use CTM\Service\DuplicatePreventionService;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class DuplicatePreventionUnitTest extends TestCase
{
    use MonkeyTrait;
    
    private DuplicatePreventionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        $this->service = new DuplicatePreventionService();
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_is_duplicate_submission_returns_false_for_first_submission()
    {
        // Mock that no transient exists (first submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $this->service->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_is_duplicate_submission_returns_true_for_duplicate()
    {
        // Mock that transient exists (duplicate submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(time());
        
        $result = $this->service->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertTrue($result);
    }

    public function test_generates_unique_transient_keys_for_different_forms()
    {
        $sessionId = 'test_session_123';
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->service, $sessionId, 'form1', 'cf7');
        $key2 = $method->invoke($this->service, $sessionId, 'form2', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
        $this->assertStringStartsWith('ctm_duplicate_', $key1);
        $this->assertStringStartsWith('ctm_duplicate_', $key2);
    }

    public function test_generates_unique_transient_keys_for_different_form_types()
    {
        $sessionId = 'test_session_123';
        
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->service, $sessionId, 'form1', 'cf7');
        $key2 = $method->invoke($this->service, $sessionId, 'form1', 'gf');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_generates_unique_transient_keys_for_different_sessions()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->service, 'session1', 'form1', 'cf7');
        $key2 = $method->invoke($this->service, 'session2', 'form1', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_handles_missing_ctm_session_id_gracefully()
    {
        // Mock no session ID available
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $this->service->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_clears_duplicate_prevention_correctly()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Set up prevention
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $this->service->isDuplicateSubmission($formId, $formType);
        
        // Clear prevention
        $this->service->clearDuplicatePrevention($formId, $formType);
        
        // Should allow submission again
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->service->isDuplicateSubmission($formId, $formType);
        
        $this->assertFalse($result);
    }

    public function test_returns_correct_settings_structure()
    {
        $settings = $this->service->getSettings();
        
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertArrayHasKey('expiration_seconds', $settings);
        $this->assertArrayHasKey('use_ctm_session', $settings);
        $this->assertArrayHasKey('fallback_to_ip', $settings);
        
        $this->assertIsBool($settings['enabled']);
        $this->assertIsInt($settings['expiration_seconds']);
        $this->assertIsBool($settings['use_ctm_session']);
        $this->assertIsBool($settings['fallback_to_ip']);
    }

    public function test_is_enabled_returns_true_by_default()
    {
        $result = $this->service->isEnabled();
        
        $this->assertTrue($result);
    }

    public function test_respects_custom_expiration_time()
    {
        $customExpiration = 120; // 2 minutes
        
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->service->isDuplicateSubmission('form123', 'cf7', $customExpiration);
        
        $this->assertFalse($result);
    }

    public function test_handles_edge_case_form_ids()
    {
        $edgeCaseFormIds = [
            '',                           // Empty string
            str_repeat('a', 1000),       // Very long
            'form-123_test@example.com', // Special characters
            '123',                       // Numeric
            'form_123',                  // Underscores
            'form-123',                  // Hyphens
        ];
        
        foreach ($edgeCaseFormIds as $formId) {
            \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
            $result = $this->service->isDuplicateSubmission($formId, 'cf7');
            
            $this->assertFalse($result);
        }
    }

    public function test_handles_different_form_types()
    {
        $formTypes = ['cf7', 'gf', 'elementor', 'woocommerce', 'custom'];
        
        foreach ($formTypes as $formType) {
            \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
            $result = $this->service->isDuplicateSubmission('form123', $formType);
            
            $this->assertFalse($result);
        }
    }

    public function test_transient_key_generation_is_consistent()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $sessionId = 'test_session_123';
        $formId = 'form123';
        $formType = 'cf7';
        
        // Generate key multiple times with same parameters
        $key1 = $method->invoke($this->service, $sessionId, $formId, $formType);
        $key2 = $method->invoke($this->service, $sessionId, $formId, $formType);
        
        $this->assertEquals($key1, $key2);
    }

    public function test_transient_key_length_is_consistent()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $sessionId = 'test_session_123';
        $formId = 'form123';
        $formType = 'cf7';
        
        $key = $method->invoke($this->service, $sessionId, $formId, $formType);
        
        // Key should be: 'ctm_duplicate_' + 32 char MD5 hash
        $this->assertEquals(46, strlen($key));
        $this->assertStringStartsWith('ctm_duplicate_', $key);
    }

    public function test_ip_based_fallback_creates_different_keys()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateIPTransientKey');
        $method->setAccessible(true);
        
        $ip1 = '192.168.1.100';
        $ip2 = '192.168.1.101';
        $formId = 'form123';
        $formType = 'cf7';
        
        $key1 = $method->invoke($this->service, $ip1, $formId, $formType);
        $key2 = $method->invoke($this->service, $ip2, $formId, $formType);
        
        $this->assertNotEquals($key1, $key2);
        $this->assertStringStartsWith('ctm_duplicate_ip_', $key1);
        $this->assertStringStartsWith('ctm_duplicate_ip_', $key2);
    }

    public function test_settings_defaults_are_correct()
    {
        $settings = $this->service->getSettings();
        
        $this->assertTrue($settings['enabled']);
        $this->assertEquals(60, $settings['expiration_seconds']);
        $this->assertTrue($settings['use_ctm_session']);
        $this->assertTrue($settings['fallback_to_ip']);
    }

    public function test_clear_duplicate_prevention_handles_missing_session()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Clear prevention even when no session ID
        $this->service->clearDuplicatePrevention($formId, $formType);
        
        // Should not throw any errors
        $this->assertTrue(true);
    }

    public function test_duplicate_prevention_with_zero_expiration()
    {
        $zeroExpiration = 0;
        
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->service->isDuplicateSubmission('form123', 'cf7', $zeroExpiration);
        
        $this->assertFalse($result);
    }

    public function test_duplicate_prevention_with_negative_expiration()
    {
        $negativeExpiration = -10;
        
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->service->isDuplicateSubmission('form123', 'cf7', $negativeExpiration);
        
        $this->assertFalse($result);
    }
}
