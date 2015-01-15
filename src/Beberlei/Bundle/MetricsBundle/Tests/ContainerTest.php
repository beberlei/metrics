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

namespace Beberlei\Bundle\MetricsBundle\Tests;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\DependencyInjection\Compiler\ResolveDefinitionTemplatesPass;
use Beberlei\Bundle\MetricsBundle\DependencyInjection\BeberleiMetricsExtension;
use Beberlei\Bundle\MetricsBundle\BeberleiMetricsBundle;
use Beberlei\Metrics\Registry;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildContainer()
    {
        $container = $this->createContainer(array(array(
            'default' => 'foo',
            'collectors' => array(
                'foo'     => array('type' => 'statsd'),
                'bar'     => array('type' => 'zabbix', 'hostname' => 'foo.beberlei.de', 'server' => 'localhost', 'port' => 10051),
                'baz'     => array('type' => 'zabbix', 'hostname' => 'foo.beberlei.de', 'file' => '/etc/zabbix/zabbix_agentd.conf'),
                'librato' => array('type' => 'librato', 'username' => 'foo', 'password' => 'bar', 'hostname' => 'foo.beberlei.de'),
            ),
        )));

        $this->assertInstanceOf('Beberlei\Metrics\Collector\StatsD', $container->get('beberlei_metrics.collector.foo'));
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Zabbix', $container->get('beberlei_metrics.collector.bar'));
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Zabbix', $container->get('beberlei_metrics.collector.baz'));
        $this->assertInstanceOf('Beberlei\Metrics\Collector\Librato', $container->get('beberlei_metrics.collector.librato'));
        $this->assertInstanceOf('Beberlei\Metrics\Registry', $container->get('beberlei_metrics.registry'));
    }

    public function testBootShutdownBundle()
    {
        $container = $this->createContainer(array(array(
            'collectors' => array(
                'default' => array('type' => 'null'),
            ),
        )));

        $bundle = new BeberleiMetricsBundle();
        $bundle->setContainer($container);
        $bundle->boot();
        $bundle->shutdown();
    }

    private function createContainer($configs)
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.bundles'     => array(),
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__.'/../../../../', // src dir
        )));

        $loader = new BeberleiMetricsExtension();
        $container->registerExtension($loader);
        $loader->load($configs, $container);
        $container->setDefinition('logger', new Definition('Psr\Log\NullLogger'));

        $container->getCompilerPassConfig()->setOptimizationPasses(array(new ResolveDefinitionTemplatesPass()));
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }

    public function setUp()
    {
        Registry::clear();
    }
}
