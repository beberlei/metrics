# CHANGELOG

## v3.0.0 (unreleased)

### Breaking changes

* Drop support for PHP < 8.1
* Drop support for Symfony < 5.4
* Drop support for zabbix collector
* Drop support for librato collector
* Rename InfluxDB collector to InfluxDbV1
* Change inner dependency of InfluxDbV1
* Change inner dependency of Prometheus
* Remove the TaggableCollectorInterface. Tags can be injected in the constructor
  instead

### New features

* collector:
  * Ensure all collectors cannot raise error or exception
* bundle:
  * All collectors has alias for autowiring. Use
    `#[Target('name-of-the-collector')]` to inject a collector
  * All collectors are tagged with `kernel.reset` to reset their state
  * All collectors are tagged with
    `Beberlei\Metrics\Collector\CollectorInterface`
* add a symfony application in the `examples` folder will all collectors enabled
  and visualisation with Grafana

### Minor changes

* collector:
    * Fix doctrine dbal deprecations
* chore:
    * modernise PHP code, use PHP 8.1 features
    * add license file, and link it in each PHP files
* ci:
    * use symfony/phpunit-bridge instead of phpunit
    * add php-cs-fixer
    * add phpstan
    * replace Travis by GitHub Actions
* composer:
    *  move tests to it's own folder, and it's own autoloader
