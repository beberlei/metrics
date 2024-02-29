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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Gauge;

class PrometheusTest extends TestCase
{
    public const TEST_NAMESPACE = 'some_metric_namespace';

    public const TEST_VARIABLE_NAME = 'some_variable_name';

    private MockObject&CollectorRegistry $collectorRegistryMock;

    private Prometheus $collector;

    protected function setUp(): void
    {
        $this->collectorRegistryMock = $this->getMockBuilder(CollectorRegistry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->collector = new Prometheus($this->collectorRegistryMock, self::TEST_NAMESPACE);
    }

    public function testMeasure(): void
    {
        $expectedVariableValue = 123;

        $gaugeMock = $this->getMockBuilder(Gauge::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, [])
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

    public function testMeasureWithTags(): void
    {
        $expectedVariableValue = 123;
        $expectedTagsValues = ['value1', 'value2'];

        $gaugeMock = $this->getMockBuilder(Gauge::class)
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

        $this->collector->setTags(['tag1' => 'value1', 'tag2' => 'value2']);

        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    public function testIncrement(): void
    {
        $gaugeMock = $this->getMockBuilder(Gauge::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('inc')
            ->with([])
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

    public function testIncrementWithTags(): void
    {
        $expectedTagsValues = ['value1', 'value2'];

        $gaugeMock = $this->getMockBuilder(Gauge::class)
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

        $this->collector->setTags(['tag1' => 'value1', 'tag2' => 'value2']);

        $this->collector->increment(self::TEST_VARIABLE_NAME);
        $this->collector->flush();
    }

    public function testDecrement(): void
    {
        $gaugeMock = $this->getMockBuilder(Gauge::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('dec')
            ->with([])
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

    public function testDecrementWithTags(): void
    {
        $expectedTagsValues = ['value1', 'value2'];

        $gaugeMock = $this->getMockBuilder(Gauge::class)
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

        $this->collector->setTags(['tag1' => 'value1', 'tag2' => 'value2']);

        $this->collector->decrement(self::TEST_VARIABLE_NAME);
        $this->collector->flush();
    }

    public function testTiming(): void
    {
        $expectedVariableValue = 123;

        $gaugeMock = $this->getMockBuilder(Gauge::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($this->once())
            ->method('set')
            ->with($expectedVariableValue, [])
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

    public function testTimingWithTags(): void
    {
        $expectedVariableValue = 123;
        $expectedTagsValues = ['value1', 'value2'];

        $gaugeMock = $this->getMockBuilder(Gauge::class)
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

        $this->collector->setTags(['tag1' => 'value1', 'tag2' => 'value2']);

        $this->collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    public function testMeasureWhenSetNewVariableWithTags(): void
    {
        $expectedVariableValue = 123;
        $expectedTagsNames = ['tag1', 'tag2'];
        $expectedTagsValues = ['value1', 'value2'];

        $gaugeMock = $this->getMockBuilder(Gauge::class)
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

        $this->collector->setTags(['tag1' => 'value1', 'tag2' => 'value2']);

        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();
    }

    /**
     * Method flush must to reset value of field `data`.
     */
    public function testFlushWhenCallsTwiceWithDifferentData(): void
    {
        $firstExpectedVariableValue = 123;
        $secondExpectedVariableValue = 321;

        $gaugeMock = $this->getMockBuilder(Gauge::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaugeMock
            ->expects($matcher = $this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($value) use ($matcher) {
                match ($matcher->getInvocationCount()) {
                    1 => $this->assertEquals(123, $value),
                    2 => $this->assertEquals(321, $value),
                };
            })
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
