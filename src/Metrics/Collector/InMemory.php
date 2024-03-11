<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

/**
 * Stores metrics in memory.
 * Useful for testing and with any custom persistence mechanisms.
 */
class InMemory implements CollectorInterface, GaugeableCollectorInterface
{
    /** @var int[] */
    private array $incrementData = [];

    /** @var int[] */
    private array $gaugeData = [];

    /** @var int[] */
    private array $timingData = [];

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->incrementData[$variable] ??= 0;
        $this->incrementData[$variable] += $value;
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->measure($variable, 1);
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->measure($variable, -1);
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->timingData[$variable] ??= 0;
        $this->timingData[$variable] = $time;
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        if (\is_int($value)) {
            $this->gaugeData[$variable] = $value;

            return;
        }

        $sign = substr($value, 0, 1);
        if (!\in_array($sign, ['-', '+'], true)) {
            throw new \InvalidArgumentException('Gauge value must be an integer or a string starting with + or -.');
        }
        $this->gaugeData[$variable] ??= 0;
        $this->gaugeData[$variable] += (int) $value;
    }

    public function flush(): void
    {
        $this->timingData = [];
        $this->gaugeData = [];
        $this->incrementData = [];
    }

    public function getMeasure(string $variable): int
    {
        return $this->incrementData[$variable] ?? 0;
    }

    public function getGauge(string $variable): int
    {
        return $this->gaugeData[$variable] ?? 0;
    }

    public function getTiming(string $variable): int
    {
        return $this->timingData[$variable] ?? 0;
    }
}
