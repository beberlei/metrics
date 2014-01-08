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

use Exception;

/**
 * Sends statistics to the stats daemon over UDP or TCP
 */
class Graphite implements Collector
{
    private $protocol;
    private $host;
    private $port;
    private $data = array();

    public function __construct($host = 'localhost', $port = 2003, $protocol = 'tcp')
    {
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
    }

    /**
     * Log timing information
     *
     * @param string $variable The metric to in log timing info for.
     * @param float  $time     The ellapsed time (ms) to log
     **/
    public function timing($variable, $time)
    {
        $this->push($variable, $time);
    }

    /**
     * Increments one or more variable counters
     *
     * @param string $variable The metric to increment.
     **/
    public function increment($variable)
    {
        $this->push($variable, 1);
    }

    /**
     * Decrements one or more variable counters.
     *
     * @param string $variable The metric to increment.
     **/
    public function decrement($variable)
    {
        $this->push($variable, -1);
    }

    /**
     * Updates one or more variable counters by arbitrary amounts.
     *
     * @param string $variable The metric to update.
     * @param int    $value    The value to log
     **/
    public function measure($variable, $value)
    {
        $this->push($variable, $value);
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

            $fp = fsockopen($this->protocol . '://' . $this->host, $this->port);

            if (! $fp) {
                return;
            }

            foreach ($this->data as $line) {
                fwrite($fp, $line);
            }

            fclose($fp);
        } catch (Exception $e) {
        }

        $this->data = array();
    }

    public function push($stat, $value, $time = null)
    {
        $this->data[] = sprintf("%s %d %d\n", $stat, $value, $time ?: time());
    }
}
