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
    /** @var  int[] */
    private $data = [];

    /**
     * Updates a counter by some arbitrary amount.
     *
     * @param string $variable
     * @param int $value The amount to increment the counter by
     */
    public function measure($variable, $value)
    {
        if (!isset($this->data[$variable])) {
            $this->data[$variable] = 0;
        }
        $this->data[$variable] += $value;
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
     * @param int $time The duration of the timing in milliseconds
     */
    public function timing($variable, $time)
    {
        $this->gauge($variable, $time);
    }

    /**
     * Sends the metrics to the adapter backend.
     */
    public function flush()
    {
        // TODO: Implement flush() method.
    }

    /**
     * Updates a gauge by an arbitrary amount.
     *
     * @param string $variable
     * @param int $value
     */
    public function gauge($variable, $value)
    {
        if (!isset($this->data[$variable])) {
            $this->data[$variable] = 0;
        }
        $this->data[$variable] = $value;
    }

    /**
     * Returns current value of variable
     *
     * @param string $variable
     * @return int
     */
    public function get($variable)
    {
        return $this->data[$variable];
    }
}
