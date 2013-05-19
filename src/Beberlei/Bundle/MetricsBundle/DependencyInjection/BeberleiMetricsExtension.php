<?php
namespace Beberlei\Bundle\MetricsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;

class BeberleiMetricsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('metrics.xml');

        $registry = $container->getDefinition('beberlei_metrics.registry');
        $registry->addMethodCall('setDefaultName', array($config['default']));

        foreach ($config['collectors'] as $name => $options) {
            $type = $options['type'];
            unset($options['type']);

            if ('doctrine_dbal' == $type) {
                $options['connection'] = new Reference(sprintf(
                    'doctrine.dbal.%s_connection', $options['connection']
                ));
            }

            if ('monolog' == $type) {
                $options['logger'] = new Reference('logger');
            }

            $def = new Definition('Beberlei\Metrics\Collector\Collector');
            $def->setFactoryMethod('create');
            $def->setFactoryClass('%beberlei_metrics.factory.class%');
            $def->setArguments(array($type, $options));

            $container->setDefinition('beberlei_metrics.collector.' . $name, $def);

            $registry->addMethodCall('set', array(
                $name, new Reference('beberlei_metrics.collector.' . $name)
            ));
        }
    }
}
