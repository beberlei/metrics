Upgrade from 1.x to 2.0
=======================

* [BC BREAK] Collector `Beberlei\Metrics\Collector\Monolog` is renamed to `Beberlei\Metrics\Collector\Logger`.

* [BC BREAK] Collector `Beberlei\Metrics\Collector\Monolog` takes a `Psr\Log\LoggerInterface`.

* [BC BREAK] The bundle does not rely on `Beberlei\Metrics\Factory` and `Beberlei\Metrics\Registry` anymore.

* [BC BREAK] The bundle configuration has a new "standard" key mapping. The
`hostname` key for `Librato` is now `source`. The `hostname` key for `Zabbix` is
now `prefix`. `hostname`, and `servername` are now `host`. `serverport` is now
`port`.

* [BC BREAK] The `Registry` is removed.

* [BC BREAK] The `functions.php` is removed.
