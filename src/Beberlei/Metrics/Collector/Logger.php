<?php
/**
 * Beberlei Metrics
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

class Logger implements Collector
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
     * {@inheritDoc}
     */
    public function increment($variable)
    {
        $this->logger->debug('increment:'.$variable);
    }

    /**
     * {@inheritDoc}
     */
    public function decrement($variable)
    {
        $this->logger->debug('decrement:'.$variable);
    }

    /**
     * {@inheritDoc}
     */
    public function timing($variable, $time)
    {
        $this->logger->debug(sprintf('timing:%s:%s', $variable, $time));
    }

    /**
     * {@inheritDoc}
     */
    public function measure($variable, $value)
    {
        $this->logger->debug(sprintf('measure:%s:%s', $variable, $value));
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
    }
}
