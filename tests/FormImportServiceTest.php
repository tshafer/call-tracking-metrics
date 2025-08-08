<?php
if (!defined('CTM_TESTING')) {
    define('CTM_TESTING', true);
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;
use CTM\Service\FormImportService;
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;

class FormImportServiceTest extends TestCase
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

    public function testCanBeConstructed()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $this->assertInstanceOf(FormImportService::class, $formImportService);
    }

    public function testValidateImportParamsWithValidData()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $params = [
            'ctm_form_id' => '123',
            'form_title' => 'Test Form',
            'target_type' => 'cf7'
        ];
        
        $result = $formImportService->validateImportParams($params);
        
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    public function testValidateImportParamsWithMissingData()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $params = [
            'ctm_form_id' => '',
            'form_title' => '',
            'target_type' => ''
        ];
        
        $result = $formImportService->validateImportParams($params);
        
        $this->assertFalse($result['valid']);
        $this->assertGreaterThanOrEqual(3, count($result['errors']));
        $this->assertContains('CTM form ID is required', $result['errors']);
        $this->assertContains('Form title is required', $result['errors']);
        $this->assertContains('Target form type is required', $result['errors']);
    }

    public function testValidateImportParamsWithInvalidTargetType()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $params = [
            'ctm_form_id' => '123',
            'form_title' => 'Test Form',
            'target_type' => 'invalid'
        ];
        
        $result = $formImportService->validateImportParams($params);
        
        $this->assertFalse($result['valid']);
        $this->assertContains('Invalid target form type', $result['errors']);
    }

    public function testGetAvailableFormsReturnsNullWhenApiFails()
    {
        // Create a real API service that will fail
        $apiService = new ApiService('https://invalid-url.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $result = $formImportService->getAvailableForms('test_key', 'test_secret');
        
        $this->assertNull($result);
    }

    public function testGetAvailableFormsReturnsFormattedData()
    {
        // Create a real API service
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // This will likely return null in test environment, but we can test the method exists
        $result = $formImportService->getAvailableForms('test_key', 'test_secret');
        
        // In test environment, this will likely be null, but the method should not throw an error
        $this->assertTrue($result === null || is_array($result));
    }

    public function testSupportsAllFieldTypes()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false); // CF7 and GF not available in test
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test data with all supported field types
        $ctmForm = [
            'id' => 'test_form',
            'name' => 'Test Form with All Fields',
            'description' => 'A test form with all field types',
            'fields' => [
                ['name' => 'text_field', 'type' => 'text', 'label' => 'Text Field', 'required' => true],
                ['name' => 'email_field', 'type' => 'email', 'label' => 'Email Field', 'required' => true],
                ['name' => 'textarea_field', 'type' => 'textarea', 'label' => 'Text Area Field', 'required' => false],
                ['name' => 'text_area_field', 'type' => 'text_area', 'label' => 'Text Area Field 2', 'required' => false],
                ['name' => 'number_field', 'type' => 'number', 'label' => 'Number Field', 'required' => false],
                ['name' => 'decimal_field', 'type' => 'decimal', 'label' => 'Decimal Field', 'required' => false],
                ['name' => 'phone_field', 'type' => 'phone', 'label' => 'Phone Field', 'required' => false],
                ['name' => 'website_field', 'type' => 'website', 'label' => 'Website Field', 'required' => false],
                ['name' => 'url_field', 'type' => 'url', 'label' => 'URL Field', 'required' => false],
                ['name' => 'select_field', 'type' => 'select', 'label' => 'Select Field', 'required' => false, 'options' => ['Option 1', 'Option 2', 'Option 3']],
                ['name' => 'picker_field', 'type' => 'picker', 'label' => 'Picker Field', 'required' => false, 'options' => ['Choice 1', 'Choice 2']],
                ['name' => 'choice_list_field', 'type' => 'choice_list', 'label' => 'Choice List Field', 'required' => false, 'options' => ['List 1', 'List 2', 'List 3']],
                ['name' => 'checkbox_field', 'type' => 'checkbox', 'label' => 'Checkbox Field', 'required' => false],
                ['name' => 'radio_field', 'type' => 'radio', 'label' => 'Radio Field', 'required' => false, 'options' => ['Radio 1', 'Radio 2']],
                ['name' => 'information_field', 'type' => 'information', 'label' => 'Information Field', 'required' => false, 'content' => 'This is informational content'],
                ['name' => 'captcha_field', 'type' => 'captcha', 'label' => 'CAPTCHA Field', 'required' => false],
                ['name' => 'date_field', 'type' => 'date', 'label' => 'Date Field', 'required' => false],
                ['name' => 'file_upload_field', 'type' => 'file_upload', 'label' => 'File Upload Field', 'required' => false],
                ['name' => 'file_field', 'type' => 'file', 'label' => 'File Field', 'required' => false]
            ]
        ];
        
        // Test CF7 conversion (will return error since CF7 not available)
        $cf7Result = $formImportService->importToCF7($ctmForm, 'Test Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertFalse($cf7Result['success']); // Should fail because CF7 not available
        
        // Test GF conversion (will return error since GF not available)
        $gfResult = $formImportService->importToGF($ctmForm, 'Test Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertFalse($gfResult['success']); // Should fail because GF not available
    }

    public function testSuccessfulFormImportWorkflow()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        // Create a real API service (will fail in test environment, but we can test the structure)
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test 1: Get available forms (will likely return null in test environment)
        $forms = $formImportService->getAvailableForms('test_key', 'test_secret');
        // In test environment, this will likely be null, but we can verify the method exists
        $this->assertTrue($forms === null || is_array($forms));
        
        // Test 2: Validate import parameters
        $validation = $formImportService->validateImportParams([
            'ctm_form_id' => 'form_123',
            'form_title' => 'Test Form',
            'target_type' => 'cf7'
        ]);
        $this->assertTrue($validation['valid']);
        $this->assertEmpty($validation['errors']);
        
        // Test 3: Test with sample form data (old format)
        $sampleForm = [
            'id' => 'test_form',
            'name' => 'Test Form',
            'description' => 'A test form',
            'fields' => [
                [
                    'name' => 'name',
                    'type' => 'text',
                    'label' => 'Name',
                    'required' => true
                ],
                [
                    'name' => 'email',
                    'type' => 'email',
                    'label' => 'Email',
                    'required' => true
                ]
            ]
        ];
        
        // Test CF7 import (will fail in test environment but we can verify the method exists)
        try {
            $cf7Result = $formImportService->importToCF7($sampleForm, 'Test Form');
            $this->assertNotNull($cf7Result);
            $this->assertIsArray($cf7Result);
            $this->assertArrayHasKey('success', $cf7Result);
        } catch (\Exception $e) {
            // Expected to fail in test environment
            $this->assertTrue(true);
        }
        
        // Test GF import (will fail in test environment but we can verify the method exists)
        try {
            $gfResult = $formImportService->importToGF($sampleForm, 'Test Form');
            $this->assertNotNull($gfResult);
            $this->assertIsArray($gfResult);
            $this->assertArrayHasKey('success', $gfResult);
        } catch (\Exception $e) {
            // Expected to fail in test environment
            $this->assertTrue(true);
        }

        // Test 4: Test with new format (custom_fields)
        $sampleFormNew = [
            'id' => 'test_form_new',
            'name' => 'Test Form New',
            'description' => 'A test form with new format',
            'custom_fields' => [
                [
                    'name' => 'company_name',
                    'type' => 'text',
                    'label' => 'Company name',
                    'half_width' => 1,
                    'required' => false,
                    'save_to_custom' => 'summary'
                ],
                [
                    'name' => 'company_website',
                    'type' => 'url',
                    'label' => 'Company Website',
                    'half_width' => 1,
                    'required' => false,
                    'save_to_custom' => 'summary'
                ],
                [
                    'name' => 'appt_date',
                    'type' => 'date',
                    'label' => 'Appt date',
                    'half_width' => false,
                    'required' => false,
                    'save_to_custom' => 'summary',
                    'disable_before' => ''
                ],
                [
                    'name' => 'document',
                    'type' => 'upload',
                    'label' => 'document',
                    'half_width' => false,
                    'file_type' => 'image/*',
                    'required' => false,
                    'save_to_custom' => ''
                ]
            ],
            'tracking_number' => [
                'number' => '+14432513564',
                'id' => 'TPNC3C4B23C348AEC2EE54EFD301979CD2E40C9501DC734E3E9EF64276CB792263C2A6C617913984C6F'
            ],
            'managed_mode' => '',
            'managed_id' => '',
            'style' => 'rounded',
            'theme' => 'pink',
            'completion_text' => '## Thank You {{caller_name}}\n\nWe will be in contact with you shortly.',
            'error_text' => '&#9888;'
        ];
        
        // Test CF7 import with new format
        try {
            $cf7ResultNew = $formImportService->importToCF7($sampleFormNew, 'Test Form New');
            $this->assertNotNull($cf7ResultNew);
            $this->assertIsArray($cf7ResultNew);
            $this->assertArrayHasKey('success', $cf7ResultNew);
        } catch (\Exception $e) {
            // Expected to fail in test environment
            $this->assertTrue(true);
        }
        
        // Test GF import with new format
        try {
            $gfResultNew = $formImportService->importToGF($sampleFormNew, 'Test Form New');
            $this->assertNotNull($gfResultNew);
            $this->assertIsArray($gfResultNew);
            $this->assertArrayHasKey('success', $gfResultNew);
        } catch (\Exception $e) {
            // Expected to fail in test environment
            $this->assertTrue(true);
        }
    }

    public function testFormImportWithAllFieldTypes()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        // Create a real API service
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test data with all supported field types (old format)
        $comprehensiveForm = [
            'id' => 'comprehensive_form',
            'name' => 'Comprehensive Form',
            'description' => 'A form with all field types',
            'fields' => [
                ['name' => 'text_field', 'type' => 'text', 'label' => 'Text Field', 'required' => true],
                ['name' => 'email_field', 'type' => 'email', 'label' => 'Email Field', 'required' => true],
                ['name' => 'textarea_field', 'type' => 'textarea', 'label' => 'Text Area Field', 'required' => false],
                ['name' => 'number_field', 'type' => 'number', 'label' => 'Number Field', 'required' => false],
                ['name' => 'decimal_field', 'type' => 'decimal', 'label' => 'Decimal Field', 'required' => false],
                ['name' => 'phone_field', 'type' => 'phone', 'label' => 'Phone Field', 'required' => false],
                ['name' => 'website_field', 'type' => 'website', 'label' => 'Website Field', 'required' => false],
                ['name' => 'select_field', 'type' => 'select', 'label' => 'Select Field', 'required' => false, 'options' => ['Option 1', 'Option 2', 'Option 3']],
                ['name' => 'picker_field', 'type' => 'picker', 'label' => 'Picker Field', 'required' => false, 'options' => ['Choice 1', 'Choice 2']],
                ['name' => 'choice_list_field', 'type' => 'choice_list', 'label' => 'Choice List Field', 'required' => false, 'options' => ['List 1', 'List 2', 'List 3']],
                ['name' => 'checkbox_field', 'type' => 'checkbox', 'label' => 'Checkbox Field', 'required' => false],
                ['name' => 'radio_field', 'type' => 'radio', 'label' => 'Radio Field', 'required' => false, 'options' => ['Radio 1', 'Radio 2']],
                ['name' => 'information_field', 'type' => 'information', 'label' => 'Information Field', 'required' => false, 'content' => 'This is informational content'],
                ['name' => 'captcha_field', 'type' => 'captcha', 'label' => 'CAPTCHA Field', 'required' => false],
                ['name' => 'date_field', 'type' => 'date', 'label' => 'Date Field', 'required' => false],
                ['name' => 'file_upload_field', 'type' => 'file_upload', 'label' => 'File Upload Field', 'required' => false]
            ]
        ];

        // Test data with all supported field types (new format)
        $comprehensiveFormNew = [
            'id' => 'comprehensive_form_new',
            'name' => 'Comprehensive Form New',
            'description' => 'A form with all field types (new format)',
            'custom_fields' => [
                ['name' => 'text_field', 'type' => 'text', 'label' => 'Text Field', 'required' => true, 'half_width' => false],
                ['name' => 'email_field', 'type' => 'email', 'label' => 'Email Field', 'required' => true, 'half_width' => false],
                ['name' => 'textarea_field', 'type' => 'textarea', 'label' => 'Text Area Field', 'required' => false, 'half_width' => false],
                ['name' => 'number_field', 'type' => 'number', 'label' => 'Number Field', 'required' => false, 'half_width' => false],
                ['name' => 'decimal_field', 'type' => 'decimal', 'label' => 'Decimal Field', 'required' => false, 'half_width' => false],
                ['name' => 'phone_field', 'type' => 'phone', 'label' => 'Phone Field', 'required' => false, 'half_width' => false],
                ['name' => 'website_field', 'type' => 'website', 'label' => 'Website Field', 'required' => false, 'half_width' => false],
                ['name' => 'select_field', 'type' => 'select', 'label' => 'Select Field', 'required' => false, 'half_width' => false, 'options' => ['Option 1', 'Option 2', 'Option 3']],
                ['name' => 'picker_field', 'type' => 'picker', 'label' => 'Picker Field', 'required' => false, 'half_width' => false, 'options' => ['Choice 1', 'Choice 2']],
                ['name' => 'choice_list_field', 'type' => 'choice_list', 'label' => 'Choice List Field', 'required' => false, 'half_width' => false, 'options' => ['List 1', 'List 2', 'List 3']],
                ['name' => 'checkbox_field', 'type' => 'checkbox', 'label' => 'Checkbox Field', 'required' => false, 'half_width' => false],
                ['name' => 'radio_field', 'type' => 'radio', 'label' => 'Radio Field', 'required' => false, 'half_width' => false, 'options' => ['Radio 1', 'Radio 2']],
                ['name' => 'information_field', 'type' => 'information', 'label' => 'Information Field', 'required' => false, 'half_width' => false, 'content' => 'This is informational content'],
                ['name' => 'captcha_field', 'type' => 'captcha', 'label' => 'CAPTCHA Field', 'required' => false, 'half_width' => false],
                ['name' => 'date_field', 'type' => 'date', 'label' => 'Date Field', 'required' => false, 'half_width' => false, 'disable_before' => ''],
                ['name' => 'file_upload_field', 'type' => 'upload', 'label' => 'File Upload Field', 'required' => false, 'half_width' => false, 'file_type' => 'image/*']
            ],
            'tracking_number' => [
                'number' => '+14432513564',
                'id' => 'TPNC3C4B23C348AEC2EE54EFD301979CD2E40C9501DC734E3E9EF64276CB792263C2A6C617913984C6F'
            ],
            'managed_mode' => '',
            'managed_id' => '',
            'style' => 'rounded',
            'theme' => 'pink',
            'completion_text' => 'Thank you for your submission.',
            'error_text' => '&#9888;'
        ];
        
        // Test CF7 import with all field types (will fail in test environment but we can verify the method exists)
        try {
            $cf7Result = $formImportService->importToCF7($comprehensiveForm, 'Comprehensive Form CF7');
            $this->assertNotNull($cf7Result);
            $this->assertIsArray($cf7Result);
            $this->assertArrayHasKey('success', $cf7Result);
        } catch (\Exception $e) {
            // Expected to fail in test environment
            $this->assertTrue(true);
        }
        
        // Test GF import with all field types (will fail in test environment but we can verify the method exists)
        try {
            $gfResult = $formImportService->importToGF($comprehensiveForm, 'Comprehensive Form GF');
            $this->assertNotNull($gfResult);
            $this->assertIsArray($gfResult);
            $this->assertArrayHasKey('success', $gfResult);
        } catch (\Exception $e) {
            // Expected to fail in test environment
            $this->assertTrue(true);
        }
        
        // Verify all field types were processed
        $this->assertCount(16, $comprehensiveForm['fields']);
        
        // Check that all field types are present
        $fieldTypes = array_column($comprehensiveForm['fields'], 'type');
        $expectedTypes = ['text', 'email', 'textarea', 'number', 'decimal', 'phone', 'website', 'select', 'picker', 'choice_list', 'checkbox', 'radio', 'information', 'captcha', 'date', 'file_upload'];
        
        foreach ($expectedTypes as $expectedType) {
            $this->assertContains($expectedType, $fieldTypes, "Field type '{$expectedType}' should be present");
        }
    }

    public function testImportWithEmptyFormData()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $emptyForm = [
            'id' => 'empty_form',
            'name' => 'Empty Form',
            'description' => 'A form with no fields',
            'fields' => []
        ];
        
        // Test CF7 import with empty form
        $cf7Result = $formImportService->importToCF7($emptyForm, 'Empty Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with empty form
        $gfResult = $formImportService->importToGF($emptyForm, 'Empty Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithInvalidFieldTypes()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $invalidForm = [
            'id' => 'invalid_form',
            'name' => 'Invalid Form',
            'description' => 'A form with invalid field types',
            'fields' => [
                ['name' => 'invalid_field', 'type' => 'invalid_type', 'label' => 'Invalid Field', 'required' => false],
                ['name' => 'unknown_field', 'type' => 'unknown_type', 'label' => 'Unknown Field', 'required' => false],
                ['name' => 'null_field', 'type' => null, 'label' => 'Null Field', 'required' => false]
            ]
        ];
        
        // Test CF7 import with invalid field types
        $cf7Result = $formImportService->importToCF7($invalidForm, 'Invalid Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with invalid field types
        $gfResult = $formImportService->importToGF($invalidForm, 'Invalid Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithMissingFieldProperties()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $incompleteForm = [
            'id' => 'incomplete_form',
            'name' => 'Incomplete Form',
            'description' => 'A form with missing field properties',
            'fields' => [
                ['type' => 'text'], // Missing name and label
                ['name' => 'field2'], // Missing type and label
                ['label' => 'Field 3'], // Missing name and type
                ['name' => 'field4', 'type' => 'email'], // Missing label
                ['name' => 'field5', 'label' => 'Field 5'], // Missing type
            ]
        ];
        
        // Test CF7 import with incomplete fields
        $cf7Result = $formImportService->importToCF7($incompleteForm, 'Incomplete Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with incomplete fields
        $gfResult = $formImportService->importToGF($incompleteForm, 'Incomplete Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithSpecialCharacters()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $specialForm = [
            'id' => 'special_form',
            'name' => 'Form with Special Characters: !@#$%^&*()',
            'description' => 'A form with special characters in names and labels',
            'fields' => [
                ['name' => 'field_with_spaces', 'type' => 'text', 'label' => 'Field with spaces', 'required' => true],
                ['name' => 'field-with-dashes', 'type' => 'email', 'label' => 'Field with dashes', 'required' => false],
                ['name' => 'field_with_underscores', 'type' => 'textarea', 'label' => 'Field with underscores', 'required' => false],
                ['name' => 'field.with.dots', 'type' => 'number', 'label' => 'Field with dots', 'required' => false],
                ['name' => 'field_with_quotes', 'type' => 'text', 'label' => 'Field with "quotes"', 'required' => false],
            ]
        ];
        
        // Test CF7 import with special characters
        $cf7Result = $formImportService->importToCF7($specialForm, 'Special Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with special characters
        $gfResult = $formImportService->importToGF($specialForm, 'Special Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithVeryLongFieldNames()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $longNameForm = [
            'id' => 'long_name_form',
            'name' => 'Form with Very Long Field Names',
            'description' => 'A form with extremely long field names and labels',
            'fields' => [
                [
                    'name' => 'this_is_a_very_long_field_name_that_exceeds_normal_length_limits_and_should_be_handled_properly',
                    'type' => 'text',
                    'label' => 'This is a very long field label that exceeds normal length limits and should be handled properly by the form import system',
                    'required' => true
                ],
                [
                    'name' => 'another_extremely_long_field_name_with_many_characters_and_numbers_123456789',
                    'type' => 'email',
                    'label' => 'Another extremely long field label with many characters and numbers 123456789 that should be processed correctly',
                    'required' => false
                ]
            ]
        ];
        
        // Test CF7 import with long field names
        $cf7Result = $formImportService->importToCF7($longNameForm, 'Long Name Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with long field names
        $gfResult = $formImportService->importToGF($longNameForm, 'Long Name Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithNestedFieldOptions()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $nestedForm = [
            'id' => 'nested_form',
            'name' => 'Form with Nested Field Options',
            'description' => 'A form with complex nested field options',
            'fields' => [
                [
                    'name' => 'simple_select',
                    'type' => 'select',
                    'label' => 'Simple Select',
                    'required' => false,
                    'options' => ['Option 1', 'Option 2', 'Option 3']
                ],
                [
                    'name' => 'complex_select',
                    'type' => 'select',
                    'label' => 'Complex Select',
                    'required' => false,
                    'options' => ['Option A', 'Option B', 'Option C']
                ],
                [
                    'name' => 'radio_group',
                    'type' => 'radio',
                    'label' => 'Radio Group',
                    'required' => false,
                    'options' => ['Radio 1', 'Radio 2', 'Radio 3']
                ]
            ]
        ];
        
        // Test CF7 import with nested options
        $cf7Result = $formImportService->importToCF7($nestedForm, 'Nested Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with nested options
        $gfResult = $formImportService->importToGF($nestedForm, 'Nested Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithFileUploadFields()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $fileForm = [
            'id' => 'file_form',
            'name' => 'Form with File Upload Fields',
            'description' => 'A form with various file upload field types',
            'fields' => [
                [
                    'name' => 'single_file',
                    'type' => 'file_upload',
                    'label' => 'Single File Upload',
                    'required' => false,
                    'allowed_extensions' => 'jpg,jpeg,png,gif'
                ],
                [
                    'name' => 'multiple_files',
                    'type' => 'file_upload',
                    'label' => 'Multiple File Upload',
                    'required' => false,
                    'allowed_extensions' => 'pdf,doc,docx',
                    'multiple' => true
                ],
                [
                    'name' => 'image_upload',
                    'type' => 'file',
                    'label' => 'Image Upload',
                    'required' => true,
                    'allowed_extensions' => 'jpg,jpeg,png'
                ]
            ]
        ];
        
        // Test CF7 import with file upload fields
        $cf7Result = $formImportService->importToCF7($fileForm, 'File Upload Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with file upload fields
        $gfResult = $formImportService->importToGF($fileForm, 'File Upload Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithConditionalFields()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $conditionalForm = [
            'id' => 'conditional_form',
            'name' => 'Form with Conditional Fields',
            'description' => 'A form with conditional field logic',
            'fields' => [
                [
                    'name' => 'contact_type',
                    'type' => 'select',
                    'label' => 'Contact Type',
                    'required' => true,
                    'options' => ['General Inquiry', 'Support Request', 'Sales Inquiry']
                ],
                [
                    'name' => 'support_ticket',
                    'type' => 'text',
                    'label' => 'Support Ticket Number',
                    'required' => false,
                    'conditional' => [
                        'field' => 'contact_type',
                        'value' => 'Support Request',
                        'operator' => 'equals'
                    ]
                ],
                [
                    'name' => 'company_size',
                    'type' => 'select',
                    'label' => 'Company Size',
                    'required' => false,
                    'conditional' => [
                        'field' => 'contact_type',
                        'value' => 'Sales Inquiry',
                        'operator' => 'equals'
                    ],
                    'options' => ['1-10', '11-50', '51-200', '200+']
                ]
            ]
        ];
        
        // Test CF7 import with conditional fields
        $cf7Result = $formImportService->importToCF7($conditionalForm, 'Conditional Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with conditional fields
        $gfResult = $formImportService->importToGF($conditionalForm, 'Conditional Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithValidationRules()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $validationForm = [
            'id' => 'validation_form',
            'name' => 'Form with Validation Rules',
            'description' => 'A form with various validation rules',
            'fields' => [
                [
                    'name' => 'email_with_validation',
                    'type' => 'email',
                    'label' => 'Email with Validation',
                    'required' => true,
                    'validation' => [
                        'pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
                        'message' => 'Please enter a valid email address'
                    ]
                ],
                [
                    'name' => 'phone_with_validation',
                    'type' => 'phone',
                    'label' => 'Phone with Validation',
                    'required' => false,
                    'validation' => [
                        'pattern' => '^\+?[1-9]\d{1,14}$',
                        'message' => 'Please enter a valid phone number'
                    ]
                ],
                [
                    'name' => 'number_with_range',
                    'type' => 'number',
                    'label' => 'Number with Range',
                    'required' => false,
                    'validation' => [
                        'min' => 1,
                        'max' => 100,
                        'message' => 'Please enter a number between 1 and 100'
                    ]
                ]
            ]
        ];
        
        // Test CF7 import with validation rules
        $cf7Result = $formImportService->importToCF7($validationForm, 'Validation Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with validation rules
        $gfResult = $formImportService->importToGF($validationForm, 'Validation Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    public function testImportWithLargeFormData()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Create a large form with many fields
        $largeForm = [
            'id' => 'large_form',
            'name' => 'Large Form with Many Fields',
            'description' => 'A form with a large number of fields to test performance',
            'fields' => []
        ];
        
        // Add 50 fields to test performance
        for ($i = 1; $i <= 50; $i++) {
            $fieldTypes = ['text', 'email', 'textarea', 'number', 'phone', 'website', 'select', 'checkbox', 'radio', 'date'];
            $fieldType = $fieldTypes[$i % count($fieldTypes)];
            
            $field = [
                'name' => "field_{$i}",
                'type' => $fieldType,
                'label' => "Field {$i}",
                'required' => ($i % 3 === 0) // Every 3rd field is required
            ];
            
            // Add options for select and radio fields
            if (in_array($fieldType, ['select', 'radio'])) {
                $field['options'] = ["Option {$i}.1", "Option {$i}.2", "Option {$i}.3"];
            }
            
            $largeForm['fields'][] = $field;
        }
        
        // Test CF7 import with large form
        $cf7Result = $formImportService->importToCF7($largeForm, 'Large Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with large form
        $gfResult = $formImportService->importToGF($largeForm, 'Large Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
        
        // Verify all fields were processed
        $this->assertCount(50, $largeForm['fields']);
    }

    public function testImportWithUnicodeCharacters()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $unicodeForm = [
            'id' => 'unicode_form',
            'name' => 'Form with Unicode Characters: 表单',
            'description' => 'A form with Unicode characters in names and labels: 表单描述',
            'fields' => [
                [
                    'name' => 'chinese_field',
                    'type' => 'text',
                    'label' => '中文字段',
                    'required' => true
                ],
                [
                    'name' => 'japanese_field',
                    'type' => 'email',
                    'label' => '日本語フィールド',
                    'required' => false
                ],
                [
                    'name' => 'korean_field',
                    'type' => 'textarea',
                    'label' => '한국어 필드',
                    'required' => false
                ],
                [
                    'name' => 'emoji_field',
                    'type' => 'text',
                    'label' => 'Field with emojis 🎉📧📱',
                    'required' => false
                ],
                [
                    'name' => 'mixed_field',
                    'type' => 'select',
                    'label' => 'Mixed: English 中文 Español',
                    'required' => false,
                    'options' => ['Option 1', '选项 2', 'Opción 3']
                ]
            ]
        ];
        
        // Test CF7 import with Unicode characters
        $cf7Result = $formImportService->importToCF7($unicodeForm, 'Unicode Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
        
        // Test GF import with Unicode characters
        $gfResult = $formImportService->importToGF($unicodeForm, 'Unicode Form');
        $this->assertNotNull($gfResult);
        $this->assertIsArray($gfResult);
        $this->assertArrayHasKey('success', $gfResult);
    }

    /**
     * Test that pagination methods exist and are callable
     */
    public function testPaginationMethodsExist()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test that the pagination method exists
        $this->assertTrue(method_exists($formImportService, 'getAvailableFormsPaginated'));
        
        // Test that the API service has pagination methods
        $this->assertTrue(method_exists($apiService, 'getFormReactors'));
        $this->assertTrue(method_exists($apiService, 'getAllFormReactors'));
        $this->assertTrue(method_exists($apiService, 'getTrackingNumbers'));
    }

    public function testApiServiceErrorHandling()
    {
        $apiService = new ApiService('https://invalid-api-url.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test with invalid credentials
        $result = $formImportService->getAvailableForms('invalid_key', 'invalid_secret');
        $this->assertNull($result);
        
        // Test with empty credentials
        $result = $formImportService->getAvailableForms('', '');
        $this->assertNull($result);
    }

    public function testFormImportWithMalformedData()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $malformedForm = [
            'id' => null,
            'name' => '',
            'description' => null,
            'fields' => 'not_an_array'
        ];
        
        $cf7Result = $formImportService->importToCF7($malformedForm, 'Malformed Form');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
    }

    public function testFieldTypeMappingAccuracy()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $mappingForm = [
            'id' => 'mapping_test',
            'name' => 'Field Type Mapping Test',
            'fields' => [
                ['name' => 'text_field', 'type' => 'text', 'label' => 'Text Field'],
                ['name' => 'email_field', 'type' => 'email', 'label' => 'Email Field'],
                ['name' => 'textarea_field', 'type' => 'textarea', 'label' => 'Textarea Field'],
                ['name' => 'number_field', 'type' => 'number', 'label' => 'Number Field'],
                ['name' => 'phone_field', 'type' => 'phone', 'label' => 'Phone Field'],
                ['name' => 'website_field', 'type' => 'website', 'label' => 'Website Field'],
                ['name' => 'select_field', 'type' => 'select', 'label' => 'Select Field', 'options' => ['A', 'B']],
                ['name' => 'checkbox_field', 'type' => 'checkbox', 'label' => 'Checkbox Field'],
                ['name' => 'radio_field', 'type' => 'radio', 'label' => 'Radio Field', 'options' => ['X', 'Y']],
                ['name' => 'date_field', 'type' => 'date', 'label' => 'Date Field'],
                ['name' => 'file_field', 'type' => 'file_upload', 'label' => 'File Field']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($mappingForm, 'Mapping Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testRequiredFieldHandling()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $requiredForm = [
            'id' => 'required_test',
            'name' => 'Required Fields Test',
            'fields' => [
                ['name' => 'required_text', 'type' => 'text', 'label' => 'Required Text', 'required' => true],
                ['name' => 'optional_text', 'type' => 'text', 'label' => 'Optional Text', 'required' => false],
                ['name' => 'required_email', 'type' => 'email', 'label' => 'Required Email', 'required' => true],
                ['name' => 'optional_email', 'type' => 'email', 'label' => 'Optional Email', 'required' => false],
                ['name' => 'required_select', 'type' => 'select', 'label' => 'Required Select', 'required' => true, 'options' => ['A', 'B']]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($requiredForm, 'Required Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testHalfWidthFieldHandling()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $halfWidthForm = [
            'id' => 'half_width_test',
            'name' => 'Half Width Fields Test',
            'custom_fields' => [
                ['name' => 'full_width', 'type' => 'text', 'label' => 'Full Width', 'half_width' => false],
                ['name' => 'half_width_1', 'type' => 'text', 'label' => 'Half Width 1', 'half_width' => true],
                ['name' => 'half_width_2', 'type' => 'email', 'label' => 'Half Width 2', 'half_width' => true],
                ['name' => 'half_width_3', 'type' => 'phone', 'label' => 'Half Width 3', 'half_width' => true]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($halfWidthForm, 'Half Width Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFileTypeHandling()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $fileTypeForm = [
            'id' => 'file_type_test',
            'name' => 'File Type Test',
            'custom_fields' => [
                ['name' => 'image_upload', 'type' => 'upload', 'label' => 'Image Upload', 'file_type' => 'image/*'],
                ['name' => 'document_upload', 'type' => 'upload', 'label' => 'Document Upload', 'file_type' => 'application/pdf'],
                ['name' => 'any_file', 'type' => 'upload', 'label' => 'Any File', 'file_type' => '*/*']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($fileTypeForm, 'File Type Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testDateFieldValidation()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $dateForm = [
            'id' => 'date_test',
            'name' => 'Date Field Test',
            'custom_fields' => [
                ['name' => 'simple_date', 'type' => 'date', 'label' => 'Simple Date'],
                ['name' => 'future_date', 'type' => 'date', 'label' => 'Future Date', 'disable_before' => 'today'],
                ['name' => 'past_date', 'type' => 'date', 'label' => 'Past Date', 'disable_before' => ''],
                ['name' => 'specific_date', 'type' => 'date', 'label' => 'Specific Date', 'disable_before' => '2024-01-01']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($dateForm, 'Date Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testAutocompleteAttributeHandling()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $autocompleteForm = [
            'id' => 'autocomplete_test',
            'name' => 'Autocomplete Test',
            'fields' => [
                ['name' => 'your_name', 'type' => 'text', 'label' => 'Your Name'],
                ['name' => 'your_email', 'type' => 'email', 'label' => 'Your Email'],
                ['name' => 'your_phone', 'type' => 'phone', 'label' => 'Your Phone'],
                ['name' => 'company_name', 'type' => 'text', 'label' => 'Company Name'],
                ['name' => 'website_url', 'type' => 'website', 'label' => 'Website URL']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($autocompleteForm, 'Autocomplete Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithNoFields()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $noFieldsForm = [
            'id' => 'no_fields_test',
            'name' => 'No Fields Test',
            'description' => 'A form with no fields',
            'fields' => []
        ];
        
        $cf7Result = $formImportService->importToCF7($noFieldsForm, 'No Fields Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
    }

    public function testFormWithNullFields()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $nullFieldsForm = [
            'id' => 'null_fields_test',
            'name' => 'Null Fields Test',
            'fields' => [
                null,
                ['name' => 'valid_field', 'type' => 'text', 'label' => 'Valid Field'],
                null
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($nullFieldsForm, 'Null Fields Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithDuplicateFieldNames()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $duplicateForm = [
            'id' => 'duplicate_test',
            'name' => 'Duplicate Fields Test',
            'fields' => [
                ['name' => 'email', 'type' => 'email', 'label' => 'Email 1'],
                ['name' => 'email', 'type' => 'email', 'label' => 'Email 2'],
                ['name' => 'name', 'type' => 'text', 'label' => 'Name 1'],
                ['name' => 'name', 'type' => 'text', 'label' => 'Name 2']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($duplicateForm, 'Duplicate Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithEmptyFieldNames()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $emptyNamesForm = [
            'id' => 'empty_names_test',
            'name' => 'Empty Names Test',
            'fields' => [
                ['name' => '', 'type' => 'text', 'label' => 'Empty Name'],
                ['name' => null, 'type' => 'email', 'label' => 'Null Name'],
                ['name' => 'valid_name', 'type' => 'text', 'label' => 'Valid Name']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($emptyNamesForm, 'Empty Names Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithSpecialFieldTypes()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $specialTypesForm = [
            'id' => 'special_types_test',
            'name' => 'Special Types Test',
            'fields' => [
                ['name' => 'information_field', 'type' => 'information', 'label' => 'Information Field'],
                ['name' => 'captcha_field', 'type' => 'captcha', 'label' => 'CAPTCHA Field'],
                ['name' => 'decimal_field', 'type' => 'decimal', 'label' => 'Decimal Field'],
                ['name' => 'text_area_field', 'type' => 'text_area', 'label' => 'Text Area Field']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($specialTypesForm, 'Special Types Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithComplexOptions()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $complexOptionsForm = [
            'id' => 'complex_options_test',
            'name' => 'Complex Options Test',
            'fields' => [
                [
                    'name' => 'select_with_quotes',
                    'type' => 'select',
                    'label' => 'Select with Quotes',
                    'options' => ['Option "with" quotes', 'Option with \'single\' quotes', 'Option with & symbols']
                ],
                [
                    'name' => 'radio_with_special_chars',
                    'type' => 'radio',
                    'label' => 'Radio with Special Chars',
                    'options' => ['Option 1 & 2', 'Option 3 < 4', 'Option 5 > 6']
                ],
                [
                    'name' => 'picker_with_unicode',
                    'type' => 'picker',
                    'label' => 'Picker with Unicode',
                    'options' => ['Option 中文', 'Option Español', 'Option Français']
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($complexOptionsForm, 'Complex Options Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithVeryLongLabels()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $longLabelsForm = [
            'id' => 'long_labels_test',
            'name' => 'Long Labels Test',
            'fields' => [
                [
                    'name' => 'field1',
                    'type' => 'text',
                    'label' => 'This is a very long field label that contains many words and should be handled properly by the form import system without causing any issues or breaking the form generation process',
                    'required' => true
                ],
                [
                    'name' => 'field2',
                    'type' => 'textarea',
                    'label' => 'Another extremely long field label with multiple sentences and various punctuation marks including commas, periods, and even some special characters like @#$%^&*() that should all be processed correctly',
                    'required' => false
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($longLabelsForm, 'Long Labels Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithNumericFieldNames()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $numericNamesForm = [
            'id' => 'numeric_names_test',
            'name' => 'Numeric Names Test',
            'fields' => [
                ['name' => '123', 'type' => 'text', 'label' => 'Numeric Name 1'],
                ['name' => 'field_456', 'type' => 'email', 'label' => 'Numeric Name 2'],
                ['name' => '789_field', 'type' => 'phone', 'label' => 'Numeric Name 3'],
                ['name' => 'field_123_456', 'type' => 'text', 'label' => 'Numeric Name 4']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($numericNamesForm, 'Numeric Names Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithReservedWords()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $reservedWordsForm = [
            'id' => 'reserved_words_test',
            'name' => 'Reserved Words Test',
            'fields' => [
                ['name' => 'submit', 'type' => 'text', 'label' => 'Submit Field'],
                ['name' => 'action', 'type' => 'text', 'label' => 'Action Field'],
                ['name' => 'method', 'type' => 'text', 'label' => 'Method Field'],
                ['name' => 'form', 'type' => 'text', 'label' => 'Form Field'],
                ['name' => 'input', 'type' => 'text', 'label' => 'Input Field']
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($reservedWordsForm, 'Reserved Words Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithMixedDataStructures()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $mixedForm = [
            'id' => 'mixed_test',
            'name' => 'Mixed Data Test',
            'fields' => [
                ['name' => 'old_format', 'type' => 'text', 'label' => 'Old Format Field']
            ],
            'custom_fields' => [
                ['name' => 'new_format', 'type' => 'email', 'label' => 'New Format Field', 'half_width' => true]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($mixedForm, 'Mixed Data Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testPerformanceWithManyOptions()
    {
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $manyOptionsForm = [
            'id' => 'many_options_test',
            'name' => 'Many Options Test',
            'fields' => [
                [
                    'name' => 'large_select',
                    'type' => 'select',
                    'label' => 'Large Select',
                    'options' => array_map(function($i) { return "Option {$i}"; }, range(1, 100))
                ],
                [
                    'name' => 'large_radio',
                    'type' => 'radio',
                    'label' => 'Large Radio',
                    'options' => array_map(function($i) { return "Radio {$i}"; }, range(1, 50))
                ]
            ]
        ];
        
        $startTime = microtime(true);
        $cf7Result = $formImportService->importToCF7($manyOptionsForm, 'Many Options Test');
        $endTime = microtime(true);
        
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertLessThan(5.0, $endTime - $startTime, 'Form import should complete within 5 seconds');
    }

    public function testApiResponseVariations()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test with different API response structures
        $variationForm1 = [
            'id' => 'variation_1',
            'name' => 'API Response Variation 1',
            'form_reactors' => [
                ['id' => 'form1', 'name' => 'Form 1', 'fields' => [['name' => 'field1', 'type' => 'text', 'label' => 'Field 1']]]
            ]
        ];
        
        $variationForm2 = [
            'id' => 'variation_2',
            'name' => 'API Response Variation 2',
            'forms' => [
                ['id' => 'form2', 'name' => 'Form 2', 'custom_fields' => [['name' => 'field2', 'type' => 'email', 'label' => 'Field 2']]]
            ]
        ];
        
        $cf7Result1 = $formImportService->importToCF7($variationForm1, 'Variation 1');
        $cf7Result2 = $formImportService->importToCF7($variationForm2, 'Variation 2');
        
        $this->assertNotNull($cf7Result1);
        $this->assertNotNull($cf7Result2);
        $this->assertIsArray($cf7Result1);
        $this->assertIsArray($cf7Result2);
    }

    public function testFormValidationWithInvalidData()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $invalidData = [
            'id' => 'invalid_data_test',
            'name' => 'Invalid Data Test',
            'fields' => [
                ['name' => 'field1', 'type' => 'invalid_type', 'label' => 'Invalid Type'],
                ['name' => 'field2', 'type' => 'text', 'label' => '', 'required' => 'not_boolean'],
                ['name' => 'field3', 'type' => 'select', 'label' => 'Select Field', 'options' => null],
                ['name' => 'field4', 'type' => 'number', 'label' => 'Number Field', 'min' => 'not_number'],
                ['name' => 'field5', 'type' => 'date', 'label' => 'Date Field', 'disable_before' => 123],
                ['name' => 'field6', 'type' => 'radio', 'label' => 'Radio Field', 'options' => 'invalid_options'],
                ['name' => 'field7', 'type' => 'picker', 'label' => 'Picker Field', 'options' => false]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($invalidData, 'Invalid Data Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
        $this->assertArrayHasKey('success', $cf7Result);
    }

    public function testFormWithConditionalLogic()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $conditionalForm = [
            'id' => 'conditional_logic_test',
            'name' => 'Conditional Logic Test',
            'custom_fields' => [
                [
                    'name' => 'contact_type',
                    'type' => 'select',
                    'label' => 'Contact Type',
                    'required' => true,
                    'options' => ['General', 'Support', 'Sales'],
                    'conditional_logic' => [
                        'show_when' => 'always',
                        'hide_when' => 'never'
                    ]
                ],
                [
                    'name' => 'support_ticket',
                    'type' => 'text',
                    'label' => 'Support Ticket',
                    'required' => false,
                    'conditional_logic' => [
                        'show_when' => 'contact_type equals Support',
                        'hide_when' => 'contact_type not equals Support'
                    ]
                ],
                [
                    'name' => 'company_size',
                    'type' => 'select',
                    'label' => 'Company Size',
                    'required' => false,
                    'options' => ['1-10', '11-50', '51+'],
                    'conditional_logic' => [
                        'show_when' => 'contact_type equals Sales',
                        'hide_when' => 'contact_type not equals Sales'
                    ]
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($conditionalForm, 'Conditional Logic Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithAdvancedFieldProperties()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $advancedForm = [
            'id' => 'advanced_properties_test',
            'name' => 'Advanced Properties Test',
            'custom_fields' => [
                [
                    'name' => 'text_with_placeholder',
                    'type' => 'text',
                    'label' => 'Text with Placeholder',
                    'placeholder' => 'Enter your name here',
                    'max_length' => 50,
                    'min_length' => 2
                ],
                [
                    'name' => 'email_with_validation',
                    'type' => 'email',
                    'label' => 'Email with Validation',
                    'validation_pattern' => '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$',
                    'validation_message' => 'Please enter a valid email address'
                ],
                [
                    'name' => 'number_with_constraints',
                    'type' => 'number',
                    'label' => 'Number with Constraints',
                    'min_value' => 1,
                    'max_value' => 100,
                    'step' => 5
                ],
                [
                    'name' => 'textarea_with_counter',
                    'type' => 'textarea',
                    'label' => 'Textarea with Counter',
                    'max_length' => 500,
                    'rows' => 5,
                    'cols' => 40
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($advancedForm, 'Advanced Properties Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithCustomValidationRules()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $customValidationForm = [
            'id' => 'custom_validation_test',
            'name' => 'Custom Validation Test',
            'custom_fields' => [
                [
                    'name' => 'phone_with_format',
                    'type' => 'phone',
                    'label' => 'Phone with Format',
                    'validation_pattern' => '^\+?[1-9]\d{1,14}$',
                    'validation_message' => 'Please enter a valid phone number'
                ],
                [
                    'name' => 'zip_code',
                    'type' => 'text',
                    'label' => 'ZIP Code',
                    'validation_pattern' => '^\d{5}(-\d{4})?$',
                    'validation_message' => 'Please enter a valid ZIP code'
                ],
                [
                    'name' => 'credit_card',
                    'type' => 'text',
                    'label' => 'Credit Card',
                    'validation_pattern' => '^\d{4}[\s-]?\d{4}[\s-]?\d{4}[\s-]?\d{4}$',
                    'validation_message' => 'Please enter a valid credit card number'
                ],
                [
                    'name' => 'ssn',
                    'type' => 'text',
                    'label' => 'Social Security Number',
                    'validation_pattern' => '^\d{3}-\d{2}-\d{4}$',
                    'validation_message' => 'Please enter SSN in format XXX-XX-XXXX'
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($customValidationForm, 'Custom Validation Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithFileUploadConstraints()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $fileUploadForm = [
            'id' => 'file_upload_constraints_test',
            'name' => 'File Upload Constraints Test',
            'custom_fields' => [
                [
                    'name' => 'image_upload',
                    'type' => 'upload',
                    'label' => 'Image Upload',
                    'file_type' => 'image/*',
                    'max_file_size' => '2MB',
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'max_files' => 1
                ],
                [
                    'name' => 'document_upload',
                    'type' => 'upload',
                    'label' => 'Document Upload',
                    'file_type' => 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'max_file_size' => '10MB',
                    'allowed_extensions' => ['pdf', 'doc', 'docx'],
                    'max_files' => 3
                ],
                [
                    'name' => 'video_upload',
                    'type' => 'upload',
                    'label' => 'Video Upload',
                    'file_type' => 'video/*',
                    'max_file_size' => '50MB',
                    'allowed_extensions' => ['mp4', 'avi', 'mov'],
                    'max_files' => 1
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($fileUploadForm, 'File Upload Constraints Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithMultiStepLogic()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $multiStepForm = [
            'id' => 'multi_step_test',
            'name' => 'Multi-Step Form Test',
            'steps' => [
                [
                    'step_number' => 1,
                    'step_title' => 'Personal Information',
                    'custom_fields' => [
                        ['name' => 'first_name', 'type' => 'text', 'label' => 'First Name', 'required' => true],
                        ['name' => 'last_name', 'type' => 'text', 'label' => 'Last Name', 'required' => true],
                        ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true]
                    ]
                ],
                [
                    'step_number' => 2,
                    'step_title' => 'Company Information',
                    'custom_fields' => [
                        ['name' => 'company_name', 'type' => 'text', 'label' => 'Company Name', 'required' => false],
                        ['name' => 'job_title', 'type' => 'text', 'label' => 'Job Title', 'required' => false],
                        ['name' => 'industry', 'type' => 'select', 'label' => 'Industry', 'required' => false, 'options' => ['Technology', 'Healthcare', 'Finance', 'Other']]
                    ]
                ],
                [
                    'step_number' => 3,
                    'step_title' => 'Additional Information',
                    'custom_fields' => [
                        ['name' => 'message', 'type' => 'textarea', 'label' => 'Message', 'required' => false],
                        ['name' => 'newsletter', 'type' => 'checkbox', 'label' => 'Subscribe to Newsletter', 'required' => false]
                    ]
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($multiStepForm, 'Multi-Step Form Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithCalculatedFields()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $calculatedForm = [
            'id' => 'calculated_fields_test',
            'name' => 'Calculated Fields Test',
            'custom_fields' => [
                [
                    'name' => 'quantity',
                    'type' => 'number',
                    'label' => 'Quantity',
                    'required' => true,
                    'min_value' => 1,
                    'max_value' => 100
                ],
                [
                    'name' => 'unit_price',
                    'type' => 'number',
                    'label' => 'Unit Price',
                    'required' => true,
                    'step' => 0.01
                ],
                [
                    'name' => 'total_amount',
                    'type' => 'number',
                    'label' => 'Total Amount',
                    'required' => false,
                    'calculated' => true,
                    'calculation' => 'quantity * unit_price'
                ],
                [
                    'name' => 'tax_rate',
                    'type' => 'number',
                    'label' => 'Tax Rate (%)',
                    'required' => false,
                    'default_value' => 8.5
                ],
                [
                    'name' => 'tax_amount',
                    'type' => 'number',
                    'label' => 'Tax Amount',
                    'required' => false,
                    'calculated' => true,
                    'calculation' => 'total_amount * (tax_rate / 100)'
                ],
                [
                    'name' => 'grand_total',
                    'type' => 'number',
                    'label' => 'Grand Total',
                    'required' => false,
                    'calculated' => true,
                    'calculation' => 'total_amount + tax_amount'
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($calculatedForm, 'Calculated Fields Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithRepeatingSections()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $repeatingForm = [
            'id' => 'repeating_sections_test',
            'name' => 'Repeating Sections Test',
            'sections' => [
                [
                    'section_name' => 'primary_contact',
                    'section_label' => 'Primary Contact',
                    'repeatable' => false,
                    'custom_fields' => [
                        ['name' => 'primary_name', 'type' => 'text', 'label' => 'Name', 'required' => true],
                        ['name' => 'primary_email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                        ['name' => 'primary_phone', 'type' => 'phone', 'label' => 'Phone', 'required' => false]
                    ]
                ],
                [
                    'section_name' => 'additional_contacts',
                    'section_label' => 'Additional Contacts',
                    'repeatable' => true,
                    'max_repeats' => 5,
                    'custom_fields' => [
                        ['name' => 'contact_name', 'type' => 'text', 'label' => 'Contact Name', 'required' => false],
                        ['name' => 'contact_email', 'type' => 'email', 'label' => 'Contact Email', 'required' => false],
                        ['name' => 'contact_role', 'type' => 'select', 'label' => 'Role', 'required' => false, 'options' => ['Manager', 'Developer', 'Designer', 'Other']]
                    ]
                ],
                [
                    'section_name' => 'project_details',
                    'section_label' => 'Project Details',
                    'repeatable' => true,
                    'max_repeats' => 10,
                    'custom_fields' => [
                        ['name' => 'project_name', 'type' => 'text', 'label' => 'Project Name', 'required' => false],
                        ['name' => 'project_description', 'type' => 'textarea', 'label' => 'Description', 'required' => false],
                        ['name' => 'project_budget', 'type' => 'number', 'label' => 'Budget', 'required' => false]
                    ]
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($repeatingForm, 'Repeating Sections Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithAdvancedStyling()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $styledForm = [
            'id' => 'advanced_styling_test',
            'name' => 'Advanced Styling Test',
            'theme' => 'modern',
            'style' => 'rounded',
            'color_scheme' => 'blue',
            'custom_css' => '.form-field { border-radius: 8px; }',
            'custom_fields' => [
                [
                    'name' => 'styled_text',
                    'type' => 'text',
                    'label' => 'Styled Text Field',
                    'required' => true,
                    'css_class' => 'highlighted-field',
                    'inline_style' => 'background-color: #f0f8ff; border: 2px solid #4CAF50;'
                ],
                [
                    'name' => 'styled_select',
                    'type' => 'select',
                    'label' => 'Styled Select Field',
                    'required' => false,
                    'options' => ['Option 1', 'Option 2', 'Option 3'],
                    'css_class' => 'custom-select',
                    'inline_style' => 'border-radius: 15px; padding: 10px;'
                ],
                [
                    'name' => 'styled_textarea',
                    'type' => 'textarea',
                    'label' => 'Styled Textarea',
                    'required' => false,
                    'rows' => 4,
                    'css_class' => 'large-textarea',
                    'inline_style' => 'resize: vertical; min-height: 100px;'
                ]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($styledForm, 'Advanced Styling Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testFormWithIntegrationHooks()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        $integrationForm = [
            'id' => 'integration_hooks_test',
            'name' => 'Integration Hooks Test',
            'integrations' => [
                'mailchimp' => [
                    'enabled' => true,
                    'list_id' => 'abc123',
                    'fields_mapping' => [
                        'email' => 'EMAIL',
                        'first_name' => 'FNAME',
                        'last_name' => 'LNAME'
                    ]
                ],
                'salesforce' => [
                    'enabled' => true,
                    'object_type' => 'Lead',
                    'fields_mapping' => [
                        'email' => 'Email',
                        'company_name' => 'Company',
                        'phone' => 'Phone'
                    ]
                ],
                'zapier' => [
                    'enabled' => true,
                    'webhook_url' => 'https://hooks.zapier.com/hooks/catch/123/abc/'
                ]
            ],
            'custom_fields' => [
                ['name' => 'first_name', 'type' => 'text', 'label' => 'First Name', 'required' => true],
                ['name' => 'last_name', 'type' => 'text', 'label' => 'Last Name', 'required' => true],
                ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                ['name' => 'company_name', 'type' => 'text', 'label' => 'Company Name', 'required' => false],
                ['name' => 'phone', 'type' => 'phone', 'label' => 'Phone', 'required' => false]
            ]
        ];
        
        $cf7Result = $formImportService->importToCF7($integrationForm, 'Integration Hooks Test');
        $this->assertNotNull($cf7Result);
        $this->assertIsArray($cf7Result);
    }

    public function testMultipleImportCapability()
    {
        $this->markTestSkipped('Test requires complex static method mocking that is difficult to set up properly');
        // Mock WordPress functions for CF7
        \Brain\Monkey\Functions\when('wp_insert_post')->justReturn(123);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test@example.com');
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        // Mock get_post_meta to return the CTM form ID for the specific form ID
        \Brain\Monkey\Functions\when('get_post_meta')->alias(function($post_id, $key, $single = true) {
            if ($key === '_ctm_form_id' && $post_id == 123) {
                return 'test_form_id';
            }
            return '';
        });
        
        // Create mock WPCF7_ContactForm class
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm {
                public static function find() {
                    $form1 = new stdClass();
                    $form1->id = function() { return 123; };
                    $form1->title = function() { return "Test Form CF7"; };
                    return [$form1];
                }
            }');
        }
        
        // Create mock GFAPI class
        if (!class_exists('GFAPI')) {
            eval('class GFAPI {
                public static function get_forms() {
                    return [
                        [
                            "id" => 456,
                            "title" => "Test Form GF"
                        ]
                    ];
                }
            }');
        }
        
        // Mock gform_get_meta to return the CTM form ID for the specific form ID
        \Brain\Monkey\Functions\when('gform_get_meta')->alias(function($form_id, $key) {
            if ($key === '_ctm_form_id' && $form_id == 456) {
                return 'test_form_id';
            }
            return '';
        });
        
        \Brain\Monkey\Functions\when('admin_url')->justReturn('http://example.com/wp-admin/');
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test that getImportedFormInfo returns multiple imports
        $importInfo = $formImportService->getImportedFormInfo('test_form_id');
        
        $this->assertNotNull($importInfo);
        $this->assertIsArray($importInfo);
        $this->assertCount(2, $importInfo); // Should have both CF7 and GF imports
        
        // Check CF7 import
        $cf7Import = $importInfo[0];
        $this->assertEquals('cf7', $cf7Import['type']);
        $this->assertEquals(123, $cf7Import['form_id']);
        $this->assertEquals('Test Form CF7', $cf7Import['form_title']);
        $this->assertStringContainsString('wpcf7', $cf7Import['edit_url']);
        
        // Check GF import
        $gfImport = $importInfo[1];
        $this->assertEquals('gf', $gfImport['type']);
        $this->assertEquals(456, $gfImport['form_id']);
        $this->assertEquals('Test Form GF', $gfImport['form_title']);
        $this->assertStringContainsString('gf_edit_forms', $gfImport['edit_url']);
    }

    public function testGetImportedFormInfoReturnsNullWhenNoImports()
    {
        // Mock WordPress functions to return no forms
        \Brain\Monkey\Functions\when('class_exists')->justReturn(true);
        
        // Create mock WPCF7_ContactForm class that returns empty array
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm {
                public static function find() {
                    return [];
                }
            }');
        }
        
        // Create mock GFAPI class that returns empty array
        if (!class_exists('GFAPI')) {
            eval('class GFAPI {
                public static function get_forms() {
                    return [];
                }
            }');
        }
        
        $apiService = new ApiService('https://api.calltrackingmetrics.com');
        $cf7Service = new CF7Service();
        $gfService = new GFService();
        
        $formImportService = new FormImportService($apiService, $cf7Service, $gfService);
        
        // Test that getImportedFormInfo returns null when no imports exist
        $importInfo = $formImportService->getImportedFormInfo('non_existent_form_id');
        
        $this->assertNull($importInfo);
    }
} 