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
        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 47.11]]], 'tags' => []];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->timing('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasure(): void
    {
        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 47.11]]], 'tags' => []];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->measure('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasureWithTags(): void
    {
        $expectedTags = ['dc' => 'west', 'node' => 'nemesis101'];

        $expectedArgs = ['points' => [['measurement' => 'series-name', 'fields' => ['value' => 47.11]]], 'tags' => $expectedTags];

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs)
        ;

        $this->collector->setTags($expectedTags);
        $this->collector->measure('series-name', 47.11);
        $this->collector->flush();
    }
}
