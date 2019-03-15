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

use InfluxDB\Database;
use InfluxDB\Point;

class InfluxDBOfficial implements Collector, TaggableCollector
{
    private $database;

    private $data = array();

    private $tags = array();

    /**
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        if (!class_exists(Database::class)) {
            throw new \RuntimeException('Package \'influxdb/influxdb-php\' is required to use this collector.');
        }

        $this->database = $database;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->data[] = array($variable, 1, $this->getCurrentTimestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->data[] = array($variable, -1, $this->getCurrentTimestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->data[] = array($variable, $time, $this->getCurrentTimestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->data[] = array($variable, $value, $this->getCurrentTimestamp());
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        foreach ($this->data as $data) {
            $this->database->writePoints(
                array(
                    new Point(
                        $data[0],
                        $data[1],
                        $this->tags,
                        array(),
                        $data[2]
                    ),
                ),
                Database::PRECISION_SECONDS
            );
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

    /**
     * @return int
     */
    private function getCurrentTimestamp()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        return $date->getTimestamp();
    }
}
