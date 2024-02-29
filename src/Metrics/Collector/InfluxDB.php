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
            $this->client->mark(['points' => [['measurement' => $data[0], 'fields' => ['value' => $data[1]]]], 'tags' => $data[2] + $this->tags]);
        }

        $this->data = [];
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }
}
