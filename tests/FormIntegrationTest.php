<?php

use PHPUnit\Framework\TestCase;
use CTM\Service\DuplicatePreventionService;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class FormIntegrationTest extends TestCase
{
    use MonkeyTrait;
    
    private DuplicatePreventionService $duplicatePrevention;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        
        $this->duplicatePrevention = new DuplicatePreventionService();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_prevents_duplicate_submissions_for_same_session_and_form()
    {
        $formId = 'test_form_123';
        $formType = 'cf7';
        
        // First submission should succeed
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result1 = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
        $this->assertFalse($result1);
        
        // Second submission with same parameters should be blocked
        \Brain\Monkey\Functions\when('get_transient')->justReturn(time());
        $result2 = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
        $this->assertTrue($result2);
    }

    public function test_allows_submissions_after_expiration_time()
    {
        $formId = 'test_form_456';
        $formType = 'gf';
        $expiration = 1; // 1 second
        
        // First submission
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result1 = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType, $expiration);
        $this->assertFalse($result1);
        
        // Simulate time passing and transient expiring
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result2 = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType, $expiration);
        $this->assertFalse($result2);
    }

    public function test_creates_unique_keys_for_different_forms()
    {
        $sessionId = 'test_session_123';
        
        $reflection = new \ReflectionClass($this->duplicatePrevention);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->duplicatePrevention, $sessionId, 'form1', 'cf7');
        $key2 = $method->invoke($this->duplicatePrevention, $sessionId, 'form2', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
        $this->assertStringStartsWith('ctm_duplicate_', $key1);
        $this->assertStringStartsWith('ctm_duplicate_', $key2);
    }

    public function test_creates_unique_keys_for_different_form_types()
    {
        $sessionId = 'test_session_123';
        
        $reflection = new \ReflectionClass($this->duplicatePrevention);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->duplicatePrevention, $sessionId, 'form1', 'cf7');
        $key2 = $method->invoke($this->duplicatePrevention, $sessionId, 'form1', 'gf');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_creates_unique_keys_for_different_sessions()
    {
        $reflection = new \ReflectionClass($this->duplicatePrevention);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->duplicatePrevention, 'session1', 'form1', 'cf7');
        $key2 = $method->invoke($this->duplicatePrevention, 'session2', 'form1', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_handles_missing_ctm_session_id_gracefully()
    {
        // Mock no session ID available
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $this->duplicatePrevention->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_clears_duplicate_prevention_correctly()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Set up prevention
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
        
        // Clear prevention
        $this->duplicatePrevention->clearDuplicatePrevention($formId, $formType);
        
        // Should allow submission again
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
        
        $this->assertFalse($result);
    }

    public function test_returns_correct_settings_structure()
    {
        $settings = $this->duplicatePrevention->getSettings();
        
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
        $result = $this->duplicatePrevention->isEnabled();
        
        $this->assertTrue($result);
    }

    public function test_uses_ip_address_when_session_id_unavailable()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Mock IP-based fallback
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
        
        $this->assertFalse($result);
    }

    public function test_prevents_duplicates_by_ip_address()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Mock IP-based duplicate detection
        \Brain\Monkey\Functions\when('get_transient')->justReturn(time());
        
        $result = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
        
        $this->assertTrue($result);
    }

    public function test_respects_expiration_time_limits()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Test minimum limit
        $minExpiration = 25;
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType, $minExpiration);
        $this->assertFalse($result);
        
        // Test maximum limit
        $maxExpiration = 350;
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType, $maxExpiration);
        $this->assertFalse($result);
    }

    public function test_handles_different_form_types_correctly()
    {
        $formTypes = ['cf7', 'gf', 'elementor', 'woocommerce', 'custom'];
        
        foreach ($formTypes as $formType) {
            \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
            $result = $this->duplicatePrevention->isDuplicateSubmission('form123', $formType);
            
            $this->assertFalse($result);
        }
    }

    public function test_handles_empty_form_id_gracefully()
    {
        // Test with empty form ID
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission('', 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_handles_empty_form_type_gracefully()
    {
        // Test with empty form type
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission('form123', '');
        
        $this->assertFalse($result);
    }

    public function test_handles_very_long_form_ids()
    {
        $longFormId = str_repeat('a', 1000);
        
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission($longFormId, 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_handles_special_characters_in_form_ids()
    {
        $specialFormId = 'form-123_test@example.com';
        
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result = $this->duplicatePrevention->isDuplicateSubmission($specialFormId, 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_handles_multiple_concurrent_submissions()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Simulate multiple concurrent submissions
        for ($i = 0; $i < 5; $i++) {
            \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
            $result = $this->duplicatePrevention->isDuplicateSubmission($formId, $formType);
            $this->assertFalse($result);
        }
    }

    public function test_handles_different_forms_independently()
    {
        $form1 = 'form_123';
        $form2 = 'form_456';
        $formType = 'cf7';
        
        // Submit to first form
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result1 = $this->duplicatePrevention->isDuplicateSubmission($form1, $formType);
        $this->assertFalse($result1);
        
        // Submit to second form (should be independent)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        $result2 = $this->duplicatePrevention->isDuplicateSubmission($form2, $formType);
        $this->assertFalse($result2);
    }
}
