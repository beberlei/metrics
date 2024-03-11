<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
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
