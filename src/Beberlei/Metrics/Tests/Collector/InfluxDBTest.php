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
        $expectedArgs = array(
            'points' => array(
                array(
                    'measurement' => 'series-name',
                    'fields' => array('value' => 1),
                ),
            ),
            'tags' => array(),
        );

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs);

        $this->collector->increment('series-name');
        $this->collector->flush();
    }

    public function testCollectDecrement()
    {
        $expectedArgs = array(
            'points' => array(
                array(
                    'measurement' => 'series-name',
                    'fields' => array('value' => -1),
                ),
            ),
            'tags' => array(),
        );

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs);

        $this->collector->decrement('series-name');
        $this->collector->flush();
    }

    public function testCollectTiming()
    {
        $expectedArgs = array(
            'points' => array(
                array(
                    'measurement' => 'series-name',
                    'fields' => array('value' => 47.11),
                ),
            ),
            'tags' => array(),
        );

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs);

        $this->collector->timing('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasure()
    {
        $expectedArgs = array(
            'points' => array(
                array(
                    'measurement' => 'series-name',
                    'fields' => array('value' => 47.11),
                ),
            ),
            'tags' => array(),
        );

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs);

        $this->collector->measure('series-name', 47.11);
        $this->collector->flush();
    }

    public function testCollectMeasureWithTags()
    {
        $expectedTags = array(
            'dc' => 'west',
            'node' => 'nemesis101',
        );

        $expectedArgs = array(
            'points' => array(
                array(
                    'measurement' => 'series-name',
                    'fields' => array('value' => 47.11),
                ),
            ),
            'tags' => $expectedTags,
        );

        $this->client->expects($this->once())
            ->method('mark')
            ->with($expectedArgs);

        $this->collector->setTags($expectedTags);
        $this->collector->measure('series-name', 47.11);
        $this->collector->flush();
    }

	public function testCollectMeasureWithInlineTags()
    {
        $globalTags = array(
            'dc' => 'west',
            'node' => 'nemesis101',
			);

		$inlineTags = array(
			'resource' => 'stuff'
			);

		$expectedTags = array_merge($globalTags, $inlineTags);

        $expectedArgs = array(
            'points' => array(
                array(
                    'measurement' => 'series-name',
                    'fields' => array('value' => 47.11),
					),
				),
            'tags' => $expectedTags,
			);

		$expectedArgs2 = array(
            'points' => array(
                array(
                    'measurement' => 'series-name-2',
                    'fields' => array('value' => 53.22),
					),
				),
            'tags' => $globalTags,
			);

        $this->client->expects($this->exactly(2))
            ->method('mark');

        $this->client->expects($this->at(0))
            ->method('mark')
            ->with($expectedArgs);

		$this->client->expects($this->at(1))
            ->method('mark')
            ->with($expectedArgs2);

        $this->collector->setTags($globalTags);
        $this->collector->measure('series-name', 47.11, $inlineTags);
        $this->collector->measure('series-name-2', 53.22);
        $this->collector->flush();
    }
}
