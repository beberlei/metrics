<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\InfluxDB;
use InfluxDB\Database;
use InfluxDB\Point;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfluxDBTest extends TestCase
{
    private MockObject&Database $database;

    private InfluxDB $collector;

    protected function setUp(): void
    {
        $this->database = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->collector = new InfluxDB($this->database);
    }

    public function testCollectIncrement(): void
    {
        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($this->isType('array'))
        ;

        $this->collector->increment('series-name');
        $this->collector->flush();
    }

    public function testCollectDecrement(): void
    {
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

        $this->collector->decrement('series-name');
        $this->collector->flush();
    }

    public function testCollectTiming(): void
    {
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

        $this->collector->timing('series-name', 47);
        $this->collector->flush();
    }

    public function testCollectMeasure(): void
    {
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

        $this->collector->measure('series-name', 47);
        $this->collector->flush();
    }

    public function testCollectMeasureWithTags(): void
    {
        $expectedTags = ['dc' => 'west', 'node' => 'nemesis101'];

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

        $this->collector->setTags($expectedTags);
        $this->collector->measure('series-name', 47);
        $this->collector->flush();
    }

    public function testCollectMeasureWithTagsMerged(): void
    {
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

        $collector = new InfluxDB($this->database, ['dc' => 'west', 'node' => 'nemesis101']);
        $collector->measure('series-name', 47, ['foo' => 'bar']);
        $collector->flush();
    }
}
