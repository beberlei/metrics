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

    public function gauge(string $variable, int $value, array $tags = []): void
    {
    }

    public function flush(): void
    {
    }

    public function setTags(array $tags): void
    {
    }
}
