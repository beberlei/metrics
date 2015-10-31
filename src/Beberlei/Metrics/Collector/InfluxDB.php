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

use InfluxDB\Client;

class InfluxDB implements Collector
{
    /** @var \InfluxDB\Client */
    private $client;

    /** @var array */
    private $data = array();

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    public function increment($variable)
    {
        $this->data[] = array($variable, 1);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement($variable)
    {
        $this->data[] = array($variable, -1);
    }

    /**
     * {@inheritDoc}
     */
    public function timing($variable, $time)
    {
        $this->data[] = array($variable, $time);
    }

    /**
     * {@inheritDoc}
     */
    public function measure($variable, $value)
    {
        $this->data[] = array($variable, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        foreach ($this->data as $data) {
            $this->client->mark($data[0], array('value' => $data[1]));
        }

        $this->data = array();
    }
}
