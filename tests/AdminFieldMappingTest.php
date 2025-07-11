<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\FieldMapping;
use Brain\Monkey;

use CTM\Tests\Traits\MonkeyTrait;

class AdminFieldMappingTest extends TestCase
{
    use MonkeyTrait;
    private $options = [];
    protected function setUp(): void
    {
        parent::setUp();
        $this->initalMonkey();
        $this->options = [];
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) {
            $this->options[$key] = $value;
            return true;
        });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            return $this->options[$key] ?? $default;
        });
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