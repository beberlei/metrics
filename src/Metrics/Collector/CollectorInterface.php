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

interface CollectorInterface
{
    /**
     * Updates a counter by some arbitrary amount.
     */
    public function measure(string $variable, int $value, array $tags = []): void;

    /**
     * Increments a counter.
     */
    public function increment(string $variable, array $tags = []): void;

    /**
     * Decrements a counter.
     */
    public function decrement(string $variable, array $tags = []): void;

    /**
     * Records a timing.
     *
     * @param int $time The duration of the timing in milliseconds
     */
    public function timing(string $variable, int $time, array $tags = []): void;

    /**
     * Sends the metrics to the adapter backend.
     */
    public function flush(): void;
}
