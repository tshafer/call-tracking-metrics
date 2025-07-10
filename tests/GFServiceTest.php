<?php

namespace CTM\Tests;

use PHPUnit\Framework\TestCase;
use CTM\Service\GFService;

class GFServiceTest extends TestCase
{
    protected GFService $gfService;

    protected function setUp(): void
    {
        $this->gfService = new GFService();
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
        $form = ['id' => 1, 'title' => 'Test Form'];
        $result = $this->gfService->processSubmission($entry, $form);
        $this->assertIsArray($result, 'Should return an array for valid entry');
    }

    // Add more tests for field mapping, multipart fields, and error cases
} 