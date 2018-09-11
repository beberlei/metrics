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

use InfluxDB\Database;
use InfluxDB\Point;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Beberlei\Metrics\Collector\InfluxDB;

class InfluxDBTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $database;

    /**
     * @var InfluxDB
     */
    private $collector;

    protected function setUp()
    {
        $this->database  = $this->getMockBuilder('\\InfluxDB\\Database')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->collector = new InfluxDB($this->database);
    }

    public function testCollectIncrement()
    {
        $time         = new \DateTime();
        $expectedArgs = array(
            new Point(
                'series-name',
                1,
                array(),
                array(),
                $time->getTimestamp()
            ),
        );

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($expectedArgs)
        ;

        $this->collector->increment('series-name');
        $this->collector->flush();
    }

    public function testCollectDecrement()
    {
        $time         = new \DateTime();
        $expectedArgs = array(
            new Point(
                'series-name',
                -1,
                array(),
                array(),
                $time->getTimestamp()
            ),
        );

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($expectedArgs, Database::PRECISION_SECONDS)
        ;

        $this->collector->decrement('series-name');
        $this->collector->flush();
    }

    public function testCollectTiming()
    {
        $time         = new \DateTime();
        $expectedArgs = array(
            new Point(
                'series-name',
                47.11,
                array(),
                array(),
                $time->getTimestamp()
            ),
        );

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($expectedArgs, Database::PRECISION_SECONDS)
        ;

        $this->collector->timing('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasure()
    {
        $time         = new \DateTime();
        $expectedArgs = array(
            new Point(
                'series-name',
                47.11,
                array(),
                array(),
                $time->getTimestamp()
            ),
        );

        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($expectedArgs, Database::PRECISION_SECONDS)
        ;

        $this->collector->measure('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasureWithTags()
    {
        $expectedTags = array(
            'dc'   => 'west',
            'node' => 'nemesis101',
        );
        $time         = new \DateTime();
        $expectedArgs = array(
            new Point(
                'series-name',
                47.11,
                $expectedTags,
                array(),
                $time->getTimestamp()
            ),
        );


        $this->database->expects($this->once())
            ->method('writePoints')
            ->with($expectedArgs, Database::PRECISION_SECONDS)
        ;

        $this->collector->setTags($expectedTags);
        $this->collector->measure('series-name', 47.11);
        $this->collector->flush();
    }
}
