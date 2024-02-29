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
        $treeBuilder = new TreeBuilder('beberlei_metrics');
        if (method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            $rootNode = $treeBuilder->root('beberlei_metrics');
        }

        $rootNode
            ->children()
                ->scalarNode('default')
                    ->defaultNull()
                ->end()
                ->arrayNode('collectors')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->isRequired()->end()
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
                                ->defaultValue(array())
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return 'librato' === $v['type'] && empty($v['source']);
                            })
                            ->thenInvalid('The source has to be specified to use a Librato')
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return 'librato' === $v['type'] && empty($v['username']);
                            })
                            ->thenInvalid('The username has to be specified to use a Librato')
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return 'librato' === $v['type'] && empty($v['password']);
                            })
                            ->thenInvalid('The password has to be specified to use a Librato')
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) {
                                return 'prometheus' === $v['type'] && empty($v['prometheus_collector_registry']);
                            })
                            ->thenInvalid('The prometheus_collector_registry has to be specified to use a Prometheus')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
