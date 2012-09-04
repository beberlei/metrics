<?php
namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('beberlei_metrics');

        $rootNode
            ->children()
                ->scalarNode('default')->defaultValue('default')->end()
                ->arrayNode('collectors')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->isRequired()->end()
                            ->scalarNode('hostname')->end()
                            ->scalarNode('server')->end()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('file')->end()
                            ->scalarNode('username')->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('connection')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}

