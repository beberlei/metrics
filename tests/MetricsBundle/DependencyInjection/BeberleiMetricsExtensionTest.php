<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Bundle\MetricsBundle\Tests\DependencyInjection;

use Beberlei\Bundle\MetricsBundle\DependencyInjection\BeberleiMetricsExtension;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\InfluxDB;
use Beberlei\Metrics\Collector\InMemory;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\Telegraf;
use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Psr\Log\NullLogger;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BeberleiMetricsExtensionTest extends TestCase
{
    public function testWithGraphite(): void
    {
        $container = $this->createContainer(['default' => 'simple', 'collectors' => ['simple' => ['type' => 'graphite'], 'full' => ['type' => 'graphite', 'host' => 'graphite.localhost', 'port' => 1234, 'protocol' => 'udp']]], ['beberlei_metrics.collector.simple', 'beberlei_metrics.collector.full']);

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf(Graphite::class, $collector);
        $this->assertSame('tcp', $this->getProperty($collector, 'protocol'));
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(2003, $this->getProperty($collector, 'port'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf(Graphite::class, $collector);
        $this->assertSame('udp', $this->getProperty($collector, 'protocol'));
        $this->assertSame('graphite.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
    }

    public function testWithLogger(): void
    {
        $container = $this->createContainer(['collectors' => ['logger' => ['type' => 'logger']]], ['beberlei_metrics.collector.logger']);

        $this->assertInstanceOf(Logger::class, $container->get('beberlei_metrics.collector.logger'));
    }

    public function testWithNullCollector(): void
    {
        $container = $this->createContainer(['collectors' => ['null' => ['type' => 'null']]], ['beberlei_metrics.collector.null']);

        $this->assertInstanceOf(NullCollector::class, $container->get('beberlei_metrics.collector.null'));
    }

    public function testWithStatsD(): void
    {
        $container = $this->createContainer(['default' => 'simple', 'collectors' => ['simple' => ['type' => 'statsd'], 'full' => ['type' => 'statsd', 'host' => 'statsd.localhost', 'port' => 1234, 'prefix' => 'application.com.symfony.']]], ['beberlei_metrics.collector.simple', 'beberlei_metrics.collector.full']);

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf(StatsD::class, $collector);
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(8125, $this->getProperty($collector, 'port'));
        $this->assertSame('', $this->getProperty($collector, 'prefix'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf(StatsD::class, $collector);
        $this->assertSame('statsd.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
        $this->assertSame('application.com.symfony.', $this->getProperty($collector, 'prefix'));
    }

    public function testWithDogStatsD(): void
    {
        $container = $this->createContainer(['default' => 'simple', 'collectors' => ['simple' => ['type' => 'dogstatsd'], 'full' => ['type' => 'dogstatsd', 'host' => 'dogstatsd.localhost', 'port' => 1234, 'prefix' => 'application.com.symfony.']]], ['beberlei_metrics.collector.simple', 'beberlei_metrics.collector.full']);

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf(DogStatsD::class, $collector);
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(8125, $this->getProperty($collector, 'port'));
        $this->assertSame('', $this->getProperty($collector, 'prefix'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf(DogStatsD::class, $collector);
        $this->assertSame('dogstatsd.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
        $this->assertSame('application.com.symfony.', $this->getProperty($collector, 'prefix'));
    }

    public function testWithTelegraf(): void
    {
        $expectedTags = ['string_tag' => 'first_value', 'int_tag' => 123];

        $container = $this->createContainer(['default' => 'simple', 'collectors' => ['simple' => ['type' => 'telegraf'], 'full' => ['type' => 'telegraf', 'host' => 'telegraf.localhost', 'port' => 1234, 'prefix' => 'application.com.symfony.', 'tags' => $expectedTags]]], ['beberlei_metrics.collector.simple', 'beberlei_metrics.collector.full']);

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf(Telegraf::class, $collector);
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(8125, $this->getProperty($collector, 'port'));
        $this->assertSame('', $this->getProperty($collector, 'prefix'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf(Telegraf::class, $collector);
        $this->assertSame('telegraf.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
        $this->assertSame('application.com.symfony.', $this->getProperty($collector, 'prefix'));

        $this->assertEquals(',string_tag=first_value,int_tag=123', $this->getProperty($collector, 'tags'));
    }

    public function testWithInfluxDB(): void
    {
        $container = $this->createContainer(['collectors' => ['influxdb' => ['type' => 'influxdb', 'database' => 'foobar']]], ['beberlei_metrics.collector.influxdb']);

        $collector = $container->get('beberlei_metrics.collector.influxdb');
        $this->assertInstanceOf(InfluxDB::class, $collector);
    }

    public function testWithInfluxDBAndWithTags(): void
    {
        $expectedTags = ['string_tag' => 'first_value', 'int_tag' => 123];

        $container = $this->createContainer(['collectors' => ['influxdb' => ['type' => 'influxdb', 'database' => 'foobar', 'tags' => $expectedTags]]], ['beberlei_metrics.collector.influxdb']);

        $collector = $container->get('beberlei_metrics.collector.influxdb');
        $this->assertInstanceOf(InfluxDB::class, $collector);
        $this->assertEquals($expectedTags, $this->getProperty($collector, 'tags'));
    }

    public function testWithPrometheus(): void
    {
        $prometheusCollectorRegistryMock = $this->getMockBuilder(CollectorRegistry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(['collectors' => ['prometheus' => ['type' => 'prometheus', 'prometheus_collector_registry' => 'prometheus_collector_registry_mock']]], ['beberlei_metrics.collector.prometheus'], ['prometheus_collector_registry_mock' => $prometheusCollectorRegistryMock]);

        $collector = $container->get('beberlei_metrics.collector.prometheus');
        $this->assertInstanceOf(Prometheus::class, $collector);
        $this->assertSame($prometheusCollectorRegistryMock, $this->getProperty($collector, 'collectorRegistry'));
        $this->assertSame('', $this->getProperty($collector, 'namespace'));
    }

    public function testWithInMemory(): void
    {
        $container = $this->createContainer(['collectors' => ['memory' => ['type' => 'memory']]], ['beberlei_metrics.collector.memory']);
        $collector = $container->get('beberlei_metrics.collector.memory');
        $this->assertInstanceOf(InMemory::class, $collector);
    }

    public function testWithPrometheusAndWithNamespace(): void
    {
        $expectedNamespace = 'some_namespace';

        $prometheusCollectorRegistryMock = $this->getMockBuilder(CollectorRegistry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(['collectors' => ['prometheus' => ['type' => 'prometheus', 'prometheus_collector_registry' => 'prometheus_collector_registry_mock', 'namespace' => $expectedNamespace]]], ['beberlei_metrics.collector.prometheus'], ['prometheus_collector_registry_mock' => $prometheusCollectorRegistryMock]);

        $collector = $container->get('beberlei_metrics.collector.prometheus');
        $this->assertInstanceOf(Prometheus::class, $collector);
        $this->assertSame($prometheusCollectorRegistryMock, $this->getProperty($collector, 'collectorRegistry'));
        $this->assertSame($expectedNamespace, $this->getProperty($collector, 'namespace'));
    }

    public function testWithPrometheusAndWithTags(): void
    {
        $expectedTags = ['string_tag' => 'first_value', 'int_tag' => 123];

        $prometheusCollectorRegistryMock = $this->getMockBuilder(CollectorRegistry::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(['collectors' => ['prometheus' => ['type' => 'prometheus', 'prometheus_collector_registry' => 'prometheus_collector_registry_mock', 'tags' => $expectedTags]]], ['beberlei_metrics.collector.prometheus'], ['prometheus_collector_registry_mock' => $prometheusCollectorRegistryMock]);

        $collector = $container->get('beberlei_metrics.collector.prometheus');
        $this->assertInstanceOf(Prometheus::class, $collector);
        $this->assertEquals($expectedTags, $this->getProperty($collector, 'tags'));
    }

    public function testValidationWhenTypeIsPrometheusAndPrometheusCollectorRegistryIsNotSpecified(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage('The "prometheus_collector_registry" has to be specified to use a Prometheus');
        $this->createContainer(['collectors' => ['prometheus' => ['type' => 'prometheus']]]);
    }

    private function createContainer($configs, array $publicServices = [], array $additionalServices = []): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $extension = new BeberleiMetricsExtension();
        $extension->load([$configs], $container);
        // Needed for logger collector
        $container->setDefinition('logger', new Definition(NullLogger::class));

        foreach ($additionalServices as $serviceId => $additionalService) {
            $container->set($serviceId, $additionalService);
        }

        foreach ($publicServices as $serviceId) {
            $container->getDefinition($serviceId)->setPublic(true);
        }

        $container->compile();

        return $container;
    }

    private function getProperty(?object $object, string $property)
    {
        $reflectionProperty = new \ReflectionProperty($object::class, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
