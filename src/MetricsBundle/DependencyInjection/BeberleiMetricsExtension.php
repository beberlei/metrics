<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

use Beberlei\Metrics\Collector\CollectorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BeberleiMetricsExtension extends Extension
{
    public const TYPES = [
        'doctrine_dbal',
        'dogstatsd',
        'graphite',
        'influxdb_v1',
        'logger',
        'memory',
        'null',
        'prometheus',
        'statsd',
        'telegraf',
    ];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container) ?? throw new \LogicException('Expected configuration to be set');
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('metrics.xml');

        if (!$config['collectors']) {
            $config['collectors']['null'] = [
                'type' => 'null',
            ];
        }
        foreach ($config['collectors'] as $name => $colConfig) {
            $definition = $this->createCollector($colConfig['type'], $colConfig);
            $container->setDefinition('beberlei_metrics.collector.' . $name, $definition);
            $container->registerAliasForArgument('beberlei_metrics.collector.' . $name, CollectorInterface::class, $name);
        }

        if ($config['default']) {
            if (!$container->hasDefinition('beberlei_metrics.collector.' . $config['default'])) {
                throw new InvalidArgumentException(sprintf('The default collector "%s" does not exist.', $config['default']));
            }
            $name = $config['default'];
        } elseif (1 === \count($config['collectors'])) {
            $name = key($config['collectors']);
        } else {
            throw new InvalidArgumentException('No default collector is configured and there is more than one collector. Please define a default collector');
        }

        if ($name) {
            $container->setAlias(CollectorInterface::class, 'beberlei_metrics.collector.' . $name);
        }
    }

    private function createCollector(string $type, array $config): ChildDefinition
    {
        $definition = new ChildDefinition('beberlei_metrics.collector_proto.' . $config['type']);

        // Theses listeners should be as late as possible
        $definition->addTag('kernel.event_listener', ['method' => 'flush', 'priority' => -1024, 'event' => 'kernel.terminate']);
        $definition->addTag('kernel.event_listener', ['method' => 'flush', 'priority' => -1024, 'event' => 'console.terminate']);
        $definition->addTag(CollectorInterface::class);
        $definition->addTag('kernel.reset', ['method' => 'flush']);

        if ($config['tags'] ?? []) {
            $definition->addMethodCall('setTags', [$config['tags']]);
        }

        switch ($type) {
            case 'doctrine_dbal':
                $ref = $config['connection'] ? sprintf('doctrine.dbal.%s_connection', $config['connection']) : 'database_connection';
                $definition->replaceArgument(0, new Reference($ref));

                return $definition;
            case 'graphite':
                $definition->replaceArgument(0, $config['host']);
                $definition->replaceArgument(1, $config['port'] ?? 2003);
                $definition->replaceArgument(2, $config['protocol'] ?? 'tcp');

                return $definition;
            case 'influxdb_v1':
                if (!class_exists(\InfluxDB\Client::class)) {
                    throw new \LogicException('The "influxdb/influxdb-php" package is required to use the "influxdb" collector.');
                }
                if ($config['service']) {
                    $definition->replaceArgument(0, new Reference($config['service']));
                } else {
                    $database = new ChildDefinition('beberlei_metrics.collector_proto.influxdb_v1.database');
                    $database->replaceArgument(0, sprintf('influxdb://%s:%s@%s:%s/%s',
                        $config['username'],
                        $config['password'],
                        $config['host'],
                        $config['port'] ?? 8086,
                        $config['database'],
                    ));
                    $definition->replaceArgument(0, $database);
                }

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
                $definition->replaceArgument(0, $config['host']);
                $definition->replaceArgument(1, $config['port'] ?? 8125);
                $definition->replaceArgument(2, $config['prefix']);

                return $definition;
            case 'telegraf':
                $definition->replaceArgument(0, $config['host']);
                $definition->replaceArgument(1, $config['port'] ?? 8125);
                $definition->replaceArgument(2, $config['prefix']);

                return $definition;
            default:
                throw new \InvalidArgumentException(sprintf('The type "%s" is not supported.', $type));
        }
    }
}
