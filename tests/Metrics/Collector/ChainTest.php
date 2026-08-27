<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\Chain;
use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\InMemory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ChainTest extends TestCase
{
    public function testMeasureIsDispatchedToEveryCollector(): void
    {
        $tags = ['dc' => 'west'];

        $a = $this->createMock(CollectorInterface::class);
        $a->expects($this->once())->method('measure')->with('foo.bar', 5, $tags);

        $b = $this->createMock(CollectorInterface::class);
        $b->expects($this->once())->method('measure')->with('foo.bar', 5, $tags);

        new Chain($a, $b)->measure('foo.bar', 5, $tags);
    }

    public function testIncrementIsDispatchedToEveryCollector(): void
    {
        $tags = ['dc' => 'west'];

        $a = $this->createMock(CollectorInterface::class);
        $a->expects($this->once())->method('increment')->with('foo.bar', $tags);

        $b = $this->createMock(CollectorInterface::class);
        $b->expects($this->once())->method('increment')->with('foo.bar', $tags);

        new Chain($a, $b)->increment('foo.bar', $tags);
    }

    public function testDecrementIsDispatchedToEveryCollector(): void
    {
        $tags = ['dc' => 'west'];

        $a = $this->createMock(CollectorInterface::class);
        $a->expects($this->once())->method('decrement')->with('foo.bar', $tags);

        $b = $this->createMock(CollectorInterface::class);
        $b->expects($this->once())->method('decrement')->with('foo.bar', $tags);

        new Chain($a, $b)->decrement('foo.bar', $tags);
    }

    public function testTimingIsDispatchedToEveryCollector(): void
    {
        $tags = ['dc' => 'west'];

        $a = $this->createMock(CollectorInterface::class);
        $a->expects($this->once())->method('timing')->with('foo.bar', 123, $tags);

        $b = $this->createMock(CollectorInterface::class);
        $b->expects($this->once())->method('timing')->with('foo.bar', 123, $tags);

        new Chain($a, $b)->timing('foo.bar', 123, $tags);
    }

    public function testFlushIsDispatchedToEveryCollector(): void
    {
        $a = $this->createMock(CollectorInterface::class);
        $a->expects($this->once())->method('flush');

        $b = $this->createMock(CollectorInterface::class);
        $b->expects($this->once())->method('flush');

        new Chain($a, $b)->flush();
    }

    public function testGaugeIsOnlyDispatchedToGaugeableCollectors(): void
    {
        $gaugeable = new InMemory();

        /** @var CollectorInterface&MockObject $notGaugeable */
        $notGaugeable = $this->createMock(CollectorInterface::class);
        $notGaugeable->expects($this->never())->method($this->anything());

        new Chain($gaugeable, $notGaugeable)->gauge('foo.bar', 5);

        $this->assertSame(5, $gaugeable->getGauge('foo.bar'));
    }

    public function testFaultyFirstCollectorDoesNotPreventOthersFromBeingCalled(): void
    {
        $tags = ['dc' => 'west'];

        $a = $this->createMock(CollectorInterface::class);
        $a->expects($this->once())->method('measure')->willThrowException(new \RuntimeException('boom'));

        $b = $this->createMock(CollectorInterface::class);
        $b->expects($this->once())->method('measure')->with('foo.bar', 5, $tags);

        new Chain($a, $b)->measure('foo.bar', 5, $tags);
    }

    public function testEmptyChainDoesNotFail(): void
    {
        $chain = new Chain();

        $chain->measure('foo.bar', 1);
        $chain->increment('foo.bar');
        $chain->decrement('foo.bar');
        $chain->timing('foo.bar', 1);
        $chain->gauge('foo.bar', 1);
        $chain->flush();

        $this->addToAssertionCount(1);
    }
}
