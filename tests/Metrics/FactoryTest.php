<?php

namespace Beberlei\Metrics\Tests;

use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\Librato;
use Beberlei\Metrics\Collector\DoctrineDBAL;
use Doctrine\DBAL\Connection;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\InlineTaggableGaugeableNullCollector;
use Beberlei\Metrics\Collector\InfluxDB;
use InfluxDB\Client;
use Beberlei\Metrics\Collector\Prometheus;
use Prometheus\CollectorRegistry;
use Beberlei\Metrics\MetricsException;
use Beberlei\Metrics\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FactoryTest extends TestCase
{
    public function getCreateValidMetricTests(): array
    {
        return [[StatsD::class, 'statsd'], [StatsD::class, 'statsd', ['host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix']], [StatsD::class, 'statsd', ['host' => 'localhost', 'port' => 1234]], [StatsD::class, 'statsd', ['host' => 'localhost']], [DogStatsD::class, 'dogstatsd'], [DogStatsD::class, 'dogstatsd', ['host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix']], [DogStatsD::class, 'dogstatsd', ['host' => 'localhost', 'port' => 1234]], [DogStatsD::class, 'dogstatsd', ['host' => 'localhost']], [Graphite::class, 'graphite'], [Graphite::class, 'graphite', ['host' => 'localhost', 'port' => 1234]], [Librato::class, 'librato', ['hostname' => 'foobar.com', 'username' => 'username', 'password' => 'password']], [DoctrineDBAL::class, 'doctrine_dbal', ['connection' => $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock()]], [Logger::class, 'logger', ['logger' => new NullLogger()]], [NullCollector::class, 'null'], [InlineTaggableGaugeableNullCollector::class, 'null_inlinetaggable'], [InfluxDB::class, 'influxdb', ['client' => $this->getMockBuilder(Client::class)->disableOriginalConstructor()->getMock()]], [Prometheus::class, 'prometheus', ['collector_registry' => $this->getMockBuilder(CollectorRegistry::class)->disableOriginalConstructor()->getMock()]], [Prometheus::class, 'prometheus', ['collector_registry' => $this->getMockBuilder(CollectorRegistry::class)->disableOriginalConstructor()->getMock(), 'namespace' => 'some_namespace']]];
    }

    /**
     * @dataProvider getCreateValidMetricTests
     */
    public function testCreateValidMetric(string $expectedClass, string $type, array $options = []): void
    {
        $this->assertInstanceOf($expectedClass, Factory::create($type, $options));
    }

    public function getCreateThrowExceptionIfOptionsAreInvalidTests(): array
    {
        return [['You should specified a host if you specified a port.', 'statsd', ['port' => '1234']], ['You should specified a host and a port if you specified a prefix.', 'statsd', ['prefix' => 'prefix']], ['You should specified a host and a port if you specified a prefix.', 'statsd', ['port' => '1234', 'prefix' => 'prefix']], ['You should specified a host and a port if you specified a prefix.', 'statsd', ['hostname' => 'foobar.com', 'prefix' => 'prefix']], ['You should specified a host if you specified a port.', 'dogstatsd', ['port' => '1234']], ['You should specified a host and a port if you specified a prefix.', 'dogstatsd', ['prefix' => 'prefix']], ['You should specified a host and a port if you specified a prefix.', 'dogstatsd', ['port' => '1234', 'prefix' => 'prefix']], ['You should specified a host and a port if you specified a prefix.', 'dogstatsd', ['hostname' => 'foobar.com', 'prefix' => 'prefix']], ['You should specified a host if you specified a port.', 'graphite', ['port' => '1234']], ['Hostname is required for librato collector.', 'librato'], ['No username given for librato collector.', 'librato', ['hostname' => 'foobar.com']], ['No password given for librato collector.', 'librato', ['hostname' => 'foobar.com', 'username' => 'username']], ['connection is required for Doctrine DBAL collector.', 'doctrine_dbal'], ["Missing 'logger' key with logger service.", 'logger'], ["Missing 'client' key for InfluxDB collector.", 'influxdb'], ["Missing 'collector_registry' key for Prometheus collector.", 'prometheus']];
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
