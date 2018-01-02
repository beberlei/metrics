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

use Beberlei\Metrics\Collector\Prometheus;
use PHPUnit\Framework\TestCase;
use Prometheus\Exception\MetricNotFoundException;

class PrometheusTest extends TestCase
{
    const TEST_NAMESPACE = 'some_metric_namespace';
    const TEST_VARIABLE_NAME = 'some_variable_name';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $collectorRegistryMock;

    /**
     * @var Prometheus
     */
    private $collector;

    protected function setUp()
    {
        $this->collectorRegistryMock = $this->getMockBuilder('\\Prometheus\\CollectorRegistry')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->collector = new Prometheus($this->collectorRegistryMock, self::TEST_NAMESPACE);
    }

    public function testMeasure()
    {
        $expectedVariableValue = 123;

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, array())
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    public function testMeasureWithTags()
    {
        $expectedVariableValue = 123;
        $expectedTagsValues = array('value1', 'value2');

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, $expectedTagsValues)
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->setTags(array(
            'tag1' => 'value1',
            'tag2' => 'value2',
        ));

        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    public function testIncrement()
    {
        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('inc')
            ->with(array())
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->increment(self::TEST_VARIABLE_NAME);
        $this->collector->flush();
    }

    public function testIncrementWithTags()
    {
        $expectedTagsValues = array('value1', 'value2');

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('inc')
            ->with($expectedTagsValues)
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->setTags(array(
            'tag1' => 'value1',
            'tag2' => 'value2',
        ));

        $this->collector->increment(self::TEST_VARIABLE_NAME);
        $this->collector->flush();
    }

    public function testDecrement()
    {
        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('dec')
            ->with(array())
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->decrement(self::TEST_VARIABLE_NAME);
        $this->collector->flush();
    }

    public function testDecrementWithTags()
    {
        $expectedTagsValues = array('value1', 'value2');

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('dec')
            ->with($expectedTagsValues)
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->setTags(array(
            'tag1' => 'value1',
            'tag2' => 'value2',
        ));

        $this->collector->decrement(self::TEST_VARIABLE_NAME);
        $this->collector->flush();
    }

    public function testTiming()
    {
        $expectedVariableValue = 123;

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, array())
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    public function testTimingWithTags()
    {
        $expectedVariableValue = 123;
        $expectedTagsValues = array('value1', 'value2');

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, $expectedTagsValues)
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->setTags(array(
            'tag1' => 'value1',
            'tag2' => 'value2',
        ));

        $this->collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    public function testMeasureWhenSetNewVariableWithTags()
    {
        $expectedVariableValue = 123;
        $expectedTagsNames = array('tag1', 'tag2');
        $expectedTagsValues = array('value1', 'value2');

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, $expectedTagsValues)
        ;

        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willThrowException(new MetricNotFoundException())
        ;
        $this->collectorRegistryMock
            ->expects($this->once())
            ->method('registerGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME, '', $expectedTagsNames)
            ->willReturn($gaugeMock)
        ;

        $this->collector->setTags(array(
            'tag1' => 'value1',
            'tag2' => 'value2',
        ));

        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    /**
     * Method flush must to reset value of field `data`.
     */
    public function testFlushWhenCallsTwiceWithDifferentData()
    {
        $firstExpectedVariableValue = 123;
        $secondExpectedVariableValue = 321;

        $gaugeMock = $this->getMockBuilder('\\Prometheus\\Gauge')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->at(0))
            ->method('set')
            ->with($firstExpectedVariableValue, array())
        ;
        $gaugeMock
            ->expects($this->at(1))
            ->method('set')
            ->with($secondExpectedVariableValue, array())
        ;

        $this->collectorRegistryMock
            ->expects($this->exactly(2))
            ->method('getGauge')
            ->with(self::TEST_NAMESPACE, self::TEST_VARIABLE_NAME)
            ->willReturn($gaugeMock)
        ;

        $this->collector->measure(self::TEST_VARIABLE_NAME, $firstExpectedVariableValue);
        $this->collector->flush();

        $this->collector->measure(self::TEST_VARIABLE_NAME, $secondExpectedVariableValue);
        $this->collector->flush();
    }
}
