<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\AjaxHandlers;
use Brain\Monkey;

class AdminAjaxHandlersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
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
    public function testRegisterHandlersDoesNotThrow()
    {
        $ajaxHandlers = new AjaxHandlers();
        $this->expectNotToPerformAssertions();
        $ajaxHandlers->registerHandlers();
    }
} 