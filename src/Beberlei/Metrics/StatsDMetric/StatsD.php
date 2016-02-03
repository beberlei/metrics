<?php

namespace Beberlei\Metrics\StatsDMetric;

/**
 * Class StatsD.
 *
 * @link https://github.com/etsy/statsd/blob/master/docs/metric_types.md
 *
 * @package Beberlei\Metrics\StatsDMetric
 */
class StatsD implements StatsDMetricFactory
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * StatsD constructor.
     * @param string $prefix
     */
    public function __construct($prefix = "")
    {
        $this->prefix = $prefix;
    }

    /**
     * Create a metric for plain StatsD.
     *
     * @param string $metric
     * @param string $value
     * @param string $typeOfMetric
     * @param float $sampleRate
     * @param array $tags
     * @return string
     */
    public function create($metric, $value, $typeOfMetric, $sampleRate = 1, array $tags = array())
    {
        // There is no tags support in plain statsD.
        return sprintf('%s:%s|%s|@%s', $this->prefixMetric($metric), $value, $typeOfMetric, $sampleRate);
    }

    /**
     * @param string $metric
     * @return string
     */
    protected function prefixMetric($metric)
    {
        return "{$this->prefix}$metric";
    }
}
