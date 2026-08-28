# UPGRADE

This document describes **every** breaking change between major versions and how
to migrate your code. Read it carefully before upgrading.

* [Upgrade from 2.x to 3.0](#upgrade-from-2x-to-30)

---

## Upgrade from 2.x to 3.0

### Version requirements

| Requirement        | 2.x                          | 3.0                |
| ------------------ | ---------------------------- | ------------------ |
| PHP                | >= 5.6                       | **>= 8.4**         |
| Symfony (bundle)   | any (best effort)            | **>= 6.4**         |
| psr/log            | `^1.0 \|\| ^2.0 \|\| ^3.0`   | **`^3.0`**         |

### Table of contents

* [Removed collectors: Zabbix and Librato](#removed-collectors-zabbix-and-librato)
* [Renamed interfaces: `Collector` and `GaugeableCollector`](#renamed-interfaces-collector-and-gaugeablecollector)
* [Removed interfaces and classes](#removed-interfaces-and-classes)
* [`CollectorInterface` methods are strictly typed and accept `$tags`](#collectorminterface-methods-are-strictly-typed-and-accept-tags)
* [All collector classes and `Factory` are now `final`](#all-collector-classes-and-factory-are-now-final)
* [InfluxDB collector renamed to `InfluxDbV1`, new dependency](#influxdb-collector-renamed-to-influxdbv1-new-dependency)
* [Prometheus dependency changed](#prometheus-dependency-changed)
* [Factory: renamed types and options](#factory-renamed-types-and-options)
* [Symfony bundle: configuration changes](#symfony-bundle-configuration-changes)
* [Symfony bundle: service ids and aliases](#symfony-bundle-service-ids-and-aliases)
* [DoctrineDBAL: schema and stored date format](#doctrinedbal-schema-and-stored-date-format)

### Removed collectors: Zabbix and Librato

The Zabbix (`zabbix`, `zabbix_file`) and Librato (`librato`) collectors have
been removed, along with their dependencies (`okitsu/zabbix-sender`,
`kriswallsmith/buzz`) and the corresponding `Factory` types.

**Before:**

```php
$zabbix = \Beberlei\Metrics\Factory::create('zabbix', ['hostname' => 'my-host']);
$librato = \Beberlei\Metrics\Factory::create('librato', [
    'hostname' => 'my-host',
    'username' => 'foo',
    'password' => 'bar',
]);
```

**After:** there is no replacement in this library. Use a dedicated client, or
send those metrics through one of the supported backends (e.g. Telegraf or
DogStatsD can both relay metrics to third-party systems).

```yaml
# Example: replace librato with a dogstatsd collector configured on a
# telegraf/graphite relay that forwards to your SaaS provider.
beberlei_metrics:
    collectors:
        dogstatsd:
            type: dogstatsd
```

### Renamed interfaces: `Collector` and `GaugeableCollector`

The interfaces have been renamed to follow the Symfony `*Interface` naming
convention:

| 2.x                     | 3.0                              |
| ----------------------- | -------------------------------- |
| `Collector\Collector`   | `Collector\CollectorInterface`   |
| `Collector\GaugeableCollector` | `Collector\GaugeableCollectorInterface` |

**Before:**

```php
use Beberlei\Metrics\Collector\Collector;

final class MyCollector implements Collector
{
    // ...
}
```

**After:**

```php
use Beberlei\Metrics\Collector\CollectorInterface;

final class MyCollector implements CollectorInterface
{
    // ...
}
```

> If you only *consume* collectors (you never referenced the interface names),
> nothing to do except for the Symfony alias change described
> [below](#symfony-bundle-service-ids-and-aliases).

### Removed interfaces and classes

The following classes and interfaces were removed without replacement:

* `Collector\TaggableCollector`: tags are no longer set with `setTags()`.
  They are passed either in the collector constructor or per-call (see
  [below](#collectorminterface-methods-are-strictly-typed-and-accept-tags)).
* `Collector\Null`: deprecated alias of `NullCollector`. Use
  `Beberlei\Metrics\Collector\NullCollector` instead.
* `Collector\InlineTaggableGaugeableCollector` and
  `Collector\InlineTaggableGaugeableNullCollector`: all collectors supporting
  tags natively, these bridges are useless. Use `InMemory` or `NullCollector`
  directly.
* The `null_inlinetaggable` factory type was removed accordingly.

**Before:**

```php
$collector = \Beberlei\Metrics\Factory::create('null_inlinetaggable');
$collector->setTags(['dc' => 'west']);
$collector->increment('foo.bar');
```

**After:**

```php
// Tags are passed per call...
$collector = \Beberlei\Metrics\Factory::create('null');
$collector->increment('foo.bar', ['dc' => 'west']);

// ...or in the constructor when instantiating the collector yourself
new \Beberlei\Metrics\Collector\Telegraf(tags: ['dc' => 'west']);
```

### `CollectorInterface` methods are strictly typed and accept `$tags`

If you implemented `Collector\Collector` yourself, your implementation will
break. Every method now uses native type declarations, returns `void`, and
accepts an additional `array $tags = []` parameter:

```php
interface CollectorInterface
{
    public function measure(string $variable, int $value, array $tags = []): void;
    public function increment(string $variable, array $tags = []): void;
    public function decrement(string $variable, array $tags = []): void;
    public function timing(string $variable, int|float $time, array $tags = []): void;
    public function flush(): void;
}

interface GaugeableCollectorInterface
{
    public function gauge(string $variable, string|int $value, array $tags = []): void;
}
```

Notable signature changes:

* Parameters are strictly typed. `timing()` accepts integer or floating-point
  milliseconds so sub-millisecond precision is preserved.
* All methods return `void` instead of the collected value.
* `gauge()` accepts `string|int`: pass an integer to set the gauge, or a string
  starting with `+`/`-` to increment/decrement it (same behaviour as 2.x).

### All collector classes and `Factory` are now `final`

Every collector class (`StatsD`, `DogStatsD`, `Telegraf`, `Graphite`,
`InfluxDbV1`, `Prometheus`, `DoctrineDBAL`, `Logger`, `InMemory`,
`NullCollector`) as well as `Beberlei\Metrics\Factory` are now `final`.
Extending them is no longer possible: implement `CollectorInterface` instead.

Additionally, `Factory` changed from an (instantiable) `abstract` class to a
non-instantiable `final` class with a `private` constructor — it is only meant
to be used statically. Error messages raised by the factory have also been
reworded (if you matched on exception messages in tests, update them).

### InfluxDB collector renamed to `InfluxDbV1`, new dependency

The inner dependency moved from the abandoned `corley/influxdb-sdk` to
[`influxdb/influxdb-php`](https://github.com/influxdata/influxdb-php) `^1.15`
(which only supports **InfluxDB v1**, hence the rename).

* Class: `Collector\InfluxDB` → `Collector\InfluxDbV1`
* Constructor: takes an `InfluxDB\Database` instead of a (corley) `Client`.
* Factory type: `influxdb` → `influxdb_v1`
* Factory option: the `client` option is gone; pass a `database` name (plus
  optional `host`, `port`, `username`, `password`) instead.

**Before:**

```php
$client = Corley\InfluxDB\Client::fromDSN('influxdb://localhost:8086/metrics');
$collector = \Beberlei\Metrics\Factory::create('influxdb', ['client' => $client]);
```

**After:**

```php
use InfluxDB\Database;

$database = Database::fromDSN('influxdb://localhost:8086/metrics'); // InfluxDB\Database
$collector = \Beberlei\Metrics\Factory::create('influxdb_v1', ['database' => $database]);

// ... or simply let the factory build it:
$collector = \Beberlei\Metrics\Factory::create('influxdb_v1', [
    'database' => 'metrics',
    'host' => 'localhost',
    'port' => 8086,
]);
```

See the [bundle configuration changes](#symfony-bundle-configuration-changes)
for the YAML equivalent.

### Prometheus dependency changed

The inner dependency moved from the abandoned `jimdo/prometheus_client_php` to
its maintained fork [`promphp/prometheus_client_php`](https://github.com/PromPHP/prometheus_client_php)
`^2.10`. The collector API itself is unchanged: it still takes a
`Prometheus\CollectorRegistry` and a namespace.

If you use the bundle, see the [configuration changes](#symfony-bundle-configuration-changes).
If you instantiate the collector manually, nothing changes except updating the
Composer package.

### Factory: renamed types and options

Summary of every change in `\Beberlei\Metrics\Factory::create()`:

| 2.x type           | 3.0 type      | Notes                                            |
| ------------------ | ------------- | ------------------------------------------------ |
| `statsd`           | `statsd`      | unchanged                                        |
| `dogstatsd`        | `dogstatsd`   | unchanged                                        |
| `telegraf`         | `telegraf`    | unchanged                                        |
| `graphite`         | `graphite`    | unchanged                                        |
| `doctrine_dbal`    | `doctrine_dbal` | unchanged                                      |
| `logger`           | `logger`      | unchanged                                        |
| `influxdb`         | **`influxdb_v1`** | option `client` removed, use `database`       |
| `prometheus`       | `prometheus`  | unchanged (`collector_registry` + `namespace`)   |
| `null`             | `null`        | unchanged                                        |
| `zabbix`           | **removed**   |                                                  |
| `zabbix_file`      | **removed**   |                                                  |
| `librato`          | **removed**   |                                                  |
| `null_inlinetaggable` | **removed** | use `null`                                       |

Behaviour notes:

* `MetricsException` is still thrown for unknown types and invalid options, but
  error messages have been reworded.

### Symfony bundle: configuration changes

The `beberlei_metrics` configuration has been cleaned up. The following keys
were removed or renamed:

| Removed key                    | Replacement                                             |
| ------------------------------ | ------------------------------------------------------- |
| `collectors.*.type: zabbix` / `zabbix_file` | none (collector removed)                   |
| `collectors.*.type: librato`   | none (collector removed)                                |
| `collectors.*.source`          | none (was Librato-only)                                 |
| `collectors.*.file`            | none (was Zabbix-only)                                  |
| `collectors.*.influxdb_client` | `collectors.*.service` (a service id of `InfluxDB\Database`) |
| `collectors.*.prometheus_collector_registry` | `collectors.*.service` (a service id of `Prometheus\CollectorRegistry`) |
| `collectors.*.type: influxdb`  | `type: influxdb_v1`                                     |
| `collectors.*.type: null_inlinetaggable` | `type: null`                                 |

The `type` key is now validated against a strict list of allowed values:
`doctrine_dbal`, `dogstatsd`, `graphite`, `influxdb_v1`, `logger`, `memory`,
`null`, `prometheus`, `statsd`, `telegraf`.

Other behavioural changes:

* The `collectors` root key is **no longer required**. With an empty
  configuration, the bundle registers a single `null` collector.
* Configuring more than one collector without a `default` key now always throws
  an exception (previously it silently fell back in some cases).

**Before (2.x):**

```yaml
beberlei_metrics:
    default: metrics
    collectors:
        metrics:
            type: influxdb
            influxdb_client: app.influxdb_client
        prom:
            type: prometheus
            prometheus_collector_registry: app.prometheus_registry
```

**After (3.0):**

```yaml
beberlei_metrics:
    default: metrics
    collectors:
        metrics:
            type: influxdb_v1
            database: metrics
            # Or reuse your own service ("InfluxDB\Database" instance):
            # service: app.influxdb_database
        prom:
            type: prometheus
            # Optional: by default the bundle creates a registry backed by
            # a Prometheus\Storage\InMemory adapter.
            # service: app.prometheus_registry
            namespace: app_name
            tags: # optional
                dc: west
```

Tags are now injected in the collector constructor instead of calling
`setTags()` after instantiation (which required the collector to be mutable).

The bundle no longer ships a services file loaded from XML/PHP resources:
collector prototypes are registered programmatically. This is transparent for
end users.

### Symfony bundle: service ids and aliases

* Each collector is still registered as `beberlei_metrics.collector.<name>` and
  is automatically flushed on `kernel.terminate` / `console.terminate`.
* **The `beberlei_metrics.collector` alias has been removed.** Inject
  `Beberlei\Metrics\Collector\CollectorInterface` instead: it is aliased to the
  default collector.
* Each named collector gets an autowiring alias: inject a specific collector
  with the `#[Target('name-of-the-collector')]` attribute.
* All collectors are tagged with `kernel.reset` (their state is flushed on
  container reset, useful in long-running workers) and with the
  `Beberlei\Metrics\Collector\CollectorInterface` tag.

**Before (2.x):**

```php
public function __construct(
    #[\Symfony\Component\DependencyInjection\Attribute\Target('metrics')] // or explicit id
    Beberlei\Metrics\Collector\Collector $collector,
) {
}
```

**After (3.0):**

```php
use Beberlei\Metrics\Collector\CollectorInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final readonly class MyService
{
    public function __construct(
        private CollectorInterface $collector,              // default collector
        #[Target('prom')] private CollectorInterface $prom, // named collector
    ) {
    }
}
```

### DoctrineDBAL: schema and stored date format

The `created` column value changed from `Y-m-d` (date only) to
`Y-m-d H:i:s` (full datetime). If you built queries or reports on the date
granularity, adapt them (e.g. `DATE(created)`).

The `measurement` column must now accept floating-point values because
`timing()` preserves fractional milliseconds. Migrate an existing `INTEGER`
column to `DOUBLE PRECISION` (or the equivalent type for your database).
Counter measurements remain integers.

The supported `doctrine/dbal` versions are now `^3.9 || ^4.0` (2.x supported
`^2.0`). DBAL deprecations have been fixed, and a rollback failure during
`flush()` no longer masks the original exception.
