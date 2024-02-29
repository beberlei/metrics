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
 * Sends statistics to the StatsD daemon over UDP,
 * ad hoc implementation for the StatsD - Telegraf integration,
 * support tagging.
 */
class Telegraf implements CollectorInterface, GaugeableCollectorInterface, TaggableCollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly string $host = 'localhost',
        private readonly int $port = 8125,
        private readonly string $prefix = '',
        private string $tags = '',
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s%s:%s|c', $variable, $this->tags, $value);
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data[] = $variable . $this->tags . ':1|c';
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data[] = $variable . $this->tags . ':-1|c';
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->data[] = sprintf('%s%s:%s|ms', $variable, $this->tags, $time);
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        $this->data[] = sprintf('%s%s:%s|g', $variable, $this->tags, $value);
    }

    public function set(string $variable, string $value): void
    {
        $this->data[] = sprintf('%s%s:%s|s', $variable, $this->tags, $value);
    }

    public function flush(): void
    {
        if (!$this->data) {
            return;
        }

        $fp = fsockopen('udp://' . $this->host, $this->port, $errno, $errstr, 1.0);

        if (!$fp) {
            return;
        }

        $level = error_reporting(0);
        foreach ($this->data as $line) {
            fwrite($fp, $this->prefix . $line);
        }

        error_reporting($level);

        fclose($fp);

        $this->data = [];
    }

    public function setTags(array $tags): void
    {
        $this->tags = http_build_query($tags, '', ',');
        $this->tags = \strlen($this->tags) > 0 ? ',' . $this->tags : $this->tags;
    }
}
