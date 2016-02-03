<?php

namespace Beberlei\Metrics\StatsDMetric;

/**
 * Class DataDogStatsD.
 *
 * @link https://github.com/DataDog/php-datadogstatsd/blob/master/libraries/datadogstatsd.php
 *
 * @package Beberlei\Metrics\StatsDMetric
 */
class DataDogStatsD extends StatsD implements StatsDMetricFactory
{
    /**
     * @param string $metric
     * @param string $value
     * @param string $typeOfMetric
     * @param float $sampleRate
     * @param array $tags
     * @return string
     */
    public function create($metric, $value, $typeOfMetric, $sampleRate = 1, array $tags = array())
    {
        $result = sprintf('%s:%s|%s|@%s', $this->prefixMetric($metric), $value, $typeOfMetric, $sampleRate);
        if (!empty($tags)) {
            $result .= "|{$this->tagsToString($tags)}";
        }

        return $result;
    }

    /**
     * @param array $tags
     * @return string
     */
    private function tagsToString(array $tags = array())
    {
        return implode(',', array_map(function($key, $value) { return "#{$key}:{$value}"; }, $tags));
    }
}
