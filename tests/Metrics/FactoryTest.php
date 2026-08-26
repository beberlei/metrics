<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests;

use Beberlei\Metrics\Collector\Chain;
use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\DoctrineDBAL;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\InfluxDbV1;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Factory;
use Beberlei\Metrics\MetricsException;
use Doctrine\DBAL\Connection;
use InfluxDB\Database;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Psr\Log\NullLogger;

class FactoryTest extends TestCase
{
    #[DataProvider('getCreateValidMetricTests')]
    public function testCreateValidMetric(string $expectedClass, string $type, array $options): void
    {
        foreach ($options as $key => $option) {
            if ($option instanceof \Closure) {
                $options[$key] = $option($this);
            }
        }

        $this->assertInstanceOf($expectedClass, Factory::create($type, $options));
    }

    /**
     * @return iterable<array{class-string, string, array<string, mixed>}>
     */
    public static function getCreateValidMetricTests(): iterable
    {
        $stub = static fn (string $class): callable => static fn (TestCase $testCase): object => $testCase->createStub($class);

        yield [Chain::class, 'chain', ['collectors' => static fn (TestCase $testCase): array => [$testCase->createStub(CollectorInterface::class), $testCase->createStub(CollectorInterface::class)]]];
        yield [StatsD::class, 'statsd', []];
        yield [StatsD::class, 'statsd', ['host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix']];
        yield [StatsD::class, 'statsd', ['host' => 'localhost', 'port' => 1234]];
        yield [StatsD::class, 'statsd', ['host' => 'localhost']];
        yield [DogStatsD::class, 'dogstatsd', []];
        yield [DogStatsD::class, 'dogstatsd', ['host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix']];
        yield [DogStatsD::class, 'dogstatsd', ['host' => 'localhost', 'port' => 1234]];
        yield [DogStatsD::class, 'dogstatsd', ['host' => 'localhost']];
        yield [Graphite::class, 'graphite', []];
        yield [Graphite::class, 'graphite', ['host' => 'localhost', 'port' => 1234]];
        yield [DoctrineDBAL::class, 'doctrine_dbal', ['connection' => $stub(Connection::class)]];
        yield [Logger::class, 'logger', ['logger' => new NullLogger()]];
        yield [NullCollector::class, 'null', []];
        yield [InfluxDbV1::class, 'influxdb_v1', ['database' => $stub(Database::class)]];
        yield [Prometheus::class, 'prometheus', ['collector_registry' => $stub(CollectorRegistry::class)]];
        yield [Prometheus::class, 'prometheus', ['collector_registry' => $stub(CollectorRegistry::class), 'namespace' => 'some_namespace']];
    }

    #[DataProvider('getCreateThrowExceptionIfOptionsAreInvalidTests')]
    public function testCreateThrowExceptionIfOptionsAreInvalid(string $expectedMessage, string $type, array $options = []): void
    {
        try {
            Factory::create($type, $options);

            $this->fail('An expected exception (MetricsException) has not been raised.');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(MetricsException::class, $exception);
            $this->assertSame($expectedMessage, $exception->getMessage());
        }
    }

    public static function getCreateThrowExceptionIfOptionsAreInvalidTests(): iterable
    {
        yield ['The "collectors" option is required for the Chain collector.', 'chain'];
        yield ['You must specify a host if you specify a port.', 'statsd', ['port' => '1234']];
        yield ['You must specify a host and a port if you specify a prefix.', 'statsd', ['prefix' => 'prefix']];
        yield ['You must specify a host and a port if you specify a prefix.', 'statsd', ['port' => '1234', 'prefix' => 'prefix']];
        yield ['You must specify a host and a port if you specify a prefix.', 'statsd', ['hostname' => 'foobar.com', 'prefix' => 'prefix']];
        yield ['You must specify a host if you specify a port.', 'dogstatsd', ['port' => '1234']];
        yield ['You must specify a host and a port if you specify a prefix.', 'dogstatsd', ['prefix' => 'prefix']];
        yield ['You must specify a host and a port if you specify a prefix.', 'dogstatsd', ['port' => '1234', 'prefix' => 'prefix']];
        yield ['You must specify a host and a port if you specify a prefix.', 'dogstatsd', ['hostname' => 'foobar.com', 'prefix' => 'prefix']];
        yield ['You must specify a host if you specify a port.', 'graphite', ['port' => '1234']];
        yield ['The "connection" option is required for the Doctrine DBAL collector.', 'doctrine_dbal'];
        yield ['The "logger" option is required for the Logger collector.', 'logger'];
        yield ['The "database" option is required for the InfluxDbV1 collector.', 'influxdb_v1'];
        yield ['The "collector_registry" option is required for the Prometheus collector.', 'prometheus'];
    }
}
