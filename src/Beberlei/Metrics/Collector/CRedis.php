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
    private $host;
    private $port;
    private $credis;

    public function __construct($host = '127.0.0.1', $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
        $this->credis = new Credis_Client($host, $port);
    }

    public function increment($variable)
    {
        $this->credis->incr($variable);
    }

    public function decrement($variable)
    {
        $this->credis->decr($variable);
    }

    public function timing($variable, $time)
    {
        $this->credis->set($variable, $time);
    }

    public function measure($variable, $value)
    {
        $this->credis->set($variable, $value);
    }

    public function flush()
    {
        // No Need to Implement flush() method for now.
    }
}
