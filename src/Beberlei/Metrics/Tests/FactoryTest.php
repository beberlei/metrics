<?php

namespace Beberlei\Metrics\Tests;

use Beberlei\Metrics\Factory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class FactoryTest extends TestCase
{
    public function getCreateValidMetricTests()
    {
        return array(
            array('Beberlei\Metrics\Collector\StatsD', 'statsd'),
            array('Beberlei\Metrics\Collector\StatsD', 'statsd', array('host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix')),
            array('Beberlei\Metrics\Collector\StatsD', 'statsd', array('host' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\StatsD', 'statsd', array('host' => 'localhost')),
            array('Beberlei\Metrics\Collector\DogStatsD', 'dogstatsd'),
            array('Beberlei\Metrics\Collector\DogStatsD', 'dogstatsd', array('host' => 'localhost', 'port' => 1234, 'prefix' => 'prefix')),
            array('Beberlei\Metrics\Collector\DogStatsD', 'dogstatsd', array('host' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\DogStatsD', 'dogstatsd', array('host' => 'localhost')),
            array('Beberlei\Metrics\Collector\Graphite', 'graphite'),
            array('Beberlei\Metrics\Collector\Graphite', 'graphite', array('host' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\Zabbix', 'zabbix', array('hostname' => 'foobar.com', 'server' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\Zabbix', 'zabbix_file', array('hostname' => 'foobar.com')),
            array('Beberlei\Metrics\Collector\Zabbix', 'zabbix_file', array('hostname' => 'foobar.com', 'file' => '/tmp/foobar')),
            array('Beberlei\Metrics\Collector\Librato', 'librato', array('hostname' => 'foobar.com', 'username' => 'username', 'password' => 'password')),
            array('Beberlei\Metrics\Collector\DoctrineDBAL', 'doctrine_dbal', array('connection' => $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock())),
            array('Beberlei\Metrics\Collector\Logger', 'logger', array('logger' => new NullLogger())),
            array('Beberlei\Metrics\Collector\NullCollector', 'null'),
            array('Beberlei\Metrics\Collector\InfluxDB', 'influxdb', array('client' => $this->getMockBuilder('\\InfluxDB\\Client')->disableOriginalConstructor()->getMock())),
            array('Beberlei\Metrics\Collector\Prometheus', 'prometheus', array('collector_registry' => $this->getMockBuilder('\\Prometheus\\CollectorRegistry')->disableOriginalConstructor()->getMock())),
            array('Beberlei\Metrics\Collector\Prometheus', 'prometheus', array('collector_registry' => $this->getMockBuilder('\\Prometheus\\CollectorRegistry')->disableOriginalConstructor()->getMock(), 'namespace' => 'some_namespace')),
        );
    }

    /**
     * @dataProvider getCreateValidMetricTests
     */
    public function testCreateValidMetric($expectedClass, $type, $options = array())
    {
        $this->assertInstanceOf($expectedClass, Factory::create($type, $options));
    }

    public function getCreateThrowExceptionIfOptionsAreInvalidTests()
    {
        return array(
            array('You should specified a host if you specified a port.', 'statsd', array('port' => '1234')),
            array('You should specified a host and a port if you specified a prefix.', 'statsd', array('prefix' => 'prefix')),
            array('You should specified a host and a port if you specified a prefix.', 'statsd', array('port' => '1234', 'prefix' => 'prefix')),
            array('You should specified a host and a port if you specified a prefix.', 'statsd', array('hostname' => 'foobar.com', 'prefix' => 'prefix')),
            array('You should specified a host if you specified a port.', 'dogstatsd', array('port' => '1234')),
            array('You should specified a host and a port if you specified a prefix.', 'dogstatsd', array('prefix' => 'prefix')),
            array('You should specified a host and a port if you specified a prefix.', 'dogstatsd', array('port' => '1234', 'prefix' => 'prefix')),
            array('You should specified a host and a port if you specified a prefix.', 'dogstatsd', array('hostname' => 'foobar.com', 'prefix' => 'prefix')),
            array('You should specified a host if you specified a port.', 'graphite', array('port' => '1234')),
            array('Hostname is required for zabbix collector.', 'zabbix'),
            array('Hostname is required for zabbix collector.', 'zabbix', array('hostname', 'foobar.com')),
            array('You should specified a server if you specified a port.', 'zabbix', array('hostname' => 'foobar.com', 'port' => '1234')),
            array('Hostname is required for zabbix collector.', 'zabbix_file'),
            array('Hostname is required for librato collector.', 'librato'),
            array('No username given for librato collector.', 'librato', array('hostname' => 'foobar.com')),
            array('No password given for librato collector.', 'librato', array('hostname' => 'foobar.com', 'username' => 'username')),
            array('connection is required for Doctrine DBAL collector.', 'doctrine_dbal'),
            array('Missing \'logger\' key with logger service.', 'logger'),
            array('Missing \'client\' key for InfluxDB collector.', 'influxdb'),
            array('Missing \'collector_registry\' key for Prometheus collector.', 'prometheus'),
        );
    }

    /**
     * @dataProvider getCreateThrowExceptionIfOptionsAreInvalidTests
     */
    public function testCreateThrowExceptionIfOptionsAreInvalid($expectedMessage, $type, $options = array())
    {
        try {
            Factory::create($type, $options);

            $this->fail('An expected exception (MetricsException) has not been raised.');
        } catch (\Exception $e) {
            $this->assertInstanceOf('Beberlei\Metrics\MetricsException', $e);
            $this->assertSame($expectedMessage, $e->getMessage());
        }
    }
}
