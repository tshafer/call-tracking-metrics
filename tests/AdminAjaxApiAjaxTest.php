<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\ApiAjax;
use Brain\Monkey;

class AdminAjaxApiAjaxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true);
        \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v){return $v;});
        \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null);
        \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null);
        \Brain\Monkey\Functions\when('current_time')->justReturn('2024-01-01 00:00:00');
        \Brain\Monkey\Functions\when('wp_generate_uuid4')->justReturn('uuid-1234');
        \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('5.8');
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('get_option')->justReturn('test');
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $apiAjax = new ApiAjax();
        $this->assertInstanceOf(ApiAjax::class, $apiAjax);
    }
    public function testAssessConnectionQualityReturnsArray()
    {
        $apiAjax = new ApiAjax();
        $result = (new \ReflectionClass($apiAjax))->getMethod('assessConnectionQuality');
        $result->setAccessible(true);
        $output = $result->invoke($apiAjax, 100, 200);
        $this->assertIsArray($output);
        $this->assertArrayHasKey('rating', $output);
    }
} 