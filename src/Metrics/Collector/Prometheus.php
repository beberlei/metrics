<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Prometheus\CollectorRegistry;

class Prometheus implements CollectorInterface, GaugeableCollectorInterface
{
    private array $counters = [];
    private array $gauges = [];

    public function __construct(
        private readonly CollectorRegistry $registry,
        private readonly string $namespace = '',
        private readonly array $tags = [],
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->gauge($variable, $value, $tags);
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->counters[] = ['variable' => $variable, 'value' => 1, 'tags' => $tags];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->counters[] = ['variable' => $variable, 'value' => -1, 'tags' => $tags];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->gauge($variable, $time, $tags);
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        $this->gauges[] = ['variable' => $variable, 'value' => $value, 'tags' => $tags];
    }

    public function flush(): void
    {
        try {
            foreach ($this->counters as $counter) {
                $variable = $this->normalizeVariable($counter['variable']);
                $labels = $this->tags + $counter['tags'];
                $this
                    ->registry
                    ->getOrRegisterCounter($this->namespace, $variable, '', $labels)
                    ->incBy($counter['value'], $labels)
                ;
            }

            foreach ($this->gauges as $gauge) {
                $variable = $this->normalizeVariable($gauge['variable']);
                $labels = $this->tags + $gauge['tags'];
                $this
                    ->registry
                    ->getOrRegisterGauge($this->namespace, $variable, '', $labels)
                    ->set($gauge['value'], $labels)
                ;
            }
        } catch (\Exception) {
        }

        $this->counters = $this->gauges = [];
    }

    private function normalizeVariable(string $variable): string
    {
        return str_replace(['.', ':'], ['_', '_'], $variable);
    }
}
