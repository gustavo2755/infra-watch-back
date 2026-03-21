<?php

declare(strict_types=1);

namespace Tests\Monitoring;

use App\Monitoring\MockServiceCheckGenerator;
use PHPUnit\Framework\TestCase;

final class MockServiceCheckGeneratorTest extends TestCase
{
    private MockServiceCheckGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MockServiceCheckGenerator();
    }

    public function testGenerateReturnsArrayWithExpectedKeys(): void
    {
        $result = $this->generator->generate();

        $this->assertArrayHasKey('is_running', $result);
        $this->assertArrayHasKey('output_message', $result);
        $this->assertCount(2, $result);
    }

    public function testGenerateReturnsValidTypes(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $result = $this->generator->generate();

            $this->assertIsBool($result['is_running']);
            $this->assertIsString($result['output_message']);
        }
    }

    public function testOutputMessageMatchesIsRunning(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $result = $this->generator->generate();

            if ($result['is_running']) {
                $this->assertSame('Service is running', $result['output_message']);
            } else {
                $this->assertSame('Service is not running', $result['output_message']);
            }
        }
    }
}
