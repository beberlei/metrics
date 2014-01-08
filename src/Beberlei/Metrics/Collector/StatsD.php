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

/**
 * Sends statistics to the stats daemon over UDP
 */
class StatsD implements Collector
{
    private $host;
    private $port;
    private $data = array();

    public function __construct($host = 'localhost', $port = '8125')
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Log timing information
     *
     * @param string $variable The metric to in log timing info for.
     * @param float  $time     The ellapsed time (ms) to log
     **/
    public function timing($variable, $time)
    {
        $this->data[] = "$variable:$time|ms";
    }

    /**
     * Increments one or more variable counters
     *
     * @param string $variable The metric to increment.
     **/
    public function increment($variable)
    {
        $this->data[] = "$variable:1|c";
    }

    /**
     * Decrements one or more variable counters.
     *
     * @param string $variable The metric to increment.
     **/
    public function decrement($variable)
    {
        $this->data[] = "$variable:-1|c";
    }

    /**
     * Updates one or more variable counters by arbitrary amounts.
     *
     * @param string $variable The metric to update.
     * @param int    $value    The value to set
     **/
    public function measure($variable, $value)
    {
        $this->data[] = "$variable:$value|c";
    }

    /**
     * Updates one stat gauges by arbitrary amounts.
     *
     * @param string $variable The metric to update.
     * @param int    $value
     **/
    public function gauge($variable, $value)
    {
        $this->data[] = "$variable:$value|g";
    }

    /**
     * Squirt the metrics over UDP
     */
    public function flush()
    {
        if (!$this->data) {
            return;
        }

        $fp = fsockopen("udp://" . $this->host, $this->port, $errno, $errstr, 1.0);

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
