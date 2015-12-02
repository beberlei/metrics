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
    /** @var \Credis_Client */
    private $credis_client;

    public function __construct(Credis_Client $credis_client)
    {
        $this->credis_client = $credis_client;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->credis_client->incr($variable);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->credis_client->decr($variable);
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->credis_client->set($variable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->credis_client->set($variable, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        // No Need to Implement flush() method for now.
    }
}
