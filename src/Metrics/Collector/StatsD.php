<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Beberlei\Metrics\Utils\Box;

/**
 * Sends statistics to the stats daemon over UDP.
 */
class StatsD implements CollectorInterface, GaugeableCollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly string $host = 'localhost',
        private readonly int $port = 8125,
        private readonly string $prefix = '',
    ) {
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|ms', $variable, $time);
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = $variable . ':1|c';
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = $variable . ':-1|c';
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|c', $variable, $value);
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|g', $variable, $value);
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
}
