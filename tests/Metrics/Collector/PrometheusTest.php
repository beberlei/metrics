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

    private CollectorRegistry $collectorRegistry;
    private Prometheus $collector;

    protected function setUp(): void
    {
        $this->collectorRegistry = new CollectorRegistry(new InMemory(), false);
        $this->collector = new Prometheus($this->collectorRegistry, self::TEST_NAMESPACE);
    }

    public function testMeasure(): void
    {
        $expectedVariableValue = 123;
        $labels = [];

        $this->collector->setTags($labels);
        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testMeasureWithTags(): void
    {
        $expectedVariableValue = 123;
        $labels = ['tag1' => 'value1', 'tag2' => 'value2'];

        $this->collector->setTags($labels);
        $this->collector->measure(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testIncrement(): void
    {
        $labels = [];

        $this->collector->setTags($labels);
        $this->collector->increment(self::TEST_VARIABLE_NAME);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testIncrementWithTags(): void
    {
        $labels = ['value1', 'value2'];

        $this->collector->setTags($labels);
        $this->collector->increment(self::TEST_VARIABLE_NAME);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testDecrement(): void
    {
        $labels = [];

        $this->collector->setTags($labels);
        $this->collector->decrement(self::TEST_VARIABLE_NAME);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('-1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testDecrementWithTags(): void
    {
        $labels = ['value1', 'value2'];

        $this->collector->setTags($labels);
        $this->collector->decrement(self::TEST_VARIABLE_NAME);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame('-1', $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testTiming(): void
    {
        $expectedVariableValue = 123;
        $labels = [];

        $this->collector->setTags($labels);
        $this->collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $expectedVariableValue, $data->getValue());
        $this->assertSame($labels, $data->getLabelValues());
    }

    public function testTimingWithTags(): void
    {
        $expectedVariableValue = 123;
        $labels = ['tag1' => 'value1', 'tag2' => 'value2'];

        $this->collector->setTags($labels);
        $this->collector->timing(self::TEST_VARIABLE_NAME, $expectedVariableValue);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
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

        $this->collector->timing(self::TEST_VARIABLE_NAME, $firstExpectedVariableValue);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $firstExpectedVariableValue, $data->getValue());

        $secondExpectedVariableValue = 321;

        $this->collector->timing(self::TEST_VARIABLE_NAME, $secondExpectedVariableValue);
        $this->collector->flush();

        $data = $this->collectorRegistry->getMetricFamilySamples()[0]->getSamples()[0];
        $this->assertSame(self::TEST_NAMESPACE . '_' . self::TEST_VARIABLE_NAME, $data->getName());
        $this->assertSame((string) $secondExpectedVariableValue, $data->getValue());
    }
}
