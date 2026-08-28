<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Beberlei\Metrics\Utils\Box;
use OpenTelemetry\API\Metrics\GaugeInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\SDK\Metrics\MeterProviderInterface as SdkMeterProviderInterface;

/**
 * Sends statistics to an OpenTelemetry Meter.
 *
 * Unlike the other collectors, measurements are recorded on the OpenTelemetry
 * instruments as soon as they happen instead of being buffered: this is how
 * the OpenTelemetry API is meant to be used, and it lets the SDK's own metric
 * readers decide when and how the data is actually exported. `flush()` only
 * triggers `MeterProviderInterface::forceFlush()`, when the given provider
 * supports it, so that anything still buffered by an exporter is sent before
 * a short-lived PHP process ends.
 */
final class OpenTelemetry implements CollectorInterface, GaugeableCollectorInterface
{
    private readonly MeterInterface $meter;

    /** @var array<string, UpDownCounterInterface> */
    private array $upDownCounters = [];

    /** @var array<string, HistogramInterface> */
    private array $histograms = [];

    /** @var array<string, GaugeInterface> */
    private array $gauges = [];

    /** @var array<string, int> */
    private array $gaugeValues = [];

    public function __construct(
        private readonly MeterProviderInterface $meterProvider,
        private readonly string $name = 'beberlei/metrics',
        /** @var array<string, mixed> */
        private readonly array $tags = [],
    ) {
        $this->meter = $meterProvider->getMeter($name);
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        Box::box(fn () => $this->upDownCounter($variable)->add($value, $this->attributes($tags)));
    }

    public function increment(string $variable, array $tags = []): void
    {
        Box::box(fn () => $this->upDownCounter($variable)->add(1, $this->attributes($tags)));
    }

    public function decrement(string $variable, array $tags = []): void
    {
        Box::box(fn () => $this->upDownCounter($variable)->add(-1, $this->attributes($tags)));
    }

    public function timing(string $variable, int|float $time, array $tags = []): void
    {
        Box::box(fn () => $this->histogram($variable)->record($time, $this->attributes($tags)));
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        if (\is_int($value)) {
            $this->gaugeValues[$variable] = $value;
        } else {
            $sign = substr($value, 0, 1);
            if (!\in_array($sign, ['-', '+'], true)) {
                throw new \InvalidArgumentException('Gauge value must be an integer or a string starting with + or -.');
            }
            $this->gaugeValues[$variable] ??= 0;
            $this->gaugeValues[$variable] += (int) $value;
        }

        Box::box(fn () => $this->gaugeInstrument($variable)->record($this->gaugeValues[$variable], $this->attributes($tags)));
    }

    public function flush(): void
    {
        Box::box(function (): void {
            if ($this->meterProvider instanceof SdkMeterProviderInterface) {
                $this->meterProvider->forceFlush();
            }
        });
    }

    /**
     * @param array<string, mixed> $tags
     *
     * @return array<non-empty-string, array<array-key, mixed>|bool|float|int|string|null>
     */
    private function attributes(array $tags): array
    {
        $attributes = [];

        foreach ($this->tags + $tags as $key => $value) {
            if ('' === $key) {
                continue;
            }

            $attributes[$key] = match (true) {
                \is_scalar($value), \is_array($value), null === $value => $value,
                $value instanceof \BackedEnum => $value->value,
                $value instanceof \UnitEnum => $value->name,
                default => (string) $value,
            };
        }

        return $attributes;
    }

    private function upDownCounter(string $variable): UpDownCounterInterface
    {
        return $this->upDownCounters[$variable] ??= $this->meter->createUpDownCounter($variable);
    }

    private function histogram(string $variable): HistogramInterface
    {
        return $this->histograms[$variable] ??= $this->meter->createHistogram($variable, 'ms');
    }

    private function gaugeInstrument(string $variable): GaugeInterface
    {
        return $this->gauges[$variable] ??= $this->meter->createGauge($variable);
    }
}
