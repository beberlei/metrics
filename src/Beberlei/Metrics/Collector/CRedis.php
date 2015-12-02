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

use Credis_Client;

class CRedis implements Collector
{
    /** @var string */
    private $host;

    /** @var string */
    private $port;

    /** @var \Credis_Client */
    private $credis;

    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
        $this->credis = new Credis_Client($host, $port);
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->credis->incr($variable);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->credis->decr($variable);
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->credis->set($variable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->credis->set($variable, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        // No Need to Implement flush() method for now.
    }
}
