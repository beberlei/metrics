<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

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
class DoctrineDBAL implements CollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly Connection $conn,
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = [$variable, $value, date('Y-m-d H:i:s')];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, 1, date('Y-m-d H:i:s')];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, -1, date('Y-m-d H:i:s')];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = [$variable, $time, date('Y-m-d H:i:s')];
    }

    public function flush(): void
    {
        if (!$this->data) {
            return;
        }

        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare('INSERT INTO metrics (metric, measurement, created) VALUES (?, ?, ?)');

            foreach ($this->data as $measurement) {
                $stmt->bindValue(1, $measurement[0]);
                $stmt->bindValue(2, $measurement[1], ParameterType::INTEGER);
                $stmt->bindValue(3, $measurement[2], ParameterType::STRING);
                $stmt->executeStatement();
            }

            $this->conn->commit();
        } catch (\Exception) {
            $this->conn->rollback();
        }

        $this->data = [];
    }
}
