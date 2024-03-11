<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\InfluxDbV1;
use InfluxDB\Database;
use InfluxDB\Point;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfluxDbV1Test extends TestCase
{
    private MockObject&Database $database;

    protected function setUp(): void
    {
        $this->database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testCollectIncrement(): void
    {
        $collector = $this->createCollector([]);

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->isType('array'))
        ;

        $collector->increment('series-name');
        $collector->flush();
    }

    public function testCollectDecrement(): void
    {
        $collector = $this->createCollector([]);

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->callback(function ($arg0) {
                $this->assertIsArray($arg0);
                $this->assertCount(1, $arg0);
                $this->assertArrayHasKey(0, $arg0);
                $point = $arg0[0];
                $this->assertInstanceOf(Point::class, $point);
                $this->assertSame('series-name', $point->getMeasurement());
                $this->assertSame(['value' => '-1i'], $point->getFields());
                $this->assertSame([], $point->getTags());

                return true;
            }))
        ;

        $collector->decrement('series-name');
        $collector->flush();
    }

    public function testCollectTiming(): void
    {
        $collector = $this->createCollector([]);

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->callback(function ($arg0) {
                $this->assertIsArray($arg0);
                $this->assertCount(1, $arg0);
                $this->assertArrayHasKey(0, $arg0);
                $point = $arg0[0];
                $this->assertInstanceOf(Point::class, $point);
                $this->assertSame('series-name', $point->getMeasurement());
                $this->assertSame(['value' => '47i'], $point->getFields());
                $this->assertSame([], $point->getTags());

                return true;
            }))
        ;

        $collector->timing('series-name', 47);
        $collector->flush();
    }

    public function testCollectMeasure(): void
    {
        $collector = $this->createCollector([]);

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->callback(function ($arg0) {
                $this->assertIsArray($arg0);
                $this->assertCount(1, $arg0);
                $this->assertArrayHasKey(0, $arg0);
                $point = $arg0[0];
                $this->assertInstanceOf(Point::class, $point);
                $this->assertSame('series-name', $point->getMeasurement());
                $this->assertSame(['value' => '47i'], $point->getFields());
                $this->assertSame([], $point->getTags());

                return true;
            }))
        ;

        $collector->measure('series-name', 47);
        $collector->flush();
    }

    public function testCollectMeasureWithTags(): void
    {
        $expectedTags = ['dc' => 'west', 'node' => 'nemesis101'];
        $collector = $this->createCollector($expectedTags);

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->callback(function ($arg0) use ($expectedTags) {
                $this->assertIsArray($arg0);
                $this->assertCount(1, $arg0);
                $this->assertArrayHasKey(0, $arg0);
                $point = $arg0[0];
                $this->assertInstanceOf(Point::class, $point);
                $this->assertSame('series-name', $point->getMeasurement());
                $this->assertSame(['value' => '47i'], $point->getFields());
                $this->assertSame($expectedTags, $point->getTags());

                return true;
            }))
        ;

        $collector->measure('series-name', 47);
        $collector->flush();
    }

    public function testCollectMeasureWithTagsMerged(): void
    {
        $collector = $this->createCollector(['dc' => 'west', 'node' => 'nemesis101']);

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->callback(function ($arg0) {
                $this->assertIsArray($arg0);
                $this->assertCount(1, $arg0);
                $this->assertArrayHasKey(0, $arg0);
                $point = $arg0[0];
                $this->assertInstanceOf(Point::class, $point);
                $this->assertSame('series-name', $point->getMeasurement());
                $this->assertSame(['value' => '47i'], $point->getFields());
                $this->assertSame(['dc' => 'west', 'node' => 'nemesis101', 'foo' => 'bar'], $point->getTags());

                return true;
            }))
        ;

        $collector->measure('series-name', 47, ['foo' => 'bar']);
        $collector->flush();
    }

    private function createCollector(array $tags): InfluxDbV1
    {
        return new InfluxDbV1($this->database, $tags);
    }
}
