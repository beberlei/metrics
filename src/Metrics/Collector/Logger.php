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

use Psr\Log\LoggerInterface;

class Logger implements CollectorInterface, GaugeableCollectorInterface
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->logger->debug(sprintf('measure:%s:%s', $variable, $value));
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->logger->debug('increment:' . $variable);
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->logger->debug('decrement:' . $variable);
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->logger->debug(sprintf('timing:%s:%s', $variable, $time));
    }

    public function gauge(string $variable, string|int $value, array $tags = []): void
    {
        $this->logger->debug(sprintf('gauge:%s:%s', $variable, $value));
    }

    public function flush(): void
    {
        $this->logger->debug('flush');
    }
}
