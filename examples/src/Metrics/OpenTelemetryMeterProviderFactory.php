<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace App\Metrics;

use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\Contrib\Otlp\MetricExporterFactory;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Attributes\ServiceAttributes;

/**
 * Builds the MeterProviderInterface used by the "opentelemetry" collector.
 *
 * The exporter is configured through the standard OTEL_EXPORTER_OTLP_*
 * environment variables (see .env): it sends OTLP/HTTP straight to
 * Prometheus's own OTLP receiver, so the metrics show up in the same
 * Prometheus dashboard as the "prometheus" collector, without needing a
 * separate OpenTelemetry Collector container.
 *
 * ExportingReader (rather than the more common PeriodicExportingMetricReader)
 * only exports when explicitly told to, which fits PHP's request-response
 * lifecycle: the "opentelemetry" collector's flush() calls
 * MeterProviderInterface::forceFlush() at the end of each request instead of
 * relying on a background timer.
 *
 * Unlike the "prometheus" collector, OTLP-ingested metrics keep their raw
 * name (no "app_" prefix): setting service.name is what lets the Grafana
 * dashboard tell them apart, since Prometheus's OTLP receiver turns it into
 * a "job" label on every sample.
 */
final class OpenTelemetryMeterProviderFactory
{
    public static function create(): MeterProviderInterface
    {
        $exporter = new MetricExporterFactory()->create();
        $reader = new ExportingReader($exporter);
        $resource = ResourceInfo::create(Attributes::create([
            ServiceAttributes::SERVICE_NAME => 'beberlei-metrics-demo',
        ]));

        return new MeterProviderBuilder()
            ->addReader($reader)
            ->setResource($resource)
            ->build()
        ;
    }
}
