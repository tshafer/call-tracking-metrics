<?php
define('CTM_TESTING', true);

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class CallTrackingMetricsTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        $this->initalMonkey();
        require_once __DIR__ . '/../ctm.php';
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }

    public function testCanBeConstructed()
    {
        // Should not throw
        $plugin = new \CallTrackingMetrics();
        $this->assertInstanceOf(\CallTrackingMetrics::class, $plugin);
    }

    public function testRegistersCoreHooks()
    {
        $calls = [];
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback, ...$args) use (&$calls) {
            $calls[] = $hook;
        });
        \Brain\Monkey\Functions\when('add_filter')->alias(function($hook, $callback, ...$args) use (&$calls) {
            $calls[] = $hook;
        });
        \Brain\Monkey\Functions\when('register_activation_hook')->alias(function($file, $cb) use (&$calls) {
            $calls[] = 'activation';
        });
        \Brain\Monkey\Functions\when('register_deactivation_hook')->alias(function($file, $cb) use (&$calls) {
            $calls[] = 'deactivation';
        });
        // Dashboard widget enabled
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_api_dashboard_enabled') return true;
            return null;
        });
        $plugin = new \CallTrackingMetrics();
        $this->assertContains('admin_init', $calls);
        $this->assertContains('admin_menu', $calls);
        $this->assertContains('wp_head', $calls);
        $this->assertContains('init', $calls);
        $this->assertContains('wp_footer', $calls);
        $this->assertContains('gform_confirmation', $calls);
        $this->assertContains('wp_dashboard_setup', $calls);
        $this->assertContains('activation', $calls);
        $this->assertContains('deactivation', $calls);
    }

    public function testPrintTrackingScriptOutputsScript()
    {
        $plugin = new \CallTrackingMetrics();
        \Brain\Monkey\Functions\when('is_admin')->justReturn(false);
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'call_track_account_script') return '<script>console.log("test")</script>';
            return null;
        });
        ob_start();
        $plugin->printTrackingScript();
        $output = ob_get_clean();
        $this->assertStringContainsString('console.log', $output);
    }
} 