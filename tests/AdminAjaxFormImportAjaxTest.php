<?php

use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\FormImportAjax;
use CTM\Service\FormImportService;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class AdminAjaxFormImportAjaxTest extends TestCase
{
    use MonkeyTrait;

    private $formImportService;
    private $formImportAjax;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();

        // Create a simple stub for FormImportService instead of using createMock
        $this->formImportService = new class extends FormImportService {
            public function __construct() {
                // Empty constructor to avoid parent dependencies
            }
            
            public function getAvailableForms(string $apiKey, string $apiSecret): ?array {
                return [
                    ['id' => 'CTM123', 'name' => 'Updated Form', 'fields' => []],
                    ['id' => 'CTM456', 'name' => 'Updated GF Form', 'fields' => []]
                ];
            }
            
            public function convertToCF7Format(array $ctmForm): string {
                return '[text* updated-name] [email* updated-email] [submit "Updated Send"]';
            }
            
            public function convertToGFFormat(array $ctmForm, string $formTitle): array {
                return [
                    'title' => 'Updated GF Form',
                    'fields' => [
                        ['id' => 1, 'label' => 'Updated Name', 'type' => 'text']
                    ]
                ];
            }
        };
        
        $this->formImportAjax = new FormImportAjax($this->formImportService);

        // WordPress classes are mocked in MonkeyTrait

        // GFAPI class is mocked in MonkeyTrait

        // Mock global WordPress functions
        \Brain\Monkey\Functions\when('wp_verify_nonce')->justReturn(true);
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(true);
        \Brain\Monkey\Functions\when('sanitize_text_field')->returnArg();
        \Brain\Monkey\Functions\when('get_option')->justReturn('test_value');
        \Brain\Monkey\Functions\when('update_post_meta')->justReturn(true);
        \Brain\Monkey\Functions\when('current_time')->justReturn('2023-12-01 12:00:00');
        \Brain\Monkey\Functions\when('esc_html')->returnArg();
        \Brain\Monkey\Functions\when('esc_attr')->returnArg();
        \Brain\Monkey\Functions\when('do_shortcode')->returnArg();
        
        // Mock wp_send_json_* functions
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($data) {
            echo json_encode(['success' => true, 'data' => $data]);
            return true;
        });
        
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($data) {
            echo json_encode(['success' => false, 'data' => $data]);
            return true;
        });
    }

    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }

    public function testCanBeConstructed()
    {
        $this->assertInstanceOf(FormImportAjax::class, $this->formImportAjax);
    }

    public function testRegisterHandlersAddsAllActions()
    {
        $actionCount = 0;
        \Brain\Monkey\Functions\when('add_action')->alias(function() use (&$actionCount) {
            $actionCount++;
        });

        $this->formImportAjax->registerHandlers();

        // Should register 6 actions: get_available_forms, import_form, preview_form, sync_form, update_form, preview_wp_form
        $this->assertEquals(6, $actionCount);
    }

    public function testUpdateFormWithMissingNonce()
    {
        \Brain\Monkey\Functions\when('wp_verify_nonce')->justReturn(false);
        
        $_POST = [
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Security check failed', $response['data']['message']);
    }

    public function testUpdateFormWithInsufficientPermissions()
    {
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(false);
        
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Insufficient permissions', $response['data']['message']);
    }

    public function testUpdateFormWithMissingParameters()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Missing required parameters', $response['data']['message']);
    }

    public function testUpdateFormWithInvalidFormType()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '123',
            'wp_form_type' => 'invalid',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Invalid form type', $response['data']['message']);
    }

    public function testUpdateFormSuccessfulCF7Update()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Form updated successfully', $response['data']['message']);
        $this->assertEquals('123', $response['data']['wp_form_id']);
        $this->assertEquals('CTM123', $response['data']['ctm_form_id']);
    }

    public function testUpdateFormSuccessfulGFUpdate()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '456',
            'wp_form_type' => 'gf',
            'ctm_form_id' => 'CTM456'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Form updated successfully', $response['data']['message']);
    }

    public function testPreviewWPFormWithMissingNonce()
    {
        \Brain\Monkey\Functions\when('wp_verify_nonce')->justReturn(false);
        
        $_POST = [
            'form_id' => '123',
            'form_type' => 'cf7'
        ];

        ob_start();
        $this->formImportAjax->previewWPForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Security check failed', $response['data']['message']);
    }

    public function testPreviewWPFormWithInsufficientPermissions()
    {
        \Brain\Monkey\Functions\when('current_user_can')->justReturn(false);
        
        $_POST = [
            'nonce' => 'valid_nonce',
            'form_id' => '123',
            'form_type' => 'cf7'
        ];

        ob_start();
        $this->formImportAjax->previewWPForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Insufficient permissions', $response['data']['message']);
    }

    public function testPreviewWPFormCF7Success()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'form_id' => '123',
            'form_type' => 'cf7'
        ];

        ob_start();
        $this->formImportAjax->previewWPForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('preview', $response['data']);
        $this->assertEquals('123', $response['data']['form_id']);
        $this->assertEquals('cf7', $response['data']['form_type']);
        $this->assertStringContainsString('ctm-form-preview', $response['data']['preview']);
    }

    public function testPreviewWPFormGFSuccess()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'form_id' => '456',
            'form_type' => 'gf'
        ];

        ob_start();
        $this->formImportAjax->previewWPForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('preview', $response['data']);
        $this->assertEquals('456', $response['data']['form_id']);
        $this->assertEquals('gf', $response['data']['form_type']);
        $this->assertStringContainsString('ctm-form-preview', $response['data']['preview']);
    }

    public function testPreviewWPFormWithNonExistentCF7Form()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'form_id' => '999', // Non-existent form
            'form_type' => 'cf7'
        ];

        ob_start();
        $this->formImportAjax->previewWPForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Contact Form 7 form not found', $response['data']['message']);
    }

    public function testPreviewWPFormWithNonExistentGFForm()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'form_id' => '999', // Non-existent form
            'form_type' => 'gf'
        ];

        ob_start();
        $this->formImportAjax->previewWPForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertStringContainsString('Gravity Forms form not found', $response['data']['message']);
    }

    public function testSyncFormWithMissingNonce()
    {
        \Brain\Monkey\Functions\when('wp_verify_nonce')->justReturn(false);
        
        $_POST = [
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->syncForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('Security check failed', $response['data']['message']);
    }

    public function testSyncFormSuccessfulCF7Sync()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->syncForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Form synced successfully', $response['data']['message']);
    }

    public function testSyncFormSuccessfulGFSync()
    {
        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '456',
            'wp_form_type' => 'gf',
            'ctm_form_id' => 'CTM456'
        ];

        ob_start();
        $this->formImportAjax->syncForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertTrue($response['success']);
        $this->assertEquals('Form synced successfully', $response['data']['message']);
    }

    public function testGenerateBasicCF7PreviewWithEmptyContent()
    {
        // Create a CF7 form with empty content
        $cf7Form = \WPCF7_ContactForm::get_instance('123');
        $cf7Form->set_properties(['form' => '']);

        $reflection = new \ReflectionClass($this->formImportAjax);
        $method = $reflection->getMethod('generateBasicCF7Preview');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->formImportAjax, [$cf7Form]);
        
        $this->assertStringContainsString('No form content available for preview', $result);
    }

    public function testGenerateBasicGFPreviewWithEmptyFields()
    {
        $gfForm = ['fields' => []];

        $reflection = new \ReflectionClass($this->formImportAjax);
        $method = $reflection->getMethod('generateBasicGFPreview');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->formImportAjax, [$gfForm]);
        
        $this->assertStringContainsString('No form fields available for preview', $result);
    }

    public function testGenerateBasicGFPreviewWithVariousFieldTypes()
    {
        $gfForm = [
            'fields' => [
                [
                    'id' => 1,
                    'label' => 'Text Field',
                    'type' => 'text',
                    'isRequired' => true
                ],
                [
                    'id' => 2,
                    'label' => 'Select Field',
                    'type' => 'select',
                    'choices' => [
                        ['value' => 'option1', 'text' => 'Option 1'],
                        ['value' => 'option2', 'text' => 'Option 2']
                    ]
                ],
                [
                    'id' => 3,
                    'label' => 'Radio Field',
                    'type' => 'radio',
                    'choices' => [
                        ['value' => 'radio1', 'text' => 'Radio 1'],
                        ['value' => 'radio2', 'text' => 'Radio 2']
                    ]
                ],
                [
                    'id' => 4,
                    'label' => 'File Upload',
                    'type' => 'fileupload'
                ]
            ]
        ];

        $reflection = new \ReflectionClass($this->formImportAjax);
        $method = $reflection->getMethod('generateBasicGFPreview');
        $method->setAccessible(true);

        $result = $method->invokeArgs($this->formImportAjax, [$gfForm]);
        
        $this->assertStringContainsString('Text Field', $result);
        $this->assertStringContainsString('Select Field', $result);
        $this->assertStringContainsString('Radio Field', $result);
        $this->assertStringContainsString('File Upload', $result);
        $this->assertStringContainsString('input type="text"', $result);
        $this->assertStringContainsString('<select', $result);
        $this->assertStringContainsString('input type="radio"', $result);
        $this->assertStringContainsString('input type="file"', $result);
        $this->assertStringContainsString('<span style="color: red;">*</span>', $result); // Required field indicator
    }

    public function testUpdateFormWithMissingCTMForm()
    {
        // Create a new stub that returns empty forms array
        $emptyFormImportService = new class extends FormImportService {
            public function __construct() {}
            public function getAvailableForms(string $apiKey, string $apiSecret): ?array {
                return []; // Empty array
            }
        };
        
        $formImportAjax = new FormImportAjax($emptyFormImportService);

        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'NONEXISTENT'
        ];

        ob_start();
        $formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('CTM form not found', $response['data']['message']);
    }

    public function testUpdateFormWithApiCredentialsNotConfigured()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_key' || $key === 'ctm_api_secret') {
                return '';
            }
            return 'test_value';
        });

        $_POST = [
            'nonce' => 'valid_nonce',
            'wp_form_id' => '123',
            'wp_form_type' => 'cf7',
            'ctm_form_id' => 'CTM123'
        ];

        ob_start();
        $this->formImportAjax->updateForm();
        $output = ob_get_clean();

        $response = json_decode($output, true);
        $this->assertFalse($response['success']);
        $this->assertEquals('API credentials not configured', $response['data']['message']);
    }
}