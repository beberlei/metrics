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

namespace Beberlei\Bundle\MetricsBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Beberlei\Bundle\MetricsBundle\DependencyInjection\BeberleiMetricsExtension;

class BeberleiMetricsExtensionTest extends TestCase
{
    public function testWithGraphite()
    {
        $container = $this->createContainer(array(
            'default' => 'simple',
            'collectors' => array(
                'simple' => array(
                    'type' => 'graphite',
                ),
                'full' => array(
                    'type' => 'graphite',
                    'host' => 'graphite.localhost',
                    'port' => 1234,
                    'protocol' => 'udp',
                ),
            ),
        ), array(
            'beberlei_metrics.collector.simple',
            'beberlei_metrics.collector.full'
        ));

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Graphite', $collector);
        $this->assertSame('tcp', $this->getProperty($collector, 'protocol'));
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(2003, $this->getProperty($collector, 'port'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Graphite', $collector);
        $this->assertSame('udp', $this->getProperty($collector, 'protocol'));
        $this->assertSame('graphite.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * The source has to be specified to use a Librato
     */
    public function testWithLibratoAndInvalidConfiguration()
    {
        $container = $this->createContainer(array(
            'collectors' => array(
                'simple' => array(
                    'type' => 'librato',
                ),
            ),
        ), array('beberlei_metrics.collector.librato'));

        $this->assertInstanceOf('Beberlei\Metrics\Collector\Librato', $container->get('beberlei_metrics.collector.librato'));
    }

    public function testWithLibrato()
    {
        $container = $this->createContainer(array(
            'collectors' => array(
                'full' => array(
                    'type' => 'librato',
                    'source' => 'foo.beberlei.de',
                    'username' => 'foo',
                    'password' => 'bar',
                ),
            ),
        ), array('beberlei_metrics.collector.full'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Librato', $collector);
        $this->assertSame('foo.beberlei.de', $this->getProperty($collector, 'source'));
        $this->assertSame('foo', $this->getProperty($collector, 'username'));
        $this->assertSame('bar', $this->getProperty($collector, 'password'));
    }

    public function testWithLogger()
    {
        $container = $this->createContainer(array(
            'collectors' => array(
                'logger' => array(
                    'type' => 'logger',
                ),
            ),
        ), array('beberlei_metrics.collector.logger'));

        $this->assertInstanceOf('Beberlei\Metrics\Collector\Logger', $container->get('beberlei_metrics.collector.logger'));
    }

    public function testWithNullCollector()
    {
        $container = $this->createContainer(array(
            'collectors' => array(
                'null' => array(
                    'type' => 'null',
                ),
            ),
        ), array('beberlei_metrics.collector.null'));

        $this->assertInstanceOf('Beberlei\Metrics\Collector\NullCollector', $container->get('beberlei_metrics.collector.null'));
    }

    public function testWithStatsD()
    {
        $container = $this->createContainer(array(
            'default' => 'simple',
            'collectors' => array(
                'simple' => array(
                    'type' => 'statsd',
                ),
                'full' => array(
                    'type' => 'statsd',
                    'host' => 'statsd.localhost',
                    'port' => 1234,
                    'prefix' => 'application.com.symfony.',
                ),
            ),
        ), array(
            'beberlei_metrics.collector.simple',
            'beberlei_metrics.collector.full'
        ));

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\StatsD', $collector);
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(8125, $this->getProperty($collector, 'port'));
        $this->assertSame('', $this->getProperty($collector, 'prefix'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\StatsD', $collector);
        $this->assertSame('statsd.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
        $this->assertSame('application.com.symfony.', $this->getProperty($collector, 'prefix'));
    }

    public function testWithDogStatsD()
    {
        $container = $this->createContainer(array(
            'default' => 'simple',
            'collectors' => array(
                'simple' => array(
                    'type' => 'dogstatsd',
                ),
                'full' => array(
                    'type' => 'dogstatsd',
                    'host' => 'dogstatsd.localhost',
                    'port' => 1234,
                    'prefix' => 'application.com.symfony.',
                ),
            ),
        ), array(
            'beberlei_metrics.collector.simple',
            'beberlei_metrics.collector.full'
        ));

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\DogStatsD', $collector);
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(8125, $this->getProperty($collector, 'port'));
        $this->assertSame('', $this->getProperty($collector, 'prefix'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\DogStatsD', $collector);
        $this->assertSame('dogstatsd.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
        $this->assertSame('application.com.symfony.', $this->getProperty($collector, 'prefix'));
    }

    public function testWithTelegraf()
    {
        $expectedTags = array(
            'string_tag' => 'first_value',
            'int_tag' => 123,
        );

        $container = $this->createContainer(array(
            'default' => 'simple',
            'collectors' => array(
                'simple' => array(
                    'type' => 'telegraf',
                ),
                'full' => array(
                    'type' => 'telegraf',
                    'host' => 'telegraf.localhost',
                    'port' => 1234,
                    'prefix' => 'application.com.symfony.',
                    'tags' => $expectedTags,
                ),
            ),
        ), array(
            'beberlei_metrics.collector.simple',
            'beberlei_metrics.collector.full'
        ));

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Telegraf', $collector);
        $this->assertSame('localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(8125, $this->getProperty($collector, 'port'));
        $this->assertSame('', $this->getProperty($collector, 'prefix'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Telegraf', $collector);
        $this->assertSame('telegraf.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1234, $this->getProperty($collector, 'port'));
        $this->assertSame('application.com.symfony.', $this->getProperty($collector, 'prefix'));

        $this->assertEquals(',string_tag=first_value,int_tag=123', $this->getProperty($collector, 'tags'));
    }

    public function testWithZabbix()
    {
        $container = $this->createContainer(array(
            'default' => 'simple',
            'collectors' => array(
                'simple' => array(
                    'type' => 'zabbix',
                ),
                'full' => array(
                    'type' => 'zabbix',
                    'prefix' => 'foo.beberlei.de',
                    'host' => 'zabbix.localhost',
                    'port' => 1234,
                ),
                'file' => array(
                    'type' => 'zabbix',
                    'prefix' => 'foo.beberlei.de',
                    'file' => '/etc/zabbix/zabbix_agentd.conf',
                ),
            ),
        ), array(
            'beberlei_metrics.collector.simple',
            'beberlei_metrics.collector.full',
            'beberlei_metrics.collector.file'
        ));

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Zabbix', $collector);
        $this->assertSame(gethostname(), $this->getProperty($collector, 'prefix'));
        $sender = $this->getProperty($collector, 'sender');
        $this->assertInstanceOf('Net\Zabbix\Sender', $sender);
        $this->assertSame('localhost', $this->getProperty($sender, '_servername'));
        $this->assertSame(10051, $this->getProperty($sender, '_serverport'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Zabbix', $collector);
        $this->assertSame('foo.beberlei.de', $this->getProperty($collector, 'prefix'));
        $sender = $this->getProperty($collector, 'sender');
        $this->assertInstanceOf('Net\Zabbix\Sender', $sender);
        $this->assertSame('zabbix.localhost', $this->getProperty($sender, '_servername'));
        $this->assertSame(1234, $this->getProperty($sender, '_serverport'));

        $collector = $container->get('beberlei_metrics.collector.file');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Zabbix', $collector);
        $this->assertSame('foo.beberlei.de', $this->getProperty($collector, 'prefix'));
        $sender = $this->getProperty($collector, 'sender');
        $this->assertInstanceOf('Net\Zabbix\Sender', $sender);
    }

    public function testWithInfluxDB()
    {
        $influxDBClientMock = $this->getMockBuilder('InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(array(
            'collectors' => array(
                'influxdb' => array(
                    'type' => 'influxdb',
                    'influxdb_client' => 'influxdb_client_mock',
                ),
            ),
        ), array('beberlei_metrics.collector.influxdb'), array(
            'influxdb_client_mock' => $influxDBClientMock,
        ));

        $collector = $container->get('beberlei_metrics.collector.influxdb');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\InfluxDB', $collector);
        $this->assertSame($influxDBClientMock, $this->getProperty($collector, 'client'));
    }

    public function testWithInfluxDBAndWithTags()
    {
        $expectedTags = array(
            'string_tag' => 'first_value',
            'int_tag' => 123,
        );

        $influxDBClientMock = $this->getMockBuilder('InfluxDB\Client')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(array(
            'collectors' => array(
                'influxdb' => array(
                    'type' => 'influxdb',
                    'influxdb_client' => 'influxdb_client_mock',
                    'tags' => $expectedTags,
                ),
            ),
        ), array('beberlei_metrics.collector.influxdb'), array(
            'influxdb_client_mock' => $influxDBClientMock,
        ));

        $collector = $container->get('beberlei_metrics.collector.influxdb');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\InfluxDB', $collector);
        $this->assertEquals($expectedTags, $this->getProperty($collector, 'tags'));
    }

    public function testWithPrometheus()
    {
        $prometheusCollectorRegistryMock = $this->getMockBuilder('\\Prometheus\\CollectorRegistry')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(array(
            'collectors' => array(
                'prometheus' => array(
                    'type' => 'prometheus',
                    'prometheus_collector_registry' => 'prometheus_collector_registry_mock',
                ),
            ),
        ), array('beberlei_metrics.collector.prometheus'), array(
            'prometheus_collector_registry_mock' => $prometheusCollectorRegistryMock,
        ));

        $collector = $container->get('beberlei_metrics.collector.prometheus');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Prometheus', $collector);
        $this->assertSame($prometheusCollectorRegistryMock, $this->getProperty($collector, 'collectorRegistry'));
        $this->assertSame('', $this->getProperty($collector, 'namespace'));
    }

    public function testWithInMemory()
    {
        $container = $this->createContainer(array(
            'collectors' => array(
                'memory' => array(
                    'type' => 'memory',
                ),
            ),
        ), array('beberlei_metrics.collector.memory'));
        $collector = $container->get('beberlei_metrics.collector.memory');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\InMemory', $collector);
    }

    public function testWithPrometheusAndWithNamespace()
    {
        $expectedNamespace = 'some_namespace';

        $prometheusCollectorRegistryMock = $this->getMockBuilder('\\Prometheus\\CollectorRegistry')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(array(
            'collectors' => array(
                'prometheus' => array(
                    'type' => 'prometheus',
                    'prometheus_collector_registry' => 'prometheus_collector_registry_mock',
                    'namespace' => $expectedNamespace,
                ),
            ),
        ), array('beberlei_metrics.collector.prometheus'), array(
            'prometheus_collector_registry_mock' => $prometheusCollectorRegistryMock,
        ));

        $collector = $container->get('beberlei_metrics.collector.prometheus');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Prometheus', $collector);
        $this->assertSame($prometheusCollectorRegistryMock, $this->getProperty($collector, 'collectorRegistry'));
        $this->assertSame($expectedNamespace, $this->getProperty($collector, 'namespace'));
    }

    public function testWithPrometheusAndWithTags()
    {
        $expectedTags = array(
            'string_tag' => 'first_value',
            'int_tag' => 123,
        );

        $prometheusCollectorRegistryMock = $this->getMockBuilder('\\Prometheus\\CollectorRegistry')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $container = $this->createContainer(array(
            'collectors' => array(
                'prometheus' => array(
                    'type' => 'prometheus',
                    'prometheus_collector_registry' => 'prometheus_collector_registry_mock',
                    'tags' => $expectedTags,
                ),
            ),
        ), array('beberlei_metrics.collector.prometheus'), array(
            'prometheus_collector_registry_mock' => $prometheusCollectorRegistryMock,
        ));

        $collector = $container->get('beberlei_metrics.collector.prometheus');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Prometheus', $collector);
        $this->assertEquals($expectedTags, $this->getProperty($collector, 'tags'));
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage The prometheus_collector_registry has to be specified to use a Prometheus
     */
    public function testValidationWhenTypeIsPrometheusAndPrometheusCollectorRegistryIsNotSpecified()
    {
        $this->createContainer(array(
            'collectors' => array(
                'prometheus' => array(
                    'type' => 'prometheus',
                ),
            ),
        ));
    }

    private function createContainer($configs, $publicServices = array(), $additionalServices = array())
    {
        $container = new ContainerBuilder();

        $extension = new BeberleiMetricsExtension();
        $extension->load(array($configs), $container);
        // Needed for logger collector
        $container->setDefinition('logger', new Definition('Psr\Log\NullLogger'));

        foreach ($additionalServices as $serviceId => $additionalService) {
            $container->set($serviceId, $additionalService);
        }

        foreach($publicServices as $serviceId) {
            $container->getDefinition($serviceId)->setPublic(true);
        }

        $container->compile();

        return $container;
    }

    private function getProperty($object, $property)
    {
        $reflectionProperty = new \ReflectionProperty(get_class($object), $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty->getValue($object);
    }
}
