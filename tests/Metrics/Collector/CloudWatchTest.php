<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests\Collector;

use Aws\CloudWatch\CloudWatchClient;
use Aws\MockHandler;
use Aws\Result;
use Beberlei\Metrics\Collector\CloudWatch;
use PHPUnit\Framework\TestCase;

class CloudWatchTest extends TestCase
{
    private MockHandler $mock;
    private CloudWatchClient $client;

    protected function setUp(): void
    {
        $this->mock = new MockHandler();
        $this->client = new CloudWatchClient([
            'region' => 'us-east-1',
            'version' => 'latest',
            'credentials' => ['key' => 'test', 'secret' => 'test'],
            'handler' => $this->mock,
        ]);
    }

    public function testCollectIncrement(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector([]);
        $collector->increment('series-name');
        $collector->flush();

        $metricData = $this->lastMetricData();
        $this->assertSame([
            'MetricName' => 'series-name',
            'Value' => 1,
            'Unit' => 'Count',
            'Dimensions' => [],
        ], $metricData);
    }

    public function testCollectDecrement(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector([]);
        $collector->decrement('series-name');
        $collector->flush();

        $metricData = $this->lastMetricData();
        $this->assertSame([
            'MetricName' => 'series-name',
            'Value' => -1,
            'Unit' => 'Count',
            'Dimensions' => [],
        ], $metricData);
    }

    public function testCollectTiming(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector([]);
        $collector->timing('series-name', 47);
        $collector->flush();

        $metricData = $this->lastMetricData();
        $this->assertSame([
            'MetricName' => 'series-name',
            'Value' => 47,
            'Unit' => 'Milliseconds',
            'Dimensions' => [],
        ], $metricData);
    }

    public function testCollectMeasure(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector([]);
        $collector->measure('series-name', 47);
        $collector->flush();

        $metricData = $this->lastMetricData();
        $this->assertSame([
            'MetricName' => 'series-name',
            'Value' => 47,
            'Unit' => 'Count',
            'Dimensions' => [],
        ], $metricData);
    }

    public function testCollectMeasureWithTags(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector([]);
        $collector->measure('series-name', 47, ['dc' => 'west', 'node' => 'nemesis101']);
        $collector->flush();

        $this->assertSame([
            ['Name' => 'dc', 'Value' => 'west'],
            ['Name' => 'node', 'Value' => 'nemesis101'],
        ], $this->lastMetricData()['Dimensions']);
    }

    public function testCollectMeasureWithTagsMerged(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector(['dc' => 'west', 'node' => 'nemesis101']);
        $collector->measure('series-name', 47, ['foo' => 'bar']);
        $collector->flush();

        $this->assertSame([
            ['Name' => 'dc', 'Value' => 'west'],
            ['Name' => 'node', 'Value' => 'nemesis101'],
            ['Name' => 'foo', 'Value' => 'bar'],
        ], $this->lastMetricData()['Dimensions']);
    }

    public function testDefaultNamespace(): void
    {
        $this->mock->append(new Result([]));

        $collector = $this->createCollector([]);
        $collector->increment('series-name');
        $collector->flush();

        $this->assertSame('beberlei/metrics', $this->lastCommandArray()['Namespace']);
    }

    public function testCustomNamespace(): void
    {
        $this->mock->append(new Result([]));

        $collector = new CloudWatch($this->client, 'my_app');
        $collector->increment('series-name');
        $collector->flush();

        $this->assertSame('my_app', $this->lastCommandArray()['Namespace']);
    }

    public function testFlushWithNoDataDoesNotCallPutMetricData(): void
    {
        $collector = $this->createCollector([]);
        $collector->flush();

        $this->assertNull($this->mock->getLastCommand());
    }

    private function createCollector(array $tags): CloudWatch
    {
        return new CloudWatch($this->client, tags: $tags);
    }

    /**
     * @return array<string, mixed>
     */
    private function lastCommandArray(): array
    {
        $command = $this->mock->getLastCommand() ?? throw new \RuntimeException('No command was sent.');

        return $command->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    private function lastMetricData(): array
    {
        return $this->lastCommandArray()['MetricData'][0];
    }
}
