<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder('beberlei_metrics')
            ->getRootNode()
                ->children()
                    ->scalarNode('default')
                        ->defaultNull()
                    ->end()
                    ->arrayNode('collectors')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->enumNode('type')
                                    ->values(BeberleiMetricsExtension::TYPES)
                                    ->isRequired()
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('host')->defaultValue('localhost')->end()
                                ->scalarNode('protocol')->defaultNull()->end()
                                ->integerNode('port')->defaultNull()->end()
                                ->scalarNode('username')->defaultValue('')->end()
                                ->scalarNode('password')->defaultValue('')->end()
                                ->scalarNode('prefix')->defaultValue('')->end()
                                ->scalarNode('service')->defaultNull()->end()
                                ->arrayNode('tags')
                                    ->defaultValue([])
                                    ->prototype('scalar')->end()
                                ->end()
                                // Doctrine DBAL stuff
                                ->scalarNode('connection')->defaultNull()->end()
                                // Also used as the instrumentation scope name by OpenTelemetry,
                                // and as the CloudWatch namespace
                                ->scalarNode('namespace')->defaultValue('')->end()
                                // InfluxDB v1 stuff
                                ->scalarNode('database')->defaultValue('')->end()
                                // InfluxDB v2 stuff
                                ->scalarNode('token')->defaultValue('')->end()
                                ->scalarNode('org')->defaultValue('')->end()
                                ->scalarNode('bucket')->defaultValue('')->end()
                                // CloudWatch stuff
                                ->scalarNode('region')->defaultValue('')->end()
                                // Chain stuff
                                ->arrayNode('collectors')
                                    ->defaultValue([])
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'influxdb_v1' === $v['type'] && '' === $v['database'])
                                ->thenInvalid('The "database" has to be specified to use InfluxDB')
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'chain' === $v['type'] && [] === $v['collectors'])
                                ->thenInvalid('The "collectors" option must not be empty to use the chain collector')
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'opentelemetry' === $v['type'] && !$v['service'])
                                ->thenInvalid('The "service" option must be set to the service id of a "OpenTelemetry\API\Metrics\MeterProviderInterface" instance to use the OpenTelemetry collector')
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'influxdb_v2' === $v['type'] && !$v['service'] && ('' === $v['token'] || '' === $v['org'] || '' === $v['bucket']))
                                ->thenInvalid('The "token", "org" and "bucket" options are required to use the InfluxDbV2 collector, unless "service" is set to the service id of a pre-configured "InfluxDB2\WriteApi" instance')
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'cloudwatch' === $v['type'] && !$v['service'] && '' === $v['region'])
                                ->thenInvalid('The "region" option is required to use the CloudWatch collector, unless "service" is set to the service id of a pre-configured "Aws\CloudWatch\CloudWatchClient" instance')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
