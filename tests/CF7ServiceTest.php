<?php

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;
use CTM\Service\CF7Service;
use CTM\Tests\Traits\MonkeyTrait;

if (!class_exists('WPCF7_Submission')) {
    class WPCF7_Submission {
        public static $instance;
        public static function get_instance() {
            return self::$instance ?: (self::$instance = new self());
        }
        public function get_posted_data() { return ["field" => "value"]; }
    }
}

class CF7ServiceTest extends TestCase
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

    public function testProcessSubmissionReturnsNullIfNoCF7()
    {
        // Simulate missing CF7 or invalid form
        $cf7Service = new \CTM\Service\CF7Service();
        $result = $cf7Service->processSubmission(null, []);
        $this->assertNull($result, 'Should return null if CF7 is missing or form is invalid');
    }

    public function testProcessSubmissionHandlesValidForm()
    {
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm {
                public function id() { return 1; }
                public function title() { return "Test Form"; }
                public function prop($key) { if ($key === "form") return "[text* your-name]"; return null; }
            }');
        }
        // Use a real instance of the minimal class
        $form = new \WPCF7_ContactForm();
        // Debug: check class and instanceof
        $this->assertEquals('WPCF7_ContactForm', get_class($form));
        $this->assertTrue($form instanceof \WPCF7_ContactForm);
        // Mock environment
        $_SERVER['HTTP_USER_AGENT'] = 'UnitTestAgent';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['REQUEST_URI'] = '/test-form';
        $_SERVER['HTTPS'] = 'off';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_GET = [];
        $data = ['your-name' => 'Tom'];
        $cf7Service = new \CTM\Service\CF7Service();
        try {
            $result = $cf7Service->processSubmission($form, $data);
        } catch (\Throwable $e) {
            $this->fail('Exception thrown: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        }
        $this->assertIsArray($result, 'Should return an array for valid form and data');
        $this->assertEquals('contact_form_7', $result['form_type']);
        $this->assertEquals(1, $result['form_id']);
        $this->assertEquals('CF7 Form', $result['form_title']);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('raw_data', $result);
    }


} 