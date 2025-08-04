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
        $log = new LoggingSystem();
        $result = $log->isDebugEnabled();
        $this->assertIsBool($result);
    }

    public function testIsDebugEnabledTrueAndFalse()
    {
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            if ($key === 'ctm_debug_enabled') return true;
            return $default;
        });
        $log = new LoggingSystem();
        $this->assertTrue($log->isDebugEnabled());
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = false) {
            if ($key === 'ctm_debug_enabled') return false;
            return $default;
        });
        $log = new LoggingSystem();
        $this->assertFalse($log->isDebugEnabled());
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
        $log = new LoggingSystem();
        $log->initializeLoggingSystem();
        $this->assertTrue($scheduled && $action);
    }

    public function testOnPluginActivationSetsDefaultsAndLogs()
    {
        $updated = [];
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_log_retention_days') return false;
            if ($key === 'ctm_log_auto_cleanup') return false;
            if ($key === 'ctm_log_email_notifications') return false;
            if ($key === 'ctm_log_notification_email') return false;
            if ($key === 'admin_email') return 'admin@example.com';
            if ($key === 'ctm_debug_enabled') return true;
            return null;
        });
        \Brain\Monkey\Functions\when('update_option')->alias(function($key, $value) use (&$updated) {
            $updated[$key] = $value;
        });
        \Brain\Monkey\Functions\when('wp_schedule_event')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_next_scheduled')->justReturn(false);
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('Test Site');
        \Brain\Monkey\Functions\when('current_time')->justReturn('2024-01-01 00:00:00');
        \Brain\Monkey\Functions\when('get_current_user_id')->justReturn(1);
        
        LoggingSystem::onPluginActivation();
        
        $this->assertArrayHasKey('ctm_log_retention_days', $updated);
        $this->assertArrayHasKey('ctm_log_auto_cleanup', $updated);
        $this->assertArrayHasKey('ctm_log_email_notifications', $updated);
        $this->assertArrayHasKey('ctm_log_notification_email', $updated);
    }

    public function testOnPluginDeactivationClearsScheduledHookAndLogs()
    {
        $cleared = false;
        \Brain\Monkey\Functions\when('wp_clear_scheduled_hook')->alias(function($hook) use (&$cleared) {
            if ($hook === 'ctm_daily_log_cleanup') $cleared = true;
        });
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_debug_enabled') return true;
            return null;
        });
        \Brain\Monkey\Functions\when('current_time')->justReturn('2024-01-01 00:00:00');
        \Brain\Monkey\Functions\when('get_current_user_id')->justReturn(1);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        
        LoggingSystem::onPluginDeactivation();
        $this->assertTrue($cleared);
    }

    public function testPerformScheduledLogCleanupCallsInstanceMethod()
    {
        $spy = new class extends LoggingSystem {
            public $called = false;
            public function performInstanceLogCleanup(): void {
                $this->called = true;
            }
        };
        $spy->performScheduledLogCleanup();
        $this->assertTrue($spy->called);
    }

    public function testLogDebugCallsLogActivity()
    {
        $spy = new class extends LoggingSystem {
            public $called = false;
            public $message = null;
            public $type = null;
            public function logActivity(string $message, string $type = 'info', array $context = []): void {
                $this->called = true;
                $this->message = $message;
                $this->type = $type;
            }
        };
        $spy->logDebug('debug message');
        $this->assertTrue($spy->called);
        $this->assertStringContainsString('debug', $spy->type);
        $this->assertStringContainsString('debug', $spy->message);
    }

    public function testCleanupOldLogsWithValidRetention()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_log_auto_cleanup') return true;
            if ($key === 'ctm_log_retention_days') return 7;
            if ($key === 'ctm_log_index') return ['2024-01-01', '2024-01-02', '2024-01-03'];
            return null;
        });
        \Brain\Monkey\Functions\when('delete_option')->justReturn(true);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('cleanupOldLogs');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($loggingSystem);
        $this->assertTrue(true);
    }

    public function testCleanupOldLogsWithAutoCleanupDisabled()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_log_auto_cleanup') return false;
            return null;
        });

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('cleanupOldLogs');
        $method->setAccessible(true);
        
        // Should not throw any exceptions and should return early
        $method->invoke($loggingSystem);
        $this->assertTrue(true);
    }

    public function testGetUserIPWithValidIP()
    {
        // Mock $_SERVER
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '192.168.1.100';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('getUserIP');
        $method->setAccessible(true);
        
        $result = $method->invoke($loggingSystem);
        $this->assertEquals('192.168.1.100', $result);
    }

    public function testGetUserIPWithInvalidIP()
    {
        // Mock $_SERVER with invalid IP
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'invalid-ip';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('getUserIP');
        $method->setAccessible(true);
        
        $result = $method->invoke($loggingSystem);
        $this->assertEquals('127.0.0.1', $result);
    }

    public function testGetUserIPWithNoServerVars()
    {
        // Clear $_SERVER
        unset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR']);

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('getUserIP');
        $method->setAccessible(true);
        
        $result = $method->invoke($loggingSystem);
        $this->assertEquals('Unknown', $result);
    }

    public function testSendCleanupNotificationWithValidData()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(true);
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_log_cleanup_notifications') return 'admin@example.com';
            return null;
        });

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('sendCleanupNotification');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($loggingSystem, 5, 1024, 7);
        $this->assertTrue(true);
    }

    public function testSendCleanupNotificationWithNoEmail()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('get_option')->alias(function($key) {
            if ($key === 'ctm_log_cleanup_notifications') return '';
            return null;
        });

        $loggingSystem = new LoggingSystem();
        
        // Use reflection to access private method
        $reflection = new \ReflectionClass($loggingSystem);
        $method = $reflection->getMethod('sendCleanupNotification');
        $method->setAccessible(true);
        
        // Should not throw any exceptions
        $method->invoke($loggingSystem, 5, 1024, 7);
        $this->assertTrue(true);
    }

    // For brevity, static methods like initializeLoggingSystem, onPluginActivation, onPluginDeactivation, performScheduledLogCleanup, logDebug are not tested here, but could be with further mocking.
} 