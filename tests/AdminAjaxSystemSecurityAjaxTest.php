<?php

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\SystemSecurityAjax;
use Brain\Monkey;
use CTM\Admin\LoggingSystem;
use CTM\Admin\SettingsRenderer;
use CTM\Tests\Traits\MonkeyTrait;

class AdminAjaxSystemSecurityAjaxTest extends TestCase
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

    public function testCanBeConstructed()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $ajax = new SystemSecurityAjax($loggingSystem, $renderer);
        $this->assertInstanceOf(SystemSecurityAjax::class, $ajax);
    }

    public function testRegisterHandlersAddsAction()
    {
        $calls = [];
        \Brain\Monkey\Functions\when('add_action')->alias(function(...$args) use (&$calls) { $calls[] = $args; });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $ajax = new SystemSecurityAjax($loggingSystem, $renderer);
        $ajax->registerHandlers();
        $this->assertNotEmpty($calls);
        $this->assertEquals('wp_ajax_ctm_security_scan', $calls[0][0]);
        $this->assertEquals([$ajax, 'ajaxSecurityScan'], $calls[0][1]);
    }

    public function testAjaxSecurityScanSuccessNoVuln()
    {
        $_POST['nonce'] = 'dummy';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([
            'akismet/akismet.php' => ['Name' => 'Akismet'],
            'some-plugin/some-plugin.php' => ['Name' => 'Some Plugin']
        ]);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called) { $called = $arg; });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $ajax = new SystemSecurityAjax($loggingSystem, $renderer);
        $ajax->ajaxSecurityScan();
        if ($called === null) { $called = []; }
        $this->assertIsArray($called);
        $this->assertArrayHasKey('results', $called);
        $this->assertEquals(100, $called['results']['security_score']);
        $this->assertEmpty($called['results']['vulnerabilities']);
    }

    public function testAjaxSecurityScanVulnerablePlugin()
    {
        $_POST['nonce'] = 'dummy';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([
            'hello.php' => ['Name' => 'Hello Dolly'],
            'akismet/akismet.php' => ['Name' => 'Akismet']
        ]);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called) { $called = $arg; });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $ajax = new SystemSecurityAjax($loggingSystem, $renderer);
        $ajax->ajaxSecurityScan();
        if ($called === null) { $called = []; }
        $this->assertIsArray($called);
        $this->assertArrayHasKey('results', $called);
        $this->assertLessThan(100, $called['results']['security_score']);
        $this->assertNotEmpty($called['results']['vulnerabilities']);
        $this->assertEquals('Vulnerable plugin detected', $called['results']['vulnerabilities'][0]['title']);
    }

    public function testAjaxSecurityScanEmptyPlugins()
    {
        $_POST['nonce'] = 'dummy';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([]);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called) { $called = $arg; });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $ajax = new SystemSecurityAjax($loggingSystem, $renderer);
        $ajax->ajaxSecurityScan();
        if ($called === null) { $called = []; }
        $this->assertIsArray($called);
        $this->assertArrayHasKey('results', $called);
        $this->assertEquals(100, $called['results']['security_score']);
        $this->assertEmpty($called['results']['vulnerabilities']);
    }

    public function testAjaxSecurityScanMultiplePluginsSomeVuln()
    {
        $_POST['nonce'] = 'dummy';
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([
            'hello.php' => ['Name' => 'Hello Dolly'],
            'akismet/akismet.php' => ['Name' => 'Akismet'],
            'some-plugin/some-plugin.php' => ['Name' => 'Some Plugin']
        ]);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called) { $called = $arg; });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $ajax = new SystemSecurityAjax($loggingSystem, $renderer);
        $ajax->ajaxSecurityScan();
        if ($called === null) { $called = []; }
        $this->assertIsArray($called);
        $this->assertArrayHasKey('results', $called);
        $this->assertLessThan(100, $called['results']['security_score']);
        $this->assertNotEmpty($called['results']['vulnerabilities']);
        $this->assertEquals('Vulnerable plugin detected', $called['results']['vulnerabilities'][0]['title']);
    }
} 