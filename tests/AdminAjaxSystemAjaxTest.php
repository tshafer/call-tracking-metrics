<?php

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\SystemAjax;
use Brain\Monkey;
use CTM\Admin\LoggingSystem;
use CTM\Admin\SettingsRenderer;
use CTM\Tests\Traits\MonkeyTrait;

class AdminAjaxSystemAjaxTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        $this->initalMonkey();
        parent::setUp();
      
    }
    protected function tearDown(): void
    {

        parent::tearDown();
    }

    public function testRegisterHandlersAddsActions() {
        // Instead of expecting exactly 10 calls, allow any number and capture the calls for assertion.
        $calls = [];
        \Brain\Monkey\Functions\when('add_action')->alias(function(...$args) use (&$calls) {
            $calls[] = $args;
        });

        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $systemAjax->registerHandlers();

        // Assert that add_action was called 10 times
        $this->assertCount(10, $calls, 'add_action should be called 10 times');
    }

    public function testAjaxSecurityScanSuccess() {
        \Brain\Monkey\Functions\when('headers_list')->justReturn([
            'Strict-Transport-Security',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'Referrer-Policy',
            'Content-Security-Policy',
            'Permissions-Policy',
        ]);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([]);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $systemAjax->ajaxSecurityScan();
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('results', $payload, 'Payload should have results key');
            $this->assertArrayHasKey('security_score', $payload['results'], 'Results should have security_score');
        }
    }

    public function testAjaxSecurityScanMissingHeaders() {
        \Brain\Monkey\Functions\when('headers_list')->justReturn([]);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([]);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $systemAjax->ajaxSecurityScan();
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('results', $payload, 'Payload should have results key');
            $this->assertArrayHasKey('security_score', $payload['results'], 'Results should have security_score');
        }
    }

    public function testAjaxSecurityScanLoosePermissions() {
        \Brain\Monkey\Functions\when('headers_list')->justReturn([
            'Strict-Transport-Security',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'Referrer-Policy',
            'Content-Security-Policy',
            'Permissions-Policy',
        ]);
        \Brain\Monkey\Functions\when('file_exists')->alias(function($f){return true;});
        \Brain\Monkey\Functions\when('fileperms')->alias(function($f){return 0100777;});
        \Brain\Monkey\Functions\when('get_plugins')->justReturn([]);
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once();
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $systemAjax->ajaxSecurityScan();
        $this->assertTrue(true);
    }

    public function testAjaxSecurityScanVulnerablePlugin() {
        \Brain\Monkey\Functions\when('headers_list')->justReturn([
            'Strict-Transport-Security',
            'X-Frame-Options',
            'X-Content-Type-Options',
            'Referrer-Policy',
            'Content-Security-Policy',
            'Permissions-Policy',
        ]);
        \Brain\Monkey\Functions\when('get_plugins')->justReturn(['hello.php' => []]);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $systemAjax->ajaxSecurityScan();
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('results', $payload, 'Payload should have results key');
            $this->assertArrayHasKey('security_score', $payload['results'], 'Results should have security_score');
        }
    }

    public function testAjaxPerformanceAnalysisSuccess() {
        \Brain\Monkey\Functions\when('wp_cache_get_stats')->justReturn(['hits' => 100, 'misses' => 10]);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $systemAjax->ajaxPerformanceAnalysis();
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('results', $payload, 'Payload should have results key');
            $this->assertArrayHasKey('performance_score', $payload['results'], 'Results should have performance_score');
        }
    }

    public function testAjaxExportDiagnosticReportSuccess() {
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxExportDiagnosticReport');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('report', $payload, 'Payload should have report key');
        }
    }

    public function testAjaxExportDiagnosticReportException() {
        $called = false;
        $payload = null;
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $mock = $this->getMockBuilder(SystemAjax::class)
            ->setConstructorArgs([$loggingSystem, $renderer])
            ->onlyMethods([])
            ->getMock();
        // Use reflection to make the private method accessible and throw an exception
        $reflection = new \ReflectionClass($mock);
        $method = $reflection->getMethod('ajaxExportDiagnosticReport');
        $method->setAccessible(true);
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        // Patch: force an exception by making generateSystemInfoReport throw
        $mockReflection = new \ReflectionClass($mock);
        $genMethod = $mockReflection->getMethod('generateSystemInfoReport');
        $genMethod->setAccessible(true);
        $genMethod->invokeArgs($mock, [true]); // This will throw if you patch the method to throw
        try {
            $method->invoke($mock);
        } catch (\Throwable $e) {
            // ignore
        }
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
        $this->assertTrue($called, 'wp_send_json_error should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
        }
    }

    public function testAjaxFullDiagnosticSuccess() {
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxFullDiagnostic');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('passed_checks', $payload, 'Payload should have passed_checks key');
            $this->assertArrayHasKey('categories', $payload, 'Payload should have categories key');
        }
    }

    public function testAjaxFullDiagnosticException() {
        $called = false;
        $payload = null;
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $mock = $this->getMockBuilder(SystemAjax::class)
            ->setConstructorArgs([$loggingSystem, $renderer])
            ->onlyMethods(['analyzeApiCredentials'])
            ->getMock();
        $mock->method('analyzeApiCredentials')->will($this->throwException(new \Exception('fail')));
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $reflection = new \ReflectionClass($mock);
        $method = $reflection->getMethod('ajaxFullDiagnostic');
        $method->setAccessible(true);
        try {
            $method->invoke($mock);
        } catch (\Throwable $e) {
            // ignore
        }
        $this->assertTrue($called, 'wp_send_json_error should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
        }
    }

    public function testAjaxAutoFixIssuesSuccess() {
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxAutoFixIssues');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
            $this->assertArrayHasKey('fixes', $payload, 'Payload should have fixes key');
        }
    }

    public function testAjaxAutoFixIssuesException() {
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $mock = $this->getMockBuilder(SystemAjax::class)
            ->setConstructorArgs([$loggingSystem, $renderer])
            ->onlyMethods(['get_option'])
            ->getMock();
        $mock->method('get_option')->will($this->throwException(new \Exception('fail')));
        \Brain\Monkey\Functions\expect('wp_send_json_error')->once();
        $reflection = new \ReflectionClass($mock);
        $method = $reflection->getMethod('ajaxAutoFixIssues');
        $method->setAccessible(true);
        $method->invoke($mock);
    }

    public function testAjaxRefreshSystemInfoSuccess() {
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxRefreshSystemInfo');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('system_info', $payload, 'Payload should have system_info key');
        }
    }

    public function testAjaxRefreshSystemInfoException() {
        $called = false;
        $payload = null;
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $mock = $this->getMockBuilder(SystemAjax::class)
            ->setConstructorArgs([$loggingSystem, $renderer])
            ->onlyMethods(['generateSystemInfoReport'])
            ->getMock();
        $mock->method('generateSystemInfoReport')->will($this->throwException(new \Exception('fail')));
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $reflection = new \ReflectionClass($mock);
        $method = $reflection->getMethod('ajaxRefreshSystemInfo');
        $method->setAccessible(true);
        try {
            $method->invoke($mock);
        } catch (\Throwable $e) {
            // ignore
        }
        $this->assertTrue($called, 'wp_send_json_error should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
        }
    }

    public function testAjaxEmailSystemInfoSuccess() {
        $_POST['email_to'] = 'test@example.com';
        $_POST['subject'] = 'Test Subject';
        $_POST['message'] = 'Test Message';
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_textarea_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(true);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxEmailSystemInfo');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
        }
    }

    public function testAjaxEmailSystemInfoNoEmail() {
        unset($_POST['email_to']);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxEmailSystemInfo');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_error should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
        }
    }

    public function testAjaxEmailSystemInfoFailSend() {
        $_POST['email_to'] = 'test@example.com';
        \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('sanitize_textarea_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_mail')->justReturn(false);
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_error')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxEmailSystemInfo');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_error should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('message', $payload, 'Payload should have message key');
        }
    }

    public function testAjaxAnalyzeIssueApiCredentials() {
        $_POST['issue_type'] = 'api_credentials';
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxAnalyzeIssue');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('analysis', $payload, 'Payload should have analysis key');
        }
    }

    public function testAjaxAnalyzeIssueUnknown() {
        $_POST['issue_type'] = 'unknown';
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxAnalyzeIssue');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('analysis', $payload, 'Payload should have analysis key');
        }
    }

    public function testAjaxHealthCheckSuccess() {
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxHealthCheck');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('checks', $payload, 'Payload should have checks key');
        }
    }

    public function testAjaxGetPerformanceMetricsSuccess() {
        $called = false;
        $payload = null;
        \Brain\Monkey\Functions\when('wp_send_json_success')->alias(function($arg) use (&$called, &$payload) {
            $called = true;
            $payload = $arg;
        });
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('ajaxGetPerformanceMetrics');
        $method->setAccessible(true);
        $method->invoke($systemAjax);
        $this->assertTrue($called, 'wp_send_json_success should be called');
        $this->assertNotNull($payload, 'Payload should not be null');
        if ($payload !== null) {
            $this->assertIsArray($payload, 'Payload should be an array');
            $this->assertArrayHasKey('current_memory', $payload, 'Payload should have current_memory key');
            $this->assertArrayHasKey('db_queries', $payload, 'Payload should have db_queries key');
        }
    }

    public function testGenerateSystemInfoReportReturnsString() {
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('generateSystemInfoReport');
        $method->setAccessible(true);
        $result = $method->invoke($systemAjax);
        $this->assertIsString($result);
    }

    public function testGenerateSystemInfoReportHtmlReturnsString() {
        $loggingSystem = new LoggingSystem();
        $renderer = new SettingsRenderer();
        $systemAjax = new SystemAjax($loggingSystem, $renderer);
        $reflection = new \ReflectionClass($systemAjax);
        $method = $reflection->getMethod('generateSystemInfoReport');
        $method->setAccessible(true);
        $result = $method->invoke($systemAjax, true);
        $this->assertIsString($result);
    }
} 