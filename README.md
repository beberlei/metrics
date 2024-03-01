# Metrics

Simple library that abstracts different metrics collectors. I find this
necessary to have a consistent and simple metrics API that doesn't cause vendor
lock-in.

It also ships with a Symfony Bundle. **This is not a library for displaying metrics.**

Currently supported backends:

* Doctrine DBAL
* DogStatsD
* Graphite
* InfluxDb (version 1)
* Logger (Psr\Log\LoggerInterface)
* Null (Dummy that does nothing)
* Prometheus
* StatsD
* Telegraf

## Installation

Using Composer:

```
composer require beberlei/metrics
```

## API

You can instantiate clients:

```php
$collector = \Beberlei\Metrics\Factory::create('statsd');
```

You can measure stats:

```php
$collector->increment('foo.bar');
$collector->decrement('foo.bar');

$start = microtime(true);
$diff  = microtime(true) - $start;
$collector->timing('foo.bar', $diff);

$value = 1234;
$collector->measure('foo.bar', $value);
```

All backends defer sending and aggregate all information, make sure to call
flush:

```php
$collector->flush();
```

## Configuration

```php

$null = \Beberlei\Metrics\Factory::create('null');
```

## Symfony Bundle Integration

Register Bundle in bundles.php

```php
// config/bundles.php

return [
    // ...
    Beberlei\Bundle\MetricsBundle\BeberleiMetricsBundle::class => ['all' => true],
];

```

Do some configuration:

```yaml
# app/config/config.yml
beberlei_metrics:
    default: statsd
    collectors:
        influxdb:
            type: influxdb
            database: metrics
            # If you want to use a custom database service
            # It must be an instance of "InfluxDB\Database"
            # In this case, you can omit de "database" option
            # service: my.service.id
            tags: # optional
                dc: "west"
                node_instance: "hermes10"
        prometheus:
            type: prometheus
            # If you want to use a custom registry service
            # It must be an instance of "Prometheus\CollectorRegistry"
            # By default it uses an "Prometheus\Storage\InMemory" adapter
            # service: my.service.id
            namespace: app_name # optional
            tags: # optional
                dc: "west"
                node_instance: "hermes10"
        statsd:
            type: statsd
            # host: localhost # default
            # port: 8125 # default
            # prefix: '' # default
        dogstatsd:
            type: dogstatsd
            # host: localhost # default
            # port: 8125 # default
            # prefix: '' # default
        dbal:
            type: doctrine_dbal
            # Use another connection, by default it uses the default connection
            # connection: metrics
        monolog:
            type: monolog
```

Then, you can inject the `Beberlei\Metrics\Collector\CollectorInterface` and
start using it:

```php
use Beberlei\Metrics\Collector\CollectorInterface;

final readonly class MyService
{

    public function __construct(
        private CollectorInterface $collector,
    ) {
    }

    public function doSomething(): void
    {
        $this->collector->increment('foo.bar');
    }
}
```

The `Beberlei\Metrics\Collector\CollectorInterface` is automatically aliased to
the default collector.

If you want to inject a specific collector, you must use the `#[Target]` attribute:
```php
public function __construct(
    #[Target('name_of_the_collector')]
    CollectorInterface $memoryCollector,
) {
```
