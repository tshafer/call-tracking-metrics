<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\FieldMapping;
use Brain\Monkey;

class AdminFieldMappingTest extends TestCase
{
    private $options = [];

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        // update_option will update the fake options array
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) {
            $this->options[$key] = $value;
            return true;
        });
        // get_option will read from the fake options array
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            return $this->options[$key] ?? $default;
        });
        \Brain\Monkey\Functions\when('delete_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class){return false;});
        \Brain\Monkey\Functions\when('maybe_unserialize')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('is_array')->alias(function($v){return is_array($v);});
        \Brain\Monkey\Functions\when('count')->alias(function($v){return is_array($v) ? count($v) : 0;});
        \Brain\Monkey\Functions\when('array_sum')->alias(function($v){return is_array($v) ? array_sum($v) : 0;});
        \Brain\Monkey\Functions\when('method_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('error_log')->justReturn(null);
        \Brain\Monkey\Functions\when('str_replace')->alias(function($search, $replace, $subject){return str_replace($search, $replace, $subject);});
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testSaveAndGetFieldMapping()
    {
        $fieldMapping = new FieldMapping();
        $formType = 'gf';
        $formId = '123';
        $mapping = ['email' => 'email_address'];
        $fieldMapping->saveFieldMapping($formType, $formId, $mapping);
        $result = $fieldMapping->getFieldMapping($formType, $formId);
        $this->assertEquals(['email' => 'email_address'], $result);
    }
} 