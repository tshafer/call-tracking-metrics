<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\SystemAjax;
use Brain\Monkey;

class SystemAjaxTestLoggingSystemStub extends \CTM\Admin\LoggingSystem {}
class SystemAjaxTestSettingsRendererStub extends \CTM\Admin\SettingsRenderer {}

class AdminAjaxSystemAjaxTest extends TestCase
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
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('5.8');
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_upload_dir')->justReturn(['basedir' => '/tmp']);
        \Brain\Monkey\Functions\when('is_writable')->justReturn(true);
        \Brain\Monkey\Functions\when('extension_loaded')->justReturn(true);
        \Brain\Monkey\Functions\when('version_compare')->justReturn(1);
        \Brain\Monkey\Functions\when('size_format')->alias(function($v){return (string)$v;});
        \Brain\Monkey\Functions\when('is_ssl')->justReturn(true);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class){return false;});
        \Brain\Monkey\Functions\when('wp_remote_get')->justReturn(['response' => ['code' => 200]]);
        \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200);
        \Brain\Monkey\Functions\when('wp_remote_head')->justReturn(['response' => ['code' => 200]]);
        \Brain\Monkey\Functions\when('wp_get_theme')->justReturn(new class {
            public function get($k) { return 'theme'; }
            public function parent() { return null; }
            public function get_stylesheet() { return 'theme'; }
        });
        \Brain\Monkey\Functions\when('is_multisite')->justReturn(false);
        \Brain\Monkey\Functions\when('get_locale')->justReturn('en_US');
        \Brain\Monkey\Functions\when('WP_DEBUG')->justReturn(false);
        \Brain\Monkey\Functions\when('WP_MEMORY_LIMIT')->justReturn('256M');
        \Brain\Monkey\Functions\when('get_num_queries')->justReturn(1);
        \Brain\Monkey\Functions\when('memory_get_usage')->justReturn(1024);
        \Brain\Monkey\Functions\when('memory_get_peak_usage')->justReturn(2048);
        \Brain\Monkey\Functions\when('admin_url')->justReturn('http://example.com/wp-admin/');
        \Brain\Monkey\Functions\when('DB_HOST')->justReturn('localhost');
        \Brain\Monkey\Functions\when('DB_CHARSET')->justReturn('utf8');
        \Brain\Monkey\Functions\when('DB_NAME')->justReturn('wordpress');
        \Brain\Monkey\Functions\when('wp_convert_hr_to_bytes')->alias(function($v){return 256*1024*1024;});
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    public function testRunSystemHealthChecksReturnsArray()
    {
        $loggingSystem = new SystemAjaxTestLoggingSystemStub();
        $renderer = new SystemAjaxTestSettingsRendererStub();
        $systemAjax = new SystemAjax();
        $method = (new \ReflectionClass($systemAjax))->getMethod('runSystemHealthChecks');
        $method->setAccessible(true);
        $result = $method->invoke($systemAjax);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }
} 