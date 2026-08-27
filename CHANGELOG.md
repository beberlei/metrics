# CHANGELOG

## v3.0.0 (unreleased)

### Breaking changes

> Every breaking change is documented in detail — with before/after examples —
> in the [UPGRADE.md](UPGRADE.md) guide.

* Drop support for PHP < 8.4
* Drop support for Symfony < 6.4
* Require `psr/log ^3.0`
* Rename `Beberlei\Metrics\Collector\Collector` to
  `Beberlei\Metrics\Collector\CollectorInterface`
* Rename `Beberlei\Metrics\Collector\GaugeableCollector` to
  `Beberlei\Metrics\Collector\GaugeableCollectorInterface`
* Remove the TaggableCollectorInterface. Tags can be injected in the constructor
  or passed per call instead (`setTags()` is gone)
* Remove the deprecated `Null` collector class (use `NullCollector`)
* Remove `InlineTaggableGaugeableCollector`,
  `InlineTaggableGaugeableNullCollector` and the `null_inlinetaggable` factory
  type (use `InMemory` / `NullCollector`, they support tags natively)
* All methods of `CollectorInterface` are now strictly typed, return `void` and
  accept an additional `$tags` argument (BC break for custom implementations)
* Remove the `beberlei_metrics.collector` service alias (inject
  `CollectorInterface` or use `#[Target('name')]` instead)
* DoctrineDBAL: store a full datetime (`Y-m-d H:i:s`) in the `created` column
  instead of a date only (`Y-m-d`)
* Drop support for zabbix collector
* Drop support for librato collector
* Rename InfluxDB collector to InfluxDbV1
* Change inner dependency of InfluxDbV1
* Change inner dependency of Prometheus
* All collector classes are now `final`
* `Factory` is now `final` and error messages have been reworded
* Remove the unused `prometheus_collector_registry` bundle configuration option
  (use `service` instead)
* The bundle no longer ships an XML services file: the collector prototypes are
  registered programmatically (this is transparent for end users)

### New features

* collector:
  * Ensure all collectors cannot raise error or exception
  * Add a `Chain` collector, dispatching every call to a list of collectors
* bundle:
  * All collectors has alias for autowiring. Use
    `#[Target('name-of-the-collector')]` to inject a collector
  * All collectors are tagged with `kernel.reset` to reset their state
  * All collectors are tagged with
    `Beberlei\Metrics\Collector\CollectorInterface`
  * Add support for the `chain` collector type, referencing other collectors
    by name through the `collectors` option
* add a symfony application in the `examples` folder will all collectors enabled
  and visualisation with Grafana

### Minor changes

* collector:
    * Fix doctrine dbal deprecations
    * DoctrineDBAL: do not raise an exception on rollback failure during flush
* chore:
    * modernise PHP code, use PHP 8.4 features
    * add license file, and link it in each PHP files
* ci:
    * use phpunit instead of symfony/phpunit-bridge
    * add php-cs-fixer
    * add phpstan
    * replace Travis by GitHub Actions
    * run the example application in CI (`castor start` + functional tests)
* composer:
    *  move tests to it's own folder, and it's own autoloader

