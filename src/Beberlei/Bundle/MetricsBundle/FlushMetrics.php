<?php

namespace Beberlei\Bundle\MetricsBundle;

use Beberlei\Metrics\Registry;

class FlushMetrics
{
    private $registry;
    private $logger;

    public function __construct(Registry $registry, $logger)
    {
        $this->registry = $registry;
        $this->logger = $logger;
    }

    public function onTerminate()
    {
        foreach ($this->registry->all() as $collector) {
            try {
                $collector->flush();
            } catch (\Exception $e) {
                $this->logger->err("Flushing metrics failed: ".$e->getMessage());
            }
        }

        $this->registry->clear();
    }
}
