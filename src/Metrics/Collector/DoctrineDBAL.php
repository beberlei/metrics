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

use Doctrine\DBAL\Connection;
use Exception;

/**
 * Sends statistics to a relational database.
 *
 * The database you connect to has to have a table
 * named `metrics` with columns:
 *
 * - metric VARCHAR(255)
 * - measurement INTEGER
 * - created DATETIME
 *
 * The Primary key can either be a surrogate (id) or
 * has to span all 3 columns.
 */
class DoctrineDBAL implements Collector
{
    /** @var \Doctrine\DBAL\Connection */
    private $conn;

    /** @var array */
    private $data;

    /**
     * @param \Doctrine\DBAL\Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * {@inheritdoc}
     */
    public function timing($stat, $time)
    {
        $this->data[] = array($stat, $time, date('Y-m-d'));
    }

    /**
     * {@inheritdoc}
     */
    public function increment($stats)
    {
        $this->data[] = array($stats, 1, date('Y-m-d'));
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($stats)
    {
        $this->data[] = array($stats, -1, date('Y-m-d'));
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->data[] = array($variable, $value, date('Y-m-d'));
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        if (!$this->data) {
            return;
        }

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('INSERT INTO metrics (metric, measurement, created) VALUES (?, ?, ?)');

            foreach ($this->data as $measurement) {
                $stmt->bindParam(1, $measurement[0]);
                $stmt->bindParam(2, $measurement[1]);
                $stmt->bindParam(3, $measurement[2]);
                $stmt->execute();
            }

            $this->conn->commit();
        } catch (Exception $e) {
            $this->conn->rollback();
        }

        $this->data = array();
    }
}
