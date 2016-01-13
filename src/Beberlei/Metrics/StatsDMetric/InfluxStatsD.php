<?php

namespace Beberlei\Metrics\StatsDMetric;

/**
 * Class Influx.
 *
 * @link https://influxdb.com/blog/2015/11/03/getting_started_with_influx_statsd.html
 *
 * @package Beberlei\Metrics\StatsDMetric
 */
class InfluxStatsD extends StatsD implements StatsDMetricFactory
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
        return sprintf(
            '%s%s:%s|%s|@%s',
            $this->prefixMetric($metric),
            $this->tagsToString($tags),
            $value,
            $typeOfMetric,
            $sampleRate
        );
    }

    /**
     * @param array $tags
     * @return string
     */
    private function tagsToString(array $tags = array())
    {
        $result = implode(',', array_map(function($key, $value) { return "{$key}={$value}"; }, $tags));
        if (mb_strlen($result) > 0) {
            $result = ',' . $result;
        }

        return $result;
    }
}
