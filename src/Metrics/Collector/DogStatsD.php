<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Beberlei\Metrics\Utils\Box;

class DogStatsD implements CollectorInterface, GaugeableCollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly string $host = 'localhost',
        private readonly int $port = 8125,
        private readonly string $prefix = '',
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|c%s', $variable, $value, $this->buildTagString($tags));
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = $variable . ':1|c' . $this->buildTagString($tags);
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = $variable . ':-1|c' . $this->buildTagString($tags);
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|ms%s', $variable, $time, $this->buildTagString($tags));
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|g%s', $variable, $value, $this->buildTagString($tags));
    }

    public function flush(): void
    {
        if (!$this->data) {
            return;
        }
        Box::box($this->doFlush(...));
    }

    private function doFlush(): void
    {
        $fp = fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, 1.0);

        if (!$fp) {
            return;
        }

        foreach ($this->data as $line) {
            fwrite($fp, $this->prefix . $line);
        }

        fclose($fp);

        $this->data = [];
    }

    /**
     * Given a key/value map of metric tags, builds them into a
     * DogStatsD tag string and returns the string.
     */
    private function buildTagString(array $tags): string
    {
        $results = [];

        foreach ($tags as $key => $value) {
            $results[] = sprintf('%s:%s', $key, $value);
        }

        $tagString = implode(',', $results);

        if (\strlen($tagString)) {
            $tagString = sprintf('|#%s', $tagString);
        }

        return $tagString;
    }
}
