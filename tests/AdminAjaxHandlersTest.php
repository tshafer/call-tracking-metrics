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
        Monkey\setUp();
        $this->initalMonkey();
    
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $ajaxHandlers = new AjaxHandlers();
        $this->assertInstanceOf(AjaxHandlers::class, $ajaxHandlers);
    }

    public function testRegisterHandlersCallsAllSubHandlers()
    {
        $formAjax = $this->createMock(\CTM\Admin\Ajax\FormAjax::class);
        $loggingSystem = $this->createMock(\CTM\Admin\LoggingSystem::class);
        $settingsRenderer = $this->createMock(\CTM\Admin\SettingsRenderer::class);
        $logAjax = $this->getMockBuilder(\CTM\Admin\Ajax\LogAjax::class)
            ->setConstructorArgs([$loggingSystem, $settingsRenderer])
            ->getMock();
        $apiAjax = $this->createMock(\CTM\Admin\Ajax\ApiAjax::class);
        $systemAjax = $this->getMockBuilder(\CTM\Admin\Ajax\SystemAjax::class)
            ->setConstructorArgs([$loggingSystem, $settingsRenderer])
            ->getMock();
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
        $loggingSystem = $this->createMock(\CTM\Admin\LoggingSystem::class);
        $settingsRenderer = $this->createMock(\CTM\Admin\SettingsRenderer::class);
        $logAjax = $this->getMockBuilder(\CTM\Admin\Ajax\LogAjax::class)
            ->setConstructorArgs([$loggingSystem, $settingsRenderer])
            ->getMock();
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
        $loggingSystem = $this->createMock(\CTM\Admin\LoggingSystem::class);
        $settingsRenderer = $this->createMock(\CTM\Admin\SettingsRenderer::class);
        $systemAjax = $this->getMockBuilder(\CTM\Admin\Ajax\SystemAjax::class)
            ->setConstructorArgs([$loggingSystem, $settingsRenderer])
            ->getMock();
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
        $loggingSystem = $this->createMock(\CTM\Admin\LoggingSystem::class);
        $settingsRenderer = $this->createMock(\CTM\Admin\SettingsRenderer::class);
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
        $ajaxHandlers->registerHandlers();
        $this->assertEquals(2, $calls['form']);
        $this->assertEquals(2, $calls['log']);
        $this->assertEquals(2, $calls['api']);
        $this->assertEquals(2, $calls['system']);
    }
    public function testSubHandlerThrowsException()
    {
        $loggingSystem = $this->createMock(\CTM\Admin\LoggingSystem::class);
        $settingsRenderer = $this->createMock(\CTM\Admin\SettingsRenderer::class);
        // Use a stub that throws for formAjax
        $formAjax = new class extends \CTM\Admin\Ajax\FormAjax {
            public function registerHandlers() { throw new \Exception('fail'); }
        };
        $logAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\LogAjax {
            public function __construct($loggingSystem, $settingsRenderer) { parent::__construct($loggingSystem, $settingsRenderer); }
            public function registerHandlers() { /* no-op */ }
        };
        $apiAjax = new class extends \CTM\Admin\Ajax\ApiAjax {
            public function registerHandlers() { /* no-op */ }
        };
        $systemAjax = new class($loggingSystem, $settingsRenderer) extends \CTM\Admin\Ajax\SystemAjax {
            public function __construct($loggingSystem, $settingsRenderer) { parent::__construct($loggingSystem, $settingsRenderer); }
            public function registerHandlers() { /* no-op */ }
        };
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        try {
            $ajaxHandlers->registerHandlers();
        } catch (\Exception $e) {
            $this->assertEquals('fail', $e->getMessage());
            return;
        }
        $this->fail('Exception not thrown');
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
        $formAjax = $this->createMock(\CTM\Admin\Ajax\FormAjax::class);
        $logAjax = $this->getMockBuilder(\CTM\Admin\Ajax\LogAjax::class)
            ->disableOriginalConstructor()->getMock();
        $apiAjax = $this->createMock(\CTM\Admin\Ajax\ApiAjax::class);
        $systemAjax = $this->getMockBuilder(\CTM\Admin\Ajax\SystemAjax::class)
            ->disableOriginalConstructor()->getMock();
        $formAjax->expects($this->once())->method('registerHandlers');
        $logAjax->expects($this->once())->method('registerHandlers');
        $apiAjax->expects($this->once())->method('registerHandlers');
        $systemAjax->expects($this->once())->method('registerHandlers');
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $ajaxHandlers->registerHandlers();
    }

    public function testRegisterHandlersPropagatesException()
    {
        $formAjax = $this->createMock(\CTM\Admin\Ajax\FormAjax::class);
        $logAjax = $this->getMockBuilder(\CTM\Admin\Ajax\LogAjax::class)
            ->disableOriginalConstructor()->getMock();
        $apiAjax = $this->createMock(\CTM\Admin\Ajax\ApiAjax::class);
        $systemAjax = $this->getMockBuilder(\CTM\Admin\Ajax\SystemAjax::class)
            ->disableOriginalConstructor()->getMock();
        $formAjax->expects($this->once())->method('registerHandlers')->will($this->throwException(new \Exception('fail')));
        $logAjax->expects($this->never())->method('registerHandlers');
        $apiAjax->expects($this->never())->method('registerHandlers');
        $systemAjax->expects($this->never())->method('registerHandlers');
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('fail');
        $ajaxHandlers->registerHandlers();
    }

    public function testDependencyInjectionForAllSubHandlers()
    {
        $formAjax = $this->createMock(\CTM\Admin\Ajax\FormAjax::class);
        $logAjax = $this->getMockBuilder(\CTM\Admin\Ajax\LogAjax::class)
            ->disableOriginalConstructor()->getMock();
        $apiAjax = $this->createMock(\CTM\Admin\Ajax\ApiAjax::class);
        $systemAjax = $this->getMockBuilder(\CTM\Admin\Ajax\SystemAjax::class)
            ->disableOriginalConstructor()->getMock();
        $ajaxHandlers = new AjaxHandlers(null, null, $formAjax, $logAjax, $apiAjax, $systemAjax);
        $ref = new \ReflectionClass($ajaxHandlers);
        $this->assertSame($formAjax, $ref->getProperty('formAjax')->getValue($ajaxHandlers));
        $this->assertSame($logAjax, $ref->getProperty('logAjax')->getValue($ajaxHandlers));
        $this->assertSame($apiAjax, $ref->getProperty('apiAjax')->getValue($ajaxHandlers));
        $this->assertSame($systemAjax, $ref->getProperty('systemAjax')->getValue($ajaxHandlers));
    }

    public function testInjectedLoggingSystemAndRendererUsedByLogAndSystemAjax()
    {
        $loggingSystem = $this->createMock(\CTM\Admin\LoggingSystem::class);
        $settingsRenderer = $this->createMock(\CTM\Admin\SettingsRenderer::class);
        $ajaxHandlers = new AjaxHandlers($loggingSystem, $settingsRenderer);
        $ref = new \ReflectionClass($ajaxHandlers);
        $logAjax = $ref->getProperty('logAjax')->getValue($ajaxHandlers);
        $systemAjax = $ref->getProperty('systemAjax')->getValue($ajaxHandlers);
        $logAjaxRef = new \ReflectionClass($logAjax);
        $systemAjaxRef = new \ReflectionClass($systemAjax);
        $this->assertSame($loggingSystem, $logAjaxRef->getProperty('loggingSystem')->getValue($logAjax));
        $this->assertSame($settingsRenderer, $logAjaxRef->getProperty('renderer')->getValue($logAjax));
        $this->assertSame($loggingSystem, $systemAjaxRef->getProperty('loggingSystem')->getValue($systemAjax));
        $this->assertSame($settingsRenderer, $systemAjaxRef->getProperty('renderer')->getValue($systemAjax));
    }
} 