<?php
/**
 * Beberlei Metrics
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
     * Create Metrics Collector Instance
     *
     * @param string $type
     * @param array $options
     * @return \Beberlei\Metrics\Collector\Collector
     */
    static public function create($type, array $options = array())
    {
        switch($type) {
            case 'statsd':
                $host = isset($options['host']) ? $options['host'] : 'localhost';
                $port = isset($options['port']) ? $options['port'] : 8125;

                return new Collector\StatsD($host, $port);

            case 'graphite':
                if ( ! isset($options['host']) && ! isset($options['port'])) {
                    return new Collector\Graphite();
                } elseif ( isset($options['host']) && ! isset($options['port'])) {
                    return new Collector\Graphite($options['host']);
                } elseif ( ! isset($options['host']) && isset($options['port'])) {
                    throw new MetricsException('You should specified a host if you specified a port');
                }

                return new Collector\Graphite($options['host'], $options['port']);

            case 'zabbix':
                if ( ! isset($options['hostname'])) {
                    throw new MetricsException('Hostname is required for zabbix collector.');
                }

                $host = isset($options['host']) ? $options['host'] : null;
                $port = isset($options['port']) ? $options['port'] : null;
                $sender = new Sender($host, $port);

                return new Collector\Zabbix($sender, $options['hostname']);

            case 'zabbix_file':
                if ( ! isset($options['hostname'])) {
                    throw new MetricsException('Hostname is required for zabbix collector.');
                }

                $file = isset($options['file']) ? $options['file'] : null;
                $sender = new Sender();
                $sender->importAgentConfig(new Config($file));

                return new Collector\Zabbix($sender, $options['hostname']);

            case 'librato':
                if ( ! isset($options['hostname'])) {
                    throw new MetricsException('Hostname is required for librato collector.');
                }

                if ( ! isset($options['username'])) {
                    throw new MetricsException("No username given for librato collector.");
                }

                if ( ! isset($options['password'])) {
                    throw new MetricsException("No password given for librato collector.");
                }

                return new Collector\Librato(
                    self::getHttpClient(),
                    $options['hostname'],
                    $options['username'],
                    $options['password']
                );

            case 'doctrine_dbal':
                if ( ! isset($options['connection'])) {
                    throw new MetricsException('connection is required for Doctrine DBAL collector.');
                }

                return new Collector\DoctrineDBAL($options['connection']);

            case 'monolog':
                if ( ! isset($options['logger'])) {
                    throw new MetricsException("Missing 'logger' key with monolog service");
                }

                return new Collector\Monolog($options['logger']);

            case 'null':
                return new Collector\Null();

            default:
                throw new MetricsException('Unknown metrics collector given.');
        }
    }

    static public function getHttpClient()
    {
        if (self::$httpClient === null) {
            self::$httpClient = new Browser(new Curl);
        }

        return self::$httpClient;
    }
}

