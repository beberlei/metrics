<?php

namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

use Beberlei\Metrics\Collector\Collector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BeberleiMetricsExtension extends Extension
{
    /**
     * @return void
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('metrics.xml');

        foreach ($config['collectors'] as $name => $colConfig) {
            $definition = $this->createCollector($colConfig['type'], $colConfig);
            $container->setDefinition('beberlei_metrics.collector.'.$name, $definition);
        }

        if ($config['default'] && $container->hasDefinition('beberlei_metrics.collector.'.$config['default'])) {
            $name = $config['default'];
        } elseif (1 !== count($config['collectors'])) {
            throw new \LogicException('You should select a default collector');
        }

        $container->setAlias('beberlei_metrics.collector', 'beberlei_metrics.collector.'.$name);
        $container->setAlias(Collector::class, 'beberlei_metrics.collector');
    }

    private function createCollector($type, $config)
    {
        $definition = new ChildDefinition('beberlei_metrics.collector_proto.'.$config['type']);

        // Theses listeners should be as late as possible
        $definition->addTag('kernel.event_listener', array(
            'method' => 'flush',
            'priority' => -1024,
            'event' => 'kernel.terminate',
        ));
        $definition->addTag('kernel.event_listener', array(
            'method' => 'flush',
            'priority' => -1024,
            'event' => 'console.terminate',
        ));

        if (count($config['tags']) > 0) {
            $definition->addMethodCall('setTags', array($config['tags']));
        }

        switch ($type) {
            case 'doctrine_dbal':
                $ref = $config['connection'] ? sprintf('doctrine.dbal.%s_connection', $config['connection']) : 'database_connection';
                $definition->replaceArgument(0, new Reference($ref));

                return $definition;
            case 'graphite':
                $definition->replaceArgument(0, $config['host'] ?: 'localhost');
                $definition->replaceArgument(1, $config['port'] ?: 2003);
                $definition->replaceArgument(2, $config['protocol'] ?: 'tcp');

                return $definition;
            case 'influxdb':
                $definition->replaceArgument(0, new Reference($config['influxdb_client']));

                return $definition;
            case 'librato':
                $definition->replaceArgument(1, $config['source']);
                $definition->replaceArgument(2, $config['username']);
                $definition->replaceArgument(3, $config['password']);

                return $definition;
            case 'logger':
            case 'null':
            case 'memory':
                return $definition;
            case 'prometheus':
                $definition->replaceArgument(0, new Reference($config['prometheus_collector_registry']));
                $definition->replaceArgument(1, $config['namespace']);

                return $definition;
            case 'statsd':
            case 'dogstatsd':
                $definition->replaceArgument(0, $config['host'] ?: 'localhost');
                $definition->replaceArgument(1, $config['port'] ?: 8125);
                $definition->replaceArgument(2, (string) $config['prefix']);

                return $definition;
            case 'telegraf':
                $definition->replaceArgument(0, $config['host'] ?: 'localhost');
                $definition->replaceArgument(1, $config['port'] ?: 8125);
                $definition->replaceArgument(2, (string) $config['prefix']);

                return $definition;
            case 'zabbix':
                $sender = new Definition('Net\Zabbix\Sender');
                if ($config['file']) {
                    $senderConfig = new Definition('Net\Zabbix\Agent\Config');
                    $senderConfig->addArgument($config['file']);
                    $sender->addMethodCall('importAgentConfig', array($senderConfig));
                } else {
                    $sender->addArgument($config['host'] ?: 'localhost');
                    $sender->addArgument((int) $config['port'] ?: 10051);
                }

                $definition->replaceArgument(0, $sender);
                $definition->replaceArgument(1, $config['prefix']);

                return $definition;
            default:
                throw new \InvalidArgumentException(sprintf('The type "%s" is not supported', $type));
        }
    }
}
