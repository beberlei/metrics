<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics;

use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\DoctrineDBAL;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\InfluxDB;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\Telegraf;

abstract class Factory
{
    /**
     * @throws MetricsException
     */
    public static function create(string $type, array $options = []): CollectorInterface
    {
        switch ($type) {
            case 'statsd':
                if ((!isset($options['host']) || !isset($options['port'])) && isset($options['prefix'])) {
                    throw new MetricsException('You should specified a host and a port if you specified a prefix.');
                }

                if (!isset($options['host']) && !isset($options['port'])) {
                    return new StatsD();
                }

                if (isset($options['host']) && !isset($options['port'])) {
                    return new StatsD($options['host']);
                }

                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                $prefix = $options['prefix'] ?? '';

                return new StatsD($options['host'], $options['port'], $prefix);

            case 'dogstatsd':
                if ((!isset($options['host']) || !isset($options['port'])) && isset($options['prefix'])) {
                    throw new MetricsException('You should specified a host and a port if you specified a prefix.');
                }

                if (!isset($options['host']) && !isset($options['port'])) {
                    return new DogStatsD();
                }

                if (isset($options['host']) && !isset($options['port'])) {
                    return new DogStatsD($options['host']);
                }

                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                $prefix = $options['prefix'] ?? '';

                return new DogStatsD($options['host'], $options['port'], $prefix);

            case 'telegraf':
                if ((!isset($options['host']) || !isset($options['port'])) && isset($options['prefix'])) {
                    throw new MetricsException('You should specified a host and a port if you specified a prefix.');
                }

                if (!isset($options['host']) && !isset($options['port'])) {
                    return new Telegraf();
                }

                if (isset($options['host']) && !isset($options['port'])) {
                    return new Telegraf($options['host']);
                }

                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                $prefix = $options['prefix'] ?? '';

                return new Telegraf($options['host'], $options['port'], $prefix);

            case 'graphite':
                if (!isset($options['host']) && !isset($options['port'])) {
                    return new Graphite();
                }

                if (isset($options['host']) && !isset($options['port'])) {
                    return new Graphite($options['host']);
                }

                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                return new Graphite($options['host'], $options['port']);

            case 'doctrine_dbal':
                if (!isset($options['connection'])) {
                    throw new MetricsException('connection is required for Doctrine DBAL collector.');
                }

                return new DoctrineDBAL($options['connection']);

            case 'logger':
                if (!isset($options['logger'])) {
                    throw new MetricsException('Missing "logger" key with logger service.');
                }

                return new Logger($options['logger']);

            case 'influxdb':
                if (!isset($options['database'])) {
                    throw new MetricsException('Missing "database" key for InfluxDB collector.');
                }

                return new InfluxDB($options['database']);

            case 'null':
                return new NullCollector();

            case 'prometheus':
                if (!isset($options['collector_registry'])) {
                    throw new MetricsException('Missing "collector_registry" key for Prometheus collector.');
                }

                $namespace = $options['namespace'] ?? '';

                return new Prometheus($options['collector_registry'], $namespace);

            default:
                throw new MetricsException(sprintf('Unknown metrics collector given (%s).', $type));
        }
    }
}
