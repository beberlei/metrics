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
 * Collector interface.
 */
interface Collector
{
    /**
     * Updates a counter by some arbitrary amount.
     *
     * @param string $variable
     * @param int    $value    The amount to increment the counter by
     */
    public function measure($variable, $value);

    /**
     * Increments a counter.
     *
     * @param string $variable
     */
    public function increment($variable);

    /**
     * Decrements a counter.
     *
     * @param string $variable
     */
    public function decrement($variable);

    /**
     * Records a timing.
     *
     * @param string $variable
     * @param int    $time     The duration of the timing in milliseconds
     */
    public function timing($variable, $time);

    /**
     * Sends the metrics to the adapter backend.
     */
    public function flush();
}
