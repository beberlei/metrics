<?php

namespace Beberlei\Metrics\Tests;

use Beberlei\Metrics\Factory;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    public function getCreateValidMetricTests()
    {
        return array(
            array('Beberlei\Metrics\Collector\StatsD', 'statsd'),
            array('Beberlei\Metrics\Collector\StatsD', 'statsd', array('host' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\StatsD', 'statsd', array('host' => 'localhost')),
            array('Beberlei\Metrics\Collector\Graphite', 'graphite'),
            array('Beberlei\Metrics\Collector\Graphite', 'graphite', array('host' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\Zabbix', 'zabbix', array('hostname' => 'foobar.com', 'server' => 'localhost', 'port' => 1234)),
            array('Beberlei\Metrics\Collector\Zabbix', 'zabbix_file', array('hostname' => 'foobar.com')),
            array('Beberlei\Metrics\Collector\Zabbix', 'zabbix_file', array('hostname' => 'foobar.com', 'file' => '/tmp/foobar')),
            array('Beberlei\Metrics\Collector\Librato', 'librato', array('hostname' => 'foobar.com', 'username' => 'username', 'password' => 'password')),
            array('Beberlei\Metrics\Collector\DoctrineDBAL', 'doctrine_dbal', array('connection' => $this->getMock('Doctrine\DBAL\Connection'))),
            array('Beberlei\Metrics\Collector\Monolog', 'monolog', array('logger' => 'a logger')),
            array('Beberlei\Metrics\Collector\Null', 'null'),
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
            array('You should specified a host if you specified a port.', 'graphite', array('port' => '1234')),
            array('Hostname is required for zabbix collector.', 'zabbix'),
            array('Hostname is required for zabbix collector.', 'zabbix', array('hostname', 'foobar.com')),
            array('You should specified a server if you specified a port.', 'zabbix', array('hostname' => 'foobar.com', 'port' => '1234')),
            array('Hostname is required for zabbix collector.', 'zabbix_file'),
            array('Hostname is required for librato collector.', 'librato'),
            array('No username given for librato collector.', 'librato', array('hostname' => 'foobar.com')),
            array('No password given for librato collector.', 'librato', array('hostname' => 'foobar.com', 'username' => 'username')),
            array('connection is required for Doctrine DBAL collector.', 'doctrine_dbal'),
            array('Missing \'logger\' key with monolog service.', 'monolog'),
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
