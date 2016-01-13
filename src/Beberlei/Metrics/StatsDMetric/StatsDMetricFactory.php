<?php

namespace Beberlei\Metrics\StatsDMetric;

/**
 * Class StatsDMetricFactory.
 *
 * @package Beberlei\Metrics\StatsDMetric
 */
interface StatsDMetricFactory
{
    /**
     * @param string $metric
     * @param string $value
     * @param string $typeOfMetric
     * @param float $sampleRate
     * @param array $tags
     * @return string
     */
    public function create($metric, $value, $typeOfMetric, $sampleRate = 1, array $tags = array());
}
