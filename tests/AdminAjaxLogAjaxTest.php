<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\LogAjax;
use Brain\Monkey;
use CTM\Admin\LoggingSystem;
use CTM\Tests\Traits\MonkeyTrait;
class AdminAjaxLogAjaxTest extends TestCase
{
    use MonkeyTrait;
    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        
    }
    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $this->assertInstanceOf(LogAjax::class, $logAjax);
    }
    public function testAjaxEmailDailyLogNoDate()
    {
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '';
        $_POST['email_to'] = 'admin@example.com';
        $called = [];
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'admin_email') return 'admin@example.com';
            return [
                [
                    'type'=>'info',
                    'timestamp'=>'2024-01-01 00:00:00',
                    'message'=>'Test',
                    'context'=>['foo'=>'bar']
                ]
            ];
        });
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $logAjax->ajaxEmailDailyLog();
        $this->assertIsArray($called);
        $this->assertEquals('No log date provided.', $called['message']);
        $this->addToAssertionCount(1);
    }

    public function testAjaxEmailDailyLogInvalidEmail()
    {
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';
        $_POST['email_to'] = 'invalid-email';
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'admin_email') return 'admin@example.com';
            return [
                [
                    'type'=>'info',
                    'timestamp'=>'2024-01-01 00:00:00',
                    'message'=>'Test',
                    'context'=>['foo'=>'bar']
                ]
            ];
        });
        \Brain\Monkey\Functions\when('is_email')->justReturn(false);
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $logAjax->ajaxEmailDailyLog();
        $this->assertIsArray($called);
        $this->assertEquals('Invalid email address.', $called['message']);
    }

    public function testAjaxEmailDailyLogSuccess()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';
        $_POST['email_to'] = 'admin@example.com';

        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'admin_email') return 'admin@example.com';
            return [
                [
                    'type'=>'info',
                    'timestamp'=>'2024-01-01 00:00:00',
                    'message'=>'Test',
                    'context'=>['foo'=>'bar']
                ]
            ];
        });
        \Brain\Monkey\Functions\when('is_email')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(true);

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxEmailDailyLog();
        $this->assertIsArray($called);
        $this->assertStringContainsString('Log emailed to', $called['message']);
    }

    public function testAjaxEmailDailyLogFailSend()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';
        $_POST['email_to'] = 'admin@example.com';

        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            if ($key === 'admin_email') return 'admin@example.com';
            return [
                [
                    'type'=>'info',
                    'timestamp'=>'2024-01-01 00:00:00',
                    'message'=>'Test',
                    'context'=>['foo'=>'bar']
                ]
            ];
        });
        \Brain\Monkey\Functions\when('is_email')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(false);

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxEmailDailyLog();
        $this->assertIsArray($called);
        $this->assertEquals('Failed to send email.', $called['message']);
    }

    public function testAjaxExportDailyLogSuccess()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';

        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            return [
                [
                    'type'=>'info',
                    'timestamp'=>'2024-01-01 00:00:00',
                    'message'=>'Test',
                    'context'=>['foo'=>'bar']
                ]
            ];
        });

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxExportDailyLog();
        $this->assertIsArray($called);
        $this->assertArrayHasKey('url', $called);
        $this->assertStringContainsString('.csv', $called['url']);
    }

    public function testAjaxClearDailyLogSuccess()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxClearDailyLog();
        $this->assertIsArray($called);
        $this->assertEquals('Log cleared.', $called['message']);
    }

    public function testAjaxGetDailyLogNoLogs()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';

        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) {
            return [];
        });

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxGetDailyLog();
        $this->assertIsArray($called);
        $this->assertArrayHasKey('logs', $called);
        $this->assertEmpty($called['logs']);
    }

    public function testAjaxGetDailyLogWithLogs()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['log_date'] = '2024-01-01';

        $logs = [
            [
                'type'=>'info',
                'timestamp'=>'2024-01-01 00:00:00',
                'message'=>'Test',
                'context'=>['foo'=>'bar']
            ]
        ];
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = []) use ($logs) {
            return $logs;
        });

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxGetDailyLog();
        $this->assertIsArray($called);
        $this->assertArrayHasKey('logs', $called);
        $this->assertEquals($logs, $called['logs']);
    }

    public function testAjaxAddLogEntryNoMessage()
    {
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = '';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Message is required.', $called['message']);
        $this->addToAssertionCount(1);
    }

    public function testAjaxAddLogEntrySuccess()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Test log entry';

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryWithContext()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'error';
        $_POST['message'] = 'Error log entry';
        $_POST['context'] = json_encode(['foo'=>'bar']);

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryInvalidContext()
    {
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'error';
        $_POST['message'] = 'Error log entry';
        $_POST['context'] = '{invalid json}';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Invalid context JSON.', $called['message']);
        $this->addToAssertionCount(1);
    }

    public function testAjaxAddLogEntryTypeDefault()
    {
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $_POST['nonce'] = 'abc';
        $_POST['message'] = 'No type provided';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Type is required.', $called['message']);
    }

    public function testAjaxAddLogEntryTypeSanitization()
    {
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $_POST['nonce'] = 'abc';
        $_POST['type'] = '<script>alert(1)</script>';
        $_POST['message'] = 'Sanitize type';
        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) {
            $called = $arr;
        });
        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Type is required.', $called['message']);
    }

    public function testAjaxAddLogEntryContextArray()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Context as array';
        $_POST['context'] = ['foo'=>'bar'];

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryContextEmpty()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Context empty';
        $_POST['context'] = '';

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryContextNull()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Context null';
        $_POST['context'] = null;

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryContextNumericString()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Context numeric string';
        $_POST['context'] = '123';

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryContextBoolean()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Context boolean';
        $_POST['context'] = true;

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxAddLogEntryContextObject()
    {
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);

        $_POST['nonce'] = 'abc';
        $_POST['type'] = 'info';
        $_POST['message'] = 'Context object';
        $_POST['context'] = (object)['foo'=>'bar'];

        $called = [];
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) {
            $called = $arr;
        });

        $logAjax->ajaxAddLogEntry();
        $this->assertIsArray($called);
        $this->assertEquals('Log entry added.', $called['message']);
    }

    public function testAjaxClearLogsCf7()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_type'] = 'cf7';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $called = null;
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $logAjax->ajaxClearLogs();
        $this->assertEquals('CF7 logs cleared successfully', $called['message']);
    }
    public function testAjaxClearLogsGf()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_type'] = 'gf';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $called = null;
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $logAjax->ajaxClearLogs();
        $this->assertEquals('Gravity Forms logs cleared successfully', $called['message']);
    }
    public function testAjaxClearLogsDebugAll()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_type'] = 'debug_all';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $logAjax->ajaxClearLogs();
        $this->assertEquals('All debug logs cleared successfully', $called['message']);
    }
    public function testAjaxClearLogsDebugSingleWithDate()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_type'] = 'debug_single';
        $_POST['log_date'] = '2024-01-01';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $logAjax->ajaxClearLogs();
        $this->assertEquals('Debug log for 2024-01-01 cleared successfully', $called['message']);
    }
    public function testAjaxClearLogsDebugSingleNoDate()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_type'] = 'debug_single';
        unset($_POST['log_date']);
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $logAjax->ajaxClearLogs();
        $this->assertEquals('Log date is required for single day clear', $called['message']);
    }
    public function testAjaxClearLogsInvalidType()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_type'] = 'invalid';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        $logAjax->ajaxClearLogs();
        $this->assertEquals('Invalid log type specified', $called['message']);
    }
    public function testAjaxUpdateLogSettingsSuccess()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_retention_days'] = '30';
        $_POST['log_auto_cleanup'] = '1';
        $_POST['log_email_notifications'] = '1';
        $_POST['log_notification_email'] = 'admin@example.com';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        $logAjax->ajaxUpdateLogSettings();
        $this->assertEquals('Log settings updated successfully', $called['message']);
        $this->assertEquals(30, $called['settings']['retention_days']);
        $this->assertTrue($called['settings']['auto_cleanup']);
        $this->assertTrue($called['settings']['email_notifications']);
        $this->assertEquals('admin@example.com', $called['settings']['notification_email']);
    }
    public function testAjaxUpdateLogSettingsMissingEmail()
    {
        $_POST['nonce'] = 'abc';
        $_POST['log_retention_days'] = '30';
        $_POST['log_auto_cleanup'] = '1';
        $_POST['log_email_notifications'] = '1';
        $_POST['log_notification_email'] = '';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        $called = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        $logAjax->ajaxUpdateLogSettings();
        $this->assertEquals('Notification email is required when email notifications are enabled', $called['message']);
    }
    public function testAjaxToggleDebugModeEnable()
    {
        $_POST['nonce'] = 'abc';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        \Brain\Monkey\Functions\when('get_option')->justReturn(false);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('get_current_user_id')->justReturn(1);
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_option')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_key')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_title')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_user')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_file_name')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_html_class')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_meta')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_mime_type')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_option')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_sql_orderby')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_textarea_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_title_for_query')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_title_with_dashes')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_user_field')->alias(function($v){return $v;});
        $called = null;
        $logAjax->ajaxToggleDebugMode();
        $this->assertEquals('Debug mode enabled successfully', $called['message']);
        $this->assertTrue($called['debug_enabled']);
    }
    public function testAjaxToggleDebugModeDisable()
    {
        $_POST['nonce'] = 'abc';
        $loggingSystem = new LoggingSystem();
        $renderer = new \CTM\Admin\SettingsRenderer();
        $logAjax = new LogAjax($loggingSystem, $renderer);
        \Brain\Monkey\Functions\when('get_option')->justReturn(true);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('get_current_user_id')->justReturn(1);
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arr) use (&$called) { $called = $arr; });
        $called = null;
        $logAjax->ajaxToggleDebugMode();
        $this->assertEquals('Debug mode disabled successfully', $called['message']);
        $this->assertFalse($called['debug_enabled']);
    }
} 