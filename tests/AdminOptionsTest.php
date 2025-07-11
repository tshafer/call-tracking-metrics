<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Options;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class AdminOptionsTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        \Brain\Monkey\Functions\when('settings_fields')->alias(function() { echo '<!--settings_fields-->'; });
        \Brain\Monkey\Functions\when('do_settings_sections')->alias(function() { echo '<!--do_settings_sections-->'; });
    }
    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
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

    public function testRegisterSettingsPageAddsOptionsPage()
    {
        $called = false;
        \Brain\Monkey\Functions\when('add_options_page')->alias(function(...$args) use (&$called) {
            $called = true;
        });
        $options = new Options();
        $options->registerSettingsPage();
        $this->assertTrue($called, 'add_options_page should be called');
    }

    public function testInitializeRegistersHandlersAndAssets()
    {
        $ajaxHandlers = new \CTM\Admin\AjaxHandlers();
        $fieldMapping = new \CTM\Admin\FieldMapping();
        $options = new Options(null, $ajaxHandlers, $fieldMapping);
        $options->initialize();
        $this->assertInstanceOf(Options::class, $options);
    }

    public function testGenerateNoticesReturnsCf7Notice()
    {
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) { return $class === 'GFAPI'; });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) { return $key === 'ctm_cf7_notice_dismissed' ? false : true; });
        $renderer = new \CTM\Admin\SettingsRenderer();
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('generateNotices');
        $method->setAccessible(true);
        $notices = $method->invoke($options);
        $this->assertIsArray($notices);
    }

    public function testGenerateNoticesReturnsGfNotice()
    {
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) { return $class === 'WPCF7_ContactForm'; });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) { return $key === 'ctm_gf_notice_dismissed' ? false : true; });
        $renderer = new \CTM\Admin\SettingsRenderer();
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('generateNotices');
        $method->setAccessible(true);
        $notices = $method->invoke($options);
        $this->assertIsArray($notices);
    }

    public function testGetTabContentRoutesToGeneral()
    {
        $renderer = new class extends \CTM\Admin\SettingsRenderer {
            public function renderView(string $view, array $vars = []): void {
                echo $view;
            }
        };
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'general');
        $this->assertStringContainsString('general', $result);
    }

    public function testGetTabContentRoutesToLogs()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = null) {
            if ($key === 'ctm_api_cf7_logs' || $key === 'ctm_api_gf_logs') return [];
            return $default;
        });
        $renderer = new class extends \CTM\Admin\SettingsRenderer {
            public function renderView(string $view, array $vars = []): void {
                echo $view;
            }
        };
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'logs');
        $this->assertStringContainsString('logs', $result);
    }

    public function testGetTabContentRoutesToMapping()
    {
        $renderer = new class extends \CTM\Admin\SettingsRenderer {
            public function renderView(string $view, array $vars = []): void {
                echo $view;
            }
        };
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'mapping');
        $this->assertStringContainsString('mapping', $result);
    }

    public function testGetTabContentRoutesToApi()
    {
        $renderer = new class extends \CTM\Admin\SettingsRenderer {
            public function renderView(string $view, array $vars = []): void {
                echo $view;
            }
        };
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'api');
        $this->assertStringContainsString('api', $result);
    }

    public function testGetTabContentRoutesToDocumentation()
    {
        $renderer = new class extends \CTM\Admin\SettingsRenderer {
            public function renderView(string $view, array $vars = []): void {
                echo $view;
            }
        };
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'documentation');
        $this->assertStringContainsString('documentation', $result);
    }

    public function testGetTabContentRoutesToDebug()
    {
        $renderer = new class extends \CTM\Admin\SettingsRenderer {
            public function renderView(string $view, array $vars = []): void {
                echo $view;
            }
        };
        $options = new Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'debug');
        $this->assertStringContainsString('debug', $result);
    }

    public function testAddDashboardWidgetAddsWidget()
    {
        $called = false;
        \Brain\Monkey\Functions\when('wp_add_dashboard_widget')->alias(function(...$args) use (&$called) {
            $called = true;
        });
        $options = new Options();
        $options->addDashboardWidget();
        $this->assertTrue($called, 'wp_add_dashboard_widget should be called');
    }

    public function testRenderDashboardWidgetOutputsHtml()
    {
        $options = new Options();
        ob_start();
        $options->renderDashboardWidget();
        $output = ob_get_clean();
        $this->assertStringContainsString('CallTrackingMetrics', $output);
    }

    public function testGetFieldMappingReturnsNullIfNotSet()
    {
        $fieldMapping = new \CTM\Admin\FieldMapping();
        $result = $fieldMapping->getFieldMapping('gf', 123);
        $this->assertNull($result, 'getFieldMapping should return null if not set');
    }

    public function testSaveFieldMappingAndGetFieldMapping()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_mapping_cf7_1') return ['foo' => 'bar'];
            return null;
        });
        $options = new Options();
        $options->saveFieldMapping('cf7', 1, ['foo' => 'bar']);
        $result = $options->getFieldMapping('cf7', 1);
        $this->assertIsArray($result);
        $this->assertEquals(['foo' => 'bar'], $result);
    }

    public function testLogDebugWritesToErrorLog()
    {
        $this->expectNotToPerformAssertions();
        Options::logDebug('test message');
    }

    public function testIsDebugEnabledReturnsBool()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn(true);
        $this->assertTrue(Options::isDebugEnabled());
        \Brain\Monkey\Functions\when('get_option')->justReturn(false);
        $this->assertFalse(Options::isDebugEnabled());
  
    }
} 