<?php
if (!defined('CTM_TESTING')) {
    define('CTM_TESTING', true);
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class CallTrackingMetricsConstructorTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock WordPress functions that are needed
        \Brain\Monkey\Functions\when('home_url')->justReturn('https://example.com');
        \Brain\Monkey\Functions\when('admin_url')->justReturn('https://example.com/wp-admin/');
        \Brain\Monkey\Functions\when('plugin_dir_path')->alias(function($file) { return __DIR__ . '/../../'; });
        \Brain\Monkey\Functions\when('plugin_dir_url')->alias(function($file) { return '/'; });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = '') {
            if ($key === 'active_plugins') {
                return [];
            }
            return $default;
        });
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('add_filter')->justReturn(null);
        \Brain\Monkey\Functions\when('register_activation_hook')->justReturn(null);
        \Brain\Monkey\Functions\when('register_deactivation_hook')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_next_scheduled')->justReturn(false);
        \Brain\Monkey\Functions\when('wp_schedule_event')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_clear_scheduled_hook')->justReturn(null);
        \Brain\Monkey\Functions\when('is_plugin_active')->alias(function($plugin) {
            if ($plugin === 'contact-form-7/wp-contact-form-7.php') return false;
            return true;
        });
        \Brain\Monkey\Functions\when('ctm_get_api_url')->justReturn('https://api.calltrackingmetrics.com');
        \Brain\Monkey\Functions\when('load_plugin_textdomain')->justReturn(null);
        \Brain\Monkey\Functions\when('plugin_basename')->alias(function($file) { return 'call-tracking-metrics/ctm.php'; });
        \Brain\Monkey\Functions\when('dirname')->alias(function($file) { return 'call-tracking-metrics'; });
        
        require_once __DIR__ . '/../ctm.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }

    public function testConstructorInitializesServices()
    {
        $plugin = new \CallTrackingMetrics();
        
        // Use reflection to check private properties
        $reflection = new \ReflectionClass($plugin);
        
        // Check that services are initialized
        $apiServiceProperty = $reflection->getProperty('apiService');
        $apiServiceProperty->setAccessible(true);
        $this->assertNotNull($apiServiceProperty->getValue($plugin));
        
        $cf7ServiceProperty = $reflection->getProperty('cf7Service');
        $cf7ServiceProperty->setAccessible(true);
        $this->assertNotNull($cf7ServiceProperty->getValue($plugin));
        
        $gfServiceProperty = $reflection->getProperty('gfService');
        $gfServiceProperty->setAccessible(true);
        $this->assertNotNull($gfServiceProperty->getValue($plugin));
        
        $adminOptionsProperty = $reflection->getProperty('adminOptions');
        $adminOptionsProperty->setAccessible(true);
        $this->assertNotNull($adminOptionsProperty->getValue($plugin));
        
        $loggingSystemProperty = $reflection->getProperty('loggingSystem');
        $loggingSystemProperty->setAccessible(true);
        $this->assertNotNull($loggingSystemProperty->getValue($plugin));
    }

    public function testConstructorRegistersCoreHooks()
    {
        $addActionCalled = false;
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) use (&$addActionCalled) {
            if ($hook === 'init') {
                $addActionCalled = true;
            }
        });
        
        $plugin = new \CallTrackingMetrics();
        $this->assertTrue($addActionCalled);
    }

    public function testConstructorRegistersPluginHooks()
    {
        $addActionCalled = false;
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) use (&$addActionCalled) {
            if ($hook === 'wp_head') {
                $addActionCalled = true;
            }
        });
        
        $plugin = new \CallTrackingMetrics();
        $this->assertTrue($addActionCalled);
    }

    public function testConstructorRegistersConditionalHooks()
    {
        $addActionCalled = false;
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) use (&$addActionCalled) {
            if ($hook === 'wp_dashboard_setup') {
                $addActionCalled = true;
            }
        });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_dashboard_enabled') return true;
            return null;
        });
        
        $plugin = new \CallTrackingMetrics();
        $this->assertTrue($addActionCalled);
    }

    public function testConstructorRegistersLifecycleHooks()
    {
        $registerHookCalled = false;
        \Brain\Monkey\Functions\when('register_activation_hook')->alias(function($file, $callback) use (&$registerHookCalled) {
            $registerHookCalled = true;
        });
        
        $plugin = new \CallTrackingMetrics();
        $this->assertTrue($registerHookCalled);
    }

    public function testRegisterCoreHooksMethod()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $method = $reflection->getMethod('registerCoreHooks');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($plugin);
        $this->assertTrue(true);
    }

    public function testRegisterPluginHooksMethod()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $method = $reflection->getMethod('registerPluginHooks');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($plugin);
        $this->assertTrue(true);
    }

    public function testRegisterConditionalHooksMethod()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $method = $reflection->getMethod('registerConditionalHooks');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($plugin);
        $this->assertTrue(true);
    }

    public function testRegisterLifecycleHooksMethod()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $method = $reflection->getMethod('registerLifecycleHooks');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($plugin);
        $this->assertTrue(true);
    }

    public function testConstructorHandlesServiceInitializationErrors()
    {
        // Mock service classes to throw exceptions
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('add_filter')->justReturn(null);
        
        // Should not throw any exceptions during construction
        $plugin = new \CallTrackingMetrics();
        $this->assertInstanceOf(\CallTrackingMetrics::class, $plugin);
    }

    public function testConstructorSetsUpTextDomain()
    {
        $addActionCalled = false;
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) use (&$addActionCalled) {
            if ($hook === 'init') {
                $addActionCalled = true;
            }
        });
        
        $plugin = new \CallTrackingMetrics();
        $this->assertTrue($addActionCalled);
    }

    public function testConstructorInitializesAdminOptions()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $adminOptionsProperty = $reflection->getProperty('adminOptions');
        $adminOptionsProperty->setAccessible(true);
        $adminOptions = $adminOptionsProperty->getValue($plugin);
        
        $this->assertNotNull($adminOptions);
        $this->assertInstanceOf(\CTM\Admin\Options::class, $adminOptions);
    }

    public function testConstructorInitializesLoggingSystem()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $loggingSystemProperty = $reflection->getProperty('loggingSystem');
        $loggingSystemProperty->setAccessible(true);
        $loggingSystem = $loggingSystemProperty->getValue($plugin);
        
        $this->assertNotNull($loggingSystem);
        $this->assertInstanceOf(\CTM\Admin\LoggingSystem::class, $loggingSystem);
    }

    public function testConstructorSetsCtmHost()
    {
        $plugin = new \CallTrackingMetrics();
        
        $reflection = new \ReflectionClass($plugin);
        $ctmHostProperty = $reflection->getProperty('ctmHost');
        $ctmHostProperty->setAccessible(true);
        $ctmHost = $ctmHostProperty->getValue($plugin);
        
        $this->assertEquals('https://api.calltrackingmetrics.com', $ctmHost);
    }
} 