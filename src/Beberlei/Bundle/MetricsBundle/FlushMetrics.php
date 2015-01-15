<?php

namespace Beberlei\Bundle\MetricsBundle;

use Beberlei\Metrics\Registry;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class FlushMetrics
{
    private $registry;
    private $logger;

    public function __construct(Registry $registry, LoggerInterface $logger = null)
    {
        $this->registry = $registry;
        $this->logger = $logger ?: new NullLogger;
    }

    public function onTerminate()
    {
        foreach ($this->registry->all() as $collector) {
            try {
                $collector->flush();
            } catch (\Exception $e) {
                $this->logger->error('Flushing metrics failed: '.$e->getMessage(), array('exception' => $e));
            }
        }

        $this->registry->clear();
    }
}
