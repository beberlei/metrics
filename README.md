# Metrics

[![Build Status](https://travis-ci.org/beberlei/metrics.svg?branch=master)](https://travis-ci.org/beberlei/metrics)

Simple library that abstracts different metrics collectors. I find this
necessary to have a consistent and simple metrics API that doesn't cause vendor
lock-in.

It also ships with a Symfony Bundle. **This is not a library for displaying metrics.**

Currently supported backends:

* Doctrine DBAL
* Graphite
* InfluxDB
* Telegraf
* Logger (Psr\Log\LoggerInterface)
* Null (Dummy that does nothing)
* Prometheus
* StatsD
* DogStatsD

## Installation

Using Composer:

```bash
composer require beberlei/metrics
```

## API

You can instantiate clients:

```php
<?php

$collector = \Beberlei\Metrics\Factory::create('statsd');
```

You can measure stats:

```php
<?php

$collector->increment('foo.bar');
$collector->decrement('foo.bar');

$start = microtime(true);
$diff  = microtime(true) - $start;
$collector->timing('foo.bar', $diff);

$value = 1234;
$collector->measure('foo.bar', $value);
```

Some backends defer sending and aggregate all information, make sure to call
flush:

```php
<?php

$collector->flush();
```

## Configuration

```php
<?php
$statsd = \Beberlei\Metrics\Factory::create('statsd');

$null = \Beberlei\Metrics\Factory::create('null');
```

## Symfony Bundle Integration

Register Bundle into Kernel:

```php
<?php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        //..
        $bundles[] = new \Beberlei\Bundle\MetricsBundle\BeberleiMetricsBundle();
        //..
    }
}
```

Do Configuration:

```yaml
# app/config/config.yml
beberlei_metrics:
    default: foo
    collectors:
        foo:
            type: statsd
        dbal:
            type: doctrine_dbal
            connection: metrics # using the connection named "metrics"
        monolog:
            type: monolog
        influxdb:
            type: influxdb
            database: metrics
            tags:
                dc: "west"
                node_instance: "hermes10"
        prometheus:
            type: prometheus
            prometheus_collector_registry: prometheus_collector_registry_service # using the Prometheus collector registry service named "prometheus_collector_registry_service"
            namespace: app_name # optional
            tags:
                dc: "west"
                node_instance: "hermes10"
```

This adds collectors to the Metrics registry. The functions are automatically
included in the Bundle class so that in your code you can just start using the
convenient functions. Metrics are also added as services:

```php
<?php

$metrics = $container->get('beberlei_metrics.collector.foo');
```

and the default collector can be fetched:

```php
<?php

$metrics = $container->get('beberlei_metrics.collector');
```

