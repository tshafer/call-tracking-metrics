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

    // Add more tests for edge cases, error handling, and output content as needed to reach 25
} 