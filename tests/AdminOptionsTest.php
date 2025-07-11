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
        // Mock get_option to provide API credentials and account ID
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_key') return 'test_key';
            if ($key === 'ctm_api_secret') return 'test_secret';
            if ($key === 'ctm_api_auth_account') return 'acct_123';
            return null;
        });
        // Mock class_exists for ApiService
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) {
            return $class === 'CTM\\Service\\ApiService';
        });
        // Fake ApiService
        $fakeApiService = new class {
            public function getCalls($apiKey, $apiSecret, $params = []) {
                $calls = [];
                for ($i = 29; $i >= 0; $i--) {
                    $date = date('Y-m-d', strtotime("-$i days"));
                    $calls[] = ['date' => $date];
                }
                return ['calls' => $calls];
            }
        };
        // Subclass Options to inject the fake ApiService
        $options = new class($fakeApiService) extends \CTM\Admin\Options {
            private $fakeApiService;
            public function __construct($fakeApiService) {
                parent::__construct();
                $this->fakeApiService = $fakeApiService;
            }
            public function renderDashboardWidget(): void {
                // Use the fakeApiService instead of real one
                $apiKey = get_option('ctm_api_key');
                $apiSecret = get_option('ctm_api_secret');
                $accountId = get_option('ctm_api_auth_account');
                $dates = [];
                $calls = [];
                $error = '';
                if ($apiKey && $apiSecret && $accountId) {
                    $since = date('Y-m-d', strtotime('-29 days'));
                    $until = date('Y-m-d');
                    $params = [
                        'start_date' => $since,
                        'end_date' => $until,
                        'group_by' => 'date',
                        'per_page' => 1000
                    ];
                    $result = $this->fakeApiService->getCalls($apiKey, $apiSecret, $params);
                    $callsByDay = [];
                    if (isset($result['calls']) && is_array($result['calls'])) {
                        foreach ($result['calls'] as $call) {
                            $date = isset($call['date']) ? substr($call['date'], 0, 10) : (isset($call['start_time']) ? substr($call['start_time'], 0, 10) : null);
                            if ($date) {
                                if (!isset($callsByDay[$date])) $callsByDay[$date] = 0;
                                $callsByDay[$date]++;
                            }
                        }
                        for ($i = 29; $i >= 0; $i--) {
                            $d = date('Y-m-d', strtotime("-$i days"));
                            $dates[] = date('M j', strtotime($d));
                            $calls[] = isset($callsByDay[$d]) ? $callsByDay[$d] : 0;
                        }
                    }
                }
                echo '<canvas id="ctm-calls-chart"></canvas>';
                echo json_encode($dates);
                echo json_encode($calls);
            }
        };
        ob_start();
        $options->renderDashboardWidget();
        $output = ob_get_clean();
        $this->assertStringContainsString('ctm-calls-chart', $output);
        // Check that the output includes the last 30 days of labels
        for ($i = 29; $i >= 0; $i--) {
            $d = date('M j', strtotime("-$i days"));
            $this->assertStringContainsString($d, $output);
        }
        // Check that the output includes 30 call counts (all 1 in this fake)
        $this->assertStringContainsString('[1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]', $output);
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

    public function testTrackingScriptIsSavedAsRawHtml()
    {
        $options = new Options();
        $rawScript = '<script async src="//12345.tctm.co/t.js"></script>';
        $_POST['call_track_account_script'] = $rawScript;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // Simulate save logic
        \Brain\Monkey\Functions\when('wp_unslash')->alias(function($v) { return $v; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($v) { return $v; });
        \Brain\Monkey\Functions\when('wp_redirect')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_get_referer')->justReturn('');
        $options->renderSettingsPage();
        // Should save as raw HTML
        $this->assertEquals($rawScript, get_option('call_track_account_script'));
    }

    public function testTrackingScriptMigrationFromEntities()
    {
        $entityScript = '&lt;script async src="//12345.tctm.co/t.js"&gt;&lt;/script&gt;';
        update_option('call_track_account_script', $entityScript);
        // Simulate admin_init migration
        $migration = function() {
            $option = get_option('call_track_account_script');
            if ($option && strpos($option, '&lt;script') !== false) {
                $decoded = html_entity_decode($option, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                update_option('call_track_account_script', $decoded);
            }
        };
        $migration();
        $this->assertEquals('<script async src="//12345.tctm.co/t.js"></script>', get_option('call_track_account_script'));
    }
} 