<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

/**
 * Dispatches every call to a list of collectors.
 */
final class Chain implements CollectorInterface, GaugeableCollectorInterface
{
    /** @var list<CollectorInterface> */
    private readonly array $collectors;

    public function __construct(CollectorInterface ...$collectors)
    {
        $this->collectors = array_values($collectors);
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->dispatch(static fn (CollectorInterface $collector) => $collector->measure($variable, $value, $tags));
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->dispatch(static fn (CollectorInterface $collector) => $collector->increment($variable, $tags));
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->dispatch(static fn (CollectorInterface $collector) => $collector->decrement($variable, $tags));
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->dispatch(static fn (CollectorInterface $collector) => $collector->timing($variable, $time, $tags));
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        $this->dispatch(static function (CollectorInterface $collector) use ($variable, $value, $tags): void {
            if ($collector instanceof GaugeableCollectorInterface) {
                $collector->gauge($variable, $value, $tags);
            }
        });
    }

    public function flush(): void
    {
        $this->dispatch(static fn (CollectorInterface $collector) => $collector->flush());
    }

    private function dispatch(\Closure $call): void
    {
        foreach ($this->collectors as $collector) {
            try {
                $call($collector);
            } catch (\Exception) {
            }
        }
    }
}
