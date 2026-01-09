<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('beberlei_metrics.util.buzz.curl', \Buzz\Client\Curl::class)
        ->private();

    $services->set('beberlei_metrics.util.buzz.browser', \Buzz\Browser::class)
        ->private()
        ->args([service('beberlei_metrics.util.buzz.curl')]);

    $services->set('beberlei_metrics.collector_proto.doctrine_dbal', \Beberlei\Metrics\Collector\DoctrineDBAL::class)
        ->abstract()
        ->args([
            abstract_arg('connection'),
        ]);

    $services->set('beberlei_metrics.collector_proto.graphite', \Beberlei\Metrics\Collector\Graphite::class)
        ->abstract()
        ->args([
            abstract_arg('host'),
            abstract_arg('port'),
            abstract_arg('protocol'),
        ]);

    $services->set('beberlei_metrics.collector_proto.influxdb', \Beberlei\Metrics\Collector\InfluxDB::class)
        ->abstract()
        ->args([
            abstract_arg('client'),
        ]);

    $services->set('beberlei_metrics.collector_proto.librato', \Beberlei\Metrics\Collector\Librato::class)
        ->abstract()
        ->args([
            service('beberlei_metrics.util.buzz.browser'),
            abstract_arg('source'),
            abstract_arg('username'),
            abstract_arg('password'),
        ]);

    $services->set('beberlei_metrics.collector_proto.logger', \Beberlei\Metrics\Collector\Logger::class)
        ->abstract()
        ->args([service('logger')])
        ->tag('monolog.logger', ['channel' => 'beberlei_metrics']);

    $services->set('beberlei_metrics.collector_proto.null', \Beberlei\Metrics\Collector\NullCollector::class)
        ->abstract();

    $services->set('beberlei_metrics.collector_proto.prometheus', \Beberlei\Metrics\Collector\Prometheus::class)
        ->abstract()
        ->args([
            abstract_arg('collector registry'),
            abstract_arg('namespace'),
        ]);

    $services->set('beberlei_metrics.collector_proto.statsd', \Beberlei\Metrics\Collector\StatsD::class)
        ->abstract()
        ->args([
            abstract_arg('host'),
            abstract_arg('port'),
            abstract_arg('prefix'),
        ]);

    $services->set('beberlei_metrics.collector_proto.dogstatsd', \Beberlei\Metrics\Collector\DogStatsD::class)
        ->abstract()
        ->args([
            abstract_arg('host'),
            abstract_arg('port'),
            abstract_arg('prefix'),
        ]);

    $services->set('beberlei_metrics.collector_proto.telegraf', \Beberlei\Metrics\Collector\Telegraf::class)
        ->abstract()
        ->args([
            abstract_arg('host'),
            abstract_arg('port'),
            abstract_arg('prefix'),
        ]);

    $services->set('beberlei_metrics.collector_proto.zabbix', \Beberlei\Metrics\Collector\Zabbix::class)
        ->abstract()
        ->args([
            abstract_arg('sender'),
            abstract_arg('prefix'),
        ]);

    $services->set('beberlei_metrics.collector_proto.memory', \Beberlei\Metrics\Collector\InMemory::class)
        ->abstract();
};
