<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Metrics;

use Beberlei\Metrics\Collector\Chain;
use Beberlei\Metrics\Collector\DoctrineDBAL;
use Beberlei\Metrics\Collector\DogStatsD;
use Beberlei\Metrics\Collector\Graphite;
use Beberlei\Metrics\Collector\InfluxDbV1;
use Beberlei\Metrics\Collector\InMemory;
use Beberlei\Metrics\Collector\Logger;
use Beberlei\Metrics\Collector\NullCollector;
use Beberlei\Metrics\Collector\OpenTelemetry;
use Beberlei\Metrics\Collector\Prometheus;
use Beberlei\Metrics\Collector\StatsD;

/**
 * Explains, for the demo homepage, what each collector configured in
 * config/packages/metrics.yaml actually does, and which Grafana dashboard
 * (if any) shows the data it produces.
 *
 * The keys match the collector names used in config/packages/metrics.yaml,
 * which are also the "beberlei_metrics.collector.<name>" service ids.
 *
 * @phpstan-type Processor array{label: string, class: class-string, description: string, gaugeable: bool, dashboardUid: string|null}
 */
final class ProcessorCatalog
{
    /** @var array<string, Processor> */
    private const PROCESSORS = [
        'dbal' => [
            'label' => 'Doctrine DBAL',
            'class' => DoctrineDBAL::class,
            'description' => 'Inserts one row per metric into the "metrics" table of the PostgreSQL database.',
            'gaugeable' => false,
            'dashboardUid' => 'pg-metrics',
        ],
        'prometheus' => [
            'label' => 'Prometheus',
            'class' => Prometheus::class,
            'description' => 'Registers counters and gauges in a Prometheus registry, scraped from /prometheus every 15 seconds.',
            'gaugeable' => true,
            'dashboardUid' => 'prom-metrics',
        ],
        'otel' => [
            'label' => 'OpenTelemetry',
            'class' => OpenTelemetry::class,
            'description' => 'Records on an OpenTelemetry Meter, exported as OTLP straight to the same Prometheus instance.',
            'gaugeable' => true,
            'dashboardUid' => 'prom-metrics',
        ],
        'graphite' => [
            'label' => 'Graphite',
            'class' => Graphite::class,
            'description' => 'Pushes raw values directly to a Graphite/Carbon server over TCP, unprefixed.',
            'gaugeable' => false,
            'dashboardUid' => 'graphite-metrics',
        ],
        'statsd' => [
            'label' => 'StatsD',
            'class' => StatsD::class,
            'description' => 'Sends UDP packets to a StatsD daemon (bundled with the Graphite container here), prefixed with "app.statsd.".',
            'gaugeable' => true,
            'dashboardUid' => 'graphite-metrics',
        ],
        'dogstatsd' => [
            'label' => 'DogStatsD',
            'class' => DogStatsD::class,
            'description' => 'Same as StatsD, but supports Datadog-style tags in the UDP payload. Prefixed with "app.dogstatsd.".',
            'gaugeable' => true,
            'dashboardUid' => 'graphite-metrics',
        ],
        'chain' => [
            'label' => 'Chain',
            'class' => Chain::class,
            'description' => 'Dispatches every call to several other collectors at once. Configured here to fan out to "statsd" and "dogstatsd".',
            'gaugeable' => true,
            'dashboardUid' => 'graphite-metrics',
        ],
        'influxdb_v1' => [
            'label' => 'InfluxDB (v1)',
            'class' => InfluxDbV1::class,
            'description' => 'Writes points to an InfluxDB 1.x time-series database, one measurement per metric name.',
            'gaugeable' => false,
            'dashboardUid' => 'influxdb-metrics',
        ],
        'logger' => [
            'label' => 'Logger',
            'class' => Logger::class,
            'description' => 'Writes each metric as a PSR-3 debug log line. No external system, useful for local debugging.',
            'gaugeable' => true,
            'dashboardUid' => null,
        ],
        'memory' => [
            'label' => 'In-memory',
            'class' => InMemory::class,
            'description' => 'Keeps metrics in a PHP array for the current request only. This is what renders the numbers below.',
            'gaugeable' => true,
            'dashboardUid' => null,
        ],
        'null' => [
            'label' => 'Null',
            'class' => NullCollector::class,
            'description' => 'Discards everything. Useful as a safe default or in tests.',
            'gaugeable' => true,
            'dashboardUid' => null,
        ],
    ];

    private function __construct()
    {
    }

    /**
     * @return array<string, Processor>
     */
    public static function all(): array
    {
        return self::PROCESSORS;
    }

    /**
     * @return Processor
     */
    public static function get(string $name): array
    {
        return self::PROCESSORS[$name] ?? throw new \InvalidArgumentException(\sprintf('Unknown processor "%s".', $name));
    }
}
