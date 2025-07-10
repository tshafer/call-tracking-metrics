<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\SettingsRenderer;
use Brain\Monkey;

class AdminSettingsRendererTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('5.8');
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class){return false;});
        \Brain\Monkey\Functions\when('esc_html')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_get_theme')->justReturn(new class {
            public function get($k) { return 'theme'; }
            public function parent() { return null; }
            public function get_stylesheet() { return 'theme'; }
        });
        \Brain\Monkey\Functions\when('size_format')->alias(function($v){return (string)$v;});
        \Brain\Monkey\Functions\when('is_multisite')->justReturn(false);
        \Brain\Monkey\Functions\when('admin_url')->justReturn('http://example.com/wp-admin/');
        \Brain\Monkey\Functions\when('get_locale')->justReturn('en_US');
        \Brain\Monkey\Functions\when('WP_DEBUG')->justReturn(false);
        \Brain\Monkey\Functions\when('WP_MEMORY_LIMIT')->justReturn('256M');
        \Brain\Monkey\Functions\when('get_num_queries')->justReturn(1);
        \Brain\Monkey\Functions\when('memory_get_usage')->justReturn(1024);
        \Brain\Monkey\Functions\when('memory_get_peak_usage')->justReturn(2048);
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn('/tmp/');
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testGetGeneralTabContentReturnsString()
    {
        $renderer = new SettingsRenderer();
        $result = $renderer->getGeneralTabContent();
        $this->assertIsString($result);
    }
} 