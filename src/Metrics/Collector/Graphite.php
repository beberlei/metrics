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

use Beberlei\Metrics\Utils\Box;

/**
 * Sends statistics to the stats daemon over UDP or TCP.
 */
class Graphite implements CollectorInterface
{
    private array $data = [];

    public function __construct(
        private readonly string $host = 'localhost',
        private readonly int $port = 2003,
        private readonly string $protocol = 'tcp'
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->push($variable, $value);
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->push($variable, 1);
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->push($variable, -1);
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->push($variable, $time);
    }

    public function flush(): void
    {
        if (!$this->data) {
            return;
        }

        Box::box($this->doFlush(...));
    }

    public function push(string $variable, int|float $value, ?int $time = null): void
    {
        $this->data[] = sprintf(
            \is_float($value) ? "%s %.18f %d\n" : "%s %d %d\n",
            $variable,
            $value,
            $time ?: time()
        );
    }

    private function doFlush(): void
    {
        $fp = fsockopen($this->protocol . '://' . $this->host, $this->port);

        if (!$fp) {
            return;
        }

        foreach ($this->data as $line) {
            fwrite($fp, (string) $line);
        }

        fclose($fp);

        $this->data = [];
    }
}
