# Metrics

Simple library that abstracts different metrics collectors. I find this necessary
to have a consistent and simple metrics API that doesn't cause vendor lock-in.

It also ships with a Symfony Bundle. This is not a library for displaying metrics.

Currently supported backends:

* StatsD
* Zabbix
* Librato
* Doctrine DBAL
* Null (Dummy that does nothing)

## Installation

Using Composer:

    {
        "require": {
            "beberlei/metrics": "*"
        }
    }

## API

You can instantiate clients:

```php
<?php
$metrics = \Beberlei\Metrics\Factory::create('statsd');
\Beberlei\Metrics\Registry::set('name', $metrics);
\Beberlei\Metrics\Registry::setDefaultName('name');

$metrics = \Beberlei\Metrics\Registry::get('name');
$metrics = \Beberlei\Metrics\Registry::get();
```

You can measure stats:

```php
<?php

$metrics = \Beberlei\Metrics\Registry::get('name');
$metrics->increment('foo.bar');
$metrics->decrement('foo.bar');

$start = microtime(true);
$diff  = microtime(true) - $start;
$metrics->timing('foo.bar', $diff);

$value = 1234;
$metrics->measure('foo.bar', $value);
```

Some backends defer sending and aggregate all information, make sure
to call flush:

```php
<?php

$metrics = \Beberlei\Metrics\Registry::get('name');
$metrics->flush();
```

There is a convenience functional API. It works with
the registry names. If null is provided, the default registry entry is used.

```php
<?php
$registryName = 'name';

bmetrics_increment('foo.bar', $registryName);
bmetrics_decrement('foo.bar', null);
bmetrics_measure('foo.bar', $value, $registryName);
bmetrics_timing('foo.bar', $diff, null);
```

The functions are automatically available through the Composer autoload files mechanism.

## Configuration

```php
<?php
$statsd = \Beberlei\Metrics\Factory::create('statsd');

$zabbix = \Beberlei\Metrics\Factory::create('zabbix', array(
    'hostname' => 'foo.beberlei.de',
    'server'   => 'localhost',
    'port'     => 10051,
));

$zabbixConfig = \Beberlei\Metrics\Factory::create('zabbix_file', array(
    'hostname' => 'foo.beberlei.de',
    'file'     => '/etc/zabbix/zabbix_agentd.conf'
));

$librato = \Beberlei\Metrics\Factory::create('librato', array(
    'hostname' => 'foo.beberlei.de',
    'username' => 'foo',
    'password' => 'bar',
));

$null = \Beberlei\Metrics\Factory::create('null');
```

## Symfony Bundle Integration

Activate Bundle into Kernel:

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

    # app/config/config.yml
    beberlei_metrics:
        default: foo
        collectors:
            foo:
                type: statsd
            bar:
                type: zabbix
                hostname: foo.beberlei.de
                server: localhost
                port: 10051
            baz:
                type: zabbix_file
                hostname: foo.beberlei.de
                file: /etc/zabbix/zabbix_agentd.conf
            librato:
                type: librato
                username: foo
                password: bar
            dbal:
                type: doctrine_dbal
                connection: doctrine.dbal.metrics_connection

This adds collectors to the Metrics registry. The functions are automatically included
in the Bundle class so that in your code you can just start using the convenience functions.
Metrics are also added as services:

```php
<?php
$metrics = $container->get('beberlei_metrics.collector.foo');
```

