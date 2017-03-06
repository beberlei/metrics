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

use InfluxDB\Client;

class InfluxDB implements Collector, TaggableCollector
{
    /** @var \InfluxDB\Client */
    private $client;

    /** @var array */
    private $data = array();

    /** @var array */
    private $tags = array();

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->data[] = array($variable, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->data[] = array($variable, -1);
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->data[] = array($variable, $time);
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->data[] = array($variable, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        foreach ($this->data as $data) {
            $this->client->mark(array(
                'points' => array(
                    array(
                        'measurement' => $data[0],
                        'fields' => array('value' => $data[1]),
                    ),
                ),
                'tags' => $this->tags,
            ));
        }

        $this->data = array();
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }
}
