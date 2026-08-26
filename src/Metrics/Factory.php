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
use Beberlei\Metrics\Collector\InfluxDbV1;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;
use Beberlei\Metrics\Collector\Telegraf;

final class Factory
{
    private const SOCKET_DEFAULTS = ['host' => 'localhost', 'port' => 8125];

    private function __construct()
    {
    }

    /**
     * Creates a collector from its type and a set of options.
     *
     * @param array<string, mixed> $options
     *
     * @throws MetricsException when the type or the options are invalid
     */
    public static function create(string $type, array $options = []): CollectorInterface
    {
        return match ($type) {
            'statsd' => new StatsD(...self::socketArguments($options, 'prefix')),
            'dogstatsd' => new DogStatsD(...self::socketArguments($options, 'prefix')),
            'telegraf' => new Telegraf(...self::socketArguments($options, 'prefix')),
            'graphite' => new Graphite(...self::socketArguments($options)),
            'doctrine_dbal' => new DoctrineDBAL(
                $options['connection'] ?? throw new MetricsException('The "connection" option is required for the Doctrine DBAL collector.'),
            ),
            'logger' => new Logger(
                $options['logger'] ?? throw new MetricsException('The "logger" option is required for the Logger collector.'),
            ),
            'influxdb_v1' => new InfluxDbV1(
                $options['database'] ?? throw new MetricsException('The "database" option is required for the InfluxDbV1 collector.'),
            ),
            'null' => new NullCollector(),
            'prometheus' => new Prometheus(
                $options['collector_registry'] ?? throw new MetricsException('The "collector_registry" option is required for the Prometheus collector.'),
                $options['namespace'] ?? '',
            ),
            default => throw new MetricsException(\sprintf('Unknown metrics collector given (%s).', $type)),
        };
    }

    /**
     * Validates the "host"/"port"/optional third argument combination and
     * returns the arguments expected by socket based collectors constructors.
     *
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private static function socketArguments(array $options, ?string $thirdArgument = null): array
    {
        $arguments = [
            'host' => $options['host'] ?? self::SOCKET_DEFAULTS['host'],
            'port' => $options['port'] ?? self::SOCKET_DEFAULTS['port'],
        ];

        if ($thirdArgument && \array_key_exists($thirdArgument, $options) && (!\array_key_exists('host', $options) || !\array_key_exists('port', $options))) {
            throw new MetricsException(\sprintf('You must specify a host and a port if you specify a %s.', $thirdArgument));
        }

        if (\array_key_exists('port', $options) && !\array_key_exists('host', $options)) {
            throw new MetricsException('You must specify a host if you specify a port.');
        }

        if ($thirdArgument) {
            $arguments[$thirdArgument] = $options[$thirdArgument] ?? '';
        }

        return $arguments;
    }
}
