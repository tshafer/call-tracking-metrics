<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\FormAjax;
use Brain\Monkey;

class AdminAjaxFormAjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $formAjax = new FormAjax();
        $this->assertInstanceOf(FormAjax::class, $formAjax);
    }
} 