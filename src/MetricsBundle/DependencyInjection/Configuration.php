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

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return (new TreeBuilder('beberlei_metrics'))
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
                                // Prom stuff
                                ->scalarNode('prometheus_collector_registry')->defaultNull()->info('It must to contain service id for Prometheus\\CollectorRegistry class instance.')->end()
                                ->scalarNode('namespace')->defaultValue('')->end()
                                // InfluxDB stuff
                                ->scalarNode('database')->defaultValue('')->end()
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'prometheus' === $v['type'] && empty($v['prometheus_collector_registry']))
                                ->thenInvalid('The "prometheus_collector_registry" has to be specified to use a Prometheus')
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'influxdb' === $v['type'] && empty($v['database']))
                                ->thenInvalid('The "database" has to be specified to use a InfluxDB')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
