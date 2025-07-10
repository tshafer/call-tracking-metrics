<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\LogAjax;
use Brain\Monkey;

class LogAjaxTestLoggingSystemStub extends \CTM\Admin\LoggingSystem {}

class AdminAjaxLogAjaxTest extends TestCase
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
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('is_email')->justReturn(true);
        \Brain\Monkey\Functions\when('esc_html')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('Test Site');
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(true);
        \Brain\Monkey\Functions\when('tempnam')->justReturn('/tmp/ctm_log.csv');
        \Brain\Monkey\Functions\when('sys_get_temp_dir')->justReturn('/tmp');
        \Brain\Monkey\Functions\when('fopen')->justReturn(true);
        \Brain\Monkey\Functions\when('fputcsv')->justReturn(true);
        \Brain\Monkey\Functions\when('fclose')->justReturn(true);
        \Brain\Monkey\Functions\when('unlink')->justReturn(true);
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $loggingSystem = new LogAjaxTestLoggingSystemStub();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $this->assertInstanceOf(LogAjax::class, $logAjax);
    }
} 