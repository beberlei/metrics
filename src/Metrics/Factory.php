<?php
/**
 * Beberlei Metrics.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace Beberlei\Metrics;

use Beberlei\Metrics\Collector\CollectorInterface;
use Beberlei\Metrics\Collector\DoctrineDBAL;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\InfluxDB;
use Beberlei\Metrics\Collector\Librato;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\Telegraf;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class Factory
{
    private static HttpClientInterface $httpClient;

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

            case 'librato':
                if (!isset($options['hostname'])) {
                    throw new MetricsException('Hostname is required for librato collector.');
                }

                if (!isset($options['username'])) {
                    throw new MetricsException('No username given for librato collector.');
                }

                if (!isset($options['password'])) {
                    throw new MetricsException('No password given for librato collector.');
                }

                return new Librato(self::getHttpClient(), $options['hostname'], $options['username'], $options['password']);

            case 'doctrine_dbal':
                if (!isset($options['connection'])) {
                    throw new MetricsException('connection is required for Doctrine DBAL collector.');
                }

                return new DoctrineDBAL($options['connection']);

            case 'logger':
                if (!isset($options['logger'])) {
                    throw new MetricsException("Missing 'logger' key with logger service.");
                }

                return new Logger($options['logger']);

            case 'influxdb':
                if (!isset($options['client'])) {
                    throw new MetricsException("Missing 'client' key for InfluxDB collector.");
                }

                return new InfluxDB($options['client']);

            case 'null':
                return new NullCollector();

            case 'prometheus':
                if (!isset($options['collector_registry'])) {
                    throw new MetricsException("Missing 'collector_registry' key for Prometheus collector.");
                }

                $namespace = $options['namespace'] ?? '';

                return new Prometheus($options['collector_registry'], $namespace);

            default:
                throw new MetricsException(sprintf('Unknown metrics collector given (%s).', $type));
        }
    }

    private static function getHttpClient(): HttpClientInterface
    {
        return self::$httpClient ??= HttpClient::create();
    }
}
