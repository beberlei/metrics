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
 * Sends statistics to the stats daemon over UDP.
 */
class StatsD implements Collector, GaugeableCollector
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var string */
    private $prefix;

    /** @var array */
    private $data;

    /**
     * @param string $host
     * @param string $port
     * @param string $prefix
     */
    public function __construct($host = 'localhost', $port = '8125', $prefix = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->prefix = $prefix;
        $this->data = array();
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->data[] = sprintf('%s:%s|ms', $variable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->data[] = $variable.':1|c';
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->data[] = $variable.':-1|c';
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->data[] = sprintf('%s:%s|c', $variable, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function gauge($variable, $value)
    {
        $this->data[] = sprintf('%s:%s|g', $variable, $value);
    }

    /**
     * {@inheritdoc}
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
            fwrite($fp, $this->prefix.$line);
        }
        error_reporting($level);

        fclose($fp);

        $this->data = array();
    }
}
