<?php

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
                                ->scalarNode('type')
                                    ->isRequired()
                                    ->validate()
                                        ->ifTrue(static fn ($v) => !\is_string($v))
                                        ->thenInvalid('The type must be a string got "%s".')
                                    ->end()

                                ->end()
                                ->scalarNode('connection')->defaultNull()->end()
                                ->scalarNode('file')->defaultNull()->end()
                                ->scalarNode('host')->defaultNull()->end()
                                ->scalarNode('password')->defaultNull()->end()
                                ->integerNode('port')->defaultNull()->end()
                                ->scalarNode('prefix')->defaultNull()->end()
                                ->scalarNode('protocol')->defaultNull()->end()
                                ->scalarNode('source')->defaultNull()->end()
                                ->scalarNode('username')->defaultNull()->end()
                                ->scalarNode('influxdb_client')->defaultNull()->end()
                                ->scalarNode('prometheus_collector_registry')->defaultNull()->info('It must to contain service id for Prometheus\\CollectorRegistry class instance.')->end()
                                ->scalarNode('namespace')->defaultValue('')->end()
                                ->arrayNode('tags')
                                    ->defaultValue([])
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                            ->validate()
                                ->ifTrue(static fn ($v): bool => 'prometheus' === $v['type'] && empty($v['prometheus_collector_registry']))
                                ->thenInvalid('The prometheus_collector_registry has to be specified to use a Prometheus')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
