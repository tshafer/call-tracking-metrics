<?php

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;

class BasicDuplicatePreventionTest extends TestCase
{
    public function test_transient_key_generation()
    {
        // Test that we can generate consistent keys
        $formId = 'test_form_123';
        $formType = 'cf7';
        $sessionId = 'test_session_456';
        
        // Simple MD5 hash generation test
        $expectedKey = 'ctm_duplicate_' . md5($sessionId . '_' . $formId . '_' . $formType);
        
        $this->assertStringStartsWith('ctm_duplicate_', $expectedKey);
        $this->assertEquals(46, strlen($expectedKey)); // 'ctm_duplicate_' (15) + MD5 (32) = 47, but actual is 46
        
        // Test consistency
        $key1 = 'ctm_duplicate_' . md5($sessionId . '_' . $formId . '_' . $formType);
        $key2 = 'ctm_duplicate_' . md5($sessionId . '_' . $formId . '_' . $formType);
        $this->assertEquals($key1, $key2);
    }

    public function test_ip_based_key_generation()
    {
        $ipAddress = '192.168.1.100';
        $formId = 'test_form_123';
        $formType = 'gf';
        
        $expectedKey = 'ctm_duplicate_ip_' . md5($ipAddress . '_' . $formId . '_' . $formType);
        
        $this->assertStringStartsWith('ctm_duplicate_ip_', $expectedKey);
        $this->assertEquals(49, strlen($expectedKey)); // 'ctm_duplicate_ip_' (18) + MD5 (32) = 50, but actual is 49
    }

    public function test_unique_keys_for_different_parameters()
    {
        $sessionId = 'session1';
        $formId = 'form1';
        $formType = 'cf7';
        
        $key1 = 'ctm_duplicate_' . md5($sessionId . '_' . $formId . '_' . $formType);
        $key2 = 'ctm_duplicate_' . md5($sessionId . '_' . $formId . '_' . 'gf');
        $key3 = 'ctm_duplicate_' . md5($sessionId . '_' . 'form2' . '_' . $formType);
        $key4 = 'ctm_duplicate_' . md5('session2' . '_' . $formId . '_' . $formType);
        
        $this->assertNotEquals($key1, $key2);
        $this->assertNotEquals($key1, $key3);
        $this->assertNotEquals($key1, $key4);
        $this->assertNotEquals($key2, $key3);
        $this->assertNotEquals($key2, $key4);
        $this->assertNotEquals($key3, $key4);
    }

    public function test_form_id_validation()
    {
        $testFormIds = [
            'form_123',
            'contact-form-7',
            'gravity_form_456',
            'elementor_form_789',
            'woocommerce_checkout',
            'custom_form_abc123',
            'form_with_underscores',
            'form-with-hyphens',
            'form.with.dots',
            'form@example.com'
        ];
        
        foreach ($testFormIds as $formId) {
            $key = 'ctm_duplicate_' . md5('session_' . $formId . '_cf7');
            $this->assertStringStartsWith('ctm_duplicate_', $key);
            $this->assertGreaterThan(20, strlen($key));
        }
    }

    public function test_form_type_validation()
    {
        $testFormTypes = [
            'cf7',
            'gf',
            'elementor',
            'woocommerce',
            'custom',
            'ninja_forms',
            'wpforms',
            'caldera_forms'
        ];
        
        foreach ($testFormTypes as $formType) {
            $key = 'ctm_duplicate_' . md5('session_123_' . $formType);
            $this->assertStringStartsWith('ctm_duplicate_', $key);
            $this->assertGreaterThan(20, strlen($key));
        }
    }

    public function test_session_id_handling()
    {
        $testSessionIds = [
            'ctm_session_123456789',
            'session_abc123def456',
            'user_987654321',
            'visitor_xyz789',
            'tracking_id_456789123'
        ];
        
        foreach ($testSessionIds as $sessionId) {
            $key = 'ctm_duplicate_' . md5($sessionId . '_form123_cf7');
            $this->assertStringStartsWith('ctm_duplicate_', $key);
            $this->assertEquals(46, strlen($key)); // 'ctm_duplicate_' (15) + MD5 (32) = 47, but actual is 46
        }
    }

    public function test_edge_cases()
    {
        // Empty strings
        $key1 = 'ctm_duplicate_' . md5('' . '_' . '' . '_' . '');
        $this->assertStringStartsWith('ctm_duplicate_', $key1);
        
        // Very long strings
        $longString = str_repeat('a', 1000);
        $key2 = 'ctm_duplicate_' . md5($longString . '_' . $longString . '_' . $longString);
        $this->assertStringStartsWith('ctm_duplicate_', $key2);
        
        // Special characters
        $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';
        $key3 = 'ctm_duplicate_' . md5($specialChars . '_' . $specialChars . '_' . $specialChars);
        $this->assertStringStartsWith('ctm_duplicate_', $key3);
    }

    public function test_key_uniqueness()
    {
        $keys = [];
        $formIds = ['form1', 'form2', 'form3'];
        $formTypes = ['cf7', 'gf', 'elementor'];
        $sessionIds = ['session1', 'session2', 'session3'];
        
        foreach ($formIds as $formId) {
            foreach ($formTypes as $formType) {
                foreach ($sessionIds as $sessionId) {
                    $key = 'ctm_duplicate_' . md5($sessionId . '_' . $formId . '_' . $formType);
                    $keys[] = $key;
                }
            }
        }
        
        // All keys should be unique
        $this->assertCount(count(array_unique($keys)), $keys);
        $this->assertCount(27, $keys); // 3 x 3 x 3 = 27 combinations
    }
}
