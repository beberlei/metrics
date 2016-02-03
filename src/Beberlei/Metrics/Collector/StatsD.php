<?php
/**
 * Beberlei Metrics
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

use Beberlei\Metrics\StatsDMetric\StatsDMetricFactory;

/**
 * Sends statistics to the stats daemon over UDP
 */
class StatsD implements Collector, GaugeableCollector
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var StatsDMetricFactory */
    private $metricFactory;

    /** @var array */
    private $data;

    /** @var float */
    private $sampleRate;

    /**
     * @param StatsDMetricFactory $metricFactory
     * @param string $host
     * @param string $port
     * @param float $sampleRate
     */
    public function __construct(StatsDMetricFactory $metricFactory, $host = 'localhost', $port = '8125', $sampleRate = 1)
    {
        $this->host = $host;
        $this->port = $port;
        $this->metricFactory = $metricFactory;
        $this->sampleRate = $sampleRate;
        $this->data = array();
    }

    /**
     * {@inheritDoc}
     */
    public function timing($variable, $time, array $tags = array())
    {
        $this->data[] = $this->metricFactory->create($variable, $time, 'ms', $this->sampleRate, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function increment($variable, array $tags = array())
    {
        $this->data[] = $this->metricFactory->create($variable, 1, 'c', $this->sampleRate, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement($variable, array $tags = array())
    {
        $this->data[] = $this->metricFactory->create($variable, -1, 'c', $this->sampleRate, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function measure($variable, $value, array $tags = array())
    {
        $this->data[] = $this->metricFactory->create($variable, $value, 'c', $this->sampleRate, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function gauge($variable, $value, array $tags = array())
    {
        $this->data[] = $this->metricFactory->create($variable, $value, 'g', $this->sampleRate, $tags);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        if (!$this->data) {
            return;
        }

        $fp = fsockopen('udp://'.$this->host, $this->port, $errno, $errstr, 1.0);

        if (!$fp) {
            return;
        }

        $level = error_reporting(0);
        foreach ($this->data as $line) {
            fwrite($fp, $line);
        }
        error_reporting($level);

        fclose($fp);

        $this->data = array();
    }
}
