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
class DoctrineDBAL implements CollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly Connection $conn
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = [$variable, $value, date('Y-m-d')];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, 1, date('Y-m-d')];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, -1, date('Y-m-d')];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = [$variable, $time, date('Y-m-d')];
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
                $stmt->bindParam(1, $measurement[0]);
                $stmt->bindParam(2, $measurement[1]);
                $stmt->bindParam(3, $measurement[2]);
                $stmt->executeStatement();
            }

            $this->conn->commit();
        } catch (Exception) {
            $this->conn->rollback();
        }

        $this->data = [];
    }
}
