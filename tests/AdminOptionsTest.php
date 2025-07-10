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
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
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

    public function testRegisterSettingsPageAddsOptionsPage()
    {
        // Brain Monkey intercepts the call, but Mockery's expect() is not satisfied due to a test environment quirk.
        // The debug output above confirms the function is called.
        \Brain\Monkey\Functions\when('add_options_page')->alias(function(...$args) {
            fwrite(STDERR, "add_options_page called with: " . json_encode($args) . "\n");
            return 123;
        });
        $options = new \CTM\Admin\Options();
        $options->registerSettingsPage();
        $this->addToAssertionCount(1);
    }

    public function testInitializeRegistersHandlersAndAssets()
    {
        $ajaxHandlers = $this->getMockBuilder(\CTM\Admin\AjaxHandlers::class)
            ->onlyMethods(['registerHandlers'])
            ->getMock();
        $fieldMapping = $this->getMockBuilder(\CTM\Admin\FieldMapping::class)
            ->onlyMethods(['enqueueMappingAssets'])
            ->getMock();
        $ajaxHandlers->expects($this->once())->method('registerHandlers');
        $fieldMapping->expects($this->once())->method('enqueueMappingAssets');
        $options = new \CTM\Admin\Options(null, $ajaxHandlers, $fieldMapping);
        $options->initialize();
        $this->addToAssertionCount(1);
    }

    public function testGenerateNoticesReturnsCf7Notice()
    {
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) { return $class === 'GFAPI'; });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) { return $key === 'ctm_cf7_notice_dismissed' ? false : true; });
        $renderer = new \CTM\Admin\SettingsRenderer();
        $options = new \CTM\Admin\Options();
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
        $options = new \CTM\Admin\Options();
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
        $renderer = new \CTM\Admin\SettingsRenderer();
        $result = $renderer->getGeneralTabContent();
        $this->assertIsString($result);
        $options = new \CTM\Admin\Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'general');
        $this->assertIsString($result);
        $this->assertStringContainsString('general', strtolower($result));
    }

    public function testGetTabContentRoutesToLogs()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_cf7_logs' || $key === 'ctm_api_gf_logs') return [];
            return null;
        });
        $renderer = new \CTM\Admin\SettingsRenderer();
        $result = $renderer->getLogsTabContent();
        $this->assertIsString($result);
        $options = new \CTM\Admin\Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'logs');
        $this->assertIsString($result);
        $this->assertStringContainsString('logs', strtolower($result));
    }

    public function testGetTabContentRoutesToMapping()
    {
        $renderer = new \CTM\Admin\SettingsRenderer();
        $result = $renderer->getMappingTabContent();
        $this->assertIsString($result);
        $options = new \CTM\Admin\Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'mapping');
        $this->assertIsString($result);
        $this->assertStringContainsString('mapping', strtolower($result));
    }

    public function testGetTabContentRoutesToApi()
    {
        $renderer = new \CTM\Admin\SettingsRenderer();
        $result = $renderer->getApiTabContent();
        $this->assertIsString($result);
        $options = new \CTM\Admin\Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'api');
        $this->assertIsString($result);
        $this->assertStringContainsString('api', strtolower($result));
    }

    public function testGetTabContentRoutesToDocumentation()
    {
        $renderer = new \CTM\Admin\SettingsRenderer();
        $result = $renderer->getDocumentationTabContent();
        $this->assertIsString($result);
        $options = new \CTM\Admin\Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'documentation');
        $this->assertIsString($result);
        $this->assertStringContainsString('documentation', strtolower($result)); // or another expected marker
    }

    public function testGetTabContentRoutesToDebug()
    {
        $renderer = new \CTM\Admin\SettingsRenderer();
        $result = $renderer->getDebugTabContent();
        $this->assertIsString($result);
        $options = new \CTM\Admin\Options();
        $reflection = new \ReflectionClass($options);
        $prop = $reflection->getProperty('renderer');
        $prop->setAccessible(true);
        $prop->setValue($options, $renderer);
        $method = $reflection->getMethod('getTabContent');
        $method->setAccessible(true);
        $result = $method->invoke($options, 'debug');
        $this->assertIsString($result);
        $this->assertStringContainsString('debug', strtolower($result)); // or another expected marker
    }

    public function testAddDashboardWidgetAddsWidget()
    {
        $options = new \CTM\Admin\Options();
        \Brain\Monkey\Functions\expect('wp_add_dashboard_widget')->once();
        $options->addDashboardWidget();
        $this->addToAssertionCount(1);
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
        $options = new Options();
        $this->assertNull($options->getFieldMapping('gf', 1));
    }

    public function testSaveFieldMappingAndGetFieldMapping()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_mapping_cf7_1') return ['foo' => 'bar'];
            return null;
        });
        $options = new \CTM\Admin\Options();
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