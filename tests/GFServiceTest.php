<?php

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;
use CTM\Service\GFService;
use CTM\Tests\Traits\MonkeyTrait;

class GFServiceTest extends TestCase
{
    use MonkeyTrait;
    protected GFService $gfService;

    protected function setUp(): void
    {
        parent::setUp();
        \Brain\Monkey\setUp();
        $this->initalMonkey();
        $this->gfService = new GFService();
        \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = null) {
            if (strpos($key, 'ctm_mapping_gf_') === 0) {
                return [];
            }
            if ($key === 'active_plugins') {
                return ['plugin1/plugin1.php', 'plugin2/plugin2.php'];
            }
            return 'test';
        });
        if (!class_exists('GFAPI')) {
            eval('class GFAPI { public static function get_form($id) { return ["id"=>$id, "title"=>"Test Form", "fields"=>[(object)["id"=>1, "label"=>"Field 1", "type"=>"text"]]]; } }');
        }
    }
    protected function tearDown(): void
    {
        \Brain\Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }

    public function testProcessSubmissionReturnsNullIfNoGFAPI()
    {
        // Simulate missing GFAPI or invalid data
        $result = $this->gfService->processSubmission([], []);
        $this->assertNull($result, 'Should return null if GFAPI is missing or data is invalid');
    }

    public function testProcessSubmissionHandlesValidEntry()
    {
        if (!class_exists('GFAPI')) {
            $this->markTestSkipped('GFAPI not available in test environment');
        }
        // Provide a minimal valid entry and form (mocked)
        $entry = ['id' => 1, 'date_created' => '2024-01-01 00:00:00'];
        $form = [
            'id' => 1,
            'title' => 'Test Form',
            'fields' => [(object)[
                'id' => 1,
                'label' => 'Field 1',
                'type' => 'text',
                'adminLabel' => null
            ]]
        ];
        $result = $this->gfService->processSubmission($entry, $form);
        $this->assertIsArray($result, 'Should return an array for valid entry');
    }


} 