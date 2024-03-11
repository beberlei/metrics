<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Beberlei\Metrics\Collector\Prometheus;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;

class PrometheusTest extends TestCase
{
    private const TEST_NAMESPACE = 'some_metric_namespace';
    private const TEST_VARIABLE_NAME = 'some_variable_name';

    private CollectorRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new CollectorRegistry(new InMemory(), false);
    }

    public function testMeasure(): void
    {
        $expectedVariableValue = 123;
        $labels = [];

        $collector = $this->createCollector($labels);
        $collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testMeasureWithTags(): void
    {
        $expectedVariableValue = 123;
        $labels = ['tag1' => 'value1', 'tag2' => 'value2'];

        $collector = $this->createCollector($labels);
        $collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testIncrement(): void
    {
        $labels = [];

        $collector = $this->createCollector($labels);
        $collector->increment(self::TEST_VARIABLE_NAME);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testIncrementWithTags(): void
    {
        $labels = ['value1', 'value2'];

        $collector = $this->createCollector($labels);
        $collector->increment(self::TEST_VARIABLE_NAME);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testDecrement(): void
    {
        $labels = [];

        $collector = $this->createCollector($labels);
        $collector->decrement(self::TEST_VARIABLE_NAME);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('-1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testDecrementWithTags(): void
    {
        $labels = ['value1', 'value2'];

        $collector = $this->createCollector($labels);
        $collector->decrement(self::TEST_VARIABLE_NAME);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('-1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testTiming(): void
    {
        $expectedVariableValue = 123;
        $labels = [];

        $collector = $this->createCollector($labels);
        $collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testTimingWithTags(): void
    {
        $expectedVariableValue = 123;
        $labels = ['tag1' => 'value1', 'tag2' => 'value2'];

        $collector = $this->createCollector($labels);
        $collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    /**
     * Method flush must to reset value of field `data`.
     */
    public function testFlushWhenCallsTwiceWithDifferentData(): void
    {
        $firstExpectedVariableValue = 123;

        $collector = $this->createCollector([]);
        $collector->timing(self::TEST_VARIABLE_NAME, $firstExpectedVariableValue);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $firstExpectedVariableValue, $data->getValue());

        $secondExpectedVariableValue = 321;

        $collector->timing(self::TEST_VARIABLE_NAME, $secondExpectedVariableValue);
        $collector->flush();

        $data = $this->registry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $secondExpectedVariableValue, $data->getValue());
    }

    private function createCollector(array $tags): Prometheus
    {
        return new Prometheus($this->registry, self::TEST_NAMESPACE, $tags);
    }
}
