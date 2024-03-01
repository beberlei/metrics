<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Tests;

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
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Psr\Log\NullLogger;

class FactoryTest extends TestCase
{
    public function getCreateValidMetricTests(): iterable
    {
        yield [StatsD::class, 'statsd'];
        yield [StatsD::class, 'statsd', ['host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix']];
        yield [StatsD::class, 'statsd', ['host' => 'localhost', 'port' => 1234]];
        yield [StatsD::class, 'statsd', ['host' => 'localhost']];
        yield [DogStatsD::class, 'dogstatsd'];
        yield [DogStatsD::class, 'dogstatsd', ['host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix']];
        yield [DogStatsD::class, 'dogstatsd', ['host' => 'localhost', 'port' => 1234]];
        yield [DogStatsD::class, 'dogstatsd', ['host' => 'localhost']];
        yield [Graphite::class, 'graphite'];
        yield [Graphite::class, 'graphite', ['host' => 'localhost', 'port' => 1234]];
        yield [DoctrineDBAL::class, 'doctrine_dbal', ['connection' => $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock()]];
        yield [Logger::class, 'logger', ['logger' => new NullLogger()]];
        yield [NullCollector::class, 'null'];
        yield [InfluxDbV1::class, 'influxdb_v1', ['database' => $this->getMockBuilder(Database::class)->disableOriginalConstructor()->getMock()]];
        yield [Prometheus::class, 'prometheus', ['collector_registry' => $this->getMockBuilder(CollectorRegistry::class)->disableOriginalConstructor()->getMock()]];
        yield [Prometheus::class, 'prometheus', ['collector_registry' => $this->getMockBuilder(CollectorRegistry::class)->disableOriginalConstructor()->getMock(), 'namespace' => 'some_namespace']];
    }

    /**
     * @dataProvider getCreateValidMetricTests
     */
    public function testCreateValidMetric(string $expectedClass, string $type, array $options = []): void
    {
        $this->assertInstanceOf($expectedClass, Factory::create($type, $options));
    }

    public function getCreateThrowExceptionIfOptionsAreInvalidTests(): iterable
    {
        yield ['You should specified a host if you specified a port.', 'statsd', ['port' => '1234']];
        yield ['You should specified a host and a port if you specified a prefix.', 'statsd', ['prefix' => 'prefix']];
        yield ['You should specified a host and a port if you specified a prefix.', 'statsd', ['port' => '1234', 'prefix' => 'prefix']];
        yield ['You should specified a host and a port if you specified a prefix.', 'statsd', ['hostname' => 'foobar.com', 'prefix' => 'prefix']];
        yield ['You should specified a host if you specified a port.', 'dogstatsd', ['port' => '1234']];
        yield ['You should specified a host and a port if you specified a prefix.', 'dogstatsd', ['prefix' => 'prefix']];
        yield ['You should specified a host and a port if you specified a prefix.', 'dogstatsd', ['port' => '1234', 'prefix' => 'prefix']];
        yield ['You should specified a host and a port if you specified a prefix.', 'dogstatsd', ['hostname' => 'foobar.com', 'prefix' => 'prefix']];
        yield ['You should specified a host if you specified a port.', 'graphite', ['port' => '1234']];
        yield ['connection is required for Doctrine DBAL collector.', 'doctrine_dbal'];
        yield ['Missing "logger" key with logger service.', 'logger'];
        yield ['Missing "database" key for InfluxDB collector.', 'influxdb_v1'];
        yield ['Missing "collector_registry" key for Prometheus collector.', 'prometheus'];
    }

    /**
     * @dataProvider getCreateThrowExceptionIfOptionsAreInvalidTests
     */
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
}
