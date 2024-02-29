<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use InfluxDB\Client;

class InfluxDB implements CollectorInterface, TaggableCollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly Client $client,
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
        foreach ($this->data as $data) {
            try {
                $this->client->mark(['points' => [['measurement' => $data[0], 'fields' => ['value' => $data[1]]]], 'tags' => $data[2] + $this->tags]);
            } catch (\Exception) {
                continue;
            }
        }

        $this->data = [];
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
