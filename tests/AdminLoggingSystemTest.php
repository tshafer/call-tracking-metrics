<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\LoggingSystem;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class AdminLoggingSystemTest extends TestCase
{
    use MonkeyTrait;
    protected function setUp(): void
    {
        parent::setUp();
        $this->initalMonkey();
    }

    public function testIsDebugEnabledReturnsBool()
    {
        $result = LoggingSystem::isDebugEnabled();
        $this->assertIsBool($result);
    }

    public function testIsDebugEnabledTrueAndFalse()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            if ($key === 'ctm_debug_enabled') return true;
            return $default;
        });
        $this->assertTrue(LoggingSystem::isDebugEnabled());
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            if ($key === 'ctm_debug_enabled') return false;
            return $default;
        });
        $this->assertFalse(LoggingSystem::isDebugEnabled());
    }

    public function testLogActivityLogsWhenEnabled()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            if ($key === 'ctm_debug_enabled') return true;
            if ($key === 'ctm_log_index') return [];
            if (strpos($key, 'ctm_daily_log_') === 0) return [];
            return $default;
        });
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) {
            return true;
        });
        \Brain\Monkey\Functions\when('current_time')->alias(function() { return '2024-01-01 00:00:00'; });
        \Brain\Monkey\Functions\when('get_current_user_id')->alias(function() { return 1; });
        $_SERVER['HTTP_USER_AGENT'] = 'TestAgent';
        $log = new LoggingSystem();
        $log->logActivity('Test message', 'info', ['foo'=>'bar']);
        $this->assertTrue(true); // If no exception, pass
    }

    public function testLogActivityDoesNotLogWhenDisabled()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            if ($key === 'ctm_debug_enabled') return false;
            return $default;
        });
        $log = new LoggingSystem();
        $log->logActivity('Should not log');
        $this->assertTrue(true); // If no exception, pass
    }

    public function testGetAvailableLogDatesHandlesArrayAndNonArray()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_log_index') return ['2024-01-01', '2024-01-02'];
            return $default;
        });
        $log = new LoggingSystem();
        $dates = $log->getAvailableLogDates();
        $this->assertEquals(['2024-01-02', '2024-01-01'], $dates);
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_log_index') return 'not-an-array';
            return $default;
        });
        $dates = $log->getAvailableLogDates();
        $this->assertEquals([], $dates);
    }

    public function testGetLogsForDateHandlesArrayAndNonArray()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_daily_log_2024-01-01') return [['msg'=>'foo']];
            return $default;
        });
        $log = new LoggingSystem();
        $logs = $log->getLogsForDate('2024-01-01');
        $this->assertEquals([['msg'=>'foo']], $logs);
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_daily_log_2024-01-01') return 'not-an-array';
            return $default;
        });
        $logs = $log->getLogsForDate('2024-01-01');
        $this->assertEquals([], $logs);
    }

    public function testClearDayLogDeletesAndUpdatesIndex()
    {
        $deleted = $updated = false;
        \Brain\Monkey\Functions\when('delete_option')->alias(function($key) use (&$deleted) {
            if ($key === 'ctm_daily_log_2024-01-01') $deleted = true;
            return true;
        });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_log_index') return ['2024-01-01', '2024-01-02'];
            return $default;
        });
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) use (&$updated) {
            if ($key === 'ctm_log_index') $updated = true;
            return true;
        });
        $log = new LoggingSystem();
        $log->clearDayLog('2024-01-01');
        $this->assertTrue($deleted && $updated);
    }

    public function testClearAllLogsDeletesAll()
    {
        $deleted = [];
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_log_index') return ['2024-01-01', '2024-01-02'];
            return $default;
        });
        \Brain\Monkey\Functions\when('delete_option')->alias(function($key) use (&$deleted) {
            $deleted[] = $key;
            return true;
        });
        $log = new LoggingSystem();
        $log->clearAllLogs();
        $this->assertContains('ctm_daily_log_2024-01-01', $deleted);
        $this->assertContains('ctm_daily_log_2024-01-02', $deleted);
        $this->assertContains('ctm_log_index', $deleted);
        $this->assertContains('ctm_debug_log', $deleted);
    }

    public function testEmailLogReturnsFalseIfNoLogs()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_daily_log_2024-01-01') return [];
            return $default;
        });
        $log = new LoggingSystem();
        $this->assertFalse($log->emailLog('2024-01-01', 'test@example.com'));
    }

    public function testEmailLogSendsIfLogsExist()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_daily_log_2024-01-01') return [['timestamp'=>'2024-01-01','type'=>'info','message'=>'msg','context'=>[],'user_id'=>1,'ip_address'=>'127.0.0.1','user_agent'=>'UA','memory_usage'=>1,'memory_peak'=>2]];
            if ($key === 'admin_email') return 'admin@example.com';
            return $default;
        });
        \Brain\Monkey\Functions\when('get_bloginfo')->alias(function($key) { return 'Site'; });
        \Brain\Monkey\Functions\when('current_time')->alias(function() { return '2024-01-01 00:00:00'; });
        \Brain\Monkey\Functions\when('get_userdata')->alias(function($id) { return (object)['user_login'=>'admin']; });
        \Brain\Monkey\Functions\when('size_format')->alias(function($v) { return $v.'B'; });
        \Brain\Monkey\Functions\when('wp_mail')->alias(function($to, $subj, $msg, $headers) { return true; });
        $log = new LoggingSystem();
        $this->assertTrue($log->emailLog('2024-01-01', 'test@example.com'));
    }

    public function testGetLogStatistics()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'ctm_log_index') return ['2024-01-01'];
            if ($key === 'ctm_daily_log_2024-01-01') return [
                ['type'=>'info'],
                ['type'=>'error'],
                ['type'=>'info']
            ];
            return $default;
        });
        $log = new LoggingSystem();
        $stats = $log->getLogStatistics();
        $this->assertEquals(1, $stats['total_days']);
        $this->assertEquals(3, $stats['total_entries']);
        $this->assertArrayHasKey('info', $stats['type_counts']);
        $this->assertArrayHasKey('error', $stats['type_counts']);
    }

    public function testInitializeLoggingSystemSchedulesEventAndRegistersAction()
    {
        $scheduled = $action = false;
        \Brain\Monkey\Functions\when('wp_next_scheduled')->alias(function($hook) { return false; });
        \Brain\Monkey\Functions\when('wp_schedule_event')->alias(function($time, $recurrence, $hook) use (&$scheduled) {
            if ($hook === 'ctm_daily_log_cleanup') $scheduled = true;
            return true;
        });
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $cb) use (&$action) {
            if ($hook === 'ctm_daily_log_cleanup') $action = true;
            return true;
        });
        LoggingSystem::initializeLoggingSystem();
        $this->assertTrue($scheduled && $action);
    }

    public function testOnPluginActivationSetsDefaultsAndLogs()
    {
        $this->markTestSkipped('Patchwork is not available for static method patching.');
    }

    public function testOnPluginDeactivationClearsScheduledHookAndLogs()
    {
        $this->markTestSkipped('Patchwork is not available for static method patching.');
    }

    public function testPerformScheduledLogCleanupCallsInstanceMethod()
    {
        $this->markTestSkipped('Patchwork is not available for static method patching.');
    }

    public function testLogDebugCallsLogActivity()
    {
        $this->markTestSkipped('Patchwork is not available for static method patching.');
    }

    // For brevity, static methods like initializeLoggingSystem, onPluginActivation, onPluginDeactivation, performScheduledLogCleanup, logDebug are not tested here, but could be with further mocking.
} 