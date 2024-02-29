<?php

/*
 * This file is part of the beberlei/metrics project.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Beberlei\Metrics\Collector;

use Prometheus\CollectorRegistry;
use Prometheus\Exception\MetricNotFoundException;
use Prometheus\Gauge;

class Prometheus implements CollectorInterface, TaggableCollectorInterface
{
    private array $data = ['counters' => [], 'gauges' => []];

    public function __construct(
        private readonly CollectorRegistry $collectorRegistry,
        private readonly string $namespace = '',
        private array $tags = [],
    ) {
    }

    public function measure(string $variable, int $value, array $tags = []): void
    {
        $this->data['gauges'][] = ['name' => $variable, 'value' => $value];
    }

    public function increment(string $variable, array $tags = []): void
    {
        $this->data['counters'][] = ['name' => $variable, 'value' => 1];
    }

    public function decrement(string $variable, array $tags = []): void
    {
        $this->data['counters'][] = ['name' => $variable, 'value' => -1];
    }

    public function timing(string $variable, int $time, array $tags = []): void
    {
        $this->measure($variable, $time);
    }

    public function flush(): void
    {
        if (!$this->data['gauges'] && !$this->data['counters']) {
            return;
        }

        $tagsValues = array_values($this->tags);

        foreach ($this->data['counters'] as $counterData) {
            $gauge = $this->getOrRegisterGaugeForVariable($counterData['name']);

            if ($counterData['value'] > 0) {
                $gauge->inc($tagsValues);
            } elseif ($counterData['value'] < 0) {
                $gauge->dec($tagsValues);
            }
        }

        foreach ($this->data['gauges'] as $gaugeData) {
            $gauge = $this->getOrRegisterGaugeForVariable($gaugeData['name']);

            $gauge->set($gaugeData['value'], $tagsValues);
        }

        $this->data = ['counters' => [], 'gauges' => []];
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    private function getOrRegisterGaugeForVariable(string $variable): Gauge
    {
        try {
            $gauge = $this->collectorRegistry->getGauge($this->namespace, $variable);
        } catch (MetricNotFoundException) {
            $gauge = $this->collectorRegistry->registerGauge(
                $this->namespace,
                $variable,
                '',
                array_keys($this->tags)
            );
        }

        return $gauge;
    }
}
