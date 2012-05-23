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

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Log timing information
     *
     * @param string $stats The metric to in log timing info for.
     * @param float $time The ellapsed time (ms) to log
     **/
    public function timing($stat, $time)
    {
        $this->data[] = "$stat:$time|ms";
    }

    /**
     * Increments one or more stats counters
     *
     * @param string $stats The metric to increment.
     * @return boolean
     **/
    public function increment($stats)
    {
        $this->data[] = "$stats:1|c";
    }

    /**
     * Decrements one or more stats counters.
     *
     * @param string $stats The metric to increment.
     * @return boolean
     **/
    public function decrement($stats)
    {
        $this->data[] = "$stats:-1|c";
    }

    /**
     * Updates one or more stats counters by arbitrary amounts.
     *
     * @param string $stats The metric to update.
     * @param int|1 $delta The amount to increment/decrement each metric by.
     **/
    public function measure($variable, $value)
    {
        $this->data[] = "$variable:$delta|c";
    }

    /**
     * Squirt the metrics over UDP
     */
    public function flush()
    {
        if ( ! $this->data) {
            return;
        }

        try {

            $fp = fsockopen("udp://" . $this->host, $this->port, $errno, $errstr);

            if (! $fp) {
                return;
            }

            foreach ($this->data as $line) {
                fwrite($fp, $line);
            }

            fclose($fp);
        } catch (Exception $e) {
        }
    }
}
