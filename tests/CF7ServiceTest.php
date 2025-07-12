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
        if (class_exists('WPCF7_ContactForm')) {
            // If the class exists but does not have prop(), skip the test
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
        } else {
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

    public function testGetFormsReturnsEmptyIfNoCF7()
    {
        if (class_exists('WPCF7_ContactForm')) {
            $this->markTestSkipped('Cannot unload WPCF7_ContactForm class in this environment.');
        }
        $cf7Service = new \CTM\Service\CF7Service();
        $this->assertSame([], $cf7Service->getForms());
    }

    public function testGetFormsReturnsFormsWithCF7()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'is_posted')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have is_posted() method.');
            }
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
            if (!method_exists('WPCF7_ContactForm', 'find')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have find() method.');
            }
            // Cannot patch static methods, so skip
            $this->markTestSkipped('Cannot patch static find method on real WPCF7_ContactForm.');
        } else {
            eval('class WPCF7_ContactForm {
                public function id() { return 42; }
                public function title() { return "CF7 Title"; }
                public function is_posted() { return true; }
                public function prop($key) { if ($key === "form") return "[text* your-name]"; return null; }
                public static function find($args) { return [new self()]; }
            }');
            $form = new \WPCF7_ContactForm();
            $cf7Service = new \CTM\Service\CF7Service();
            $forms = $cf7Service->getForms();
            $this->assertIsArray($forms);
            $this->assertNotEmpty($forms);
            $this->assertEquals(42, $forms[0]['id']);
            $this->assertEquals('CF7 Title', $forms[0]['title']);
            $this->assertEquals('active', $forms[0]['status']);
            $this->assertGreaterThanOrEqual(1, $forms[0]['field_count']);
        }
    }

    public function testGetFormsHandlesException()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'find')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have find() method.');
            }
            // Cannot patch static methods, so skip
            $this->markTestSkipped('Cannot patch static find method on real WPCF7_ContactForm.');
        } else {
            eval('class WPCF7_ContactForm {
                public static function find($args) { throw new \Exception("fail"); }
            }');
            $cf7Service = new \CTM\Service\CF7Service();
            $forms = $cf7Service->getForms();
            $this->assertIsArray($forms);
            $this->assertEmpty($forms);
        }
    }

    public function testGetFormFieldsWithNumericId()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'get_instance')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have get_instance() method.');
            }
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
            // Cannot patch static methods, so skip
            $this->markTestSkipped('Cannot patch static get_instance method on real WPCF7_ContactForm.');
        } else {
            eval('class WPCF7_ContactForm {
                public function prop($key) { if ($key === "form") return "[text* your-name]"; return null; }
                public static function get_instance($id) { return new self(); }
            }');
            $cf7Service = new \CTM\Service\CF7Service();
            $fields = $cf7Service->getFormFields(1);
            $this->assertIsArray($fields);
            $this->assertNotEmpty($fields);
            $this->assertEquals('your-name', $fields[0]['name']);
        }
    }

    public function testGetFormFieldsWithFormObject()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
            $form = new \WPCF7_ContactForm();
        } else {
            eval('class WPCF7_ContactForm {
                public function prop($key) { if ($key === "form") return "[email* email]"; return null; }
            }');
            $form = new \WPCF7_ContactForm();
        }
        $cf7Service = new \CTM\Service\CF7Service();
        $fields = $cf7Service->getFormFields($form);
        $this->assertIsArray($fields);
        $this->assertNotEmpty($fields);
        $this->assertEquals('email', $fields[0]['name']);
        $this->assertEquals('email', $fields[0]['type']);
    }

    public function testGetFormFieldsReturnsEmptyIfNoForm()
    {
        $cf7Service = new \CTM\Service\CF7Service();
        $fields = $cf7Service->getFormFields(null);
        $this->assertSame([], $fields);
    }

    public function testGetFormFieldsHandlesException()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
            $form = $this->getMockBuilder('WPCF7_ContactForm')
                ->disableOriginalConstructor()
                ->onlyMethods(['prop'])
                ->getMock();
            $form->method('prop')->will($this->throwException(new \Exception('fail')));
        } else {
            eval('class WPCF7_ContactForm {
                public function prop($key) { throw new \Exception("fail"); }
            }');
            $form = new \WPCF7_ContactForm();
        }
        $cf7Service = new \CTM\Service\CF7Service();
        $fields = $cf7Service->getFormFields($form);
        $this->assertSame([], $fields);
    }

    public function testProcessSubmissionWithAddressAndCheckboxAndFile()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
            $form = new \WPCF7_ContactForm();
        } else {
            eval('class WPCF7_ContactForm {
                public function id() { return 2; }
                public function title() { return "Form2"; }
                public function prop($key) { if ($key === "form") return "[text* address_street][checkbox interests][file upload]"; return null; }
            }');
            $form = new \WPCF7_ContactForm();
        }
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
        $_SERVER['HTTP_REFERER'] = 'http://referrer';
        $_SERVER['REQUEST_URI'] = '/form2';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_GET = [];
        $data = [
            'address_street' => '123 Main St',
            'interests' => ['A', 'B'],
            'upload' => 'http://localhost/file.pdf',
        ];
        $cf7Service = new \CTM\Service\CF7Service();
        $result = $cf7Service->processSubmission($form, $data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('address', $result['fields']);
        $this->assertEquals('123 Main St', $result['fields']['address']['street']);
        $this->assertEquals(['A', 'B'], $result['fields']['interests']);
        $this->assertEquals('http://localhost/file.pdf', $result['fields']['upload']);
    }

    public function testProcessSubmissionWithUtmParameters()
    {
        if (class_exists('WPCF7_ContactForm')) {
            if (!method_exists('WPCF7_ContactForm', 'prop')) {
                $this->markTestSkipped('WPCF7_ContactForm exists but does not have prop() method.');
            }
        } else {
            eval('class WPCF7_ContactForm {
                public function id() { return 3; }
                public function title() { return "Form3"; }
                public function prop($key) { if ($key === "form") return "[text* name]"; return null; }
            }');
        }
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
        $_SERVER['REQUEST_URI'] = '/form3';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_GET = [
            'utm_source' => 'google',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'spring',
            'utm_term' => 'flowers',
            'utm_content' => 'ad1',
        ];
        $data = ['name' => 'Alice'];
        $cf7Service = new \CTM\Service\CF7Service();
        $result = $cf7Service->processSubmission(new \WPCF7_ContactForm(), $data);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('utm_parameters', $result);
        $this->assertEquals('google', $result['utm_parameters']['utm_source']);
        $this->assertEquals('cpc', $result['utm_parameters']['utm_medium']);
        $this->assertEquals('spring', $result['utm_parameters']['utm_campaign']);
        $this->assertEquals('flowers', $result['utm_parameters']['utm_term']);
        $this->assertEquals('ad1', $result['utm_parameters']['utm_content']);
    }

    public function testPrivateMethodsViaReflection()
    {
        $cf7Service = new \CTM\Service\CF7Service();
        // mapFormFields
        $ref = new \ReflectionClass($cf7Service);
        $mapMethod = $ref->getMethod('mapFormFields');
        $mapMethod->setAccessible(true);
        $fields = $mapMethod->invoke($cf7Service, ['foo' => 'bar'], []);
        $this->assertEquals(['foo' => 'bar'], $fields);
        // extractFieldLabel
        $labelMethod = $ref->getMethod('extractFieldLabel');
        $labelMethod->setAccessible(true);
        $this->assertEquals('Placeholder', $labelMethod->invoke($cf7Service, 'placeholder "Placeholder"', 'field'));
        $this->assertEquals('Watermark', $labelMethod->invoke($cf7Service, 'watermark "Watermark"', 'field'));
        $this->assertEquals('Field', $labelMethod->invoke($cf7Service, '', 'field'));
        // normalizeFieldType
        $normMethod = $ref->getMethod('normalizeFieldType');
        $normMethod->setAccessible(true);
        $this->assertEquals('text', $normMethod->invoke($cf7Service, 'text'));
        $this->assertEquals('phone', $normMethod->invoke($cf7Service, 'tel'));
        $this->assertEquals('text', $normMethod->invoke($cf7Service, 'unknown'));
        // sanitizeFieldValue
        $sanitizeMethod = $ref->getMethod('sanitizeFieldValue');
        $sanitizeMethod->setAccessible(true);
        $this->assertEquals('abc', $sanitizeMethod->invoke($cf7Service, 'abc'));
        $this->assertEquals('a, b', $sanitizeMethod->invoke($cf7Service, ['a', 'b']));
        // extractUtmParameters
        $_GET = ['utm_source' => 'src'];
        $utmMethod = $ref->getMethod('extractUtmParameters');
        $utmMethod->setAccessible(true);
        $this->assertEquals(['utm_source' => 'src'], $utmMethod->invoke($cf7Service));
        // getClientIpAddress
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';
        $ipMethod = $ref->getMethod('getClientIpAddress');
        $ipMethod->setAccessible(true);
        $this->assertEquals('1.2.3.4', $ipMethod->invoke($cf7Service));
    }


} 