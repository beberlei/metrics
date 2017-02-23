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

class InMemoryTest extends \PHPUnit_Framework_TestCase
{
    const VARIABLE_A = 'variable_a';
    const VARIABLE_B = 'variable_b';

    /** @var  InMemory */
    private $collector;

    public function setUp()
    {
        $this->collector = new InMemory();
    }

    public function test_increment()
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->increment(self::VARIABLE_A);

        $this->collector->increment(self::VARIABLE_B);

        $this->assertEquals(2, $this->collector->get(self::VARIABLE_A));
        $this->assertEquals(1, $this->collector->get(self::VARIABLE_B));
    }

    public function test_decrement()
    {
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->increment(self::VARIABLE_A);
        $this->collector->decrement(self::VARIABLE_A);

        $this->collector->decrement(self::VARIABLE_B);
        $this->collector->decrement(self::VARIABLE_B);

        $this->assertEquals(1, $this->collector->get(self::VARIABLE_A));
        $this->assertEquals(-2, $this->collector->get(self::VARIABLE_B));
    }

    public function test_timing()
    {
        $this->collector->timing(self::VARIABLE_A, 123);

        $this->collector->timing(self::VARIABLE_B, 111);
        $this->collector->timing(self::VARIABLE_B, 112);

        $this->assertEquals(123, $this->collector->get(self::VARIABLE_A));
        $this->assertEquals(112, $this->collector->get(self::VARIABLE_B));
    }

    public function test_measure()
    {
        $this->collector->measure(self::VARIABLE_A, 2);
        $this->collector->measure(self::VARIABLE_A, -5);

        $this->collector->measure(self::VARIABLE_B, 123);
        $this->collector->measure(self::VARIABLE_B, 0);

        $this->assertEquals(-3, $this->collector->get(self::VARIABLE_A));
        $this->assertEquals(123, $this->collector->get(self::VARIABLE_B));
    }

    public function test_gauge()
    {
        $this->collector->gauge(self::VARIABLE_A, 2);
        $this->collector->gauge(self::VARIABLE_A, -5);

        $this->collector->gauge(self::VARIABLE_B, 123);
        $this->collector->gauge(self::VARIABLE_B, 0);

        $this->assertEquals(-5, $this->collector->get(self::VARIABLE_A));
        $this->assertEquals(0, $this->collector->get(self::VARIABLE_B));
    }
}
