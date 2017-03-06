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
class InMemory implements Collector, GaugeableCollector
{
    /** @var int[] */
    private $incrementData = [];
    /** @var int[] */
    private $gaugeData = [];
    /** @var int[] */
    private $timingData = [];

    /**
     * Updates a counter by some arbitrary amount.
     *
     * @param string $variable
     * @param int    $value    The amount to increment the counter by
     */
    public function measure($variable, $value)
    {
        if (!isset($this->incrementData[$variable])) {
            $this->incrementData[$variable] = 0;
        }
        $this->incrementData[$variable] += $value;
    }

    /**
     * Increments a counter.
     *
     * @param string $variable
     */
    public function increment($variable)
    {
        $this->measure($variable, 1);
    }

    /**
     * Decrements a counter.
     *
     * @param string $variable
     */
    public function decrement($variable)
    {
        $this->measure($variable, -1);
    }

    /**
     * Records a timing.
     *
     * @param string $variable
     * @param int    $time     The duration of the timing in milliseconds
     */
    public function timing($variable, $time)
    {
        if (!isset($this->timingData[$variable])) {
            $this->timingData[$variable] = 0;
        }
        $this->timingData[$variable] = $time;
    }

    /**
     * Sends the metrics to the adapter backend.
     */
    public function flush()
    {
        $this->timingData = [];
        $this->gaugeData = [];
        $this->incrementData = [];
    }

    /**
     * Updates a gauge by an arbitrary amount.
     *
     * @param string $variable
     * @param int    $value
     */
    public function gauge($variable, $value)
    {
        $sign = substr($value, 0, 1);

        if (in_array($sign, ['-', '+'])) {
            $this->gaugeIncrement($variable, (int) $value);

            return;
        }

        $this->gaugeData[$variable] = $value;
    }

    /**
     * Returns current value of incremented/decremented/measured variable.
     *
     * @param string $variable
     *
     * @return int
     */
    public function getMeasure($variable)
    {
        return isset($this->incrementData[$variable]) ? $this->incrementData[$variable] : 0;
    }

    /**
     * Returns current value of gauged variable.
     *
     * @param string $variable
     *
     * @return int
     */
    public function getGauge($variable)
    {
        return isset($this->gaugeData[$variable]) ? $this->gaugeData[$variable] : 0;
    }

    /**
     * Returns current value of timed variable.
     *
     * @param string $variable
     *
     * @return int
     */
    public function getTiming($variable)
    {
        return isset($this->timingData[$variable]) ? $this->timingData[$variable] : 0;
    }

    /**
     * @param string $variable
     * @param int    $value
     */
    private function gaugeIncrement($variable, $value)
    {
        if (!isset($this->gaugeData[$variable])) {
            $this->gaugeData[$variable] = 0;
        }

        $this->gaugeData[$variable] += $value;
    }
}
