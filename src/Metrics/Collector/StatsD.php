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
        $this->data[] = $variable.':1|c';
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = $variable.':-1|c';
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|c', $variable, $value);
    }

    public function gauge(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s:%s|g', $variable, $value);
    }

    public function flush(): void
    {
        if (!$this->data) {
            return;
        }

        $fp = fsockopen('udp://'.$this->host, $this->port, $errno, $errstr, 1.0);

        if (!$fp) {
            return;
        }

        $level = error_reporting(0);
        foreach ($this->data as $line) {
            fwrite($fp, $this->prefix.$line);
        }

        error_reporting($level);

        fclose($fp);

        $this->data = [];
    }
}
