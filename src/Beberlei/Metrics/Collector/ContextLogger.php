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

class ContextLogger implements Collector
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function increment($variable)
    {
        $this->logger->debug('{type}:{variable}', array('type' => 'increment', 'variable' => $variable));
    }

    public function decrement($variable)
    {
        $this->logger->debug('{type}:{variable}', array('type' => 'decrement', 'variable' => $variable));
    }

    public function timing($variable, $time)
    {
        $this->logger->debug(
            '{type}:{variable}:{time}', array('type' => 'timing', 'variable' => $variable, 'value' => $time)
        );
    }

    public function measure($variable, $value)
    {
        $this->logger->debug(
            '{type}:{variable}:{value}', array('type' => 'measure', 'variable' => $variable, 'value' => $value)
        );
    }

    public function flush()
    {
    }
}
