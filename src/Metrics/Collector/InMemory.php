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
        if (!isset($this->timingData[$variable])) {
            $this->timingData[$variable] = 0;
        }

        $this->timingData[$variable] = $time;
    }

    public function gauge(string $variable, int $value, array $tags = []): void
    {
        $this->gaugeData[$variable] = $value;
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
