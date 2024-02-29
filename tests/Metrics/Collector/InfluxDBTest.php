<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\InfluxDB;
use InfluxDB\Client;
use PHPUnit\Framework\TestCase;

class InfluxDBTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    private InfluxDB $collector;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->collector = new InfluxDB($this->client);
    }

    public function testCollectIncrement(): void
    {
        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 1]]], 'tags' => []];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->increment('series-name');
        $this->collector->flush();
    }

    public function testCollectDecrement(): void
    {
        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => -1]]], 'tags' => []];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->decrement('series-name');
        $this->collector->flush();
    }

    public function testCollectTiming(): void
    {
        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 47]]], 'tags' => []];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->timing('series-name', 47);
        $this->collector->flush();
    }

    public function testCollectMeasure(): void
    {
        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 47]]], 'tags' => []];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->measure('series-name', 47);
        $this->collector->flush();
    }

    public function testCollectMeasureWithTags(): void
    {
        $expectedTags = ['dc' => 'west', 'node' => 'nemesis101'];

        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 47]]], 'tags' => $expectedTags];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->setTags($expectedTags);
        $this->collector->measure('series-name', 47);
        $this->collector->flush();
    }
}
