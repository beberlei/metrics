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

class Logger implements Collector, GaugeableCollector
{
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function increment($variable)
    {
        $this->logger->debug('increment:'.$variable);
    }

    /**
     * {@inheritdoc}
     */
    public function decrement($variable)
    {
        $this->logger->debug('decrement:'.$variable);
    }

    /**
     * {@inheritdoc}
     */
    public function timing($variable, $time)
    {
        $this->logger->debug(sprintf('timing:%s:%s', $variable, $time));
    }

    /**
     * {@inheritdoc}
     */
    public function measure($variable, $value)
    {
        $this->logger->debug(sprintf('measure:%s:%s', $variable, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function gauge($variable, $value)
    {
        $this->logger->debug(sprintf('gauge:%s:%s', $variable, $value));
    }

    /**
     * {@inheritdoc}
     */
    public function flush()
    {
        $this->logger->debug('flush');
    }
}
