<?php

namespace Beberlei\Metrics\Collector;

class TaggableNullCollector implements Collector, InlineTaggableGaugeableCollector
{
    /**
     * Updates a counter by some arbitrary amount.
     *
     * @param string $variable
     * @param int $value The amount to increment the counter by
     * @param array $tags Tags to be attached to the metric
     * @return
     */
    public function measure($variable, $value, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * Increments a counter.
     *
     * @param string $variable
     * @param array $tags Tags to be attached to the metric
     */
    public function increment($variable, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * Decrements a counter.
     *
     * @param string $variable
     * @param array $tags Tags to be attached to the metric
     */
    public function decrement($variable, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * Records a timing.
     *
     * @param string $variable
     * @param int $time The duration of the timing in milliseconds
     * @param array $tags Tags to be attached to the metric
     */
    public function timing($variable, $time, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * Updates a gauge by an arbitrary amount.
     *
     * @param string $variable
     * @param int $value
     * @param array $tags Tags to be attached to the metric
     */
    public function gauge($variable, $value, $tags = array())
    {
        // Not logging, this is a null collector
    }

    /**
     * Sends the metrics to the adapter backend.
     */
    public function flush()
    {
        // Not logging, this is a null collector
    }
}
