<?php
/**
 * Beberlei Metrics
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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Beberlei\Bundle\MetricsBundle\DependencyInjection\BeberleiMetricsExtension;

class BeberleiMetricsExtensionTest extends \PHPUnit_Framework_TestCase
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
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
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
        ));

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
        ));

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
        ));

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
        ));

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

    public function testWithCRedis()
    {
        $container = $this->createContainer(array(
            'default' => 'simple',
            'collectors' => array(
                'simple' => array(
                    'type' => 'credis',
                ),
                'full' => array(
                    'type' => 'credis',
                    'host' => 'credis.localhost',
                    'port' => 1337,
                ),
            ),
        ));

        $collector = $container->get('beberlei_metrics.collector.simple');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\CRedis', $collector);
        $this->assertSame('127.0.0.1', $this->getProperty($collector, 'host'));
        $this->assertSame(6379, $this->getProperty($collector, 'port'));

        $collector = $container->get('beberlei_metrics.collector.full');
        $this->assertInstanceOf('Beberlei\Metrics\Collector\CRedis', $collector);
        $this->assertSame('credis.localhost', $this->getProperty($collector, 'host'));
        $this->assertSame(1337, $this->getProperty($collector, 'port'));
    }


    private function createContainer($configs)
    {
        $container = new ContainerBuilder();

        $extension = new BeberleiMetricsExtension();
        $extension->load(array($configs), $container);
        // Needed for logger collector
        $container->setDefinition('logger', new Definition('Psr\Log\NullLogger'));

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
