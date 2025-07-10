<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Options;
use Brain\Monkey;

class AdminOptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('register_setting')->justReturn(null);
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_redirect')->justReturn(null);
        \Brain\Monkey\Functions\when('add_options_page')->justReturn(1);
        \Brain\Monkey\Functions\when('admin_url')->justReturn('http://example.com/wp-admin/');
        \Brain\Monkey\Functions\when('wp_get_referer')->justReturn('http://example.com');
        \Brain\Monkey\Functions\when('add_query_arg')->justReturn('http://example.com');
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('5.8');
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('esc_html')->alias(function($v){return $v;});
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $options = new Options();
        $this->assertInstanceOf(Options::class, $options);
    }
    public function testRegisterSettingsDoesNotThrow()
    {
        $options = new Options();
        $this->expectNotToPerformAssertions();
        $options->registerSettings();
    }
} 