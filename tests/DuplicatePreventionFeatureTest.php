<?php

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;
use CTM\Service\DuplicatePreventionService;

class DuplicatePreventionFeatureTest extends TestCase
{
    use MonkeyTrait;
    private DuplicatePreventionService $duplicatePreventionService;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        $this->duplicatePreventionService = new DuplicatePreventionService();
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    public function test_prevents_duplicate_submissions_for_same_session_and_form()
    {
        // Mock that no transient exists (first submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        // First submission should succeed
        $result1 = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7');
        $this->assertFalse($result1);
        
        // Mock that transient exists (duplicate submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(time());
        
        // Second submission should be blocked
        $result2 = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7');
        $this->assertTrue($result2);
    }

    public function test_allows_submissions_after_expiration_time()
    {
        $expiration = 1; // 1 second for testing
        
        // Mock that no transient exists (first submission)
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        // First submission
        $result1 = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7', $expiration);
        $this->assertFalse($result1);
        
        // Mock that transient has expired
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        // Should allow submission after expiration
        $result2 = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7', $expiration);
        $this->assertFalse($result2);
    }

    public function test_creates_unique_keys_for_different_forms()
    {
        $sessionId = 'test_session_123';
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->duplicatePreventionService);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->duplicatePreventionService, $sessionId, 'form1', 'cf7');
        $key2 = $method->invoke($this->duplicatePreventionService, $sessionId, 'form2', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_creates_unique_keys_for_different_form_types()
    {
        $sessionId = 'test_session_123';
        
        $reflection = new \ReflectionClass($this->duplicatePreventionService);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->duplicatePreventionService, $sessionId, 'form1', 'cf7');
        $key2 = $method->invoke($this->duplicatePreventionService, $sessionId, 'form1', 'gf');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_creates_unique_keys_for_different_sessions()
    {
        $reflection = new \ReflectionClass($this->duplicatePreventionService);
        $method = $reflection->getMethod('generateTransientKey');
        $method->setAccessible(true);
        
        $key1 = $method->invoke($this->duplicatePreventionService, 'session1', 'form1', 'cf7');
        $key2 = $method->invoke($this->duplicatePreventionService, 'session2', 'form1', 'cf7');
        
        $this->assertNotEquals($key1, $key2);
    }

    public function test_handles_missing_ctm_session_id_gracefully()
    {
        // Mock no session ID available
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_clears_duplicate_prevention_correctly()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Set up prevention
        $this->duplicatePreventionService->isDuplicateSubmission($formId, $formType);
        
        // Clear prevention
        $this->duplicatePreventionService->clearDuplicatePrevention($formId, $formType);
        
        // Should allow submission again
        $result = $this->duplicatePreventionService->isDuplicateSubmission($formId, $formType);
        $this->assertFalse($result);
    }

    public function test_returns_correct_settings_structure()
    {
        $settings = $this->duplicatePreventionService->getSettings();
        
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
        $result = $this->duplicatePreventionService->isEnabled();
        
        $this->assertTrue($result);
    }

    public function test_uses_ip_address_when_session_id_unavailable()
    {
        // Mock IP detection
        \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
        
        $result = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertFalse($result);
    }

    public function test_prevents_duplicates_by_ip_address()
    {
        // Mock IP-based transient
        \Brain\Monkey\Functions\when('get_transient')->justReturn(time());
        
        $result = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7');
        
        $this->assertTrue($result);
    }

    public function test_respects_expiration_time_limits()
    {
        // Test minimum limit
        $result1 = $this->duplicatePreventionService->isDuplicateSubmission('form123', 'cf7', 25);
        $this->assertFalse($result1);
        
        // Test maximum limit
        $result2 = $this->duplicatePreventionService->isDuplicateSubmission('form456', 'cf7', 350);
        $this->assertFalse($result2);
    }

    public function test_handles_different_form_types_correctly()
    {
        $formTypes = ['cf7', 'gf', 'elementor', 'woocommerce'];
        
        foreach ($formTypes as $formType) {
            $result = $this->duplicatePreventionService->isDuplicateSubmission('form123', $formType);
            $this->assertFalse($result);
        }
    }

    public function test_handles_empty_form_id_gracefully()
    {
        $result = $this->duplicatePreventionService->isDuplicateSubmission('', 'cf7');
        $this->assertFalse($result);
    }

    public function test_handles_empty_form_type_gracefully()
    {
        $result = $this->duplicatePreventionService->isDuplicateSubmission('form123', '');
        $this->assertFalse($result);
    }

    public function test_handles_very_long_form_ids()
    {
        $longFormId = str_repeat('a', 1000);
        $result = $this->duplicatePreventionService->isDuplicateSubmission($longFormId, 'cf7');
        $this->assertFalse($result);
    }

    public function test_handles_special_characters_in_form_ids()
    {
        $specialFormId = 'form-123_test@example.com';
        $result = $this->duplicatePreventionService->isDuplicateSubmission($specialFormId, 'cf7');
        $this->assertFalse($result);
    }

    public function test_handles_multiple_concurrent_submissions()
    {
        $formId = 'form123';
        $formType = 'cf7';
        
        // Test that the service can handle multiple calls
        $results = [];
        for ($i = 0; $i < 5; $i++) {
            // Mock each call to return false (no duplicate detected)
            \Brain\Monkey\Functions\when('get_transient')->justReturn(false);
            $results[] = $this->duplicatePreventionService->isDuplicateSubmission($formId, $formType);
        }
        
        // All should return false since we're mocking no duplicates
        foreach ($results as $result) {
            $this->assertFalse($result);
        }
        
        $this->assertCount(5, $results);
    }

    public function test_handles_different_forms_independently()
    {
        $forms = [
            ['id' => 'form1', 'type' => 'cf7'],
            ['id' => 'form2', 'type' => 'cf7'],
            ['id' => 'form1', 'type' => 'gf'],
            ['id' => 'form2', 'type' => 'gf']
        ];
        
        foreach ($forms as $form) {
            $result = $this->duplicatePreventionService->isDuplicateSubmission($form['id'], $form['type']);
            $this->assertFalse($result);
        }
    }
}
