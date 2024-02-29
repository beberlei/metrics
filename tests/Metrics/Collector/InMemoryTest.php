<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\InMemory;
use PHPUnit\Framework\TestCase;

class InMemoryTest extends TestCase
{
    public const VARIABLE_A = 'variable_a';

    public const VARIABLE_B = 'variable_b';

    private InMemory $collector;

    protected function setUp(): void
    {
        $this->collector = new InMemory();
    }

    public function testIncrement(): void
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->increment(self::VARIABLE_A);

        $this->collector->increment(self::VARIABLE_B);

        $this->assertEquals(2, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(1, $this->collector->getMeasure(self::VARIABLE_B));
    }

    public function testDecrement(): void
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->decrement(self::VARIABLE_A);

        $this->collector->decrement(self::VARIABLE_B);
        $this->collector->decrement(self::VARIABLE_B);

        $this->assertEquals(1, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(-2, $this->collector->getMeasure(self::VARIABLE_B));
    }

    public function testTiming(): void
    {
        $this->collector->timing(self::VARIABLE_A, 123);

        $this->collector->timing(self::VARIABLE_B, 111);
        $this->collector->timing(self::VARIABLE_B, 112);

        $this->assertEquals(123, $this->collector->getTiming(self::VARIABLE_A));
        $this->assertEquals(112, $this->collector->getTiming(self::VARIABLE_B));
    }

    public function testMeasure(): void
    {
        $this->collector->measure(self::VARIABLE_A, 2);
        $this->collector->measure(self::VARIABLE_A, -5);

        $this->collector->measure(self::VARIABLE_B, 123);
        $this->collector->measure(self::VARIABLE_B, 0);

        $this->assertEquals(-3, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(123, $this->collector->getMeasure(self::VARIABLE_B));
    }

    public function testSettingGauge(): void
    {
        $this->collector->gauge(self::VARIABLE_A, 2);
        $this->collector->gauge(self::VARIABLE_A, 5);

        $this->collector->gauge(self::VARIABLE_B, 123);
        $this->collector->gauge(self::VARIABLE_B, 0);

        $this->assertEquals(5, $this->collector->getGauge(self::VARIABLE_A));
        $this->assertEquals(0, $this->collector->getGauge(self::VARIABLE_B));
    }

    public function testIncrementingGauge(): void
    {
        $this->collector->gauge(self::VARIABLE_A, 10);
        $this->collector->gauge(self::VARIABLE_A, '+2');
        $this->collector->gauge(self::VARIABLE_A, '-3');

        $this->assertEquals(9, $this->collector->getGauge(self::VARIABLE_A));
    }

    public function testSettingGaugeToNegativeValue(): void
    {
        $this->collector->gauge(self::VARIABLE_A, 1); // sets to 1
        $this->collector->gauge(self::VARIABLE_A, 2); // sets to 2
        $this->collector->gauge(self::VARIABLE_A, '-5'); // decreases by 5
        $this->assertEquals(-3, $this->collector->getGauge(self::VARIABLE_A));

        $this->collector->gauge(self::VARIABLE_A, 0);
        $this->collector->gauge(self::VARIABLE_A, '-5');
        $this->assertEquals(-5, $this->collector->getGauge(self::VARIABLE_A));
    }

    public function testTypesOfMetricsAreSeparate(): void
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->gauge(self::VARIABLE_A, 2);
        $this->collector->timing(self::VARIABLE_A, 3);

        $this->assertEquals(1, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(2, $this->collector->getGauge(self::VARIABLE_A));
        $this->assertEquals(3, $this->collector->getTiming(self::VARIABLE_A));
    }

    public function testFlushClearsData(): void
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->gauge(self::VARIABLE_A, 2);
        $this->collector->timing(self::VARIABLE_A, 3);

        $this->collector->flush();

        $this->assertEquals(0, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(0, $this->collector->getGauge(self::VARIABLE_A));
        $this->assertEquals(0, $this->collector->getTiming(self::VARIABLE_A));
    }
}
