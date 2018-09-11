Upgrade from 2.x to 3.0
=======================

* [BC BREAK] Collector `Beberlei\Metrics\Collector\InfluxDB` now uses the official InfluxDB php library. Configuration parameters have changed. InfluxDB now uses the official influxdb-php client instead of corley client. An instance of \InfluxDB\Database must be used instead of \InfluxDB\Client.

