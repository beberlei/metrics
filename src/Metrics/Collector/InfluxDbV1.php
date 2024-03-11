<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use InfluxDB\Database;
use InfluxDB\Exception;
use InfluxDB\Point;

class InfluxDbV1 implements CollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly Database $database,
        private array $tags = [],
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = [$variable, $value, $tags];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, 1, $tags];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = [$variable, -1, $tags];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = [$variable, $time, $tags];
    }

    public function flush(): void
    {
        $points = [];
        foreach ($this->data as $data) {
            $points[] = new Point(
                $data[0],
                $data[1],
                $this->tags + $data[2],
            );
        }

        try {
            $this->database->writePoints($points, Database::PRECISION_SECONDS);
        } catch (Exception) {
        }

        $this->data = [];
    }
}
