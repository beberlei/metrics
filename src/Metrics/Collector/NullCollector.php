<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

class NullCollector implements CollectorInterface, GaugeableCollectorInterface, TaggableCollectorInterface
{
    public function increment(string $variable, array $tags = []): void
    {
    }

    public function decrement(string $variable, array $tags = []): void
    {
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
    }

    public function flush(): void
    {
    }

    public function setTags(array $tags): void
    {
    }
}
