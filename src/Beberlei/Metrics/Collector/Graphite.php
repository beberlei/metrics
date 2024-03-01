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

use Exception;

/**
 * Sends statistics to the stats daemon over UDP or TCP.
 */
class Graphite implements Collector
{
    /** @var string */
    private $protocol;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var int fsocket connection timeout, in seconds. */
    private $timeout;

    /** @var array */
    private $data = array();

    /**
     * @param string $host
     * @param int $port
     * @param string $protocol
     */
    public function __construct($host = 'localhost', $port = 2003, $timeout = null, $protocol = 'tcp')
    {
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
        $this->timeout = $timeout;
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->push($variable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->push($variable, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->push($variable, -1);
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->push($variable, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (!$this->data) {
            return;
        }

        $fp = @fsockopen($this->protocol . '://' . $this->host, $this->port, $errno, $errmsg, $this->timeout);

        if ($errno != 0 || $fp == false) {
            throw new \RuntimeException("Couldn't connect to " . $this->host . ':' . $this->port . ' with message: ' . $errmsg);
        }

        foreach ($this->data as $line) {
            fwrite($fp, $line);
        }

        fclose($fp);

        $this->data = array();
    }

    public function push($stat, $value, $time = null)
    {
        $this->data[] = sprintf(
            is_float($value) ? "%s %.18f %d\n" : "%s %d %d\n",
            $stat,
            $value,
            $time ?: time()
        );
    }
}

