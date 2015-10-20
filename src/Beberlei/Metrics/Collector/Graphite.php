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
    /** @var string */
    private $protocol;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var array */
    private $data = array();

    /**
     * @param string $host
     * @param int $port
     * @param string $protocol
     */
    public function __construct($host = 'localhost', $port = 2003, $protocol = 'tcp')
    {
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
    }

    /**
     * {@inheritDoc}
     */
    public function timing($variable, $time)
    {
        $this->pushStat($variable, $time);
    }

    /**
     * {@inheritDoc}
     */
    public function increment($variable)
    {
        $this->pushStat($variable, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement($variable)
    {
        $this->pushStat($variable, -1);
    }

    /**
     * {@inheritDoc}
     */
    public function measure($variable, $value)
    {
        $this->pushStat($variable, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        if (!$this->data) {
            return;
        }

        try {
            $fp = fsockopen($this->protocol.'://'.$this->host, $this->port);

            if (!$fp) {
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

    /**
     * @param string $stat
     * @param string $value
     * @param int|null $time
     */
    private function pushStat($stat, $value, $time = null)
    {
        $this->data[] = sprintf(
            is_float($value) ? "%s %.18f %d\n" : "%s %d %d\n",
            $stat,
            $value,
            $time ?: time()
        );
    }

    /**
     * @internal
     * @todo Makes this method private, move pushStat() logic into this one afterward
     *
     * @param $stat
     * @param $value
     * @param int|null $time
     */
    public function push($stat, $value, $time = null)
    {
        trigger_error(
            'This method is used for internal usage only. It will be removed from public API on the next major version',
            E_USER_WARNING
        );

        $this->pushStat($stat, $value, $time);
    }
}
