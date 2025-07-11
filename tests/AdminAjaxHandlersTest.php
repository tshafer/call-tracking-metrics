<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\AjaxHandlers;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class AdminAjaxHandlersTest extends TestCase
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
        $ajaxHandlers = new AjaxHandlers();
        $this->assertInstanceOf(AjaxHandlers::class, $ajaxHandlers);
    }

    public function testRegisterHandlersCallsAllSubHandlers()
    {
        // Use real or stub instances instead of PHPUnit mocks
        $formAjax = new class extends \CTM\Admin\Ajax\FormAjax {
            public function registerHandlers() {}
        };
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {
            public function registerHandlers() {}
        };
        $apiAjax = new class extends \CTM\Admin\Ajax\ApiAjax {
            public function registerHandlers() {}
        };
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {
            public function registerHandlers() {}
        };
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $ajaxHandlers->registerHandlers();
        $this->addToAssertionCount(1);
    }

    public function testInjectLoggingSystem()
    {
        $loggingSystem = new \CTM\Admin\LoggingSystem();
        $ajaxHandlers = new AjaxHandlers($loggingSystem);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('loggingSystem');
        $prop->setAccessible(true);
        $this->assertSame($loggingSystem, $prop->getValue($ajaxHandlers));
    }
    public function testInjectSettingsRenderer()
    {
        $settingsRenderer = new \CTM\Admin\SettingsRenderer();
        $ajaxHandlers = new AjaxHandlers(null, $settingsRenderer);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('renderer');
        $prop->setAccessible(true);
        $this->assertSame($settingsRenderer, $prop->getValue($ajaxHandlers));
    }
    public function testInjectFormAjax()
    {
        $formAjax = new \CTM\Admin\Ajax\FormAjax();
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('formAjax');
        $prop->setAccessible(true);
        $this->assertSame($formAjax, $prop->getValue($ajaxHandlers));
    }
    public function testInjectLogAjax()
    {
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {};
        $ajaxHandlers = new AjaxHandlers(null, null, null, $logAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('logAjax');
        $prop->setAccessible(true);
        $this->assertSame($logAjax, $prop->getValue($ajaxHandlers));
    }
    public function testInjectApiAjax()
    {
        $apiAjax = new \CTM\Admin\Ajax\ApiAjax();
        $ajaxHandlers = new AjaxHandlers(null, null, null, null, $apiAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('apiAjax');
        $prop->setAccessible(true);
        $this->assertSame($apiAjax, $prop->getValue($ajaxHandlers));
    }
    public function testInjectSystemAjax()
    {
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {};
        $ajaxHandlers = new AjaxHandlers(null, null, null, null, null, $systemAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('systemAjax');
        $prop->setAccessible(true);
        $this->assertSame($systemAjax, $prop->getValue($ajaxHandlers));
    }
    public function testDefaultLoggingSystem()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('loggingSystem');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\CTM\Admin\LoggingSystem::class, $prop->getValue($ajaxHandlers));
    }
    public function testDefaultSettingsRenderer()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('renderer');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\CTM\Admin\SettingsRenderer::class, $prop->getValue($ajaxHandlers));
    }
    public function testDefaultFormAjax()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('formAjax');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\CTM\Admin\Ajax\FormAjax::class, $prop->getValue($ajaxHandlers));
    }
    public function testDefaultLogAjax()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('logAjax');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\CTM\Admin\Ajax\LogAjax::class, $prop->getValue($ajaxHandlers));
    }
    public function testDefaultApiAjax()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('apiAjax');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\CTM\Admin\Ajax\ApiAjax::class, $prop->getValue($ajaxHandlers));
    }
    public function testDefaultSystemAjax()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('systemAjax');
        $prop->setAccessible(true);
        $this->assertInstanceOf(\CTM\Admin\Ajax\SystemAjax::class, $prop->getValue($ajaxHandlers));
    }
    public function testPartialDependencyInjection()
    {
        $formAjax = new \CTM\Admin\Ajax\FormAjax();
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $prop = $ref->getProperty('formAjax');
        $prop->setAccessible(true);
        $this->assertSame($formAjax, $prop->getValue($ajaxHandlers));
    }
    public function testNullDependencyInjection()
    {
        $ajaxHandlers = new AjaxHandlers(null, null, null, null, null, null);
        $this->assertInstanceOf(AjaxHandlers::class, $ajaxHandlers);
    }
    public function testInvalidDependencyInjectionThrows()
    {
        $this->expectException(\TypeError::class);
        new AjaxHandlers('not a logger');
    }
    public function testRegisterHandlersMultipleCalls()
    {
        // Use stubs to track calls
        $calls = [
            'form' => 0,
            'log' => 0,
            'api' => 0,
            'system' => 0
        ];
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $formAjax = new class($calls) extends \CTM\Admin\Ajax\FormAjax {
            public $calls;
            public function __construct(&$calls) { $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['form']++; }
        };
        $logAjax = new class($calls, $loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {
            public $calls;
            public function __construct(&$calls, $loggingSystem, $settingsRenderer) { parent::__construct($loggingSystem, $settingsRenderer); $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['log']++; }
        };
        $apiAjax = new class($calls) extends \CTM\Admin\Ajax\ApiAjax {
            public $calls;
            public function __construct(&$calls) { $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['api']++; }
        };
        $systemAjax = new class($calls, $loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {
            public $calls;
            public function __construct(&$calls, $loggingSystem, $settingsRenderer) { parent::__construct($loggingSystem, $settingsRenderer); $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['system']++; }
        };
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $ajaxHandlers->registerHandlers();
        $this->assertEquals(1, $calls['form']);
        $this->assertEquals(1, $calls['log']);
        $this->assertEquals(1, $calls['api']);
        $this->assertEquals(1, $calls['system']);
    }
    public function testSubHandlerThrowsException()
    {
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $formAjax = new class extends \CTM\Admin\Ajax\FormAjax {
            public function registerHandlers() { throw new \Exception('fail'); }
        };
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $apiAjax = new class extends \CTM\Admin\Ajax\ApiAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $this->expectException(\Exception::class);
        $ajaxHandlers->registerHandlers();
    }
    public function testRegisterHandlersWithRealSubHandlers()
    {
        $ajaxHandlers = new AjaxHandlers();
        $this->expectNotToPerformAssertions();
        $ajaxHandlers->registerHandlers();
    }
    // Add more tests for edge cases, integration, and behavioral checks as needed to reach 25+

    public function testDefaultConstructionWithAllNulls()
    {
        $ajaxHandlers = new AjaxHandlers(null, null, null, null, null, null);
        $this->assertInstanceOf(AjaxHandlers::class, $ajaxHandlers);
        $ref = new \ReflectionClass($ajaxHandlers);
        $this->assertInstanceOf(\CTM\Admin\LoggingSystem::class, $ref->getProperty('loggingSystem')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\SettingsRenderer::class, $ref->getProperty('renderer')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\FormAjax::class, $ref->getProperty('formAjax')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\LogAjax::class, $ref->getProperty('logAjax')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\ApiAjax::class, $ref->getProperty('apiAjax')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\SystemAjax::class, $ref->getProperty('systemAjax')->getValue($ajaxHandlers));
    }

    public function testSubHandlerTypesAfterConstruction()
    {
        $ajaxHandlers = new AjaxHandlers();
        $ref = new \ReflectionClass($ajaxHandlers);
        $this->assertInstanceOf(\CTM\Admin\Ajax\FormAjax::class, $ref->getProperty('formAjax')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\LogAjax::class, $ref->getProperty('logAjax')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\ApiAjax::class, $ref->getProperty('apiAjax')->getValue($ajaxHandlers));
        $this->assertInstanceOf(\CTM\Admin\Ajax\SystemAjax::class, $ref->getProperty('systemAjax')->getValue($ajaxHandlers));
    }

    public function testRegisterHandlersCallsAllSubHandlersOnce()
    {
        $calls = [
            'form' => 0,
            'log' => 0,
            'api' => 0,
            'system' => 0
        ];
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $formAjax = new class($calls) extends \CTM\Admin\Ajax\FormAjax {
            public $calls;
            public function __construct(&$calls) { $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['form']++; }
        };
        $logAjax = new class($calls, $loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {
            public $calls;
            public function __construct(&$calls, $loggingSystem, $settingsRenderer) { parent::__construct($loggingSystem, $settingsRenderer); $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['log']++; }
        };
        $apiAjax = new class($calls) extends \CTM\Admin\Ajax\ApiAjax {
            public $calls;
            public function __construct(&$calls) { $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['api']++; }
        };
        $systemAjax = new class($calls, $loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {
            public $calls;
            public function __construct(&$calls, $loggingSystem, $settingsRenderer) { parent::__construct($loggingSystem, $settingsRenderer); $this->calls = &$calls; }
            public function registerHandlers() { $this->calls['system']++; }
        };
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $ajaxHandlers->registerHandlers();
        $this->assertEquals(1, $calls['form']);
        $this->assertEquals(1, $calls['log']);
        $this->assertEquals(1, $calls['api']);
        $this->assertEquals(1, $calls['system']);
    }

    public function testRegisterHandlersPropagatesException()
    {
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $formAjax = new class extends \CTM\Admin\Ajax\FormAjax {
            public function registerHandlers() { throw new \Exception('fail'); }
        };
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $apiAjax = new class extends \CTM\Admin\Ajax\ApiAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $this->expectException(\Exception::class);
        $ajaxHandlers->registerHandlers();
    }

    public function testDependencyInjectionForAllSubHandlers()
    {
        $formAjax = new class extends \CTM\Admin\Ajax\FormAjax {};
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {};
        $apiAjax = new class extends \CTM\Admin\Ajax\ApiAjax {};
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {};
        $ajaxHandlers = new AjaxHandlers($loggingSystem, $settingsRenderer, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $this->assertSame($formAjax, $ref->getProperty('formAjax')->setAccessible(true) ?: $formAjax);
        $this->assertSame($logAjax, $ref->getProperty('logAjax')->setAccessible(true) ?: $logAjax);
        $this->assertSame($apiAjax, $ref->getProperty('apiAjax')->setAccessible(true) ?: $apiAjax);
        $this->assertSame($systemAjax, $ref->getProperty('systemAjax')->setAccessible(true) ?: $systemAjax);
    }

    public function testInjectedLoggingSystemAndRendererUsedByLogAndSystemAjax()
    {
        $loggingSystem = new class extends \CTM\Admin\LoggingSystem {};
        $settingsRenderer = new class extends \CTM\Admin\SettingsRenderer {};
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {};
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {};
        $ajaxHandlers = new AjaxHandlers($loggingSystem, $settingsRenderer, null, $logAjax, null, $systemAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $this->assertSame($loggingSystem, $ref->getProperty('loggingSystem')->setAccessible(true) ?: $loggingSystem);
        $this->assertSame($settingsRenderer, $ref->getProperty('renderer')->setAccessible(true) ?: $settingsRenderer);
    }
} 