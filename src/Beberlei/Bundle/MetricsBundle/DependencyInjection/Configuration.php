<?php
namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

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
                ->scalarNode('default')
                    ->defaultNull()
                ->end()
                ->arrayNode('collectors')
                    ->isRequired()
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->end()
                            ->scalarNode('connection')->defaultValue('default')->end()
                            ->scalarNode('file')->defaultNull()->end()
                            ->scalarNode('host')->defaultNull()->end()
                            ->scalarNode('password')->defaultNull()->end()
                            ->scalarNode('port')->defaultNull()->end()
                            ->scalarNode('prefix')->defaultNull()->end()
                            ->scalarNode('protocol')->defaultNull()->end()
                            ->scalarNode('source')->defaultNull()->end()
                            ->scalarNode('username')->defaultNull()->end()
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) { return 'librato' === $v['type'] && empty($v['source']); })
                            ->thenInvalid('The source has to be specified to use a Librato')
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) { return 'librato' === $v['type'] && empty($v['username']); })
                            ->thenInvalid('The username has to be specified to use a Librato')
                        ->end()
                        ->validate()
                            ->ifTrue(function ($v) { return 'librato' === $v['type'] && empty($v['password']); })
                            ->thenInvalid('The password has to be specified to use a Librato')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
