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
 * InlineTaggableGaugeableCollector interface.
 */
interface InlineTaggableGaugeableCollector
{
    /**
     * Updates a counter by some arbitrary amount.
     *
     * @param string $variable
     * @param int    $value    The amount to increment the counter by
     * @param array  $tags     Tags to be attached to the metric
     *
     * @return
     */
    public function measure($variable, $value, $tags = array());

    /**
     * Increments a counter.
     *
     * @param string $variable
     * @param array  $tags     Tags to be attached to the metric
     */
    public function increment($variable, $tags = array());

    /**
     * Decrements a counter.
     *
     * @param string $variable
     * @param array  $tags     Tags to be attached to the metric
     */
    public function decrement($variable, $tags = array());

    /**
     * Records a timing.
     *
     * @param string $variable
     * @param int    $time     The duration of the timing in milliseconds
     * @param array  $tags     Tags to be attached to the metric
     */
    public function timing($variable, $time, $tags = array());

    /**
     * Updates a gauge by an arbitrary amount.
     *
     * @param string $variable
     * @param int    $value
     * @param array  $tags     Tags to be attached to the metric
     */
    public function gauge($variable, $value, $tags = array());
}
