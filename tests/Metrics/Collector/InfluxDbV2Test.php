<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\InfluxDbV2;
use InfluxDB2\Point;
use InfluxDB2\WriteApi;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InfluxDbV2Test extends TestCase
{
    private MockObject&WriteApi $writeApi;

    protected function setUp(): void
    {
        $this->writeApi = $this->getMockBuilder(WriteApi::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
    }

    public function testCollectIncrement(): void
    {
        $collector = $this->createCollector([]);

        $this->writeApi->expects($this->once())
            ->method('write')
            ->with($this->callback(function ($points) {
                $this->assertIsArray($points);
                $this->assertCount(1, $points);
                $this->assertInstanceOf(Point::class, $points[0]);
                $this->assertSame('series-name value=1i', $points[0]->toLineProtocol());

                return true;
            }))
        ;

        $collector->increment('series-name');
        $collector->flush();
    }

    public function testCollectDecrement(): void
    {
        $collector = $this->createCollector([]);

        $this->writeApi->expects($this->once())
            ->method('write')
            ->with($this->callback(function ($points) {
                $this->assertSame('series-name value=-1i', $points[0]->toLineProtocol());

                return true;
            }))
        ;

        $collector->decrement('series-name');
        $collector->flush();
    }

    public function testCollectTiming(): void
    {
        $collector = $this->createCollector([]);

        $this->writeApi->expects($this->once())
            ->method('write')
            ->with($this->callback(function ($points) {
                $this->assertSame('series-name value=47i', $points[0]->toLineProtocol());

                return true;
            }))
        ;

        $collector->timing('series-name', 47);
        $collector->flush();
    }

    public function testCollectMeasure(): void
    {
        $collector = $this->createCollector([]);

        $this->writeApi->expects($this->once())
            ->method('write')
            ->with($this->callback(function ($points) {
                $this->assertSame('series-name value=47i', $points[0]->toLineProtocol());

                return true;
            }))
        ;

        $collector->measure('series-name', 47);
        $collector->flush();
    }

    public function testCollectMeasureWithTags(): void
    {
        $collector = $this->createCollector(['dc' => 'west', 'node' => 'nemesis101']);

        $this->writeApi->expects($this->once())
            ->method('write')
            ->with($this->callback(function ($points) {
                $this->assertSame('series-name,dc=west,node=nemesis101 value=47i', $points[0]->toLineProtocol());

                return true;
            }))
        ;

        $collector->measure('series-name', 47);
        $collector->flush();
    }

    public function testCollectMeasureWithTagsMerged(): void
    {
        $collector = $this->createCollector(['dc' => 'west', 'node' => 'nemesis101']);

        $this->writeApi->expects($this->once())
            ->method('write')
            ->with($this->callback(function ($points) {
                $this->assertSame('series-name,dc=west,foo=bar,node=nemesis101 value=47i', $points[0]->toLineProtocol());

                return true;
            }))
        ;

        $collector->measure('series-name', 47, ['foo' => 'bar']);
        $collector->flush();
    }

    public function testFlushWithNoDataDoesNotCallWrite(): void
    {
        $collector = $this->createCollector([]);

        $this->writeApi->expects($this->never())->method('write');

        $collector->flush();
    }

    private function createCollector(array $tags): InfluxDbV2
    {
        return new InfluxDbV2($this->writeApi, $tags);
    }
}
