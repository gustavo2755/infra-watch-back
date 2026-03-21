<?php

declare(strict_types=1);

namespace Tests\Monitoring;

use App\Monitoring\MockMetricGenerator;
use PHPUnit\Framework\TestCase;

final class MockMetricGeneratorTest extends TestCase
{
    private MockMetricGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MockMetricGenerator();
    }

    public function testGenerateReturnsArrayWithExpectedKeys(): void
    {
        $result = $this->generator->generate();

        $this->assertArrayHasKey('cpu_usage_percent', $result);
        $this->assertArrayHasKey('ram_usage_percent', $result);
        $this->assertArrayHasKey('disk_usage_percent', $result);
        $this->assertArrayHasKey('bandwidth_usage_percent', $result);
        $this->assertCount(4, $result);
    }

    public function testGenerateReturnsFloatsInZeroToFiftyRange(): void
    {
        for ($i = 0; $i < 50; $i++) {
            $result = $this->generator->generate();

            foreach ($result as $key => $value) {
                $this->assertIsFloat($value, "Key $key");
                $this->assertGreaterThanOrEqual(0.0, $value, "Key $key");
                $this->assertLessThanOrEqual(50.0, $value, "Key $key");
            }
        }
    }

    public function testRandomMetricReturnsFloatInRange(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $value = $this->generator->randomMetric();
            $this->assertIsFloat($value);
            $this->assertGreaterThanOrEqual(0.0, $value);
            $this->assertLessThanOrEqual(50.0, $value);
        }
    }
}
