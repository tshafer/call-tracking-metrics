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