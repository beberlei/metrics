<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

interface GaugeableCollectorInterface
{
    /**
     * Updates a gauge by an arbitrary amount.
     */
    public function gauge(string $variable, string|int $value, array $tags = []): void;
}
