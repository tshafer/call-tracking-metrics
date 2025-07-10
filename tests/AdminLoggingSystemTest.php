<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\LoggingSystem;
use Brain\Monkey;

class AdminLoggingSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('get_option')->justReturn(false);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('delete_option')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(true);
        \Brain\Monkey\Functions\when('current_time')->justReturn('2024-01-01 00:00:00');
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('Test Site');
        \Brain\Monkey\Functions\when('get_userdata')->justReturn((object)['user_login' => 'admin']);
        \Brain\Monkey\Functions\when('size_format')->alias(function($v){return (string)$v;});
        \Brain\Monkey\Functions\when('wp_next_scheduled')->justReturn(false);
        \Brain\Monkey\Functions\when('wp_schedule_event')->justReturn(true);
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_clear_scheduled_hook')->justReturn(null);
        \Brain\Monkey\Functions\when('str_repeat')->alias(function($v, $n){return str_repeat($v, $n);});
        \Brain\Monkey\Functions\when('is_array')->alias(function($v){return is_array($v);});
        \Brain\Monkey\Functions\when('array_filter')->alias(function($v, $cb = null){return is_array($v) ? array_filter($v, $cb) : [];});
        \Brain\Monkey\Functions\when('array_values')->alias(function($v){return is_array($v) ? array_values($v) : [];});
        \Brain\Monkey\Functions\when('number_format')->alias(function($v){return (string)$v;});
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $loggingSystem = new LoggingSystem();
        $this->assertInstanceOf(LoggingSystem::class, $loggingSystem);
    }
    public function testIsDebugEnabledReturnsBool()
    {
        $result = LoggingSystem::isDebugEnabled();
        $this->assertIsBool($result);
    }
} 