<?php
/**
 * Beberlei Metrics
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

use PHPUnit_Framework_MockObject_MockObject;
use Beberlei\Metrics\Collector\InfluxDB;

class InfluxDBTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $client;

    /**
     * @var InfluxDB
     */
    private $collector;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder('\\InfluxDB\\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $this->collector = new InfluxDB($this->client);
    }

    public function testCollectIncrement()
    {
        $this->client->expects($this->once())
            ->method('mark')
            ->with('series-name', array('value' => 1));

        $this->collector->increment('series-name');
        $this->collector->flush();
    }

    public function testCollectDecrement()
    {
        $this->client->expects($this->once())
            ->method('mark')
            ->with('series-name', array('value' => -1));

        $this->collector->decrement('series-name');
        $this->collector->flush();
    }

    public function testCollectTiming()
    {
        $this->client->expects($this->once())
            ->method('mark')
            ->with('series-name', array('value' => 47.11));

        $this->collector->timing('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasure()
    {
        $this->client->expects($this->once())
            ->method('mark')
            ->with('series-name', array('value' => 47.11));

        $this->collector->timing('series-name', 47.11);
        $this->collector->flush();
    }
}
