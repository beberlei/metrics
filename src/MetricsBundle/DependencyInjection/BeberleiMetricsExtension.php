<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\DoctrineDBAL;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\InfluxDbV1;
use Beberlei\Metrics\Collector\InMemory;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\Telegraf;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory as InMemoryStorage;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class BeberleiMetricsExtension extends Extension
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

    private const ABSTRACT_SERVICES = [
        'doctrine_dbal' => DoctrineDBAL::class,
        'dogstatsd' => DogStatsD::class,
        'graphite' => Graphite::class,
        'influxdb_v1' => InfluxDbV1::class,
        'logger' => Logger::class,
        'memory' => InMemory::class,
        'null' => NullCollector::class,
        'prometheus' => Prometheus::class,
        'statsd' => StatsD::class,
        'telegraf' => Telegraf::class,
    ];

    /** @param list<array<string, mixed>> $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container) ?? throw new \LogicException('Expected configuration to be set');
        /** @var array{default: ?string, collectors: array<string, array<string, mixed>>} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $this->registerAbstractServices($container);

        if (!$config['collectors']) {
            $config['collectors']['null'] = ['type' => 'null'];
        }
        foreach ($config['collectors'] as $name => $colConfig) {
            /* @var array<string, mixed> $colConfig */
            $colConfig['name'] = $name;
            $definition = $this->createCollector($container, $colConfig['type'], $colConfig);
            $container->setDefinition('beberlei_metrics.collector.' . $name, $definition);
            $container->registerAliasForArgument('beberlei_metrics.collector.' . $name, CollectorInterface::class, $name);
        }

        if ($config['default']) {
            if (!$container->hasDefinition('beberlei_metrics.collector.' . $config['default'])) {
                throw new InvalidArgumentException(\sprintf('The default collector "%s" does not exist.', $config['default']));
            }
            $name = $config['default'];
        } elseif (1 === \count($config['collectors'])) {
            $name = key($config['collectors']);
        } else {
            throw new InvalidArgumentException('No default collector is configured and there is more than one collector. Please define a default collector');
        }

        $container->setAlias(CollectorInterface::class, 'beberlei_metrics.collector.' . $name);
    }

    private function registerAbstractServices(ContainerBuilder $container): void
    {
        foreach (self::ABSTRACT_SERVICES as $type => $class) {
            $container->setDefinition(
                'beberlei_metrics.collector_proto.' . $type,
                new Definition($class)->setAbstract(true),
            );
        }

        $logger = $container->getDefinition('beberlei_metrics.collector_proto.logger');
        $logger->setArgument('$logger', new Reference('logger'));
        $logger->addTag('monolog.logger', ['channel' => 'beberlei_metrics']);

        $registry = new Definition(CollectorRegistry::class)
            ->setArgument('$storageAdapter', new Definition(InMemoryStorage::class))
        ;
        $container->setDefinition('beberlei_metrics.collector_proto.prometheus.registry', $registry->setAbstract(true));

        $database = new Definition(\InfluxDB\Database::class)
            ->setFactory([\InfluxDB\Client::class, 'fromDSN'])
        ;
        $container->setDefinition('beberlei_metrics.collector_proto.influxdb_v1.database', $database->setAbstract(true));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createCollector(ContainerBuilder $container, string $type, array $config): ChildDefinition
    {
        $definition = new ChildDefinition('beberlei_metrics.collector_proto.' . $type);

        // Theses listeners should be as late as possible
        $definition->addTag('kernel.event_listener', ['method' => 'flush', 'priority' => -1024, 'event' => 'kernel.terminate']);
        $definition->addTag('kernel.event_listener', ['method' => 'flush', 'priority' => -1024, 'event' => 'console.terminate']);
        $definition->addTag(CollectorInterface::class);
        $definition->addTag('kernel.reset', ['method' => 'flush']);

        /** @var array<string, scalar> $tags */
        $tags = $config['tags'] ?? [];

        switch ($type) {
            case 'influxdb_v1':
                if (!class_exists(\InfluxDB\Client::class)) {
                    throw new \LogicException('The "influxdb/influxdb-php" package is required to use the "influxdb" collector.');
                }

                if ($config['service']) {
                    $database = new Reference($config['service']);
                } else {
                    $database = new ChildDefinition('beberlei_metrics.collector_proto.influxdb_v1.database');
                    $database->replaceArgument('$dsn', \sprintf(
                        'influxdb://%s:%s@%s:%s/%s',
                        $config['username'],
                        $config['password'],
                        $config['host'],
                        $config['port'] ?? 8086,
                        $config['database'],
                    ));
                }

                return $definition
                    ->replaceArgument('$database', $database)
                    ->replaceArgument('$tags', $tags)
                ;
            case 'prometheus':
                if (!class_exists(CollectorRegistry::class)) {
                    throw new \LogicException('The "promphp/prometheus_client_php" package is required to use the "prometheus" collector.');
                }

                if ($config['service']) {
                    $registryId = $config['service'];
                } else {
                    $container->setDefinition(
                        $registryId = 'beberlei_metrics.collector.' . ($config['name'] ?? $type) . '.prometheus.registry',
                        new ChildDefinition('beberlei_metrics.collector_proto.prometheus.registry'),
                    );

                    if (!$container->hasAlias(CollectorRegistry::class)) {
                        $container->setAlias(CollectorRegistry::class, $registryId);
                    }
                }

                return $definition
                    ->replaceArgument('$registry', new Reference($registryId))
                    ->replaceArgument('$namespace', $config['namespace'])
                    ->replaceArgument('$tags', $tags)
                ;
            case 'graphite':
                return $definition
                    ->replaceArgument('$host', $config['host'])
                    ->replaceArgument('$port', $config['port'] ?? 2003)
                    ->replaceArgument('$protocol', $config['protocol'] ?? 'tcp')
                ;
            case 'statsd':
            case 'dogstatsd':
                return $definition
                    ->replaceArgument('$host', $config['host'])
                    ->replaceArgument('$port', $config['port'] ?? 8125)
                    ->replaceArgument('$prefix', $config['prefix'])
                ;
            case 'telegraf':
                return $definition
                    ->replaceArgument('$host', $config['host'])
                    ->replaceArgument('$port', $config['port'] ?? 8125)
                    ->replaceArgument('$prefix', $config['prefix'])
                    ->replaceArgument('$tags', $tags)
                ;
            case 'doctrine_dbal':
                $ref = $config['connection'] ? \sprintf('doctrine.dbal.%s_connection', $config['connection']) : 'database_connection';

                return $definition->replaceArgument('$conn', new Reference($ref));
            case 'logger':
            case 'memory':
            case 'null':
                return $definition;
            default:
                throw new InvalidArgumentException(\sprintf('The type "%s" is not supported.', $type));
        }
    }
}
