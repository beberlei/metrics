# Metrics

Simple library that abstracts different metrics collectors. I find this
necessary to have a consistent and simple metrics API that doesn't cause vendor
lock-in.

It also ships with a Symfony Bundle. This is not a library for displaying
metrics.

Currently supported backends:

* Doctrine DBAL
* Graphite
* Librato
* Logger (Psr\Log\LoggerInterface)
* Null (Dummy that does nothing)
* StatsD
* Zabbix

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
                previx: foo.beberlei.de
                host: localhost
                port: 10051
            baz:
                type: zabbix_file
                previx: foo.beberlei.de
                file: /etc/zabbix/zabbix_agentd.conf
            librato:
                type: librato
                username: foo
                password: bar
            dbal:
                type: doctrine_dbal
                connection: metrics # using the connection named "metrics"
            monolog:
                type: monolog

This adds collectors to the Metrics registry. The functions are automatically
included in the Bundle class so that in your code you can just start using the
convenience functions. Metrics are also added as services:

```php
<?php

$metrics = $container->get('beberlei_metrics.collector.foo');
```

and the default collactor can be fetch:

```php
<?php

$metrics = $container->get('beberlei_metrics.collector');
```

