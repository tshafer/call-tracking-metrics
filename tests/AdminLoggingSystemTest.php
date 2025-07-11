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
} 