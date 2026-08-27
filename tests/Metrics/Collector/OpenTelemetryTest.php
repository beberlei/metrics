<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\OpenTelemetry;
use OpenTelemetry\API\Metrics\Noop\NoopMeterProvider;
use OpenTelemetry\SDK\Metrics\Data\Gauge;
use OpenTelemetry\SDK\Metrics\Data\Histogram;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Sum;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as SdkMeterProviderInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use PHPUnit\Framework\TestCase;

enum TestBackedEnum: string
{
    case Active = 'active';
}

enum TestUnitEnum
{
    case Active;
}

class OpenTelemetryTest extends TestCase
{
    private const TEST_VARIABLE_NAME = 'some_variable_name';

    private InMemoryExporter $exporter;
    private MeterProvider $meterProvider;

    protected function setUp(): void
    {
        $this->exporter = new InMemoryExporter();
        $this->meterProvider = MeterProvider::builder()
            ->addReader(new ExportingReader($this->exporter))
            ->build()
        ;
    }

    public function testMeasure(): void
    {
        $this->createCollector()->measure(self::TEST_VARIABLE_NAME, 123);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertEquals(123, $dataPoint->value);
        $this->assertSame([], $dataPoint->attributes->toArray());
    }

    public function testMeasureWithTags(): void
    {
        $tags = ['tag1' => 'value1', 'tag2' => 'value2'];

        $this->createCollector()->measure(self::TEST_VARIABLE_NAME, 123, $tags);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertEquals(123, $dataPoint->value);
        $this->assertSame($tags, $dataPoint->attributes->toArray());
    }

    public function testIncrement(): void
    {
        $this->createCollector()->increment(self::TEST_VARIABLE_NAME);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertEquals(1, $dataPoint->value);
    }

    public function testDecrement(): void
    {
        $this->createCollector()->decrement(self::TEST_VARIABLE_NAME);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertEquals(-1, $dataPoint->value);
    }

    public function testIncrementAndDecrementShareTheSameInstrument(): void
    {
        $collector = $this->createCollector();
        $collector->increment(self::TEST_VARIABLE_NAME);
        $collector->increment(self::TEST_VARIABLE_NAME);
        $collector->decrement(self::TEST_VARIABLE_NAME);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertEquals(1, $dataPoint->value);
    }

    public function testMeasureWithNegativeValueIsNotClampedByAMonotonicCounter(): void
    {
        $collector = $this->createCollector();
        $collector->measure(self::TEST_VARIABLE_NAME, 10);
        $collector->measure(self::TEST_VARIABLE_NAME, -3);
        $this->flush();

        $metric = $this->firstMetric();
        $this->assertInstanceOf(Sum::class, $metric->data);
        $this->assertFalse($metric->data->monotonic);

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertEquals(7, $dataPoint->value);
    }

    public function testTiming(): void
    {
        $this->createCollector()->timing(self::TEST_VARIABLE_NAME, 123);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Histogram::class);
        $this->assertSame(1, $dataPoint->count);
        $this->assertEquals(123, $dataPoint->sum);
    }

    public function testGauge(): void
    {
        $this->createCollector()->gauge(self::TEST_VARIABLE_NAME, 42);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Gauge::class);
        $this->assertEquals(42, $dataPoint->value);
    }

    public function testGaugeWithStringValue(): void
    {
        $this->createCollector()->gauge(self::TEST_VARIABLE_NAME, '+42');
        $this->flush();

        $dataPoint = $this->firstDataPoint(Gauge::class);
        $this->assertEquals(42, $dataPoint->value);
    }

    public function testGaugeAcceptsRelativeDeltaValues(): void
    {
        $collector = $this->createCollector();
        $collector->gauge(self::TEST_VARIABLE_NAME, 10);
        $collector->gauge(self::TEST_VARIABLE_NAME, '+5');
        $collector->gauge(self::TEST_VARIABLE_NAME, '-3');
        $this->flush();

        $dataPoint = $this->firstDataPoint(Gauge::class);
        $this->assertEquals(12, $dataPoint->value);
    }

    public function testGaugeRejectsAnInvalidStringValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createCollector()->gauge(self::TEST_VARIABLE_NAME, 'nonsense');
    }

    public function testTagsAcceptBackedEnumValues(): void
    {
        $this->createCollector()->increment(self::TEST_VARIABLE_NAME, ['status' => TestBackedEnum::Active]);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertSame(['status' => 'active'], $dataPoint->attributes->toArray());
    }

    public function testTagsAcceptUnitEnumValues(): void
    {
        $this->createCollector()->increment(self::TEST_VARIABLE_NAME, ['status' => TestUnitEnum::Active]);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertSame(['status' => 'Active'], $dataPoint->attributes->toArray());
    }

    public function testTagWithANonStringableObjectDoesNotThrow(): void
    {
        $this->createCollector()->increment(self::TEST_VARIABLE_NAME, ['object' => new \stdClass()]);
        $this->flush();

        $this->addToAssertionCount(1);
    }

    public function testFlushSwallowsAFailureFromTheMeterProvider(): void
    {
        $meterProviderMock = $this->createStub(SdkMeterProviderInterface::class);
        $meterProviderMock->method('getMeter')->willReturn($this->meterProvider->getMeter('beberlei/metrics'));
        $meterProviderMock->method('forceFlush')->willThrowException(new \RuntimeException('boom'));

        new OpenTelemetry($meterProviderMock)->flush();

        $this->addToAssertionCount(1);
    }

    public function testDefaultTagsAreMergedWithPerCallTags(): void
    {
        $this->createCollector(['dc' => 'west'])->increment(self::TEST_VARIABLE_NAME, ['node' => 'hermes10']);
        $this->flush();

        $dataPoint = $this->firstDataPoint(Sum::class);
        $this->assertSame(['dc' => 'west', 'node' => 'hermes10'], $dataPoint->attributes->toArray());
    }

    public function testMeasurementsAreNotExportedBeforeFlush(): void
    {
        $this->createCollector()->increment(self::TEST_VARIABLE_NAME);

        $this->assertSame([], $this->exporter->collect());
    }

    public function testInstrumentationScopeNameIsConfigurable(): void
    {
        $this->createCollector(name: 'my_app')->increment(self::TEST_VARIABLE_NAME);
        $this->flush();

        $this->assertSame('my_app', $this->firstMetric()->instrumentationScope->getName());
    }

    public function testFlushWithoutAnSdkMeterProviderDoesNotFail(): void
    {
        new OpenTelemetry(new NoopMeterProvider())->flush();

        $this->addToAssertionCount(1);
    }

    private function createCollector(array $tags = [], string $name = 'beberlei/metrics'): OpenTelemetry
    {
        return new OpenTelemetry($this->meterProvider, $name, $tags);
    }

    private function flush(): void
    {
        $this->meterProvider->forceFlush();
    }

    private function firstMetric(): Metric
    {
        $metrics = $this->exporter->collect();
        $this->assertNotEmpty($metrics, 'Expected at least one exported metric.');

        return $metrics[0];
    }

    private function firstDataPoint(string $dataType): object
    {
        $metric = $this->firstMetric();
        $this->assertInstanceOf($dataType, $metric->data);

        $dataPoints = \is_array($metric->data->dataPoints) ? $metric->data->dataPoints : iterator_to_array($metric->data->dataPoints);
        $this->assertNotEmpty($dataPoints, 'Expected at least one data point.');

        return $dataPoints[0];
    }
}
