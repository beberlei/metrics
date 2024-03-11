<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
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
