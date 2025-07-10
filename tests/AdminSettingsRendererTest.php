<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\SettingsRenderer;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class AdminSettingsRendererTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initalMonkey();
        
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testGetGeneralTabContentReturnsString()
    {
        $renderer = new SettingsRenderer(); // Optionally inject mocks if needed
        $result = $renderer->getGeneralTabContent();
        $this->assertIsString($result);
    }

    public function testRenderViewOutputsErrorIfViewMissing()
    {
        $renderer = new SettingsRenderer();
        ob_start();
        $renderer->renderView('nonexistent-view');
        $output = ob_get_clean();
        $this->assertStringContainsString('View not found', $output);
    }

    public function testRenderViewIncludesViewFile()
    {
        $renderer = new SettingsRenderer();
        $viewsDir = dirname(getcwd(), 2) . '/views';
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir);
        }
        $viewPath = $viewsDir . '/test-view.php';
        file_put_contents($viewPath, '<?php echo "Hello, $foo!";');
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn('/Users/tomshafer/Sites/wordpress/wp-content/plugins/call-tracking-metrics/');
        ob_start();
        $renderer->renderView('test-view', ['foo' => 'World']);
        $output = ob_get_clean();
        $this->assertStringContainsString('Hello, World!', $output);
        unlink($viewPath);
        // Remove the views directory if empty
        if (count(glob($viewsDir . '/*')) === 0) {
            rmdir($viewsDir);
        }
    }

    public function testGetGeneralTabContentReturnsStringWithApiStatusConnected()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_key') return 'key';
            if ($key === 'ctm_api_secret') return 'secret';
            return null;
        });
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) {
            if ($class === 'WPCF7_ContactForm' || $class === 'GFAPI') return false;
            return false;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getGeneralTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetGeneralTabContentReturnsStringWithApiStatusNotConnected()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getGeneralTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetLogsTabContentReturnsStringWithNoLogs()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn([]);
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getLogsTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetLogsTabContentReturnsStringWithLogs()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_cf7_logs') return [['id'=>1]];
            if ($key === 'ctm_api_gf_logs') return [['id'=>2]];
            return [];
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getLogsTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetMappingTabContentReturnsStringNoPlugins()
    {
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getMappingTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetMappingTabContentReturnsStringWithCF7()
    {
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) {
            if ($class === 'WPCF7_ContactForm') return true;
            return false;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        // Mock WPCF7_ContactForm::find to return a fake form
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm { public static function find() { $f = new self; return [$f]; } public function id() { return 1; } public function title() { return "CF7"; } }');
        }
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getMappingTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetMappingTabContentReturnsStringWithGF()
    {
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) {
            if ($class === 'GFAPI') return true;
            return false;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        // Mock GFAPI::get_forms to return a fake form
        if (!class_exists('GFAPI')) {
            eval('class GFAPI { public static function get_forms() { return [["id"=>2,"title"=>"GF"]]; } }');
        }
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getMappingTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetApiTabContentReturnsStringConnected()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_key') return 'key';
            if ($key === 'ctm_api_secret') return 'secret';
            return null;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getApiTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetApiTabContentReturnsStringNotConnected()
    {
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getApiTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetDocumentationTabContentReturnsString()
    {
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getDocumentationTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetDebugTabContentReturnsStringDebugEnabled()
    {
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = null) {
            if ($key === 'ctm_log_retention_days') return 7;
            if ($key === 'ctm_log_auto_cleanup') return true;
            if ($key === 'ctm_log_email_notifications') return true;
            if ($key === 'ctm_log_notification_email') return 'test@example.com';
            return $default;
        });
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getDebugTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testGetDebugTabContentReturnsStringDebugDisabled()
    {
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = null) {
            if ($key === 'ctm_log_retention_days') return 7;
            if ($key === 'ctm_log_auto_cleanup') return false;
            if ($key === 'ctm_log_email_notifications') return false;
            if ($key === 'ctm_log_notification_email') return '';
            return $default;
        });
        $renderer = new SettingsRenderer();
        ob_start();
        $result = $renderer->getDebugTabContent();
        ob_end_clean();
        $this->assertIsString($result);
    }

    public function testRenderViewWithSpecialCharacters() {
        $renderer = new SettingsRenderer();
        $pluginDir = rtrim(__DIR__, '/').'/';
        $viewsDir = realpath($pluginDir . '../../views');
        if ($viewsDir === false) {
            $viewsDir = $pluginDir . '../../views';
            if (!is_dir($viewsDir)) { mkdir($viewsDir, 0777, true); }
        }
        $viewPath = $viewsDir . '/specialchars-view.php';
        file_put_contents($viewPath, '<?php echo htmlspecialchars($text, ENT_QUOTES);');
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn($pluginDir);
        ob_start();
        $renderer->renderView('specialchars-view', ['text' => 'Tom & "Shafer" <test>']);
        $output = ob_get_clean();
        $this->assertStringContainsString('Tom &amp; &quot;Shafer&quot; &lt;test&gt;', $output);
        unlink($viewPath);
        if (count(glob($viewsDir . '/*')) === 0) { rmdir($viewsDir); }
    }

    public function testRenderViewWithPhpError() {
        $renderer = new SettingsRenderer();
        $pluginDir = rtrim(__DIR__, '/').'/';
        $viewsDir = realpath($pluginDir . '../../views');
        if ($viewsDir === false) {
            $viewsDir = $pluginDir . '../../views';
            if (!is_dir($viewsDir)) { mkdir($viewsDir, 0777, true); }
        }
        $viewPath = $viewsDir . '/error-view.php';
        file_put_contents($viewPath, '<?php trigger_error("Test error", E_USER_NOTICE); echo "ErrorView";');
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn($pluginDir);
        ob_start();
        $renderer->renderView('error-view');
        $output = ob_get_clean();
        $this->assertStringContainsString('ErrorView', $output);
        unlink($viewPath);
        if (count(glob($viewsDir . '/*')) === 0) { rmdir($viewsDir); }
    }

    public function testGetGeneralTabContentNoApiKeyOrService() {
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer(null, null);
        $result = $renderer->getGeneralTabContent();
        $this->assertIsString($result);
    }

    public function testGetLogsTabContentWithManyLogs() {
        $logs = array_map(function($i){ return ['id'=>$i]; }, range(1, 50));
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) use ($logs) {
            if ($key === 'ctm_api_cf7_logs') return $logs;
            if ($key === 'ctm_api_gf_logs') return $logs;
            return [];
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        $renderer = new SettingsRenderer();
        $result = $renderer->getLogsTabContent();
        $this->assertIsString($result);
    }

    public function testGetMappingTabContentWithBothPlugins() {
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) {
            if ($class === 'WPCF7_ContactForm' || $class === 'GFAPI') return true;
            return false;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm { public static function find() { $f = new self; return [$f]; } public function id() { return 1; } public function title() { return "CF7"; } }');
        }
        if (!class_exists('GFAPI')) {
            eval('class GFAPI { public static function get_forms() { return [["id"=>2,"title"=>"GF"]]; } }');
        }
        $renderer = new SettingsRenderer();
        $result = $renderer->getMappingTabContent();
        $this->assertIsString($result);
    }

    public function testGetApiTabContentNoAccountInfo() {
        $mockApiService = new class {
            public function getAccountInfo($key, $secret) { return null; }
        };
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_key') return 'key';
            if ($key === 'ctm_api_secret') return 'secret';
            return null;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(dirname(__DIR__, 2) . '/');
        $renderer = new SettingsRenderer($mockApiService);
        $result = $renderer->getApiTabContent();
        $this->assertIsString($result);
    }

    public function testGetApiTabContentApiServiceThrows() {
        $mockApiService = new class {
            public function getAccountInfo($key, $secret) { throw new \Exception('fail'); }
        };
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_key') return 'key';
            if ($key === 'ctm_api_secret') return 'secret';
            return null;
        });
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(dirname(__DIR__, 2) . '/');
        $renderer = new SettingsRenderer($mockApiService);
        $this->expectException(\Exception::class);
        $renderer->getApiTabContent();
    }

    public function testGetDebugTabContentLogStatsThrows() {
        $this->markTestSkipped('Cannot reliably mock static method isDebugEnabled with Brain Monkey/PHP limitations.');
    }

    public function testGetMappingTabContentWithEmptyCTMFields() {
        // Skipped: Cannot override private getCTMFields without runkit or making it protected/public.
        $this->markTestSkipped('Cannot override private getCTMFields for this test without runkit or changing method visibility.');
    }

    public function testRenderViewWithEmptyFile() {
        $renderer = new SettingsRenderer();
        $viewsDir = sys_get_temp_dir() . '/views';
        if (!is_dir($viewsDir)) { mkdir($viewsDir); }
        $viewPath = $viewsDir . '/empty-view.php';
        file_put_contents($viewPath, '');
        \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn(sys_get_temp_dir() . '/');
        ob_start();
        $renderer->renderView('views/empty-view');
        $output = ob_get_clean();
        $this->assertSame('', $output);
        unlink($viewPath);
        if (count(glob($viewsDir . '/*')) === 0) { rmdir($viewsDir); }
    }

    // Add more tests for edge cases, error handling, and output content as needed to reach 25
} 