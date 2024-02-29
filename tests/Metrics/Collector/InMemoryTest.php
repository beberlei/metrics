<?php
/**
 * Beberlei Metrics.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\InMemory;
use PHPUnit\Framework\TestCase;

class InMemoryTest extends TestCase
{
    const VARIABLE_A = 'variable_a';
    const VARIABLE_B = 'variable_b';

    /** @var InMemory */
    private $collector;

    public function setUp()
    {
        $this->collector = new InMemory();
    }

    public function testIncrement()
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->increment(self::VARIABLE_A);

        $this->collector->increment(self::VARIABLE_B);

        $this->assertEquals(2, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(1, $this->collector->getMeasure(self::VARIABLE_B));
    }

    public function testDecrement()
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->decrement(self::VARIABLE_A);

        $this->collector->decrement(self::VARIABLE_B);
        $this->collector->decrement(self::VARIABLE_B);

        $this->assertEquals(1, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(-2, $this->collector->getMeasure(self::VARIABLE_B));
    }

    public function testTiming()
    {
        $this->collector->timing(self::VARIABLE_A, 123);

        $this->collector->timing(self::VARIABLE_B, 111);
        $this->collector->timing(self::VARIABLE_B, 112);

        $this->assertEquals(123, $this->collector->getTiming(self::VARIABLE_A));
        $this->assertEquals(112, $this->collector->getTiming(self::VARIABLE_B));
    }

    public function testMeasure()
    {
        $this->collector->measure(self::VARIABLE_A, 2);
        $this->collector->measure(self::VARIABLE_A, -5);

        $this->collector->measure(self::VARIABLE_B, 123);
        $this->collector->measure(self::VARIABLE_B, 0);

        $this->assertEquals(-3, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(123, $this->collector->getMeasure(self::VARIABLE_B));
    }

    public function testSettingGauge()
    {
        $this->collector->gauge(self::VARIABLE_A, 2);
        $this->collector->gauge(self::VARIABLE_A, 5);

        $this->collector->gauge(self::VARIABLE_B, 123);
        $this->collector->gauge(self::VARIABLE_B, 0);

        $this->assertEquals(5, $this->collector->getGauge(self::VARIABLE_A));
        $this->assertEquals(0, $this->collector->getGauge(self::VARIABLE_B));
    }

    public function testIncrementingGauge()
    {
        $this->collector->gauge(self::VARIABLE_A, '10');
        $this->collector->gauge(self::VARIABLE_A, '+2');
        $this->collector->gauge(self::VARIABLE_A, '-3');

        $this->assertEquals(9, $this->collector->getGauge(self::VARIABLE_A));
    }

    public function testSettingGaugeToNegativeValue()
    {
        $this->collector->gauge(self::VARIABLE_A, 1); //sets to 1
        $this->collector->gauge(self::VARIABLE_A, 2); //sets to 2
        $this->collector->gauge(self::VARIABLE_A, -5); //decreases by 5
        $this->assertEquals(-3, $this->collector->getGauge(self::VARIABLE_A));

        $this->collector->gauge(self::VARIABLE_A, 0);
        $this->collector->gauge(self::VARIABLE_A, -5);
        $this->assertEquals(-5, $this->collector->getGauge(self::VARIABLE_A));
    }

    public function testTypesOfMetricsAreSeparate()
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->gauge(self::VARIABLE_A, 2);
        $this->collector->timing(self::VARIABLE_A, 3);

        $this->assertEquals(1, $this->collector->getMeasure(self::VARIABLE_A));
        $this->assertEquals(2, $this->collector->getGauge(self::VARIABLE_A));
        $this->assertEquals(3, $this->collector->getTiming(self::VARIABLE_A));
    }

    public function testFlushClearsData()
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
