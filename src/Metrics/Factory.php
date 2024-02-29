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

use Net\Zabbix\Sender;
use Net\Zabbix\Agent\Config;
use Buzz\Browser;
use Buzz\Client\Curl;

/**
 * Static factory for Metrics Collectors.
 */
abstract class Factory
{
    /**
     * @var Buzz\Browser
     */
    private static $httpClient;

    /**
     * Create Metrics Collector Instance.
     *
     * @param string $type
     * @param array  $options
     *
     * @throws MetricsException
     *
     * @return Collector\Collector
     */
    public static function create($type, array $options = array())
    {
        switch ($type) {
            case 'statsd':
                if ((!isset($options['host']) || !isset($options['port'])) && isset($options['prefix'])) {
                    throw new MetricsException('You should specified a host and a port if you specified a prefix.');
                }
                if (!isset($options['host']) && !isset($options['port'])) {
                    return new Collector\StatsD();
                }
                if (isset($options['host']) && !isset($options['port'])) {
                    return new Collector\StatsD($options['host']);
                }
                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                $prefix = isset($options['prefix']) ? $options['prefix'] : '';

                return new Collector\StatsD($options['host'], $options['port'], $prefix);

            case 'dogstatsd':
                if ((!isset($options['host']) || !isset($options['port'])) && isset($options['prefix'])) {
                    throw new MetricsException('You should specified a host and a port if you specified a prefix.');
                }
                if (!isset($options['host']) && !isset($options['port'])) {
                    return new Collector\DogStatsD();
                }
                if (isset($options['host']) && !isset($options['port'])) {
                    return new Collector\DogStatsD($options['host']);
                }
                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                $prefix = isset($options['prefix']) ? $options['prefix'] : '';

                return new Collector\DogStatsD($options['host'], $options['port'], $prefix);

            case 'telegraf':
                if ((!isset($options['host']) || !isset($options['port'])) && isset($options['prefix'])) {
                    throw new MetricsException('You should specified a host and a port if you specified a prefix.');
                }
                if (!isset($options['host']) && !isset($options['port'])) {
                    return new Collector\Telegraf();
                }
                if (isset($options['host']) && !isset($options['port'])) {
                    return new Collector\Telegraf($options['host']);
                }
                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                $prefix = isset($options['prefix']) ? $options['prefix'] : '';

                return new Collector\Telegraf($options['host'], $options['port'], $prefix);

            case 'graphite':
                if (!isset($options['host']) && !isset($options['port'])) {
                    return new Collector\Graphite();
                }
                if (isset($options['host']) && !isset($options['port'])) {
                    return new Collector\Graphite($options['host']);
                }
                if (!isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port.');
                }

                return new Collector\Graphite($options['host'], $options['port']);

            case 'zabbix':
                if (!isset($options['hostname'])) {
                    throw new MetricsException('Hostname is required for zabbix collector.');
                }

                if (!isset($options['server']) && !isset($options['port'])) {
                    $sender = new Sender();
                } elseif (isset($options['server']) && !isset($options['port'])) {
                    $sender = new Sender($options['server']);
                } elseif (!isset($options['server']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a server if you specified a port.');
                } else {
                    $sender = new Sender($options['server'], $options['port']);
                }

                return new Collector\Zabbix($sender, $options['hostname']);

            case 'zabbix_file':
                if (!isset($options['hostname'])) {
                    throw new MetricsException('Hostname is required for zabbix collector.');
                }

                $file = isset($options['file']) ? $options['file'] : null;
                $sender = new Sender();
                $sender->importAgentConfig(new Config($file));

                return new Collector\Zabbix($sender, $options['hostname']);

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

                return new Collector\Librato(self::getHttpClient(), $options['hostname'], $options['username'], $options['password']);

            case 'doctrine_dbal':
                if (!isset($options['connection'])) {
                    throw new MetricsException('connection is required for Doctrine DBAL collector.');
                }

                return new Collector\DoctrineDBAL($options['connection']);

            case 'logger':
                if (!isset($options['logger'])) {
                    throw new MetricsException("Missing 'logger' key with logger service.");
                }

                return new Collector\Logger($options['logger']);

            case 'influxdb':
                if (!isset($options['client'])) {
                    throw new MetricsException('Missing \'client\' key for InfluxDB collector.');
                }

                return new Collector\InfluxDB($options['client']);

            case 'null':
                return new Collector\NullCollector();

            case 'null_inlinetaggable':
                return new Collector\InlineTaggableGaugeableNullCollector();

            case 'prometheus':
                if (!isset($options['collector_registry'])) {
                    throw new MetricsException('Missing \'collector_registry\' key for Prometheus collector.');
                }

                $namespace = isset($options['namespace']) ? $options['namespace'] : '';

                return new Collector\Prometheus($options['collector_registry'], $namespace);

            default:
                throw new MetricsException(sprintf('Unknown metrics collector given (%s).', $type));
        }
    }

    private static function getHttpClient()
    {
        if (self::$httpClient === null) {
            self::$httpClient = new Browser(new Curl());
        }

        return self::$httpClient;
    }
}
